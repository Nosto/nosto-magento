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
class Nosto_Tagging_Model_Meta_Product extends Nosto_Tagging_Model_Base implements NostoProductInterface, NostoValidatableInterface
{
    /**
     * @var string the absolute url to the product page in the shop frontend.
     */
    protected $_url;

    /**
     * @var string the product's unique identifier.
     */
    protected $_productId;

    /**
     * @var string the name of the product.
     */
    protected $_name;

    /**
     * @var string the absolute url the one of the product images in frontend.
     */
    protected $_imageUrl;

    /**
     * @var string the product price including possible discounts and taxes.
     */
    protected $_price;

    /**
     * @var string the product list price without discounts but incl taxes.
     */
    protected $_listPrice;

    /**
     * @var string the currency code (ISO 4217) the product is sold in.
     */
    protected $_currencyCode;

    /**
     * @var string the availability of the product, i.e. is in stock or not.
     */
    protected $_availability;

    /**
     * @var array the tags for the product.
     */
    protected $_tags = array();

    /**
     * @var array the categories the product is located in.
     */
    protected $_categories = array();

    /**
     * @var string the product short description.
     */
    protected $_shortDescription;

    /**
     * @var string the product description.
     */
    protected $_description;

    /**
     * @var string the product brand name.
     */
    protected $_brand;

    /**
     * @var string the default variation identifier of the shop
     */
    protected $_variationId;

    /**
     * @var float the price paid for the supplier
     */
    protected $_supplierCost;

    /**
     * @var int product stock
     */
    protected $_inventoryLevel;

    /**
     * @var int the amount of reviews
     */
    protected $_reviewCount;

    /**
     * @var float the value of the rating(s)
     */
    protected $_ratingValue;

    /**
     * @var array alternative image urls
     */
    protected $_alternateImageUrls;

    /**
     * @var string the condition of the product
     */
    protected $_condition;

    /**
     * @var string the gender (target group) of the product
     */
    protected $_gender;

    /**
     * @var string the the age group
     */
    protected $_ageGroup;

    /**
     * @var string the barcode
     */
    protected $_gtin;

    /**
     * @var string category used in Google's services
     */
    protected $_googleCategory;

    /**
     * @var string the pricing measure of the product. Pricing measure for a
     * 0.33 liter bottle for example is "0.33".
     */
    protected $_unitPricingMeasure;

    /**
     * @var string the pricing base measure of the product. Pricing base measure
     * for a 0.33l bottle is "1".
     */
    protected $_unitPricingBaseMeasure;

