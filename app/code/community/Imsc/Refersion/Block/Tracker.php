<?php

/**
 * Created by PhpStorm.
 * User: Numa
 * Date: 1/24/2017
 * Time: 5:26 PM
 */
class Imsc_Refersion_Block_Tracker extends Mage_Core_Block_Template
{
    /**
     *  Check if refersion tracking is enable
     *
     *  @return	  string
     */
    public function isEnabled()
    {
        return Mage::getStoreConfig('refersion/refersion_settings/refersion_active');
    }

    /**
     * Get tracking js snippet
     *
     * @return string
     */
    public function getTrackingCode()
    {
        // Get API key from config
        $api_key = Mage::getStoreConfig('refersion/refersion_settings/refersion_api_key');

        // Build the url to get the script
        $script_url = '//www.refersion.com/tracker/v3/'.$api_key.'.js';

        return $script_url;
    }
}