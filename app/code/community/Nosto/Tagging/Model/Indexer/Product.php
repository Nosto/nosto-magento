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
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'nosto_indexer';

    /**
     * Reindex price event type
     */
    const EVENT_TYPE_REINDEX_PRICE = 'catalog_reindex_price';

    /**
     * Reindex price event type
     */
    const HARD_LIMIT_FOR_PRODUCTS = 10000;

    private $reindexQueue = array();
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
     * Adds product object to index queue
     *
     * @param Mage_Catalog_Model_Product $product
     * @param int $storeId
     */
    private function addToReindexQueue(
        Mage_Catalog_Model_Product $product,
        $storeId
    ) {
        if (empty($this->reindexQueue[$storeId])) {
            $this->reindexQueue[$storeId] = array();
        }
        if (!isset($this->reindexQueue[$storeId][$product->getId()])) {
            $this->reindexQueue[$storeId][$product->getId()] = $product;
        }
    }

    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     * @return bool
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $entity = $event->getEntity();
        $objectId = $event->getDataObject()->getId();

        if ($entity !== 'catalog_product' && !$objectId) {
            return false;
        }
        $catalogProduct = Mage::getModel('catalog/product')->load($objectId);
        if ($catalogProduct instanceof Mage_Catalog_Model_Product === false) {
            return false;
        }
        // Check if we're handling simple product with parents
        $products = Nosto_Tagging_Util_Product::toParentProducts($catalogProduct);
        foreach ($products as $product) {
            foreach ($product->getStoreIds() as $storeId) {
                $this->addToReindexQueue($product, $storeId);
            }
        }

        return true;
    }

    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        foreach ($this->reindexQueue as $storeId => $products) {
            $store = Mage::app()->getStore($storeId);
            /** @var Nosto_Tagging_Helper_Account $helper */
            $helper = Mage::helper('nosto_tagging/account');
            $account = $helper->find($store);
            /* @var $nostoHelper Nosto_Tagging_Helper_Data */
            $nostoHelper = Mage::helper('nosto_tagging');
            if (
                $account === null
                || !$account->isConnectedToNosto()
                || !$nostoHelper->getUseProductApi($store)
            ) {
                continue;
            }

            foreach ($products as $product) {
                /* @var Nosto_Tagging_Model_Meta_Product $nostoProduct */
                $nostoProduct = Mage::getModel('nosto_tagging/meta_product');
                $nostoProduct->reloadData(
                    $product,
                    $store
                );
                if ($nostoProduct instanceof Nosto_Tagging_Model_Meta_Product) {
                    $this->reindexProductInStore($nostoProduct, $store);
                }
            }
        }
    }

    /**
     * Reindex all products in a store
     *
     * @param Mage_Core_Model_Store $store
     */
    public function reindexAllInStore(Mage_Core_Model_Store $store)
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
            ->setPageSize(self::HARD_LIMIT_FOR_PRODUCTS)
            ->setCurPage(1);

        Nosto_Tagging_Helper_Log::info(
            sprintf(
                'Indexing / checking %d products',
                    count($products)
                )
        );
        /* @var Mage_Catalog_Model_Product $product */
        foreach ($products as $product) {
            $parents = Nosto_Tagging_Util_Product::toParentProducts($product);
            foreach ($parents as $parent) {
                if ($this->reindexable($parent)) {
                    /* @var Nosto_Tagging_Model_Meta_Product $nostoProduct */
                    $nostoProduct = Mage::getModel('nosto_tagging/meta_product');
                    $nostoProduct->reloadData($parent, $store);
                    $this->reindexProductInStore($nostoProduct, $store);
                }
            }
        }
    }

    private function reindexable(Mage_Catalog_Model_Product $product)
    {
        if (
            $product->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE
        ) {
            return false;
        }

        return true;
    }

    public function reindexProductInStore(
        Nosto_Tagging_Model_Meta_Product $nostoProduct,
        Mage_Core_Model_Store $store
    ) {
        /*
        * - Find the product from index
        * - Check if the content hash matches
        * - If not or not exist, insert new one
        */

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

        $serialized = serialize($nostoProduct);
        if ($indexedProduct instanceof Nosto_Tagging_Model_Index) {
            if ($indexedProduct->getNostoProduct() !== $serialized) {
                $indexedProduct->setNostoProduct($serialized);
                $indexedProduct->setUpdatedAt($dateHelper->gmtDate());
                $indexedProduct->setInSync(false);
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
    }

    /**
     * Reindex all products in all stores where Nosto is installed
     */
    public function reindexAll()
    {
        /* @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('nosto_tagging/account');
        $stores = $helper->getAllStoreViewsWithNostoAccount();
        foreach ($stores as $store) {
            $this->reindexAllInStore($store);
        }
    }

    private function reindexByProductId($id)
    {
        $catalogProduct = Mage::getModel('catalog/product')->load($id);
        if ($catalogProduct instanceof Mage_Catalog_Model_Product === false) {
            return false;
        }
        foreach ($catalogProduct->getStoreIds() as $storeId) {
            $store = Mage::app()->getStore($storeId);
            /** @var Nosto_Tagging_Helper_Account $helper */
            $helper = Mage::helper('nosto_tagging/account');
            $account = $helper->find($store);
            /* @var $nostoHelper Nosto_Tagging_Helper_Data */
            $nostoHelper = Mage::helper('nosto_tagging');
            if (
                $account === null
                || !$account->isConnectedToNosto()
                || !$nostoHelper->getUseProductApi($store)
            ) {
                continue;
            }
            $products = Nosto_Tagging_Util_Product::toParentProducts($catalogProduct);
            foreach ($products as $product) {
                /* @var Nosto_Tagging_Model_Meta_Product $nostoProduct */
                $nostoProduct = Mage::getModel('nosto_tagging/meta_product');
                $nostoProduct = $nostoProduct->reloadData(
                    $product,
                    $store
                );
                if ($nostoProduct instanceof Nosto_Tagging_Model_Meta_Product) {
                    $this->reindexProductInStore($nostoProduct, $store);
                }
            }
        }
    }
}
