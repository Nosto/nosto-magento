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
 * @copyright   Copyright (c) 2013 Nosto Solutions Ltd (http://www.nosto.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Config data model for the server address setting.
 *
 * @category    Nosto
 * @package     Nosto_Tagging
 * @author      Nosto Solutions Ltd
 */
class Nosto_Tagging_Model_Config_Server extends Mage_Core_Model_Config_Data
{
	/**
	 * Save object data.
	 * Validates that the server is set and does not include the protocol.
	 *
	 * @return Nosto_Tagging_Model_Config_Server
	 */
	public function save()
	{
		$server = $this->getValue();

		if (empty($server)) {
			Mage::throwException(Mage::helper('nosto_tagging')->__('Server address is required.'));
		}

		$pattern = '@^https?://@i';
		if (preg_match($pattern, $server)) {
			Mage::throwException(
				Mage::helper('nosto_tagging')->__(
					'The server address should not include the protocol (http:// or https://).'
				)
			);
		}

		return parent::save();
	}
}
