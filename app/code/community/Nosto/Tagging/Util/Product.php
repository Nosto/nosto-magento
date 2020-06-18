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
 * @copyright Copyright (c) 2013-2020 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Util class for product handling
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Util_Product
{
    /**
     * Helper method to fetch all parent products for a simple product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public static function buildParentProducts(Mage_Catalog_Model_Product $product)
    {
        $parents = array();
        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            /** @var Mage_Catalog_Model_Product_Type_Configurable $model */
            $model = Mage::getModel('catalog/product_type_configurable');
            $parentIds = $model->getParentIdsByChild($product->getId());
            if (!empty($parentIds)) {
                foreach ($parentIds as $productId) {
                    $configurable = Mage::getModel('catalog/product')->load($productId);
                    $parents[] = $configurable;
                }
            }
        }

        return $parents;
    }

    /**
     * Helper method to check if the product has parent products & return an array
     * of products that Nosto should handle. Note that this method returns the given
     * product in array if no other parents were found.
     *
     * @param Mage_Catalog_Model_Product $product
     * @suppress PhanUndeclaredClassInstanceof
     * @suppress PhanTypeMismatchArgument
     * @return array
     */
    public static function toParentProducts(Mage_Catalog_Model_Product $product)
    {
        $parents = array();
        if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            /** @var Mage_Catalog_Model_Product_Type_Configurable $model */
            $model = Mage::getModel('catalog/product_type_configurable');
            $parentIds = $model->getParentIdsByChild($product->getId());
            if (!empty($parentIds)) {
                foreach ($parentIds as $productId) {
                    try {
                        $configurable = Mage::getModel('catalog/product')->load($productId);
                        $parents[] = $configurable;
                    } catch (\Exception $e) {
                        if ($e instanceof \Enterprise_AdminGws_Controller_Exception) {
                            Nosto_Tagging_Helper_Log::exception($e);
                        }
                    }
                }
            }
        }

        if (empty($parents)) {
            $parents[] = $product;
        }

        return $parents;
    }
}
