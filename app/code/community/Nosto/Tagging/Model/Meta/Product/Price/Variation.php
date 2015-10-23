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
 * Data Transfer object representing a product price variation.
 * This is used by the the Nosto_Tagging_Model_Meta_Product class.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Product_Price_Variation extends Nosto_Tagging_Model_Base implements NostoProductPriceVariationInterface
{
    /**
     * @var NostoPriceVariation the price variation ID, e.g. the currency code.
     */
    protected $_id;

    /**
     * @var NostoCurrencyCode the currency code (ISO 4217) for the price variation.
     */
    protected $_currency;

    /**
     * @var NostoPrice the price of the variation including possible discounts and taxes.
     */
    protected $_price;

    /**
     * @var NostoPrice the list price of the variation without discounts but incl taxes.
     */
    protected $_listPrice;

    /**
     * @var NostoProductAvailability the availability of the price variation.
     */
    protected $_availability;

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
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_product_price_variation');
    }

    /**
     * Loads the Data Transfer Object.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store      $store the store model.
     * @param NostoCurrencyCode          $currencyCode the currency code.
     */
    public function loadData(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store, NostoCurrencyCode $currencyCode)
    {
        $currency = Mage::getModel('directory/currency')
            ->load($currencyCode->getCode());
        /** @var Nosto_Tagging_Helper_Price $priceHelper */
        $priceHelper = Mage::helper('nosto_tagging/price');

        $this->setId(new NostoPriceVariation($currencyCode->getCode()));
        $this->setCurrency($currency);
        $price = $priceHelper->getProductFinalPriceInclTax($product);
        $price = $store->getBaseCurrency()->convert($price, $currency);
        $this->setPrice(new NostoPrice($price));
        $listPrice = $priceHelper->getProductPriceInclTax($product);
        $listPrice = $store->getBaseCurrency()->convert($listPrice, $currency);
        $this->setListPrice(new NostoPrice($listPrice));
        $this->setAvailability(new NostoProductAvailability(
            $product->isAvailable()
                ? NostoProductAvailability::IN_STOCK
                : NostoProductAvailability::OUT_OF_STOCK
        ));

    }

    /**
     * Returns the price variation ID.
     *
     * @return NostoPriceVariation the variation ID.
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Returns the currency code (ISO 4217) for the price variation.
     *
     * @return NostoCurrencyCode the price currency code.
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     * Returns the price of the variation including possible discounts and taxes.
     *
     * @return NostoPrice the price.
     */
    public function getPrice()
    {
        return $this->_price;
    }

    /**
     * Returns the list price of the variation without discounts but incl taxes.
     *
     * @return NostoPrice the price.
     */
    public function getListPrice()
    {
        return $this->_listPrice;
    }

    /**
     * Returns the availability of the price variation, i.e. if it is in stock or not.
     *
     * @return NostoProductAvailability the availability.
     */
    public function getAvailability()
    {
        return $this->_availability;
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
        return null;
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
     * Setter for Id
     * @param string $id
     * @return void
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * Setter for currency
     * @param NostoCurrencyCode $currencyCode
     * @return void
     */
    public function setCurrency(NostoCurrencyCode $currencyCode)
    {
        $this->_currency = $currencyCode;
    }

    /**
     * Setter for price
     * @param NostoPrice $price
     * @return void
     */
    public function setPrice(NostoPrice $price)
    {
        $this->_price = $price;
    }

    /**
     * Setter for listPrice
     * @param NostoPrice $listPrice
     * @return void
     */
    public function setListPrice(NostoPrice $listPrice)
    {
        $this->_listPrice = $listPrice;
    }

    /**
     * Setter for availability
     * @param NostoProductAvailability $availability
     * @return void
     */
    public function setAvailability(NostoProductAvailability $availability)
    {
        $this->_availability = $availability;
    }

    /**
     * Setter for url
     * @param string $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->_url = $url;
    }

    /**
     * Setter for name
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Setter for imageUrl
     * @param string $imageUrl
     * @return void
     */
    public function setImageUrl($imageUrl)
    {
        $this->_imageUrl = $imageUrl;
    }

    /**
     * Setter for shortDescription
     * @param string $shortDescription
     * @return void
     */
    public function setShortDescription($shortDescription)
    {
        $this->_shortDescription = $shortDescription;
    }

    /**
     * Setter for description
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    /**
     * Setter for brand
     * @param string $brand
     * @return void
     */
    public function setBrand($brand)
    {
        $this->_brand = $brand;
    }

    /**
     * Setter for datePublished
     * @param NostoDate $datePublished
     * @return void
     */
    public function setDatePublished(NostoDate $datePublished)
    {
        $this->_datePublished = $datePublished;
    }

    /**
     * Adder for category
     * @param string $category
     * @return void
     */
    public function addCategory($category)
    {
        $this->_categories[] = $category;
    }

    /**
     * Sets all the tags to the `tag1` field.
     *
     * The tags must be an array of non-empty string values.
     *
     * Usage:
     * $object->setTag1(array('customTag1', 'customTag2'));
     *
     * @param array $tags the tags.
     *
     * @throws InvalidArgumentException
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
     * The tag must be a non-empty string value.
     *
     * Usage:
     * $object->addTag1('customTag');
     *
     * @param string $tag the tag to add.
     *
     * @throws InvalidArgumentException
     */
    public function addTag1($tag)
    {
        if (!is_string($tag) || empty($tag)) {
            throw new NostoInvalidArgumentException('Tag must be a non-empty string value.');
        }
        $this->_tags['tag1'][] = $tag;
    }
    /**
     * Sets all the tags to the `tag2` field.
     *
     * The tags must be an array of non-empty string values.
     *
     * Usage:
     * $object->setTag2(array('customTag1', 'customTag2'));
     *
     * @param array $tags the tags.
     *
     * @throws InvalidArgumentException
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
     * The tag must be a non-empty string value.
     *
     * Usage:
     * $object->addTag2('customTag');
     *
     * @param string $tag the tag to add.
     *
     * @throws InvalidArgumentException
     */
    public function addTag2($tag)
    {
        if (!is_string($tag) || empty($tag)) {
            throw new InvalidArgumentException('Tag must be a non-empty string value.');
        }
        $this->_tags['tag2'][] = $tag;
    }
    /**
     * Sets all the tags to the `tag3` field.
     *
     * The tags must be an array of non-empty string values.
     *
     * Usage:
     * $object->setTag3(array('customTag1', 'customTag2'));
     *
     * @param array $tags the tags.
     *
     * @throws InvalidArgumentException
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
     * The tag must be a non-empty string value.
     *
     * Usage:
     * $object->addTag3('customTag');
     *
     * @param string $tag the tag to add.
     *
     * @throws InvalidArgumentException
     */
    public function addTag3($tag)
    {
        if (!is_string($tag) || empty($tag)) {
            throw new InvalidArgumentException('Tag must be a non-empty string value.');
        }
        $this->_tags['tag3'][] = $tag;
    }

}