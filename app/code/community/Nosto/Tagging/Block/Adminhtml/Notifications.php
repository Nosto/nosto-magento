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
 * Nosto notification block.
 * Adds a notification for showing information about missing tokens
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    /**
     * The id for the a tag where exchange rate cron is configured
     *
     * @var string
     */
    const EXCHANGE_RATE_CRON_FRAGMENT = 'nosto_tagging_scheduled_currency_exchange_rate_update-head';

    /**
     * Returns the status of the accounts for showing the notification. If any
     * of the accounts use multi-currency and have tokens missing, then the
     * notification bar should inform the merchant
     *
     * @return bool true or false indicating the status of the accounts
     */
    public function allAccountsOK()
    {
        /** @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging/data');
        foreach (Mage::app()->getStores() as $store) {
            /** @var NostoAccount $account */
            $account = $accountHelper->find($store);
            if ($account !== null
                && $account->isConnectedToNosto()
                && $account->hasMissingTokens()
                && !$dataHelper->multiCurrencyDisabled($store)) {
                    return false;
            }
        }
        return true;
    }

    /**
     * Get index management url
     *
     * @return string
     */
    public function getConfigureUrl()
    {
        return $this->getUrl(
            'adminhtml/system_config/edit/section/nosto_tagging',
            array(
                '_fragment' => self::EXCHANGE_RATE_CRON_FRAGMENT
            )
        );
    }

    /**
     * Returns the status of the cron of the accounts for showing the
     * notification. If any of the accounts use multi-currency but cron is
     * disabled then the notification bar should inform the merchant
     *
     * @return bool true or false indicating the cron status
     */
    public function cronEnabledIfNeeded()
    {
        /** @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging/data');
        foreach (Mage::app()->getStores() as $store) {
            $account = $accountHelper->find($store);
            if ($account !== null
                && !$dataHelper->multiCurrencyDisabled($store)
                && !$dataHelper->isScheduledCurrencyExchangeRateUpdateEnabled($store)) {
                    return false;
            }
        }
        return true;
    }

    /**
     * Checks if any of the Nosto accounts where previously installed into a
     * different store or magento installation. This would happen mostly when
     * development/staging setup is deployed to the production or vice versa.
     *
     * Return array structure
     * [
     *  'savedUrl' => $savedFrontPageUrl,
     *  'currentUrl' => $currentFrontPageUrl,
     *  'storeName' => $store->getName(),
     *  'nostoAccount' => $nostoAccount->getName(),
     *  'actionUrl' => $this->getUrl(
     * ]
     *
     * @return array (see structure above)
     */
    public function getInvalidAccountDomains()
    {
        /** @var Nosto_Tagging_Helper_Url $urlHelper */
        $urlHelper = Mage::helper('nosto_tagging/url');
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging/data');
        /** @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        $result = array();
        /* @var $store Mage_Core_Model_Store */
        foreach (Mage::app()->getStores() as $store) {
            $savedFrontPageUrl = $dataHelper->getStoreFrontPageUrl($store);
            /* @var $nostoAccount NostoAccount */
            $nostoAccount = $accountHelper->find($store);
            if (
                empty($savedFrontPageUrl)
                || $nostoAccount instanceof NostoAccount == false
            ) {
                continue;
            }

            $currentFrontPageUrl = $urlHelper->getFrontPageUrl($store);
            if (
                !self::storeUrlsMatch(
                    $savedFrontPageUrl,
                    $currentFrontPageUrl
                )
            ) {
                $invalidConfig = array(
                    'savedUrl' => $savedFrontPageUrl,
                    'currentUrl' => $currentFrontPageUrl,
                    'storeName' => $store->getName(),
                    'nostoAccount' => $nostoAccount->getName(),
                    'actionUrl' => $this->getUrl(
                        'adminhtml/nosto/resetAccountSettings/',
                        array('store'=>$store->getId())
                    )
                );

                $result[] = $invalidConfig;
            }
        }

        return $result;
    }

    /**
     * Compares two URLs with host and path part
     * @param string $installedUrl
     * @param string $currentUrl
     * @return bool
     */
    protected static function storeUrlsMatch(
        $installedUrl,
        $currentUrl
    )
    {
        $match = true;
        /* @var Mage_Core_Model_Url $mageUrl */
        $mageUrl = Mage::getSingleton('core/url');
        $installedModel = clone $mageUrl->parseUrl($installedUrl);;
        $currentModel = $mageUrl->parseUrl($currentUrl);
        $concatInstalled = $installedModel->getHost() . $installedModel->getPath();
        $concatCurrent= $currentModel->getHost() . $currentModel->getPath();
        if ($concatCurrent != $concatInstalled) {
            $match = false;
        }

        return $match;
    }
}
