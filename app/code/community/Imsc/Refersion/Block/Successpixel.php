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
* Refersion success pixel block
*
* @category    IMSC
* @package     Imsc_Refersion
* @author	    Refersion Developer <info@refersion.com>
*/
class Imsc_Refersion_Block_Successpixel extends Mage_Checkout_Block_Success
{
    /**
    *  Check if refersion tracking is enable
    *
    *  @return	  string
    */
    public function isEnabled()
    {
        return Mage::getStoreConfig('refersion/refersion_settings/refersion_active');
    }//end function isEnabled

    /**
    *  Return refersion traking url with api and unique code
    *
    *  @return	  string
    */
    public function getSuccessPixel ()
    {
        // Get API key from config
        $api_key = Mage::getStoreConfig('refersion/refersion_settings/refersion_api_key');

        // Get subdomain, if any
        $subdomain = Mage::getStoreConfig('refersion/refersion_settings/refersion_subdomain');

        // If the subdomain is empty, we default to www
        if( empty( $subdomain ) )
        {
            $subdomain = 'www';
        }

        // Get random string
        $ci = $this->_getUniqueCode();

        // Build URL for pixel
        $refersion_url = 'https://'.$subdomain.'.refersion.com/tracker/magento?k='.$api_key.'&ci='.$ci;

        return $refersion_url;
    }//end funcion getSuccessPixel

    /**
    *  Return unique code
    *
    *  @return	  string
    */
    private function _getUniqueCode()
    {
        return Mage::getSingleton("core/session")->getEncryptedSessionId();;
    }//end function _getUniqueCode
}