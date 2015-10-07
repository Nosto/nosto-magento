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
     * @var string the Magento order "real order ID" property.
     */
    protected $_externalOrderRef;

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
     * @var Nosto_Tagging_Model_Meta_Order_Buyer the user info of the buyer.
     */
    protected $_buyer;

    /**
     * @var Nosto_Tagging_Model_Meta_Order_Item[] the items in the order.
     */
    protected $_items = array();

    /**
     * @var Nosto_Tagging_Model_Meta_Order_Status the order status.
     */
    protected $_orderStatus;

    /**
     * @var Nosto_Tagging_Model_Meta_Order_Status[] list of order status history.
     */
    protected $_orderStatuses = array();

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_order');
    }

    /**
     * Loads the order info from a Magento order model.
     *
     * @param Mage_Sales_Model_Order $order the order model.
     */
    public function loadData(Mage_Sales_Model_Order $order)
    {
        $this->_orderNumber = $order->getId();
        $this->_externalOrderRef = $order->getRealOrderId();
        $this->_createdDate = $order->getCreatedAt();
        $this->_paymentProvider = $order->getPayment()->getMethod();

        $this->_orderStatus = Mage::getModel(
            'nosto_tagging/meta_order_status',
            array(
                'code' => $order->getStatus(),
                'label' => $order->getStatusLabel()
            )
        );

        foreach ($order->getAllStatusHistory() as $item) {
            /** @var Mage_Sales_Model_Order_Status_History $item */
            $this->_orderStatuses[] = Mage::getModel(
                'nosto_tagging/meta_order_status',
                array(
                    'code' => $item->getStatus(),
                    'label' => $item->getStatusLabel(),
                    'createdAt' => $item->getCreatedAt()
                )
            );
        }

        $this->_buyer = Mage::getModel(
            'nosto_tagging/meta_order_buyer',
            array(
                'firstName' => $order->getCustomerFirstname(),
                'lastName' => $order->getCustomerLastname(),
                'email' => $order->getCustomerEmail()
            )
        );

        foreach ($order->getAllVisibleItems() as $item) {
            /** @var $item Mage_Sales_Model_Order_Item */
            $this->_items[] = $this->buildItem($item, $order);
        }

        if ($this->includeSpecialItems) {
            if (($discount = $order->getDiscountAmount()) > 0) {
                /** @var Nosto_Tagging_Model_Meta_Order_Item $orderItem */
                $this->_items[] = Mage::getModel(
                    'nosto_tagging/meta_order_item',
                    array(
                        'productId' => -1,
                        'quantity' => 1,
                        'name' => 'Discount',
                        'unitPrice' => $discount,
                        'currencyCode' => $order->getOrderCurrencyCode()
                    )
                );
            }

            if (($shippingInclTax = $order->getShippingInclTax()) > 0) {
                /** @var Nosto_Tagging_Model_Meta_Order_Item $orderItem */
                $this->_items[] = Mage::getModel(
                    'nosto_tagging/meta_order_item',
                    array(
                        'productId' => -1,
                        'quantity' => 1,
                        'name' => 'Shipping and handling',
                        'unitPrice' => $shippingInclTax,
                        'currencyCode' => $order->getOrderCurrencyCode()
                    )
                );
            }
        }
    }

    /**
     * Builds a order items object form the Magento sales item.
     *
     * @param Mage_Sales_Model_Order_Item $item the sales item model.
     * @param Mage_Sales_Model_Order $order the order model.
     *
     * @return Nosto_Tagging_Model_Meta_Order_Item the built item.
     */
    protected function buildItem(Mage_Sales_Model_Order_Item $item, Mage_Sales_Model_Order $order)
    {
        return Mage::getModel(
            'nosto_tagging/meta_order_item',
            array(
                'productId' => (int)$this->buildItemProductId($item),
                'quantity' => (int)$item->getQtyOrdered(),
                'name' => $this->buildItemName($item),
                'unitPrice' => $item->getPriceInclTax(),
                'currencyCode' => $order->getOrderCurrencyCode()
            )
        );
    }

    /**
     * Returns the product id for a quote item.
     * Always try to find the "parent" product ID if the product is a child of
     * another product type. We do this because it is the parent product that
     * we tag on the product page, and the child does not always have it's own
     * product page. This is important because it is the tagged info on the
     * product page that is used to generate recommendations and email content.
     *
     * @param Mage_Sales_Model_Order_Item $item the sales item model.
     *
     * @return int
     */
    protected function buildItemProductId(Mage_Sales_Model_Order_Item $item)
    {
        $parent = $item->getProductOptionByCode('super_product_config');
        if (isset($parent['product_id'])) {
            return $parent['product_id'];
        } elseif ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            /** @var Mage_Catalog_Model_Product_Type_Configurable $model */
            $model = Mage::getModel('catalog/product_type_configurable');
            $parentIds = $model->getParentIdsByChild($item->getProductId());
            $attributes = $item->getBuyRequest()->getData('super_attribute');
            // If the product has a configurable parent, we assume we should tag
            // the parent. If there are many parent IDs, we are safer to tag the
            // products own ID.
            if (count($parentIds) === 1 && !empty($attributes)) {
                return $parentIds[0];
            }
        }
        return $item->getProductId();
    }

    /**
     * Returns the name for a sales item.
     * Configurable products will have their chosen options added to their name.
     * Bundle products will have their chosen child product names added.
     * Grouped products will have their parents name prepended.
     * All others will have their own name only.
     *
     * @param Mage_Sales_Model_Order_Item $item the sales item model.
     *
     * @return string
     */
    protected function buildItemName(Mage_Sales_Model_Order_Item $item)
    {
        $name = $item->getName();
        $optNames = array();

        if ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            /** @var Mage_Catalog_Model_Product_Type_Configurable $model */
            $model = Mage::getModel('catalog/product_type_configurable');
            $parentIds = $model->getParentIdsByChild($item->getProductId());
            // If the product has a configurable parent, we assume we should tag
            // the parent. If there are many parent IDs, we are safer to tag the
            // products own name alone.
            if (count($parentIds) === 1) {
                $attributes = $item->getBuyRequest()->getData('super_attribute');
                if (is_array($attributes)) {
                    foreach ($attributes as $id => $value) {
                        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
                        $attribute = Mage::getModel('catalog/resource_eav_attribute')
                            ->load($id);
                        $label = $attribute->getSource()->getOptionText($value);
                        if (!empty($label)) {
                            $optNames[] = $label;
                        }
                    }
                }
            }
        } elseif ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $opts = $item->getProductOptionByCode('attributes_info');
            if (is_array($opts)) {
                foreach ($opts as $opt) {
                    if (isset($opt['value']) && is_string($opt['value'])) {
                        $optNames[] = $opt['value'];
                    }
                }
            }
        } elseif ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $opts = $item->getProductOptionByCode('bundle_options');
            if (is_array($opts)) {
                foreach ($opts as $opt) {
                    if (isset($opt['value']) && is_array($opt['value'])) {
                        foreach ($opt['value'] as $val) {
                            $qty = '';
                            if (isset($val['qty']) && is_int($val['qty'])) {
                                $qty .= $val['qty'] . ' x ';
                            }
                            if (isset($val['title']) && is_string($val['title'])) {
                                $optNames[] = $qty . $val['title'];
                            }
                        }
                    }
                }
            }
        } elseif ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
            $config = $item->getProductOptionByCode('super_product_config');
            if (isset($config['product_id'])) {
                /** @var Mage_Catalog_Model_Product $parent */
                $parent = Mage::getModel('catalog/product')
                    ->load($config['product_id']);
                $parentName = $parent->getName();
                if (!empty($parentName)) {
                    $name = $parentName.' - '.$name;
                }
            }
        }

        if (!empty($optNames)) {
            $name .= ' (' . implode(', ', $optNames) . ')';
        }

        return $name;
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
     * Returns the Magento order "real order ID" property.
     *
     * @return string the order ref.
     */
    public function getExternalOrderRef()
    {
        return $this->_externalOrderRef;
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
     * Returns the order status model.
     *
     * @return NostoOrderStatusInterface the model.
     */
    public function getOrderStatus()
    {
        return $this->_orderStatus;
    }

    /**
     * Returns a list of order status history items.
     *
     * @return Nosto_Tagging_Model_Meta_Order_Status[] the list.
     */
    public function getOrderStatuses()
    {
        return $this->_orderStatuses;
    }
}
