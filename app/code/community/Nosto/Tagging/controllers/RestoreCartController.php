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

require_once __DIR__ . '/../bootstrap.php'; // @codingStandardsIgnoreLine

/**
 * Restores an abandoned cart
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_RestoreCartController extends Mage_Core_Controller_Front_Action
{
    const hashParam = 'h';

    /**
     * Restores a cart based on hash
     */
    public function indexAction()
    {
        $store = Mage::app()->getStore();
        /* @var Nosto_Tagging_Helper_Url $urlHelper */
        $urlHelper = Mage::helper('nosto_tagging/url');
        $frontPageUrl = $urlHelper->getFrontPageUrl($store);
        $redirectUrl = $frontPageUrl;
        if (Mage::helper('nosto_tagging')->isModuleEnabled()) {
            /* @var Mage_Checkout_Model_Session $checkoutSession */
            $checkoutSession = Mage::getSingleton('checkout/session');
            if (!$checkoutSession->getQuoteId()) {
                $restoreCartHash = $this->getRequest()->getParam(self::hashParam);
                if (!$restoreCartHash) {
                    Nosto_Tagging_Helper_Log::exception(
                        new Nosto_Exception_NostoException(
                            'No hash provided for restore cart'
                        )
                    );
                } else {
                    /* @var Nosto_Tagging_Model_Customer $nostoCustomer */
                    $nostoCustomer = Mage::getModel('nosto_tagging/customer')
                        ->getCollection()
                        ->addFieldToFilter('restore_cart_hash', $restoreCartHash)
                        ->setPageSize(1)
                        ->setCurPage(1)
                        ->getFirstItem(); // @codingStandardsIgnoreLine
                    if ($nostoCustomer->getQuoteId()) {
                        $quote = Mage::getModel('sales/quote')->load(
                            $nostoCustomer->getQuoteId()
                        );
                        // ToDo - do we want to reactivate cart if it has been bought?
                        if (!$quote->getIsActive()) {
                            Mage::getSingleton(
                                'core/session'
                            )->addWarning('It seems that you have already bought items in this cart');
                            $quote->setIsActive(1)->save();
                        }
                        $checkoutSession->setQuoteId($quote->getId());
                        $redirectUrl = $urlHelper->getUrlCart($store);
                    } else {
                        /* @var Mage_Checkout_Model_Session $session */
                        Mage::getSingleton(
                            'core/session'
                        )->addError('We could not find your cart');
                    }
                }
            } else {
                $redirectUrl = $urlHelper->getUrlCart($store);
            }
        }
        $this->_redirectUrl($redirectUrl);
    }
}
