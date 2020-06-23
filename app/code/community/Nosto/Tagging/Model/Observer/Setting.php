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

/* @var Nosto_Tagging_Helper_Bootstrap $nostoBootstrapHelper */
$nostoBootstrapHelper = Mage::helper('nosto_tagging/bootstrap');
$nostoBootstrapHelper->init();

use Nosto_Tagging_Helper_Log as NostoLog;

/**
 * Event observer model for system configuration.
 * Used to interact with Magento events.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 * @suppress PhanUnreferencedClass
 */
class Nosto_Tagging_Model_Observer_Setting
{
    /**
     * Updates / synchronizes Nosto account settings via API to Nosto
     * for each store that has Nosto account.
     *
     * Event 'admin_system_config_changed_section_nosto_tagging'.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Nosto_Tagging_Model_Observer_Setting
     */
    public function syncNostoAccount(/** @noinspection PhpUnusedParameterInspection */ // @codingStandardsIgnoreLine
        Varien_Event_Observer $observer
    ) {
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        if ($helper->isModuleEnabled()) {
            /** @var Nosto_Tagging_Helper_Account $accountHelper */
            $accountHelper = Mage::helper('nosto_tagging/account');
            /** @var Mage_Core_Model_Store $store */
            foreach (Mage::app()->getStores() as $store) {
                $account = $accountHelper->find($store);
                if ($account instanceof Nosto_Object_Signup_Account === false) {
                    continue;
                }

                /* @var Mage_Core_Model_App_Emulation $emulation */
                $emulation = Mage::getSingleton('core/app_emulation');
                $env = $emulation->startEnvironmentEmulation($store->getId());
                if (!$accountHelper->updateAccount($account, $store)) {
                    NostoLog::error(
                        'Failed sync account #%s for store #%s in class %s',
                        array(
                            $account->getName(),
                            $store->getName(),
                            __CLASS__
                        )
                    );
                }

                if ($helper->isMultiCurrencyMethodExchangeRate($store) && !$accountHelper->updateCurrencyExchangeRates(
                        $account,
                        $store
                    )) {
                    NostoLog::error(
                        'Failed sync currency rates #%s for store #%s in class %s',
                        array(
                            $account->getName(),
                            $store->getName(),
                            __CLASS__
                        )
                    );
                }

                $emulation->stopEnvironmentEmulation($env);
            }
        }

        return $this;
    }
}
