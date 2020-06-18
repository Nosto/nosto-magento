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
 * Meta data class which holds information about a variation.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Variation extends Nosto_Object_Product_Variation
{
    /**
     * Loads the Variation info from a Magento product model.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Customer_Model_Group $group the customer group
     * @param string $productAvailability
     * @param string $currencyCode
     * @param Mage_Core_Model_Store|null $store the store to get the product data for.
     * @return bool
     */
    public function loadData(
        Mage_Catalog_Model_Product $product,
        Mage_Customer_Model_Group $group,
        $productAvailability,
        $currencyCode,
        Mage_Core_Model_Store $store = null
    ) {
        if ($store === null) {
            /** @var Nosto_Tagging_Helper_Data $helper */
            $helper = Mage::helper('nosto_tagging');
            $store = $helper->getStore();
        }

        //It has to be a new instance of the Product. Because magento product takes customer group Id once only
        /** @var Mage_Catalog_Model_Product $tmpProduct */
        $tmpProduct = Mage::getModel('catalog/product')->load($product->getId());
        /** @noinspection PhpUndefinedMethodInspection */
        $tmpProduct->setCustomerGroupId($group->getCustomerGroupId());
        /* @var Nosto_Tagging_Helper_Variation $variationHelper  */
        $variationHelper = Mage::helper('nosto_tagging/variation');
        $this->setVariationId($variationHelper->generateVariationId($group));
        $this->setAvailability($productAvailability);
        $this->setPriceCurrencyCode($currencyCode);

        /** @var Nosto_Tagging_Helper_Price $priceHelper */
        $priceHelper = Mage::helper('nosto_tagging/price');
        $this->setListPrice($priceHelper->getProductTaggingPrice($tmpProduct, $store, false));
        $this->setPrice($priceHelper->getProductTaggingPrice($tmpProduct, $store, true));

        return true;
    }
}
