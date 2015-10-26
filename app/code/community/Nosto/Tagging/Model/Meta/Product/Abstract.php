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
 * Abstract product extended by the following classes:
 * - Nosto_Tagging_Model_Meta_Product
 * - Nosto_Tagging_Model_Meta_Product_Variation
 *
 * This class provides the common properties and their setters/getters.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
abstract class Nosto_Tagging_Model_Meta_Product_Abstract extends Nosto_Tagging_Model_Base
{
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
     * @return array list of category strings, e.g. array("/shoes/winter").
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
     * Sets the absolute url to the product page of the product in the shop.
     *
     * @param string $url the URL.
     *
     * @throws NostoInvalidArgumentException
     */
    public function setUrl($url)
    {
        if (!NostoUri::check($url)) {
            throw new NostoInvalidArgumentException(sprintf('%s.url must be an absolute URL using either the http or https protocol.', __CLASS__));
        }

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
        if (!NostoUri::check($imageUrl)) {
            throw new NostoInvalidArgumentException(sprintf('%s.imageUrl must be an absolute URL using either the http or https protocol.', __CLASS__));
        }

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
        if (!NostoUri::check($thumbUrl)) {
            throw new NostoInvalidArgumentException(sprintf('%s.thumbUrl must be an absolute URL using either the http or https protocol.', __CLASS__));
        }

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
    public function addCategory($category)
    {
        if (!is_string($category) || empty($category)) {
            throw new NostoInvalidArgumentException(sprintf('%s.categories must be an array of non-empty string values.', __CLASS__));
        }

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
}
