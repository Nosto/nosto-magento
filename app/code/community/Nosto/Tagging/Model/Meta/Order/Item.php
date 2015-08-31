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
 * Data Transfer object representing an item included in an order.
 * This is used during the order confirmation API request and the order history
 * export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Order_Item extends Nosto_Tagging_Model_Meta_LineItem implements NostoOrderItemInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_order_item');
    }

    /**
     * Loads the Data Transfer Object.
     *
     * @param Mage_Sales_Model_Order_Item $item the item model.
     * @param NostoCurrencyCode           $currencyCode the order currency code.
     */
    public function loadData(Mage_Sales_Model_Order_Item $item, NostoCurrencyCode $currencyCode)
    {
        $this->_productId = $this->fetchProductId($item);
        $this->_quantity = (int)$item->getQtyOrdered();
        $this->_name = $this->fetchProductName($item);
        $this->_unitPrice = new NostoPrice($item->getBasePriceInclTax());
        $this->_currency = $currencyCode;
    }

    /**
     * Loads the "special item" info from provided data.
     * A "special item" is an item that is included in an order but does not
     * represent an item being bough, e.g. shipping fees, discounts etc.
     *
     * @param string            $name the name of the item.
     * @param NostoPrice        $unitPrice the unit price of the item.
     * @param NostoCurrencyCode $currencyCode the currency code for the item unit price.
     */
    public function loadSpecialItemData($name, NostoPrice $unitPrice, NostoCurrencyCode $currencyCode)
    {
        $this->_productId = -1;
        $this->_quantity = 1;
        $this->_name = (string)$name;
        $this->_unitPrice = $unitPrice;
        $this->_currency = $currencyCode;
    }

    /**
     * Returns the product id for a quote item.
     * Always try to find the "parent" product ID if the product is a child of
     * another product type. We do this because it is the parent product that
     * we tag on the product page, and the child does not always have it's own
     * product page. This is important because it is the tagged info on the
     * product page that is used to generate recommendations and email content.
     *
     * @param Mage_Sales_Model_Order_Item $item the sales item model.
     *
     * @return int
     */
    protected function fetchProductId(Mage_Sales_Model_Order_Item $item)
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
            if (count($parentIds) === 1 && !empty($attributes)) {
                return $parentIds[0];
            }
        }
        return $item->getProductId();
    }

    /**
     * Returns the name for an quote/order item representing a simple product.
     *
     * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $item the item model.
     *
     * @return string
     */
    protected function fetchSimpleProductName($item)
    {
        $name = $item->getName();
        $nameOptions = array();

        /** @var Mage_Catalog_Model_Product_Type_Configurable $model */
        $model = Mage::getModel('catalog/product_type_configurable');
        $parentIds = $model->getParentIdsByChild($item->getProductId());
        // If the product has a configurable parent, we assume we should tag
        // the parent. If there are many parent IDs, we are safer to tag the
        // products own name alone.
        if (count($parentIds) === 1) {
            $attributes = $item->getBuyRequest()->getData('super_attribute');
            if (is_array($attributes) && count($attributes) > 0) {
                $nameOptions = $this->getAttributeLabels($attributes);
            }
        }

        return $this->applyProductNameOptions($name, $nameOptions);
    }

    /**
     * Returns the name for an quote/order item representing a configurable product.
     *
     * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $item the item model.
     *
     * @return string
     */
    protected function fetchConfigurableProductName($item)
    {
        $name = $item->getName();
        $nameOptions = array();

        $opts = $item->getProductOptionByCode('attributes_info');
        if (is_array($opts) && count($opts) > 0) {
            foreach ($opts as $opt) {
                if (isset($opt['value']) && is_string($opt['value'])) {
                    $nameOptions[] = $opt['value'];
                }
            }
        }

        return $this->applyProductNameOptions($name, $nameOptions);
    }

    /**
     * Returns the name for an quote/order item representing a bundle product.
     *
     * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $item the item model.
     *
     * @return string
     */
    protected function fetchBundleProductName($item)
    {
        $name = $item->getName();
        $nameOptions = array();

        $opts = $item->getProductOptionByCode('bundle_options');
        if (is_array($opts) && count($opts) > 0) {
            foreach ($opts as $opt) {
                if (isset($opt['value']) && is_array($opt['value'])) {
                    foreach ($opt['value'] as $val) {
                        $qty = '';
                        if (isset($val['qty']) && is_int($val['qty'])) {
                            $qty .= $val['qty'] . ' x ';
                        }
                        if (isset($val['title']) && is_string($val['title'])) {
                            $nameOptions[] = $qty . $val['title'];
                        }
                    }
                }
            }
        }

        return $this->applyProductNameOptions($name, $nameOptions);
    }

    /**
     * Returns the name for an quote/order item representing a grouped product.
     *
     * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $item the item model.
     *
     * @return string
     */
    protected function fetchGroupedProductName($item)
    {
        $name = $item->getName();

        $config = $item->getProductOptionByCode('super_product_config');
        if (isset($config['product_id'])) {
            /** @var Mage_Catalog_Model_Product $parent */
            $parent = Mage::getModel('catalog/product')
                ->load($config['product_id']);
            $parentName = $parent->getName();
            if (!empty($parentName)) {
                $name = $parentName . ' - ' . $name;
            }
        }

        return $name;
    }
}
