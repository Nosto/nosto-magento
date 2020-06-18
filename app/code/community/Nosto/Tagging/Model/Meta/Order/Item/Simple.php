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
 * Meta data class which holds information about a simple item included in an order.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Order_Item_Simple extends Nosto_Tagging_Model_Meta_Order_Item
{

    /**
     * @inheritdoc
     */
    public function buildItemName(Mage_Sales_Model_Order_Item $item)
    {
        $name = $item->getName();
        $optNames = array();

        /** @var Mage_Catalog_Model_Product_Type_Configurable $model */
        $model = Mage::getModel('catalog/product_type_configurable');
        $parentIds = $model->getParentIdsByChild($item->getProductId());
        // If the product has a configurable parent, we assume we should tag
        // the parent. If there are many parent IDs, we are safer to tag the
        // products own name alone.
        if (!empty($parentIds)) {
            $attributes = $item->getBuyRequest()->getData('super_attribute');
            if (is_array($attributes)) {
                foreach ($attributes as $id => $value) {
                    /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
                    $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($id);
                    /** @noinspection PhpUnhandledExceptionInspection */
                    $label = $attribute->getSource()->getOptionText($value);
                    if (!empty($label)) {
                        $optNames[] = $label;
                    }
                }
            }
        }

        $name .= !empty($optNames) ? ' (' . implode(', ', $optNames) . ')' : '';
        return $name;
    }
}