    /**
     * @var string the pricing unit of the product. Pricing unit for a 0.33l
     * bottle is "l" (litre).
     */
    protected $_unitPricingUnit;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_product');
    }

    /**
     * @inheritdoc
     */
    public function getValidationRules()
    {
        return array(
            array(
                array(
                    '_url',
                    '_productId',
                    '_name',
                    '_imageUrl',
                    '_price',
                    '_listPrice',
                    '_currencyCode',
                    '_availability'
                ),
                'required'
            )
        );
    }

    public function __construct()
    {
        parent::__construct();
        foreach (Nosto_Tagging_Helper_Data::$validTags as $validTag) {
            $this->_tags[$validTag] = array();
        }
        $this->_alternateImageUrls = array();
    }

    /**
     * Loads the product info from a Magento product model.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store|null $store the store to get the product data for.
     */
    public function loadData(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store = null)
    {
        if (is_null($store)) {
            $store = Mage::app()->getStore();
        }

        /** @var Nosto_Tagging_Helper_Price $priceHelper */
        $priceHelper = Mage::helper('nosto_tagging/price');
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');

        $this->_url = $this->buildUrl($product, $store);
        $this->_productId = $product->getId();
        $this->_name = $product->getName();
        $this->_imageUrl = $this->buildImageUrl($product, $store);
        $currentCurrencyCode = $store->getCurrentCurrencyCode();
        $this->_price = $priceHelper->getTaggingPrice(
            $priceHelper->getProductFinalPriceInclTax($product),
            $currentCurrencyCode,
            $store
        );
        $this->_listPrice = $priceHelper->getTaggingPrice(
            $priceHelper->getProductPriceInclTax($product),
            $currentCurrencyCode,
            $store
        );
        $this->_currencyCode = $priceHelper->getTaggingCurrencyCode($currentCurrencyCode, $store);
        $this->_availability = $this->buildAvailability($product);
        $this->_categories = $this->buildCategories($product);

        // Optional properties.

        if ($product->hasData('short_description')) {
            $this->_shortDescription = $product->getData('short_description');
        }
        if ($product->hasData('description')) {
            $this->_description = $product->getData('description');
        }
        $brandAttribute = $dataHelper->getBrandAttribute($store);
        if ($product->hasData($brandAttribute)) {
            $this->_brand = $this->getAttributeValue($product, $brandAttribute);
        }
        if (($tags = $this->buildTags($product, $store)) !== array()) {
            $this->_tags['tag1'] = $tags;
        }
        if (!$dataHelper->multiCurrencyDisabled($store)) {
            $this->_variationId = $store->getBaseCurrencyCode();
        }

        $this->amendAttributeTags($product, $store);
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
        if(!$product->isVisibleInSiteVisibility()) {
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
     * Amends the product attributes to tags array if attributes are defined
     * and are present in product
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store      $store the store model.
     *
     */
    protected function amendAttributeTags(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        $product_attributes = $product->getAttributes();
        /* @var Nosto_Tagging_Helper_Data $nosto_helper */
        $nosto_helper = Mage::helper("nosto_tagging");

        foreach (Nosto_Tagging_Helper_Data::$validTags as $tag_id) {
            $attributes_to_tag = $nosto_helper->getAttributesToTag($tag_id, $store->getId());
            if (empty($attributes_to_tag) || !is_array($attributes_to_tag)) {
                continue;
            }
            /* @var Mage_Catalog_Model_Resource_Eav_Attribute $product_attribute*/
            foreach ($product_attributes as $key=>$product_attribute) {
                if (in_array($key, $attributes_to_tag)) {
                    try {
                        $attribute_value = $this->getAttributeValue($product, $key);
                        if (!empty($attribute_value)) {
                            $this->_tags[$tag_id][] = sprintf(
                                '%s:%s',
                                $key,
                                $attribute_value
                            );
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
        /** @var Nosto_Tagging_Helper_Url $url_helper */
        $url_helper = Mage::helper('nosto_tagging/url');
        $product_url = $url_helper->generateProductUrl($product, $store);
        return $product_url;
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
     * Returns the absolute url to the product page in the shop frontend.
     *
     * @return string the url.
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Returns the product's unique identifier.
     *
     * @return int|string the ID.
     */
    public function getProductId()
    {
        return $this->_productId;
    }

    /**
     * Setter for the product's unique identifier.
     *
     * @param int|string $productId the ID.
     */
    public function setProductId($productId)
    {
        $this->_productId = $productId;
    }

    /**
     * Returns the name of the product.
     *
     * @return string the name.
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns the absolute url the one of the product images in the frontend.
     *
     * @return string the url.
     */
    public function getImageUrl()
    {
        return $this->_imageUrl;
    }

    /**
     * Returns the price of the product including possible discounts and taxes.
     *
     * @return float the price.
     */
    public function getPrice()
    {
        return $this->_price;
    }

    /**
     * Returns the list price of the product without discounts but incl taxes.
     *
     * @return float the price.
     */
    public function getListPrice()
    {
        return $this->_listPrice;
    }

    /**
     * Returns the currency code (ISO 4217) the product is sold in.
     *
     * @return string the currency ISO code.
     */
    public function getCurrencyCode()
    {
        return $this->_currencyCode;
    }

    /**
     * Returns the availability of the product, i.e. if it is in stock or not.
     *
     * @return string the availability, either "InStock" or "OutOfStock".
     */
    public function getAvailability()
    {
        return $this->_availability;
    }

    /**
     * Returns the tags for the product.
     *
     * @return array the tags array, e.g. array('tag1' => array("winter", "shoe")).
     */
    public function getTags()
    {
        return $this->_tags;
    }

    /**
     * Returns the categories the product is located in.
     *
     * @return array list of category strings, e.g. array("/shoes/winter").
     */
    public function getCategories()
    {
        return $this->_categories;
    }

    /**
     * Returns the product short description.
     *
     * @return string the short description.
     */
    public function getShortDescription()
    {
        return $this->_shortDescription;
    }

    /**
     * Returns the product description.
     *
     * @return string the description.
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Returns the product brand name.
     *
     * @return string the brand name.
     */
    public function getBrand()
    {
        return $this->_brand;
    }

    /**
     * Returns the full product description,
     * i.e. both the "short" and "normal" descriptions concatenated.
     *
     * @return string the full descriptions.
     */
    public function getFullDescription()
    {
        $descriptions = array();
        if (!empty($this->_shortDescription)) {
            $descriptions[] = $this->_shortDescription;
        }
        if (!empty($this->_description)) {
            $descriptions[] = $this->_description;
        }
        return implode(' ', $descriptions);
    }

    /**
     * Returns the product variation id.
     *
     * @return mixed|null
     */
    public function getVariationId()
    {
        return $this->_variationId;
    }

    private function getAttributeValue(Mage_Catalog_Model_Product $product, $attributeName)
    {
        $attribute = $product->getResource()->getAttribute($attributeName);
        if ($attribute instanceof Mage_Catalog_Model_Resource_Eav_Attribute) {
            $attribute_data = $product->getData($attributeName);
            $attribute_value = $product->getAttributeText($attributeName);
            if (empty($attribute_value) && is_scalar($attribute_data)) {
                $attribute_value = $attribute_data;
            }
        } else {
            $attribute_value = null;
        }

        return trim($attribute_value);
    }

    /**
     * @inheritdoc
     */
    public function getSupplierCost()
    {
        return $this->_supplierCost;
    }

    /**
     * @inheritdoc
     */
    public function getInventoryLevel()
    {
        return $this->_inventoryLevel;
    }

    /**
     * @inheritdoc
     */
    public function getReviewCount()
    {
        return $this->_reviewCount;
    }

    /**
     * @inheritdoc
     */
    public function getRatingValue()
    {
        return $this->_ratingValue;
    }

    /**
     * @inheritdoc
     */
    public function getAlternateImageUrls()
    {
        return $this->_alternateImageUrls;
    }

    /**
     * @inheritdoc
     */
    public function getCondition()
    {
        return $this->_condition;
    }

    /**
     * @inheritdoc
     */
    public function getGender()
    {
        return $this->_gender;
    }

    /**
     * @inheritdoc
     */
    public function getAgeGroup()
    {
        return $this->_ageGroup;
    }

    /**
     * @inheritdoc
     */
    public function getGtin()
    {
        return $this->_gtin;
    }

    /**
     * @inheritdoc
     */
    public function getGoogleCategory()
    {
        return $this->_googleCategory;
    }

    /**
     * @inheritdoc
     */
    public function getUnitPricingMeasure()
    {
        return $this->_unitPricingMeasure;
    }

    /**
     * @inheritdoc
     */
    public function getUnitPricingBaseMeasure()
    {
        return $this->_unitPricingBaseMeasure;
    }

    /**
     * @inheritdoc
     */
    public function getUnitPricingUnit()
    {
        return $this->_unitPricingUnit;
    }
}
