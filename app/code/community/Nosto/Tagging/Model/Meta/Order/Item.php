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
     * Constructor.
     *
     * Sets up this Value Object.
     *
     * @param array $args the object data.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $args)
    {
        if (!isset($args['productId']) || !is_int($args['productId'])) {
            throw new InvalidArgumentException(sprintf('%s.productId must be a integer value.', __CLASS__));
        }
        if (!isset($args['quantity']) || !is_int($args['quantity']) || !($args['quantity'] > 0)) {
            throw new InvalidArgumentException(sprintf('%s.quantity must be a integer value above zero.', __CLASS__));
        }
        if (!isset($args['name']) || !is_string($args['name']) || empty($args['name'])) {
            throw new InvalidArgumentException(sprintf('%s.name must be a non-empty string value.', __CLASS__));
        }
        if (!isset($args['unitPrice']) || !is_numeric($args['unitPrice'])) {
            throw new InvalidArgumentException(sprintf('%s.unitPrice must be a numeric value.', __CLASS__));
        }
        if (!isset($args['currencyCode']) || !is_string($args['currencyCode']) || empty($args['currencyCode'])) {
            throw new InvalidArgumentException(sprintf('%s.currencyCode must be a non-empty string value.', __CLASS__));
        }

        $this->_productId = $args['productId'];
        $this->_quantity = $args['quantity'];
        $this->_name = $args['name'];
        $this->_unitPrice = $args['unitPrice'];
        $this->_currencyCode = $args['currencyCode'];
    }

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
     * The quantity of the item included in the order.
     *
     * @return int the quantity.
     */
    public function getQuantity()
    {
        return $this->_quantity;
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
     * The unit price of the item included in the order.
     *
     * @return float the unit price.
     */
    public function getUnitPrice()
    {
        return $this->_unitPrice;
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
}
