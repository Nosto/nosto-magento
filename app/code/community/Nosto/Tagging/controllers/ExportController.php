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

/* @var Nosto_Tagging_Helper_Bootstrap $nostoBootstrapHelper */
$nostoBootstrapHelper = Mage::helper('nosto_tagging/bootstrap');
$nostoBootstrapHelper->init();

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
    const ID = 'id';
    const LIMIT = 'limit';
    const OFFSET = 'offset';
    const CREATED_AT = 'created_at';

    protected static $_searchableFields = array(
        'sales/order' => array(
            self::ID => 'entity_id'
        ),
        'nosto_tagging/product' => array(
            self::ID => 'entity_id'
        )
    );

    /**
     * @param $collection
     */
    protected function applyIdFilters(&$collection)
    {
        /** @var Mage_Sales_Model_Resource_Collection_Abstract $collection */
        if ($id = $this->getRequest()->getParam(self::ID)) {
            /** @var string $collectionModel */
            $collectionModel = $collection->getModelName();
            if (!empty(self::$_searchableFields[$collectionModel])
                && !empty(self::$_searchableFields[$collectionModel][self::ID])
            ) {
                $filterByField = self::$_searchableFields[$collectionModel][self::ID];
                if (!is_array($id)) {
                    $ids = explode(',', $id);
                    if (!empty($ids)) {
                        $id = $ids;
                    }
                }

                if (is_array($id) && !empty($id)) {
                    $collection->addFieldToFilter($filterByField, array('in' => $id));
                } else {
                    $collection->addFieldToFilter($filterByField, $id);
                }
            }
        }
    }

    /**
     * Exports completed orders from the current store.
     * Result can be limited by the `limit` and `offset` GET parameters.
     */
    public function orderAction()
    {
        /** @var Nosto_Tagging_Helper_Module $moduleHelper */
        $moduleHelper = Mage::helper('nosto_tagging/module');
        if ($moduleHelper->isModuleEnabled()) {
            $pageSize = (int)$this->getRequest()->getParam(self::LIMIT, 100);
            $currentOffset = (int)$this->getRequest()->getParam(self::OFFSET, 0);
            $currentPage = ($currentOffset / $pageSize) + 1;
            /** @var Mage_Sales_Model_Resource_Order_Collection $orders */
            $orders = Mage::getModel('sales/order')->getCollection();
            $this->applyIdFilters($orders);
            /** @var Nosto_Tagging_Helper_Data $helper */
            $helper = Mage::helper('nosto_tagging');
            $orders->addFieldToFilter('store_id', $helper->getStore()->getId())
                ->setPageSize($pageSize)
                ->setCurPage($currentPage)
                ->setOrder(self::CREATED_AT, Varien_Data_Collection::SORT_ORDER_DESC);
            if ($currentPage > $orders->getLastPageNumber()) {
                $orders = array();
            }

            $collection = new Nosto_Object_Order_OrderCollection();
            /* @var Mage_Sales_Model_Order $order */
            foreach ($orders as $order) {
                /** @var Nosto_Tagging_Helper_Class $helper */
                $helper = Mage::helper('nosto_tagging/class');
                /** @var Nosto_Tagging_Model_Meta_Order $meta */
                $meta = $helper->getOrderClass($order);
                try {
                    $meta->loadData($order);
                } catch (Nosto_NostoException $e) {
                    NostoLog::exception($e);
                }

                $collection->append($meta);
            }

            $this->export($collection);
        }
    }

    /**
     * Exports visible products from the current store.
     * Result can be limited by the `limit` and `offset` GET parameters.
     * @throws Mage_Core_Exception
     */
    public function productAction()
    {
        /** @var Nosto_Tagging_Helper_Module $moduleHelper */
        $moduleHelper = Mage::helper('nosto_tagging/module');
        if ($moduleHelper->isModuleEnabled()) {
            /** @var Nosto_Tagging_Helper_Data $helper */
            $helper = Mage::helper('nosto_tagging');
            $store = $helper->getStore();
            $storeId = $store->getId();
            $pageSize = (int)$this->getRequest()->getParam(self::LIMIT, 100);
            $currentOffset = (int)$this->getRequest()->getParam(self::OFFSET, 0);
            $currentPage = ($currentOffset / $pageSize) + 1;
            // We use our own collection object to avoid issues with the product
            // flat collection. It's missing required data by default.
            /** @var Nosto_Tagging_Model_Resource_Product_Collection $products */
            $products = Mage::getModel('nosto_tagging/product')->getCollection();
            $this->applyIdFilters($products);
            $products->addStoreFilter($storeId)
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
                ->setCurPage($currentPage)
                ->setOrder(self::CREATED_AT, Varien_Data_Collection::SORT_ORDER_DESC);
            if ($currentPage > $products->getLastPageNumber()) {
                $products = array();
            }

            $collection = new Nosto_Object_Product_ProductCollection();
            /** @var Mage_Catalog_Model_Product $product */
            foreach ($products as $product) {
                try {
                    $meta = Nosto_Tagging_Model_Meta_Product_Builder::build(
                        $product,
                        $store
                    );
                    if ($meta instanceof Nosto_Tagging_Model_Meta_Product && $meta->isValid()) {
                        $collection->append($meta);
                    }
                } catch (Nosto_NostoException $e) {
                    Nosto_Tagging_Helper_Log::exception($e);
                }
            }

            $this->export($collection);
        }
    }

    /**
     * Encrypts the export collection and outputs it to the browser.
     *
     * @param Nosto_Object_AbstractCollection $collection the data collection to export.
     */
    protected function export(Nosto_Object_AbstractCollection $collection)
    {
        /** @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('nosto_tagging/account');
        $account = $helper->find();
        $this->getResponse()->setHeader('Content-type', 'application/octet-stream');
        if ($account !== null) {
            $cipherText = (new Nosto_Helper_ExportHelper())->export($account, $collection);
            $this->getResponse()->setBody($cipherText);
        } else {
            $this->getResponse()->setBody('');
        }
    }
}
