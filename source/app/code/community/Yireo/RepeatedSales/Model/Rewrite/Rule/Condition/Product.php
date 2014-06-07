<?php
/**
 * RepeatedSales plugin for Magento 
 *
 * @package     Yireo_RepeatedSales
 * @author      Yireo (http://www.yireo.com/)
 * @copyright   Copyright (c) 2013 Yireo (http://www.yireo.com/)
 * @license     Open Software License
 */

class Yireo_RepeatedSales_Model_Rewrite_Rule_Condition_Product extends Mage_Rule_Model_Condition_Product_Abstract
{
    /**
     * Add special attributes
     *
     * @param array $attributes
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['quote_item_qty'] = Mage::helper('salesrule')->__('Quantity in cart');
        $attributes['quote_item_price'] = Mage::helper('salesrule')->__('Price in cart');
        $attributes['quote_item_row_total'] = Mage::helper('salesrule')->__('Row total in cart');
    }

    /**
     * Override of original Magento method
     *
     * @param Varien_Object $object Quote
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $object->getProduct();
        if (!($product instanceof Mage_Catalog_Model_Product)) {
            $product = Mage::getModel('catalog/product')->load($object->getProductId());
        }

        $product
            ->setQuoteItemQty($object->getQty())
            ->setQuoteItemPrice($object->getPrice()) // possible bug: need to use $object->getBasePrice()
            ->setQuoteItemRowTotal($object->getBaseRowTotal());

        $product->setPreviousOrder($this->isPreviouslyOrdered($product));

        $valid = parent::validate($product);
        if (!$valid && $product->getTypeId() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            $children = $object->getChildren();
            $valid = $children && $this->validate($children[0]);
        }

        return $valid;
    }

    protected function isPreviouslyOrdered($product)
    {
        if(Mage::getSingleton('customer/session')->isLoggedIn() == false) {
            return false;
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
            return false;
        }

        $previousOrderItemsCollection = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToFilter('order_id', $previousOrderIds)
        ;

        foreach($previousOrderItemsCollection as $previousOrderItem) {

            if($previousOrderItem->getQtyCanceled() == $previousOrderItem->getQtyOrdered()) {
                continue;
            }

            if($previousOrderItem->getQtyRefunded() == $previousOrderItem->getQtyInvoiced()) {
                continue;
            }

            $previousSku = $previousOrderItem->getSku();
            $sku = $product->getSku();
            if($previousSku == $sku) {
                return true;
            }

            /* @todo: move this to an observer event */
            if(preg_match('/([a-zA-Z]+)([0-9]+)-([0-9]+)/', $sku, $matchSku) 
                && preg_match('/([a-zA-Z]+)([0-9]+)-([0-9]+)/', $previousSku, $matchPreviousSku)) {
                if($matchSku[2] == $matchPreviousSku[2]) {
                    return true;
                }
            }
        }
        
        return false;
    }
}
