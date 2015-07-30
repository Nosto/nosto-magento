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
 * @copyright Copyright (c) 2013-2015 Nosto Solutions Ltd (http://www.nosto.com)
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
class Nosto_Tagging_Model_Meta_Account extends Mage_Core_Model_Abstract implements NostoAccountMetaDataInterface
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
     * @var string the store currency ISO (ISO 4217) code.
     */
    protected $_currencyCode;

    /**
     * @var string the store language ISO (ISO 639-1) code.
     */
    protected $_languageCode;

    /**
     * @var string the owner language ISO (ISO 639-1) code.
     */
    protected $_ownerLanguageCode;

    /**
     * @var Nosto_Tagging_Model_Meta_Account_Owner the account owner meta model.
     */
    protected $_owner;

    /**
     * @var Nosto_Tagging_Model_Meta_Account_Billing the billing meta model.
     */
    protected $_billing;

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
        $this->_currencyCode = $store->getBaseCurrencyCode();
        $this->_languageCode = substr(
            $store->getConfig('general/locale/code'), 0, 2
        );
        $this->_ownerLanguageCode = substr(
            Mage::app()->getLocale()->getLocaleCode(), 0, 2
        );

        /** @var Nosto_Tagging_Model_Meta_Account_Owner $owner */
        $owner = Mage::getModel('nosto_tagging/meta_account_owner');
        $owner->loadData($store);
        $this->_owner = $owner;

        /** @var Nosto_Tagging_Model_Meta_Account_Billing $billing */
        $billing = Mage::getModel('nosto_tagging/meta_account_billing');
        $billing->loadData($store);
        $this->_billing = $billing;
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
     * @return string the currency ISO code.
     */
    public function getCurrencyCode()
    {
        return $this->_currencyCode;
    }

    /**
     * The 2-letter ISO code (ISO 639-1) for the language used by the shop for
     * which the account is created for.
     *
     * @return string the language ISO code.
     */
    public function getLanguageCode()
    {
        return $this->_languageCode;
    }

    /**
     * The 2-letter ISO code (ISO 639-1) for the language of the account owner
     * who is creating the account.
     *
     * @return string the language ISO code.
     */
    public function getOwnerLanguageCode()
    {
        return $this->_ownerLanguageCode;
    }

    /**
     * Meta data model for the account owner who is creating the account.
     *
     * @return NostoAccountMetaDataOwnerInterface the meta data model.
     */
    public function getOwner()
    {
        return $this->_owner;
    }

    /**
     * Meta data model for the account billing details.
     *
     * @return NostoAccountMetaDataBillingDetailsInterface the meta data model.
     */
    public function getBillingDetails()
    {
        return $this->_billing;
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
        // todo: implement partner code storage.
        return null;
    }
}
