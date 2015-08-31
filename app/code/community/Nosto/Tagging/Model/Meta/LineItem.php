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
 * Data Transfer Object representing a line item.
 * This is used in the cart & order meta models.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
abstract class Nosto_Tagging_Model_Meta_LineItem extends Mage_Core_Model_Abstract
{
    /**
     * @var string|int the item product ID.
     */
    protected $_productId;

    /**
     * @var int the amount of items.
     */
    protected $_quantity;

    /**
     * @var string the item name.
     */
    protected $_name;

    /**
     * @var NostoPrice the item unit price.
     */
    protected $_unitPrice;

    /**
     * @var NostoCurrencyCode the item price currency.
     */
    protected $_currency;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_lineitem');
    }

    /**
     * Returns the name for a quote/order item.
     * Configurable products will have their chosen options added to their name.
     * Bundle products will have their chosen child product names added.
     * Grouped products will have their parent product name prepended.
     * All others will have their own name only.
     *
     * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $item the item model.
     *
     * @return string
     */
    protected function fetchProductName($item)
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
     * The unique identifier for the item.
     *
     * @return string|int
     */
    public function getProductId()
    {
        return $this->_productId;
    }

    /**
     * The quantity of the item.
     *
     * @return int the quantity.
     */
    public function getQuantity()
    {
        return $this->_quantity;
    }

    /**
     * The name of the item.
     *
     * @return string the name.
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * The unit price of the item.
     *
     * @return NostoPrice the unit price.
     */
    public function getUnitPrice()
    {
        return $this->_unitPrice;
    }

    /**
     * The 3-letter ISO code (ISO 4217) for the item currency.
     *
     * @return NostoCurrencyCode the currency ISO code.
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     * Returns the name for an quote/order item representing a simple product.
     *
     * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $item the item model.
     *
     * @return string
     */
    abstract protected function fetchSimpleProductName($item);

    /**
     * Returns the name for an quote/order item representing a configurable product.
     *
     * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $item the item model.
     *
     * @return string
     */
    abstract protected function fetchConfigurableProductName($item);

    /**
     * Returns the name for an quote/order item representing a bundle product.
     *
     * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $item the item model.
     *
     * @return string
     */
    abstract protected function fetchBundleProductName($item);

    /**
     * Returns the name for an quote/order item representing a grouped product.
     *
     * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $item the item model.
     *
     * @return string
     */
    abstract protected function fetchGroupedProductName($item);
}
