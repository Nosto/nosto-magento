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

class Nosto_Tagging_Helper_Account extends Mage_Core_Helper_Abstract
{
	/**
	 * @param NostoAccount $account
	 * @return bool
	 */
	public function save(NostoAccount $account)
	{
		// todo: implement
		return false;
	}

	/**
	 * @param NostoAccount $account
	 * @return bool
	 */
	public function remove(NostoAccount $account)
	{
		// todo: implement
		return false;
	}

	/**
	 * @param Mage_Core_Model_Store $store
	 * @return NostoAccount|null
	 */
	public function find(Mage_Core_Model_Store $store)
	{
		// todo: implement
		return null;
	}

	/**
	 * @return Nosto_Tagging_Model_Meta_Account
	 */
	public function getMetaData()
	{
		return new Nosto_Tagging_Model_Meta_Account();
	}
}
