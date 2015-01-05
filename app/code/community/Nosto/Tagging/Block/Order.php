<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Nosto
 * @package     Nosto_Tagging
 * @copyright   Copyright (c) 2013 Nosto Solutions Ltd (http://www.nosto.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order tagging block.
 * Adds meta-data to the HTML document for successful orders.
 *
 * @category    Nosto
 * @package     Nosto_Tagging
 * @author      Nosto Solutions Ltd
 */
class Nosto_Tagging_Block_Order extends Mage_Checkout_Block_Success
{
    /**
     * Render order info as hidden meta data if the module is enabled for the current store.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('nosto_tagging')->isModuleEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Return the last placed order for the customer.
     *
     * @return Mage_Sales_Model_Order
     */
    public function getLastOrder()
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        return Mage::getModel('sales/order')->load($orderId);
    }

    /**
     * Returns an array of generic data objects for all ordered items.
     * The list includes possible discount and shipping cost as separate items.
     *
     * Structure:
     * array({
     *     productId: 1,
     *     quantity: 1,
     *     name: foo,
     *     price: 2.00
     * }, {...});
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return object[]
     */
    public function getOrderItems($order)
    {
        $items = array();

        /** @var $visibleItems Mage_Sales_Model_Order_Item[] */
        $visibleItems = $order->getAllVisibleItems();
        foreach ($visibleItems as $visibleItem) {
            $product = Mage::getModel('catalog/product')->load($visibleItem->getProductId());
            if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                if ((int)$product->getPriceType() === Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
                    continue;
                }
                $children = $visibleItem->getChildrenItems();
                foreach ($children as $child) {
                    $items[] = $this->_orderModelToItem($child);
                }
            } else {
                $items[] = $this->_orderModelToItem($visibleItem);
            }
        }

        if (!empty($items)) {
            $items = array_merge($items, $this->_getOrderSpecialItems($order));
        }

        return $items;
    }

    /**
     * Converts a order item model into a generic data object.
     *
     * @see Nosto_Tagging_Block_Order::getOrderItems()
     *
     * @param Mage_Sales_Model_Order_Item $model
     *
     * @return object
     */
    protected function _orderModelToItem($model)
    {
        return (object)array(
            'productId' => $this->getProductId($model),
            'quantity'  => (int)$model->getQtyOrdered(),
            'name'      => $model->getName(),
            'unitPrice' => $model->getPriceInclTax(),
        );
    }

    /**
     * Returns an array of generic data objects for discount and shipping from the order.
     *
     * @see Nosto_Tagging_Block_Order::getOrderItems()
     *
     * @param Mage_Sales_Model_Order $order
     *
     * @return object[]
     */
    protected function _getOrderSpecialItems($order)
    {
        $items = array();

        if ($discount = $order->getDiscountAmount()) {
            $items[] = (object)array(
                'productId' => -1,
                'quantity'  => 1,
                'name'      => 'Discount',
                'unitPrice' => $discount,
            );
        }

        if ($shippingInclTax = $order->getShippingInclTax()) {
            $items[] = (object)array(
                'productId' => -1,
                'quantity'  => 1,
                'name'      => 'Shipping and handling',
                'unitPrice' => $shippingInclTax,
            );
        }

        return $items;
    }

    /**
     * Returns the product id for a order item.
     * If the product type is "grouped", then return the grouped product's id and not the id of the actual product.
     *
     * @param Mage_Sales_Model_Order_Item $item
     *
     * @return int
     */
    public function getProductId($item)
    {
        switch ($item->getProductType()) {
            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                $info = $item->getProductOptionByCode('info_buyRequest');
                if ($info !== null && isset($info['super_product_config']['product_id'])) {
                    $productId = $info['super_product_config']['product_id'];
                } else {
                    $productId = $item->getProductId();
                }
                break;

            default:
                $productId = $item->getProductId();
                break;
        }

        return (int)$productId;
    }
}
