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

    protected function applyIdFilters(&$collection)
    {
        /** @var Mage_Sales_Model_Resource_Collection_Abstract $collection */
        if ($id = $this->getRequest()->getParam(self::ID)) {
            /** @var string $collectionModel */
            $collectionModel = $collection->getModelName();
            if (
                !empty(self::$_searchableFields[$collectionModel])
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
     * Reindex product into Nosto product index if it doesn't exist already
     *
     * @param Nosto_Tagging_Model_Meta_Product $nostoProduct
     * @param Mage_Core_Model_Store $store
     */
    protected function reindexIfNeeded(
        Nosto_Tagging_Model_Meta_Product $nostoProduct,
        Mage_Core_Model_Store $store
    ) 
    {
        /* @var Nosto_Tagging_Model_Indexer_Product $indexer */
        $indexer = Mage::getModel('nosto_tagging/indexer_product');
        try {
            /* @var Nosto_Tagging_Model_Meta_Product $nostoProduct */
            $indexedProduct = Mage::getModel('nosto_tagging/index')
                ->getCollection()
                ->addFieldToFilter('product_id', $nostoProduct->getProductId())
                ->addFieldToFilter('store_id', $store->getId())
                ->setPageSize(1)
                ->setCurPage(1)
                ->getFirstItem(); // @codingStandardsIgnoreLine
            if ($indexedProduct instanceof Nosto_Tagging_Model_Index === false
                || !$indexedProduct->getId()
            ) {
                $indexer->reindexProductInStore($nostoProduct, $store, true);
            }
        } catch (\Exception $reindexException) {
            Nosto_Tagging_Helper_Log::exception($reindexException);
        }
    }

    /**
     * Exports completed orders from the current store.
     * Result can be limited by the `limit` and `offset` GET parameters.
     */
    public function orderAction()
    {
        if (Mage::helper('nosto_tagging/module')->isModuleEnabled()) {
            $pageSize = (int)$this->getRequest()->getParam(self::LIMIT, 100);
            $currentOffset = (int)$this->getRequest()->getParam(self::OFFSET, 0);
            $currentPage = ($currentOffset / $pageSize) + 1;
            /** @var Mage_Sales_Model_Resource_Order_Collection $orders */
            $orders = Mage::getModel('sales/order')->getCollection();
            $this->applyIdFilters($orders);
            $orders->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
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
                $meta->loadData($order);
                $collection->append($meta);
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
        if (Mage::helper('nosto_tagging/module')->isModuleEnabled()) {
            /* @var Mage_Core_Model_Store $store */
            $store = Mage::app()->getStore();
            $storeId = $store->getId();
            /* @var Nosto_Tagging_Helper_Data $dataHelper */
            $dataHelper = Mage::helper('nosto_tagging');
            $reindexProducts = $dataHelper->getUseProductIndexer($store);
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
                        Mage::app()->getStore(),
                        false
                    );
                    if ($meta instanceof Nosto_Tagging_Model_Meta_Product && $meta->isValid()) {
                        // reindex the product as well if index is being used
                        if ($reindexProducts) {
                            $this->reindexIfNeeded($meta, $store);
                        }
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
