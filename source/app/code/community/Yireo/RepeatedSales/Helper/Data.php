<?php
/**
 * RepeatedSales plugin for Magento 
 *
 * @package     Yireo_RepeatedSales
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (http://www.yireo.com/)
 * @license     Open Source License
 */

class Yireo_RepeatedSales_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getConfigValue($key = null, $default_value = null)
    {
        $value = Mage::getStoreConfig($key);
        if(empty($value)) $value = $default_value;
        return $value;
    }
}
