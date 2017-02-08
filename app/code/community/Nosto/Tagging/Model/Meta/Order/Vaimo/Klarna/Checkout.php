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
    protected $discountFactor;

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
     * @throws NostoException
     * @return bool
     * @param Mage_Sales_Model_Quote $quote the order model.
     */
    public function loadDataFromQuote(Mage_Sales_Model_Quote $quote)
    {
        $vaimoKlarnaOrder = null;
        /** @noinspection PhpUndefinedMethodInspection */
        $checkoutId = $quote->getKlarnaCheckoutId();
        $this->_orderNumber = $checkoutId;
        $this->_externalOrderRef = null;
        $this->_createdDate = $quote->getCreatedAt();
        $this->_orderStatus = Mage::getModel(
            'nosto_tagging/meta_order_status',
            array(
                'code' => self::DEFAULT_ORDER_STATUS,
                'label' => self::DEFAULT_ORDER_STATUS
            )
        );

        $payment = $quote->getPayment();
        if (
            $payment instanceof Mage_Sales_Model_Quote_Payment
            && $payment->getMethod()
        ) {
            $this->_paymentProvider = $payment->getMethod();
        } else {
            $this->_paymentProvider = 'unknown[from_vaimo_klarna_plugin]';
        }
        /* @var Vaimo_Klarna_Model_Klarnacheckout $klarna */
        $klarna = Mage::getModel('klarna/klarnacheckout');
        if ($klarna instanceof Vaimo_Klarna_Model_Klarnacheckout === false) {
            Nosto::throwException('No Vaimo_Klarna_Model_Klarnacheckout found');
        }
        $buyer_attributes = array(
            'firstName' => '',
            'lastName' => '',
            'email' => ''
        );
        $klarna->setQuote($quote, Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);
        /** @noinspection PhpUndefinedMethodInspection */
        $vaimoKlarnaOrder = $klarna->getKlarnaOrderRaw($quote->getKlarnaCheckoutId());
        try {
            self::validateKlarnaOrder($vaimoKlarnaOrder);
        } catch (NostoException $e) {
            Mage::log(
                sprintf(
                    'Failed to validate VaimoKlarnaOrder. Error was %s',
                    $e->getMessage()
                )
            );

            return false;
        }
        $vaimoKlarnaBilling = $vaimoKlarnaOrder['billing_address'];
        if (!empty($vaimoKlarnaBilling['given_name'])) {
            $buyer_attributes['firstName'] = $vaimoKlarnaBilling['given_name'];
        }
        if (!empty($vaimoKlarnaBilling['family_name'])) {
            $buyer_attributes['lastName'] = $vaimoKlarnaBilling['family_name'];
        }
        if (!empty($vaimoKlarnaBilling['email'])) {
            $buyer_attributes['email'] = $vaimoKlarnaBilling['email'];
        }
        $this->_buyer = Mage::getModel(
            'nosto_tagging/meta_order_buyer',
            $buyer_attributes
        );
        try {
            $this->buildItemsFromQuote($quote);
        } catch (Exception $e) {
            Mage::log(
                sprintf(
                    'Could not find klarnaCheckoutId from quote #%d. Error: %s',
                    $quote->getId(),
                    $e->getMessage()
                ),
                Zend_Log::ERR,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
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
        $discountFactor = $this->getDiscountFactor($quote);
        /* @var Mage_Sales_Model_Quote_Item $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            if ($discountFactor && is_numeric($discountFactor)) {
                $itemPrice = round($quoteItem->getPriceInclTax(),2)*$discountFactor;
            } else {
                $itemPrice = $quoteItem->getPriceInclTax();
            }
            $itemArguments = array(
                'productId' => $quoteItem->getProductId(),
                'quantity' => $quoteItem->getQty(),
                'name' => $quoteItem->getName(),
                'unitPrice' => $itemPrice,
                'currencyCode' => strtoupper($quote->getQuoteCurrencyCode())
            );
            $nostoItem = Mage::getModel(
                'nosto_tagging/meta_order_item',
                $itemArguments
            );
            $this->_items[] = $nostoItem;
        }
        if ($this->includeSpecialItems) {
            $appliedRuleIds = $quote->getAppliedRuleIds();
            if ($appliedRuleIds) {
                $ruleIds = explode(',',$quote->getAppliedRuleIds());
                if (is_array($ruleIds)) {
                    foreach ($ruleIds as $ruleId) {
                        /* @var Mage_SalesRule_Model_Rule $discountRule */
                        $discountRule = Mage::getModel('salesrule/rule')->load($ruleId);
                        $name = sprintf(
                            'Discount (%s)',
                            $discountRule->getName()
                        );
                        $itemArguments = array(
                            'productId' => -1,
                            'quantity' => 1,
                            'name' => $name,
                            'unitPrice' => 0,
                            'currencyCode' => strtoupper($quote->getQuoteCurrencyCode())
                        );
                        $nostoItem = Mage::getModel(
                            'nosto_tagging/meta_order_item',
                            $itemArguments
                        );
                        $this->_items[] = $nostoItem;
                    }
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
    protected function getDiscountFactor(Mage_Sales_Model_Quote $quote) {
        if (!$this->discountFactor) {
            $totalPrice = $quote->getSubtotal();
            $discountedPrice = $quote->getSubtotalWithDiscount();
            $this->discountFactor = $discountedPrice / $totalPrice;
        }

        return $this->discountFactor;
    }

    /**
     * Loads data based on klarna checkout id
     *
     * @param string $klarnaCheckoutId
     */
    public function loadOrderByKlarnaCheckoutId($klarnaCheckoutId)
    {
        /* @var Vaimo_Klarna_Helper_Data $klarna_helper */
        $klarna_helper = Mage::helper('klarna');
        if ($klarna_helper instanceof Vaimo_Klarna_Helper_Data) {
            /* @var $quote Mage_Sales_Model_Quote */
            $quote = $klarna_helper->findQuote($klarnaCheckoutId);
            if ($quote instanceof Mage_Sales_Model_Quote) {
                /* @var $order Mage_Sales_Model_Order */
                $salesOrderModel = Mage::getModel('sales/order');
                /** @noinspection PhpUndefinedMethodInspection */
                $order = $salesOrderModel->loadByAttribute(
                    'quote_id',
                    $quote->getId()
                );
                if (
                    $order instanceof Mage_Sales_Model_Order
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
     */
    public function loadData(Mage_Sales_Model_Order $order)
    {
        $store = Mage::getSingleton('core/store')->load($order->store_id);
        /* @var Mage_Sales_Model_Quote $quoteModel */
        $quoteModel = Mage::getModel('sales/quote');
        $quote = $quoteModel->setStore($store)->load($order->getQuoteId());
        parent::loadData($order);
        /** @noinspection PhpUndefinedMethodInspection */
        $klarnaCheckoutId = $quote->getKlarnaCheckoutId();
        if (empty($klarnaCheckoutId)) {
            /** @noinspection PhpUndefinedMethodInspection */
            Mage::log(
                sprintf(
                    'Could not find klarnaCheckoutId from quote #%d',
                    $order->quoteId()
                ),
                Zend_Log::ERR,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );
        } else {
            $this->_orderNumber = $klarnaCheckoutId;
        }
    }

    /**
     * Validates Klarna entity
     *
     * @param $order
     * @return bool
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
     */
    public static function validateKlarnaEntity($type, $entity)
    {
        $rules = sprintf('requiredFieldsFor%s', ucfirst($type));
        $empty = false;
        foreach (self::$$rules as $field) {
            if (
                is_object($entity)
            ) {
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
                Nosto::throwException(
                    sprintf(
                        'Cannot create item - empty %s',
                        $field
                    )
                );
            }
        }

        return true;
    }
}
