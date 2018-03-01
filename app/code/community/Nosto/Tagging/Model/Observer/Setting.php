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
     * Scheduled currency exchange rate setting key.
     */
    const SCHEDULED_CURRENCY_EXCHANGE_RATE_UPDATE
        = 'scheduled_currency_exchange_rate_update';

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
    )
    {
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
                if ($helper->isMultiCurrencyMethodExchangeRate($store)) {
                    if (!$accountHelper->updateCurrencyExchangeRates(
                        $account,
                        $store
                    )
                    ) {
                        NostoLog::error(
                            'Failed sync currency rates #%s for store #%s in class %s',
                            array(
                                $account->getName(),
                                $store->getName(),
                                __CLASS__
                            )
                        );
                    }
                }
                $emulation->stopEnvironmentEmulation($env);
            }
        }

        return $this;
    }


    /**
     * Checks if Nosto settings has changed
     * under the magento config panel
     *
     * Event 'model_config_data_save_before'.
     *
     * @return Nosto_Tagging_Model_Observer_Setting
     */
    public function checkNostoSettingsHaveChanged()
    {
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        try {
            $request = Mage::app()->getRequest();
            $section = isset($request->getParams()['section']) ? $request->getParams()['section'] : null;
            if ($section === 'nosto_tagging' && $helper->isModuleEnabled()) {
                /** @var Nosto_Tagging_Helper_Account $accountHelper */
                $accountHelper = Mage::helper('nosto_tagging/account');
                $persistedConfig = $helper->getNostoStoreConfig();
                $postGroups = isset($request->getPost()['groups']) ? $request->getPost()['groups'] : null;
                if (empty($persistedConfig) || empty($postGroups)) {
                    return $this;
                }
                /** @var Mage_Core_Model_Store $store */
                foreach (Mage::app()->getStores() as $store) {
                    $account = $accountHelper->find($store);
                    if (!$helper->getUseProductIndexer($store) ||
                        $account instanceof Nosto_Object_Signup_Account === false) {
                        continue;
                    }
                    // Remove fields key and shift elements one level up
                    $postGroups = self::removeArrayKeyShiftOneLevelUp($postGroups, 'fields');
                    // Remove unnecessary configuration section from the array
                    $persistedConfig = self::filterNonIndexTriggering($persistedConfig);
                    $postGroups = self::filterNonIndexTriggering($postGroups);
                    // Normalize attribute_to_tag sub array to match post array structure
                    if (isset($persistedConfig['attribute_to_tag'])) {
                        $persistedConfig['attribute_to_tag'] =
                            self::restructureTagsKey($persistedConfig['attribute_to_tag']);
                    }
                    if (self::getDiffNostoSettingsInPost($postGroups, $persistedConfig) != array()) {
                        Mage::getSingleton('index/indexer')
                            ->getProcessByCode('nosto_indexer')
                            ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
                        return $this;
                    }
                }
            }
        } catch (\Exception $e) {
            NostoLog::exception($e);
        }
        return $this;
    }

    /**
     * Checks if Nosto settings POST array
     * is different than the persisted configurations
     *
     * @param $postArray
     *
     * @param $persistedArray
     *
     * @return array
     */
    protected function getDiffNostoSettingsInPost($postArray, $persistedArray)
    {
        $difference = array();
        foreach ($postArray as $key => $value) {
            if (is_array($value) && $key != 'value') {
                if (!isset($persistedArray[$key])) {
                    $difference[$key] = $value;
                } elseif (is_array($persistedArray[$key])) {
                    $newDiff = self::getDiffNostoSettingsInPost($value, $persistedArray[$key]);
                    if ($newDiff) {
                        $difference[$key] = $newDiff;
                    }
                } elseif (array_key_exists('value', $value) && ($value['value'] != $persistedArray[$key])) {
                    $difference[$key] = $value;
                }
            } elseif (!isset($persistedArray[$key]) || $persistedArray[$key] != $value) {
                $difference[$key] = $value;
            }
        }
        $difference = array_filter($difference);
        if (empty($difference)) {
            return array();
        }
        return $difference;
    }

    /**
     * Removes from an array Nosto settings
     * that should not trigger product reindex
     *
     * @param array $array
     * @return array[]
     */
    protected function filterNonIndexTriggering(array $array)
    {
        $excludedReindexSettings = array(
            self::SCHEDULED_CURRENCY_EXCHANGE_RATE_UPDATE
        );
        foreach ($excludedReindexSettings  as $setting) {
            if (isset($array[$setting])) {
                unset($array[$setting]);
            }
        }
        return $array;
    }

    /**
     * Normalizes the attribute_to_tag key from the
     * Nosto persisted configuration array to match the
     * structure of the POST array
     *
     * @param array $tags
     * @return array
     */
    protected function restructureTagsKey(array $tags)
    {
        $returnTags = array();
        foreach ($tags as $key => $tag) {
            $values = explode(',', $tag);
            foreach ($values as $value) {
                $returnTags[$key]['value'][] = $value;
            }
        }
        return $returnTags;
    }

    /**
     * Removes array key and shift elements one level up
     *
     * @param array $array
     * @param string $arrayKey
     * @return array
     */
    protected function removeArrayKeyShiftOneLevelUp(array $array, $arrayKey)
    {
        foreach ($array as $key => $element) {
            unset($element[$key]);
            $array[$key] = $element[$arrayKey];
        }
        return $array;
    }
}
