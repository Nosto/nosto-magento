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
                $price = $product->getPriceModel()
                    ->getTotalPrices($product, 'min', $inclTax);
                break;

            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                // Get the grouped product "starting at" price.
                /** @var $tmpProduct Mage_Catalog_Model_Product */
                $tmpProduct = Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addAttributeToSelect(
                        Mage::getSingleton('catalog/config')
                            ->getProductAttributes()
                    )
                    ->addAttributeToFilter('entity_id', $product->getId())
                    ->setPage(1, 1)
                    ->addMinimalPrice()
                    ->addTaxPercents()
                    ->load()
                    ->getFirstItem();
                if ($tmpProduct) {
                    $price = $tmpProduct->getMinimalPrice();
                    if ($inclTax) {
                        $price = Mage::helper('tax')
                            ->getPrice($tmpProduct, $price, true);
                    }
                }
                break;

            default:
                $price = $finalPrice
                    ? $product->getFinalPrice()
                    : $product->getPrice();
                if ($inclTax) {
                    $price = Mage::helper('tax')
                        ->getPrice($product, $price, true);
                }
                break;
        }

        return $price;
    }
}
