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
 * @copyright Copyright (c) 2013-2016 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Data Transfer object representing an order.
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
     * @var string the external order reference number, i.e. "real order id".
     */
    protected $_externalOrderRef;

    /**
     * @var NostoDate the date when the order was placed.
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
     * @var array Nosto_Tagging_Model_Meta_Order_Status[] list of order status history.
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
     * Loads the Data Transfer Object.
     *
     * @param Mage_Sales_Model_Order $order the order model.
     */
    public function loadData(Mage_Sales_Model_Order $order)
    {
        $this->_orderNumber = $order->getId();
        $this->_externalOrderRef = $order->getRealOrderId();
        $this->_createdDate = new NostoDate(strtotime($order->getCreatedAt()));
        $this->_paymentProvider = new NostoOrderPaymentProvider($order->getPayment()->getMethod());

        if ($order->getStatus()) {
            $this->_orderStatus = Mage::getModel(
                'nosto_tagging/meta_order_status',
                array(
                    'code' => $order->getStatus(),
                    'label' => $order->getStatusLabel()
                )
            );
        }

        foreach ($order->getAllStatusHistory() as $item) {
            /** @var Mage_Sales_Model_Order_Status_History $item */
            if ($item->getStatus()) {
                $this->_orderStatuses[] = Mage::getModel(
                    'nosto_tagging/meta_order_status',
                    array(
                        'code' => $item->getStatus(),
                        'label' => $item->getStatusLabel(),
                        'createdAt' => new NostoDate(strtotime($item->getCreatedAt()))
                    )
                );
            }
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
            /** @var Mage_Sales_Model_Order_Item $item */
            $this->_items[] = $this->buildItem($item, $order);
        }

        if ($this->includeSpecialItems) {
            if (($discount = $order->getBaseDiscountAmount()) < 0) {
                $nostoPrice = new NostoPrice($discount);
                $nostoCurrencyCode = new NostoCurrencyCode($order->getBaseCurrencyCode());
                $this->_items[] = Mage::getModel(
                    'nosto_tagging/meta_order_item',
                    array(
                        'productId' => -1,
                        'quantity' => 1,
                        'name' => $this->buildDiscountRuleDescription($order),
                        'unitPrice' => $nostoPrice,
                        'currency' => $nostoCurrencyCode
                    )
                );
            }

            if (($shippingInclTax = $order->getBaseShippingInclTax()) > 0) {
                $nostoPrice = new NostoPrice($shippingInclTax);
                $nostoCurrencyCode = new NostoCurrencyCode($order->getBaseCurrencyCode());
                $this->_items[] = Mage::getModel(
                    'nosto_tagging/meta_order_item',
                    array(
                        'productId' => -1,
                        'quantity' => 1,
                        'name' => 'Shipping and handling',
                        'unitPrice' => $nostoPrice,
                        'currency' =>$nostoCurrencyCode
                    )
                );
            }
        }
    }

    protected function buildDiscountRuleDescription(Mage_Sales_Model_Order $order)
    {
        try {
            $appliedRules = array();
            foreach ($order->getAllVisibleItems() as $item) {
                $itemAppliedRules = $item->getAppliedRuleIds();
                if (empty($itemAppliedRules)) {
                    continue;
                }
                $ruleIds = explode(',', $item->getAppliedRuleIds());
                foreach ($ruleIds as $ruleId) {
                    $rule = Mage::getModel('salesrule/rule')->load($ruleId);
                    $appliedRules[$ruleId] = $rule->getName();
                }
            }
            if (count($appliedRules) == 0) {
                $appliedRules[] = 'unknown rule';
            }
            $discountTxt = sprintf(
                'Discount (%s)', implode(', ', $appliedRules)
            );
        } catch(\Exception $e) {
            $discountTxt = 'Discount (error)';
        }

        return $discountTxt;
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
        /* @var Nosto_Tagging_Helper_Price */
        $nostoPriceHelper = Mage::helper('nosto_tagging/price');
        $itemPrice = $nostoPriceHelper->getItemFinalPriceInclTax($item);
        $itemNostoPrice = new NostoPrice($itemPrice);
        $orderCurrencyCode = new NostoCurrencyCode($item->getOrder()->getOrderCurrencyCode());
        return Mage::getModel(
            'nosto_tagging/meta_order_item',
            array(
                'productId' => (int)$this->buildItemProductId($item),
                'quantity' => (int)$item->getQtyOrdered(),
                'name' => $this->buildItemName($item),
                'unitPrice' => $itemNostoPrice,
                'currency' => $orderCurrencyCode
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
        switch ($item->getProductType()) {
            case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE:
                return $this->buildSimpleProductName($item);

            case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                return $this->buildConfigurableProductName($item);

            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                return $this->buildBundleProductName($item);

            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                return $this->buildGroupedProductName($item);

            default:
                return $item->getName();
        }
    }

    /**
     * Returns the name for an order item representing a simple product.
     *
     * @param Mage_Sales_Model_Order_Item $item the sales item model.
     *
     * @return string
     */
    protected function buildSimpleProductName(Mage_Sales_Model_Order_Item $item)
    {
        $name = $item->getName();
        $nameOptions = array();

        /** @var Mage_Catalog_Model_Product_Type_Configurable $model */
        $model = Mage::getModel('catalog/product_type_configurable');
        $parentIds = $model->getParentIdsByChild($item->getProductId());
        // If the product has a configurable parent, we assume we should tag
        // the parent. If there are many parent IDs, we are safer to tag the
        // products own name alone.
        if (count($parentIds) === 1) {
            $attributes = $item->getBuyRequest()->getData('super_attribute');
            if (is_array($attributes) && count($attributes) > 0) {
                $nameOptions = $this->getAttributeLabels($attributes);
            }
        }

        return $this->applyProductNameOptions($name, $nameOptions);
    }

    /**
     * Returns the name for an order item representing a configurable product.
     *
     * @param Mage_Sales_Model_Order_Item $item the sales item model.
     *
     * @return string
     */
    protected function buildConfigurableProductName(Mage_Sales_Model_Order_Item $item)
    {
        $name = $item->getName();
        $nameOptions = array();

        $opts = $item->getProductOptionByCode('attributes_info');
        if (is_array($opts) && count($opts) > 0) {
            foreach ($opts as $opt) {
                if (isset($opt['value']) && is_string($opt['value'])) {
                    $nameOptions[] = $opt['value'];
                }
            }
        }

        return $this->applyProductNameOptions($name, $nameOptions);
    }

    /**
     * Returns the name for an order item representing a bundle product.
     *
     * @param Mage_Sales_Model_Order_Item $item the sales item model.
     *
     * @return string
     */
    protected function buildBundleProductName(Mage_Sales_Model_Order_Item $item)
    {
        $name = $item->getName();
        $nameOptions = array();

        $opts = $item->getProductOptionByCode('bundle_options');
        if (is_array($opts) && count($opts) > 0) {
            foreach ($opts as $opt) {
                if (isset($opt['value']) && is_array($opt['value'])) {
                    foreach ($opt['value'] as $val) {
                        $qty = '';
                        if (isset($val['qty']) && is_int($val['qty'])) {
                            $qty .= $val['qty'] . ' x ';
                        }
                        if (isset($val['title']) && is_string($val['title'])) {
                            $nameOptions[] = $qty . $val['title'];
                        }
                    }
                }
            }
        }

        return $this->applyProductNameOptions($name, $nameOptions);
    }

    /**
     * Returns the name for an order item representing a grouped product.
     *
     * @param Mage_Sales_Model_Order_Item $item the sales item model.
     *
     * @return string
     */
    protected function buildGroupedProductName(Mage_Sales_Model_Order_Item $item)
    {
        $name = $item->getName();

        $config = $item->getProductOptionByCode('super_product_config');
        if (isset($config['product_id'])) {
            /** @var Mage_Catalog_Model_Product $parent */
            $parent = Mage::getModel('catalog/product')
                ->load($config['product_id']);
            $parentName = $parent->getName();
            if (!empty($parentName)) {
                $name = $parentName . ' - ' . $name;
            }
        }

        return $name;
    }

    /**
     * Returns a list of attribute labels based on given attribute option map.
     *
     * The map must be passed with attribute id's as keys and the option id's
     * as values.
     *
     * @param array $attributes the attribute id map.
     *
     * @return array
     */
    protected function getAttributeLabels(array $attributes)
    {
        $labels = array();
        if (count($attributes) > 0) {
            /** @var Mage_Eav_Model_Entity_Attribute[] $collection */
            $collection = Mage::getModel('eav/entity_attribute')
                ->getCollection()
                ->addFieldToFilter(
                    'attribute_id',
                    array(
                        'in' => array_keys($attributes)
                    )
                );
            foreach ($collection as $attribute) {
                $optionId = $attributes[$attribute->getId()];
                if (!$attribute->getData('source_model')) {
                    $attribute->setData(
                        'source_model',
                        'eav/entity_attribute_source_table'
                    );
                }
                try {
                    $label = $attribute->getSource()->getOptionText($optionId);
                    if (!empty($label)) {
                        $labels[] = $label;
                    }
                } catch (Mage_Core_Exception $e) {
                    // If the source model cannot be found, just continue;
                    continue;
                }

            }
        }
        return $labels;
    }

    /**
     * Applies given options to the name.
     *
     * Format:
     *
     * "Product Name (Green, M)"
     *
     * @param string $name the name.
     * @param array  $options list of string values to apply as name option.
     *
     * @return string
     */
    protected function applyProductNameOptions($name, array $options)
    {
        if (!empty($options)) {
            $name .= ' (' . implode(', ', $options) . ')';
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
     * Returns an external order reference number.
     * This can help identify the order in Nosto's backend, while the above
     * order number is more of a "machine name" for the order.
     *
     * @return string|null the order reference or null if not used.
     */
    public function getExternalOrderRef()
    {
        return $this->_externalOrderRef;
    }

    /**
     * The date when the order was placed.
     *
     * @return NostoDate the creation date.
     */
    public function getCreatedDate()
    {
        return $this->_createdDate;
    }

    /**
     * The payment provider used for placing the order, formatted according to
     * "[provider name] [provider version]".
     *
     * @return NostoPaymentProvider the payment provider.
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
     * @return NostoOrderItemInterface[] the meta data models.
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
     * Returns a list of history order status models.
     * These are used in the order export to track the order funnel.
     *
     * @return NostoOrderStatusInterface[] the status models.
     */
    public function getOrderStatuses()
    {
        return $this->_orderStatuses;
    }

    /**
     * Returns an external order reference number. Backwards compatibility with SDK
     *
     * @return $this->getExternalOrderRef()
     */
    public function getExternalRef()
    {
        return $this->getExternalOrderRef();
    }

    /**
     * The buyer info of the user who placed the order. Backward compatibility with SDK
     *
     * @return $this->getBuyerInfo().
     */
    public function getBuyer()
    {
        return $this->getBuyerInfo();
    }

    /**
     * Items in this order.
     *
     * @return array an array if Nosto_Tagging_Model_Meta_Order_Item objects
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * The status of the order
     *
     * @return Nosto_Tagging_Model_Meta_Order_Status
     */
    public function getStatus()
    {
        return $this->_orderStatus;
    }

    /**
     * Status change history of the ordrer
     *
     * @return array A list of Nosto_Tagging_Model_Meta_Order_Status objects
     */
    public function getHistoryStatuses()
    {
        return $this->_orderStatuses;
    }
}
