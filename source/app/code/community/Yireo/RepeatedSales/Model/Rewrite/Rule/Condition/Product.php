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
 * Class Yireo_RepeatedSales_Model_Rewrite_Rule_Condition_Product
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
     * @param Varien_Object $object
     *
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $this->getProductFromObject($object);

        $previousOrder = $this->getOrderItemHelper()->isPreviouslyOrdered($product);
        $product->setData('previous_order', (int) $previousOrder);

        $anyPreviousOrder = $this->getOrderItemHelper()->hasAnyPreviousOrder();
        $product->setData('any_previous_order', (int) $anyPreviousOrder);

        //$this->log('attribute: ' . $this->getAttribute().' = ('.$object->getSku().')' . $object->getData($this->getAttribute()));

        $valid = parent::validate($product);

        if ($valid === true) {
            return $valid;
        }

        if ($product->getTypeId() !== Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            return false;
        }

        if (!$children = $object->getChildren()) {
            return false;
        }

        return $this->validate($children[0]);
    }

    /**
     * @param object $object
     *
     * @return Mage_Core_Model_Abstract
     */
    private function getProductFromObject($object)
    {
        $product = $object->getProduct();
        if (!($product instanceof Mage_Catalog_Model_Product)) {
            $product = Mage::getModel('catalog/product')->load($object->getProductId());
        }

        $product
            ->setQuoteItemQty($object->getQty())
            ->setQuoteItemPrice($object->getBasePrice())
            ->setQuoteItemRowTotal($object->getBaseRowTotal());

        return $product;
    }

    /**
     * @return Yireo_RepeatedSales_Helper_OrderItem
     */
    private function getOrderItemHelper()
    {
        return Mage::helper('repeatedsales/orderItem');
    }

    private function log($message)
    {
        Mage::helper('repeatedsales')->debug($message);
    }
}
