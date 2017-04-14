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
 * @copyright Copyright (c) 2013-2017 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product trait to be used in SKU and Product building
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
trait Nosto_Tagging_Model_Meta_Product_Trait
{

    protected abstract function getCustomisableAttributes();

    /**
     * Builds the availability for the product.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     *
     * @return string
     */
    protected function buildAvailability(Mage_Catalog_Model_Product $product)
    {
        $availability = Nosto_Types_Product_ProductInterface::OUT_OF_STOCK;
        if (!$product->isVisibleInSiteVisibility()) {
            $availability = Nosto_Types_Product_ProductInterface::INVISIBLE;
        } elseif ($product->isAvailable()) {
            $availability = Nosto_Types_Product_ProductInterface::IN_STOCK;
        }

        return $availability;
    }

    /**
     * Builds the absolute store front url for the product page.
     *
     * The url includes the "___store" GET parameter in order for the Nosto
     * crawler to distinguish between stores that do not have separate domains
     * or paths.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store $store the store model.
     *
     * @return string
     */
    protected function buildUrl(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        /** @var Nosto_Tagging_Helper_Url $urlHelper */
        $urlHelper = Mage::helper('nosto_tagging/url');
        $productUrl = $urlHelper->generateProductUrl($product, $store);
        return $productUrl;
    }

    /**
     * Builds the product absolute image url for the store and returns it.
     * The image version is primarily taken from the store config, but falls
     * back the the base image if nothing is configured.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store $store the store model.
     *
     * @return null|string
     */
    protected function buildImageUrl(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        $url = null;
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        $imageVersion = $helper->getProductImageVersion($store);
        $img = $product->getData($imageVersion);
        $img = $this->isValidImage($img) ? $img : $product->getData('image');
        if ($this->isValidImage($img)) {
            // We build the image url manually in order get the correct base
            // url, even if this product is populated in the backend.
            $baseUrl = rtrim($store->getBaseUrl('media'), '/');
            $file = str_replace(DS, '/', $img);
            $file = ltrim($file, '/');
            $url = $baseUrl . '/catalog/product/' . $file;
        }
        return $url;
    }

    /**
     * Checks if the given image file path is valid.
     *
     * @param string $image the image file path.
     *
     * @return bool
     */
    protected function isValidImage($image)
    {
        return (!empty($image) && $image !== 'no_selection');
    }

    protected function buildPrice(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store, $type)
    {
        if ($type === 'listPrice') {
            $helperMethod = 'getProductPriceInclTax';
        } else {
            $helperMethod = 'getProductFinalPriceInclTax';
        }
        /** @var Nosto_Tagging_Helper_Price $priceHelper */
        $priceHelper = Mage::helper('nosto_tagging/price');

        return $priceHelper->getTaggingPrice(
            $priceHelper->$helperMethod($product),
            $store->getCurrentCurrencyCode(),
            $store
        );
    }

    protected function buildProductPrice(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        return $this->buildPrice($product, $store, 'price');
    }

    protected function buildProductListPrice(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        return $this->buildPrice($product, $store, 'listPrice');
    }

    /**
     * Amends the customizable attributes
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store $store the store model.
     * @throws Nosto_NostoException
     */
    protected function amendCustomizableAttributes(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        /* @var Nosto_Tagging_Helper_Data $nostoHelper */
        $nostoHelper = Mage::helper("nosto_tagging");

        $attributes = self::getCustomisableAttributes();
        if (!isset($attributes)) {
            throw new Nosto_NostoException(
                sprintf(
                    'Customizable attributes not defined for class %s',
                    get_class($this)
                )
            );
        }
        foreach ($attributes as $mageAttr => $nostoAttr) {
            $mapped = $nostoHelper->getMappedAttribute($mageAttr, $store);
            if ($mapped) {
                $value = $this->getAttributeValue($product, $mapped);
                if (!empty($value)) {
                    $method = sprintf('set%s', ucfirst($nostoAttr));
                    $this->$method($value);
                }
            }
        }
    }

    /**
     * Fetches the value of a product attribute
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $attributeName
     * @return string
     * @suppress PhanUndeclaredMethod
     */
    protected function getAttributeValue(Mage_Catalog_Model_Product $product, $attributeName)
    {
        $attribute = $product->getResource()->getAttribute($attributeName);
        if ($attribute instanceof Mage_Catalog_Model_Resource_Eav_Attribute) {
            $attributeData = $product->getData($attributeName);
            /** @noinspection PhpParamsInspection */
            $attributeValue = $product->getAttributeText($attributeName);
            if (empty($attributeValue) && is_scalar($attributeData)) {
                $attributeValue = $attributeData;
            }
        } else {
            $attributeValue = null;
        }

        return trim($attributeValue);
    }
}
