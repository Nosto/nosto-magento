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

require_once(Mage::getBaseDir('lib').'/nosto/php-sdk/src/config.inc.php');

/**
 * Nosto admin controller.
 * Handles all actions for the configuration wizard as well as redirecting logic for the OAuth2 authorization cycle.
 *
 * @category    Nosto
 * @package     Nosto_Tagging
 * @author      Nosto Solutions Ltd
 */
class Nosto_Tagging_Adminhtml_NostoController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * @inheritdoc
	 */
	protected $_publicActions = array('redirectProxy');

	/**
	 * Redirect action that acts as a proxy when the front end oauth controller redirects the admin user back to the
	 * backend after finishing the oauth authorization cycle.
	 * This is a workaround as you cannot redirect directly to a protected action in the backend end from the front end.
	 * The action also handles setting any error/success messages in the notification system.
	 */
	public function redirectProxyAction()
	{
		if (($success = $this->getRequest()->getParam('success')) !== null) {
			Mage::getSingleton('core/session')->addSuccess($success);
		}
		if (($error = $this->getRequest()->getParam('error')) !== null) {
			Mage::getSingleton('core/session')->addError($error);
		}
		$this->_redirect('*/*/index', array('store' => (int)$this->getRequest()->getParam('store')));
	}

	/**
	 * Shows the main config page for the extension.
	 */
	public function indexAction()
	{
		$this->_title($this->__('Nosto'));
		if (!$this->checkStoreScope()) {
			Mage::getSingleton('core/session')->addNotice($this->__('Please choose a shop to configure Nosto for.'));
		}
		$this->loadLayout();
		$this->renderLayout();
	}

	/**
	 * Redirects user to the Nosto OAuth 2 authorization server to connect and existing nosto account to current scope.
	 */
	public function connectAccountAction()
	{
		if ($this->getRequest()->isPost() && $this->checkStoreScope()) {
			$client = new NostoOAuthClient(Mage::helper('nosto_tagging/oauth')->getMetaData());
			$this->_redirectUrl($client->getAuthorizationUrl());
		} else {
			$this->_redirect('*/*/index', array('store' => (int)$this->getRequest()->getParam('store')));
		}
	}

	/**
	 * Creates a new Nosto account for the current scope using the Nosto API.
	 */
	public function createAccountAction()
	{
		if ($this->getRequest()->isPost() && $this->checkStoreScope()) {
			try {
				$email = $this->getRequest()->getPost('nosto_create_account_email');
				/** @var Nosto_Tagging_Model_Meta_Account $meta */
				$meta = Mage::helper('nosto_tagging/account')->getMetaData();
				if (Zend_Validate::is($email, 'EmailAddress')) {
					$meta->getOwner()->setEmail($email);
				}
				$account = NostoAccount::create($meta);
				if (Mage::helper('nosto_tagging/account')->save($account)) {
					Mage::getSingleton('core/session')->addSuccess($this->__('Account created. Please check your email and follow the instructions to set a password for your new account within three days.'));
				}
			} catch (NostoException $e) {
				Mage::log("\n" . $e->__toString(), Zend_Log::ERR, 'nostotagging.log');
				Mage::getSingleton('core/session')->addException($e, $this->__('Account could not be automatically created. Please visit nosto.com to create a new account.'));
			}
		}
		$this->_redirect('*/*/index', array('store' => (int)$this->getRequest()->getParam('store')));
	}

	/**
	 * Removes a Nosto account from the current scope.
	 */
	public function removeAccountAction()
	{
		if ($this->getRequest()->isPost() && $this->checkStoreScope()) {
			if (Mage::helper('nosto_tagging/account')->remove()) {
				Mage::getSingleton('core/session')->addSuccess($this->__('Account successfully removed.'));
			}
		}
		$this->_redirect('*/*/index', array('store' => (int)$this->getRequest()->getParam('store')));
	}

	/**
	 * Checks that a valid store view scope id has been passed in the request params and set that as current store.
	 *
	 * @return bool if the current store is valid, false otherwise.
	 */
	protected function checkStoreScope()
	{
		$storeId = (int)$this->getRequest()->getParam('store');
		if ($storeId > 0) {
			Mage::app()->setCurrentStore($storeId);
			return true;
		}
		return false;
	}
}
