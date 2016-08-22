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
require_once __DIR__ . '/../../bootstrap.php';

/**
 * Nosto admin controller.
 * Handles all actions for the configuration wizard as well as redirecting
 * logic for the OAuth2 authorization cycle.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Adminhtml_NostoController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @var array Actions which can be processed without secret key validation.
     */
    protected $_publicActions = array('redirectProxy');

    /**
     * Redirect action that acts as a proxy when the front end oauth controller
     * redirects the admin user back to the backend after finishing the oauth
     * authorization cycle.
     * This is a workaround as you cannot redirect directly to a protected
     * action in the backend end from the front end. The action also handles
     * passing along any error/success messages.
     */
    public function redirectProxyAction()
    {
        $session = Mage::getSingleton('adminhtml/session');
        if ($session !== null) {
            $type = $this->getRequest()->getParam('message_type');
            $code = $this->getRequest()->getParam('message_code');
            $text = $this->getRequest()->getParam('message_text');
            if ($type !== null && $code !== null) {
                $session->setData('nosto_message', array(
                    'type' => $type,
                    'code' => $code,
                    'text' => $text,
                ));
            }
        }
        $params = array();
        if (($storeId = (int)$this->getRequest()->getParam('store')) !== 0) {
            $params['store'] = $storeId;
        }
        $this->_redirect('*/*/index', $params);
    }

    /**
     * Shows the main config page for the extension.
     */
    public function indexAction()
    {
        $this->_title($this->__('Nosto'));
        if (!$this->getSelectedStore()) {
            // If we are not under a store view, then redirect to the first
            // found one. Nosto is configured per store.
            foreach (Mage::app()->getWebsites() as $website) {
                /** @var Mage_Core_Model_Website $website */
                $storeId = $website->getDefaultGroup()->getDefaultStoreId();
                if (!empty($storeId)) {
                    $this->_redirect('*/*/index', array('store' => $storeId));
                    return; // stop execution after redirect is set.
                }
            }
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Redirects user to the Nosto OAuth 2 authorization server to connect and
     * existing nosto account to current scope.
     */
    public function connectAccountAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);

        $store = $this->getSelectedStore();
        if ($this->getRequest()->isPost() && $store !== null) {
            /** @var Nosto_Tagging_Helper_Oauth $oauthHelper */
            $oauthHelper = Mage::helper('nosto_tagging/oauth');
            $client = new NostoOAuthClient($oauthHelper->getMetaData($store));
            $responseBody = array(
                'success' => true,
                'redirect_url' => $client->getAuthorizationUrl(),
            );
        }

        if (!isset($responseBody)) {
            /** @var Nosto_Tagging_Helper_Account $accountHelper */
            $accountHelper = Mage::helper('nosto_tagging/account');
            $responseBody = array(
                'success' => false,
                'redirect_url' => $accountHelper->getIframeUrl(
                    $store,
                    null, // connect attempt failed, so we have no account.
                    array(
                        'message_type' => NostoMessage::TYPE_ERROR,
                        'message_code' => NostoMessage::CODE_ACCOUNT_CONNECT,
                    )
                )
            );
        }

        $this->getResponse()->setBody(json_encode($responseBody));
    }

    /**
     * Creates a new Nosto account for the current scope using the Nosto API.
     */
    public function createAccountAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);

        /** @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');

        $store = $this->getSelectedStore();
        if ($this->getRequest()->isPost() && $store !== null) {
            try {
                $email = $this->getRequest()->getPost('email');
                $details = $this->getRequest()->getPost('details');
                $meta = $accountHelper->getMetaData($store);
                if (Zend_Validate::is($email, 'EmailAddress')) {
                    /** @var Nosto_Tagging_Model_Meta_Account_Owner $owner */
                    $owner = $meta->getOwner();
                    $owner->setEmail($email);
                }
                if (!empty($details)) {
                   $meta->setDetails(json_decode($details));
                }
                $service = new NostoOperationAccount($meta);
                $account = $service->create();
                if ($accountHelper->save($account, $store)) {
                    $responseBody = array(
                        'success' => true,
                        'redirect_url' => $accountHelper->getIframeUrl(
                            $store,
                            $account,
                            array(
                                'message_type' => NostoMessage::TYPE_SUCCESS,
                                'message_code' => NostoMessage::CODE_ACCOUNT_CREATE,
                            )
                        )
                    );
                }
            } catch (NostoException $e) {
                Mage::log(
                    "\n" . $e->__toString(), Zend_Log::ERR, Nosto_Tagging_Model_Base::LOG_FILE_NAME
                );
            }
        }

        if (!isset($responseBody)) {
            $responseBody = array(
                'success' => false,
                'redirect_url' => $accountHelper->getIframeUrl(
                    $store,
                    null, // account creation failed, so we have none.
                    array(
                        'message_type' => NostoMessage::TYPE_ERROR,
                        'message_code' => NostoMessage::CODE_ACCOUNT_CREATE,
                    )
                )
            );
        }

        $this->getResponse()->setBody(json_encode($responseBody));
    }

    /**
     * Removes a Nosto account from the current scope and notifies Nosto.
     */
    public function removeAccountAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);

        /** @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');

        $store = $this->getSelectedStore();
        if ($this->getRequest()->isPost() && $store !== null) {
            $account = $accountHelper->find($store);
            if ($account !== null && $accountHelper->remove($account, $store)) {
                $responseBody = array(
                    'success' => true,
                    'redirect_url' => $accountHelper->getIframeUrl(
                        $store,
                        null, // we don't have an account anymore
                        array(
                            'message_type' => NostoMessage::TYPE_SUCCESS,
                            'message_code' => NostoMessage::CODE_ACCOUNT_DELETE,
                        )
                    )
                );
            }
        }

        if (!isset($responseBody)) {
            $responseBody = array(
                'success' => false,
                'redirect_url' => $accountHelper->getIframeUrl(
                    $store,
                    $accountHelper->find($store),
                    array(
                        'message_type' => NostoMessage::TYPE_ERROR,
                        'message_code' => NostoMessage::CODE_ACCOUNT_DELETE,
                    )
                )
            );
        }

        $this->getResponse()->setBody(json_encode($responseBody));
    }

    /**
     * Returns the currently selected store view.
     * If it is single store setup, then just return the default store.
     * If it is a multi store setup, the expect a store id to passed in the
     * request params and return that store as the current one.
     *
     * @return Mage_Core_Model_Store|null the store view or null if not found.
     */
    protected function getSelectedStore()
    {
        if (Mage::app()->isSingleStoreMode()) {
            return Mage::app()->getStore(true);
        } elseif (($storeId = (int)$this->getRequest()->getParam('store')) !== 0) {
            return Mage::app()->getStore($storeId);
        } else {
            return null;
        }
    }

    /**
     * Checks if logged in user has privilege to access Nosto settings
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        /** @var Mage_Admin_Model_Session $session */
        $session = Mage::getSingleton('admin/session');
        return $session->isAllowed('nosto');
    }
}
