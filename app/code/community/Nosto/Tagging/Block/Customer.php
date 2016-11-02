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
 * Customer info tagging block.
 * Adds meta-data to the HTML document for logged in customer.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Customer extends Mage_Customer_Block_Account_Dashboard
{
    /**
     * Render customer info as hidden meta data if the customer is logged in,
     * the module is enabled for the current store.
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('nosto_tagging/account');
        /** @noinspection PhpUndefinedMethodInspection */
        if (!Mage::helper('nosto_tagging')->isModuleEnabled()
            || !$helper->existsAndIsConnected()
            || !$this->helper('customer')->isLoggedIn()
        ) {
            return '';
        }

        return parent::_toHtml();
    }

    /*
     * Returns the visitor's Nosto Id
     */
    public function getVisitorChecksum()
    {
        /* @var $helper Nosto_Tagging_Helper_Data */
        $helper = Mage::helper('nosto_tagging');

        return $helper->getVisitorChecksum();
    }

    /*
     * Returns the customer reference of the customer
     */
    protected function getCustomerReference()
    {
        try {
            /* @var $customerHelper Nosto_Tagging_Helper_Customer */
            $customerHelper = Mage::helper('nosto_tagging/customer');
            /* @var $customer Mage_Customer_Model_Customer */
            $customer = $this->getCustomer();
            $ref = $customer->getData(
                Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME
            );
            if (empty($ref)) {
                $ref = $customerHelper->generateCustomerReference($customer);
                $customer->setData(
                    Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME,
                    $ref
                );
                $customer->save();
            }
        } catch (\Exception $e) {
            Mage::log(
                sprintf(
                    'Could not get customer reference. Error was: %s',
                    $e->getMessage()
                ),
                Zend_Log::ERR,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );
            $ref = null;
        }

        return $ref;
    }
}
