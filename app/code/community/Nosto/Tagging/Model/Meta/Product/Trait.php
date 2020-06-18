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
     * @throws Nosto_NostoException
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
     * @throws Mage_Core_Exception
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
            /** @noinspection PhpUnhandledExceptionInspection */
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

    /**
     * Build product final price
     * 
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store $store
     * @return float final price of the product
     */
    protected function buildProductPrice(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        /** @var Nosto_Tagging_Helper_Price $priceHelper */
        $priceHelper = Mage::helper('nosto_tagging/price');
        $productClone = $product;

        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging/data');
        if ($dataHelper->isVariationEnabled($store)) {
            // We need to set the default customer group here, otherwise Magento will
            // return the price for the current user logged in group.
            $productClone = clone $product;
            $productClone->setGroupPrice(Nosto_Tagging_Helper_Variation::DEFAULT_CUSTOMER_GROUP_ID);
        }

        return $priceHelper->getProductTaggingPrice($productClone, $store, true);
    }

    /**
     * Build product list price
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store $store
     * @return float list price
     */
    protected function buildProductListPrice(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        /** @var Nosto_Tagging_Helper_Price $priceHelper */
        $priceHelper = Mage::helper('nosto_tagging/price');

        return $priceHelper->getProductTaggingPrice($product, $store, false);
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
                $value = $this->getAttributeValue($product, $mapped, $store->getId());
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
     * @param null|int $storeId
     * @return string
     * @suppress PhanUndeclaredMethod
     */
    protected function getAttributeValue(
        Mage_Catalog_Model_Product $product,
        $attributeName,
        $storeId = null
    ) {
        $attribute = $product->getResource()->getAttribute($attributeName);
        if ($attribute instanceof Mage_Catalog_Model_Resource_Eav_Attribute) {
            if ($storeId && method_exists($product, 'setStoreId')) {
                $product->setStoreId($storeId);
            }

            $attributeData = $product->getData($attributeName);
            /** @noinspection PhpParamsInspection */
            $attributeValue = $product->getAttributeText($attributeName);
            if (empty($attributeValue) && is_scalar($attributeData)) {
                $attributeValue = (string) $attributeData;
            }
        } else {
            $attributeValue = '';
        }

        return trim($attributeValue);
    }

    /**
     * Get the custom attributes
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store $store
     * @return array|null custom fields
     */
    protected function loadCustomFields(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        $customFields = array();

        $attributes = $product->getTypeInstance(true)->getSetAttributes($product);
        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
        foreach ($attributes as $attribute) {
            try {
                //tag user defined attributes only
                if ($attribute->getData('is_user_defined') == 1
                    && (
                        $attribute->getIsVisibleOnFront() == 1
                        || $attribute->getIsFilterable() == 1
                    )
                ) {
                    $attributeCode = $attribute->getAttributeCode();
                    $attributeValue = $this->getAttributeValue($product, $attributeCode, $store->getId());
                    if (is_scalar($attributeValue)) {
                        $customFields[$attributeCode] = $attributeValue;
                    }
                }
            } catch (\Exception $e) {
                Nosto_Tagging_Helper_Log::exception($e);
            }
        }

        return $customFields;
    }
}
