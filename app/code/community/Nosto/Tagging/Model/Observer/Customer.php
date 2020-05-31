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
 * @copyright Copyright (c) 2013-2019 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Nosto_Tagging_Helper_Log as NostoLog;

/**
 * Event observer model for Customer.
 * Used to interact with Magento events.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Observer_Customer
{
    /**
     * Makes an API call to Nosto when a customer is registered/updated.
     *
     * Event 'customer_save_commit_after'.
     *
     * @param Varien_Event_Observer $observer the event observer.
     * @return Nosto_Tagging_Model_Observer_Customer
     */
    public function customerUpdated(Varien_Event_Observer $observer)
    {
        /** @var Nosto_Tagging_Helper_Module $moduleHelper */
        $moduleHelper = Mage::helper('nosto_tagging/module');
        if ($moduleHelper->isModuleEnabled()) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $mageCustomer = $observer->getEvent()->getCustomer();
                /** @var Nosto_Tagging_Model_Service_Customer $service */
                $service = Mage::getModel('nosto_tagging/service_customer');
                $service->update($mageCustomer);
            } catch (Exception $e) {
                NostoLog::exception($e);
            }
        }

        return $this;
    }
}
