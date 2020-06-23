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
     * @return bool
     * @throws Nosto_NostoException
     * @throws Mage_Core_Exception
     */
    public function loadData(
        Mage_Catalog_Model_Product $sku,
        Mage_Catalog_Model_Product $parent,
        Mage_Core_Model_Store $store = null
    ) {
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');

        if ($store === null) {
            /** @var Nosto_Tagging_Helper_Data $helper */
            $helper = Mage::helper('nosto_tagging');
            $store = $helper->getStore();
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
        /** @noinspection PhpUndefinedMethodInspection */
        if ((int)$sku->getVisibility() != Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
            $this->setUrl($this->buildUrl($sku, $store));
        }

        $this->amendCustomizableAttributes($sku, $store);
        $this->loadCustomFieldsFromConfigurableAttributes($sku, $parent, $store);
        $this->loadCustomFieldsFromAttributeSet($sku, $store);

        if ($dataHelper->getUseInventoryLevel($store)) {
            $this->amendInventoryLevel($sku);
        }

        return true;
    }

    /**
     * Tag the custom attributes
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store $store
     */
    protected function loadCustomFieldsFromAttributeSet(
        Mage_Catalog_Model_Product $product,
        Mage_Core_Model_Store $store
    ) {
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');
        if ($dataHelper->getUseCustomFields($store)) {
            $customFields = $this->loadCustomFields($product, $store);
            foreach ($customFields as $key => $value) {
                $this->addCustomField($key, $value);
            }
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $sku
     * @param Mage_Catalog_Model_Product $parent
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    protected function loadCustomFieldsFromConfigurableAttributes(
        Mage_Catalog_Model_Product $sku,
        Mage_Catalog_Model_Product $parent,
        Mage_Core_Model_Store $store
    ) {
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');
        if (!$dataHelper->getUseCustomFields($store)) {
            return false;
        }

        /** @var Mage_Catalog_Model_Product_Type_Configurable $parentType */
        $parentType = $parent->getTypeInstance();
        if ($parentType instanceof Mage_Catalog_Model_Product_Type_Configurable) {
            $configurableAttributes = $parentType->getConfigurableAttributesAsArray($parent);
            foreach ($configurableAttributes as $configurableAttribute) {
                try {
                    $attributeCode = $configurableAttribute['attribute_code'];
                    if (!array_key_exists($attributeCode, $this->getCustomFields())) {
                        $attributeValue = $this->getAttributeValue($sku, $attributeCode, $store->getId());
                        if (is_scalar($attributeValue)) {
                            $this->addCustomField($attributeCode, $attributeValue);
                        }
                    }
                } catch (Exception $e) {
                    Nosto_Tagging_Helper_Log::exception($e);
                }
            }
        }

        return true;
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

    /**
     * Builds the availability for the SKU.
     * The SKU availability doesn't concern about the visibility. A SKU can be found by looking at its parent product
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @return string
     */
    protected function buildAvailability(Mage_Catalog_Model_Product $product)
    {
        $availability = Nosto_Types_Product_ProductInterface::OUT_OF_STOCK;
        if ($product->isAvailable()) {
            $availability = Nosto_Types_Product_ProductInterface::IN_STOCK;
        }

        return $availability;
    }

    /**
     * Adds the stock level / inventory level for SKU
     *
     * @param Mage_Catalog_Model_Product $sku the product sku model.
     *
     */
    protected function amendInventoryLevel(Mage_Catalog_Model_Product $sku)
    {
        /* @var Nosto_Tagging_Helper_Stock $stockHelper */
        $stockHelper = Mage::helper('nosto_tagging/stock');
        try {
            $this->setInventoryLevel($stockHelper->getQty($sku));
        } catch (Exception $e) {
            Nosto_Tagging_Helper_Log::error(
                'Failed to resolve inventory level for SKU %d to tags. Error message was: %s',
                array(
                    $sku->getId(),
                    $e->getMessage()
                )
            );
        }
    }
}
