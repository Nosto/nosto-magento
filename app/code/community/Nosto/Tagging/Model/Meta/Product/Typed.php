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
 * Data Transfer object representing a typed product.
 * This is used during the order confirmation API request and the product
 * history export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Product_Typed extends Nosto_Tagging_Model_Base implements NostoProductInterface
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
     * @var Nosto_Tagging_Model_Meta_Product_Price_Variation[] the product price variations.
     */
    protected $_priceVariations = array();

    /**
     * @var string|int the variation currently in use.
     */
    protected $_variationId;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_product_typed');
    }

    /**
     * Sets the availability of the product,
     * i.e. if it is in stock or not.
     *
     * @param NostoProductAvailability $availability
     */
    public function setAvailability($availability)
    {
        $this->_availability = $availability;
    }

    /**
     * Sets the product brand name.
     *
     * @param string $brand
     */
    public function setBrand($brand)
    {
        $this->_brand = $brand;
    }

    /**
     * Sets the categories for the product.
     *
     * @param array $categories
     */
    public function setCategories($categories)
    {
        $this->_categories = $categories;
    }

    /**
     * Adds a category to the product
     *
     * @param NostoCategory $category
     */
    public function addCategory(NostoCategory $category)
    {
        $this->_categories[] = $category;
    }

    /**
     * Sets the currency code for the product.
     *
     * @param NostoCurrencyCode $currency
     */
    public function setCurrency($currency)
    {
        $this->_currency = $currency;
    }

    /**
     * Sets the product publication date in the shop.
     *
     * @param NostoDate $datePublished
     */
    public function setDatePublished($datePublished)
    {
        $this->_datePublished = $datePublished;
    }

    /**
     * Sets the product description.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    /**
     * Sets the absolute url the one of the product images in the shop.
     *
     * @param string $imageUrl
     */
    public function setImageUrl($imageUrl)
    {
        $this->_imageUrl = $imageUrl;
    }

    /**
     * Sets the absolute url the one of the product thumbnails in the shop.
     *
     * @param string $thumbUrl
     */
    public function setThumbUrl($thumbUrl)
    {
        $this->_thumbUrl = $thumbUrl;
    }

    /**
     * Sets the list price of the product without discounts but incl taxes.
     *
     * @param NostoPrice $listPrice
     */
    public function setListPrice($listPrice)
    {
        $this->_listPrice = $listPrice;
    }

    /**
     * Sets the name of the product.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Sets the price of the product including possible discounts and taxes.
     *
     * @param NostoPrice $price
     */
    public function setPrice($price)
    {
        $this->_price = $price;
    }

    /**
     * Sets the price variations that exist for this product.
     *
     * @param Nosto_Tagging_Model_Meta_Product_Price_Variation[] $priceVariations
     */
    public function setPriceVariations($priceVariations)
    {
        $this->_priceVariations = $priceVariations;
    }

    /**
     * Sets the short description for this product.
     *
     * @param string $shortDescription
     */
    public function setShortDescription($shortDescription)
    {
        $this->_shortDescription = $shortDescription;
    }


    /**
     * Sets all tags to the `tag1` field.
     *
     * @param array $tags the tags.
     *
     */
    public function setTag1(array $tags)
    {
        $this->_tags['tag1'] = $tags;
    }

    /**
     * Adds a new tag to the `tag1` field.
     *
     * @param string $tag the tag to add.
     *
     */
    public function addTag1($tag)
    {
        $this->_tags['tag1'][] = $tag;
    }

    /**
     * Sets all tags to the `tag2` field.
     *
     * @param array $tags the tags.
     *
     */
    public function setTag2(array $tags)
    {
        $this->_tags['tag2'] = $tags;
    }

    /**
     * Adds a new tag to the `tag2` field.
     *
     * @param string $tag the tag to add.
     */
    public function addTag2($tag)
    {
        $this->_tags['tag2'][] = $tag;
    }

    /**
     * Sets all the tags to the `tag3` field.
     *
     * @param array $tags the tags.
     */
    public function setTag3(array $tags)
    {
        $this->_tags['tag3'] = $tags;
    }

    /**
     * Adds a new tag to the `tag3` field.
     *
     * @param string $tag the tag to add.
     */
    public function addTag3($tag)
    {
        $this->_tags['tag3'][] = $tag;
    }

    /**
     * Sets the absolute url to the product page of the product in the shop.
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->_url = $url;
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
     * Returns the ID of the price variation that is currently in use.
     *
     * @return string the price variation ID.
     */
    public function getPriceVariationId()
    {
        return $this->_variationId;
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
     * Returns the product publication date in the shop.
     *
     * @return NostoDate the date.
     */
    public function getDatePublished()
    {
        return $this->_datePublished;
    }

    /**
     * Returns the product price variations if any exist.
     *
     * @return NostoProductPriceVariationInterface[] the price variations.
     */
    public function getPriceVariations()
    {
        return $this->_priceVariations;
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
     * Returns the ID of the variation that is currently in use. Backwards compatibiity with the SDK
     *
     * @return $this->getPriceVariationId().
     */
    public function getVariationId()
    {
        return $this->getPriceVariationId();
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
     * Sets the tags for a product
     *
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->_tags = $tags;
    }

}
