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
 * @copyright Copyright (c) 2013-2016 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Data Transfer Object representing a shopping cart.
 * This is used in the cart tagging.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Cart extends Mage_Core_Model_Abstract
{
    /**
     * @var Nosto_Tagging_Model_Meta_Cart_Item[] list of cart items.
     */
    protected $_lineItems = array();

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_cart');
    }

    /**
     * Loads the Data Transfer Object.
     *
     * @param Mage_Sales_Model_Quote_Item[] $quoteItems the quote items.
     * @param Mage_Core_Model_Store         $store the store view.
     */
    public function loadData(array $quoteItems, Mage_Core_Model_Store $store = null)
    {
        if (is_null($store)) {
            $store = Mage::app()->getStore();
        }

        foreach ($quoteItems as $item) {

            /** @var Nosto_Tagging_Model_Meta_Cart_Item $model */
            $nostoCurrency = new NostoCurrencyCode($item->getQuote()->getQuoteCurrencyCode());
            $nostoPrice = new NostoPrice($item->getConvertedPrice());
            $this->_lineItems[] = Mage::getModel(
                'nosto_tagging/meta_cart_item',
                array(
                    'productId' => (int)$this->buildProductId($item),
                    'quantity' => (int)$item->getQty(),
                    'name' => $this->buildProductName($item),
                    'unitPrice' => $nostoPrice,
                    'currency' => $nostoCurrency,
                )
            );
        }
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
    protected function buildProductId(Mage_Sales_Model_Quote_Item $item)
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
    protected function buildProductName(Mage_Sales_Model_Quote_Item $item)
    {
        switch ($item->getProductType()) {
            case Mage_Catalog_Model_Product_Type::TYPE_SIMPLE:
                return $this->fetchSimpleProductName($item);

            case Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE:
                return $this->fetchConfigurableProductName($item);

            case Mage_Catalog_Model_Product_Type::TYPE_BUNDLE:
                return $this->fetchBundleProductName($item);

            case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
                return $this->fetchGroupedProductName($item);

            default:
                return $item->getName();
        }
    }

    /**
     * Returns the name for an quote item representing a simple product.
     *
     * @param Mage_Sales_Model_Quote_Item $item the quote item model.
     *
     * @return string
     */
    protected function fetchSimpleProductName(Mage_Sales_Model_Quote_Item $item)
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
     * Returns the name for an quote item representing a configurable product.
     *
     * @param Mage_Sales_Model_Quote_Item $item the quote item model.
     *
     * @return string
     */
    protected function fetchConfigurableProductName(Mage_Sales_Model_Quote_Item $item)
    {
        $name = $item->getName();
        $nameOptions = array();

        /* @var $helper Mage_Catalog_Helper_Product_Configuration */
        $helper = Mage::helper('catalog/product_configuration');
        foreach ($helper->getConfigurableOptions($item) as $opt) {
            if (isset($opt['value']) && is_string($opt['value'])) {
                $nameOptions[] = $opt['value'];
            }
        }

        return $this->applyProductNameOptions($name, $nameOptions);
    }

    /**
     * Returns the name for an quote item representing a bundle product.
     *
     * @param Mage_Sales_Model_Quote_Item $item the quote item model.
     *
     * @return string
     */
    protected function fetchBundleProductName(Mage_Sales_Model_Quote_Item $item)
    {
        $name = $item->getName();
        $nameOptions = array();

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
                            $nameOptions[] = $qty . $val['title'];
                        }
                    }
                }
            }
        }

        return $this->applyProductNameOptions($name, $nameOptions);
    }

    /**
     * Returns the name for an quote item representing a grouped product.
     *
     * @param Mage_Sales_Model_Quote_Item $item the quote item model.
     *
     * @return string
     */
    protected function fetchGroupedProductName(Mage_Sales_Model_Quote_Item $item)
    {
        $name = $item->getName();

        $config = $item->getBuyRequest()->getData('super_product_config');
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

    /**
     * Returns a list of attribute labels based on given attribute option map.
     *
     * The map must be passed with attribute id's as keys and the option id's
     * as values.
     *
     * @param array $attributes the attribute id map.
     *
     * @return array
     */
    protected function getAttributeLabels(array $attributes)
    {
        $labels = array();
        if (count($attributes) > 0) {
            /** @var Mage_Eav_Model_Entity_Attribute[] $collection */
            $collection = Mage::getModel('eav/entity_attribute')
                ->getCollection()
                ->addFieldToFilter(
                    'attribute_id',
                    array(
                        'in' => array_keys($attributes)
                    )
                );
            foreach ($collection as $attribute) {
                $optionId = $attributes[$attribute->getId()];
                if (!$attribute->getData('source_model')) {
                    $attribute->setData(
                        'source_model',
                        'eav/entity_attribute_source_table'
                    );
                }
                try {
                    $label = $attribute->getSource()->getOptionText($optionId);
                    if (!empty($label)) {
                        $labels[] = $label;
                    }
                } catch (Mage_Core_Exception $e) {
                    // If the source model cannot be found, just continue;
                    continue;
                }

            }
        }
        return $labels;
    }

    /**
     * Applies given options to the name.
     *
     * Format:
     *
     * "Product Name (Green, M)"
     *
     * @param string $name the name.
     * @param array  $options list of string values to apply as name option.
     *
     * @return string
     */
    protected function applyProductNameOptions($name, array $options)
    {
        if (!empty($options)) {
            $name .= ' (' . implode(', ', $options) . ')';
        }
        return $name;
    }

    /**
     * Returns the cart line items.
     *
     * @return Nosto_Tagging_Model_Meta_Cart_Item[] the items.
     */
    public function getLineItems()
    {
        return $this->_lineItems;
    }
}
