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
 * @copyright Copyright (c) 2013-2017 Nosto Solutions Ltd (http://www.nosto.com)
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
            /** @var Nosto_Tagging_Helper_Oauth $helper */
            $helper = Mage::helper('nosto_tagging/oauth');
            $client = new NostoOAuthClient(
                $helper->getMetaData($store)
            );
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
     * Redirects user to the Nosto OAuth 2 authorization server to fetch missing
     * scopes (API tokens) for an account.
     */
    public function syncAccountAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        /** @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        $store = $this->getSelectedStore();
        $account = !is_null($store) ? $accountHelper->find($store) : null;
        if ($this->getRequest()->isPost() && !is_null($store) && !is_null($account)) {
            /** @var Nosto_Tagging_Model_Meta_Oauth $meta */
            $meta = new Nosto_Tagging_Model_Meta_Oauth();
            $meta->loadData($store);
            $client = new NostoOAuthClient($meta);
            $responseBody = array(
                'success' => true,
                'redirect_url' => $client->getAuthorizationUrl(),
            );
        }
        if (!isset($responseBody)) {
            $responseBody = array(
                'success' => false,
                'redirect_url' => $accountHelper->getIframeUrl(
                    $store,
                    $account,
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
                    /** @noinspection PhpUndefinedMethodInspection */
                    $meta->getOwner()->setEmail($email);
                }
                if (!empty($details)) {
                   $meta->setDetails(json_decode($details));
                }
                $account = NostoAccount::create($meta);
                if ($accountHelper->save($account, $store)) {
                    $accountHelper->updateCurrencyExchangeRates($account, $store);
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
     * Resets Nosto settings inside Magento
     */
    public function resetAccountSettingsAction()
    {
        $storeId = $this->getRequest()->getParam('store');
        /* @var $adminSession Mage_Admin_Model_Session */
        $adminSession = Mage::getSingleton('adminhtml/session');
        if (empty($storeId)) {
            $adminSession->addError(
                'Nosto account could not be resetted due to a missing store'
            );
            $this->_redirect('*/*/index');

            return;
        }

        /* @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        /* @var $store Mage_Core_Model_Store */
        $store = Mage::getModel('core/store')->load($storeId);
        if ($store instanceof Mage_Core_Model_Store === false ) {
            $adminSession->addError(
                'Nosto account could not be resetted due to a invalid store id'
            );
            $this->_redirect('*/*/index');

            return;
        }
        $nostoAccount = $accountHelper->find($store);
        if ($nostoAccount instanceof NostoAccount == false) {
            $adminSession->addError(
                'No Nosto account found for this store'
            );
            $this->_redirect('*/*/index');

            return;
        }

        $accountHelper->resetAccountSettings($nostoAccount, $store);
        $adminSession->addSuccess(
            'Nosto account settings successfully resetted. Please create new account or connect with existing Nosto account'
        );
        $this->_redirect(
            'adminhtml/nosto/index/',
            array('store'=>$store->getId())
        );

        return;
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
     * Ajax action for updating the currency exchange rates.
     *
     * Used from the extension system configuration page.
     * Checks if any stores support multi currency before trying to update the
     * exchange rate for each store/account.
     */
    public function ajaxUpdateExchangeRatesAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        $responseBody = array('success' => true, 'data' => array());
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        /** @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        /** @var Mage_Core_Model_Store[] $stores */
        $storeId = $this->getRequest()->getParam('store');
        if (!empty($storeId)) {
            $stores = array(Mage::app()->getStore($storeId));
        } else {
            $stores = Mage::app()->getStores();
        }
        $countStores = count($stores);
        $countStoresWithoutMultiCurrency = 0;
        foreach ($stores as $store) {
            if (!$helper->isMultiCurrencyMethodExchangeRate($store)) {
                $countStoresWithoutMultiCurrency++;
                continue;
            }
            $account = $accountHelper->find($store);
            if (is_null($account)) {
                continue;
            }
            if ($accountHelper->updateCurrencyExchangeRates($account, $store)) {
                $responseBody['data'][] = array(
                    'type' => 'success',
                    'message' => $helper->__(sprintf("The exchange rates have been updated for the %s store.", $store->getName()))
                );
            } else {
                $responseBody['data'][] = array(
                    'type' => 'error',
                    'message' => $helper->__(sprintf("There was an error updating the exchange rates for the %s store.", $store->getName()))
                );
            }
        }
        if ($countStores === $countStoresWithoutMultiCurrency) {
            $responseBody['data'][] = array(
                'type' => 'error',
                'message' => $helper->__("Failed to find any stores in the current scope with other currencies than the base currency configured.")
            );
        } elseif (empty($responseBody['data'])) {
            $responseBody['data'][] = array(
                'type' => 'error',
                'message' => $helper->__("Nosto has not been installed in any of the stores in the current scope. Please make sure you have installed Nosto to at least one of your stores in the scope.")
            );
        }
        $this->getResponse()->setBody(json_encode($responseBody));
    }

    /**
     * Ajax action for updating a Nosto account.
     *
     * Used from the extension system configuration page.
     * Checks the scope of the update on a store/website/global level.
     */
    public function ajaxUpdateAccountAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        $responseBody = array('success' => true, 'data' => array());
        /** @var Mage_Core_Model_Store[] $stores */
        $storeId = $this->getRequest()->getParam('store');
        if (!empty($storeId)) {
            $stores = array(Mage::app()->getStore($storeId));
        } else {
            $stores = Mage::app()->getStores();
        }
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        /** @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        foreach ($stores as $store) {
            $account = $accountHelper->find($store);
            if (is_null($account)) {
                continue;
            }
            if ($accountHelper->updateAccount($account, $store)) {
                $responseBody['data'][] = array(
                    'type' => 'success',
                    'message' => $helper->__(sprintf("The account has been updated for the %s store.", $store->getName()))
                );
            } else {
                $responseBody['data'][] = array(
                    'type' => 'error',
                    'message' => $helper->__(sprintf("There was an error updating the account for the %s store.", $store->getName()))
                );
            }
        }
        if (empty($responseBody['data'])) {
            $responseBody['data'][] = array(
                'type' => 'error',
                'message' => $helper->__("Nosto has not been installed in any of the stores in the current scope. Please make sure you have installed Nosto to at least one of your stores in the scope.")
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
