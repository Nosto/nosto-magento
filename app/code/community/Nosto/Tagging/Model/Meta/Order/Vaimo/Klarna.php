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
 * Meta data class which holds information about an order.
 * This is used during the order confirmation API request and the order
 * history export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Order_Vaimo_Klarna extends Nosto_Tagging_Model_Meta_Order
{

    const VAIMO_KLARNA_PAYMENT_PROVIDER = 'vaimo_klarna';

    private $discountFactor;

    /**
     * Loads the order info from a Magento quote model.
     *
     * @throws NostoException
     *
     * @param Mage_Sales_Model_Quote $quote the order model.
     */
    public function loadDataFromQuote(Mage_Sales_Model_Quote $quote)
    {
        /* @var Vaimo_Klarna_Model_Klarnacheckout $klarna */
        $klarna = null;
        $vaimoKlarnaOrder = null;
        $klarna = Mage::getModel('klarna/klarnacheckout');
        if ($klarna instanceof Vaimo_Klarna_Model_Klarnacheckout === false) {
            Nosto::throwException('No Vaimo_Klarna_Model_Klarnacheckout found');
        }
        $cid = $quote->getKlarnaCheckoutId();
        $klarna->setQuote($quote, Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);
        $vaimoKlarnaOrder = $klarna->getKlarnaOrderRaw($quote->getKlarnaCheckoutId());
        $this->_orderNumber = $quote->getKlarnaCheckoutId();
        $this->_externalOrderRef = null;
        $this->_createdDate = $vaimoKlarnaOrder['completed_at'];
        $this->_orderStatus = Mage::getModel(
            'nosto_tagging/meta_order_status',
            array(
                'code' => $vaimoKlarnaOrder['status'],
                'label' => $vaimoKlarnaOrder['status']
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
        $buyer_attributes = array(
            'firstName' => '',
            'lastName' => '',
            'email' => ''
        );
        if(!empty($vaimoKlarnaOrder['billing_address'])) {
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
        }
        $this->_buyer = Mage::getModel(
            'nosto_tagging/meta_order_buyer',
            $buyer_attributes
        );

        if (
            !empty($vaimoKlarnaOrder['cart'])
            && is_array($vaimoKlarnaOrder['cart'])
            && !empty($vaimoKlarnaOrder['cart']['items'])
            && is_array($vaimoKlarnaOrder['cart']['items'])
        ) {
            foreach ($vaimoKlarnaOrder['cart']['items'] as $item) {
                try {
                    $this->_items[] = $this->buildKlarnaItem($item, $vaimoKlarnaOrder);
                } catch (NostoException $e) {
                    Mage::log(
                        sprintf(
                            'Failed to create Nosto item from Klarna. Error was %s',
                            $e->getMessage()
                        )
                    );
                }
            }
        }
    }

    private function getDiscountFactor(array $klarnaCart) {
        if (!$this->discountFactor) {
            $totalPrice = 0;
            $totalDiscount = 0;
            foreach ($klarnaCart['items'] as $item) {
                $price = $item['total_price_including_tax'];
                if($price > 0) {
                    $totalPrice += $price;
                } elseif($price < 1) {
                    $totalDiscount += $price;
                }
            }

            $discountedPrice = $totalPrice+$totalDiscount;
            $this->discountFactor = $discountedPrice / $totalPrice;
        }
        return $this->discountFactor;
    }

    public function buildKlarnaItem(array $klarnaItem, $klarnaOrder)
    {
        $discountedPercentage = $this->getDiscountFactor($klarnaOrder['cart']);

        if (empty($klarnaItem['reference'])) {
            Nosto::throwException('Empty product reference - cannot create item');
        }
        if (empty($klarnaItem['quantity'])) {
            Nosto::throwException('Empty product quantiy- cannot create item');
        }
        if (empty($klarnaItem['name'])) {
            Nosto::throwException('Empty product name - cannot create item');
        }
        if (empty($klarnaItem['total_price_including_tax'])) {
            Nosto::throwException('Empty product price including tax - cannot create item');
        }
        //ToDo - check that this check works
        if (!empty($klarnaOrder['purchase_currency'])) {
            $currencyCode = $klarnaOrder['purchase_currency'];
        } elseif (isset($klarnaOrder->purchase_currency)) {
            $currencyCode = $klarnaOrder['purchase_currency'];
        } else {
            Nosto::throwException('Empty currency - cannot create item');
        }

        $product = Mage::getModel('catalog/product');
        $id = Mage::getModel('catalog/product')->getResource()->getIdBySku($klarnaItem['reference']);
        if ($id) {
            $product->load($id);
        }

        if(!is_numeric($klarnaItem['unit_price'])) {
            $klarnaItem['unit_price'] = 0;
        }
        $productId = $product->getId();
        if (empty($productId) || !is_numeric($productId)) {
            $productId = -1;
        }

        $itemPrice = $klarnaItem['unit_price'];
        if($klarnaItem['unit_price'] > 0 && $discountedPercentage) {
            $itemPrice =  $klarnaItem['unit_price']*$discountedPercentage;
        }
        $itemArguments = array(
            'productId' => $productId,
            'quantity' => $klarnaItem['quantity'],
            'name' => $klarnaItem['name'],
            'unitPrice' => round($itemPrice/100,2),
            'currencyCode' => strtoupper($currencyCode)
        );
        $nostoItem = Mage::getModel(
            'nosto_tagging/meta_order_item',
            $itemArguments
        );
        return $nostoItem;
    }

    public function loadOrderByKlarnaId($klarnaCheckoutId)
    {
        /* @var Vaimo_Klarna_Helper_Data $klarna_helper */
        $klarna_helper = Mage::helper('klarna');
        if ($klarna_helper instanceof Vaimo_Klarna_Helper_Data) {
            /* @var $quote Mage_Sales_Model_Quote */
            $quote = $klarna_helper->findQuote($klarnaCheckoutId);
            if ($quote instanceof Mage_Sales_Model_Quote) {
                /* @var $order Mage_Sales_Model_Order */
                $salesOrderModel = Mage::getModel('sales/order');
                $order = $salesOrderModel->loadByAttribute(
                    'quote_id',
                    $quote->getId()
                );
                if (
                    $order instanceof Mage_Sales_Model_Order
                    && $order->getId()
                ) {
                    $this->loadFromOrder($order);
                } else {
                    $this->loadDataFromQuote($quote);
                }
            }
        }
    }

    public function loadFromOrder(Mage_Sales_Model_Order $order)
    {
        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        $klarnaCheckoutId = $quote->getKlarnaCheckoutId();
        parent::loadData($order);
        $this->_orderNumber = $klarnaCheckoutId;
    }
}
