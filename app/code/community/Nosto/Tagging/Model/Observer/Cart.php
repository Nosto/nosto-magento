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
 * @copyright Copyright (c) 2013-2019 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var Nosto_Tagging_Helper_Bootstrap $nostoBootstrapHelper */
$nostoBootstrapHelper = Mage::helper('nosto_tagging/bootstrap');
$nostoBootstrapHelper->init();

use Nosto_Tagging_Helper_Log as NostoLog;

/**
 * Event observer model for cart.
 * Used to interact with Magento events.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 * @suppress PhanUnreferencedClass
 */
class Nosto_Tagging_Model_Observer_Cart
{
    const COOKIE_NAME = 'nosto.itemsAddedToCart';

    /**
     * Cart item added event handler
     *
     * Event 'checkout_cart_product_add_after'.
     *
     * @param Varien_Event_Observer $observer the event observer.
     *
     * @return Nosto_Tagging_Model_Observer_Cart
     */
    public function cartItemAdded(Varien_Event_Observer $observer)
    {
        try {
            /** @var Nosto_Tagging_Helper_Data $helper */
            $helper = Mage::helper('nosto_tagging');
            if (!$helper->isModuleEnabled()) {
                return $this;
            }

            /** @var Nosto_Tagging_Helper_Data $dataHelper */
            $dataHelper = Mage::helper('nosto_tagging');
            $store = Mage::app()->getStore();

            /** @var Nosto_Tagging_Helper_Account $helper */
            $helper = Mage::helper('nosto_tagging/account');
            $account = $helper->find($store);

            if (!$account || !$account->isConnectedToNosto()) {
                return $this;
            }

            /** @noinspection PhpUndefinedMethodInspection */
            $quoteItem = $observer->getQuoteItem();

            if (!$quoteItem instanceof Mage_Sales_Model_Quote_Item) {
                NostoLog::info('Cannot find quote item from the event.');

                return $this;
            }

            $currencyCode = $store->getCurrentCurrencyCode();

            $cartUpdate = new Nosto_Object_Event_Cart_Update();
            $addedItem = Nosto_Tagging_Model_Meta_Cart_Builder::buildItem($quoteItem, $currencyCode);
            $cartUpdate->setAddedItems(array($addedItem));

            //set the cookie to trigger add to cart event
            if (!headers_sent()) {
                /** @var Mage_Core_Model_Cookie $cookie */
                $cookie = Mage::getModel('core/cookie');

                $cookie->set(
                    self::COOKIE_NAME,
                    Nosto_Helper_SerializationHelper::serialize($cartUpdate),
                    60,     //60 seconds
                    '/',    //path
                    false,
                    false,
                    false
                );
            } else {
                NostoLog::info('Headers sent already. Cannot set the cookie.');
            }

            /** @noinspection PhpDeprecationInspection */
            if ($dataHelper->getSendAddToCartEvent()) {
                $quote = $quoteItem->getQuote();
                if ($quote instanceof Mage_Sales_Model_Quote) {
                    /** @var Nosto_Tagging_Model_Meta_Cart $nostoCart */
                    $nostoCart = Mage::getModel('nosto_tagging/meta_cart');
                    $nostoCart->loadData($quote);
                    /** @phan-suppress-next-line PhanTypeMismatchArgument */
                    $cartUpdate->setCart($nostoCart);
                } else {
                    NostoLog::info('Cannot find quote from the event.');
                }

                /* @var Nosto_Tagging_Model_Service_Cart $service */
                $service = Mage::getModel('nosto_tagging/service_cart');
                $service->update($cartUpdate, $account);
            }
        } catch (Exception $e) {
            NostoLog::exception($e);
        }

        return $this;
    }
}
