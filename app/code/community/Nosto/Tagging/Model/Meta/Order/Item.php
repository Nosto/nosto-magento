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
 * Meta data class which holds information about an item included in an order.
 * This is used during the order confirmation API request and the order history export.
 *
 * @category    Nosto
 * @package     Nosto_Tagging
 * @author      Nosto Solutions Ltd
 */
class Nosto_Tagging_Model_Meta_Order_Item extends Mage_Core_Model_Abstract implements NostoOrderPurchasedItemInterface
{
	/**
	 * @var string|int the unique identifier of the purchased item.
	 * If this item is for discounts or shipping cost, the id can be 0.
	 */
	protected $productId;

	/**
	 * @var int the quantity of the item included in the order.
	 */
	protected $quantity;

	/**
	 * @var string the name of the item included in the order.
	 */
	protected $name;

	/**
	 * @var float The unit price of the item included in the order.
	 */
	protected $unitPrice;

	/**
	 * @var string the 3-letter ISO code (ISO 4217) for the currency the item was purchased in.
	 */
	protected $currencyCode;

	/**
	 * @inheritdoc
	 */
	protected function _construct()
	{
		$this->_init('nosto_tagging/meta_order_item');
	}

	/**
	 * @inheritdoc
	 */
	public function getProductId()
	{
		return $this->productId;
	}

	/**
	 * Sets the unique identifier for the item.
	 *
	 * @param string|int $id the product id.
	 */
	public function setProductId($id)
	{
		$this->productId = $id;
	}

	/**
	 * @inheritdoc
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 * Sets the item quantity.
	 *
	 * @param int $qty the quantity.
	 */
	public function setQuantity($qty)
	{
		$this->quantity = $qty;
	}

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Sets the item name.
	 *
	 * @param string $name the item name.
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @inheritdoc
	 */
	public function getUnitPrice()
	{
		return $this->unitPrice;
	}

	/**
	 * Sets the item unit price.
	 *
	 * @param float $price the item unit price.
	 */
	public function setUnitPrice($price)
	{
		$this->unitPrice = $price;
	}

	/**
	 * @inheritdoc
	 */
	public function getCurrencyCode()
	{
		return $this->currencyCode;
	}

	/**
	 * Sets the items currency code (ISO 4217).
	 *
	 * @param string $code the currency ISO code.
	 */
	public function setCurrencyCode($code)
	{
		$this->currencyCode = $code;
	}

	/**
	 * Loads the item info from the Magento order item model.
	 *
	 * @param Mage_Sales_Model_Order_Item $item the item model.
	 */
	public function loadData(Mage_Sales_Model_Order_Item $item)
	{
		switch ($item->getProductType()) {
			case Mage_Catalog_Model_Product_Type::TYPE_GROUPED:
				$info = $item->getProductOptionByCode('info_buyRequest');
				if ($info !== null && isset($info['super_product_config']['product_id'])) {
					$this->productId = (int)$info['super_product_config']['product_id'];
				} else {
					$this->productId = (int)$item->getProductId();
				}
				break;
			default:
				$this->productId = (int)$item->getProductId();
				break;
		}
		$this->quantity = (int)$item->getQtyOrdered();
		$this->name = $item->getName();
		$this->unitPrice = $item->getPriceInclTax();
		$this->currencyCode = $item->getOrder()->getOrderCurrencyCode();
	}
}
