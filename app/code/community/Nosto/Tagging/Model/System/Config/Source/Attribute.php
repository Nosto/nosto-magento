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
 * Abstract source model class for product attributes
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
abstract class Nosto_Tagging_Model_System_Config_Source_Attribute
{

    /**
     * @var int the product attribute type id
     */
    const PRODUCT_TYPE_ATTRIBUTE_ID = 4;

    /**
     * @var string the form key for the value
     */
    const OPTION_KEY_VALUE = 'value';

    /**
     * @var string the form key for the label
     */
    const OPTION_KEY_LABEL = 'label';

    /**
     * Returns all available product attributes
     *
     * @param array $filters ['field_to_filter' => 'value']
     * @return Mage_Catalog_Model_Resource_Product_Attribute_Collection
     */
    public function getProductAttributesCollection(array $filters=array())
    {
        $resourceModel = Mage::getResourceModel(
            'catalog/product_attribute_collection'
        );

        if (is_array($filters) && !empty($filters)) {
            foreach ($filters as $attribute => $value) {
                $resourceModel->addFieldToFilter(
                    $attribute,
                    $value
                );
            }
        }
        $attributes = $resourceModel
            ->addFieldToFilter(
                'entity_type_id',
                self::PRODUCT_TYPE_ATTRIBUTE_ID
            )
            ->setOrder(
                'attribute_code',
                Varien_Data_Collection::SORT_ORDER_ASC
            );

        return $attributes;
    }

    /**
     * List of attributes that cannot be added to tags due to data type and
     * Magento's internal processing of attributes
     *
     * @var array
     */
    public static $notValidCustomAttributes = array(
        'group_price',
        'tier_price',
        'media_gallery',
    );

    /**
     * Returns all available attributes
     *
     * @return array the options.
     */
    public function toOptionArray()
    {
        $attributes = $this->getProductAttributes();
        $attributeArray = array(
            array(
                self::OPTION_KEY_VALUE => 0,
                self::OPTION_KEY_LABEL => 'None'
            )
        );
        foreach($attributes as $attribute) {
            $code = $attribute->getData('attribute_code');
            if (in_array($code, self::$notValidCustomAttributes)) {
                continue;
            }
            $label = $attribute->getData('frontend_label');
            $attributeArray[] = array(
                self::OPTION_KEY_VALUE => $code,
                self::OPTION_KEY_LABEL => sprintf('%s (%s)', $code, $label)
            );
        }

        return $attributeArray;
    }


    /**
     * Returns a collection of attributes defined by the child class
     *
     * @return Mage_Catalog_Model_Resource_Product_Attribute_Collection
     */
    abstract public function getProductAttributes();
}
