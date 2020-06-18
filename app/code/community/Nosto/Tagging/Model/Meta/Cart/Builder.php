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
 * Trait for building the cart
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Cart_Builder
{
    /**
     * Builds
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @param string $currencyCode
     * @return Nosto_Object_Cart_LineItem|null
     * @suppress PhanTypeMismatchReturn
     */
    public static function buildItem(Mage_Sales_Model_Quote_Item $item, $currencyCode)
    {
        $nostoItem = null;
        /** @var Mage_Sales_Model_Quote_Item $item */
        switch ($item->getProductType()) {
            case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE:
            case Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL:
            case Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE:
                /** @var Nosto_Tagging_Model_Meta_Cart_Item_Simple $simpleItem */
                $simpleItem = Mage::getModel(
                    'nosto_tagging/meta_cart_item_simple'
                );
                if ($simpleItem->loadData($item, $currencyCode)) {
                    $nostoItem = $simpleItem;
                }
                break;

            case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                /** @var Nosto_Tagging_Model_Meta_Cart_Item_Configurable $configurableItem */
                $configurableItem = Mage::getModel(
                    'nosto_tagging/meta_cart_item_configurable'
                );
                if ($configurableItem->loadData($item, $currencyCode)) {
                    $nostoItem = $configurableItem;
                }
                break;

            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                /** @var Nosto_Tagging_Model_Meta_Cart_Item_Grouped $groupedItem */
                $groupedItem = Mage::getModel(
                    'nosto_tagging/meta_cart_item_grouped'
                );
                if ($groupedItem->loadData($item, $currencyCode)) {
                    $nostoItem = $groupedItem;
                }
                break;

            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                /** @var Nosto_Tagging_Model_Meta_Cart_Item_Bundled $bundledItem */
                $bundledItem = Mage::getModel(
                    'nosto_tagging/meta_cart_item_bundled'
                );
                if ($bundledItem->loadData($item, $currencyCode)) {
                    $nostoItem = $bundledItem;
                }
                break;
        }

        return $nostoItem;
    }
}
