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
class Nosto_Tagging_Model_Meta_Product_Variation extends Nosto_Tagging_Model_Meta_Product_Abstract implements NostoProductVariationInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_product_variation');
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

        $this->setProductId($currencyCode->getCode());
        $this->setCurrency($currencyCode);
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
     * Returns the variation ID.
     *
     * @return string|int the variation ID.
     */
    public function getVariationId()
    {
        return $this->_productId;
    }
}
