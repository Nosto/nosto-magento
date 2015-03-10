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

require_once Mage::getBaseDir('lib') . '/nosto/php-sdk/src/config.inc.php';

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
        $params = array();
        if (($type = $this->getRequest()->getParam('message_type')) !== null) {
            $params['message_type'] = $type;
        }
        if (($code = $this->getRequest()->getParam('message_code')) !== null) {
            $params['message_code'] = $code;
        }
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
        if (!$this->checkStoreScope()) {
            // If we are not under a store view, then redirect to the first
            // found one. Nosto is configured per store.
            foreach (Mage::app()->getWebsites() as $website) {
                $storeId = $website->getDefaultGroup()->getDefaultStoreId();
                if (!empty($storeId)) {
                    $this->_redirect('*/*/index', array('store' => $storeId));
                    break;
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
        if ($this->getRequest()->isPost() && $this->checkStoreScope()) {
            $client = new NostoOAuthClient(
                Mage::helper('nosto_tagging/oauth')->getMetaData()
            );
            $response = new NostoXhrResponse();
            $response->setSuccess(true)
                ->setRedirectUrl($client->getAuthorizationUrl());
        }

        if (!isset($response)) {
            /** @var Nosto_Tagging_Helper_Account $accountHelper */
            $accountHelper = Mage::helper('nosto_tagging/account');

            $response = new NostoXhrResponse();
            $response->setRedirectUrl(
                $accountHelper->getIframeUrl(
                    null, // connect attempt failed, so we have no account.
                    array(
                        'message_type' => NostoMessage::TYPE_ERROR,
                        'message_code' => NostoMessage::CODE_ACCOUNT_CONNECT,
                    )
                )
            );
        }

        $this->sendXhrResponse($response);
    }

    /**
     * Creates a new Nosto account for the current scope using the Nosto API.
     */
    public function createAccountAction()
    {
        /** @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');

        if ($this->getRequest()->isPost() && $this->checkStoreScope()) {
            try {
                $email = $this->getRequest()->getPost('email');
                $meta = $accountHelper->getMetaData();
                if (Zend_Validate::is($email, 'EmailAddress')) {
                    $meta->getOwner()->setEmail($email);
                }
                $account = NostoAccount::create($meta);
                if ($accountHelper->save($account)) {
                    $response = new NostoXhrResponse();
                    $response->setSuccess(true)->setRedirectUrl(
                        $accountHelper->getIframeUrl(
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
                    "\n" . $e->__toString(), Zend_Log::ERR, 'nostotagging.log'
                );
            }
        }

        if (!isset($response)) {
            $response = new NostoXhrResponse();
            $response->setRedirectUrl(
                $accountHelper->getIframeUrl(
                    null, // account creation failed, so we have none.
                    array(
                        'message_type' => NostoMessage::TYPE_ERROR,
                        'message_code' => NostoMessage::CODE_ACCOUNT_CREATE,
                    )
                )
            );
        }

        $this->sendXhrResponse($response);
    }

    /**
     * Removes a Nosto account from the current scope and notifies Nosto.
     */
    public function removeAccountAction()
    {
        /** @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');

        if ($this->getRequest()->isPost() && $this->checkStoreScope()) {
            $account = $accountHelper->find();
            if ($account !== null && $accountHelper->remove($account)) {
                $response = new NostoXhrResponse();
                $response->setSuccess(true)->setRedirectUrl(
                    $accountHelper->getIframeUrl(
                        null, // we don't have an account anymore
                        array(
                            'message_type' => NostoMessage::TYPE_SUCCESS,
                            'message_code' => NostoMessage::CODE_ACCOUNT_DELETE,
                        )
                    )
                );
            }
        }

        if (!isset($response)) {
            $response = new NostoXhrResponse();
            $response->setRedirectUrl(
                $accountHelper->getIframeUrl(
                    $accountHelper->find(),
                    array(
                        'message_type' => NostoMessage::TYPE_ERROR,
                        'message_code' => NostoMessage::CODE_ACCOUNT_DELETE,
                    )
                )
            );
        }

        $this->sendXhrResponse($response);
    }

    /**
     * Checks that a valid store view scope is available.
     * If it is single store setup, then just use the default store as current.
     * If it is a multi store setup, the expect a store id to passed in the
     * request params and set that store as the current one.
     *
     * @return bool if the current store is valid, false otherwise.
     */
    protected function checkStoreScope()
    {
        if (Mage::app()->isSingleStoreMode()) {
            $storeId = (int)Mage::app()->getStore(true)->getId();
            if ($storeId > 0) {
                Mage::app()->setCurrentStore($storeId);
                return true;
            }
        } elseif (($storeId = (int)$this->getRequest()->getParam('store')) !== 0) {
            Mage::app()->setCurrentStore($storeId);
            return true;
        }
        return false;
    }

    /**
     * Sends an XHR response to the browser.
     *
     * @param NostoXhrResponse $response the response object.
     */
    protected function sendXhrResponse(NostoXhrResponse $response)
    {
        $this->getResponse()->setHeader('Content-type', $response->contentType);
        $this->getResponse()->setBody($response->__toString());
    }
}
