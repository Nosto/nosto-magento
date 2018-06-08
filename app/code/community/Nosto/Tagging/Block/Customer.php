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

use Nosto_Tagging_Helper_Log as NostoLog;

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
     * @throws Varien_Exception
     */
    protected function _toHtml()
    {
        /** @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('nosto_tagging/account');
        /** @noinspection PhpUndefinedMethodInspection */
        if (!$helper->existsAndIsConnected()
            || $this->getNostoCustomer() === null
            ||!Mage::helper('nosto_tagging/module')->isModuleEnabled()
            || !$this->helper('customer')->isLoggedIn()
        ) {
            return '';
        }
        return $this->getNostoCustomer()->toHtml();
    }

    /*
     * Returns the checksum for Nosto visit id
     */
    /**
     * @return null|string
     */
    public function getVisitorChecksum()
    {
        /* @var $helper Nosto_Tagging_Helper_Data */
        $helper = Mage::helper('nosto_tagging');

        return $helper->getVisitorChecksum();
    }

    /**
     * Populates Nosto customer object
     *
     * @return Nosto_Object_Customer|null
     */
    public function getNostoCustomer()
    {
        $helper = Mage::helper('nosto_tagging');
        $store = Mage::app()->getStore();
        /* @var Nosto_Tagging_Helper_Data $helper */
        if (!$helper->getSendCustomerData($store)) {
            return null;
        }
        $customer = $this->getCustomer();
        $email = $customer->getEmail();
        /** @var Nosto_Tagging_Helper_Email $emailHelper */
        $emailHelper = Mage::helper('nosto_tagging/email');
        $nostoCustomer = new Nosto_Object_Customer();
        $nostoCustomer->setFirstName($customer->getFirstname());
        $nostoCustomer->setLastName($customer->getLastname());
        $nostoCustomer->setCustomerReference($this->getCustomerReference());
        $nostoCustomer->setEmail($email);
        $nostoCustomer->setMarketingPermission($emailHelper->isOptedIn($email));

        return $nostoCustomer;
    }

    /**
     * Returns the customer reference of the customer
     */
    protected function getCustomerReference()
    {
        $ref = '';

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
                return $ref;
            }
        } catch (\Exception $e) {
            NostoLog::exception($e);
        }

        return $ref;
    }
}
