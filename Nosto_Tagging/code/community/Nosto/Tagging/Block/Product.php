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
 * @copyright   Copyright (c) 2013 Nosto Solutions Ltd (http://www.nosto.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product tagging block.
 * Adds meta-data to the HTML document for the currently viewed product.
 *
 * @category    Nosto
 * @package     Nosto_Tagging
 * @author      Nosto Solutions Ltd
 */
class Nosto_Tagging_Block_Product extends Mage_Catalog_Block_Product_Abstract
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
     * Render product info as hidden meta data if the module is enabled for the current store.
     * If it is a "bundle" product with fixed price type, then do not render. These are not supported due to their
     * child products not having prices available.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $product = $this->getProduct();
        if (!Mage::helper('nosto_tagging')->isModuleEnabled()
            || ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE
                && (int)$product->getPriceType() === Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED)
        ) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Return tagging data for the product availability.
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return string
     */
    public function getProductAvailability($product)
    {
        if ($product instanceof Mage_Catalog_Model_Product && $product->isAvailable()) {
            return self::PRODUCT_IN_STOCK;
        } else {
            return self::PRODUCT_OUT_OF_STOCK;
        }
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
    public function getProductCategories($product)
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
