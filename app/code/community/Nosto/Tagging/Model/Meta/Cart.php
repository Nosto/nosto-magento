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
 * Meta data class which holds information about an cart.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Cart extends NostoCart
{
    /**
     * Loads the order info from a Magento quote model.
     *
     * @param Mage_Sales_Model_Quote $quote the quote model.
     */
    public function loadData(Mage_Sales_Model_Quote $quote)
    {
        $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            switch ($item->getProductType()) {
                case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE:
                    /** @var Nosto_Tagging_Model_Meta_Cart_Item_Simple $simpleItem */
                    $simpleItem = Mage::getModel('nosto_tagging/meta_order_item_simple');
                    $simpleItem->loadData($item, $currencyCode);
                    $this->addItem($simpleItem);
                    break;

                case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                    /** @var Nosto_Tagging_Model_Meta_Cart_Item_Configurable $configurableItem */
                    $configurableItem = Mage::getModel('nosto_tagging/meta_order_item_simple');
                    $configurableItem->loadData($item, $currencyCode);
                    $this->addItem($configurableItem);
                    break;

                case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                    /** @var Nosto_Tagging_Model_Meta_Cart_Item_Grouped $groupedItem */
                    $groupedItem = Mage::getModel('nosto_tagging/meta_cart_item_grouped');
                    $groupedItem->loadData($item, $currencyCode);
                    $this->addItem($groupedItem);
                    break;

                case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                    /** @var Nosto_Tagging_Model_Meta_Cart_Item_Bundled $bundledItem */
                    $bundledItem = Mage::getModel('nosto_tagging/meta_cart_item_bundled');
                    $bundledItem->loadData($item, $currencyCode);
                    $this->addItem($bundledItem);
                    break;
            }
        }
    }
}
