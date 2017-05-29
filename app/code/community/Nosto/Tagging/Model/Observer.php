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
use Nosto_Tagging_Helper_Log as NostoLog;

/**
 * Event observer model.
 * Used to interact with Magento events.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Observer
{
    /**
     * Xml layout handle for the default page footer section.
     */
    const XML_LAYOUT_PAGE_DEFAULT_FOOTER_HANDLE = 'nosto_tagging_page_default_footer';

    /**
     * Adds Nosto footer block at the end of the content block.
     *
     * Event 'controller_action_layout_load_before'.
     *
     * @param Varien_Event_Observer $observer the event observer.
     *
     * @return Nosto_Tagging_Model_Observer
     */
    public function addBlockAfterMainContent(Varien_Event_Observer $observer)
    {
        if (Mage::helper('nosto_tagging')->isModuleEnabled()) {
            /** @var $layout Mage_Core_Model_Layout_Update */
            /** @noinspection PhpUndefinedMethodInspection */
            $layout = $observer->getEvent()->getLayout()->getUpdate();
            $layout->addHandle(self::XML_LAYOUT_PAGE_DEFAULT_FOOTER_HANDLE);
        }

        return $this;
    }

    /**
     * Event handler for the "catalog_product_save_after" event.
     * Sends a product update API call to Nosto.
     *
     * @param Varien_Event_Observer $observer the event observer.
     *
     * @return Nosto_Tagging_Model_Observer
     */
    public function sendProductUpdate(Varien_Event_Observer $observer)
    {
        if (Mage::helper('nosto_tagging')->isModuleEnabled()) {
            /** @var Mage_Catalog_Model_Product $product */
            /** @noinspection PhpUndefinedMethodInspection */
            $product = $observer->getEvent()->getProduct();

            try {
                /* @var Nosto_Tagging_Model_Service_Product $service */
                $service = Mage::getModel('nosto_tagging/service_product');
                $service->updateProduct($product);
            } catch (Nosto_NostoException$e) {
                NostoLog::exception($e);
            }
        }

        return $this;
    }

    /**
     * Event handler for the "catalog_product_delete_after" event.
     * Sends a product delete API call to Nosto.
     *
     * @param Varien_Event_Observer $observer the event observer.
     *
     * @return Nosto_Tagging_Model_Observer
     */
    public function sendProductDelete(Varien_Event_Observer $observer)
    {
        if (Mage::helper('nosto_tagging')->isModuleEnabled()) {
            /** @var Mage_Catalog_Model_Product $product */
            /** @noinspection PhpUndefinedMethodInspection */
            $product = $observer->getEvent()->getProduct();
            // Products are always deleted from all store views, regardless of
            // the store view scope switcher on the product edit page.
            /** @var Mage_Core_Model_Store $store */
            foreach (Mage::app()->getStores() as $store) {
                /** @var Nosto_Tagging_Helper_Account $helper */
                $helper = Mage::helper('nosto_tagging/account');
                $account = $helper->find($store);

                if ($account === null || !$account->isConnectedToNosto()) {
                    continue;
                }
                /* @var Mage_Core_Model_App_Emulation $emulation */
                $emulation = Mage::getSingleton('core/app_emulation');
                $env = $emulation->startEnvironmentEmulation($store->getId());
                /** @var Nosto_Tagging_Model_Meta_Product $model */
                $model = Mage::getModel('nosto_tagging/meta_product');
                $model->setProductId($product->getId());
                try {
                    $service = new Nosto_Operation_UpsertProduct($account);
                    $service->addProduct($model);
                    $service->upsert();
                } catch (Nosto_NostoException $e) {
                    NostoLog::exception($e);
                }
                $emulation->stopEnvironmentEmulation($env);
            }
        }

        return $this;
    }

    /**
     * Sends an order confirmation API request to Nosto if the order is completed.
     *
     * Event 'sales_order_save_commit_after'.
     *
     * @param Varien_Event_Observer $observer the event observer.
     *
     * @return Nosto_Tagging_Model_Observer
     */
    public function sendOrderConfirmation(Varien_Event_Observer $observer)
    {
        if (Mage::helper('nosto_tagging')->isModuleEnabled()) {
            /** @var Mage_Sales_Model_Order $mageOrder */
            /** @noinspection PhpUndefinedMethodInspection */
            $mageOrder = $observer->getEvent()->getOrder();
            /** @var Nosto_Tagging_Model_Service_Order $service */
            $service = Mage::getModel('nosto_tagging/service_order');
            try {
                $service->confirm($mageOrder);
            } catch (Exception $e) {
                NostoLog::exception($e);
            }
        }

        return $this;
    }

    /**
     * Cron job for syncing currency exchange rates to Nosto.
     * Only stores that have the scheduled update enabled, have more currencies
     * than the default one defined and has a Nosto account are synced.
     *
     * @throws Mage_Cron_Exception
     */
    public function scheduledCurrencyExchangeRateUpdate()
    {
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        if ($helper->isModuleEnabled()) {
            /** @var Nosto_Tagging_Helper_Account $accountHelper */
            $accountHelper = Mage::helper('nosto_tagging/account');
            $error = false;
            /** @var Mage_Core_Model_Store $store */
            foreach (Mage::app()->getStores() as $store) {
                if (
                    !$helper->isScheduledCurrencyExchangeRateUpdateEnabled($store)
                    || !$helper->isMultiCurrencyMethodExchangeRate($store)
                ) {
                    continue;
                }
                $account = $accountHelper->find($store);
                if ($account === null) {
                    continue;
                }
                if (!$accountHelper->updateCurrencyExchangeRates($account, $store)) {
                    $error = true;
                }
            }
            if ($error) {
                throw Mage::exception(
                    'Mage_Cron',
                    sprintf(
                        'There was an error updating the exchange rates. More info in "%".',
                        Nosto_Tagging_Model_Base::LOG_FILE_NAME
                    )
                );
            }
        }
    }

    /**
     * Updates / synchronizes Nosto account settings via API to Nosto
     * for each store that has Nosto account.
     *
     * Event 'admin_system_config_changed_section_nosto_tagging'.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Nosto_Tagging_Model_Observer
     */
    public function syncNostoAccount(/** @noinspection PhpUnusedParameterInspection */ // @codingStandardsIgnoreLine
        Varien_Event_Observer $observer)
    {
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        if ($helper->isModuleEnabled()) {
            /** @var Nosto_Tagging_Helper_Account $accountHelper */
            $accountHelper = Mage::helper('nosto_tagging/account');
            /** @var Mage_Core_Model_Store $store */
            foreach (Mage::app()->getStores() as $store) {
                $account = $accountHelper->find($store);
                if ($account instanceof Nosto_Object_Signup_Account === false) {
                    continue;
                }
                /* @var Mage_Core_Model_App_Emulation $emulation */
                $emulation = Mage::getSingleton('core/app_emulation');
                $env = $emulation->startEnvironmentEmulation($store->getId());
                if (!$accountHelper->updateAccount($account, $store)) {
                    NostoLog::error(
                        'Failed sync account #%s for store #%s in class %s',
                        array(
                            $account->getName(),
                            $store->getName(),
                            __CLASS__
                        )
                    );
                }
                if ($helper->isMultiCurrencyMethodExchangeRate($store)) {
                    if (
                        !$accountHelper->updateCurrencyExchangeRates(
                            $account, $store
                        )
                    ) {
                        NostoLog::error(
                            'Failed sync currency rates #%s for store #%s in class %s',
                            array(
                                $account->getName(),
                                $store->getName(),
                                __CLASS__
                            )
                        );
                    }
                }
                $emulation->stopEnvironmentEmulation($env);
            }
        }

        return $this;
    }
}
