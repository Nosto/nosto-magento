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
 * @copyright Copyright (c) 2013-2016 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class for common price operations.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Price extends Mage_Core_Helper_Abstract
{
    /**
     * Formats price into Nosto format, e.g. 1000.99.
     *
     * @param string|int|float $price the price to format.
     *
     * @return string
     */
    public function getFormattedPrice($price)
    {
        return number_format($price, 2, '.', '');
    }

    /**
     * Gets the unit price for a product model including taxes.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     *
     * @return float
     */
    public function getProductPriceInclTax($product)
    {
        return $this->_getProductPrice($product, false, true);
    }

    /**
     * Get the final price for a product model including taxes.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     *
     * @return float
     */
    public function getProductFinalPriceInclTax($product)
    {
        return $this->_getProductPrice($product, true, true);
    }

    /**
     * Get unit/final price for a product model.
     *
     * @param Mage_Catalog_Model_Product $product    the product model.
     * @param bool                       $finalPrice if final price.
     * @param bool                       $inclTax    if tax is to be included.
     *
     * @return float
     */
    protected function _getProductPrice($product, $finalPrice = false, $inclTax = true)
    {
        $price = 0;

        switch ($product->getTypeId()) {
            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                // Get the bundle product "from" price.
                /** @var Mage_Bundle_Model_Product_Price $model */
                $model = $product->getPriceModel();
                $price = $model->getTotalPrices($product, 'min', $inclTax);
                break;

            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                // Get the grouped product "starting at" price.
                /** @var Mage_Catalog_Model_Config $config */
                $config = Mage::getSingleton('catalog/config');
                /** @var $tmpProduct Mage_Catalog_Model_Product */
                $tmpProduct = Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addAttributeToSelect(
                        $config->getProductAttributes()
                    )
                    ->addAttributeToFilter('entity_id', $product->getId())
                    ->setPage(1, 1)
                    ->addMinimalPrice()
                    ->addTaxPercents()
                    ->load()
                    ->getFirstItem();
                if ($tmpProduct) {
                    /** @var Mage_Tax_Helper_Data $helper */
                    $helper = Mage::helper('tax');
                    $price = $tmpProduct->getMinimalPrice();
                    if ($inclTax) {
                        $price = $helper->getPrice($tmpProduct, $price, true);
                    }
                }
                break;

            default:
                /** @var Mage_Tax_Helper_Data $helper */
                $helper = Mage::helper('tax');
                $price = $finalPrice
                    ? $product->getFinalPrice()
                    : $product->getPrice();
                if ($inclTax) {
                    $price = $helper->getPrice($product, $price, true);
                }
                break;
        }

        return $price;
    }

    /**
     * @param float $price
     * @param Mage_Core_Model_Store $store
     * @return float
     */
    public function convertToDefaultCurrency($price, Mage_Core_Model_Store $store)
    {
        if (!is_numeric($price)) {
            Mage::log(
                sprintf(
                    'price must be a numeric value in %s, got %s.',
                    __CLASS__,
                    $price
                ),
                Zend_Log::WARN,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );
            $price = 0;
        }
        /** @var Mage_Directory_Helper_Data $helper */
        $helper = Mage::helper('directory');
        return $helper->currencyConvert(
            $price,
            $store->getBaseCurrency()->getCode(),
            $store->getDefaultCurrency()->getCode()
        );
    }

    /**
     * Get the final price in base currency for an ordered item including
     * taxes as discounts.
     *
     * @param Mage_Sales_Model_Order_Item $item the item model.
     *
     * @return float
     */
    public function getItemFinalPriceInclTax(Mage_Sales_Model_Order_Item $item)
    {
        /** @var Mage_Directory_Helper_Data $helper */
        $helper = Mage::helper('directory');
        $quantity = (double)$item->getQtyOrdered();
        $basePrice = $item->getBaseRowTotal() + $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount() - $item->getBaseDiscountAmount();
        $orderCurrencyCode = $item->getOrder()->getOrderCurrencyCode();
        $baseCurrencyCode = $item->getOrder()->getBaseCurrencyCode();
        if ($orderCurrencyCode != $baseCurrencyCode) {
            $priceInOrderCurrency = $helper->currencyConvert(
                $basePrice,
                $baseCurrencyCode,
                $orderCurrencyCode
            );
        } else {
            $priceInOrderCurrency = $basePrice;
        }
        if ($quantity > 1) {
            $priceInOrderCurrency = round($priceInOrderCurrency/$quantity, 2);
        }

        return $priceInOrderCurrency;
    }
}
