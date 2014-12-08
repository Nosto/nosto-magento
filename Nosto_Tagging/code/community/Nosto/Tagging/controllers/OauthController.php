<?php

require_once(Mage::getBaseDir('lib').'/nosto/sdk/src/config.inc.php');

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
		// todo: language/store??

		if (($code = $this->getRequest()->getParam('code')) !== null) {
			try {
				$account = NostoAccount::syncFromNosto(Mage::helper('nosto_tagging/oauth')->getMetaData(), $code);
				if (Mage::helper('nosto_tagging/account')->save($account)) {
					// todo: success flash message
				} else {
					throw new NostoException('Failed to connect account');
				}
			} catch (NostoException $e) {
				Mage::log("\n" . $e->__toString(), Zend_Log::ERR, 'nostotagging.log');
				// todo: exception flash message
			}
			$this->_redirect('adminhtml/nosto/index');
		} elseif (($error = $this->getRequest()->getParam('error')) !== null) {
			$messageParts = array($error);
			if (($errorReason = $this->getRequest()->getParam('error_reason')) !== null) {
				$messageParts[] = $errorReason;
			}
			if (($errorDesc = $this->getRequest()->getParam('error_description')) !== null) {
				$messageParts[] = $errorDesc;
			}
			Mage::log("\n" . implode(' - ', $messageParts), Zend_Log::ERR, 'nostotagging.log');
			// todo: error flash message
			$this->_redirect('adminhtml/nosto/index');
		} else {
			$this->norouteAction();
		}
	}
}
