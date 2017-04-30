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
 * Class Yireo_RepeatedSales_Helper_OrderItem
 */
class Yireo_RepeatedSales_Helper_OrderItem
{
    /**
     * @var array
     */
    private $previousOrderIds;

    /**
     * @param Mage_Sales_Model_Order_Item $orderItem
     *
     * @return bool
     */
    public function isMatchableOrderItem(Mage_Sales_Model_Order_Item $orderItem)
    {
        $order = $orderItem->getOrder();
        if ($order->getState() !== Mage_Sales_Model_Order::STATE_COMPLETE) {
            return false;
        }

        if ($orderItem->getQtyCanceled() == $orderItem->getQtyOrdered()) {
            return false;
        }

        if ($orderItem->getQtyRefunded() == $orderItem->getQtyInvoiced()) {
            return false;
        }

        return true;
    }


    /**
     * @return bool
     */
    public function hasAnyPreviousOrder()
    {
        $previousOrderIds = $this->getPreviousOrderIds();
        if (empty($previousOrderIds)) {
            return false;
        }

        return true;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    public function isPreviouslyOrdered(Mage_Catalog_Model_Product $product)
    {
        try {
            $previousOrderItemsCollection = $this->getPreviousOrderItemCollection();
        } catch(Exception $e) {
            return true;
        }

        if (empty($previousOrderItemsCollection)) {
            return false;
        }

        foreach ($previousOrderItemsCollection as $previousOrderItem) {

            /** @var Mage_Sales_Model_Order_Item $previousOrderItem */
            if ($this->isMatchableOrderItem($previousOrderItem) === false) {
                continue;
            }

            $previousSku = $previousOrderItem->getSku();
            $sku = $product->getSku();

            if ($previousSku === $sku) {
                return true;
            }

            /** @todo: move this to an observer event */
            if ($this->matchPrefixSkus($sku, $previousSku)) {
                return true;
            }
        }

        return false;
    }

    /** @todo: move this to an observer event */
    private function matchPrefixSkus($sku, $previousSku)
    {
        if (!preg_match('/([a-zA-Z]+)([0-9]+)-([0-9]+)/', $sku, $matchSku)) {
            return false;
        }

        if (!preg_match('/([a-zA-Z]+)([0-9]+)-([0-9]+)/', $previousSku, $matchPreviousSku)) {
            return false;
        }

        if ($matchSku[2] == $matchPreviousSku[2]) {
            return true;
        }
    }

    /**
     * @return Varien_Data_Collection
     * @throws Exception
     */
    public function getPreviousOrderItemCollection()
    {
        $previousOrderIds = $this->getPreviousOrderIds();
        if (empty($previousOrderIds)) {
            throw new Exception('No previous order IDs found');
        }

        $previousOrderItemsCollection = Mage::getModel('sales/order_item')->getCollection()
            ->addFieldToFilter('order_id', $previousOrderIds);

        return $previousOrderItemsCollection;
    }

    /**
     * @return array|bool
     */
    private function getPreviousOrderIds()
    {
        if ($this->isCustomerLoggedIn() === false) {
            return [];
        }

        $customer = $this->getCustomer();
        if (!$customer->getId() > 0) {
            return [];
        }

        if (is_array($this->previousOrderIds)) {
            return $this->previousOrderIds;
        }

        $this->previousOrderIds = [];

        $previousOrdersCollection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addFieldToFilter('state', Mage_Sales_Model_Order::STATE_COMPLETE);

        foreach ($previousOrdersCollection as $previousOrder) {
            /** @var Mage_Sales_Model_Order $previousOrder */
            $this->previousOrderIds[] = $previousOrder->getId();
        }

        return $this->previousOrderIds;
    }

    /**
     * @return Mage_Customer_Model_Customer
     * @throws Exception
     */
    private function getCustomer()
    {
        $customer = Mage::registry('current_customer');
        if (!empty($customer)) {
            return $customer;
        }

        $customer = $this->getCustomerSession()->getCustomer();
        if (!empty($customer)) {
            return $customer;
        }

        throw new Exception('Unable to load current customer');
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        $customer = Mage::registry('current_customer');
        if (!empty($customer)) {
            return true;
        }

        return (bool)$this->getCustomerSession()->isLoggedIn();
    }

    /**
     * @return Mage_Customer_Model_Session
     */
    private function getCustomerSession()
    {
        /** @var Mage_Customer_Model_Session $session */
        $session = Mage::getSingleton('customer/session');
        return $session;
    }
}
