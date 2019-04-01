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
 * Extension system setting source model for choosing the multi-currency method
 * to use.
 *
 * Options are "Exchange Rate" and "Product Tagging". The former makes use of the built
 * in currency exchange rates and is the preferred method. The latter is the old
 * way of tagging all price variations on the product pages.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Adminhtml_System_Config_Currency_Method extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $store = null;
        $disabled = false;
        $code = null;
        try {
            $code = $this->getRequest()->getParam('store');
        } catch (\Exception $e) {
            NostoLog::exception($e);
        }
        if ($code) {
            $store = Mage::getModel('core/store')->load($code);
        }
        if ($store instanceof Mage_Core_Model_Store) {
            /* @var Nosto_Tagging_Helper_Account $accountHelper */
            $accountHelper = Mage::helper('nosto_tagging/account');
            /* @var Nosto_Object_Signup_Account $nostoAccount */
            $nostoAccount = $accountHelper->find($store);
            if ($nostoAccount instanceof Nosto_Types_Signup_AccountInterface) {
                foreach (Nosto_Request_Api_Token::getApiTokenNames() as $token) {
                    if (!$nostoAccount->getApiToken($token)) {
                        $disabled = true;
                        break;
                    }
                }
            }
        }

        if ($disabled === true) {
            /** @noinspection PhpUndefinedMethodInspection */
            $element->setDisabled('disabled');
            $metaOauth = new Nosto_Tagging_Model_Meta_Oauth();
            /** @noinspection PhpUndefinedVariableInspection */
            $metaOauth->loadData($store);

            $comment = sprintf(
                'Your Nosto account is missing required tokens' .
                ' for updating settings to Nosto. Please click <!--suppress HtmlUnknownTarget -->
<a href="%s">' .
                ' here to re-connect</a> your account.',
                Nosto_Helper_OAuthHelper::getAuthorizationUrl($metaOauth)
            );
            $element->setData(
                'comment', $comment
            );
        }
        return parent::_getElementHtml($element);
    }
}
