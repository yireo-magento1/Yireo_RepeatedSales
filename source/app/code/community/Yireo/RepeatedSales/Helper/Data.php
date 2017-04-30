<?php
/**
 * RepeatedSales plugin for Magento 
 *
 * @package     Yireo_RepeatedSales
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Source License
 */

/**
 * Class Yireo_RepeatedSales_Helper_Data
 */
class Yireo_RepeatedSales_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Switch to determine whether this extension is enabled or not
     *
     * @return bool
     */
    public function enabled()
    {
        if ((bool) Mage::getStoreConfig('advanced/modules_disable_output/Yireo_RepeatedSales')) {
            return false;
        }

        return true;
    }

    /**
     * @param string $key
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    public function getConfigValue($key, $defaultValue = null)
    {
        $value = Mage::getStoreConfig($key);
        if(empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }

    /**
     * @param string $string
     */
    public function debug($string)
    {
        //Mage::log($string);
        file_put_contents(BP. '/var/log/system.log', $string."\n", FILE_APPEND);
    }
}
