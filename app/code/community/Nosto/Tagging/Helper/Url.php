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
     * The ___store parameter in Magento URLs
     */
    const MAGENTO_STORE_URL_PARAMETER = '___store';

    /**
     * The array option key for store in Magento's URLs
     */
    const MAGENTO_URL_OPTION_STORE = '_store';

    /**
     * The array option key for store to url in Magento's URLs
     */
    const MAGENTO_URL_OPTION_STORE_TO_URL = '_store_to_url';

    /**
     * The array option key for no session id in Magento's URLs.
     * The session id should be included into the URLs which are potentially
     * used during the same session, e.g. Oauth redirect URL. For example for
     * product URLs we cannot include the session id as the product URL should
     * be the same for all visitors and it will be saved to Nosto.
     */
    const MAGENTO_URL_OPTION_NOSID = '_nosid';

    /**
     * The array option key for ignoring category in Magento's product URLs
     */
    const MAGENTO_URL_OPTION_IGNORE_CATEGORY = '_ignore_category';

    /**
     * The array option key for URL type in Magento's URLs
     */
    const MAGENTO_URL_OPTION_LINK_TYPE = '_type';

    /**
     * Nosto oauth path
     */
    const NOSTO_OAUTH_PATH = 'nosto/oauth';

    /**
     * The URL parameter to invoke Nosto debug mode in store
     */
    const NOSTO_URL_DEBUG_PARAMETER = 'nostodebug';

    /**
     * The url type to be used for links.
     *
     * This is the only URL type that works correctly the URls when
     * "Add Store Code to Urls" setting is set to "Yes"
     *
     * @see Mage_Core_Model_Store::URL_TYPE_LINK
     *
     * @var string
     */
    public static $urlType = Mage_Core_Model_Store::URL_TYPE_LINK;

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
        $productUrl = '';
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
        /** @var Mage_Catalog_Model_Product $product */
        $product = $collection->getFirstItem();
        if ($product instanceof Mage_Catalog_Model_Product) {
            $url = $this->generateProductUrl($product, $store);
            $productUrl = $this->addNostoPreviewParameter($url);
        }

        return $productUrl;
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
        $rootCategoryId = (int)$store->getRootCategoryId();
        $categoryUrl = '';
        $collection = Mage::getModel('catalog/category')
            ->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('path', array('like' => "1/$rootCategoryId/%"))
            ->setPageSize(1)
            ->setCurPage(1);
        /** @var Mage_Catalog_Model_Category $category */
        $category = $collection->getFirstItem();
        if ($category instanceof Mage_Catalog_Model_Category) {
            $urlOptions = $this->getUrlOptions($store);
            $url = $category->getUrl();
            if ($urlOptions['_store_to_url']) {
                $url = NostoHttpRequest::replaceQueryParamInUrl(
                    self::MAGENTO_STORE_URL_PARAMETER, $store->getCode(), $url
                );
            }
            $categoryUrl = $this->addNostoPreviewParameter($url);
        }

        return $categoryUrl;
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

        return $this->addNostoPreviewParameter($url);
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

        return $this->addNostoPreviewParameter($url);
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

        return $this->addNostoPreviewParameter($url);
    }

    /**
     * Returns the default options for fetching Magento urls
     *
     * @param Mage_Core_Model_Store $store
     * @return array
     */
    private function getUrlOptions(Mage_Core_Model_Store $store)
    {
        /* @var Nosto_Tagging_Helper_Data $nosto_helper */
        $nosto_helper = Mage::helper('nosto_tagging');
        $params = array(
            self::MAGENTO_URL_OPTION_STORE => $store->getId(),
            self::MAGENTO_URL_OPTION_STORE_TO_URL => true,
            self::MAGENTO_URL_OPTION_NOSID => true
        );
        if ($nosto_helper->getUsePrettyProductUrls($store)) {
            $params[self::MAGENTO_URL_OPTION_STORE_TO_URL] = false;
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
        $url_params = array(
            self::MAGENTO_URL_OPTION_NOSID => true,
            self::MAGENTO_URL_OPTION_IGNORE_CATEGORY => true,
            self::MAGENTO_URL_OPTION_STORE => $store->getId(),
            self::MAGENTO_URL_OPTION_STORE_TO_URL => true
        );
        $product_url = $product->getUrlInStore($url_params);
        if ($helper->getUsePrettyProductUrls($store)) {
            $product_url = $this->removeQueryParamFromUrl(
                $product_url,
                self::MAGENTO_STORE_URL_PARAMETER
            );
        }

        return $product_url;
    }


    /**
     * Removes given parameter from the url
     *
     * @param $url
     * @param $param
     * @return string
     */
    public function removeQueryParamFromUrl($url, $param)
    {
        $modified_url = $url;
        $url_parts = NostoHttpRequest::parseUrl($url);
        if (
            $url_parts !== false
            && isset($url_parts['query'])
        ) {
            $query_array = NostoHttpRequest::parseQueryString($url_parts['query']);
            if(isset($query_array[$param])) {
                unset($query_array[$param]);
                if (empty($query_array)) {
                    unset($url_parts['query']);
                } else {
                    $url_parts['query'] = http_build_query($query_array);
                }
                $modified_url = NostoHttpRequest::buildUrl($url_parts);
            }
        }

        return $modified_url;
    }

    /**
     * Returns Oauth redirection URL
     *
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getOauthRedirectUrl(Mage_Core_Model_Store $store)
    {
        $url = Mage::getUrl(
            self::NOSTO_OAUTH_PATH,
            array(
                self::MAGENTO_URL_OPTION_STORE => $store->getId(),
                self::MAGENTO_URL_OPTION_STORE_TO_URL => true,
                self::MAGENTO_URL_OPTION_LINK_TYPE => self::$urlType,
            )
        );

        return $url;
    }

    /**
     * Returns front page URL of the store
     *
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function getFrontPageUrl(Mage_Core_Model_Store $store)
    {
        /* @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        if (!$helper->getUsePrettyProductUrls($store)) {
            $url = NostoHttpRequest::replaceQueryParamInUrl(
                self::MAGENTO_STORE_URL_PARAMETER,
                $store->getCode(),
                $store->getBaseUrl(self::$urlType)
            );
        } else {
            $url = $store->getBaseUrl(self::$urlType);
        }

        return $url;
    }

    private function addNostoPreviewParameter($url)
    {
        return NostoHttpRequest::replaceQueryParamInUrl(
            self::NOSTO_URL_DEBUG_PARAMETER,
            true,
            $url
        );
    }
}
