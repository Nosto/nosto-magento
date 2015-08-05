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
 * Meta data class which holds information about a product price variation.
 * This is used by the the Nosto_Tagging_Model_Meta_Product class.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Product_Price_Variation extends Nosto_Tagging_Model_Base implements NostoProductPriceVariationInterface
{
    /**
     * @var string the price variation ID, e.g. the currency code.
     */
    protected $_id;

    /**
     * @var string the currency code (SIO 4217) for the price variation.
     */
    protected $_currencyCode;

    /**
     * @var float the price of the variation including possible discounts and taxes.
     */
    protected $_price;

    /**
     * @var float the list price of the variation without discounts but incl taxes.
     */
    protected $_listPrice;

    /**
     * @var string the availability of the price variation, i.e. if it is in stock or not.
     */
    protected $_availability;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_product_price_variation');
    }

    /**
     * Loads the price variation data from product and store.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store      $store the store model.
     * @param string                     $currencyCode
     */
    public function loadData(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store = null, $currencyCode)
    {
        $currency = Mage::getModel('directory/currency')->load($currencyCode);
        /** @var Nosto_Tagging_Helper_Price $priceHelper */
        $priceHelper = Mage::helper('nosto_tagging/price');

        $this->_id = strtoupper($currencyCode);
        $this->_currencyCode = strtoupper($currencyCode);
        $price = $priceHelper->getProductFinalPriceInclTax($product);
        $this->_price = $store->getBaseCurrency()->convert($price, $currency);
        $listPrice = $priceHelper->getProductPriceInclTax($product);
        $this->_listPrice = $store->getBaseCurrency()->convert($listPrice, $currency);
        $this->_availability = $product->isAvailable()
            ? Nosto_Tagging_Model_Meta_Product::PRODUCT_IN_STOCK
            : Nosto_Tagging_Model_Meta_Product::PRODUCT_OUT_OF_STOCK;
    }

    /**
     * Returns the price variation ID.
     *
     * @return string the variation id.
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Returns the currency code (SIO 4217) for the price variation.
     *
     * @return string the price currency code.
     */
    public function getCurrencyCode()
    {
        return $this->_currencyCode;
    }

    /**
     * Returns the price of the variation including possible discounts and taxes.
     *
     * @return float the price.
     */
    public function getPrice()
    {
        return $this->_price;
    }

    /**
     * Returns the list price of the variation without discounts but incl taxes.
     *
     * @return float the price.
     */
    public function getListPrice()
    {
        return $this->_listPrice;
    }

    /**
     * Returns the availability of the price variation, i.e. if it is in stock or not.
     *
     * @return string the availability, either "InStock" or "OutOfStock".
     */
    public function getAvailability()
    {
        return $this->_availability;
    }
}
