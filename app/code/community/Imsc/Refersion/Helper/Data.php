<?php
/*
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/gpl-license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@refersion.com so we can send you a copy immediately.
 *
 * @category   IMSC
 * @package    Imsc_Refersion
 * @copyright  Copyright (c) 2015 Refersion, Inc.
 * @license    http://opensource.org/licenses/gpl-license GNU General Public License
 */

/**
 * Refersion default helper
 *
 * @category    IMSC
 * @package     Imsc_Refersion
 * @author	    Refersion Developer <info@refersion.com>
 */
class Imsc_Refersion_Helper_Data extends Mage_Core_Helper_Abstract
{
    function generateCartId($orderId) {

        $order = Mage::getModel('sales/order')->load($orderId);
        $incrementId = $order->getIncrementId();
        $customerEmail = $order->getCustomerEmail();

        $cartId = sha1($incrementId.$customerEmail);

        return $cartId;

    }

    function callRefersionWebhook($orderId, $cartId){

        $order = Mage::getModel('sales/order')->load($orderId);

        //Api key from configuration
        $api_key = Mage::getStoreConfig('refersion/refersion_settings/refersion_api_key');

        //Secret key from configuration
        $secret_key = Mage::getStoreConfig('refersion/refersion_settings/refersion_secret_key');

        if($api_key != "") {

            //array to hold order value to be converted in json
            $order_json = array();

            //populating values to repective indexes
            $order_json['refersion_public_key'] = $api_key;
            $order_json['refersion_secret_key'] = $secret_key;

            $order_json['order_id'] = $order->getIncrementId();
            $order_json['unique_id'] = $cartId;

            $order_json['shipping'] = $order->getShippingAmount();

            //Tax info
            $tax_info = $order->getFullTaxInfo();
            $order_json['tax'] = 0;
            if(!empty($tax_info)) {
                foreach ($tax_info as $info) $order_json['tax'] = $order_json['tax']+$info['amount'];
            }

            $order_json['discount'] = abs($order->getDiscountAmount());
            $order_json['discount_code'] = $order->getCouponCode();

            //Get all the items for the order to fecth individual details
            $items = $order->getAllItems();

            foreach ($items as $itemId => $item) {
                $order_json['products'][$item->getProductId()]['sku'] = $item->getSku();
                $order_json['products'][$item->getProductId()]['quantity'] = $item->getQtyOrdered();
                $order_json['products'][$item->getProductId()]['price'] = $item->getPrice();
            }

            $order_json['currency_code'] = $order->getBaseCurrency()->getCurrencyCode();

            //Customer details
            $order_json['customer']['first_name'] = $order->getBillingAddress()->getFirstname();
            $order_json['customer']['last_name'] = $order->getBillingAddress()->getLastname();
            $order_json['customer']['email'] = $order->getBillingAddress()->getEmail();
            $order_json['customer']['ip_address'] = $order->getRemoteIp();

            //Sending value via curl to refersion
            $this->curlRefersion($order_json);

        }

    }

    private function curlRefersion($data){

        // URL to post to
        $url = 'https://www.refersion.com/tracker/magento/conversion';

        // Start cURL
        $curl = curl_init();

        // Headers
        $headers = array();
        $headers[] = 'Content-type: application/json';

        // Run cURL
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array($headers),
            CURLOPT_POSTFIELDS => base64_encode(json_encode($data)),
            CURLOPT_TIMEOUT => 60,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HEADER => FALSE,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_USERAGENT => 'Refersion Reporter'
        );
        curl_setopt_array($curl,$options);

        // Get response
        $response = curl_exec($curl);

        // Close cURL
        curl_close($curl);

        // Return response from Refersion
        return $response;

    }
}