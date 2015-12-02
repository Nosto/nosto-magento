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
 * Value Object representing a line item.
 * This is used in the cart & order meta models.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
abstract class Nosto_Tagging_Model_Meta_LineItem extends Mage_Core_Model_Abstract
{
    /**
     * @var string|int the item product ID.
     */
    protected $_productId;

    /**
     * @var int the amount of items.
     */
    protected $_quantity;

    /**
     * @var string the item name.
     */
    protected $_name;

    /**
     * @var NostoPrice the item unit price.
     */
    protected $_unitPrice;

    /**
     * @var NostoCurrencyCode the item price currency.
     */
    protected $_currency;

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
            Mage::log(
                sprintf(
                    '%s.productId must be a integer value, got %s.',
                    __CLASS__,
                    $args['productId']
                ),
                Zend_Log::WARN,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );
        }
        if (!isset($args['quantity']) || !is_int($args['quantity']) || !($args['quantity'] > 0)) {
            Mage::log(
                sprintf(
                    '%s.quantity must be a integer value, got %s.',
                    __CLASS__,
                    $args['quantity']
                ),
                Zend_Log::WARN,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );
        }
        if (!isset($args['name']) || !is_string($args['name']) || empty($args['name'])) {
            Mage::log(
                sprintf(
                    '%s.name must be a non-empty string value, got %s.',
                    __CLASS__,
                    $args['name']
                ),
                Zend_Log::WARN,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );
        }

        if (!($args['unitPrice'] instanceof NostoPrice)) {
            Mage::log(
                sprintf(
                    '%s.unitPrice must be an instance of NostoPrice, got %s',
                    __CLASS__,
                    get_class($args['unitPrice'])
                ),
                Zend_Log::WARN,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );
        }
        if (!($args['currency'] instanceof NostoCurrencyCode)) {
            Mage::log(
                sprintf(
                    '%s.currencyCode must be an instance of NostoCurrencyCode, got %s.',
                    __CLASS__,
                    get_class($args['currencyCode'])
                ),
                Zend_Log::WARN,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );

        }

        $this->_productId = $args['productId'];
        $this->_quantity = $args['quantity'];
        $this->_name = $args['name'];
        $this->_unitPrice = $args['unitPrice'];
        $this->_currency = $args['currency'];
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_lineitem');
    }

    /**
     * The unique identifier for the item.
     *
     * @return string|int
     */
    public function getProductId()
    {
        return $this->_productId;
    }

    /**
     * The quantity of the item.
     *
     * @return int the quantity.
     */
    public function getQuantity()
    {
        return $this->_quantity;
    }

    /**
     * The name of the item.
     *
     * @return string the name.
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * The unit price of the item.
     *
     * @return NostoPrice the unit price.
     */
    public function getUnitPrice()
    {
        return $this->_unitPrice;
    }

    /**
     * The 3-letter ISO code (ISO 4217) for the item currency.
     *
     * @return NostoCurrencyCode the currency ISO code.
     */
    public function getCurrency()
    {
        return $this->_currency;
    }
}
