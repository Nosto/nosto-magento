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
 * Meta data class which holds information needed to complete OAuth2 requests.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Oauth extends Mage_Core_Model_Abstract implements NostoOAuthClientMetaDataInterface
{
    /**
     * @var string the url where the oauth2 server should redirect after
     * authorization is done.
     */
    protected $_redirectUrl;

    /**
     * @var string the language ISO code for localization on oauth2 server.
     */
    protected $_languageIsoCode;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_oauth');
    }

    /**
     * Loads the meta data for the given store.
     *
     * @param Mage_Core_Model_Store $store the store view to load the data for.
     */
    public function loadData(Mage_Core_Model_Store $store)
    {
        $this->_redirectUrl = Mage::getUrl(
            'nosto/oauth',
            array(
                '_store' => $store->getId(),
                '_store_to_url' => true
            )
        );
        $this->_languageIsoCode = substr(
            Mage::app()->getLocale()->getLocaleCode(), 0, 2
        );
    }

    /**
     * The OAuth2 client ID.
     * This will be a platform specific ID that Nosto will issue.
     *
     * @return string the client id.
     */
    public function getClientId()
    {
        return 'magento';
    }

    /**
     * The OAuth2 client secret.
     * This will be a platform specific secret that Nosto will issue.
     *
     * @return string the client secret.
     */
    public function getClientSecret()
    {
        return 'magento';
    }

    /**
     * The scopes for the OAuth2 request.
     * These are used to request specific API tokens from Nosto and should
     * almost always be the ones defined in NostoApiToken::$tokenNames.
     *
     * @return array the scopes.
     */
    public function getScopes()
    {
        // We want all the available Nosto API tokens.
        return NostoApiToken::$tokenNames;
    }

    /**
     * The OAuth2 redirect url to where the OAuth2 server should redirect the
     * user after authorizing the application to act on the users behalf.
     * This url must by publicly accessible and the domain must match the one
     * defined for the Nosto account.
     *
     * @return string the url.
     */
    public function getRedirectUrl()
    {
        return $this->_redirectUrl;
    }

    /**
     * The 2-letter ISO code (ISO 639-1) for the language the OAuth2 server
     * uses for UI localization.
     *
     * @return string the ISO code.
     */
    public function getLanguageIsoCode()
    {
        return $this->_languageIsoCode;
    }
}
