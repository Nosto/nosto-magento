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

require_once(Mage::getBaseDir('lib').'/nosto/php-sdk/src/config.inc.php');

/**
 * OAuth2 controller.
 * Handles the redirect from Nosto OAuth2 authorization server.
 *
 * @category    Nosto
 * @package     Nosto_Tagging
 * @author      Nosto Solutions Ltd
 */
class Nosto_tagging_OauthController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Handles the redirect from Nosto oauth2 authorization server when an existing account is connected to a store.
	 * This is handled in the front end as the oauth2 server validates the "return_url" sent in the first step of the
	 * authorization cycle, and requires it to be from the same domain that the account is configured for and only
	 * redirects to that domain.
	 */
	public function indexAction()
	{
		if (($code = $this->getRequest()->getParam('code')) !== null) {
			try {
				$account = NostoAccount::syncFromNosto(Mage::helper('nosto_tagging/oauth')->getMetaData(), $code);
				if (Mage::helper('nosto_tagging/account')->save($account)) {
					$params = array(
						'success' => $this->__('Account %s successfully connected to Nosto.', $account->name),
						'store' => (int)Mage::app()->getStore()->getId(),
					);
				} else {
					throw new NostoException('Failed to connect account');
				}
			} catch (NostoException $e) {
				Mage::log("\n" . $e->__toString(), Zend_Log::ERR, 'nostotagging.log');
				$params = array(
					'error' => $this->__('Account could not be connected to Nosto. Please contact Nosto support.'),
					'store' => (int)Mage::app()->getStore()->getId(),
				);
			}
			$this->_redirect('adminhtml/nosto/redirectProxy', $params);
		} elseif (($error = $this->getRequest()->getParam('error')) !== null) {
			$parts = array($error);
			if (($reason = $this->getRequest()->getParam('error_reason')) !== null) {
				$parts[] = $reason;
			}
			if (($desc = $this->getRequest()->getParam('error_description')) !== null) {
				$parts[] = $desc;
			}
			Mage::log("\n" . implode(' - ', $parts), Zend_Log::ERR, 'nostotagging.log');
			$this->_redirect('adminhtml/nosto/redirectProxy', array(
				'error' => $this->__('Account could not be connected to Nosto. You rejected the connection request.'),
				'store' => (int)Mage::app()->getStore()->getId(),
			));
		} else {
			$this->norouteAction();
		}
	}
}
