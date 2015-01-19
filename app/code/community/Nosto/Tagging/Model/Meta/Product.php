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
 * @copyright Copyright (c) 2013-2015 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Meta data class which holds information about a product.
 * This is used during the order confirmation API request and the product history export.
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
     * @var string the absolute url to the product page in the shop frontend.
     */
    protected $url;

    /**
     * @var string the product's unique identifier.
     */
    protected $productId;

    /**
     * @var string the name of the product.
     */
    protected $name;

    /**
     * @var string the absolute url the one of the product images in shop frontend.
     */
    protected $imageUrl;

    /**
     * @var string the price of the product including possible discounts and taxes.
     */
    protected $price;

    /**
     * @var string the list price of the product without discounts but incl taxes.
     */
    protected $listPrice;

    /**
     * @var string the currency code (ISO 4217) the product is sold in.
     */
    protected $currencyCode;

    /**
     * @var string the availability of the product, i.e. if it is in stock or not.
     */
    protected $availability;

    /**
     * @var array the tags for the product.
     */
    protected $tags = array();

    /**
     * @var array the categories the product is located in.
     */
    protected $categories = array();

    /**
     * @var string the product description.
     */
    protected $description;

    /**
     * @var string the product brand name.
     */
    protected $brand;

    /**
     * @var string the product publication date in the shop.
     */
    protected $datePublished;

    /**
     * Internal Magento constructor.
     *
     * @return void
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
        return $this->url;
    }

    /**
     * Returns the product's unique identifier.
     *
     * @return int|string the ID.
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Returns the name of the product.
     *
     * @return string the name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the absolute url the one of the product images in the shop frontend.
     *
     * @return string the url.
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * Returns the price of the product including possible discounts and taxes.
     *
     * @return float the price.
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Returns the list price of the product without discounts but incl taxes.
     *
     * @return float the price.
     */
    public function getListPrice()
    {
        return $this->listPrice;
    }

    /**
     * Returns the currency code (ISO 4217) the product is sold in.
     *
     * @return string the currency ISO code.
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * Returns the availability of the product, i.e. if it is in stock or not.
     *
     * @return string the availability, either "InStock" or "OutOfStock".
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * Returns the tags for the product.
     *
     * @return array the tags array, e.g. array("winter", "shoe").
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Returns the categories the product is located in.
     *
     * @return array list of category strings, e.g. array("/shoes/winter").
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Returns the product description.
     *
     * @return string the description.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the product brand name.
     *
     * @return string the brand name.
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Returns the product publication date in the shop.
     *
     * @return string the date.
     */
    public function getDatePublished()
    {
        return $this->datePublished;
    }

    /**
     * Loads the product info from a Magento product model.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     *
     * @return void
     */
    public function loadData(Mage_Catalog_Model_Product $product)
    {
        $this->url = $product->getProductUrl();
        $this->productId = $product->getId();
        $this->name = $product->getName();

        if ($product->getImage() == 'no_selection') {
            $this->imageUrl = $product->getImageUrl();
        } else {
            $this->imageUrl = $product->getMediaConfig()
                ->getMediaUrl($product->getImage());
        }

        $this->price = Mage::helper('tax')->getPrice(
            $product,
            Mage::helper('nosto_tagging/price')->getProductFinalPrice($product),
            true
        );
        $this->listPrice = Mage::helper('tax')->getPrice(
            $product,
            Mage::helper('nosto_tagging/price')->getProductPrice($product),
            true
        );
        $this->currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

        if ($product instanceof Mage_Catalog_Model_Product
            && $product->isAvailable()
        ) {
            $this->availability = self::PRODUCT_IN_STOCK;
        } else {
            $this->availability = self::PRODUCT_OUT_OF_STOCK;
        }

        // todo: $this->tags = array();

        $this->categories = $this->getProductCategories($product);
        $this->description = $product->getDescription();
        $this->brand = (string)$product->getAttributeText('manufacturer');
        $this->datePublished = $product->getCreatedAt();
    }

    /**
     * Return array of categories for the product.
     * The items in the array are strings combined of the complete category path to
     * the products own category.
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
