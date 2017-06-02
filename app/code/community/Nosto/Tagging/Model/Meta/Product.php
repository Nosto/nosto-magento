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
 * Meta data class which holds information about a product.
 * This is used during the order confirmation API request and the product
 * history export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Product extends Nosto_Object_Product_Product
{

    use Nosto_Tagging_Model_Meta_Product_Trait;

    /**
     * Backwards compatibility for tags
     *
     * @deprecated Use setters instead of direct assignment. This attribute will
     * be removed in future release.
     * @var array
     */
    protected $_tags = array();

    /**
     * Backwards compatibility for categories
     *
     * @deprecated Use setters instead of direct assignment. This attribute will
     * be removed in future release.
     * @var array
     */
    protected $_categories = array();

    /**
     * Array of deprecated direct attribute assignments
     *
     * @var array
     */
    public static $deprecatedAttributeMap = array(
        '_supplierCost' => 'supplierCost',
        '_tags' => 'tags',
    );

    /**
     * Nosto_Tagging_Model_Meta_Product constructor.
     * @suppress PhanDeprecatedProperty
     */
    public function __construct()
    {
        parent::__construct();
        foreach (Nosto_Tagging_Helper_Data::$validTags as $validTag) {
            /** @noinspection PhpDeprecationInspection */
            $this->_tags[$validTag] = array();
        }
    }

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
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');
        /** @var Nosto_Tagging_Helper_Price $priceHelper */
        $priceHelper = Mage::helper('nosto_tagging/price');
        $this->setUrl($this->buildUrl($product, $store));
        $this->setProductId($product->getId());
        $this->setName($product->getName());
        $this->setImageUrl($this->buildImageUrl($product, $store));
        $this->setPrice($this->buildProductPrice($product, $store));
        $this->setListPrice($this->buildProductListPrice($product, $store));
        $this->setPriceCurrencyCode($priceHelper->getTaggingCurrencyCode($store->getCurrentCurrencyCode(), $store));
        $this->setAvailability($this->buildAvailability($product));
        $this->setCategories($this->buildCategories($product));

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
        if ($dataHelper->getUseAlternateImages($store)) {
            $this->amendAlternativeImages($product, $store);
        }
        if ($dataHelper->getUseInventoryLevel($store)) {
            $this->amendInventoryLevel($product);
        }
        if ($dataHelper->getUseSkus($store)) {
            $this->amendSkus($product, $store);
        }
    }

    /**
     * Builds SKUs
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store $store
     */
    protected function amendSkus(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            /** @var Mage_Catalog_Model_Product_Type_Configurable $configurableProduct */
            $configurableProduct = Mage::getModel('catalog/product_type_configurable');
            $associatedProducts = $configurableProduct->getUsedProducts(null, $product);
            /** @var Mage_Catalog_Model_Product $associatedProduct */
            foreach ($associatedProducts as $associatedProduct) {
                /** @var Mage_Catalog_Model_Product $productModel */
                $productModel = Mage::getModel('catalog/product');
                /* @var Mage_Catalog_Model_Product $mageSku */
                $mageSku = $productModel->load($associatedProduct->getId());
                try {
                    /* @var Nosto_Tagging_Model_Meta_Sku $skuModel */
                    $skuModel = Mage::getModel('nosto_tagging/meta_sku');
                    $skuModel->loadData($mageSku, $product, $store);
                    $this->addSku($skuModel);
                } catch (Nosto_NostoException $e) {
                    Nosto_Tagging_Helper_Log::exception($e);
                }
            }
        }
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
     * @param Mage_Core_Model_Store $store the store model.
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
            Nosto_Tagging_Helper_Log::error(
                'Failed to resolve inventory level for product %d to tags. Error message was: %s',
                array(
                    $product->getId(),
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Adds the alternative image urls
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store $store the store model.d
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
     * @param Mage_Core_Model_Store $store the store model.
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
                Nosto_Tagging_Helper_Log::error(
                    'No rating class implementation found for %s',
                    array($ratingProvider)
                );
            }
        }
    }

    /**
     * Amends the product attributes to tags array if attributes are defined
     * and are present in product
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store $store the store model.
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
            /* @var Mage_Catalog_Model_Resource_Eav_Attribute $productAttribute */
            foreach ($productAttributes as $key => $productAttribute) {
                if (!in_array($key, $attributesToTag)) {
                    continue;
                }
                try {
                    $attributeValue = $this->getAttributeValue($product, $key);
                    if (empty($attributeValue)) {
                        continue;
                    }

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
                } catch (Exception $e) {
                    Nosto_Tagging_Helper_Log::exception($e);
                }
            }
        }
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
     * @suppress PhanUndeclaredMethod
     */
    protected function buildCategories(Mage_Catalog_Model_Product $product)
    {
        $data = array();

        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        $categoryCollection = $product->getCategoryCollection();
        $categoryCollection->addAttributeToFilter('is_active', 1);
        foreach ($categoryCollection as $category) {
            $categoryString = $helper->buildCategoryString($category);
            if (!empty($categoryString)) {
                $data[] = $categoryString;
            }
        }

        return array_values(array_unique($data));
    }

    /**
     * Backwards compatibility method to make the extension work with
     * old customisations
     *
     * @param string $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (!is_array($args)) {
            Nosto_Tagging_Helper_Log::deprecated(
                'Deprecated call %s with attributes %s',
                array($method, implode(',', $args))
            );
        }

        $compatibilityMethod = sprintf('__%s', $method);
        if (method_exists($this, $compatibilityMethod)) {
            return $this->$compatibilityMethod($args);
        } else {
            trigger_error('Call to undefined method '.__CLASS__.'::'.$method.'()', E_USER_ERROR); // @codingStandardsIgnoreLine
        }
    }

    /**
     * Backwards compatibility method to make the extension work with
     * old customisations
     *
     * @param $attribute
     * @param $value
     */
    public function __set($attribute, $value)
    {
        Nosto_Tagging_Helper_Log::deprecated(
            'Deprecated direct assignment %s with attributes %s',
            array($attribute, $value)
        );

        $trimmedAttribute = trim($attribute, '_');
        $setter = sprintf('set%s', ucfirst($trimmedAttribute));
        if (method_exists($this, $setter)) {
            try {
                $this->$setter($value);
            } catch (Exception $e) {
                Nosto_Tagging_Helper_Log::exception($e);
            }
        }
    }

    /**
     * Backwards compatibility method to make the extension work with
     * old customisations
     *
     * @param string $attribute
     * @return null
     */
    public function __get($attribute)
    {
        Nosto_Tagging_Helper_Log::deprecated(
            'Deprecated direct access for attribute %s',
            array($attribute)
        );

        $trimmedAttribute = trim($attribute, '_');
        $getter = sprintf('get%s', ucfirst($trimmedAttribute));
        $value = null;
        if (method_exists($this, $getter)) {
            try {
                $value = $this->$getter();
            } catch (Exception $e) {
                Nosto_Tagging_Helper_Log::exception($e);
            }
        }
        return $value;
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
        $attributeValue = null;
        if ($attribute instanceof Mage_Catalog_Model_Resource_Eav_Attribute) {
            $attributeData = $product->getData($attributeName);
            /** @noinspection PhpParamsInspection */
            $attributeValue = $product->getAttributeText($attributeName);
            if (empty($attributeValue) && is_scalar($attributeData)) {
                $attributeValue = trim($attributeData);
            } elseif (is_array($attributeValue)) {
                $attributeValue = implode(',', $attributeValue);
            }
        }

        return $attributeValue;
    }

    /**
     * @inheritdoc
     */
    public function getTag1()
    {
        return $this->mergeDeprecatedTags(Nosto_Tagging_Helper_Data::TAG1);
    }

    /**
     * @inheritdoc
     */
    public function getTag2()
    {
        return $this->mergeDeprecatedTags(Nosto_Tagging_Helper_Data::TAG2);
    }

    /**
     * @inheritdoc
     */
    public function getTag3()
    {
        return $this->mergeDeprecatedTags(Nosto_Tagging_Helper_Data::TAG3);
    }

    /**
     * Merges directly accessed tags with the getTags method
     *
     * @param $tag
     * @return array
     * @suppress PhanDeprecatedProperty
     */
    protected function mergeDeprecatedTags($tag)
    {
        $parentMethod = sprintf('get%s', ucfirst($tag));
        $tags = parent::$parentMethod();
        /** @noinspection PhpDeprecationInspection */
        if (!empty($this->_tags[$tag])) {
            Nosto_Tagging_Helper_Log::deprecated(
                'Deprecated tag usage for %s in class %s',
                array($tag, get_class_methods($this))
            );
            /** @noinspection PhpDeprecationInspection */
            $tags = array_merge($tags, $this->_tags[$tag]);
        }

        return $tags;
    }

    /**
     * @inheritdoc
     * @suppress PhanDeprecatedProperty
     */
    public function getCategories()
    {
        $categories = parent::getCategories();
        /** @noinspection PhpDeprecationInspection */
        if (!empty($this->_categories)) {
            /** @noinspection PhpDeprecationInspection */
            $categories = array_merge($categories, $this->_categories);
        }

        return $categories;
    }

    /**
     * Backwards compatibility method only accessible via magic method
     *
     * @return array
     * @suppress PhanDeprecatedProperty
     */
    protected function __getTags()
    {
        /** @noinspection PhpDeprecationInspection */
        return $this->_tags;
    }

    /**
     * Array of attributes that can be customized from Nosto's store admin
     * settings
     *
     * @@return array
     */
    protected function getCustomisableAttributes()
    {
        return array('gtin' => 'gtin', 'supplier_cost' => 'supplierCost');
    }

    /**
     * Reloads the product info from a Magento product model.
     *
     * @param Mage_Catalog_Model_Product $product the product model to reload
     * @param Mage_Core_Model_Store|null $store the store to get the product data for.
     *
     * @return bool returns false if the product is not available in a given store
     */
    public function reloadData(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        $return = false;
        /** @var Mage_Catalog_Model_Product $productModel */
        $productModel = Mage::getModel('catalog/product');
        /** @noinspection PhpUndefinedMethodInspection */
        $reloadedProduct = $productModel->setStoreId($store->getId())->load($product->getId());
        if ($reloadedProduct instanceof Mage_Catalog_Model_Product) {
            $this->loadData($reloadedProduct, $store);
            $return = true;
        }

        return $return;
    }
}
