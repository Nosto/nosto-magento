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
 * @copyright Copyright (c) 2013-2017 Nosto Solutions Ltd (http://www.nosto.com)
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
class Nosto_Tagging_Model_Meta_Order extends NostoOrder
{
    /**
     * Loads the order info from a Magento order model.
     *
     * @param Mage_Sales_Model_Order $order the order model.
     */
    public function loadData(Mage_Sales_Model_Order $order)
    {
        $this->setOrderNumber($order->getId());
        $this->setExternalOrderRef($order->getRealOrderId());
        $this->setCreatedDate($order->getCreatedAt());
        $payment = $order->getPayment();
        if (is_object($payment)) {
            $this->setPaymentProvider($payment->getMethod());
        }
        if (empty($this->_paymentProvider)) {
            $this->setPaymentProvider('unknown');
        }

        if ($order->getStatus()) {
            $orderStatus = new NostoOrderStatus();
            $orderStatus->setCode($order->getStatus());
            $orderStatus->setLabel($order->getStatusLabel());
            $this->setOrderStatus($orderStatus);
        }

        foreach ($order->getAllStatusHistory() as $status) {
            /** @var Mage_Sales_Model_Order_Status_History $status */
            if ($status->getStatus()) {
                $orderStatus = new NostoOrderStatus();
                $orderStatus->setCode($status->getStatus());
                $orderStatus->setLabel($status->getStatusLabel());
                $orderStatus->setDate($status->getCreatedAt());
                $this->addOrderStatus($orderStatus);
            }
        }

        $orderBuyer = new NostoOrderBuyer();
        $orderBuyer->setFirstName($order->getCustomerFirstname());
        $orderBuyer->setLastName($order->getCustomerLastname());
        $orderBuyer->setEmail($order->getCustomerEmail());
        $this->setCustomer($orderBuyer);

        /** @var Mage_Sales_Model_Order_Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            switch ($item->getProductType()) {
                case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE:
                    /** @var Nosto_Tagging_Model_Meta_Order_Item_Simple $simpleItem */
                    $simpleItem = Mage::getModel('nosto_tagging/meta_order_item_simple');
                    $simpleItem->loadData($item, $order->getOrderCurrencyCode());
                    $this->addPurchasedItems($simpleItem);
                    break;

                case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                    /** @var Nosto_Tagging_Model_Meta_Order_Item_Configurable $configurableItem */
                    $configurableItem = Mage::getModel('nosto_tagging/meta_order_item_simple');
                    $configurableItem->loadData($item, $order->getOrderCurrencyCode());
                    $this->addPurchasedItems($configurableItem);
                    break;

                case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                    /** @var Nosto_Tagging_Model_Meta_Order_Item_Grouped $groupedItem */
                    $groupedItem = Mage::getModel('nosto_tagging/meta_order_item_grouped');
                    $groupedItem->loadData($item, $order->getOrderCurrencyCode());
                    $this->addPurchasedItems($groupedItem);
                    break;

                case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                    /** @var Nosto_Tagging_Model_Meta_Order_Item_Bundled $bundledItem */
                    $bundledItem = Mage::getModel('nosto_tagging/meta_order_item_bundled');
                    $bundledItem->loadData($item, $order->getOrderCurrencyCode());
                    $this->addPurchasedItems($bundledItem);
                    break;
            }
        }

        if (($discountAmount = $order->getDiscountAmount()) < 0) {
            $discountItem = new NostoLineItem();
            $discountName = $this->buildDiscountRuleDescription($order);
            $discountItem->loadSpecialItemData($discountName, $discountAmount, $order->getOrderCurrencyCode());
            $this->addPurchasedItems($discountItem);
        }

        if (($shippingAmount = $order->getShippingInclTax()) > 0) {
            $shippingItem = new NostoLineItem();
            $shippingName = 'Shipping and handling';
            $shippingItem->loadSpecialItemData($shippingName, $shippingAmount, $order->getOrderCurrencyCode());
            $this->addPurchasedItems($shippingItem);
        }
    }

    /**
     * Generates a textual description of the applied discount rules
     *
     * @param Mage_Sales_Model_Order $order
     * @return string discount description
     */
    protected function buildDiscountRuleDescription(Mage_Sales_Model_Order $order)
    {
        try {
            $appliedRules = array();
            foreach ($order->getAllVisibleItems() as $item) {
                /** @var Mage_Sales_Model_Order_Item $item */
                $itemAppliedRules = $item->getAppliedRuleIds();
                if (empty($itemAppliedRules)) {
                    continue;
                }
                $ruleIds = explode(',', $item->getAppliedRuleIds());
                foreach ($ruleIds as $ruleId) {
                    /** @var Mage_SalesRule_Model_Rule $rule */
                    $rule = Mage::getModel('salesrule/rule')->load($ruleId);
                    $appliedRules[$ruleId] = $rule->getName();
                }
            }
            if (empty($appliedRules)) {
                $appliedRules[] = 'unknown rule';
            }
            $discountTxt = sprintf(
                'Discount (%s)', implode(', ', $appliedRules)
            );
        } catch (\Exception $e) {
            $discountTxt = 'Discount (error)';
        }

        return $discountTxt;
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
            if (!empty($parentIds) === 1 && !empty($attributes)) {
                return $parentIds[0];
            }
        }
        return $item->getProductId();
    }
}
