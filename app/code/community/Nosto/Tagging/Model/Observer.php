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
 * @copyright Copyright (c) 2013-2016 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once __DIR__ . '/../bootstrap.php';

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
            // Always "upsert" the product for all stores it is available in.
            // This is done to avoid data inconsistencies as even if a product
            // is edited for only one store, the updated data can reflect in
            // other stores as well.
            foreach ($product->getStoreIds() as $storeId) {
                $store = Mage::app()->getStore($storeId);

                /** @var Nosto_Tagging_Helper_Account $helper */
                $helper = Mage::helper('nosto_tagging/account');
                $account = $helper->find($store);

                /* @var $nostoHelper Nosto_Tagging_Helper_Data */
                $nostoHelper = Mage::helper('nosto_tagging');
                if ($account === null || !$account->isConnectedToNosto()
                    || !$nostoHelper->getUseProductApi($store)) {
                    continue;
                }

                // Load the product model for this particular store view.
                /** @var Mage_Catalog_Model_Product $catalog */
                $catalog = Mage::getModel('catalog/product');
                /** @noinspection PhpUndefinedMethodInspection */
                $product = $catalog
                    ->setStoreId($store->getId())
                    ->load($product->getId());
                if (is_null($product)) {
                    continue;
                }

                /** @var Nosto_Tagging_Model_Meta_Product $model */
                $model = Mage::getModel('nosto_tagging/meta_product');
                $model->loadData($product, $store);

                // Only send product update if we have all required
                // data for the product model.
                $validator = new NostoValidator($model);
                if ($validator->validate()) {
                    try {
                        $service = new NostoOperationProduct($account);
                        $service->addProduct($model);
                        $service->upsert();
                    } catch (NostoException $e) {
                        Mage::log("\n" . $e, Zend_Log::ERR, 'nostotagging.log');
                    }
                }
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

                /** @var Nosto_Tagging_Model_Meta_Product $model */
                $model = Mage::getModel('nosto_tagging/meta_product');
                $model->setProductId($product->getId());

                try {
                    $service = new NostoOperationProduct($account);
                    $service->addProduct($model);
                    $service->delete();
                } catch (NostoException $e) {
                    Mage::log("\n" . $e, Zend_Log::ERR, 'nostotagging.log');
                }
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
            try {
                /** @var Mage_Sales_Model_Order $mageOrder */
                /** @noinspection PhpUndefinedMethodInspection */
                $mageOrder = $observer->getEvent()->getOrder();
                /** @var Nosto_Tagging_Model_Meta_Order $order */
                $order = Mage::getModel('nosto_tagging/meta_order');
                $order->loadData($mageOrder);
                /** @var Nosto_Tagging_Helper_Account $helper */
                $helper = Mage::helper('nosto_tagging/account');
                $account = $helper->find($mageOrder->getStore());
                /** @var Nosto_Tagging_Helper_Customer $helper */
                $helper = Mage::helper('nosto_tagging/customer');
                $customerId = $helper->getNostoId($mageOrder);
                if ($account !== null && $account->isConnectedToNosto()) {
                    /** @var Nosto_Tagging_Model_Service_Order $service */
                    $service = Mage::getModel('nosto_tagging/service_order');
                    $service->confirm($order, $account, $customerId);
                }
            } catch (NostoException $e) {
                Mage::log("\n" . $e->__toString(), Zend_Log::ERR, 'nostotagging.log');
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
            foreach (Mage::app()->getStores() as $store) {
                /** @var Mage_Core_Model_Store $store */
                if (
                    !$helper->isScheduledCurrencyExchangeRateUpdateEnabled($store)
                    || !$helper->isMultiCurrencyMethodExchangeRate($store)
                ) {
                    continue;
                }
                $account = $accountHelper->find($store);
                if (is_null($account)) {
                    continue;
                }
                if (!$accountHelper->updateCurrencyExchangeRates($account, $store)) {
                    $error = true;
                }
            }
            if ($error) {
                throw Mage::exception(
                    'Mage_Cron',
                    'There was an error updating the exchange rates. More info in "var/log/nostotagging.log".'
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
    public function syncNostoAccount(/** @noinspection PhpUnusedParameterInspection */
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
                if ($account instanceof NostoAccount === false) {
                    continue;
                }
                if (!$accountHelper->updateAccount($account, $store)) {
                    Mage::log(
                        sprintf(
                            'Failed sync account #%s for store #%s in class %s',
                            $account->getName(),
                            $store->getName(),
                            __CLASS__
                        ),
                        Zend_Log::WARN,
                        Nosto_Tagging_Model_Base::LOG_FILE_NAME
                    );
                }
                if ($helper->isMultiCurrencyMethodExchangeRate($store)) {
                    if (!$accountHelper->updateCurrencyExchangeRates(
                        $account, $store
                    )
                    ) {
                        Mage::log(
                            sprintf(
                                'Failed sync currency rates #%s for store #%s in class %s',
                                $account->getName(),
                                $store->getName(),
                                __CLASS__
                            ),
                            Zend_Log::WARN,
                            Nosto_Tagging_Model_Base::LOG_FILE_NAME
                        );
                    }
                }
            }
        }
        return $this;
    }
}
