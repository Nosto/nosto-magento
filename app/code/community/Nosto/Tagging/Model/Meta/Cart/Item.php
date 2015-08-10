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
 * Data Transfer Object representing a cart item.
 * This is used in the cart tagging.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Cart_Item extends Mage_Core_Model_Abstract
{
    /**
     * @var string|int the cart item product ID.
     */
    protected $_productId;

    /**
     * @var int the amount of items in cart.
     */
    protected $_quantity;

    /**
     * @var string the cart item name.
     */
    protected $_name;

    /**
     * @var NostoPrice the cart item unit price.
     */
    protected $_unitPrice;

    /**
     * @var NostoCurrencyCode the price currency.
     */
    protected $_currency;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_cart_item');
    }

    /**
     * Loads the Data Transfer Object.
     *
     * @param Mage_Sales_Model_Quote_Item $item the quote item.
     * @param NostoCurrencyCode           $currencyCode the currency code.
     */
    public function loadData(Mage_Sales_Model_Quote_Item $item, NostoCurrencyCode $currencyCode)
    {
        $this->_productId = $this->fetchProductId($item);
        $this->_quantity = (int)$item->getQty();
        $this->_name = $this->fetchProductName($item);
        $this->_unitPrice = new NostoPrice($item->getBasePriceInclTax());
        $this->_currency = $currencyCode;
    }

    /**
     * Returns the cart item product ID.
     *
     * @return int|string the cart item product ID.
     */
    public function getProductId()
    {
        return $this->_productId;
    }

    /**
     * Returns the amount of items in cart.
     *
     * @return int the amount of items in cart.
     */
    public function getQuantity()
    {
        return $this->_quantity;
    }

    /**
     * Returns the cart item name.
     *
     * @return string the cart item name.
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns the cart item unit price.
     *
     * @return NostoPrice the cart item unit price.
     */
    public function getUnitPrice()
    {
        return $this->_unitPrice;
    }

    /**
     * Returns the price currency.
     *
     * @return NostoCurrencyCode the price currency.
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     * Returns the product id for a quote item.
     * Always try to find the "parent" product ID if the product is a child of
     * another product type. We do this because it is the parent product that
     * we tag on the product page, and the child does not always have it's own
     * product page. This is important because it is the tagged info on the
     * product page that is used to generate recommendations and email content.
     *
     * @param Mage_Sales_Model_Quote_Item $item the quote item model.
     *
     * @return int|string
     */
    protected function fetchProductId($item)
    {
        $parentItem = $item->getOptionByCode('product_type');
        if (!is_null($parentItem)) {
            return $parentItem->getProductId();
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
     * Returns the name for a quote item.
     * Configurable products will have their chosen options added to their name.
     * Bundle products will have their chosen child product names added.
     * Grouped products will have their parent product name prepended.
     * All others will have their own name only.
     *
     * @param Mage_Sales_Model_Quote_Item $item the quote item model.
     *
     * @return string
     */
    protected function fetchProductName($item)
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
            /* @var $helper Mage_Catalog_Helper_Product_Configuration */
            $helper = Mage::helper('catalog/product_configuration');
            foreach ($helper->getConfigurableOptions($item) as $opt) {
                if (isset($opt['value']) && is_string($opt['value'])) {
                    $optNames[] = $opt['value'];
                }
            }
        } elseif ($item->getProductType() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $type = $item->getProduct()->getTypeInstance(true);
            $opts = $type->getOrderOptions($item->getProduct());
            if (isset($opts['bundle_options']) && is_array($opts['bundle_options'])) {
                foreach ($opts['bundle_options'] as $opt) {
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
            $config = $item->getBuyRequest()->getData('super_product_config');
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
}
