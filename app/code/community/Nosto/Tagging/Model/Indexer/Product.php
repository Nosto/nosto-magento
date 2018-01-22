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

/**
 * Class for indexing Nosto products
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Indexer_Product extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Reindex price event type
     */
    const EVENT_TYPE_REINDEX_PRICE = 'catalog_reindex_price';

    /**
     * A queue for products to be updated
     *
     * @var Mage_Catalog_Model_Product[]
     */
    protected $_reindexQueue = array();

    /**
     * A queue for product ids to be deleted
     *
     * @var array
     */
    protected $_deleteQueue = array();

    /**
     * Array containing already procecced product ids
     *
     * @var array
     */
    protected $_processed = array();

    /**
     * Maximum batch size for indexing products. If exceeded the batches will
     * be splitted into smaller ones.
     *
     * @var int
     */
    public static $maxBatchSize = 500;

    /**
     * Maximum amount of batches to be indexed.
     *
     * @var int
     */
    public static $maxBatchCount = 10000;



    /**
     * Matched Entities instruction array
     *
     * @var array
     */
    protected $_matchedEntities = array(
        Mage_Catalog_Model_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE,
            Mage_Index_Model_Event::TYPE_MASS_ACTION,
            self::EVENT_TYPE_REINDEX_PRICE,
        )
    );

    protected $_relatedConfigSettings = array(
        Mage_Catalog_Helper_Data::XML_PATH_PRICE_SCOPE,
        Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK
    );

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/index');
    }

    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('catalog')->__('Nosto Products');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('catalog')->__('Index Nosto product data');
    }

    /**
     * Adds product object to reindex queue
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $storeId
     */
    protected function addToReindexQueue(
        Mage_Catalog_Model_Product $product,
        $storeId
    ) 
    {
        if (!isset($this->_reindexQueue[$storeId])) {
            $this->_reindexQueue[$storeId] = array();
        }
        if (!isset($this->_reindexQueue[$storeId][$product->getId()])) {
            $this->_reindexQueue[$storeId][$product->getId()] = $product;
        }
    }

    /**
     * Checks if the product has been already processed / indexed
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store $store
     * @return bool
     */
    protected function isProcessed(
        Mage_Catalog_Model_Product $product,
        Mage_Core_Model_Store $store
    ) 
    {
        $storeId = $store->getId();
        if (!isset($this->_processed[$storeId])) {
            return false;
        }
        if (!isset($this->_processed[$storeId][$product->getId()])) {
            return false;
        }

        return true;
    }

    /**
     * Adds product object processed list
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store $store
     */
    protected function setProcessed(
        Mage_Catalog_Model_Product $product,
        Mage_Core_Model_Store $store
    ) 
    {
        $storeId = $store->getId();
        if (empty($this->_processed[$storeId])) {
            $this->_processed[$storeId] = array();
        }
        $this->_processed[$storeId][$product->getId()] = $product->getId();
    }

    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     * @return bool
     * @throws Exception
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $entity = $event->getEntity();
        /* @var Mage_Catalog_Model_Product $object */
        $object = $event->getDataObject();

        if ($entity !== 'catalog_product'
            || !$object->getId()

        ) {
            return false;
        }
        /* @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product')->load($object->getId());
        if ($event->getType() === 'delete') {
            $this->addProductIdToDeleteQueue($object->getId());
        } else {
            $this->addProductToIndexQueue($product);
        }

        return true;
    }

    /**
     * Adds a product to reindexing queue for all store views
     *
     * @param Mage_Catalog_Model_Product $catalogProduct
     */
    protected function addProductToIndexQueue(Mage_Catalog_Model_Product $catalogProduct)
    {
        // Check if we're handling simple product with parents
        $products = Nosto_Tagging_Util_Product::toParentProducts($catalogProduct);
        foreach ($products as $product) {
            foreach ($product->getStoreIds() as $storeId) {
                $this->addToReindexQueue($product, $storeId);
            }
        }
    }

    /**
     * Adds a product to delete queue for all store views
     *
     * @param string $productId
     */
    protected function addProductIdToDeleteQueue($productId)
    {
        if (!isset($this->_deleteQueue[$productId])) {
            $this->_deleteQueue[$productId] = $productId;
        }
    }

    /**
     * Removes all products that are in delete queue
     *
     * @throws Exception
     */
    protected function flushDeleteQueue()
    {
        foreach ($this->_deleteQueue as $productId) {
            try {
                /** @var Nosto_Tagging_Model_Index $indexedProduct */
                $indexedProducts = Mage::getModel('nosto_tagging/index')
                    ->getCollection()
                    ->addFieldToFilter('product_id', $productId);
                foreach ($indexedProducts as $indexedProduct) {
                    $indexedProduct->delete();
                }
            } catch (\Exception $e) {
                Nosto_Tagging_Helper_Log::exception($e);
            }
        }
    }

    /**
     * Reindexes all products in queue and updates products to Nosto
     *
     * @throws Exception
     */
    protected function flushReindexingQueue()
    {
        foreach ($this->_reindexQueue as $storeId => $products) {
            $store = Mage::app()->getStore($storeId);
            /** @var Nosto_Tagging_Helper_Account $accountHelper */
            $accountHelper = Mage::helper('nosto_tagging/account');
            $account = $accountHelper->find($store);
            /** @var Nosto_Tagging_Helper_Data $dataHelper */
            $dataHelper = Mage::helper('nosto_tagging');
            if (
                $account === null
                || !$account->isConnectedToNosto()
                || !$dataHelper->getUseProductIndexer($store)
            ) {
                continue;
            }
            /* @var Mage_Core_Model_App_Emulation $emulation */
            $emulation = Mage::getSingleton('core/app_emulation');
            $env = $emulation->startEnvironmentEmulation($store->getId());
            foreach ($products as $product) {
                /* @var Nosto_Tagging_Model_Meta_Product $nostoProduct */
                $nostoProduct = Mage::getModel('nosto_tagging/meta_product');
                if ($nostoProduct->reloadData($product, $store)
                    && $nostoProduct instanceof Nosto_Tagging_Model_Meta_Product
                ) {
                    $this->reindexProductInStore($nostoProduct, $store);
                }
            }
            $emulation->stopEnvironmentEmulation($env);
        }
        /* @var Nosto_Tagging_Model_Service_Product $service */
        $service = Mage::getModel('nosto_tagging/service_product');
        $service->updateOutOfSyncToNosto();
    }

    /**
     * Reindexes a product in all store views and updates product to Nosto
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function reindexAndUpdate(Mage_Catalog_Model_Product $product)
    {
        try {
            $this->addProductToIndexQueue($product);
            $this->flushReindexingQueue();
        } catch (\Exception $e) {
            Nosto_Tagging_Helper_Log::exception($e);
        }
    }

    /**
     * Reindexes a product in all store views and updates product to Nosto
     *
     * @param Mage_Catalog_Model_Product[] $products
     */
    public function reindexAndUpdateMany($products)
    {
        try {
            foreach ($products as $product) {
                $this->addProductToIndexQueue($product);
            }
            $this->flushReindexingQueue();
        } catch (\Exception $e) {
            Nosto_Tagging_Helper_Log::exception($e);
        }
    }

    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     *
     * @throws Exception
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        if ($event->getType() == 'delete') {
            $this->flushDeleteQueue();
        } else {
            $this->flushReindexingQueue();
        }
    }

    /**
     * Reindex all products in a store
     *
     * @param Mage_Core_Model_Store $store
     *
     * @throws Exception
     */
    public function reindexAllInStore(Mage_Core_Model_Store $store)
    {
        $start = microtime(true);

        /* @var Mage_Core_Model_App_Emulation $emulation */
        $emulation = Mage::getSingleton('core/app_emulation');
        $env = $emulation->startEnvironmentEmulation($store->getId());

        $iterations = 1;
        $products = $this->getProductBatch($store, $iterations);
        $totalProductCount = $products->getSize();
        $totalBatchCount = ceil($totalProductCount/self::$maxBatchSize);
        $changed = 0;

        while (true) {
            if ($iterations >= self::$maxBatchCount) {
                Nosto_Tagging_Helper_Log::info(
                    sprintf(
                        'Max batch count (%d) reached - exiting indexing',
                        self::$maxBatchCount
                    )
                );
                break;
            }
            $batchCount = count($products);
            if ($batchCount == 0) {
                break;
            }
            Nosto_Tagging_Helper_Log::info(
                sprintf(
                    'Processing %d products in store %s [%d/%d]',
                    count($products),
                    $store->getCode(),
                    $iterations,
                    $totalBatchCount
                )
            );
            /* @var Mage_Catalog_Model_Product $product */
            foreach ($products as $product) {
                try {
                    $changed += $this->reindexMagentoProductInStore($product, $store);
                } catch (\Exception $e) {
                    Nosto_Tagging_Helper_Log::exception($e);
                }
            }
            if ($batchCount < self::$maxBatchSize) {
                break;
            }
            ++$iterations;
            $products = $this->getProductBatch($store, $iterations);
        }
        $emulation->stopEnvironmentEmulation($env);
        Nosto_Tagging_Helper_Log::info(
            sprintf(
                'Indexing done in %d secs, updated %d products',
                microtime(true) - $start,
                $changed
            )
        );
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store $store
     * @return int amount of updated products
     * @throws Exception
     */
    protected function reindexMagentoProductInStore(
        Mage_Catalog_Model_Product $product,
        Mage_Core_Model_Store $store
    ) 
    {
        $changed = 0;
        if ($this->isProcessed($product, $store)) {
            return $changed;
        }
        /** @var Mage_Core_Model_Date $dateHelper */
        $dateHelper = Mage::getSingleton('core/date');
        $startTime = $dateHelper->gmtDate();
        $parents = Nosto_Tagging_Util_Product::toParentProducts($product);
        foreach ($parents as $parent) {
            if ($this->isProcessed($parent, $store)) {
                continue;
            }
            /* @var Nosto_Tagging_Model_Meta_Product $nostoProduct */
            $nostoProduct = Mage::getModel('nosto_tagging/meta_product');
            if ($nostoProduct->reloadData($parent, $store)) {
                $reindexed = $this->reindexProductInStore($nostoProduct, $store);
                if (strtotime($reindexed->getUpdatedAt()) >= strtotime($startTime)) {
                    ++$changed;
                }
            }
            $this->setProcessed($parent, $store);
        }
        $this->setProcessed($product, $store);

        return $changed;
    }

    /**
     * Indexes Nosto product
     *
     * @param Nosto_Tagging_Model_Meta_Product $nostoProduct
     * @param Mage_Core_Model_Store $store
     * @param bool $setSynced
     * @return Nosto_Tagging_Model_Index
     * @throws Exception
     */
    public function reindexProductInStore(
        Nosto_Tagging_Model_Meta_Product $nostoProduct,
        Mage_Core_Model_Store $store,
        $setSynced = false
    ) 
    {
        /** @var Mage_Core_Model_Date $dateHelper */
        $dateHelper = Mage::getSingleton('core/date');
        /* @var Nosto_Tagging_Model_Meta_Product $nostoProduct */
        $indexedProduct = Mage::getModel('nosto_tagging/index')
            ->getCollection()
            ->addFieldToFilter('product_id', $nostoProduct->getProductId())
            ->addFieldToFilter('store_id', $store->getId())
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem(); // @codingStandardsIgnoreLine
        if ($indexedProduct instanceof Nosto_Tagging_Model_Index) {
            $indexedMetaProduct = $indexedProduct->getNostoMetaProduct();
            if ($indexedMetaProduct instanceof Nosto_Tagging_Model_Meta_Product === false
                || $indexedMetaProduct != $nostoProduct
            ) {
                $indexedProduct->setNostoMetaProduct($nostoProduct);
                $indexedProduct->setUpdatedAt($dateHelper->gmtDate());
                if ($setSynced === true) {
                    $indexedProduct->setInSync(1);
                } else {
                    $indexedProduct->setInSync(0);
                }
                if (!$indexedProduct->getId()) {
                    $indexedProduct->setCreatedAt($dateHelper->gmtDate());
                    $indexedProduct->setProductId(
                        $nostoProduct->getProductId()
                    );
                    $indexedProduct->setStoreId($store->getId());
                }
                $indexedProduct->save();
            }
        }

        return $indexedProduct;
    }

    /**
     * Reindex all products in all stores where Nosto is installed
     */
    public function reindexAll()
    {
        /* @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        $stores = $accountHelper->getAllStoreViewsWithNostoAccount();
        /* @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');
        foreach ($stores as $store) {
            if ($dataHelper->getUseProductIndexer($store)) {
                $this->reindexAllInStore($store);
            }
        }
        /* @var Nosto_Tagging_Model_Service_Product $service */
        $service = Mage::getModel('nosto_tagging/service_product');
        $service->updateOutOfSyncToNosto();
    }

    /**
     * Reindex products by productIds
     * @param array $ids
     * @param Mage_Core_Model_Store $store
     * @return bool
     * @throws Exception
     */
    public function reindexByProductIdsInStore(
        array $ids,
        Mage_Core_Model_Store $store
    ) 
    {
        $start = microtime(true);
        Nosto_Tagging_Helper_Log::info(
            sprintf(
                'Processing %d products with given product ids in store %s',
                count($ids),
                $store->getCode()
            )
        );

        /* @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        if (!$accountHelper->find($store)) {
            return true;
        }

        /* @var Mage_Core_Model_App_Emulation $emulation */
        $emulation = Mage::getSingleton('core/app_emulation');
        $env = $emulation->startEnvironmentEmulation($store->getId());
        $changed = 0;
        $batches = array_chunk($ids, self::$maxBatchSize);
        foreach ($batches as $productIds) {
            /* @var Nosto_Tagging_Model_Resource_Product_Collection $products */
            $products = Mage::getModel('nosto_tagging/product')->getCollection();
            $products->addStoreFilter($store->getId())
                ->addFieldToFilter('entity_id', array('in' => $productIds));
            foreach ($products as $product) {
                try {
                    $changed += $this->reindexMagentoProductInStore($product, $store);
                } catch (\Exception $e) {
                    Nosto_Tagging_Helper_Log::exception($e);
                }
            }
        }
        $emulation->stopEnvironmentEmulation($env);
        Nosto_Tagging_Helper_Log::info(
            sprintf(
                'Indexing done in %d secs, updated %d products',
                microtime(true) - $start,
                $changed
            )
        );
        /* @var Nosto_Tagging_Model_Service_Product $service */
        $service = Mage::getModel('nosto_tagging/service_product');
        $service->updateOutOfSyncToNosto();

        return true;
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @param int $pageNumber
     * @return Nosto_Tagging_Model_Resource_Product_Collection
     */
    protected function getProductBatch(
        Mage_Core_Model_Store $store,
        $pageNumber = 1
    ) 
    {
        $products = Mage::getModel('nosto_tagging/product')->getCollection();
        $products->addStoreFilter($store->getId())
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
            ->addAttributeToSort('entity_id', Varien_Data_Collection::SORT_ORDER_DESC)
            ->setPageSize(self::$maxBatchSize)
            ->setCurPage($pageNumber);

        return $products;
    }
}
