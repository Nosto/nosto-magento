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
class Nosto_Tagging_Model_Meta_Account extends Mage_Core_Model_Abstract implements NostoAccountMetaInterface
{
    /**
     * @var string the store name.
     */
    protected $_title;

    /**
     * @var string the account name.
     */
    protected $_name;

    /**
     * @var string the store front end url.
     */
    protected $_frontPageUrl;

    /**
     * @var NostoCurrencyCode the store currency ISO (ISO 4217) code.
     */
    protected $_currency;

    /**
     * @var NostoLanguageCode the store language ISO (ISO 639-1) code.
     */
    protected $_language;

    /**
     * @var NostoLanguageCode the owner language ISO (ISO 639-1) code.
     */
    protected $_ownerLanguage;

    /**
     * @var Nosto_Tagging_Model_Meta_Account_Owner the account owner meta model.
     */
    protected $_owner;

    /**
     * @var Nosto_Tagging_Model_Meta_Account_Billing the billing meta model.
     */
    protected $_billing;

    /**
     * @var NostoCurrency[] list of supported currencies by the store.
     */
    protected $_currencies = array();

    /**
     * @var string the default price variation ID if using multiple currencies.
     */
    protected $_defaultPriceVariationId;

    /**
     * @var bool if the store is set to use multi variants for currencies or pricing
     */
    private $_useMultiVariants = false;

    /**
     * @var string the API token used to identify an account creation.
     */
    protected $_signUpApiToken = 'YBDKYwSqTCzSsU8Bwbg4im2pkHMcgTy9cCX7vevjJwON1UISJIwXOLMM0a8nZY7h';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_account');
    }

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
        $owner = Mage::getModel('nosto_tagging/meta_account_owner');
        /** @var Mage_Admin_Model_User $user */
        $user = Mage::getSingleton('admin/session')->getUser();
        if (!is_null($user)) {
            $owner->loadData($user);
        }
        $this->_owner = $owner;

        /** @var Nosto_Tagging_Model_Meta_Account_Billing $billing */
        $billing = Mage::getModel('nosto_tagging/meta_account_billing');
        $billing->loadData($store);
        $this->_billing = $billing;

        if ($helper->isMultiCurrencyMethodPriceVariation($store)) {
            $this->_useMultiVariants = true;
            $this->_defaultPriceVariationId
                = $store->getBaseCurrencyCode();
        } else {
            $currencyCodes = $store->getAvailableCurrencyCodes(true);
            if (is_array($currencyCodes) && count($currencyCodes) > 0) {
                /** @var Nosto_Tagging_Helper_Currency $currencyHelper */
                $currencyHelper = Mage::helper('nosto_tagging/currency');
                foreach ($currencyCodes as $currencyCode) {
                    $this->_currencies[$currencyCode] = $currencyHelper
                        ->getCurrencyObject($storeLocale, $currencyCode);
                }
            }
        }
    }

    /**
     * The shops name for which the account is to be created for.
     *
     * @return string the name.
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * The name of the account to create.
     * This has to follow the pattern of
     * "[platform name]-[8 character lowercase alpha numeric string]".
     *
     * @return string the account name.
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * The name of the platform the account is used on.
     * A list of valid platform names is issued by Nosto.
     *
     * @return string the platform names.
     */
    public function getPlatform()
    {
        return 'magento';
    }

    /**
     * Absolute url to the front page of the shop for which the account is
     * created for.
     *
     * @return string the url.
     */
    public function getFrontPageUrl()
    {
        return $this->_frontPageUrl;
    }

    /**
     * The 3-letter ISO code (ISO 4217) for the currency used by the shop for
     * which the account is created for.
     *
     * @return NostoCurrencyCode the currency code.
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     * The 2-letter ISO code (ISO 639-1) for the language used by the shop for
     * which the account is created for.
     *
     * @return NostoLanguageCode the language code.
     */
    public function getLanguage()
    {
        return $this->_language;
    }

    /**
     * The 2-letter ISO code (ISO 639-1) for the language of the account owner
     * who is creating the account.
     *
     * @return NostoLanguageCode the language code.
     */
    public function getOwnerLanguage()
    {
        return $this->_ownerLanguage;
    }

    /**
     * Meta data model for the account owner who is creating the account.
     *
     * @return NostoAccountMetaOwnerInterface the meta data model.
     */
    public function getOwner()
    {
        return $this->_owner;
    }

    /**
     * Meta data model for the account billing details.
     *
     * @return NostoAccountMetaBillingInterface the meta data model.
     */
    public function getBillingDetails()
    {
        return $this->_billing;
    }

    /**
     * Returns a list of currency objects supported by the store the account is
     * to be created for.
     *
     * @return NostoCurrency[] the currencies.
     */
    public function getCurrencies()
    {
        return $this->_currencies;
    }

    /**
     * Returns the default price variation ID if store is using multiple
     * currencies.
     * This ID identifies the price that products are specified in and can
     * be set to the currency ISO 639-1 code
     *
     * @return string|null the currency ID or null if not set.
     */
    public function getDefaultPriceVariationId()
    {
        return $this->_defaultPriceVariationId;
    }

    /**
     * Returns if the multi variant approach should be used for handling
     * multiple currencies or in pricing. Please note that only tells if the
     * setting is active. This will not take acconut whether there are variants
     * configured or not.
     *
     * @return boolean if multi variants are used
     */
    public function getUseMultiVariants()
    {
        return $this->_useMultiVariants;
    }


    /**
     * The API token used to identify an account creation.
     * This token is platform specific and issued by Nosto.
     *
     * @return string the API token.
     */
    public function getSignUpApiToken()
    {
        return $this->_signUpApiToken;
    }

    /**
     * Optional partner code for Nosto partners.
     * The code is issued by Nosto to partners only.
     *
     * @return string|null the partner code or null if none exist.
     */
    public function getPartnerCode()
    {
        return null;
    }
}
