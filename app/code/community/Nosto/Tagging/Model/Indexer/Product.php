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
        ),
        Mage_Core_Model_Config_Data::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mage_Catalog_Model_Convert_Adapter_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mage_Customer_Model_Group::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
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
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        $entity = $event->getEntity();

        if ($entity == Mage_Core_Model_Config_Data::ENTITY || $entity == Mage_Customer_Model_Group::ENTITY) {
            $process = $event->getProcess();
            $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        } else if ($entity == Mage_Catalog_Model_Convert_Adapter_Product::ENTITY) {
            $event->addNewData('catalog_product_price_reindex_all', true);
        } else if ($entity == Mage_Catalog_Model_Product::ENTITY) {
            switch ($event->getType()) {
                case Mage_Index_Model_Event::TYPE_DELETE:
                    $this->_registerCatalogProductDeleteEvent($event);
                    break;

                case Mage_Index_Model_Event::TYPE_SAVE:
                    $this->_registerCatalogProductSaveEvent($event);
                    break;

                case Mage_Index_Model_Event::TYPE_MASS_ACTION:
                    $this->_registerCatalogProductMassActionEvent($event);
                    break;
                case self::EVENT_TYPE_REINDEX_PRICE:
                    $event->addNewData('id', $event->getDataObject()->getId());
                    break;
            }

            // call product type indexers registerEvent
            $indexers = $this->_getResource()->getTypeIndexers();
            foreach ($indexers as $indexer) {
                $indexer->registerEvent($event);
            }
        }
    }

    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if ($event->getType() == self::EVENT_TYPE_REINDEX_PRICE) {
            $this->_getResource()->reindexProductIds($data['id']);
            return;
        }
        if (!empty($data['catalog_product_price_reindex_all'])) {
            $this->reindexAll();
        }
        if (empty($data['catalog_product_price_skip_call_event_handler'])) {
            $this->callEventHandler($event);
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
            ->setPageSize(3)
            ->setCurPage(1);

        foreach ($products as $product) {
            /* @var Nosto_Tagging_Model_Meta_Product $nostoProduct */
            $nostoProduct = Mage::getModel('nosto_tagging/meta_product');
            $nostoProduct->reloadData($product, $store);
            $this->reindexProduct($nostoProduct);
        }
    }

    public function reindexProduct(Nosto_Tagging_Model_Meta_Product $product)
    {
        /*
        * - Find the product from index
        * - Check if the content hash matches
        * - If not or not exist, insert new one
        */

        $indexedProduct = Mage::getModel('nosto_tagging/index')->load($product->getProductId());
        $serialized = serialize($indexedProduct);
        if ($indexedProduct instanceof Nosto_Tagging_Model_Index) {
            if ($indexedProduct->getData() !== $serialized) {
                $indexedProduct = Mage::getModel('nosto_tagging/index');
                $indexedProduct->setId($product->getProductId());
                $indexedProduct->setData($serialized);
                $indexedProduct->save();
                // Update
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
}
