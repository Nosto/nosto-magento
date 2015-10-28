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
 * Data Transfer object representing a product variation.
 * This is used by the the Nosto_Tagging_Model_Meta_Product class.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Product_Variation extends Nosto_Tagging_Model_Base implements NostoProductVariationInterface
{
    /**
     * @var string|int the variation ID.
     */
    protected $_variationId;

    /**
     * @var NostoCurrencyCode the currency code the product is sold in.
     */
    protected $_currency;

    /**
     * @var NostoPrice the product price including possible discounts and taxes.
     */
    protected $_price;

    /**
     * @var NostoPrice the product list price without discounts but incl taxes.
     */
    protected $_listPrice;

    /**
     * @var NostoProductAvailability the availability of the product.
     */
    protected $_availability;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_product_variation');
    }

    /**
     * Returns the variation ID.
     *
     * @return string|int the variation ID.
     */
    public function getVariationId()
    {
        return $this->_variationId;
    }

    /**
     * Returns the currency code (ISO 4217) the variation is sold in.
     *
     * @return NostoCurrencyCode the currency ISO code.
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
     * Returns the availability of the variation, i.e. if it is in stock or not.
     *
     * @return NostoProductAvailability the availability
     */
    public function getAvailability()
    {
        return $this->_availability;
    }

    /**
     * Sets the variation ID.
     *
     * @param string|int $variationId the variation ID.
     */
    public function setVariationId($variationId)
    {
        $this->_variationId = $variationId;
    }

    /**
     * Sets the currency code (ISO 4217) for the variation.
     *
     * @param NostoCurrencyCode $currencyCode the variation price currency.
     */
    public function setCurrency(NostoCurrencyCode $currencyCode)
    {
        $this->_currency = $currencyCode;
    }

    /**
     * Sets the price of the variation including possible discounts and taxes.
     *
     * @param NostoPrice $price the price.
     */
    public function setPrice(NostoPrice $price)
    {
        $this->_price = $price;
    }

    /**
     * Sets the list price of the variation without discounts but incl taxes.
     *
     * @param NostoPrice $listPrice the list price.
     */
    public function setListPrice(NostoPrice $listPrice)
    {
        $this->_listPrice = $listPrice;
    }

    /**
     * Sets the availability of the variation,
     * i.e. if it is in stock or not.
     *
     * @param NostoProductAvailability $availability the availability.
     */
    public function setAvailability(NostoProductAvailability $availability)
    {
        $this->_availability = $availability;
    }
}
