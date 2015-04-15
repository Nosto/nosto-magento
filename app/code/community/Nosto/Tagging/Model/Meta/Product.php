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
 * Meta data class which holds information about a product.
 * This is used during the order confirmation API request and the product
 * history export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Product extends Mage_Core_Model_Abstract implements NostoProductInterface
{
    /**
     * Product "in stock" tagging string.
     */
    const PRODUCT_IN_STOCK = 'InStock';

    /**
     * Product "out of stock" tagging string.
     */
    const PRODUCT_OUT_OF_STOCK = 'OutOfStock';

    /**
     * Product "can be directly added to cart" tag string.
     */
    const PRODUCT_ADD_TO_CART = 'add-to-cart';

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
     * @var string the product publication date in the shop.
     */
    protected $_datePublished;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_product');
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
     * Setter for the absolute url to the product page in the shop frontend.
     *
     * @param $url string the url.
     */
    public function setUrl($url)
    {
        $this->_url = $url;
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
     * Sets the product's unique identifier.
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
     * @return array the tags array, e.g. array("winter", "shoe").
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
     * @return string the date.
     */
    public function getDatePublished()
    {
        return $this->_datePublished;
    }

    /**
     * Loads the product info from a Magento product model.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     */
    public function loadData(Mage_Catalog_Model_Product $product)
    {
        // Unset the cached url first, as it won't include the `___store` param.
        // We need to define the specific store view in the url for the crawler
        // to see the correct product data when crawling the site.
        $this->_url = $product
            ->unsetData('url')
            ->getUrlInStore(array('_ignore_category' => true));

        $this->_productId = $product->getId();
        $this->_name = $product->getName();

        if (!$product->getImage() || $product->getImage() == 'no_selection') {
            $this->_imageUrl = $product->getImageUrl();
        } else {
            $this->_imageUrl = $product->getMediaConfig()
                ->getMediaUrl($product->getImage());
        }

        $this->_price = Mage::helper('tax')->getPrice(
            $product,
            Mage::helper('nosto_tagging/price')->getProductFinalPrice($product),
            true
        );
        $this->_listPrice = Mage::helper('tax')->getPrice(
            $product,
            Mage::helper('nosto_tagging/price')->getProductPrice($product),
            true
        );
        $this->_currencyCode = Mage::app()->getStore()
            ->getCurrentCurrencyCode();

        $this->_availability = $product->isAvailable()
            ? self::PRODUCT_IN_STOCK
            : self::PRODUCT_OUT_OF_STOCK;

        if (Mage::helper('core')->isModuleEnabled('Mage_Tag')) {
            $tagCollection = Mage::getModel('tag/tag')
                ->getCollection()
                ->addPopularity()
                ->addStatusFilter(Mage_Tag_Model_Tag::STATUS_APPROVED)
                ->addProductFilter($product->getId())
                ->setFlag('relation', true)
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->setActiveFilter();
            foreach ($tagCollection as $tag) {
                $this->_tags[] = $tag->getName();
            }
        }

        if (!$product->canConfigure()) {
            $this->_tags[] = self::PRODUCT_ADD_TO_CART;
        }

        $this->_categories = $this->getProductCategories($product);
        $this->_shortDescription = (string)$product->getShortDescription();
        $this->_description = (string)$product->getDescription();
        $this->_brand = $product->getManufacturer()
            ? (string)$product->getAttributeText('manufacturer')
            : '';

        $this->_datePublished = $product->getCreatedAt();
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
    public function getProductCategories(Mage_Catalog_Model_Product $product)
    {
        $data = array();

        if ($product instanceof Mage_Catalog_Model_Product) {
            $categoryCollection = $product->getCategoryCollection();
            foreach ($categoryCollection as $category) {
                $categoryString = Mage::helper('nosto_tagging')
                    ->buildCategoryString($category);
                if (!empty($categoryString)) {
                    $data[] = $categoryString;
                }
            }
        }

        return $data;
    }
}
