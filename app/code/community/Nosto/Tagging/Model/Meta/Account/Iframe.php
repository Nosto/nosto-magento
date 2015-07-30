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
 * Meta data class which holds information to be sent to the Nosto account
 * configuration iframe.
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
    protected $_platform = 'magento';

    /**
     * @var string the admin user first name.
     */
    protected $_firstName;

    /**
     * @var string the admin user last name.
     */
    protected $_lastName;

    /**
     * @var    string the admin user email address.
     */
    protected $_email;

    /**
     * @var string the language ISO (ISO 639-1) code for oauth server locale.
     */
    protected $_languageIsoCode;

    /**
     * @var string the language ISO (ISO 639-1) for the store view scope.
     */
    protected $_languageIsoCodeShop;

    /**
     * @var string unique ID that identifies the Magento installation.
     */
    protected $_uniqueId;

    /**
     * @var string preview url for the product page in the active store scope.
     */
    protected $_previewUrlProduct;

    /**
     * @var string preview url for the category page in the active store scope.
     */
    protected $_previewUrlCategory;

    /**
     * @var string preview url for the search page in the active store scope.
     */
    protected $_previewUrlSearch;

    /**
     * @var string preview url for the cart page in the active store scope.
     */
    protected $_previewUrlCart;

    /**
     * @var string preview url for the front page in the active store scope.
     */
    protected $_previewUrlFront;

    /**
     * @var string the name of the store Nosto is installed in or about to be installed.
     */
    protected $_shopName;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_account_iframe');
    }

    /**
     * Loads the meta data for the given store.
     *
     * @param Mage_Core_Model_Store $store the store view to load the data for.
     */
    public function loadData(Mage_Core_Model_Store $store)
    {
        /** @var Mage_Admin_Model_User $user */
        $user = Mage::getSingleton('admin/session')->getUser();
        /** @var Nosto_Tagging_Helper_Url $urlHelper */
        $urlHelper = Mage::helper('nosto_tagging/url');
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging/data');

        $this->_firstName = $user->getFirstname();
        $this->_lastName = $user->getLastname();
        $this->_email = $user->getEmail();
        $this->_languageIsoCode = substr(
            Mage::app()->getLocale()->getLocaleCode(), 0, 2
        );
        $this->_languageIsoCodeShop = substr(
            $store->getConfig('general/locale/code'), 0, 2
        );
        $this->_uniqueId = $dataHelper->getInstallationId();
        $this->_previewUrlProduct = $urlHelper->getPreviewUrlProduct($store);
        $this->_previewUrlCategory = $urlHelper->getPreviewUrlCategory($store);
        $this->_previewUrlSearch = $urlHelper->getPreviewUrlSearch($store);
        $this->_previewUrlCart = $urlHelper->getPreviewUrlCart($store);
        $this->_previewUrlFront = $urlHelper->getPreviewUrlFront($store);
        $this->_shopName = $store->getName();
    }

    /**
     * The name of the platform the iframe is used on.
     * A list of valid platform names is issued by Nosto.
     *
     * @return string the platform name.
     */
    public function getPlatform()
    {
        return $this->_platform;
    }

    /**
     * The first name of the user who is loading the config iframe.
     *
     * @return string the first name.
     */
    public function getFirstName()
    {
        return $this->_firstName;
    }

    /**
     * The last name of the user who is loading the config iframe.
     *
     * @return string the last name.
     */
    public function getLastName()
    {
        return $this->_lastName;
    }

    /**
     * The email address of the user who is loading the config iframe.
     *
     * @return string the email address.
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * The 2-letter ISO code (ISO 639-1) for the language of the user who is
     * loading the config iframe.
     *
     * @return string the language ISO code.
     */
    public function getLanguageIsoCode()
    {
        return $this->_languageIsoCode;
    }

    /**
     * The 2-letter ISO code (ISO 639-1) for the language of the shop the
     * account belongs to.
     *
     * @return string the language ISO code.
     */
    public function getLanguageIsoCodeShop()
    {
        return $this->_languageIsoCodeShop;
    }

    /**
     * Unique identifier for the e-commerce installation.
     * This identifier is used to link accounts together that are created on
     * the same installation.
     *
     * @return string the identifier.
     */
    public function getUniqueId()
    {
        return $this->_uniqueId;
    }

    /**
     * The version number of the platform the e-commerce installation is
     * running on.
     *
     * @return string the platform version.
     */
    public function getVersionPlatform()
    {
        return Mage::getVersion();
    }

    /**
     * The version number of the Nosto module/extension running on the
     * e-commerce installation.
     *
     * @return string the module version.
     */
    public function getVersionModule()
    {
        // Path is hard-coded to be like in "etc/config.xml".
        return (string)Mage::getConfig()
            ->getNode('modules/Nosto_Tagging/version');
    }

    /**
     * An absolute URL for any product page in the shop the account is linked
     * to, with the nostodebug GET parameter enabled.
     * e.g. http://myshop.com/products/product123?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview
     * the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlProduct()
    {
        return $this->_previewUrlProduct;
    }

    /**
     * An absolute URL for any category page in the shop the account is linked
     * to, with the nostodebug GET parameter enabled.
     * e.g. http://myshop.com/products/category123?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview
     * the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlCategory()
    {
        return $this->_previewUrlCategory;
    }

    /**
     * An absolute URL for the search page in the shop the account is linked
     * to, with the nostodebug GET parameter enabled.
     * e.g. http://myshop.com/search?query=red?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview
     * the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlSearch()
    {
        return $this->_previewUrlSearch;
    }

    /**
     * An absolute URL for the shopping cart page in the shop the account is
     * linked to, with the nostodebug GET parameter enabled.
     * e.g. http://myshop.com/cart?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview
     * the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlCart()
    {
        return $this->_previewUrlCart;
    }

    /**
     * An absolute URL for the front page in the shop the account is linked to,
     * with the nostodebug GET parameter enabled.
     * e.g. http://myshop.com?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview
     * the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlFront()
    {
        return $this->_previewUrlFront;
    }

    /**
     * Returns the name of the shop context where Nosto is installed or about to be installed in.
     *
     * @return string the name.
     */
    public function getShopName()
    {
        return $this->_shopName;
    }
}
