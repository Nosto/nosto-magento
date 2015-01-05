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
 * @copyright   Copyright (c) 2015 Nosto Solutions Ltd (http://www.nosto.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Meta data class which holds information about the buyer of an order.
 * This is used during the order confirmation API request.
 *
 * @category    Nosto
 * @package     Nosto_Tagging
 * @author      Nosto Solutions Ltd
 */
class Nosto_Tagging_Model_Meta_Order_Buyer extends Mage_Core_Model_Abstract implements NostoOrderBuyerInterface
{
	/**
	 * @var string the first name of the user who placed the order.
	 */
	protected $firstName;

	/**
	 * @var string the last name of the user who placed the order.
	 */
	protected $lastName;

	/**
	 * @var string the email address of the user who placed the order.
	 */
	protected $email;

	/**
	 * @inheritdoc
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * @inheritdoc
	 */
	public function getLastName()
	{
		return $this->lastName;
	}

	/**
	 * @inheritdoc
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Loads the buyer info from a Magento order model.
	 *
	 * @param Mage_Sales_Model_Order $order the order model to get the data from.
	 */
	public function loadData(Mage_Sales_Model_Order $order)
	{
		$this->firstName = $order->getCustomerFirstname();
		$this->lastName = $order->getCustomerLastname();
		$this->email = $order->getCustomerEmail();
	}
}
