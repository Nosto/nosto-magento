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
 * Collection class of Variation
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Variation_Collection extends Nosto_Object_Product_VariationCollection
{
    /**
     * Build price variations.
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $productAvailability
     * @param string $currencyCode
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    public function loadData(
        Mage_Catalog_Model_Product $product,
        $productAvailability,
        $currencyCode,
        Mage_Core_Model_Store $store
    ) {
        $groups = Mage::getModel('customer/group')->getCollection();
        /** @var Mage_Customer_Model_Group $group */
        foreach ($groups as $group) {
            // skip the default customer group
            if ($group->getId() == Nosto_Tagging_Helper_Variation::DEFAULT_CUSTOMER_GROUP_ID) {
                continue;
            }

            /** @var Nosto_Tagging_Model_Meta_Variation $variation */
            $variation = Mage::getModel('nosto_tagging/meta_variation');
            $variation->loadData($product, $group, $productAvailability, $currencyCode, $store);
            /** @phan-suppress-next-line PhanTypeMismatchArgument */
            $this->append($variation);
        }

        return true;
    }
}
