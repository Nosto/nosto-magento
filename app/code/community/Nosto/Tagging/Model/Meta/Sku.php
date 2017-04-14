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
 * Meta data class which holds information about a product.
 * This is used during the order confirmation API request and the product
 * history export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Sku extends Nosto_Object_Product_Sku
{

    use Nosto_Tagging_Model_Meta_Product_Trait;

    /**
     * Loads the SKU info from a Magento product model.
     *
     * @param Mage_Catalog_Model_Product $sku the product model.
     * @param Mage_Catalog_Model_Product $parent
     * @param Mage_Core_Model_Store|null $store the store to get the product data for.
     * @throws Nosto_NostoException
     */
    public function loadData(
        Mage_Catalog_Model_Product $sku,
        Mage_Catalog_Model_Product $parent,
        Mage_Core_Model_Store $store = null
    )
    {
        if ($store === null) {
            $store = Mage::app()->getStore();
        }

        if ($sku->getTypeId() !== Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            throw new Nosto_NostoException(
                sprintf(
                    'Nosto SKU can be loaded only from single type product. %d given',
                    $sku->getTypeId()
                )
            );
        }
        $this->setId($sku->getId());
        $this->setName($sku->getName());
        $this->setImageUrl($this->buildImageUrl($sku, $store));
        $this->setPrice($this->buildProductPrice($sku, $store));
        $this->setListPrice($this->buildProductListPrice($sku, $store));
        $this->setAvailability($this->buildAvailability($sku));
        $this->setUrl($this->buildUrl($sku, $store));
        $this->amendCustomizableAttributes($sku, $store);
        /** @var Mage_Catalog_Model_Product_Type_Configurable $parentType */
        $parentType = $parent->getTypeInstance();
        if ($parentType instanceof Mage_Catalog_Model_Product_Type_Configurable) {
            $configurableAttributes = $parentType->getConfigurableAttributesAsArray($parent);
            foreach ($configurableAttributes as $configurableAttribute) {
                try {
                    $attributeValue = $this->getAttributeValue(
                        $sku,
                        $configurableAttribute['attribute_code']
                    );
                    if (!empty($attributeValue) && is_scalar($attributeValue)) {
                        $this->addCustomAttribute(
                            $configurableAttribute['attribute_code'],
                            $attributeValue
                        );
                    }
                } catch (Exception $e) {
                    Nosto_Tagging_Helper_Log::exception($e);
                }
            }
        }
    }

    /**
     * Array of attributes that can be customized from Nosto's store admin
     * settings
     *
     * @@return array
     */
    protected function getCustomisableAttributes()
    {
        return array('gtin' => 'gtin');
    }
}
