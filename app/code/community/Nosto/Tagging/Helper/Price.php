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
 * Helper class for common price operations.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Price extends Mage_Core_Helper_Abstract
{
    /**
     * Gets the unit price for a product model including taxes.
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
     * Get the final price for a product model including taxes.
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
     * Get unit/final price for a product model based on store's setting
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store $store
     * @param bool $finalPrice if it is final price.
     * @return float
     * @suppress PhanUndeclaredMethod
     */
    public function getDisplayPriceInStore(
        Mage_Catalog_Model_Product $product,
        Mage_Core_Model_Store $store,
        $finalPrice = false
    ) {
        /** @var Mage_Tax_Helper_Data $taxHelper */
        $taxHelper = Mage::helper('tax');
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $inclTax = $taxHelper->displayPriceIncludingTax($store);
        return $this->_getProductPrice($product, $finalPrice, $inclTax);
    }

    /**
     * Get unit/final price for a product model.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param bool $finalPrice if final price.
     * @param bool $inclTax if tax is to be included.
     * @return float
     * @suppress PhanUndeclaredMethod
     * @codingStandardsIgnoreStart
     */
    protected function _getProductPrice(
        Mage_Catalog_Model_Product $product,
        $finalPrice = false,
        $inclTax = true
    ) 
    {
        $price = 0;

        switch ($product->getTypeId()) {
            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                $price = $this->getBundleProductPrices($product, $finalPrice, $inclTax);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                // Get the grouped product "starting at" price.
                // Price for grouped "parent" product cannot be configured in
                // store admin. In practise there is no such thing as
                // parent product for the grouped type product
                /** @var Mage_Catalog_Model_Config $config */
                $config = Mage::getSingleton('catalog/config');
                /** @var $tmpProduct Mage_Catalog_Model_Product */
                $tmpProduct = Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addAttributeToSelect(
                        $config->getProductAttributes()
                    )
                    ->addAttributeToFilter('entity_id', $product->getId())
                    ->setPage(1, 1)
                    ->addMinimalPrice()
                    ->addTaxPercents()
                    ->load()
                    ->getFirstItem(); // @codingStandardsIgnoreLine
                if ($tmpProduct) {
                    /** @var Mage_Tax_Helper_Data $helper */
                    $helper = Mage::helper('tax');
                    $price = $tmpProduct->getMinimalPrice();
                    if ($inclTax) {
                        $price = $helper->getPrice($tmpProduct, $price, true);
                    }
                }
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                // For configurable products we use the price defined for the
                // "parent" product. If for some reason the parent product
                // doesn't have a price configured we fetch the lowest price
                // configured from a child product / variation
                $price = $this->_getDefaultFromProduct(
                    $product,
                    $finalPrice,
                    $inclTax
                );
                if ((!$price && $price !== 0) || $price < 0) {
                    /** @var Mage_Catalog_Model_Product_Type_Configurable $configurableProduct */
                    $configurableProduct = Mage::getModel('catalog/product_type_configurable');
                    $associatedProducts = $configurableProduct->getUsedProducts(null, $product);
                    $lowestPrice = false;
                    $lowestUnavailablePrice = false;
                    /** @var Mage_Catalog_Model_Product $associatedProduct */
                    foreach ($associatedProducts as $associatedProduct) {
                        /* @var Mage_Catalog_Model_Product $productModel */
                        $productModel = Mage::getModel('catalog/product')->load(
                            $associatedProduct->getId()
                        );
                        if ($productModel instanceof Mage_Catalog_Model_Product && $productModel->isAvailable()) {
                            $variationPrice = $this->_getProductPrice($productModel, $finalPrice, $inclTax);
                            if (!$lowestPrice || $variationPrice < $lowestPrice) {
                                $lowestPrice = $variationPrice;
                            }
                        // If no SKU is available, we use the lowest price of them all
                        } elseif ($productModel instanceof Mage_Catalog_Model_Product && $lowestPrice === false) {
                            $variationPrice = $this->_getProductPrice($productModel, $finalPrice, $inclTax);
                            if (!$lowestUnavailablePrice || $variationPrice < $lowestUnavailablePrice) {
                                $lowestUnavailablePrice = $variationPrice;
                            }
                        }
                    }
                    $price = $lowestPrice;
                    if ($price === false) {
                        $price = $lowestUnavailablePrice;
                    }
                }
                break;
            default:
                $price = $this->_getDefaultFromProduct(
                    $product,
                    $finalPrice,
                    $inclTax
                );
                break;
        }

        return $price;
    }
    // @codingStandardsIgnoreEnd

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param bool $finalPrice
     * @param bool $inclTax
     * @return float
     * @suppress PhanUndeclaredMethod
     */
    public function getBundleProductPrices(
        Mage_Catalog_Model_Product $product,
        $finalPrice = false,
        $inclTax = true
    ) {
        // If a bundled uses fixed pricing the list price can be fethched from
        // product itself. For final price we always get the min price. If dynamic
        // pricing is used the list price for the bundled product is the sum of
        // list prices of the simple products included in the bundle.
        $fixedPrice = $this->_getDefaultFromProduct($product, $finalPrice, $inclTax);
        if ($fixedPrice && $fixedPrice > 0) {
            return $fixedPrice;
        }

        /** @var Mage_Bundle_Model_Product_Price $model */
        $model = $product->getPriceModel();
        $minBundlePrice = $model->getTotalPrices($product, 'min', $inclTax, $finalPrice);

        if ($finalPrice) {
            return (float)$minBundlePrice;
        }

        /** @var Mage_Bundle_Model_Product_Type $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $typeInstance->setStoreFilter($product->getStoreId(), $product);

        /** @var Mage_Bundle_Model_Resource_Option_Collection $optionCollection */
        $optionCollection = $typeInstance->getOptionsCollection($product);

        $selectionCollection = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($product),
            $product
        );

        /** @var Mage_Catalog_Helper_Product $productHelper */
        $productHelper = Mage::helper('catalog/product');
        $options = $optionCollection->appendSelections(
            $selectionCollection,
            false,
            $productHelper->getSkipSaleableCheck()
        );
        $sumListPrice = 0;
        $allOptional = true;
        /** @var Mage_Bundle_Model_Option $option */
        foreach ($options as $option) {
            if (!$option->getRequired()) {
                continue;
            }

            $allOptional = false;
            $minSimpleProductPricePrice = null;
            $simpleProductListPrice = null;
            /** @noinspection PhpUndefinedMethodInspection */
            $selections = $option->getSelections();
            /**
             * @var Mage_Catalog_Model_Product $selection
             */
            foreach ($selections as $selection) {
                if ($selection->isAvailable()) {
                    $simpleProductPrice = $this->_getProductPrice($selection, true, $inclTax);
                    if ($minSimpleProductPricePrice === null || $simpleProductPrice < $minSimpleProductPricePrice) {
                        $minSimpleProductPricePrice = $simpleProductPrice;
                        $simpleProductListPrice = $this->_getProductPrice($selection, false, $inclTax);
                    }
                }
            }

            $sumListPrice += $simpleProductListPrice;
        }

        //None of them are required, take the cheapest item
        if ($allOptional) {
            $cheapestItemPrice = null;//Cheapest item across all the options
            $cheapestItemListPrice = null;
            /** @var Mage_Bundle_Model_Option $option */
            foreach ($options as $option) {
                /** @noinspection PhpUndefinedMethodInspection */
                $selections = $option->getSelections();
                /**
                 * @var Mage_Catalog_Model_Product $selection
                 */
                foreach ($selections as $selection) {
                    if ($selection->isAvailable()) {
                        $simpleProductPrice = $this->_getProductPrice($selection, true, $inclTax);
                        if ($cheapestItemPrice === null || $simpleProductPrice < $cheapestItemPrice) {
                            $cheapestItemPrice = $simpleProductPrice;
                            $cheapestItemListPrice = $this->_getProductPrice($selection, false, $inclTax);
                        }
                    }
                }
            }

            if ($cheapestItemListPrice !== null) {
                $sumListPrice = $cheapestItemListPrice;
            }
        }

        return max($sumListPrice, $minBundlePrice);
    }

    /**
     * Returns the price from product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $finalPrice
     * @param bool $inclTax
     * @return float
     */
    protected function _getDefaultFromProduct(
        Mage_Catalog_Model_Product $product,
        $finalPrice = false,
        $inclTax = true
    ) { 
        /** @var Mage_Tax_Helper_Data $helper */
        $helper = Mage::helper('tax');
        if ($finalPrice) {
            /** @var Mage_Core_Model_Date $coreModel */
            $coreModel = Mage::getSingleton('core/date');
            $timestamp = $coreModel->gmtTimestamp();
            /* @var Mage_CatalogRule_Model_Resource_Rule $priceRule */
            /** @noinspection PhpUndefinedMethodInspection */
            $customerGroupId = $product->getCustomerGroupId() ? $product->getCustomerGroupId() : 0;
            $rulePrice = Mage::getResourceModel('catalogrule/rule')
                ->getRulePrice(
                    $timestamp,
                    $product->getStore()->getWebsiteId(),
                    $customerGroupId,
                    $product->getId()
                );
            $productFinalPrice = $product->getFinalPrice();
            if (is_numeric($rulePrice) && (!$productFinalPrice || $productFinalPrice > $rulePrice)) {
                $price = $rulePrice;
            } else {
                $price = $productFinalPrice;
            }
        } else {
            $price = $product->getPrice();
        }

        if ($inclTax) {
            $price = $helper->getPrice($product, $price, true);
        }

        return $price;
    }

    /**
     * @param float $price
     * @param Mage_Core_Model_Store $store
     * @return float
     */
    public function convertToDefaultCurrency($price, Mage_Core_Model_Store $store)
    {
        if (!is_numeric($price)) {
            NostoLog::error(
                'price must be a numeric value in %s, got %s.',
                array(__CLASS__, $price)
            );
            $price = 0;
        }

        /** @var Mage_Directory_Helper_Data $helper */
        $helper = Mage::helper('directory');
        return $helper->currencyConvert(
            $price,
            $store->getBaseCurrency()->getCode(),
            $store->getDefaultCurrency()->getCode()
        );
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
        /** @var Mage_Directory_Helper_Data $helper */
        $helper = Mage::helper('directory');
        $quantity = (double)$item->getQtyOrdered();
        $basePrice = $item->getBaseRowTotal() + $item->getBaseTaxAmount()
            + $item->getBaseHiddenTaxAmount() - $item->getBaseDiscountAmount();
        $orderCurrencyCode = $item->getOrder()->getOrderCurrencyCode();
        $baseCurrencyCode = $item->getOrder()->getBaseCurrencyCode();
        if ($orderCurrencyCode != $baseCurrencyCode) {
            $priceInOrderCurrency = $helper->currencyConvert(
                $basePrice,
                $baseCurrencyCode,
                $orderCurrencyCode
            );
        } else {
            $priceInOrderCurrency = $basePrice;
        }

        if ($quantity > 1) {
            $priceInOrderCurrency = round($priceInOrderCurrency / $quantity, 2);
        }

        return $priceInOrderCurrency;
    }

    /**
     * If the store uses multiple currencies the prices are converted from base
     * currency into given currency. Otherwise the given price is returned.
     *
     * @param float $basePrice The price of a product in base currency
     * @param string $currentCurrencyCode
     * @param Mage_Core_Model_Store $store
     * @return float
     */
    public function getTaggingPrice($basePrice, $currentCurrencyCode, Mage_Core_Model_Store $store)
    {
        /* @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        $baseCurrencyCode = $store->getBaseCurrencyCode();
        $taggingPrice = $basePrice;
        if ($helper->multiCurrencyDisabled($store) && $currentCurrencyCode !== $store->getBaseCurrencyCode()) {
            /** @var Mage_Directory_Helper_Data $directoryHelper */
            $directoryHelper = Mage::helper('directory');
            $taggingPrice = $directoryHelper->currencyConvert(
                $basePrice,
                $baseCurrencyCode,
                $currentCurrencyCode
            );
        }

        return $taggingPrice;
    }

    /**
     * Build product price
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store $store
     * @param $isFinalPrice true means it is final price, or it is list price
     * @return float the price
     */
    public function getProductTaggingPrice(
        Mage_Catalog_Model_Product $product,
        Mage_Core_Model_Store $store,
        $isFinalPrice
    ) {
        $basePrice = $this->getDisplayPriceInStore($product, $store, $isFinalPrice);

        return $this->getTaggingPrice(
            $basePrice,
            $store->getCurrentCurrencyCode(),
            $store
        );
    }

    /**
     * Returns the correct currency code for tagging
     *
     * @param $currentCurrencyCode
     * @param Mage_Core_Model_Store $store
     * @return string currency code in ISO 4217 format
     */
    public function getTaggingCurrencyCode($currentCurrencyCode, Mage_Core_Model_Store $store)
    {
        /* @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        if ($helper->multiCurrencyDisabled($store)) {
            $taggingCurrencyCode = $currentCurrencyCode;
        } else {
            $taggingCurrencyCode = $store->getBaseCurrencyCode();
        }

        return $taggingCurrencyCode;
    }

}
