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

use Nosto_Tagging_Model_Meta_Cart_Builder as CartBuilder;
use Nosto_Tagging_Helper_Log as NostoLog;

/**
 * Meta data class which holds information about an order.
 * This is used during the order confirmation API request and the order
 * history export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Order_Vaimo_Klarna_Checkout extends Nosto_Tagging_Model_Meta_Order
{

    /**
     * Default order status for completed checkouts
     */
    const DEFAULT_ORDER_STATUS = 'checkout_complete';

    /**
     * Discount factor for the order
     * @var float
     */
    protected $_discountFactor;

    /**
     * Required fields for Klarna order
     *
     * @var array
     */
    public static $requiredFieldsForOrder = array(
        'completed_at',
        'status',
        'billing_address',
        'cart',
        'purchase_currency',
    );

    /**
     * Loads the order data from a Magento quote model.
     *
     *
     * @param Mage_Sales_Model_Quote $quote the order model.
     * @return bool
     * @throws Exception
     */
    public function loadDataFromQuote(Mage_Sales_Model_Quote $quote)
    {
        $vaimoKlarnaOrder = null;
        /** @noinspection PhpUndefinedMethodInspection */
        $checkoutId = $quote->getKlarnaCheckoutId();
        $this->setOrderNumber($checkoutId);
        $createdAt = \DateTime::createFromFormat(
            'Y-m-d H:i:s', $quote->getCreatedAt()
        );
        if ($createdAt instanceof \DateTime) {
            $this->setCreatedAt($createdAt);
        } else {
            $this->setCreatedAt(new \DateTime('now'));
        }

        $orderStatus = new Nosto_Object_Order_OrderStatus();
        $orderStatus->setCode(self::DEFAULT_ORDER_STATUS);
        $orderStatus->setLabel(self::DEFAULT_ORDER_STATUS);
        $this->setOrderStatus($orderStatus);

        $payment = $quote->getPayment();
        if ($payment instanceof Mage_Sales_Model_Quote_Payment
            && $payment->getMethod()
        ) {
            $this->setPaymentProvider($payment->getMethod());
        } else {
            $this->setPaymentProvider('unknown[from_vaimo_klarna_plugin]');
        }

        /* @var Vaimo_Klarna_Model_Klarnacheckout $klarna */
        $klarna = Mage::getModel('klarna/klarnacheckout');
        if ($klarna instanceof Vaimo_Klarna_Model_Klarnacheckout === false) {
            Mage::throwException('No Vaimo_Klarna_Model_Klarnacheckout found');
        }

        $klarna->setQuote($quote, Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);
        /** @noinspection PhpUndefinedMethodInspection */
        $vaimoKlarnaOrder = $klarna->getKlarnaOrderRaw($quote->getKlarnaCheckoutId());
        try {
            self::validateKlarnaOrder($vaimoKlarnaOrder);
        } catch (\Exception $e) {
            NostoLog::exception($e);
            return false;
        }

        $vaimoKlarnaBilling = $vaimoKlarnaOrder['billing_address'];
        $orderBuyer = new Nosto_Object_Order_Buyer();
        if (!empty($vaimoKlarnaBilling['given_name'])) {
            $orderBuyer->setFirstName($vaimoKlarnaBilling['given_name']);
        }

        if (!empty($vaimoKlarnaBilling['family_name'])) {
            $orderBuyer->setLastName($vaimoKlarnaBilling['family_name']);
        }

        if (!empty($vaimoKlarnaBilling['email'])) {
            $orderBuyer->setEmail($vaimoKlarnaBilling['email']);
        }

        $this->setCustomer($orderBuyer);
        try {
            $this->buildItemsFromQuote($quote);
        } catch (\Exception $e) {
            NostoLog::error(
                'Could not find klarnaCheckoutId from quote #%d. Error: %s',
                array($quote->getId(), $e->getMessage())
            );
        }

        return true;
    }

    /**
     * Builds item array from quote (items in cart)
     *
     * @param Mage_Sales_Model_Quote $quote
     */
    public function buildItemsFromQuote(Mage_Sales_Model_Quote $quote)
    {
        $discountFactor = $this->calcDiscountFactor($quote);
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        $currencyCode = $helper->getStore()->getCurrentCurrencyCode();
        /* @var Mage_Sales_Model_Quote_Item $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            $nostoItem = CartBuilder::buildItem($quoteItem, $currencyCode);
            if ($discountFactor && is_numeric($discountFactor)) {
                $itemPrice = round($quoteItem->getPriceInclTax(), 2) * $discountFactor;
            } else {
                $itemPrice = $quoteItem->getPriceInclTax();
            }

            $nostoItem->setPrice($itemPrice);
            $this->addPurchasedItems($nostoItem);
        }

        $appliedRuleIds = $quote->getAppliedRuleIds();
        if ($appliedRuleIds) {
            $ruleIds = explode(',', $quote->getAppliedRuleIds());
            if (is_array($ruleIds)) {
                foreach ($ruleIds as $ruleId) {
                    /* @var Mage_SalesRule_Model_Rule $discountRule */
                    /** @phan-suppress-next-line PhanTypeMismatchArgument */
                    $discountRule = Mage::getModel('salesrule/rule')->load($ruleId); // @codingStandardsIgnoreLine
                    $name = sprintf(
                        'Discount (%s)',
                        $discountRule->getName()
                    );
                    $discountItem = new Nosto_Object_Cart_LineItem();
                    $discountItem->loadSpecialItemData($name, 0, $currencyCode);
                    $this->addPurchasedItems($discountItem);
                }
            }
        }
    }

    /**
     * Calculates the discount factor
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return float|int
     */
    protected function calcDiscountFactor(Mage_Sales_Model_Quote $quote)
    {
        if (!$this->_discountFactor) {
            $totalPrice = $quote->getSubtotal();
            $discountedPrice = $quote->getSubtotalWithDiscount();
            $this->_discountFactor = $discountedPrice / $totalPrice;
        }

        return $this->_discountFactor;
    }

    /**
     * Loads data based on klarna checkout id
     *
     * @param string $klarnaCheckoutId
     * @throws Exception
     */
    public function loadOrderByKlarnaCheckoutId($klarnaCheckoutId)
    {
        /* @var Vaimo_Klarna_Helper_Data $klarnaHelper */
        $klarnaHelper = Mage::helper('klarna');
        if ($klarnaHelper instanceof Vaimo_Klarna_Helper_Data) {
            /* @var $quote Mage_Sales_Model_Quote */
            $quote = $klarnaHelper->findQuote($klarnaCheckoutId);
            if ($quote instanceof Mage_Sales_Model_Quote) {
                /* @var $order Mage_Sales_Model_Order */
                $salesOrderModel = Mage::getModel('sales/order');
                /** @noinspection PhpUndefinedMethodInspection */
                $order = $salesOrderModel->loadByAttribute(
                    'quote_id',
                    $quote->getId()
                );
                if ($order instanceof Mage_Sales_Model_Order
                    && $order->getId()
                ) {
                    $this->loadData($order);
                } else {
                    $this->loadDataFromQuote($quote);
                }
            }
        }
    }

    /**
     * Loads data from order
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     * @throws Nosto_NostoException
     */
    public function loadData(Mage_Sales_Model_Order $order)
    {
        /** @var Mage_Core_Model_Store $storeModel */
        $storeModel = Mage::getSingleton('core/store');
        $store = $storeModel->load($order->getStoreId());
        /* @var Mage_Sales_Model_Quote $quoteModel */
        $quoteModel = Mage::getModel('sales/quote');
        $quote = $quoteModel->setStore($store)->load($order->getQuoteId());
        parent::loadData($order);
        /** @noinspection PhpUndefinedMethodInspection */
        $klarnaCheckoutId = $quote->getKlarnaCheckoutId();
        if (empty($klarnaCheckoutId)) {
            /** @noinspection PhpUndefinedMethodInspection */
            NostoLog::error(
                'Could not find klarnaCheckoutId from quote #%d',
                array($order->quoteId())
            );
        } else {
            $this->setOrderNumber($klarnaCheckoutId);
        }

        return true;
    }

    /**
     * Validates Klarna entity
     *
     * @param $order
     * @return bool
     * @throws Exception
     */
    public static function validateKlarnaOrder($order)
    {
        return self::validateKlarnaEntity('Order', $order);
    }

    /**
     * Validates Klarna entity
     *
     * @param $type
     * @param $entity
     * @return bool
     * @throws Exception
     * @suppress PhanTypeArraySuspicious
     */
    public static function validateKlarnaEntity($type, $entity)
    {
        $rules = sprintf('requiredFieldsFor%s', ucfirst($type));
        $empty = false;
        /** @noinspection PhpVariableVariableInspection */
        foreach (self::$$rules as $field) {
            if (is_object($entity)) {
                if (get_class($entity) === 'Varien_Object') {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $val = $entity->get($field);
                    if (empty($val)) {
                        $empty = true;
                    }
                } elseif (isset($entity[$field])) {
                    $val = $entity[$field];
                    if (empty($val)) {
                        $empty = true;
                    }
                }
            } elseif (is_array($entity)) {
                if (empty($entity[$field])) {
                    $empty = true;
                }
            }

            if ($empty === true) {
                Mage::throwException(sprintf('Cannot create item - empty %s', $field));
            }
        }

        return true;
    }
}
