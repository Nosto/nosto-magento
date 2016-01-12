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
 * Helper class for Nosto customer related actions.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Customer extends Mage_Core_Helper_Abstract
{
    /**
     * Gets the Nosto ID for an order model.
     * The Nosto ID represents the customer who placed to the order on Nosto's
     * side.
     *
     * @param Mage_Sales_Model_Order $order the order to get the Nosto ID for.
     *
     * @return bool|string the Nosto ID or false if not found for order.
     */
    public function getNostoId(Mage_Sales_Model_Order $order)
    {
        /** @var Nosto_Tagging_Model_Customer $customer */
        $customer = Mage::getModel('nosto_tagging/customer');
        $customer->load($order->getQuoteId(), 'quote_id');
        return $customer->hasData('nosto_id') ? $customer->getNostoId() : false;
    }

    /**
     * Update the Nosto ID form the current quote if it exists.
     * The Nosto ID is present in a cookie set by the JavaScript loaded from
     * Nosto.
     */
    public function updateNostoId()
    {
        /** @var Mage_Checkout_Model_Cart $cart */
        $cart = Mage::getModel('checkout/cart');
        /** @var Mage_Core_Model_Cookie $cookie */
        $cookie = Mage::getModel('core/cookie');
        $quoteId = ($cart->getQuote() !== null)
            ? $cart->getQuote()->getId()
            : false;
        $nostoId = $cookie->get(Nosto_Tagging_Helper_Data::COOKIE_NAME);
        if (!empty($quoteId) && !empty($nostoId)) {
            /** @var Nosto_Tagging_Model_Customer $customer */
            $customer = Mage::getModel('nosto_tagging/customer')
                ->getCollection()
                ->addFieldToFilter('quote_id', $quoteId)
                ->addFieldToFilter('nosto_id', $nostoId)
                ->getFirstItem();
            if ($customer->hasData()) {
                $customer->setUpdatedAt(date('Y-m-d H:i:s'));
                $customer->save();
            } else {
                $customer->setQuoteId($quoteId);
                $customer->setNostoId($nostoId);
                $customer->setCreatedAt(date('Y-m-d H:i:s'));
                $customer->save();
            }
        }
    }
}
