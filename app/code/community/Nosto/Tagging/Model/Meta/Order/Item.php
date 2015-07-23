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
 * Meta data class which holds information about an item included in an order.
 * This is used during the order confirmation API request and the order history
 * export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Order_Item extends Mage_Core_Model_Abstract implements NostoOrderPurchasedItemInterface
{
    /**
     * @var string|int the unique identifier of the purchased item.
     * If this item is for discounts or shipping cost, the id can be 0.
     */
    protected $_productId;

    /**
     * @var int the quantity of the item included in the order.
     */
    protected $_quantity;

    /**
     * @var string the name of the item included in the order.
     */
    protected $_name;

    /**
     * @var float The unit price of the item included in the order.
     */
    protected $_unitPrice;

    /**
     * @var string the 3-letter ISO code (ISO 4217) for the item currency.
     */
    protected $_currencyCode;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_order_item');
    }

    /**
     * Loads the item info from the Magento order item model.
     *
     * @param Mage_Sales_Model_Order_Item $item the item model.
     */
    public function loadData(Mage_Sales_Model_Order_Item $item)
    {
        $order = $item->getOrder();
        $this->_productId = (int)$this->fetchProductId($item);
        $this->_quantity = (int)$item->getQtyOrdered();
        $this->_name = $this->fetchProductName($item);
        $this->_unitPrice = $item->getPriceInclTax();
        $this->_currencyCode = strtoupper($order->getOrderCurrencyCode());
    }

    /**
     * Loads the "special item" info from provided data.
     * A "special item" is an item that is included in an order but does not
     * represent an item being bough, e.g. shipping fees, discounts etc.
     *
     * @param string $name the name of the item.
     * @param float|int|string $unitPrice the unit price of the item.
     * @param string $currencyCode the currency code for the item unit price.
     */
    public function loadSpecialItemData($name, $unitPrice, $currencyCode)
    {
        $this->_productId = -1;
        $this->_quantity = 1;
        $this->_name = (string)$name;
        $this->_unitPrice = $unitPrice;
        $this->_currencyCode = strtoupper($currencyCode);
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
     * Returns the name for a sales item.
     * Configurable products will have their chosen options added to their name.
     * Bundle products will have their chosen child product names added.
     * Grouped products will have their parents name prepended.
     * All others will have their own name only.
     *
     * @param Mage_Sales_Model_Order_Item $item the sales item model.
     *
     * @return string
     */
    protected function fetchProductName(Mage_Sales_Model_Order_Item $item)
    {
        $name = $item->getName();
        $optNames = array();

        if ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            /** @var Mage_Catalog_Model_Product_Type_Configurable $model */
            $model = Mage::getModel('catalog/product_type_configurable');
            $parentIds = $model->getParentIdsByChild($item->getProductId());
            // If the product has a configurable parent, we assume we should tag
            // the parent. If there are many parent IDs, we are safer to tag the
            // products own name alone.
            if (count($parentIds) === 1) {
                $attributes = $item->getBuyRequest()->getData('super_attribute');
                if (is_array($attributes)) {
                    foreach ($attributes as $id => $value) {
                        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attribute */
                        $attribute = Mage::getModel('catalog/resource_eav_attribute')
                            ->load($id);
                        $label = $attribute->getSource()->getOptionText($value);
                        if (!empty($label)) {
                            $optNames[] = $label;
                        }
                    }
                }
            }
        } elseif ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $opts = $item->getProductOptionByCode('attributes_info');
            if (is_array($opts)) {
                foreach ($opts as $opt) {
                    if (isset($opt['value']) && is_string($opt['value'])) {
                        $optNames[] = $opt['value'];
                    }
                }
            }
        } elseif ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $opts = $item->getProductOptionByCode('bundle_options');
            if (is_array($opts)) {
                foreach ($opts as $opt) {
                    if (isset($opt['value']) && is_array($opt['value'])) {
                        foreach ($opt['value'] as $val) {
                            $qty = '';
                            if (isset($val['qty']) && is_int($val['qty'])) {
                                $qty .= $val['qty'] . ' x ';
                            }
                            if (isset($val['title']) && is_string($val['title'])) {
                                $optNames[] = $qty . $val['title'];
                            }
                        }
                    }
                }
            }
        } elseif ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
            $config = $item->getProductOptionByCode('super_product_config');
            if (isset($config['product_id'])) {
                /** @var Mage_Catalog_Model_Product $parent */
                $parent = Mage::getModel('catalog/product')
                    ->load($config['product_id']);
                $parentName = $parent->getName();
                if (!empty($parentName)) {
                    $name = $parentName.' - '.$name;
                }
            }
        }

        if (!empty($optNames)) {
            $name .= ' (' . implode(', ', $optNames) . ')';
        }

        return $name;
    }

    /**
     * The unique identifier of the purchased item.
     * If this item is for discounts or shipping cost, the id can be 0.
     *
     * @return string|int
     */
    public function getProductId()
    {
        return $this->_productId;
    }

    /**
     * The quantity of the item included in the order.
     *
     * @return int the quantity.
     */
    public function getQuantity()
    {
        return $this->_quantity;
    }

    /**
     * The name of the item included in the order.
     *
     * @return string the name.
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * The unit price of the item included in the order.
     *
     * @return float the unit price.
     */
    public function getUnitPrice()
    {
        return $this->_unitPrice;
    }

    /**
     * The 3-letter ISO code (ISO 4217) for the item currency.
     *
     * @return string the currency ISO code.
     */
    public function getCurrencyCode()
    {
        return $this->_currencyCode;
    }
}
