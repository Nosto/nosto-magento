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
            $purchasedItem = new NostoLineItem();
            /* @var Nosto_Tagging_Helper_Price $nostoPriceHelper */
            $nostoPriceHelper = Mage::helper('nosto_tagging/price');
            $purchasedItem->setProductId($this->buildItemProductId($item));
            $purchasedItem->setQuantity((int)$item->getQtyOrdered());
            $purchasedItem->setName($this->buildItemName($item));
            $purchasedItem->setPrice($nostoPriceHelper->getItemFinalPriceInclTax($item));
            $purchasedItem->setPriceCurrencyCode($order->getOrderCurrencyCode());
            $this->addPurchasedItems($purchasedItem);
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
            if (!empty($parentIds)) {
                $attributes = $item->getBuyRequest()->getData('super_attribute');
                if (is_array($attributes)) {
                    foreach ($attributes as $id => $value) {
                        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
                        $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($id);
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
                    $name = $parentName . ' - ' . $name;
                }
            }
        }

        $name .= !empty($optNames) ? ' (' . implode(', ', $optNames) . ')' : '';

        return $name;
    }
}
