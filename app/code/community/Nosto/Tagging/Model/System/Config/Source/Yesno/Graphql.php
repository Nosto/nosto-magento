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
 * Source model for grapqhl related settings
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 * @suppress PhanUnreferencedClass
 */
class Nosto_Tagging_Model_System_Config_Source_Yesno_Graphql
    extends Nosto_Tagging_Model_System_Config_Source_Yesno_Base
{
    /**
     * @inheritdoc
     */
    protected function featureAvailable()
    {
        $storeCode = Mage::getSingleton('adminhtml/config_data')->getStore();
        /* @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        $accounts = array();
        if (empty($storeCode)) {
           $stores = $accountHelper->getAllStoreViewsWithNostoAccount();
           foreach ($stores as $store) {
               $accounts[] = $accountHelper->find($store);
           }
        } else {
            $store = Mage::getModel('core/store')->load($storeCode);
            $account = $accountHelper->find($store);
            if ($account instanceof Nosto_Types_Signup_AccountInterface) {
                $accounts[] = $account;
            }
        }
        foreach ($accounts as $account) {
            $featureService = new Nosto_Service_FeatureAccess($account);
            if ($featureService->canUseGraphql()) {

                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    protected function getDisabledMessage()
    {
        return 'No (missing access token)';
    }
}
