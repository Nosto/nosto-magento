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
 * Meta data class which holds information about an item included in an order.
 * This is used during the order confirmation API request and the order history
 * export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
abstract class Nosto_Tagging_Model_Meta_Order_Item extends Nosto_Object_Cart_LineItem
{
    /**
     * Populates the model
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @param $currencyCode
     * @return bool
     */
    public function loadData(Mage_Sales_Model_Order_Item $item, $currencyCode)
    {
        /* @var Nosto_Tagging_Helper_Price $nostoPriceHelper */
        $nostoPriceHelper = Mage::helper('nosto_tagging/price');
        parent::setProductId($this->buildItemProductId($item));
        parent::setQuantity((int)$item->getQtyOrdered());
        parent::setName($this->buildItemName($item));
        parent::setPrice($nostoPriceHelper->getItemFinalPriceInclTax($item));
        parent::setPriceCurrencyCode($currencyCode);
        parent::setSkuId($this->buildSkuId($item));

        return true;
    }

    /**
     * Builds the item name
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @return mixed
     */
    abstract public function buildItemName(Mage_Sales_Model_Order_Item $item);

    /**
     * Returns the product id for a quote item.
     * Always try to find the "parent" product ID if the product is a child of
     * another product type. We do this because it is the parent product that
     * we tag on the product page, and the child does not always have it's own
     * product page. This is important because it is the tagged info on the
     * product page that is used to generate recommendations and email content.
     *
     * @param Mage_Sales_Model_Order_Item $item the sales item model.
     * @return string
     */
    protected function buildItemProductId(Mage_Sales_Model_Order_Item $item)
    {
        $parent = $item->getProductOptionByCode('super_product_config');
        if (isset($parent['product_id'])) {
            return $parent['product_id'];
        } elseif ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            /** @var Mage_Catalog_Model_Product_Type_Configurable $model */
            $model = Mage::getModel('catalog/product_type_configurable');
            $parentIds = $model->getParentIdsByChild($item->getProductId());
            $attributes = $item->getBuyRequest()->getData('super_attribute');
            // If the product has a configurable parent, we assume we should tag
            // the parent. If there are many parent IDs, we are safer to tag the
            // products own ID.
            if (!empty($parentIds) === 1 && !empty($attributes)) {
                return $parentIds[0];
            }
        }

        $productId = (string)$item->getProductId();
        if (trim($productId) === '') {
            return Nosto_Object_Cart_LineItem::PSEUDO_PRODUCT_ID;
        }

        return $productId;
    }

    /**
     * Resolves the item id / variation id
     *
     * @param Mage_Sales_Model_Order_Item $item
     *
     * @return string|int|null
     */
    protected function buildSkuId(Mage_Sales_Model_Order_Item $item)
    {
        $skus = $item->getChildrenItems();
        /* @var Mage_Sales_Model_Order_Item $sku */
        if (isset($skus[0]) && $skus[0] instanceof Mage_Sales_Model_Order_Item) {
            /** @var Mage_Sales_Model_Order_Item[] $skus */
            return $skus[0]->getProductId();
        }

        return null;
    }

}
