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
     * @var Mage_Sales_Model_Quote_Item[] Cached items in cart.
     */
    protected $_items;

    /**
     * Render shopping cart content as hidden meta data if the module is
     * enabled for the current store.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('nosto_tagging')->isModuleEnabled()
            || !Mage::helper('nosto_tagging/account')->existsAndIsConnected()
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
        if (count($this->getItems()) > 0) {
            Mage::helper('nosto_tagging/customer')->updateNostoId();
        }

        return parent::_toHtml();
    }

    /**
     * Returns all visible cart items. If it is a bundle product with dynamic
     * price settings, we get it's products and return them. Fixed price bundle
     * is not supported.
     *
     * @return Mage_Sales_Model_Quote_Item[]
     */
    public function getItems()
    {
        if (!$this->_items) {
            $items = array();
            /** @var $visibleItems Mage_Sales_Model_Quote_Item[] */
            $visibleItems = parent::getItems();
            foreach ($visibleItems as $item) {
                $product = $item->getProduct();
                if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                    if ((int)$product->getPriceType() === Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
                        continue;
                    }
                    $items = array_merge($items, $item->getChildren());
                } else {
                    $items[] = $item;
                }
            }

            $this->_items = $items;
        }

        return $this->_items;
    }

    /**
     * Returns the product id for a quote item.
     * If the product type is "grouped", then return the grouped product's id
     * and not the id of the actual product.
     *
     * @param Mage_Sales_Model_Quote_Item $item the quote item model.
     *
     * @return int
     */
    public function getProductId($item)
    {
        switch ($item->getProductType()) {
            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                $option = $item->getOptionByCode('product_type');
                if ($option !== null) {
                    $productId = $option->getProductId();
                } else {
                    $productId = $item->getProductId();
                }
                break;

            default:
                $productId = $item->getProductId();
                break;
        }

        return (int)$productId;
    }
}
