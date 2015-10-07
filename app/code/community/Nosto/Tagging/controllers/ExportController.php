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
 * History data export controller.
 * Handles the export of history data for orders and products that nosto can
 * call when a new account has been set up.
 * The exported data is encrypted with AES as the endpoint is public.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_ExportController extends Mage_Core_Controller_Front_Action
{
    /**
     * Exports completed orders from the current store.
     * Result can be limited by the `limit` and `offset` GET parameters.
     */
    public function orderAction()
    {
        if (Mage::helper('nosto_tagging')->isModuleEnabled()) {
            $pageSize = (int)$this->getRequest()->getParam('limit', 100);
            $currentOffset = (int)$this->getRequest()->getParam('offset', 0);
            $currentPage = ($currentOffset / $pageSize) + 1;
            /** @var Mage_Sales_Model_Resource_Order_Collection $orders */
            $orders = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
                ->setPageSize($pageSize)
                ->setCurPage($currentPage);
            if ($currentPage > $orders->getLastPageNumber()) {
                $orders = array();
            }
            /** @var Nosto_Tagging_Model_Export_Collection_Order $collection */
            $collection = Mage::getModel('nosto_tagging/export_collection_order');
            foreach ($orders as $order) {
                /** @var Nosto_Tagging_Model_Meta_Order $meta */
                $meta = Mage::getModel('nosto_tagging/meta_order');
                // We don't need special items like shipping cost and discounts.
                $meta->includeSpecialItems = false;
                $meta->loadData($order);
                $collection[] = $meta;
            }
            $this->export($collection);
        }
    }

    /**
     * Exports visible products from the current store.
     * Result can be limited by the `limit` and `offset` GET parameters.
     */
    public function productAction()
    {
        if (Mage::helper('nosto_tagging')->isModuleEnabled()) {
            $pageSize = (int)$this->getRequest()->getParam('limit', 100);
            $currentOffset = (int)$this->getRequest()->getParam('offset', 0);
            $currentPage = ($currentOffset / $pageSize) + 1;
            // We use our own collection object to avoid issues with the product
            // flat collection. It's missing required data by default.
            /** @var Nosto_Tagging_Model_Resource_Product_Collection $products */
            $products = Mage::getModel('nosto_tagging/product')
                ->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addAttributeToSelect('*')
                ->addAttributeToFilter(
                    'status', array(
                        'eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED
                    )
                )
                ->addFieldToFilter(
                    'visibility',
                    Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
                )
                ->setPageSize($pageSize)
                ->setCurPage($currentPage);
            if ($currentPage > $products->getLastPageNumber()) {
                $products = array();
            }
            $collection = new NostoExportProductCollection();
            foreach ($products as $product) {
                /** @var Mage_Catalog_Model_Product $product */
                /** @var Nosto_Tagging_Model_Meta_Product $meta */
                $meta = Mage::getModel('nosto_tagging/meta_product');
                $meta->loadData($product);
                $validator = new NostoValidator($meta);
                if ($validator->validate()) {
                    $collection[] = $meta;
                }
            }
            $this->export($collection);
        }
    }

    /**
     * Encrypts the export collection and outputs it to the browser.
     *
     * @param NostoExportCollectionInterface $collection the data collection to export.
     */
    protected function export(NostoExportCollectionInterface $collection)
    {
        $account = Mage::helper('nosto_tagging/account')->find();
        if ($account !== null) {
            $cipherText = NostoExporter::export($account, $collection);
            echo $cipherText;
        }
        die();
    }
}
