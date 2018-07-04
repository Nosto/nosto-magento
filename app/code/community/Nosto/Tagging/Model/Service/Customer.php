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

use Nosto_Object_Signup_Account as NostoSDKAccount;
use Nosto_Operation_MarketingPermission as NostoSDKMarketingPermission;

/**
 * Handles sending the customer updates to Nosto via the API.
 */
class Nosto_Tagging_Model_Service_Customer
{
    /**
     * Sends an customer update to Nosto
     *
     * @param Mage_Customer_Model_Customer $mageCustomer
     * @return bool
     */
    public function update(Mage_Customer_Model_Customer $mageCustomer)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $email = $mageCustomer->getEmail();
        $account = Nosto_Tagging_Helper_Account::find();
        if (!$account instanceof NostoSDKAccount
            || $email === null
            || !$account->isConnectedToNosto()
        ) {
            return false;
        }
        /** @var Nosto_Tagging_Helper_Email $emailHelper */
        $emailHelper = Mage::helper('nosto_tagging/email');
        $newsletter = $emailHelper->isOptedIn($email);
        $service = new NostoSDKMarketingPermission($account);
        return $service->update($email, $newsletter);
    }
}
