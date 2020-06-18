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

/**
 * Handles sending product updates to Nosto via the API.
 *
 */
class Nosto_Tagging_Model_Service_Product
{
    /**
     * Maximum batch size. If exceeded, the batches will be splitted into smaller
     * ones.
     *
     * @var int
     */
    public static $maxBatchSize = 500;

    /**
     * Sends a product update to Nosto for all stores and installed Nosto
     * accounts
     *
     * @param Mage_Catalog_Model_Product[] $products
     * @return bool
     * @throws Mage_Core_Exception
     */
    protected function update(array $products)
    {
        $productsInStore = array();
        $counter = 0;
        $batch = 1;
        foreach ($products as $product) {
            if ($counter > 0 && $counter % self::$maxBatchSize === 0) {
                ++$batch;
            }

            ++$counter;
            if ($product instanceof Mage_Catalog_Model_Product === false) {
                Mage::throwException(
                    sprintf(
                        'Invalid data type, expecting Mage_Catalog_Model_Product' .
                        ', got %s',
                        get_class($product)
                    )
                );
            }

            $productsToUpdate = Nosto_Tagging_Util_Product::toParentProducts($product);
            foreach ($productsToUpdate as $productToUpdate) {
                foreach ($product->getStoreIds() as $storeId) {
                    if (!isset($productsInStore[$storeId])) {
                        $productsInStore[$storeId] = array();
                    }

                    if (!isset($productsInStore[$storeId][$batch])) {
                        $productsInStore[$storeId][$batch] = array();
                    }

                    $productsInStore[$storeId][$batch][] = $productToUpdate;
                }
            }
        }

        // Batch ready - process batches for each store
        foreach ($productsInStore as $storeId => $productBatches) {
            /** @var Nosto_Tagging_Helper_Data $helper */
            $helper = Mage::helper('nosto_tagging');
            $store = $helper->getStore($storeId);
            /** @var Nosto_Tagging_Helper_Account $helper */
            $helper = Mage::helper('nosto_tagging/account');
            $account = $helper->find($store);
            /* @var $nostoHelper Nosto_Tagging_Helper_Data */
            $nostoHelper = Mage::helper('nosto_tagging');
            if ($account === null
                || !$account->isConnectedToNosto()
                || !$nostoHelper->getUseProductApi($store)
            ) {
                continue;
            }

            /* @var Mage_Core_Model_App_Emulation $emulation */
            $emulation = Mage::getSingleton('core/app_emulation');
            $env = $emulation->startEnvironmentEmulation($store->getId());
            $urlHelper = Mage::helper('nosto_tagging/url');
            $activeDomain = $urlHelper->getActiveDomain($store);
            foreach ($productBatches as $productsInStore) {
                try {
                    $operation = new Nosto_Operation_UpsertProduct($account, $activeDomain);
                    $operation->setResponseTimeout($this->getApiWaitTimeout());
                    /* @var $mageProduct Mage_Catalog_Model_Product */
                    foreach ($productsInStore as $mageProduct) {
                        if ($mageProduct instanceof Mage_Catalog_Model_Product === false) {
                            continue;
                        }

                        /** @var Nosto_Tagging_Model_Meta_Product $nostoProduct */
                        $nostoProduct = Mage::getModel('nosto_tagging/meta_product');
                        // If the current store scope is the main store scope, also referred to as
                        // the admin store scope, then we should reload the product as the store
                        // code of the product refers to an pseudo store scope called "admin"
                        // which leads to issues when flat tables are enabled.
                        if ($nostoProduct->reloadData($mageProduct, $store)
                            && $nostoProduct->isValid()
                        ) {
                            /** @phan-suppress-next-line PhanTypeMismatchArgument */
                            $operation->addProduct($nostoProduct);
                        }
                    }

                    $operation->upsert();
                } catch (\Exception $e) {
                    Nosto_Tagging_Helper_Log::exception($e);
                }
            }

            $emulation->stopEnvironmentEmulation($env);
        }

        return true;
    }

    /**
     * Updates a batch of products to Nosto
     *
     * @param Nosto_Tagging_Model_Resource_Product_Collection $products
     * @return bool
     *
     * @throws Mage_Core_Exception
     */
    public function updateBatch(Nosto_Tagging_Model_Resource_Product_Collection $products)
    {
        $productsArray = iterator_to_array($products);
        return $this->update($productsArray);
    }

    /**
     * Updates single product to Nosto
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     *
     * @throws Mage_Core_Exception
     */
    public function updateProduct(Mage_Catalog_Model_Product $product)
    {
        return $this->update(array($product));
    }

    /**
     * Deletes / discontinues products
     *
     * @param Mage_Core_Model_Store $store
     * @param array $productIds
     * @throws Exception
     */
    public function discontinue(Mage_Core_Model_Store $store, array $productIds)
    {
        /** @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('nosto_tagging/account');
        $account = $helper->find($store);
        if ($account === null || !$account->isConnectedToNosto()) {
            return;
        }

        $operation = new Nosto_Operation_DeleteProduct($account);
        $operation->setProductIds($productIds);
        try {
            $operation->delete();
        } catch (\Exception $e) {
            Nosto_Tagging_Helper_Log::exception($e);
        }
    }

    /**
     * Returns the wait timeout for product API call
     *
     * @return int
     */
    protected function getApiWaitTimeout()
    {
        return Nosto_Nosto::getEnvVariable('NOSTO_PRODUCT_API_WAIT_TIMEOUT', 120);
    }
}
