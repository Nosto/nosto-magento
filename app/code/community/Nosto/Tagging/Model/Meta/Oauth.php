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
 * @category    Nosto
 * @package     Nosto_Tagging
 * @copyright   Copyright (c) 2013-2015 Nosto Solutions Ltd (http://www.nosto.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Meta data class which holds information needed to complete OAuth2 requests.
 *
 * @category    Nosto
 * @package     Nosto_Tagging
 * @author      Nosto Solutions Ltd
 */
class Nosto_Tagging_Model_Meta_Oauth extends Mage_Core_Model_Abstract implements NostoOAuthClientMetaDataInterface
{
    /**
     * @var string the url where the oauth2 server should redirect after authorization is done.
     */
    protected $redirectUrl;

    /**
     * @var string the language ISO code for localization on oauth2 server.
     */
    protected $languageIsoCode;

    /**
     * Constructor.
     * Sets initial values.
     */
    public function __construct()
    {
        parent::__construct();

        $this->redirectUrl = Mage::getUrl('nosto/oauth', array('_store' => Mage::app()->getStore()->getId(), '_store_to_url' => true));
        $this->languageIsoCode = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_oauth');
    }

    /**
     * @inheritdoc
     */
    public function getClientId()
    {
        return 'magento';
    }

    /**
     * @inheritdoc
     */
    public function getClientSecret()
    {
        return 'magento';
    }

    /**
     * @inheritdoc
     */
    public function getScopes()
    {
        // We want all the available Nosto API tokens.
        return NostoApiToken::$tokenNames;
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * Sets the redirect url.
     *
     * @param string $url the url.
     */
    public function setRedirectUrl($url)
    {
        $this->redirectUrl = $url;
    }

    /**
     * @inheritdoc
     */
    public function getLanguageIsoCode()
    {
        return $this->languageIsoCode;
    }

    /**
     * Sets the language ISO code.
     *
     * @param string $code the ISO code.
     */
    public function setLanguageIsoCode($code)
    {
        $this->languageIsoCode = $code;
    }
}
