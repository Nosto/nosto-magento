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
 * @category    design
 * @package     base_default
 * @copyright   Copyright (c) 2013 Nosto Solutions Ltd (http://www.nosto.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once(Mage::getBaseDir('lib').'/nosto/sdk/src/config.inc.php');

class Nosto_Tagging_Adminhtml_NostoController extends Mage_Adminhtml_Controller_Action
{
	/**
	 * Shows the main config page for the extension.
	 */
	public function indexAction()
	{
		$this->_title($this->__('Nosto'));
		$this->loadLayout();
		$this->renderLayout();
	}

	/**
	 * Redirects user to the Nosto OAuth 2 authorization server to connect and existing nosto account to current scope.
	 */
	public function connectAccountAction()
	{
		if ($this->getRequest()->isPost()) {
			$storeId = (int)$this->getRequest()->getPost('nosto_store_id');
			if ($storeId > 0) {
				Mage::app()->setCurrentStore($storeId);
			}
			$client = new NostoOAuthClient(Mage::helper('nosto_tagging/oauth')->getMetaData());
			$this->_redirectUrl($client->getAuthorizationUrl());
		} else {
			$storeId = (int)$this->getRequest()->getParam('store');
			$this->_redirect('*/*/index', array('store' => $storeId));
		}
	}

	/**
	 * Creates a new Nosto account for the current scope using the Nosto API.
	 */
	public function createAccountAction()
	{
		Mage::app()->setCurrentStore((int)$this->getRequest()->getParam('store'));
		if ($this->getRequest()->isPost()) {
			$storeId = (int)$this->getRequest()->getPost('nosto_store_id');
			if ($storeId > 0) {
				Mage::app()->setCurrentStore($storeId);
			}
			try {
				$email = $this->getRequest()->getPost('nosto_create_account_email');
				/** @var Nosto_Tagging_Model_Meta_Account $meta */
				$meta = Mage::helper('nosto_tagging/account')->getMetaData();
				if (!empty($email)) {
					$meta->getOwner()->setEmail($email);
				}
				$account = NostoAccount::create($meta);
				if (Mage::helper('nosto_tagging/account')->save($account)) {
					Mage::getSingleton('core/session')->addSuccess($this->__('Account created. Please check your email and follow the instructions to set a password for your new account within three days.'));
				} else {
					throw new NostoException('Failed to create account');
				}
			} catch (NostoException $e) {
				Mage::log("\n" . $e->__toString(), Zend_Log::ERR, 'nostotagging.log');
				Mage::getSingleton('core/session')->addException($e, $this->__('Account could not be automatically created. Please visit nosto.com to create a new account.'));
			}
		} else {
			$storeId = (int)$this->getRequest()->getParam('store');
		}
		$this->_redirect('*/*/index', array('store' => $storeId));
	}

	/**
	 * Removes a Nosto account from the current scope.
	 */
	public function removeAccountAction()
	{
		if ($this->getRequest()->isPost()) {
			$storeId = (int)$this->getRequest()->getPost('nosto_store_id');
			if ($storeId > 0) {
				Mage::app()->setCurrentStore($storeId);
			}
			$account = Mage::helper('nosto_tagging/account')->find(Mage::app()->getStore());
			if (Mage::helper('nosto_tagging/account')->remove($account)) {
				Mage::getSingleton('core/session')->addSuccess($this->__('Account successfully removed.'));
			} else {
				Mage::getSingleton('core/session')->addError($this->__('Failed to remove account.'));
			}
		} else {
			$storeId = (int)$this->getRequest()->getParam('store');
		}
		$this->_redirect('*/*/index', array('store' => $storeId));
	}
}
