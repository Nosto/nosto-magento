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
 * @category  Nosto
 * @package   Nosto_Tagging
 * @author    Nosto Solutions Ltd <magento@nosto.com>
 * @copyright Copyright (c) 2013-2015 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Meta data class which holds information about an item included in an order.
 * This is used during the order confirmation API request and the order history
 * export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Order_Item extends Mage_Core_Model_Abstract implements NostoOrderPurchasedItemInterface
{
    /**
     * @var string|int the unique identifier of the purchased item.
     * If this item is for discounts or shipping cost, the id can be 0.
     */
    protected $_productId;

    /**
     * @var int the quantity of the item included in the order.
     */
    protected $_quantity;

    /**
     * @var string the name of the item included in the order.
     */
    protected $_name;

    /**
     * @var float The unit price of the item included in the order.
     */
    protected $_unitPrice;

    /**
     * @var string the 3-letter ISO code (ISO 4217) for the item currency.
     */
    protected $_currencyCode;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_order_item');
    }

    /**
     * The unique identifier of the purchased item.
     * If this item is for discounts or shipping cost, the id can be 0.
     *
     * @return string|int
     */
    public function getProductId()
    {
        return $this->_productId;
    }

    /**
     * Sets the unique identifier for the item.
     *
     * @param string|int $id the product id.
     */
    public function setProductId($id)
    {
        $this->_productId = $id;
    }

    /**
     * The quantity of the item included in the order.
     *
     * @return int the quantity.
     */
    public function getQuantity()
    {
        return $this->_quantity;
    }

    /**
     * Sets the item quantity.
     *
     * @param int $qty the quantity.
     */
    public function setQuantity($qty)
    {
        $this->_quantity = $qty;
    }

    /**
     * The name of the item included in the order.
     *
     * @return string the name.
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Sets the item name.
     *
     * @param string $name the item name.
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * The unit price of the item included in the order.
     *
     * @return float the unit price.
     */
    public function getUnitPrice()
    {
        return $this->_unitPrice;
    }

    /**
     * Sets the item unit price.
     *
     * @param float $price the item unit price.
     */
    public function setUnitPrice($price)
    {
        $this->_unitPrice = $price;
    }

    /**
     * The 3-letter ISO code (ISO 4217) for the item currency.
     *
     * @return string the currency ISO code.
     */
    public function getCurrencyCode()
    {
        return $this->_currencyCode;
    }

    /**
     * Sets the items currency code (ISO 4217).
     *
     * @param string $code the currency ISO code.
     */
    public function setCurrencyCode($code)
    {
        $this->_currencyCode = $code;
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
                    $this->_productId = (int)$info['super_product_config']['product_id'];
                } else {
                    $this->_productId = (int)$item->getProductId();
                }
                break;

            default:
                $this->_productId = (int)$item->getProductId();
                break;
        }

        $this->_quantity = (int)$item->getQtyOrdered();
        $this->_name = $item->getName();
        $this->_unitPrice = $item->getPriceInclTax();
        $this->_currencyCode = $item->getOrder()->getOrderCurrencyCode();
    }
}
