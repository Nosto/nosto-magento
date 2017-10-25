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

require_once __DIR__ . '/../../bootstrap.php'; // @codingStandardsIgnoreLine
use Nosto_Tagging_Helper_Log as NostoLog;

/**
 * Event observer model for product.
 * Used to interact with Magento events.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 * @suppress PhanUnreferencedClass
 */
class Nosto_Tagging_Model_Observer_Product
{
    /**
     * Event handler for the "catalog_product_save_after" event.
     * Sends a product update API call to Nosto.
     *
     * @param Varien_Event_Observer $observer the event observer.
     *
     * @return Nosto_Tagging_Model_Observer_Product
     */
    public function sendProductUpdate(Varien_Event_Observer $observer)
    {
        if (Mage::helper('nosto_tagging/module')->isModuleEnabled()) {
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
     * @return Nosto_Tagging_Model_Observer_Product
     */
    public function sendProductDelete(Varien_Event_Observer $observer)
    {
        if (Mage::helper('nosto_tagging/module')->isModuleEnabled()) {
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
     * Update product after review/rating has been updated.
     * On event: 'review_save_after' and 'review_delete_after'
     * @param Varien_Event_Observer $observer the event observer.
     * @return Nosto_Tagging_Model_Observer_Product
     */
    public function onReviewUpdated(Varien_Event_Observer $observer)
    {
        if (Mage::helper('nosto_tagging/module')->isModuleEnabled()) {
            $object = $observer->getEvent()->getObject();
            /** @var Mage_Catalog_Model_Product $product */
            $productId = $object->getEntityPkValue();
            $product = Mage::getModel('catalog/product')->load($productId);
            if ($product instanceof Mage_Catalog_Model_Product) {
                try {
                    /* @var Nosto_Tagging_Model_Service_Product $service */
                    $service = Mage::getModel('nosto_tagging/service_product');
                    $service->updateProduct($product);
                } catch (\Exception $e) {
                    NostoLog::exception($e);
                }
            }
        }

        return $this;
    }
}
