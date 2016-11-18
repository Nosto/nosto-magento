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
        $klarna = Mage::getModel('klarna/klarnacheckout');
        if ($klarna instanceof Vaimo_Klarna_Model_Klarnacheckout === false) {
            Nosto::throwException('No Vaimo_Klarna_Model_Klarnacheckout found');
        }

        $this->_orderNumber = $quote->getKlarnaCheckoutId();

        $klarna->setQuote($quote, Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);
        $vaimoOrder = $klarna->getKlarnaOrderRaw($quote->getKlarnaCheckoutId());
        $this->_externalOrderRef = null;
        $this->_createdDate = $vaimoOrder->completed_at;
        $this->_orderStatus = Mage::getModel(
            'nosto_tagging/meta_order_status',
            array(
                'code' => $vaimoOrder->status,
                'label' => $vaimoOrder->status
            )
        );
        if ($quote->getKlarnaCheckoutId()) {
            $this->_paymentProvider = self::VAIMO_KLARNA_PAYMENT_PROVIDER;
        } else {
            $this->_paymentProvider = 'unknown[from_vaimo_klarna_plugin]';
        }
        $buyer_attributes = array(
            'firstName' => '',
            'lastName' => '',
            'email' => ''
        );
        if (!empty($vaimoOrder->billing_address['given_name'])) {
            $buyer_attributes['firstName'] = $vaimoOrder->billing_address['given_name'];
        }
        if (!empty($vaimoOrder->billing_address['family_name'])) {
            $buyer_attributes['lastName'] = $vaimoOrder->billing_address['family_name'];
        }
        if (!empty($vaimoOrder->billing_address['email'])) {
            $buyer_attributes['email'] = $vaimoOrder->billing_address['email'];
        }
        $this->_buyer = Mage::getModel(
            'nosto_tagging/meta_order_buyer',
            $buyer_attributes
        );
        if (
            !empty($vaimoOrder['cart'])
            && is_array($vaimoOrder['cart'])
            && !empty($vaimoOrder['cart']['items'])
            && is_array($vaimoOrder['cart']['items'])
        ) {
            foreach ($vaimoOrder['cart']['items'] as $item) {
                try {
                    $this->_items[] = $this->buildKlarnaItem($item, $vaimoOrder);
                } catch (NostoException $e) {
                    Mage::log(
                        sprintf(
                            'Failed to create Nosto item from Klarna. Error was %s',
                            $e->getMessage()
                        )
                    );
                }
            }
            if ($this->includeSpecialItems) {
                //ToDo - add discounts

                //ToDo - add shipping
            }
        }
    }

    public function buildKlarnaItem(array $klarnaItem, $klarnaOrder)
    {
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
//        if (!$klarnaOrder->getPurchaseCurrency()) {
//            Nosto::throwException('Empty currency - cannot create item');
//        }

        $product = Mage::getModel('catalog/product');
        $id = Mage::getModel('catalog/product')->getResource()->getIdBySku($klarnaItem['reference']);
        if ($id) {
            $product->load($id);
        }
        $itemArguments = array(
            'productId' => $product->getId(),
            'quantity' => $klarnaItem['quantity'],
            'name' => $klarnaItem['name'],
            'unitPrice' => $klarnaItem['unit_price'],
            'currencyCode' => strtoupper($klarnaOrder->purchase_currency)
        );
        $this->_items[] = Mage::getModel(
            'nosto_tagging/meta_order_item',
            $itemArguments
        );
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
                    parent::loadData($order);
                } else {
                    $this->loadDataFromQuote($quote);
                }
            }
        }
    }
}
