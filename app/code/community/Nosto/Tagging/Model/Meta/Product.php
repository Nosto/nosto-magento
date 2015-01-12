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
 * @category    Nosto
 * @package     Nosto_Tagging
 * @copyright   Copyright (c) 2015 Nosto Solutions Ltd (http://www.nosto.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Meta data class which holds information about a product.
 * This is used during the order confirmation API request and the product history export.
 *
 * @category    Nosto
 * @package     Nosto_Tagging
 * @author      Nosto Solutions Ltd
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
	 * @var string the absolute url the one of the product images in the shop frontend.
	 */
	protected $imageUrl;

	/**
	 * @var string the price of the product including possible discounts and taxes.
	 */
	protected $price;

	/**
	 * @var string the list price of the product without discounts but including possible taxes.
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
	 * @inheritdoc
	 */
	protected function _construct()
	{
		$this->_init('nosto_tagging/meta_product');
	}

	/**
	 * @inheritdoc
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @inheritdoc
	 */
	public function getProductId()
	{
		return $this->productId;
	}

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @inheritdoc
	 */
	public function getImageUrl()
	{
		return $this->imageUrl;
	}

	/**
	 * @inheritdoc
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @inheritdoc
	 */
	public function getListPrice()
	{
		return $this->listPrice;
	}

	/**
	 * @inheritdoc
	 */
	public function getCurrencyCode()
	{
		return $this->currencyCode;
	}

	/**
	 * @inheritdoc
	 */
	public function getAvailability()
	{
		return $this->availability;
	}

	/**
	 * @inheritdoc
	 */
	public function getTags()
	{
		return $this->tags;
	}

	/**
	 * @inheritdoc
	 */
	public function getCategories()
	{
		return $this->categories;
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @inheritdoc
	 */
	public function getBrand()
	{
		return $this->brand;
	}

	/**
	 * @inheritdoc
	 */
	public function getDatePublished()
	{
		return $this->datePublished;
	}

	/**
	 * Loads the product info from a Magento product model.
	 *
	 * @param Mage_Catalog_Model_Product $product the product model.
	 */
	public function loadData(Mage_Catalog_Model_Product $product)
	{
		$this->url = $product->getProductUrl();
		$this->productId = $product->getId();
		$this->name = $product->getName();

		if ($product->getImage() == 'no_selection') {
			$this->imageUrl = $product->getImageUrl();
		} else {
			$this->imageUrl = $product->getMediaConfig()->getMediaUrl($product->getImage());
		}

		$this->price = Mage::helper('tax')->getPrice($product, Mage::helper('nosto_tagging/price')->getProductFinalPrice($product), true);
		$this->listPrice = Mage::helper('tax')->getPrice($product, Mage::helper('nosto_tagging/price')->getProductPrice($product), true);
		$this->currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

		if ($product instanceof Mage_Catalog_Model_Product && $product->isAvailable()) {
			$this->availability = self::PRODUCT_IN_STOCK;
		} else {
			$this->availability = self::PRODUCT_OUT_OF_STOCK;
		}

		// todo: $this->tags = array();

		$this->categories = $this->getProductCategories($product);
		$this->description = $product->getDescription();
		$this->brand = $product->getAttributeText('manufacturer');
		$this->datePublished = $product->getCreatedAt();

	}

	/**
	 * Return array of categories for the product.
	 * The items in the array are strings combined of the complete category path to the products own category.
	 *
	 * Structure:
	 * array (
	 *     /Electronics/Computers
	 * )
	 *
	 * @param Mage_Catalog_Model_Product $product
	 *
	 * @return array
	 */
	public function getProductCategories(Mage_Catalog_Model_Product $product)
	{
		$data = array();

		if ($product instanceof Mage_Catalog_Model_Product) {
			$categoryCollection = $product->getCategoryCollection();
			foreach ($categoryCollection as $category) {
				$categoryString = Mage::helper('nosto_tagging')->buildCategoryString($category);
				if (!empty($categoryString)) {
					$data[] = $categoryString;
				}
			}
		}

		return $data;
	}
}
