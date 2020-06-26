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
 * @copyright Copyright (c) 2013-2019 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Meta data class which holds information about a product.
 * This is used during the order confirmation API request and the product
 * history export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Product_Builder
{
    /**
     * Builds Nosto product. We use "raw data" for building.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store|null $store
     * @return Nosto_Tagging_Model_Meta_Product|null
     * @throws Mage_Core_Exception
     * @throws Nosto_NostoException
     * @suppress PhanTypeMismatchReturn
     */
    public static function build(
        Mage_Catalog_Model_Product $product,
        Mage_Core_Model_Store $store = null
    ) {
        if ($store === null) {
            /** @var Nosto_Tagging_Helper_Data $helper */
            $helper = Mage::helper('nosto_tagging');
            $store = $helper->getStore();
        }

        /* @var Nosto_Tagging_Model_Meta_Product $nostoProduct */
        $nostoProduct = Mage::getModel('nosto_tagging/meta_product');
        if ($nostoProduct->loadData($product, $store) === false) {
            return null;
        }

        return $nostoProduct;
    }
}
