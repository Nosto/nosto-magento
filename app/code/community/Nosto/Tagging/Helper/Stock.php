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
 * @copyright Copyright (c) 2013-2017 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class for common stock operations.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Stock extends Mage_Core_Helper_Abstract
{
    /**
     * Calculates the total qty in stock. If the product is configurable the
     * the sum of associated products will be calculated.
     *
     * @param Mage_Catalog_Model_Product $product
     *
     * @return int
     */
    public function getQty(Mage_Catalog_Model_Product $product)
    {
        $qty = 0;

        switch ($product->getTypeId()) {
            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                $bundledItemIds = Mage::getResourceSingleton('bundle/selection')
                    ->getChildrenIds($product->getId(), $required=true);
                $products = array();
                foreach ($bundledItemIds as $variants) {
                    if (is_array($variants) && count($variants) > 0) {
                        foreach ($variants as $variantId) {
                            /* @var Mage_Catalog_Model_Product $productModel */
                            $productModel = Mage::getModel('catalog/product')->load(
                                $variantId
                            );
                            $products[] = $productModel;
                        }
                    }
                }
                $qty = $this->getMinQty($products);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                /** @noinspection PhpUndefinedMethodInspection */
                $products = $product->getTypeInstance(true)->getAssociatedProducts($product);
                $qty = $this->getMinQty($products);
                break;
            case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                /** @noinspection PhpUndefinedMethodInspection */
                $products = Mage::getModel(
                    'catalog/product_type_configurable'
                )->getUsedProducts(null, $product);
                $qty = $this->getQtySum($products);
                break;
            default:
                $stockItem = Mage::getModel('cataloginventory/stock_item');
                /** @noinspection PhpUndefinedMethodInspection */
                $qty += $stockItem->loadByProduct($product)->getQty();
                break;
        }

        return $qty;
    }

    /**
     * Searches the minimum quantity from the products collection
     *
     * @param array|Mage_Catalog_Model_Product[] $productCollection

     * @return int|mixed
     */
    protected function getMinQty(array $productCollection)
    {
        $quantities = array();
        $minQty = 0;
        /* @var Mage_Catalog_Model_Product $product */
        foreach ($productCollection as $product) {
            $quantities[] = $this->getQty($product);
        }
        if(!empty($quantities)) {
            rsort($quantities, SORT_NUMERIC);
            $minQty = array_pop($quantities);
        }

        return $minQty;
    }

    /*
     * Sums quantities for all products in array
     *
     * @param array|Mage_Catalog_Model_Product[] $productCollection
     * @return int
     */
    protected function getQtySum(array $productCollection)
    {
        $qty = 0;
        /* @var Mage_Catalog_Model_Product $product */
        foreach ($productCollection as $product) {
            $qty += $this->getQty($product);
        }

        return $qty;
    }
}
