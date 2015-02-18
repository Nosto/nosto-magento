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
 * @category  Nosto
 * @package   Nosto_Tagging
 * @author    Nosto Solutions Ltd <magento@nosto.com>
 * @copyright Copyright (c) 2013-2015 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Meta data class which holds information about an order.
 * This is used during the order confirmation API request and the order
 * history export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Order extends Mage_Core_Model_Abstract implements NostoOrderInterface
{
    /**
     * @var bool if the special items, e.g. shipping cost, discounts, should be
     * included in the `$_items` list.
     */
    public $includeSpecialItems = true;

    /**
     * @var string|int the unique order number identifying the order.
     */
    protected $_orderNumber;

    /**
     * @var string the date when the order was placed.
     */
    protected $_createdDate;

    /**
     * @var string the payment provider used for order.
     *
     * Formatted according to "[provider name] [provider version]".
     */
    protected $_paymentProvider;

    /**
     * @var Nosto_Tagging_Model_Meta_Order_Buyer The user info of the buyer.
     */
    protected $_buyer;

    /**
     * @var Nosto_Tagging_Model_Meta_Order_Item[] the items in the order.
     */
    protected $_items = array();

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_order');
    }

    /**
     * The unique order number identifying the order.
     *
     * @return string|int the order number.
     */
    public function getOrderNumber()
    {
        return $this->_orderNumber;
    }

    /**
     * The date when the order was placed.
     *
     * @return string the creation date.
     */
    public function getCreatedDate()
    {
        return $this->_createdDate;
    }

    /**
     * The payment provider used for placing the order, formatted according to
     * "[provider name] [provider version]".
     *
     * @return string the payment provider.
     */
    public function getPaymentProvider()
    {
        return $this->_paymentProvider;
    }

    /**
     * The buyer info of the user who placed the order.
     *
     * @return NostoOrderBuyerInterface the meta data model.
     */
    public function getBuyerInfo()
    {
        return $this->_buyer;
    }

    /**
     * The purchased items which were included in the order.
     *
     * @return NostoOrderPurchasedItemInterface[] the meta data models.
     */
    public function getPurchasedItems()
    {
        return $this->_items;
    }

    /**
     * Loads the order info from a Magento order model.
     *
     * @param Mage_Sales_Model_Order $order the order model.
     */
    public function loadData(Mage_Sales_Model_Order $order)
    {
        $this->_orderNumber = $order->getId();
        $this->_createdDate = $order->getCreatedAt();

        $method = $order->getPayment()->getMethodInstance();
        $this->_paymentProvider = $method->getCode();

        $this->_buyer = new Nosto_Tagging_Model_Meta_Order_Buyer();
        $this->_buyer->loadData($order);

        /** @var $item Mage_Sales_Model_Order_Item */
        foreach ($order->getAllVisibleItems() as $item) {
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')
                ->load($item->getProductId());
            if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                if ((int)$product->getPriceType() === Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
                    continue;
                }
                foreach ($item->getChildrenItems() as $child) {
                    $orderItem = new Nosto_Tagging_Model_Meta_Order_Item();
                    $orderItem->loadData($child);
                    $this->_items[] = $orderItem;
                }
            } else {
                $orderItem = new Nosto_Tagging_Model_Meta_Order_Item();
                $orderItem->loadData($item);
                $this->_items[] = $orderItem;
            }
        }

        if ($this->includeSpecialItems) {
            if (($discount = $order->getDiscountAmount()) > 0) {
                $orderItem = new Nosto_Tagging_Model_Meta_Order_Item();
                $orderItem->setProductId(-1);
                $orderItem->setQuantity(1);
                $orderItem->setName('Discount');
                $orderItem->setUnitPrice($discount);
                $orderItem->setCurrencyCode($order->getOrderCurrencyCode());
                $this->_items[] = $orderItem;
            }

            if (($shippingInclTax = $order->getShippingInclTax()) > 0) {
                $orderItem = new Nosto_Tagging_Model_Meta_Order_Item();
                $orderItem->setProductId(-1);
                $orderItem->setQuantity(1);
                $orderItem->setName('Shipping and handling');
                $orderItem->setUnitPrice($shippingInclTax);
                $orderItem->setCurrencyCode($order->getOrderCurrencyCode());
                $this->_items[] = $orderItem;
            }
        }
    }
}
