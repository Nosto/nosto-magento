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

/* @var Nosto_Tagging_Helper_Bootstrap $nostoBootstrapHelper */
$nostoBootstrapHelper = Mage::helper('nosto_tagging/bootstrap');
$nostoBootstrapHelper->init();

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
     * @throws Mage_Core_Exception
     */
    public function sendProductUpdate(Varien_Event_Observer $observer)
    {
        /** @var Nosto_Tagging_Helper_Module $moduleHelper */
        $moduleHelper = Mage::helper('nosto_tagging/module');
        if ($moduleHelper->isModuleEnabled()) {
            /** @var Mage_Catalog_Model_Product $product */
            /** @noinspection PhpUndefinedMethodInspection */
            $product = $observer->getEvent()->getProduct();

            /* @var Nosto_Tagging_Model_Service_Product $service */
            $service = Mage::getModel('nosto_tagging/service_product');
            $service->updateProduct($product);
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
        /** @var Nosto_Tagging_Helper_Module $moduleHelper */
        $moduleHelper = Mage::helper('nosto_tagging/module');
        if ($moduleHelper->isModuleEnabled()) {
            /** @var Mage_Catalog_Model_Product $product */
            /** @noinspection PhpUndefinedMethodInspection */
            $product = $observer->getEvent()->getProduct();
            // Products are always deleted from all store views, regardless of
            // the store view scope switcher on the product edit page.
            /** @var Mage_Core_Model_Store $store */
            /* @var Nosto_Tagging_Model_Service_Product $productService */
            $productService = Mage::getModel('nosto_tagging/service_product');
            foreach (Mage::app()->getStores() as $store) {
                try {
                    $productService->discontinue($store, array($product->getId()));
                } catch (Exception $e) {
                    Nosto_Tagging_Helper_Log::exception($e);
                }
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
        /** @var Nosto_Tagging_Helper_Module $moduleHelper */
        $moduleHelper = Mage::helper('nosto_tagging/module');
        if ($moduleHelper->isModuleEnabled()) {
            /** @noinspection PhpUndefinedMethodInspection */
            $object = $observer->getEvent()->getObject();
            /** @var Mage_Review_Model_Review_Summary $object */
            $productId = $object->getEntityPkValue();
            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::getModel('catalog/product')->load($productId);
            if ($product instanceof Mage_Catalog_Model_Product) {
                try {
                    /* @var Nosto_Tagging_Model_Service_Product $service */
                    $service = Mage::getModel('nosto_tagging/service_product');
                    $service->updateProduct($product);
                } catch (Exception $e) {
                    NostoLog::exception($e);
                }
            }
        }

        return $this;
    }
}
