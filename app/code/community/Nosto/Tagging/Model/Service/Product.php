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
 * Handles sending product updates to Nosto via the API.
 *
 */
class Nosto_Tagging_Model_Service_Product
{
    /**
     * Maximum batch size. If exceeded the batches will be splitted into smaller
     * ones.
     *
     * @var int
     */
    public static $maxBatchSize = 500;

    /**
     * Max time to wait for Nosto's API response
     *
     * @var int
     */
    public static $apiWaitTimeout = 60;

    /**
     * Sends a product update to Nosto for all stores and installed Nosto
     * accounts
     *
     * @param Mage_Catalog_Model_Product[] $products
     * @return bool
     * @throws NostoException
     */
    protected function update(array $products)
    {
        NostoHttpRequest::$responseTimeout = self::$apiWaitTimeout;
        $productsInStore = array();
        $counter = 0;
        $batch = 1;
        foreach ($products as $product) {
            if ($counter > 0 && $counter % self::$maxBatchSize === 0) {
                ++$batch;
            }
            ++$counter;
            if ($product instanceof Mage_Catalog_Model_Product === false) {
                Nosto::throwException(
                    sprintf(
                        'Invalid data type, expecting Mage_Catalog_Model_Product' .
                        ', got %s',
                        get_class($product)
                    )
                );
            }
            foreach ($product->getStoreIds() as $storeId) {
                if (!isset($productsInStore[$storeId])) {
                    $productsInStore[$storeId] = array();
                }
                if (!isset($productsInStore[$storeId][$batch])) {
                    $productsInStore[$storeId][$batch] = array();
                }
                $productsInStore[$storeId][$batch][] = $product;
            }
        }
        foreach ($productsInStore as $storeId => $productBatches) {
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
            /* @var Mage_Core_Model_App_Emulation $emulation */
            $emulation = Mage::getSingleton('core/app_emulation');
            $env = $emulation->startEnvironmentEmulation($store->getId());
            foreach ($productBatches as $productsInStore) {
                $service = new NostoOperationProduct($account);
                foreach ($productsInStore as $mageProduct) {
                    /** @var Nosto_Tagging_Model_Meta_Product $nostoProduct */
                    $nostoProduct = Mage::getModel('nosto_tagging/meta_product');
                    $nostoProduct->loadData($mageProduct, $store);
                    $service->addProduct($nostoProduct);
                }
                $service->upsert();
            }
            $emulation->stopEnvironmentEmulation($env);
        }

        return true;
    }

    /**
     * Updates a batch of products to Nosto
     *
     * @param Nosto_Tagging_Model_Resource_Product_Collection $products
     * @return true
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
     * @return true
     */
    public function updateProduct(Mage_Catalog_Model_Product $product)
    {
        return $this->update(array($product));
    }
}

