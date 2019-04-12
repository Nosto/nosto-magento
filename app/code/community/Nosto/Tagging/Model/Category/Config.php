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
 * Adds relevance attribute to the sorting options for category products
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Category_Config extends Mage_Catalog_Model_Config
{
    const NOSTO_PERSONALIZED_KEY = 'nosto-personalized';
    const NOSTO_TOPLIST_KEY = 'nosto-toplist';

    /**
     * Add relevance attribute as a sorting option
     *
     * @param null $storeId
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getAttributeUsedForSortByArray($storeId = null)
    {
        $options = parent::getAttributeUsedForSortByArray();
        /* @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');
        $store = Mage::app()->getStore($storeId);
        /* @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        $nostoAccount = $accountHelper->find($store);

        if ($nostoAccount instanceof Nosto_Types_Signup_AccountInterface) {
            $featureAccessService = new Nosto_Service_FeatureAccess($nostoAccount);
            if ($dataHelper->getUsePersonalizedCategorySorting($store)
                && $featureAccessService->canUseGraphql()
            ) {
                $options[self::NOSTO_PERSONALIZED_KEY] = Mage::helper('catalog')->__('Personalized for you');
                $options[self::NOSTO_TOPLIST_KEY] = Mage::helper('catalog')->__('Top products');
            }
        }

        return $options;
    }
}
