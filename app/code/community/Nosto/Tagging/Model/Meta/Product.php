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
 * Meta data class which holds information about a product.
 * This is used during the order confirmation API request and the product
 * history export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Product extends NostoProduct
{
    /**
     * Array of attributes that can be customized from Nosto's store admin
     * settings
     *
     * @var array
     */
    public static $customizableAttributes = array(
        'gtin' => '_gtin',
    );

    /**
     * Loads the product info from a Magento product model.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store|null $store the store to get the product data for.
     */
    public function loadData(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store = null)
    {
        if ($store === null) {
            $store = Mage::app()->getStore();
        }

        /** @var Nosto_Tagging_Helper_Price $priceHelper */
        $priceHelper = Mage::helper('nosto_tagging/price');
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');

        $this->setUrl($this->buildUrl($product, $store));
        $this->setProductId($product->getId());
        $this->setName($product->getName());
        $this->setImageUrl($this->buildImageUrl($product, $store));
        $this->setPrice(
            $priceHelper->getTaggingPrice(
                $priceHelper->getProductFinalPriceInclTax($product),
                $store->getCurrentCurrencyCode(),
                $store
            )
        );
        $this->setListPrice(
            $priceHelper->getTaggingPrice(
                $priceHelper->getProductPriceInclTax($product),
                $store->getCurrentCurrencyCode(),
                $store
            )
        );
        $this->setPriceCurrencyCode($priceHelper->getTaggingCurrencyCode($store->getCurrentCurrencyCode(), $store));
        $this->setAvailability($this->buildAvailability($product));
        $this->setCategories($this->buildCategories($product));

        // Optional properties.

        if ($product->hasData('short_description')) {
            $this->setDescription($product->getData('short_description'));
        }
        if ($product->hasData('description')) {
            $this->setDescription($this->getDescription() . ' ' . $product->getData('description'));
        }
        $brandAttribute = $dataHelper->getBrandAttribute($store);
        if ($product->hasData($brandAttribute)) {
            $this->setBrand($this->getAttributeValue($product, $brandAttribute));
        }
        if (($tags = $this->buildTags($product, $store)) !== array()) {
            $this->setTag1($tags);
        }
        if (!$dataHelper->multiCurrencyDisabled($store)) {
            $this->setVariationId($store->getBaseCurrencyCode());
        }

        $this->amendAttributeTags($product, $store);
        $this->amendReviews($product, $store);
        $this->amendCustomizableAttributes($product, $store);
        $this->amendAlternativeImages($product, $store);
        $this->amendInventoryLevel($product);
    }

    /**
     * Builds the availability for the product.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     *
     * @return string
     */
    protected function buildAvailability(Mage_Catalog_Model_Product $product)
    {
        $availability = self::OUT_OF_STOCK;
        if (!$product->isVisibleInSiteVisibility()) {
            $availability = self::INVISIBLE;
        } elseif ($product->isAvailable()) {
            $availability = self::IN_STOCK;
        }

        return $availability;
    }


    /**
     * Builds the "tag1" tags.
     *
     * These include any "tag/tag" model names linked to the product, as well
     * as a special "add-to-cart" tag if the product can be added to the
     * cart directly without any choices, i.e. it is a non-configurable simple
     * product.
     * This special tag can then be used in the store frontend to enable a
     * "add to cart" button in the product recommendations.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store      $store the store model.
     *
     * @return array
     */
    protected function buildTags(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        $tags = array();

        if (Mage::helper('core')->isModuleEnabled('Mage_Tag')) {
            $tagCollection = Mage::getModel('tag/tag')
                ->getCollection()
                ->addPopularity()
                ->addStatusFilter(Mage_Tag_Model_Tag::STATUS_APPROVED)
                ->addProductFilter($product->getId())
                ->setFlag('relation', true)
                ->addStoreFilter($store->getId())
                ->setActiveFilter();
            foreach ($tagCollection as $tag) {
                /** @var Mage_Tag_Model_Tag $tag */
                $tags[] = $tag->getName();
            }
        }

        if (!$product->canConfigure()) {
            $tags[] = self::ADD_TO_CART;
        }

        return $tags;
    }

    /**
     * Adds the stock level / inventory level
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     *
     */
    protected function amendInventoryLevel(Mage_Catalog_Model_Product $product) 
    {
        /* @var Nosto_Tagging_Helper_Stock $stockHelper */
        $stockHelper = Mage::helper('nosto_tagging/stock');
        try {
            $this->setInventoryLevel($stockHelper->getQty($product));
        } catch (Exception $e) {
            Mage::log(
                sprintf(
                    'Failed to resolve inventory level for product %d to tags. Error message was: %s',
                    $product->getId(),
                    $e->getMessage()
                ),
                Zend_Log::WARN,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );
        }
    }

    /**
     * Adds the alternative image urls
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store      $store the store model.
     *
     */
    protected function amendAlternativeImages(
        Mage_Catalog_Model_Product $product,
        Mage_Core_Model_Store $store
    ) 
    {
        /* @var Mage_Catalog_Model_Product_Attribute_Media_Api $mediaApi */
        $mediaApi = Mage::getModel('catalog/product_attribute_media_api');
        $mediaItems = $mediaApi->items($product->getId(), $store);
        if (is_array($mediaItems)) {
            foreach ($mediaItems as $image) {
                if (
                    isset($image['url'])
                    && (isset($image['exclude']) && empty($image['exclude']))
                    && !in_array($image['url'], $this->getAlternateImageUrls())
                    && $image['url'] != $this->getImageUrl()
                ) {
                    $this->addAlternateImageUrls($image['url']);
                }
            }
        }
    }

    /**
     * Amends the product reviews product
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store      $store the store model.
     *
     */
    protected function amendReviews(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        /* @var Nosto_Tagging_Helper_Data $dataHelper*/
        $dataHelper = Mage::helper('nosto_tagging');
        $ratingProvider = $dataHelper->getRatingsAndReviewsProvider($store);
        if ($ratingProvider) {
            /* @var Nosto_Tagging_Helper_Class $classHelper */
            $classHelper = Mage::helper('nosto_tagging/class');
            /* @var Nosto_Tagging_Model_Meta_Rating $ratingClass */
            $ratingClass = $classHelper->getRatingClass($store);
            if ($ratingClass instanceof Nosto_Tagging_Model_Meta_Rating_Interface) {
                $ratingClass->init($product, $store);
                if ($ratingClass->getRating()) {
                    $this->setRatingValue($ratingClass->getRating());
                }
                if ($ratingClass->getReviewCount()) {
                    $this->setReviewCount($ratingClass->getReviewCount());
                }
            } else {
                Mage::log(
                    sprintf(
                        'No rating class implementation found for %s',
                        $ratingProvider
                    ),
                    Zend_Log::WARN,
                    Nosto_Tagging_Model_Base::LOG_FILE_NAME
                );
            }
        }
    }

    /**
     * Amends the customizable attributes
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store      $store the store model.
     *
     */
    protected function amendCustomizableAttributes(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        /* @var Nosto_Tagging_Helper_Data $nostoHelper */
        $nostoHelper = Mage::helper("nosto_tagging");

        foreach (self::$customizableAttributes as $mageAttr => $nostoAttr) {
            $mapped = $nostoHelper->getMappedAttribute($mageAttr, $store);
            if ($mapped) {
                $value = $this->getAttributeValue($product, $mapped);
                if (!empty($value)) {
                    $this->$nostoAttr = $value;
                }
            }
        }
    }

    /**
     * Amends the product attributes to tags array if attributes are defined
     * and are present in product
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store      $store the store model.
     *
     */
    protected function amendAttributeTags(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        $productAttributes = $product->getAttributes();
        /* @var Nosto_Tagging_Helper_Data $nostoHelper */
        $nostoHelper = Mage::helper("nosto_tagging");

        foreach (Nosto_Tagging_Helper_Data::$validTags as $tagId) {
            $attributesToTag = $nostoHelper->getAttributesToTag($tagId, $store->getId());
            if (empty($attributesToTag) || !is_array($attributesToTag)) {
                continue;
            }
            /* @var Mage_Catalog_Model_Resource_Eav_Attribute $productAttribute*/
            foreach ($productAttributes as $key=>$productAttribute) {
                if (in_array($key, $attributesToTag)) {
                    try {
                        $attributeValue = $this->getAttributeValue($product, $key);
                        if (!empty($attributeValue)) {
                            switch ($tagId) {
                                case Nosto_Tagging_Helper_Data::TAG1:
                                    $this->addTag1(sprintf('%s:%s', $key, $attributeValue));
                                    break;
                                case Nosto_Tagging_Helper_Data::TAG2:
                                    $this->addTag2(sprintf('%s:%s', $key, $attributeValue));
                                    break;
                                case Nosto_Tagging_Helper_Data::TAG3:
                                    $this->addTag3(sprintf('%s:%s', $key, $attributeValue));
                                    break;
                            }
                        }
                    } catch (Exception $e) {
                        Mage::log(
                            sprintf(
                                'Failed to add attribute %s to tags. Error message was: %s',
                                $key,
                                $e->getMessage()
                            ),
                            Zend_Log::WARN,
                            Nosto_Tagging_Model_Base::LOG_FILE_NAME
                        );
                    }
                }
            }
        }
    }

    /**
     * Builds the absolute store front url for the product page.
     *
     * The url includes the "___store" GET parameter in order for the Nosto
     * crawler to distinguish between stores that do not have separate domains
     * or paths.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store      $store the store model.
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
     * @param Mage_Core_Model_Store      $store the store model.
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
            $url = $baseUrl.'/catalog/product/'.$file;
        }
        return $url;
    }

    /**
     * Return array of categories for the product.
     * The items in the array are strings combined of the complete category
     * path to the products own category.
     *
     * Structure:
     * array (
     *     /Electronics/Computers
     * )
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     *
     * @return array
     */
    protected function buildCategories(Mage_Catalog_Model_Product $product)
    {
        $data = array();

        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        $categoryCollection = $product->getCategoryCollection();
        foreach ($categoryCollection as $category) {
            $categoryString = $helper->buildCategoryString($category);
            if (!empty($categoryString)) {
                $data[] = $categoryString;
            }
        }
        
        return array_unique($data);
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
     * Fetches the value of a product attribute
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $attributeName
     * @return string
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
