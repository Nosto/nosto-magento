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
 * Meta data class which holds information about an order.
 * This is used during the order confirmation API request and the order history export.
 *
 * @category    Nosto
 * @package     Nosto_Tagging
 * @author      Nosto Solutions Ltd
 */
class Nosto_Tagging_Model_Meta_Order extends Mage_Core_Model_Abstract implements NostoOrderInterface
{
	/**
	 * @var string|int the unique order number identifying the order.
	 */
	protected $orderNumber;

	/**
	 * @var string the date when the order was placed.
	 */
	protected $createdDate;

	/**
	 * @var string the payment provider used for placing the order, formatted according to "[provider name] [provider version]".
	 */
	protected $paymentProvider;

	/**
	 * @var Nosto_Tagging_Model_Meta_Order_Buyer The buyer info of the user who placed the order.
	 */
	protected $buyer;

	/**
	 * @var Nosto_Tagging_Model_Meta_Order_Item[] the purchased items which were included in the order.
	 */
	protected $items = array();

	/**
	 * @inheritdoc
	 */
	protected function _construct()
	{
		$this->_init('nosto_tagging/meta_order');
	}

	/**
	 * @inheritdoc
	 */
	public function getOrderNumber()
	{
		return $this->orderNumber;
	}

	/**
	 * @inheritdoc
	 */
	public function getCreatedDate()
	{
		return $this->createdDate;
	}

	/**
	 * @inheritdoc
	 */
	public function getPaymentProvider()
	{
		return $this->paymentProvider;
	}

	/**
	 * @inheritdoc
	 */
	public function getBuyerInfo()
	{
		return $this->buyer;
	}

	/**
	 * @inheritdoc
	 */
	public function getPurchasedItems()
	{
		return $this->items;
	}

	/**
	 * Loads the order info from a Magento order model.
	 *
	 * @param Mage_Sales_Model_Order $order the order model.
	 */
	public function loadData(Mage_Sales_Model_Order $order)
	{
		$this->orderNumber = $order->getId();
		$this->createdDate = $order->getCreatedAt();
		$method = $order->getPayment()->getMethodInstance();
		$this->paymentProvider = $method->getCode();
		$this->buyer = new Nosto_Tagging_Model_Meta_Order_Buyer();
		$this->buyer->loadData($order);
		/** @var $item Mage_Sales_Model_Order_Item */
		foreach ($order->getAllVisibleItems() as $item) {
			/** @var Mage_Catalog_Model_Product $product */
			$product = Mage::getModel('catalog/product')->load($item->getProductId());
			if ($product->getTypeId() === Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
				if ((int)$product->getPriceType() === Mage_Bundle_Model_Product_Price::PRICE_TYPE_FIXED) {
					continue;
				}
				foreach ($item->getChildrenItems() as $child) {
					$orderItem = new Nosto_Tagging_Model_Meta_Order_Item();
					$orderItem->loadData($child);
					$this->items[] = $orderItem;
				}
			} else {
				$orderItem = new Nosto_Tagging_Model_Meta_Order_Item();
				$orderItem->loadData($item);
				$this->items[] = $orderItem;
			}
		}
		if ($discount = $order->getDiscountAmount()) {
			$orderItem = new Nosto_Tagging_Model_Meta_Order_Item();
			$orderItem->setProductId(-1);
			$orderItem->setQuantity(1);
			$orderItem->setName('Discount');
			$orderItem->setUnitPrice($discount);
			$orderItem->setCurrencyCode($order->getOrderCurrencyCode());
			$this->items[] = $orderItem;
		}
		if ($shippingInclTax = $order->getShippingInclTax()) {
			$orderItem = new Nosto_Tagging_Model_Meta_Order_Item();
			$orderItem->setProductId(-1);
			$orderItem->setQuantity(1);
			$orderItem->setName('Shipping and handling');
			$orderItem->setUnitPrice($shippingInclTax);
			$orderItem->setCurrencyCode($order->getOrderCurrencyCode());
			$this->items[] = $orderItem;
		}
	}
}
