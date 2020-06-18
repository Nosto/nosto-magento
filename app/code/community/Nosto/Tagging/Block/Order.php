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

use Nosto_Tagging_Helper_Log as NostoLog;

/**
 * Order tagging block.
 * Adds meta-data to the HTML document for successful orders.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Order extends Mage_Checkout_Block_Success
{
    /**
     * Render order info as hidden meta data if the module is enabled for the
     * current store.
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('nosto_tagging/account');
        /** @var Nosto_Tagging_Helper_Module $moduleHelper */
        $moduleHelper = Mage::helper('nosto_tagging/module');
        if (!$helper->existsAndIsConnected()
            ||$this->getLastOrder() === null
            || !$moduleHelper->isModuleEnabled()
        ) {
            return '';
        }

        return $this->getLastOrder()->toHtml();
    }

    /**
     * Return the last placed order meta data for the customer.
     *
     * @return Nosto_Tagging_Model_Meta_Order|null the order meta data model.
     * @suppress PhanTypeMismatchReturn
     */
    public function getLastOrder()
    {
        /** @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('core');
        try {
            $nostoOrder = null;
            if ($helper->isModuleEnabled('Vaimo_Klarna')) {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $checkoutId = Mage::getSingleton('checkout/session')
                        ->getKlarnaCheckoutPrevId();
                    /* @var Nosto_Tagging_Model_Meta_Order_Vaimo_Klarna_Checkout $nostoOrder */
                    $nostoOrder = Mage::getModel(
                        'nosto_tagging/meta_order_vaimo_klarna_checkout'
                    );
                    $nostoOrder->loadOrderByKlarnaCheckoutId($checkoutId);
                    // Double check that payment provider is vaimo_klarna_checkout
                    if ($nostoOrder->getPaymentProvider() !== 'vaimo_klarna_checkout') {
                        $nostoOrder = null;
                    }
                } catch (\Exception $vaimoException) {
                    NostoLog::exception($vaimoException);
                }
            }

            if (!$nostoOrder) {
                /** @noinspection PhpUndefinedMethodInspection */
                $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
                /** @var Mage_Sales_Model_Order $order */
                $order = Mage::getModel('sales/order')->load($orderId);
                /** @var Nosto_Tagging_Model_Meta_Order $nostoOrder */
                $nostoOrder = Mage::getModel('nosto_tagging/meta_order');
                $nostoOrder->loadData($order);
            }
        } catch (\Exception $e) {
            NostoLog::exception($e);
        }

        return $nostoOrder;
    }

    /**
     * Returns the visitor's Nosto Id
     *
     * @return null|string
     */
    public function getVisitorChecksum()
    {
        /* @var $helper Nosto_Tagging_Helper_Data */
        $helper = Mage::helper('nosto_tagging');

        return $helper->getVisitorChecksum();
    }

    /**
     * Formats a price e.g. "1234.56".
     *
     * @param int $price the price to format.
     * @return string the formatted price.
     */
    public function formatNostoPrice($price)
    {
        return Nosto_Helper_PriceHelper::format($price);
    }
}
