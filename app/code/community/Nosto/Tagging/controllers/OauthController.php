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

require_once Mage::getBaseDir('lib') . '/nosto/php-sdk/src/config.inc.php';

/**
 * OAuth2 controller.
 * Handles the redirect from Nosto OAuth2 authorization server.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_tagging_OauthController extends Mage_Core_Controller_Front_Action
{
    /**
     * Handles the redirect from Nosto oauth2 authorization server when an
     * existing account is connected to a store.
     * This is handled in the front end as the oauth2 server validates the
     * "return_url" sent in the first step of the authorization cycle, and
     * requires it to be from the same domain that the account is configured
     * for and only redirects to that domain.
     */
    public function indexAction()
    {
        // If the "Add Store Code to Urls" setting is set to "No" under
        // System -> Configuration -> Web -> Url Options, then Magento won't
        // set the store context based on the "___store" GET parameter if the
        // store does NOT belong to the default website. When this setting is
        // "Yes", then the store code will be a part of the url path and then
        // the correct context is set by Magento, regardless of the website the
        // store belongs to.
        // If the "___store" parameter is present in the url in the current
        // store context is not that store, then switch the store context.
        if (($storeCode = $this->getRequest()->getParam('___store')) !== null) {
            $store = Mage::app()->getStore($storeCode);
            if ($store && $store->getId() !== Mage::app()->getStore()->getId()) {
                Mage::app()->setCurrentStore($store->getCode());
            }
        }

        $request = $this->getRequest();
        $store = Mage::app()->getStore();
        if (($code = $request->getParam('code')) !== null) {
            try {
                $account = NostoAccount::syncFromNosto(
                    Mage::helper('nosto_tagging/oauth')->getMetaData($store),
                    $code
                );
                if (Mage::helper('nosto_tagging/account')->save($account, $store)) {
                    $params = array(
                        'message_type' => NostoMessage::TYPE_SUCCESS,
                        'message_code' => NostoMessage::CODE_ACCOUNT_CONNECT,
                        'store' => (int)$store->getId(),
                        '_store' => Mage_Core_Model_App::ADMIN_STORE_ID,
                    );
                } else {
                    throw new NostoException('Failed to connect account');
                }
            } catch (NostoException $e) {
                Mage::log(
                    "\n" . $e->__toString(), Zend_Log::ERR, 'nostotagging.log'
                );
                $params = array(
                    'message_type' => NostoMessage::TYPE_ERROR,
                    'message_code' => NostoMessage::CODE_ACCOUNT_CONNECT,
                    'store' => (int)$store->getId(),
                    '_store' => Mage_Core_Model_App::ADMIN_STORE_ID,
                );
            }
            $this->_redirect('adminhtml/nosto/redirectProxy', $params);
        } elseif (($error = $request->getParam('error')) !== null) {
            $logMsg = $error;
            if (($reason = $request->getParam('error_reason')) !== null) {
                $logMsg .= ' - ' . $reason;
            }
            if (($desc = $request->getParam('error_description')) !== null) {
                $logMsg .= ' - ' . $desc;
            }
            Mage::log("\n" . $logMsg, Zend_Log::ERR, 'nostotagging.log');
            $this->_redirect(
                'adminhtml/nosto/redirectProxy', array(
                    'message_type' => NostoMessage::TYPE_ERROR,
                    'message_code' => NostoMessage::CODE_ACCOUNT_CONNECT,
                    'message_text' => $desc,
                    'store' => (int)$store->getId(),
                    '_store' => Mage_Core_Model_App::ADMIN_STORE_ID,
                )
            );
        } else {
            $this->norouteAction();
        }
    }
}
