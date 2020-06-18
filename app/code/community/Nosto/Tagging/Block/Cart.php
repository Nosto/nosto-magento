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
 * Shopping cart content tagging block.
 * Adds meta-data to the HTML document for shopping cart content.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Cart extends Mage_Checkout_Block_Cart_Abstract
{
    /**
     * Render shopping cart content as hidden meta data if the module is
     * enabled for the current store.
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
            || $this->getCart() === null
            ||!$moduleHelper->isModuleEnabled()
        ) {
            return '';
        }

        // If we have items in the cart, then update the Nosto customer quote
        // link. This is done to enable server-to-server order confirmation
        // requests once the quote is turned into an order.
        // We do it here as this will be run on every request when we have a
        // quote. This is important as the Nosto customer ID will change after
        // a period of time while the Mage quote ID can be the same.
        // The ideal place to run it would be once when the customer goes to
        // the `checkout/cart` page, but there are no events that are fired on
        // that page only, and the cart page recommendation elements we output
        // come through a generic block that cannot be used for this specific
        // action.
        $items = $this->getItems();
        if (!empty($items)) {
            /** @var Nosto_Tagging_Helper_Customer $customerHelper */
            $customerHelper = Mage::helper('nosto_tagging/customer');
            try {
                $customerHelper->updateNostoId();
            } catch (\Exception $e) {
                NostoLog::exception($e);
            }
        }

        return $this->getCart()->toHtml();
    }

    /**
     * Return the current cart for the customer
     *
     * @return Nosto_Tagging_Model_Meta_Cart the cart meta data model.
     * @suppress PhanTypeMismatchReturn
     */
    public function getCart()
    {
        /** @var Nosto_Tagging_Model_Meta_Cart $meta */
        $meta = Mage::getModel('nosto_tagging/meta_cart');
        $meta->loadData($this->getQuote());
        return $meta;
    }

    /**
     * Returns the visitor's Nosto Id
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
