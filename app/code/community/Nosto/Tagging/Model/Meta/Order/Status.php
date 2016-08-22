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
 * @copyright Copyright (c) 2013-2016 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Meta data class which holds information about the order status.
 * This is used during the order confirmation API request and the order history
 * export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Order_Status extends Mage_Core_Model_Abstract implements NostoOrderStatusInterface
{
    /**
     * @var string the order status code.
     */
    protected $_code;

    /**
     * @var string the order status label.
     */
    protected $_label;

    /**
     * @var string the order status created at date.
     */
    protected $_createdAt;

    /**
     * Constructor.
     *
     * Sets up this Value Object.
     *
     * @param array $args the object data.
     *
     */
    public function __construct(array $args)
    {
        parent::__construct();
        if (!isset($args['code']) || !is_string($args['code'])) {
            Mage::log(
                sprintf(
                    '%s.code must be a non-empty string value',
                    __CLASS__
                ),
                Zend_Log::WARN,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );
            $args['code'] = '';
        }

        if (!isset($args['label'])) {
            Mage::log(
                sprintf(
                    '%s.label is not set',
                    __CLASS__
                ),
                Zend_Log::WARN,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );
            $args['label'] = $args['code'];
        }

        if (isset($args['createdAt'])) {
            if (empty($args['createdAt'])) {
                Mage::log(
                    sprintf(
                        '%s.createdAt is not set',
                        __CLASS__
                    ),
                    Zend_Log::WARN,
                    Nosto_Tagging_Model_Base::LOG_FILE_NAME
                );
                $args['createdAt'] = '';
            }
        } else {
            $args['createdAt'] = '';
        }

        $this->_code = $args['code'];
        $this->_label = $args['label'];
        $this->_createdAt = $args['createdAt'];
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_order_status');
    }

    /**
     * Returns the order status code.
     *
     * @return string the code.
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Returns the order status label.
     *
     * @return string the label.
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * Returns the status created date.
     *
     * @return string the created date or null if not set.
     */
    public function getCreatedAt()
    {
        return $this->_createdAt;
    }
}
