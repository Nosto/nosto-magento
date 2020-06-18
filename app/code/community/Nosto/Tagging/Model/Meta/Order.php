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
 * @copyright Copyright (c) 2013-2020 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Nosto_Tagging_Model_Meta_Order_Builder as OrderBuilder;

/**
 * Meta data class which holds information about an order.
 * This is used during the order confirmation API request and the order
 * history export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Order extends Nosto_Object_Order_Order
{
    /**
     * Loads the order info from a Magento order model.
     *
     * @param Mage_Sales_Model_Order $order the order model.
     * @return bool
     * @throws Nosto_NostoException
     */
    public function loadData(Mage_Sales_Model_Order $order)
    {
        $this->setOrderNumber($order->getId());
        $this->setExternalOrderRef($order->getRealOrderId());
        $createdAt = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            $order->getCreatedAt()
        );
        if ($createdAt instanceof DateTimeInterface) {
            $this->setCreatedAt(
                $createdAt
            );
        }

        $payment = $order->getPayment();
        $this->setPaymentProvider('unknown');
        if (is_object($payment)) {
            $this->setPaymentProvider($payment->getMethod());
        }

        if ($order->getStatus()) {
            $orderStatus = new Nosto_Object_Order_OrderStatus();
            $orderStatus->setCode($order->getStatus());
            $orderStatus->setLabel($order->getStatusLabel());
            $this->setOrderStatus($orderStatus);
        }

        foreach ($order->getAllStatusHistory() as $status) {
            /** @var Mage_Sales_Model_Order_Status_History $status */
            if ($status->getStatus()) {
                /** @var Nosto_Tagging_Model_Meta_Order_Status $orderStatus */
                $orderStatus = Mage::getModel('nosto_tagging/meta_order_status');
                $orderStatus->loadData($status);
                /** @phan-suppress-next-line PhanTypeMismatchArgument */
                $this->addOrderStatus($orderStatus);
            }
        }

        /** @var Nosto_Tagging_Model_Meta_Order_Buyer $orderBuyer */
        $orderBuyer = Mage::getModel('nosto_tagging/meta_order_buyer');
        $orderBuyer->loadData($order);
        /** @phan-suppress-next-line PhanTypeMismatchArgument */
        $this->setCustomer($orderBuyer);

        /** @var Mage_Sales_Model_Order_Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            $nostoItem = OrderBuilder::buildItem($item, $order);
            if ($nostoItem instanceof Nosto_Object_Cart_LineItem) {
                $this->addPurchasedItems($nostoItem);
            }
        }

        if (($discountAmount = $order->getDiscountAmount()) < 0) {
            $discountItem = new Nosto_Object_Cart_LineItem();
            $discountName = $this->buildDiscountRuleDescription($order);
            $discountItem->loadSpecialItemData($discountName, $discountAmount, $order->getOrderCurrencyCode());
            $this->addPurchasedItems($discountItem);
        }

        if (($shippingAmount = $order->getShippingInclTax()) > 0) {
            $shippingItem = new Nosto_Object_Cart_LineItem();
            $shippingName = 'Shipping and handling';
            $shippingItem->loadSpecialItemData($shippingName, $shippingAmount, $order->getOrderCurrencyCode());
            $this->addPurchasedItems($shippingItem);
        }

        Mage::dispatchEvent(
            Nosto_Tagging_Helper_Event::EVENT_NOSTO_ORDER_LOAD_AFTER,
            array(
                'order' => $this,
                'magentoOrder' => $order
            )
        );

        return true;
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
                    /** @phan-suppress-next-line PhanTypeMismatchArgument */
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
}
