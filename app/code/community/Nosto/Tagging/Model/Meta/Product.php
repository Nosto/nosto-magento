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
 * Data Transfer object representing a product.
 * This is used during the order confirmation API request and the product
 * history export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Product extends Nosto_Tagging_Model_Base implements NostoProductInterface
{
    /**
     * Product "can be directly added to cart" tag string.
     */
    const PRODUCT_ADD_TO_CART = 'add-to-cart';

    /**
     * @var string the product's unique identifier.
     */
    protected $_productId;

    /**
     * @var string the absolute url to the product page in the shop frontend.
     */
    protected $_url;

    /**
     * @var string the name of the product.
     */
    protected $_name;

    /**
     * @var string the absolute url the one of the product images in frontend.
     */
    protected $_imageUrl;

    /**
     * @var string the absolute url the one of the product thumbnails in frontend.
     */
    protected $_thumbUrl;

    /**
     * @var NostoPrice the product price including possible discounts and taxes.
     */
    protected $_price;

    /**
     * @var NostoPrice the product list price without discounts but incl taxes.
     */
    protected $_listPrice;

    /**
     * @var NostoCurrencyCode the currency code the product is sold in.
     */
    protected $_currency;

    /**
     * @var NostoProductAvailability the availability of the product.
     */
    protected $_availability;

    /**
     * @var array the tags for the product.
     */
    protected $_tags = array(
        'tag1' => array(),
        'tag2' => array(),
        'tag3' => array(),
    );

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
     * @var NostoDate the product publication date in the shop.
     */
    protected $_datePublished;

    /**
     * @var string|int the variation currently in use.
     */
    protected $_variationId;

    /**
     * @var Nosto_Tagging_Model_Meta_Product_Price_Variation[] the product variations.
     */
    protected $_priceVariations = array();

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_product');
    }

    /**
     * Loads the Data Transfer object.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store|null $store the store to get the product data for.
     */
    public function loadData(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store = null)
    {
        if (is_null($store)) {
            $store = Mage::app()->getStore();
        }

        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        /** @var Nosto_Tagging_Helper_Price $priceHelper */
        $priceHelper = Mage::helper('nosto_tagging/price');
        $this->setUrl($this->buildUrl($product, $store));
        $this->setProductId($product->getId());
        $this->setName($product->getName());
        $this->setImageUrl($this->buildImageUrl($product, $store));
        $price = $priceHelper->convertToDefaultCurrency($priceHelper->getProductFinalPriceInclTax($product), $store);
        $this->setPrice(new NostoPrice($price));
        $listPrice = $priceHelper->convertToDefaultCurrency($priceHelper->getProductPriceInclTax($product), $store);
        $this->setListPrice(new NostoPrice($listPrice));
        $this->setCurrency(new NostoCurrencyCode($store->getDefaultCurrencyCode()));
        $this->setAvailability(new NostoProductAvailability(
            $product->isAvailable()
                ? NostoProductAvailability::IN_STOCK
                : NostoProductAvailability::OUT_OF_STOCK
        ));
        $this->setCategories($this->buildCategories($product));

        // Optional properties.

        $descriptions = array();
        if ($product->hasData('short_description')) {
            $descriptions[] = $product->getData('short_description');
        }
        if ($product->hasData('description')) {
            $descriptions[] = $product->getData('description');
        }
        if (count($descriptions) > 0) {
            $this->setDescription(implode(' ', $descriptions));
        }

        if ($product->hasData('manufacturer')) {
            $this->setBrand($product->getAttributeText('manufacturer'));
        }
        if (($tags = $this->buildTags($product, $store)) !== array()) {
            $this->setTag1($tags);
        }

        if ($product->hasData('created_at')) {
            if (($timestamp = strtotime($product->getData('created_at')))) {
                $this->setDatePublished(new NostoDate($timestamp));
            }
        }

        if ($helper->isMultiCurrencyMethodPriceVariation($store)) {
            $this->setPriceVariationId($store->getDefaultCurrencyCode());
            if ($helper->isMultiCurrencyMethodPriceVariation($store)) {
                $this->setPriceVariations($this->buildPriceVariations($product, $store));
            }
        }
    }

    /**
     * Build the product price variations.
     *
     * These are the different prices for the product's supported currencies.
     * Only used when the multi currency method is set to 'priceVariation'.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store      $store the store model.
     *
     * @return array
     */
    protected function buildPriceVariations(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        $variations = array();
        $currencyCodes = $store->getAvailableCurrencyCodes(true);
        foreach ($currencyCodes as $currencyCode) {
            // Skip base currency.
            if ($currencyCode === $store->getDefaultCurrencyCode()) {
                continue;
            }
            try {
                /** @var Nosto_Tagging_Model_Meta_Product_Price_Variation $variation */
                $variation = Mage::getModel('nosto_tagging/meta_product_price_variation');
                $variation->loadData($product, $store, new NostoCurrencyCode($currencyCode));
                $variations[] = $variation;
            } catch (Exception $e) {
                // The price variation cannot be obtained if there are no
                // exchange rates defined for the currency and Magento will
                // throw and exception. Just ignore this and continue.
                continue;
            }
        }
        return $variations;
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
            $tags[] = self::PRODUCT_ADD_TO_CART;
        }

        return $tags;
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
        // Unset the cached url first, as it won't include the `___store` param
        // if it's cached. We need to define the specific store view in the url
        // in case the same domain is used for all sites.
        $product->unsetData('url');
        return $product
            ->getUrlInStore(
                array(
                    '_nosid' => true,
                    '_ignore_category' => true,
                    '_store' => $store->getCode(),
                )
            );
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
                $category = Mage::getModel('nosto_tagging/meta_category');
                try {
                    $category->setPath($categoryString);
                } catch (NostoInvalidArgumentException $e) {
                    $category->setPath('');
                }
                $data[] = $category;
            }
        }

        return $data;
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
     * Returns the product's unique identifier.
     *
     * @return int|string the ID.
     */
    public function getProductId()
    {
        return $this->_productId;
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
     * Returns the absolute url to one of the product image thumbnails in the shop frontend.
     *
     * @return string the url.
     */
    public function getThumbUrl()
    {
        return $this->_thumbUrl;
    }

    /**
     * Returns the price of the product including possible discounts and taxes.
     *
     * @return NostoPrice the price.
     */
    public function getPrice()
    {
        return $this->_price;
    }

    /**
     * Returns the list price of the product without discounts but incl taxes.
     *
     * @return NostoPrice the price.
     */
    public function getListPrice()
    {
        return $this->_listPrice;
    }

    /**
     * Returns the currency code (ISO 4217) the product is sold in.
     *
     * @return NostoCurrencyCode the currency ISO code.
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     * Returns the availability of the product, i.e. if it is in stock or not.
     *
     * @return NostoProductAvailability the availability
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
     * @return array list of Nosto_Tagging_Meta_Category objects.
     */
    public function getCategories()
    {
        return $this->_categories;
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
     * Returns the product publication date in the shop.
     *
     * @return NostoDate the date.
     */
    public function getDatePublished()
    {
        return $this->_datePublished;
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
     * Returns the ID of the variation that is currently in use.
     *
     * @return string the variation ID.
     */
    public function getPriceVariationId()
    {
        return $this->_variationId;
    }

    /**
     * Returns the ID of the variation that is currently in use. Backwards compatibiity with the SDK
     *
     * @return $this->getPriceVariationId().
     */
    public function getVariationId()
    {
        return $this->getPriceVariationId();
    }

    /**
     * Returns the product variations if any exist.
     *
     * @return NostoProductVariationInterface[] the variations.
     */
    public function getPriceVariations()
    {
        return $this->_priceVariations;
    }

    /**
     * Returns the product variations if any exist. Backwards compatibility with the SDK.
     *
     * @return $this->getPriceVariations()
     */
    public function getVariations()
    {
        return $this->getPriceVariations();
    }

    /**
     * Returns the product description.
     *
     * @return string the description.
     */
    public function getShortDescription()
    {
        return $this->_shortDescription;
    }

    /**
     * Sets the absolute url to the product page of the product in the shop.
     *
     * @param string $url the URL.
     *
     * @throws NostoInvalidArgumentException
     */
    public function setUrl($url)
    {
        $this->_url = $url;
    }

    /**
     * Sets the name of the product.
     *
     * @param string $name the name.
     *
     * @throws NostoInvalidArgumentException
     */
    public function setName($name)
    {
        if (!is_string($name) || empty($name)) {
            throw new NostoInvalidArgumentException(sprintf('%s.name must be a non-empty string value.', __CLASS__));
        }

        $this->_name = $name;
    }

    /**
     * Sets the absolute url the one of the product images in the shop.
     *
     * @param string $imageUrl the image url.
     *
     * @throws NostoInvalidArgumentException
     */
    public function setImageUrl($imageUrl)
    {
        $this->_imageUrl = $imageUrl;
    }

    /**
     * Sets the absolute url the one of the product thumbnails in the shop.
     *
     * @param string $thumbUrl the thumbnail url.
     *
     * @throws NostoInvalidArgumentException
     */
    public function setThumbUrl($thumbUrl)
    {
        $this->_thumbUrl = $thumbUrl;
    }

    /**
     * Sets the price of the product including possible discounts and taxes.
     *
     * @param NostoPrice $price the price.
     */
    public function setPrice(NostoPrice $price)
    {
        $this->_price = $price;
    }

    /**
     * Sets the list price of the product without discounts but incl taxes.
     *
     * @param NostoPrice $listPrice the list price.
     */
    public function setListPrice(NostoPrice $listPrice)
    {
        $this->_listPrice = $listPrice;
    }

    /**
     * Sets the currency code (ISO 4217) for the product.
     *
     * @param NostoCurrencyCode $currencyCode the product price currency.
     */
    public function setCurrency(NostoCurrencyCode $currencyCode)
    {
        $this->_currency = $currencyCode;
    }

    /**
     * Sets the availability of the product,
     * i.e. if it is in stock or not.
     *
     * @param NostoProductAvailability $availability the availability.
     */
    public function setAvailability(NostoProductAvailability $availability)
    {
        $this->_availability = $availability;
    }

    /**
     * Sets all the tags to the `tag1` field.
     *
     * @param array $tags the tags.
     *
     * @throws NostoInvalidArgumentException
     */
    public function setTag1(array $tags)
    {
        $this->_tags['tag1'] = array();
        foreach ($tags as $tag) {
            $this->addTag1($tag);
        }
    }

    /**
     * Adds a new tag to the `tag1` field.
     *
     * @param string $tag the tag to add.
     *
     * @throws NostoInvalidArgumentException
     */
    public function addTag1($tag)
    {
        if (!is_string($tag) || empty($tag)) {
            throw new NostoInvalidArgumentException(sprintf('%s.tag1 must be an array of non-empty string values.', __CLASS__));
        }

        $this->_tags['tag1'][] = $tag;
    }

    /**
     * Sets all the tags to the `tag2` field.
     *
     * @param array $tags the tags.
     *
     * @throws NostoInvalidArgumentException
     */
    public function setTag2(array $tags)
    {
        $this->_tags['tag2'] = array();
        foreach ($tags as $tag) {
            $this->addTag2($tag);
        }
    }

    /**
     * Adds a new tag to the `tag2` field.
     *
     * @param string $tag the tag to add.
     *
     * @throws NostoInvalidArgumentException
     */
    public function addTag2($tag)
    {
        if (!is_string($tag) || empty($tag)) {
            throw new NostoInvalidArgumentException(sprintf('%s.tag2 must be an array of non-empty string values.', __CLASS__));
        }

        $this->_tags['tag2'][] = $tag;
    }

    /**
     * Sets all the tags to the `tag3` field.
     *
     * @param array $tags the tags.
     *
     * @throws NostoInvalidArgumentException
     */
    public function setTag3(array $tags)
    {
        $this->_tags['tag3'] = array();
        foreach ($tags as $tag) {
            $this->addTag3($tag);
        }
    }

    /**
     * Adds a new tag to the `tag3` field.
     *
     * @param string $tag the tag to add.
     *
     * @throws NostoInvalidArgumentException
     */
    public function addTag3($tag)
    {
        if (!is_string($tag) || empty($tag)) {
            throw new NostoInvalidArgumentException(sprintf('%s.tag3 must be an array of non-empty string values.', __CLASS__));
        }

        $this->_tags['tag3'][] = $tag;
    }

    /**
     * Sets the categories for the product.
     *
     * @param array $categories the categories.
     *
     * @throws NostoInvalidArgumentException
     */
    public function setCategories(array $categories)
    {
        $this->_categories = array();
        foreach ($categories as $category) {
            $this->addCategory($category);
        }
    }

    /**
     * Adds a category for the product.
     *
     * @param string $category the category to add.
     *
     * @throws NostoInvalidArgumentException
     */
    public function addCategory(Nosto_Tagging_Model_Meta_Category $category)
    {
        $this->_categories[] = $category;
    }

    /**
     * Sets the product description.
     *
     * @param string $description the description.
     *
     * @throws NostoInvalidArgumentException
     */
    public function setDescription($description)
    {
        if (!is_string($description) || empty($description)) {
            throw new NostoInvalidArgumentException(sprintf('%s.description must be a non-empty string value.', __CLASS__));
        }

        $this->_description = $description;
    }

    /**
     * Sets the product brand name.
     *
     * @param string $brand the brand name.
     *
     * @throws NostoInvalidArgumentException
     */
    public function setBrand($brand)
    {
        if (!is_string($brand) || empty($brand)) {
            throw new NostoInvalidArgumentException(sprintf('%s.brand must be a non-empty string value.', __CLASS__));
        }

        $this->_brand = $brand;
    }

    /**
     * Sets the product publication date in the shop.
     *
     * @param NostoDate $datePublished the published date.
     */
    public function setDatePublished(NostoDate $datePublished)
    {
        $this->_datePublished = $datePublished;
    }

    /**
     * Sets the variation the current product prices are displayed in.
     *
     * @param string|int $variationId the variation ID.
     */
    public function setPriceVariationId($variationId)
    {
        $this->_variationId = $variationId;
    }

    /**
     * Sets the variations that exist for this product.
     *
     * @param Nosto_Tagging_Model_Meta_Product_Price_Variation[] $variations the variations.
     */
    public function setPriceVariations(array $variations)
    {
        $this->_priceVariations = array();
        foreach ($variations as $variation) {
            $this->addVariation($variation);
        }
    }

    /**
     * Adds a variation for this product.
     *
     * @param Nosto_Tagging_Model_Meta_Product_Price_Variation $variation the variation.
     */
    public function addVariation(Nosto_Tagging_Model_Meta_Product_Price_Variation $variation)
    {
        $this->_priceVariations[] = $variation;
    }

    /**
     * Sets the short description for this product.
     *
     * @param $shortDescription
     */
    public function setShortDescription($shortDescription)
    {
        $this->_shortDescription = $shortDescription;
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

}
