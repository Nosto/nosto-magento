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
        $this->_id = $currencyCode->getCode();
        $this->_currency = $currencyCode;
        $price = $priceHelper->getProductFinalPriceInclTax($product);
        $price = $store->getBaseCurrency()->convert($price, $currency);
        $this->_price = new NostoPrice($price);
        $listPrice = $priceHelper->getProductPriceInclTax($product);
        $listPrice = $store->getBaseCurrency()->convert($listPrice, $currency);
        $this->_listPrice = new NostoPrice($listPrice);
        $this->_availability = new NostoProductAvailability(
            $product->isAvailable()
                ? NostoProductAvailability::IN_STOCK
                : NostoProductAvailability::OUT_OF_STOCK
        );
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
}
