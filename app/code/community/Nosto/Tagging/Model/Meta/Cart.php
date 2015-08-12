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
 * Data Transfer Object representing a shopping cart.
 * This is used in the cart tagging.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Cart extends Mage_Core_Model_Abstract
{
    /**
     * @var Nosto_Tagging_Model_Meta_Cart_Item[] list of cart items.
     */
    protected $_lineItems = array();

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_cart');
    }

    /**
     * Loads the Data Transfer Object.
     *
     * @param Mage_Sales_Model_Quote_Item[] $quoteItems the quote items.
     * @param Mage_Core_Model_Store         $store the store view.
     */
    public function loadData(array $quoteItems, Mage_Core_Model_Store $store = null)
    {
        if (is_null($store)) {
            $store = Mage::app()->getStore();
        }
        $currencyCode = new NostoCurrencyCode($store->getBaseCurrencyCode());
        foreach ($quoteItems as $quoteItem) {
            /** @var Nosto_Tagging_Model_Meta_Cart_Item $model */
            $model = Mage::getModel('nosto_tagging/meta_cart_item');
            $model->loadData($quoteItem, $currencyCode);
            $this->_lineItems[] = $model;
        }
    }

    /**
     * Returns the cart line items.
     *
     * @return Nosto_Tagging_Model_Meta_Cart_Item[] the items.
     */
    public function getLineItems()
    {
        return $this->_lineItems;
    }
}
