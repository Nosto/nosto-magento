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
 * @copyright Copyright (c) 2013-2020 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Nosto_Tagging_Helper_Log as NostoLog;

/**
 * Handles sending cart updates to Nosto via the API.
 *
 */
class Nosto_Tagging_Model_Service_Cart
{
    /**
     * Sends a cart update to Nosto
     *
     * @param Nosto_Object_Event_Cart_Update $cartUpdate
     * @param Nosto_Object_Signup_Account $account
     * @return bool
     */
    public function update(
        Nosto_Object_Event_Cart_Update $cartUpdate,
        Nosto_Object_Signup_Account $account
    ) {
        if (!$account || !$account->isConnectedToNosto()) {
            return false;
        }

        /* @var $helper Nosto_Tagging_Helper_Data */
        $helper = Mage::helper('nosto_tagging');
        $nostoCustomerId = $helper->getCookieId();
        if (!$nostoCustomerId) {
            NostoLog::error('Cannot find customer id from cookie');

            return false;
        }

        $service = new Nosto_Operation_CartOperation($account);

        try {
            return $service->updateCart($cartUpdate, $nostoCustomerId, $account->getName());
        } catch (Nosto_Request_Http_Exception_AbstractHttpException $e) {
            NostoLog::exception($e);
        }

        return false;
    }
}
