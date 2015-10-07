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
 * Meta data class which holds information about the buyer of an order.
 * This is used during the order confirmation API request and the order history
 * export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Order_Buyer extends Mage_Core_Model_Abstract implements NostoOrderBuyerInterface
{
    /**
     * @var string the first name of the user who placed the order.
     */
    protected $_firstName;

    /**
     * @var string the last name of the user who placed the order.
     */
    protected $_lastName;

    /**
     * @var string the email address of the user who placed the order.
     */
    protected $_email;

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
        if (!isset($args['firstName']) || !is_string($args['firstName']) || empty($args['firstName'])) {
            throw new InvalidArgumentException(sprintf('%s.firstName must be a non-empty string value.', __CLASS__));
        }
        if (!isset($args['lastName']) || !is_string($args['lastName']) || empty($args['lastName'])) {
            throw new InvalidArgumentException(sprintf('%s.lastName must be a non-empty string value.', __CLASS__));
        }
        if (!isset($args['email']) || !is_string($args['email']) || empty($args['email'])) {
            throw new InvalidArgumentException(sprintf('%s.email must be a non-empty string value.', __CLASS__));
        }

        $this->_firstName = $args['firstName'];
        $this->_lastName = $args['lastName'];
        $this->_email = $args['email'];
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_order_buyer');
    }

    /**
     * Gets the first name of the user who placed the order.
     *
     * @return string the first name.
     */
    public function getFirstName()
    {
        return $this->_firstName;
    }

    /**
     * Gets the last name of the user who placed the order.
     *
     * @return string the last name.
     */
    public function getLastName()
    {
        return $this->_lastName;
    }

    /**
     * Gets the email address of the user who placed the order.
     *
     * @return string the email address.
     */
    public function getEmail()
    {
        return $this->_email;
    }
}
