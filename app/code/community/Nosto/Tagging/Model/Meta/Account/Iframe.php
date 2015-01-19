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
 * @copyright Copyright (c) 2013-2015 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Meta data class which holds information to be sent to the Nosto account configuration iframe.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Account_Iframe extends Mage_Core_Model_Abstract implements NostoAccountMetaDataIframeInterface
{
    /**
     * @var string the name of the platform the iframe is used on.
     */
    protected $platform = 'magento';

    /**
     * @var string the admin user first name.
     */
    protected $firstName;

    /**
     * @var string the admin user last name.
     */
    protected $lastName;

    /**
     * @var    string the admin user email address.
     */
    protected $email;

    /**
     * @var string the language ISO (ISO 639-1) code for localization on oauth2 server.
     */
    protected $languageIsoCode;

    /**
     * @var string the language ISO (ISO 639-1) for the store view scope.
     */
    protected $languageIsoCodeShop;

    /**
     * @var string unique ID that identifies the Magento installation and all the accounts within.
     */
    protected $uniqueId;

    /**
     * @var string preview url for the product page in the active store view scope.
     */
    protected $previewUrlProduct;

    /**
     * @var string preview url for the category page in the active store view scope.
     */
    protected $previewUrlCategory;

    /**
     * @var string preview url for the search page in the active store view scope.
     */
    protected $previewUrlSearch;

    /**
     * @var string preview url for the cart page in the active store view scope.
     */
    protected $previewUrlCart;

    /**
     * @var string preview url for the front page in the active store view scope.
     */
    protected $previewUrlFront;

    /**
     * Constructor.
     * Sets initial values.
     */
    public function __construct()
    {
        parent::__construct();

        /** @var Mage_Admin_Model_User $user */
        $user = Mage::getSingleton('admin/session')->getUser();
        /** @var Nosto_Tagging_Helper_Url $urlHelper */
        $urlHelper = Mage::helper('nosto_tagging/url');
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging/data');

        $this->firstName = $user->getFirstname();
        $this->lastName = $user->getLastname();
        $this->email = $user->getEmail();
        $this->languageIsoCode = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
        $this->languageIsoCodeShop = substr(Mage::app()->getStore()->getConfig('general/locale/code'), 0, 2);
        $this->uniqueId = $dataHelper->getInstallationId();
        $this->previewUrlProduct = $urlHelper->getPreviewUrlProduct();
        $this->previewUrlCategory = $urlHelper->getPreviewUrlCategory();
        $this->previewUrlSearch = $urlHelper->getPreviewUrlSearch();
        $this->previewUrlCart = $urlHelper->getPreviewUrlCart();
        $this->previewUrlFront = $urlHelper->getPreviewUrlFront();
    }

    /**
     * Internal Magento constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_account_iframe');
    }

    /**
     * The name of the platform the iframe is used on.
     * A list of valid platform names is issued by Nosto.
     *
     * @return string the platform name.
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Sets the first name of the admin user.
     *
     * @param string $firstName the first name.
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * The first name of the user who is loading the config iframe.
     *
     * @return string the first name.
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Sets the last name of the admin user.
     *
     * @param string $lastName the last name.
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * The last name of the user who is loading the config iframe.
     *
     * @return string the last name.
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Sets the email address of the admin user.
     *
     * @param string $email the email address.
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * The email address of the user who is loading the config iframe.
     *
     * @return string the email address.
     */
    public function getEmail()
    {
        return $this->email;
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

    /**
     * The 2-letter ISO code (ISO 639-1) for the language of the user who is loading the config iframe.
     *
     * @return string the language ISO code.
     */
    public function getLanguageIsoCode()
    {
        return $this->languageIsoCode;
    }

    /**
     * The 2-letter ISO code (ISO 639-1) for the language of the shop the account belongs to.
     *
     * @return string the language ISO code.
     */
    public function getLanguageIsoCodeShop()
    {
        return $this->languageIsoCodeShop;
    }

    /**
     * Unique identifier for the e-commerce installation.
     * This identifier is used to link accounts together that are created on the same installation.
     *
     * @return string the identifier.
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * The version number of the platform the e-commerce installation is running on.
     *
     * @return string the platform version.
     */
    public function getVersionPlatform()
    {
        return Mage::getVersion();
    }

    /**
     * The version number of the Nosto module/extension running on the e-commerce installation.
     *
     * @return string the module version.
     */
    public function getVersionModule()
    {
        // Path is hard-coded to be like in "etc/config.xml".
        return (string)Mage::getConfig()->getNode('modules/Nosto_Tagging/version');
    }

    /**
     * An absolute URL for any product page in the shop the account is linked to, with the nostodebug GET parameter enabled.
     * e.g. http://myshop.com/products/product123?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlProduct()
    {
        return $this->previewUrlProduct;
    }

    /**
     * An absolute URL for any category page in the shop the account is linked to, with the nostodebug GET parameter enabled.
     * e.g. http://myshop.com/products/category123?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlCategory()
    {
        return $this->previewUrlCategory;
    }

    /**
     * An absolute URL for the search page in the shop the account is linked to, with the nostodebug GET parameter enabled.
     * e.g. http://myshop.com/search?query=red?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlSearch()
    {
        return $this->previewUrlSearch;
    }

    /**
     * An absolute URL for the shopping cart page in the shop the account is linked to, with the nostodebug GET parameter enabled.
     * e.g. http://myshop.com/cart?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlCart()
    {
        return $this->previewUrlCart;
    }

    /**
     * An absolute URL for the front page in the shop the account is linked to, with the nostodebug GET parameter enabled.
     * e.g. http://myshop.com?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlFront()
    {
        return $this->previewUrlFront;
    }
}
