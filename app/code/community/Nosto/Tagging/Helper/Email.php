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

/**
 * Helper class for email related operations
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Email extends Mage_Core_Helper_Abstract
{
    /**
     * Fetches marketing email subscription for given email address
     *
     * @param $email
     * @return Mage_Newsletter_Model_Subscriber|null
     */
    public function getNewsletterOptInForEmail($email)
    {
        /** @var Mage_Newsletter_Model_Subscriber $subscriberModel */
        $subscriberModel = Mage::getModel('newsletter/subscriber');
        return $subscriberModel->loadByEmail($email);
    }

    /**
     * Checks if marketing emails are allowed for given email address
     *
     * @param $email
     * @return bool
     */
    public function isOptedIn($email)
    {
        try {
            $subscription = $this->getNewsletterOptInForEmail($email);
            if ($subscription->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
                return true;
            }
        } catch (Exception $e) {
            Nosto_Tagging_Helper_Log::exception($e);
        }

        return false;
    }
}
