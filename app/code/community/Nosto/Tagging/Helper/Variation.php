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

/* @var Nosto_Tagging_Helper_Bootstrap $nostoBootstrapHelper */
$nostoBootstrapHelper = Mage::helper('nosto_tagging/bootstrap');
$nostoBootstrapHelper->init();

/**
 * Helper class for variation related tasks
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Variation extends Mage_Core_Helper_Abstract
{
    /**
     * Default customer group id for generating the variation_id
     */
    const DEFAULT_CUSTOMER_GROUP_ID = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;

    /**
     * Generates "slug style" variation id
     *
     * @param Mage_Customer_Model_Group $group
     * @return string
     */
    public function generateVariationId(Mage_Customer_Model_Group $group)
    {
        /** @var Mage_Catalog_Model_Product_Url $productUrlModel */
        $productUrlModel = Mage::getModel('catalog/product_url');
        $slug = $productUrlModel->formatUrlKey($group->getCode());

        return strtoupper($slug);
    }

    /**
     * Resolves the default variation id for a store
     *
     * @return null|string the identifier of the default variation
     */
    public function getDefaultVariationId()
    {
        /** @var Mage_Customer_Model_Group $group */
        $defaultGroup = Mage::getModel('customer/group')->load(self::DEFAULT_CUSTOMER_GROUP_ID);
        if ($defaultGroup instanceof Mage_Customer_Model_Group) {
            return $this->generateVariationId($defaultGroup);
        }

        return null;
    }
}

