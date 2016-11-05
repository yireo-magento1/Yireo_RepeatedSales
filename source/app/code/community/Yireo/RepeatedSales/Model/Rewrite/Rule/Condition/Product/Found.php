<?php
/**
 * RepeatedSales plugin for Magento 
 *
 * @package     Yireo_RepeatedSales
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2015 Yireo (https://www.yireo.com/)
 * @license     Open Software License
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
        $all = $this->getAggregator()==='all';
        $true = (bool)$this->getValue();
        $found = false;
        foreach ($this->getCustomerOrderItems($object->getAllItems()) as $item) {
            $found = $all;
            foreach ($this->getConditions() as $cond) {
                $validated = $cond->validate($item);
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
        elseif (!$found && !$true) {
            return true;
        }

        return false;
    }

    private function getCustomerOrderItems($currentItemsCollection)
    {
        if(Mage::getSingleton('customer/session')->isLoggedIn() == false) {
            return $currentItemsCollection;
        }

        $customer = Mage::helper('customer')->getCustomer();
        $previousOrdersCollection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_COMPLETE)
        ;

        $previousOrderIds = array();
        foreach($previousOrdersCollection as $previousOrder) {
            $previousOrderIds[] = $previousOrder->getId();
        }

        if(empty($previousOrderIds)) {
            return $currentItemsCollection;
        }

        $previousOrderItemsCollection = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToFilter('order_id', $previousOrderIds)
        ;

        // Merge the results
        $mergeType = (is_array($currentItemsCollection)) ? 'array' : 'collection';
        foreach($previousOrderItemsCollection as $previousOrderItem) {

            $product = $previousOrderItem->getProduct();
            $order = $previousOrderItem->getOrder();
            if($order->getState() != Mage_Sales_Model_Order::STATE_COMPLETE) {
                continue;
            }

            if($previousOrderItem->getQtyCanceled() == $previousOrderItem->getQtyOrdered()) {
                continue;
            }

            if($previousOrderItem->getQtyRefunded() == $previousOrderItem->getQtyInvoiced()) {
                continue;
            }

            $product->setData('previous_order', 1);
            $previousOrderItem->setProduct($product);

            if($mergeType == 'array') {
                $currentItemsCollection[] = $previousOrderItem;
            } else {
                $currentItemsCollection->addItem($previousOrderItem);
            }
        }
        
        return $currentItemsCollection;
    }
}
