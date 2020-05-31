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

/**
 * Event observer model for currency exchange rate.
 * Used to interact with Magento events.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 * @suppress PhanUnreferencedClass
 */
class Nosto_Tagging_Model_Observer_Exchange
{
    /**
     * Cron job for syncing currency exchange rates to Nosto.
     * Only stores that have the scheduled update enabled, have more currencies
     * than the default one defined and has a Nosto account are synced.
     *
     */
    public function scheduledCurrencyExchangeRateUpdate()
    {
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        if ($helper->isModuleEnabled()) {
            /** @var Nosto_Tagging_Helper_Account $accountHelper */
            $accountHelper = Mage::helper('nosto_tagging/account');
            $error = false;
            /** @var Mage_Core_Model_Store $store */
            foreach (Mage::app()->getStores() as $store) {
                if (!$helper->isScheduledCurrencyExchangeRateUpdateEnabled($store)
                    || !$helper->isMultiCurrencyMethodExchangeRate($store)
                ) {
                    continue;
                }

                $account = $accountHelper->find($store);
                if ($account === null) {
                    continue;
                }

                if (!$accountHelper->updateCurrencyExchangeRates($account, $store)) {
                    $error = true;
                }
            }

            if ($error) {
                /** @noinspection PhpUnhandledExceptionInspection */
                throw Mage::exception(
                    'Mage_Cron',
                    sprintf(
                        'There was an error updating the exchange rates. More info in "%".',
                        Nosto_Tagging_Model_Base::LOG_FILE_NAME
                    )
                );
            }
        }
    }
}
