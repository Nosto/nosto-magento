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
 * @copyright Copyright (c) 2013-2018 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once __DIR__ . '/../../bootstrap.php'; // @codingStandardsIgnoreLine
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
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');
        if (!$dataHelper->getSendAddToCartEvent(Mage::app()->getStore())) {
            return $this;
        }

        $quoteItem = $observer->getQuoteItem();

        if (!$quoteItem instanceof Mage_Sales_Model_Quote_Item) {
            NostoLog::info('Can not find quote item out of the event.');

            return $this;
        }

        $store = Mage::app()->getStore();
        $currencyCode = $store->getCurrentCurrencyCode();

        $addedItem = Nosto_Tagging_Model_Meta_Cart_Builder::buildItem($quoteItem, $currencyCode);

        $nostoCart = null;
        $quote = $quoteItem->getQuote();
        if ($quote instanceof Mage_Sales_Model_Quote) {
            /** @var Nosto_Tagging_Model_Meta_Cart $nostoCart */
            $nostoCart = Mage::getModel('nosto_tagging/meta_cart');
            $nostoCart->loadData($quote);
        } else {
            NostoLog::info('Can not find quote out of the event.');
        }

        /* @var $helper Nosto_Tagging_Helper_Data */
        $helper = Mage::helper('nosto_tagging');
        $nostoCustomerId = $helper->getCookieId();

        /** @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('nosto_tagging/account');
        $account = $helper->find($store);
        $service = new Nosto_Operation_CartOperation($account);
        $cartUpdate = new Nosto_Object_Cart_Update();

        $cartUpdate->setCart($nostoCart);
        $cartUpdate->setAddedItems(array($addedItem));
        $service->updateCart($cartUpdate, $nostoCustomerId, $account->getName());

        return $this;
    }
}
