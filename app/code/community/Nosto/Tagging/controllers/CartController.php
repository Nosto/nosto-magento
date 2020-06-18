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

/* @var Nosto_Tagging_Helper_Bootstrap $nostoBootstrapHelper */
$nostoBootstrapHelper = Mage::helper('nosto_tagging/bootstrap');
$nostoBootstrapHelper->init();

use Nosto_NostoException as NostoException;
use Nosto_Tagging_Helper_Log as NostoLog;

/**
 * Restores an abandoned cart
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_CartController extends Mage_Core_Controller_Front_Action
{
    /**
     * The name of the hash parameter to look from URL
     */
    const HASH_PARAM = 'h';

    /**
     * Restores a cart based on hash. On succesful restoration redirects user
     * to the cart page
     * @throws Zend_Uri_Exception
     */
    public function indexAction()
    {
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        /** @var Nosto_Tagging_Helper_Module $moduleHelper */
        $moduleHelper = Mage::helper('nosto_tagging/module');
        $store = $helper->getStore();
        /* @var Nosto_Tagging_Helper_Url $nostoUrlHelper */
        $nostoUrlHelper = Mage::helper('nosto_tagging/url');
        $currentUrl = $nostoUrlHelper->getCurrentUrl();
        $urlParameters = Zend_Uri_Http::fromString($currentUrl)->getQueryAsArray();
        $frontPageUrl = $nostoUrlHelper->getFrontPageUrl($store);
        $redirectUrl = $frontPageUrl;
        if ($moduleHelper->isModuleEnabled()) {
            /* @var Mage_Checkout_Model_Session $checkoutSession */
            $checkoutSession = Mage::getSingleton('checkout/session');
            /* @var Mage_Core_Model_Session $coreSession */
            $coreSession = Mage::getSingleton('core/session');
            if (!$checkoutSession->getQuoteId()) {
                $restoreCartHash = $this->getRequest()->getParam(self::HASH_PARAM);
                if (!$restoreCartHash) {
                    NostoLog::exception(
                        new NostoException(
                            'No hash provided for restore cart'
                        )
                    );
                } else {
                    try {
                        $quote = $this->resolveQuote($restoreCartHash);
                        $checkoutSession->setQuoteId($quote->getId());
                        $redirectUrl = $nostoUrlHelper->getRestoreCartRedirectUrl(
                            $store,
                            $urlParameters
                        );
                    } catch (\Exception $e) {
                        NostoLog::exception($e);
                        $coreSession->addError(
                            $this->__('Sorry, we could not find your cart')
                        );
                    }
                }
            } else {
                $redirectUrl = $nostoUrlHelper->getRestoreCartRedirectUrl(
                    $store,
                    $urlParameters
                );
            }
        }

        $this->_redirectUrl($redirectUrl);
    }

    /**
     * Resolves the cart (quote) by the given hash
     *
     * @param $restoreCartHash
     * @return Mage_Sales_Model_Quote|null
     * @throws Nosto_NostoException
     */
    protected function resolveQuote($restoreCartHash)
    {
        /* @var Nosto_Tagging_Model_Customer $nostoCustomer */
        $nostoCustomer = Mage::getModel('nosto_tagging/customer')
            ->getCollection()
            ->addFieldToFilter('restore_cart_hash', $restoreCartHash)
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem(); // @codingStandardsIgnoreLine
        $quoteId = $nostoCustomer->getQuoteId();

        if (!$nostoCustomer->hasData() || !$quoteId) {
            throw new Nosto_NostoException(
                sprintf(
                    'No nosto customer found for hash %s',
                    $restoreCartHash
                )
            );
        }

        /* @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getModel('sales/quote')->load(
            $quoteId
        );
        if (!$quote->hasData()) {
            throw new Nosto_NostoException(
                sprintf(
                    'No quote found for id %d',
                    $quoteId
                )
            );
        }

        // Note - we reactivate the cart if it's not active.
        // This would happen for example when the cart was bought.
        if (!$quote->getIsActive()) {
            $quote->setIsActive(1)->save();
        }

        return $quote;
    }
}
