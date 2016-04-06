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
 * Helper class for common price operations.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Price extends Mage_Core_Helper_Abstract
{
    /**
     * Gets the unit price in base currency for a product model including
     * taxes.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     *
     * @return float
     */
    public function getProductPriceInclTax($product)
    {
        return $this->_getProductPrice($product, false, true);
    }

    /**
     * Get the final price in base currency for a product model including
     * taxes.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     *
     * @return float
     */
    public function getProductFinalPriceInclTax($product)
    {
        return $this->_getProductPrice($product, true, true);
    }

    /**
     * Get the final price in base currency for an ordered item including
     * taxes as discounts.
     *
     * @param Mage_Sales_Model_Order_Item $item the item model.
     *
     * @return float
     */
    public function getItemFinalPriceInclTax(Mage_Sales_Model_Order_Item $item)
    {
        $quantity = (double)$item->getQtyOrdered();
        $price = $item->getBaseRowTotal() + $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount() - $item->getBaseDiscountAmount();
        if ($quantity > 1) {
            $price = round($price/$quantity, 2);
        }
        return $price;
    }

    /**
     * Get unit/final price in base currency for a product model.
     *
     * @param Mage_Catalog_Model_Product $product    the product model.
     * @param bool                       $finalPrice if final price.
     * @param bool                       $inclTax    if tax is to be included.
     *
     * @return float
     */
    protected function _getProductPrice($product, $finalPrice = false, $inclTax = true)
    {
        $price = 0;

        switch ($product->getTypeId()) {
            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                // Get the bundle product "from" price.
                $price = $product->getPriceModel()
                    ->getTotalPrices($product, 'min', $inclTax);
                break;

            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                // Get the grouped product "starting at" price.
                /** @var Mage_Catalog_Model_Config $config */
                $config = Mage::getSingleton('catalog/config');
                /** @var $tmpProduct Mage_Catalog_Model_Product */
                $tmpProduct = Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addAttributeToSelect($config->getProductAttributes())
                    ->addAttributeToFilter('entity_id', $product->getId())
                    ->addMinimalPrice()
                    ->addTaxPercents()
                    ->setPage(1, 1)
                    ->getFirstItem();

                if (!$tmpProduct->isEmpty()) {
                    $price = $tmpProduct->getMinimalPrice();
                    if ($inclTax) {
                        $price = Mage::helper('tax')
                            ->getPrice($tmpProduct, $price, true);
                    }
                }
                break;

            default:
                $price = $finalPrice
                    ? $product->getFinalPrice()
                    : $product->getPrice();
                if ($inclTax) {
                    $price = Mage::helper('tax')
                        ->getPrice($product, $price, true);
                }
                break;
        }

        return $price;
    }

    /**
     * Formats price into Nosto format, e.g. 1000.99.
     *
     * @param string|int|float $price the price to format.
     *
     * @return string
     */
    public function getFormattedPrice($price)
    {
        return number_format($price, 2, '.', '');
    }

    /**
     * If the store uses multiple currencies the prices are converted from base
     * currency into given currency. Otherwise the given price is returned.
     *
     * @param float                 $basePrice The price of a product in base currency
     * @param string                $currentCurrencyCode
     * @param Mage_Core_Model_Store $store
     * @return float
     */
    public function getTaggingPrice($basePrice, $currentCurrencyCode, Mage_Core_Model_Store $store)
    {
        /* @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        $taggingPrice = $basePrice;
        if ($helper->multiCurrencyDisabled($store) && $currentCurrencyCode !== $store->getBaseCurrencyCode()) {
            $taggingPrice = Mage::helper('directory')->currencyConvert(
                $basePrice,
                $store->getBaseCurrencyCode(),
                $currentCurrencyCode
            );
        }

        return $taggingPrice;
    }

    public function getTaggingCurrencyCode($currentCurrencyCode, Mage_Core_Model_Store $store)
    {
        /* @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        if ($helper->multiCurrencyDisabled()) {
            $taggingCurrencyCode = $currentCurrencyCode;
        } else {
            $taggingCurrencyCode = $store->getBaseCurrencyCode();
        }

        return $taggingCurrencyCode;
    }
}
