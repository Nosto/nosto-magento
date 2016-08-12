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
 * @copyright Copyright (c) 2013-2016 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper class for building urls.
 * Includes getters for all preview urls for the Nosto account configuration
 * iframe.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Url extends Mage_Core_Helper_Abstract
{
    /**
     * Gets the absolute preview URL to the current store view product page.
     * The product is the first one found in the database for the store.
     * The preview url includes "nostodebug=true" parameter.
     *
     * @param Mage_Core_Model_Store $store the store to get the url for.
     *
     * @return string the url.
     */
    public function getPreviewUrlProduct(Mage_Core_Model_Store $store)
    {
        $url_options = $this->getUrlOptions($store);
        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addStoreFilter($store->getId())
            ->addAttributeToFilter(
                'status', array(
                    'eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED
                )
            )
            ->addFieldToFilter(
                'visibility',
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
            )
            ->setPageSize(1)
            ->setCurPage(1);
        foreach ($collection as $product) {
            /** @var Mage_Catalog_Model_Product $product */
            $url = $product->getProductUrl(!$url_options['_nosid']);
            if ($url_options['_store_to_url']) {
                $url = NostoHttpRequest::replaceQueryParamInUrl(
                    '___store', $store->getCode(), $url
                );
            }
            return NostoHttpRequest::replaceQueryParamInUrl(
                'nostodebug', 'true', $url
            );
        }
        return '';
    }

    /**
     * Gets the absolute preview URL to the current store view category page.
     * The category is the first one found in the database for the store.
     * The preview url includes "nostodebug=true" parameter.
     *
     * @param Mage_Core_Model_Store $store the store to get the url for.
     *
     * @return string the url.
     */
    public function getPreviewUrlCategory(Mage_Core_Model_Store $store)
    {
        $url_options = $this->getUrlOptions($store);
        $rootCategoryId = (int)$store->getRootCategoryId();
        $collection = Mage::getModel('catalog/category')
            ->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('path', array('like' => "1/$rootCategoryId/%"))
            ->setPageSize(1)
            ->setCurPage(1);
        /** @var Mage_Catalog_Model_Category $category */
        foreach ($collection as $category) {
            $url = $category->getUrl();
            if ($url_options['_store_to_url']) {
                $url = NostoHttpRequest::replaceQueryParamInUrl(
                    '___store', $store->getCode(), $url
                );
            }
            return NostoHttpRequest::replaceQueryParamInUrl(
                'nostodebug', 'true', $url
            );
        }
        return '';
    }

    /**
     * Gets the absolute preview URL to the current store view search page.
     * The search query in the URL is "q=nosto".
     * The preview url includes "nostodebug=true" parameter.
     *
     * @param Mage_Core_Model_Store $store the store to get the url for.
     *
     * @return string the url.
     */
    public function getPreviewUrlSearch(Mage_Core_Model_Store $store)
    {
        $url = Mage::getUrl('catalogsearch/result', $this->getUrlOptions($store));
        $url = NostoHttpRequest::replaceQueryParamInUrl('q', 'nosto', $url);
        return NostoHttpRequest::replaceQueryParamInUrl(
            'nostodebug', 'true', $url
        );
    }

    /**
     * Gets the absolute preview URL to the current store view cart page.
     * The preview url includes "nostodebug=true" parameter.
     *
     * @param Mage_Core_Model_Store $store the store to get the url for.
     *
     * @return string the url.
     */
    public function getPreviewUrlCart(Mage_Core_Model_Store $store)
    {
        $url = Mage::getUrl('checkout/cart', $this->getUrlOptions($store));
        return NostoHttpRequest::replaceQueryParamInUrl(
            'nostodebug', 'true', $url
        );
    }

    /**
     * Gets the absolute preview URL to the current store view front page.
     * The preview url includes "nostodebug=true" parameter.
     *
     * @param Mage_Core_Model_Store $store the store to get the url for.
     *
     * @return string the url.
     */
    public function getPreviewUrlFront(Mage_Core_Model_Store $store)
    {
        $url = Mage::getUrl('', $this->getUrlOptions($store));
        return NostoHttpRequest::replaceQueryParamInUrl(
            'nostodebug', 'true', $url
        );
    }

    private function getUrlOptions(Mage_Core_Model_Store $store)
    {
        /* @var Nosto_Tagging_Helper_Data $nosto_helper */
        $nosto_helper = Mage::helper('nosto_tagging');
        $params = array(
            '_store' => $store->getId(),
            '_store_to_url' => true,
            '_nosid' => true
        );
        if ($nosto_helper->getUsePrettyProductUrls($store)) {
            $params['_store_to_url'] = false;
        }

        return $params;
    }

    /**
     * Generates url for a product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store $store
     *
     * @return string the url.
     */
    public function generateProductUrl(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        // Unset the cached url first, as it won't include the `___store` param
        // if it's cached. We need to define the specific store view in the url
        // in case the same domain is used for all sites.
        $product->unsetData('url');
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        /* @var Nosto_Tagging_Model_Meta_Product_Url $url*/
        $nosto_product_url = Mage::getModel('nosto_tagging/meta_product_url');
        $url_params = array(
            '_nosid' => true,
            '_ignore_category' => true,
            '_store' => $store->getId(),
        );
        if ($helper->getUsePrettyProductUrls($store)) {
            $url_params['_store_to_url'] = false;
        } else {
            $url_params['_store_to_url'] = true;
        }
        $product_url = $nosto_product_url->getUrl($product, $url_params);

        return $product_url;
    }
}
