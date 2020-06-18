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
 * @copyright Copyright (c) 2013-2020 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Nosto_Tagging_Helper_Log as NostoLog;

/**
 * Nosto iframe block.
 * Adds an iframe for configuring a Nosto account.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Adminhtml_Iframe extends Mage_Adminhtml_Block_Template
{
    use Nosto_Mixins_IframeTrait;
    const IFRAME_VERSION = 1;

    /**
     * @var string the iframe url if SSO to Nosto can be made.
     */
    protected $_iframeUrl;

    /**
     * Gets the iframe url for the account settings page from Nosto.
     * This url is only returned if the current admin user can be logged in
     * with SSO to Nosto.
     *
     * @return string the iframe url or empty string if it cannot be created.
     */
    public function getIframeUrl()
    {
        if ($this->_iframeUrl !== null) {
            return $this->_iframeUrl;
        }

        $params = array();
        // Pass any error/success messages we might have to the iframe.
        // These can be available when getting redirect back from the OAuth
        // front controller after connecting a Nosto account to a store.
        $session = Mage::getSingleton('adminhtml/session');
        if ($session !== null) {
            $nostoMessage = $session->getData('nosto_message');
            if (!empty($nostoMessage)) {
                if (isset($nostoMessage['type'], $nostoMessage['code'])) {
                    $params['message_type'] = $nostoMessage['type'];
                    $params['message_code'] = $nostoMessage['code'];
                    if (isset($nostoMessage['text'])) {
                        $params['message_text'] = $nostoMessage['text'];
                    }
                }

                $session->setData('nosto_message', null);
            }
        }

        $params['v'] = self::IFRAME_VERSION;
        try {
            $store = $this->getSelectedStore();
            /* @var Mage_Core_Model_App_Emulation $emulation */
            $emulation = Mage::getSingleton('core/app_emulation');
            $env = $emulation->startEnvironmentEmulation($store->getId());
            $this->_iframeUrl = self::buildURL($params);
            $emulation->stopEnvironmentEmulation($env);
            return $this->_iframeUrl;
        } catch (\Exception $e) {
            NostoLog::exception($e);
            return '';
        }
    }

    /**
     * Returns the currently selected store view.
     *
     * @return Mage_Core_Model_Store|null the store view model.
     *
     * @throws Exception if store view cannot be found.
     */
    public function getSelectedStore()
    {
        $store = null;

        if (Mage::app()->isSingleStoreMode()) {
            $store = Mage::app()->getStore(true);
        } elseif (($id = (int)$this->getRequest()->getParam('store')) !== 0) {
            $store = Mage::app()->getStore($id);
        } else {
            Mage::throwException('Failed to find currently selected store view.');
        }

        return $store;
    }

    /**
     * Returns the valid origin url from where the iframe should accept
     * postMessage calls.
     * This is configurable to support different origins based on $_ENV.
     *
     * @return string the origin url.
     */
    public function getIframeOrigin()
    {
        return Nosto_Nosto::getIframeOriginRegex();
    }

    /**
     * @inheritdoc
     */
    public function getIframe()
    {
        /** @var Nosto_Tagging_Model_Meta_Account_Iframe $iframeParams */
        $iframeParams = Mage::getModel('nosto_tagging/meta_account_iframe');
        try {
            $iframeParams->loadData($this->getSelectedStore());
        } catch (\Exception $e) {
            NostoLog::exception($e);
        }

        return $iframeParams;
    }

    /**
     * @inheritdoc
     */
    public function getUser()
    {
        /** @var Nosto_Tagging_Model_Meta_User $currentUser */
        $currentUser = Mage::getModel('nosto_tagging/meta_user');
        $currentUser->loadData();
        return $currentUser;
    }

    /**
     * @inheritdoc
     */
    public function getAccount()
    {
        /** @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        try {
            return $accountHelper->find($this->getSelectedStore());
        } catch (\Exception $e) {
            NostoLog::exception($e);
            return null;
        }
    }

    /**
     * Returns URL for the configuration section
     *
     * @return string
     */
    public function getConfigurationUrl()
    {
        /** @var Nosto_Tagging_Helper_Url $urlHelper */
        $urlHelper = Mage::helper('nosto_tagging/url');
        try {
            $store = $this->getSelectedStore();
            return $urlHelper->getAdminNostoConfiguratioUrl($store);
        } catch (\Exception $e) {
            NostoLog::exception($e);
            return '';
        }
    }
}
