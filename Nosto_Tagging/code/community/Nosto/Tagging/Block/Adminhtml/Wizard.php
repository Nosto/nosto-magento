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

class Nosto_tagging_Block_Adminhtml_Wizard extends Mage_Adminhtml_Block_Template
{
	/**
	 * @var NostoAccount the Nosto account for current store view scope.
	 */
	private $_account;

	/**
	 * @var string the iframe url if SSO to Nosto can be made.
	 */
	private $_iframeUrl;

	/**
	 * Gets the iframe url for the account settings page from Nosto.
	 * This url is only returned if the current admin user can be logged in with SSO to Nosto.
	 *
	 * @return string the iframe url or empty string if it cannot be created.
	 */
	public function getIframeUrl()
	{
		if ($this->_iframeUrl !== null) {
			return $this->_iframeUrl;
		}
		$account = $this->getAccount();
		if ($account) {
			try {
				$meta = new Nosto_Tagging_Model_Meta_Account_Iframe();
				return $this->_iframeUrl = $account->getIframeUrl($meta);
			} catch (NostoException $e) {
				Mage::log("\n" . $e->__toString(), Zend_Log::ERR, 'nostotagging.log');
			}
		}
		return $this->_iframeUrl = '';
	}

	/**
	 * Gets the Nosto account for the current active store view scope.
	 *
	 * @return NostoAccount|null the account or null if it cannot be found.
	 */
	public function getAccount()
	{
		if ($this->_account !== null) {
			return $this->_account;
		}
		return $this->_account = Mage::helper('nosto_tagging/account')->find();
	}
}
