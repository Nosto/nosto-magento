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
 * @copyright Copyright (c) 2013-2017 Nosto Solutions Ltd (http://www.nosto.com)
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
class Nosto_Tagging_Model_Meta_Order_Buyer extends NostoOrderBuyer
{
    /**
     * Constructor.
     *
     * Sets up this Value Object.
     *
     * @param array $args the object data.
     */
    public function __construct(array $args)
    {
        parent::__construct();
        if (!isset($args['firstName']) || !is_string($args['firstName'])) {
            Mage::log(
                sprintf(
                    '%s.firstName must be a string value',
                    __CLASS__
                ),
                Zend_Log::WARN,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );
            $args['firstName'] = '';
        }
        if (!isset($args['lastName']) || !is_string($args['lastName'])) {
            Mage::log(
                sprintf(
                    '%s.lastName must be a string value',
                    __CLASS__
                ),
                Zend_Log::WARN,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );
            $args['lastName'] = '';
        }
        if (!isset($args['email']) || !is_string($args['email'])) {
            Mage::log(
                sprintf(
                    '%s.email must be a string value',
                    __CLASS__
                ),
                Zend_Log::WARN,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );
            $args['email'] = '';
        }

        $this->setFirstName($args['firstName']);
        $this->setLastName($args['lastName']);
        $this->setEmail($args['email']);
    }
}
