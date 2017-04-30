<?php
/**
 * RepeatedSales plugin for Magento 
 *
 * @package     Yireo_RepeatedSales
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     Open Software License
 */

/**
 * Class Yireo_RepeatedSales_Model_Rewrite_Rule_Condition_Product_Found
 */
class Yireo_RepeatedSales_Model_Rewrite_Rule_Condition_Product_Found extends Mage_SalesRule_Model_Rule_Condition_Product_Found
{
    /**
     * Override of original Magento method
     *
     * @param Varien_Object $object Quote
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getValue();
        $found = false;

        $quoteItems = $object->getAllItems();
        $quoteItems = $this->appendCustomerOrderItems($quoteItems);

        foreach ($quoteItems as $quoteItem) {
            $found = $all;
            foreach ($this->getConditions() as $condition) {
                /** @var Mage_Rule_Model_Condition_Abstract $condition */
                $validated = $condition->validate($quoteItem);
                if (($all && !$validated) || (!$all && $validated)) {
                    $found = $validated;
                    break;
                }
            }

            if (($found && $true) || (!$true && $found)) {
                break;
            }
        }

        // Found an item and we're looking for existing one
        if ($found && $true) {
            return true;
        }

        // Not found and we're making sure it doesn't exist
        if (!$found && !$true) {
            return true;
        }

        return false;
    }

    /**
     * @param array $currentItemsCollection
     *
     * @return array
     */
    private function appendCustomerOrderItems($currentItemsCollection)
    {
        if($this->getOrderItemHelper()->isCustomerLoggedIn() === false) {
            return $currentItemsCollection;
        }

        try {
            $previousOrderItemsCollection = $this->getOrderItemHelper()->getPreviousOrderItemCollection();
        } catch(Exception $e) {
            return $currentItemsCollection;
        }

        if (empty($previousOrderItemsCollection)) {
            return $currentItemsCollection;
        }

        // Merge the results
        foreach($previousOrderItemsCollection as $previousOrderItem) {

            /** @var Mage_Sales_Model_Order_Item $previousOrderItem */
            $product = $previousOrderItem->getProduct();
            if($this->getOrderItemHelper()->isMatchableOrderItem($previousOrderItem) === false) {
                continue;
            }

            $product->setData('previous_order', 1);
            $previousOrderItem->setData('product', $product);
            $previousOrderItem->setData('previous_order', 1);

            $this->addToCollection($previousOrderItem, $currentItemsCollection);
        }
        
        return $currentItemsCollection;
    }


    /**
     * @param Mage_Sales_Model_Order_Item $item
     * @param Varien_Data_Collection|array $collection
     */
    private function addToCollection(Mage_Sales_Model_Order_Item $item, &$collection)
    {
        $mergeType = (is_array($collection)) ? 'array' : 'collection';
        if($mergeType == 'array') {
            $collection[] = $item;
        } else {
            $collection->addItem($item);
        }
    }

    /**
     * @return Yireo_RepeatedSales_Helper_OrderItem
     */
    private function getOrderItemHelper()
    {
        /** @var Yireo_RepeatedSales_Helper_OrderItem $helper */
        $helper = Mage::helper('repeatedsales/orderItem');
        return $helper;
    }

    private function log($message)
    {
        Mage::helper('repeatedsales')->debug($message);
    }
}
