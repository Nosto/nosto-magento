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
     * setting any error/success messages in the notification system.
     */
    public function redirectProxyAction()
    {
        if (($success = $this->getRequest()->getParam('success')) !== null) {
            Mage::getSingleton('core/session')->addSuccess($success);
        }
        if (($error = $this->getRequest()->getParam('error')) !== null) {
            Mage::getSingleton('core/session')->addError($error);
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
        if (!$this->checkStoreScope()) {
            // todo: redirect to default store instead of showing message
            Mage::getSingleton('core/session')->addNotice(
                $this->__('Please choose a shop to configure Nosto for.')
            );
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
        $response = array('success' => false);

        if ($this->getRequest()->isPost() && $this->checkStoreScope()) {
            $client = new NostoOAuthClient(
                Mage::helper('nosto_tagging/oauth')->getMetaData()
            );
            $response['success'] = true;
            $response['redirect_url'] = $client->getAuthorizationUrl();
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }

    /**
     * Creates a new Nosto account for the current scope using the Nosto API.
     */
    public function createAccountAction()
    {
        $response = array('success' => false);

        if ($this->getRequest()->isPost() && $this->checkStoreScope()) {
            /** @var Nosto_Tagging_Helper_Account $accountHelper */
            $accountHelper = Mage::helper('nosto_tagging/account');
            try {
                $email = $this->getRequest()->getPost('email');
                $meta = $accountHelper->getMetaData();
                if (Zend_Validate::is($email, 'EmailAddress')) {
                    $meta->getOwner()->setEmail($email);
                }
                $account = NostoAccount::create($meta);
                if ($accountHelper->save($account)) {
                    $response['success'] = true;
                    $response['redirect_url'] = $accountHelper->getIframeUrl($account);
                }
            } catch (NostoException $e) {
                Mage::log(
                    "\n" . $e->__toString(), Zend_Log::ERR, 'nostotagging.log'
                );
                $response['message'] = $e->__toString();
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }

    /**
     * Removes a Nosto account from the current scope and notifies Nosto.
     */
    public function removeAccountAction()
    {
        $response = array('success' => false);

        if ($this->getRequest()->isPost() && $this->checkStoreScope()) {
            /** @var Nosto_Tagging_Helper_Account $accountHelper */
            $accountHelper = Mage::helper('nosto_tagging/account');
            $account = $accountHelper->find();
            if ($account !== null && $accountHelper->remove($account)
            ) {
                $response['success'] = true;
                $response['redirect_url'] = $accountHelper->getIframeUrl();
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($response));
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
}
