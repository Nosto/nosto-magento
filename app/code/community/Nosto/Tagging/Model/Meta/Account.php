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
 * @copyright Copyright (c) 2013-2016 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Meta data class which holds information about a new Nosto account.
 * This is used during the Nosto account creation.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Account extends NostoAccount
{
    /**
     * @var string the API token used to identify an account creation.
     */
    protected $_signUpApiToken = 'YBDKYwSqTCzSsU8Bwbg4im2pkHMcgTy9cCX7vevjJwON1UISJIwXOLMM0a8nZY7h';

    /**
     * Loads the meta data for the given store.
     *
     * @param Mage_Core_Model_Store $store the store view to load the data for.
     */
    public function loadData(Mage_Core_Model_Store $store)
    {
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');

        $this->_title = $store->getWebsite()->getName()
            . ' - '
            . $store->getGroup()->getName()
            . ' - '
            . $store->getName();
        $this->_name = substr(sha1(rand()), 0, 8);
        $this->_frontPageUrl = NostoHttpRequest::replaceQueryParamInUrl(
            '___store',
            $store->getCode(),
            $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)
        );
        $this->_currency = new NostoCurrencyCode($store->getBaseCurrencyCode());
        $storeLocale = $store->getConfig('general/locale/code');
        $this->_language = new NostoLanguageCode(substr($storeLocale, 0, 2));
        $this->_ownerLanguage = new NostoLanguageCode(
            substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2)
        );

        /** @var Nosto_Tagging_Model_Meta_Account_Owner $owner */
        $owner = new Nosto_Tagging_Model_Meta_Account_Owner();
        /** @var Mage_Admin_Model_User $user */
        $user = Mage::getSingleton('admin/session')->getUser();
        if (!is_null($user)) {
            $owner->loadData($user);
        }
        $this->_owner = $owner;

        /** @var Nosto_Tagging_Model_Meta_Account_Billing $billing */
        $billing = new Nosto_Tagging_Model_Meta_Account_Billing();
        $billing->loadData($store);
        $this->_billing = $billing;
        $currencyCodes = $store->getAvailableCurrencyCodes(true);
        if (is_array($currencyCodes) && count($currencyCodes) > 0) {
            /** @var Nosto_Tagging_Helper_Currency $currencyHelper */
            $currencyHelper = Mage::helper('nosto_tagging/currency');
            foreach ($currencyCodes as $currencyCode) {
                $this->_currencies[$currencyCode] = $currencyHelper
                    ->getCurrencyObject($storeLocale, $currencyCode);
            }
        }
        $this->_useCurrencyExchangeRates = false;
        if (!$helper->multiCurrencyDisabled($store)) {
            $this->_defaultPriceVariationId = $store->getBaseCurrencyCode();
            if ($helper->isMultiCurrencyMethodPriceVariation($store)) {
                $this->_useCurrencyExchangeRates = false;
            } else {
                $this->_useCurrencyExchangeRates = true;
            }
        }
    }
}
