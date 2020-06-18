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

/**
 * Helper class for common operations.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Path to store config installation ID.
     */
    const XML_PATH_INSTALLATION_ID = 'nosto_tagging/installation/id';

    /**
     * Path to store config nosto product image version.
     */
    const XML_PATH_IMAGE_VERSION = 'nosto_tagging/image_options/image_version';

    /**
     * Path to store config for attributes to tag
     */
    const XML_PATH_CUSTOM_TAGS = 'nosto_tagging/attribute_to_tag/';

    /**
     * @var string the name of the cookie where the Nosto ID can be found.
     */
    const COOKIE_NAME = '2c_cId';

    /**
     * @var string the algorithm to use for hashing visitor id.
     */
    const VISITOR_HASH_ALGO = 'sha256';

    /**
     * Path to store config multi currency method setting.
     */
    const XML_PATH_MULTI_CURRENCY_METHOD = 'nosto_tagging/multi_currency/method';

    /**
     * Path to store config of price variation switch
     */
    const XML_PATH_VARIATION_SWITCH = 'nosto_tagging/variation/switch';

    /**
     * Path to store config of sending inventory level
     */
    const XML_PATH_SEND_INVENTORY_LEVEL_AFTER_PURCHASE = 'nosto_tagging/general/send_inventory_level_after_purchase';

    /**
     * Path to store config scheduled currency exchange rate update enabled setting.
     */
    const XML_PATH_SCHEDULED_CURRENCY_EXCHANGE_RATE_UPDATE_ENABLED
        = 'nosto_tagging/scheduled_currency_exchange_rate_update/enabled';

    /**
     * Multi currency method option for currency exchange rates.
     */
    const MULTI_CURRENCY_METHOD_EXCHANGE_RATE = 'exchangeRate';

    /**
     * No multi currency
     */
    const MULTI_CURRENCY_DISABLED = 'disabled';

    /**
     * Path to store config for using the product API or not
     */
    const XML_PATH_USE_PRODUCT_API = 'nosto_tagging/general/use_product_api';

    /**
     * Path to store config for sending customer data to Nosto or not
     */
    const XML_PATH_SEND_CUSTOMER_DATA = 'nosto_tagging/general/send_customer_data';

    /**
     * Path to store config for tagging the date a product has beed added to Magento's catalog
     */
    const XML_PATH_TAG_DATE_PUBLISHED = 'nosto_tagging/general/tag_date_published';

    /**
     * Path to store config for using SKUs
     */
    const XML_PATH_USE_SKUS = 'nosto_tagging/general/use_skus';

    /**
     * Path to store config for restore cart redirection
     */
    const XML_PATH_RESTORE_CART_LOCATION = 'nosto_tagging/general/restore_cart_location';

    /**
     * Path to store config for custom fields
     */
    const XML_PATH_USE_CUSTOM_FIELDS = 'nosto_tagging/general/use_custom_fields';

    /**
     * Path to store config for alternate images
     */
    const XML_PATH_USE_ALTERNATE_IMAGES = 'nosto_tagging/general/use_alternate_images';

    /**
     * Path to store config for send add to cart event to nosto
     */
    const XML_PATH_SEND_ADD_TO_CART_EVENT = 'nosto_tagging/general/send_add_to_cart_event';

    /**
     * Path to store config for using inventory level
     */
    const XML_PATH_USE_INVENTORY_LEVEL = 'nosto_tagging/general/use_inventory_level';

    /**
     * Path to store config for tagging low stock
     */
    const XML_PATH_USE_LOW_STOCK = 'nosto_tagging/general/use_low_stock';

    /**
     * @var boolean the path for setting for product urls
     */
    const XML_PATH_PRETTY_URL = 'nosto_tagging/pretty_url/in_use';

    /**
     * Path to store config for brand attribute
     */
    const XML_PATH_BRAND_ATTRIBUTE = 'nosto_tagging/brand_attribute/tag';

    /**
     * Path to store config for installed domain
     */
    const XML_PATH_STORE_FRONT_PAGE_URL = 'nosto_tagging/settings/front_page_url';

    /**
     * @var string Nosto customer reference attribute name
     */
    const NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME = 'nosto_customer_reference';

    /**
     * @var string Attribute name for restore cart hash
     */
    const NOSTO_TAGGING_RESTORE_CART_ATTRIBUTE = 'restore_cart_hash';

    /**
     * @var int The length of the restore cart attribute
     */
    const NOSTO_TAGGING_RESTORE_CART_ATTRIBUTE_LENGTH = 64;

    /**
     * @var string Nosto customer reference attribute name
     */
    const XML_PATH_EXCHANGE_RATE_CRON_FREQUENCY = 'nosto_tagging/scheduled_currency_exchange_rate_update/frequency';

    const TAG1 = 'tag1';
    const TAG2 = 'tag2';
    const TAG3 = 'tag3';

    /**
     * Path to store attribute map
     */
    const XML_PATH_ATTRIBUTE_MAP = 'nosto_tagging/attribute_map';

    /**
     * Path to store rating provider
     */
    const XML_PATH_RATING_PROVIDER = 'nosto_tagging/ratings_and_reviews/provider';

    /**
     * The release candidate version. Set to null for stable.
     */
    const NOSTO_RC_VERSION = null;

    /**
     * List of strings to remove from the default Nosto account title
     *
     * @var array
     */
    public static $removeFromTitle = array(
        'Main Website - '
    );

    /**
     * List of valid tag types
     *
     * @var array
     */
    public static $validTags = array(self::TAG1, self::TAG2, self::TAG3);

    /**
     * Escape quotes inside html attributes.
     *
     * @param string $data the data to be escaped
     * @param bool $addSlashes false for escaping js that inside html attribute
     * @return string the escaped data
     */
    public function quoteEscape($data, $addSlashes = false)
    {
        if ($addSlashes === true) {
            $data = addslashes($data); //@codingStandardsIgnoreLine
        }

        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Builds a tagging string of the given category including all its parent
     * categories.
     * The categories are sorted by their position in the category tree path.
     *
     * @param Mage_Catalog_Model_Category $category the category model.
     *
     * @return string
     */
    public function buildCategoryString($category)
    {
        $data = array();

        if ($category instanceof Mage_Catalog_Model_Category) {
            /** @var $categories Mage_Catalog_Model_Category[] */
            $categories = $category->getParentCategories();
            $path = $category->getPathInStore();
            $ids = array_reverse(explode(',', $path));
            $data = array();
            foreach ($ids as $id) {
                if (isset($categories[$id]) && $categories[$id]->getName()) {
                    $data[] = $categories[$id]->getName();
                }
            }
        }

        if (!empty($data)) {
            return DS . implode(DS, $data);
        } else {
            return '';
        }
    }

    /**
     * Returns a unique ID that identifies this Magento installation.
     * This ID is sent to the Nosto account config iframe and used to link all
     * Nosto accounts used on this installation.
     *
     * @return string the ID.
     */
    public function getInstallationId()
    {
        $installationId = Mage::getStoreConfig(self::XML_PATH_INSTALLATION_ID);
        if (empty($installationId)) {
            // Running bin2hex() will make the ID string length 64 characters.
            /** @var Mage_Core_Helper_Data $dataHelper */
            $dataHelper = Mage::helper('core');
            $installationId = $dataHelper->getRandomString($length = 64);
            /** @var Mage_Core_Model_Config $config */
            $config = Mage::getModel('core/config');
            $config->saveConfig(
                self::XML_PATH_INSTALLATION_ID, $installationId, 'default', 0
            );

            /** @var Nosto_Tagging_Helper_Cache $helper */
            $helper = Mage::helper('nosto_tagging/cache');
            $helper->flushConfigCache();
        }

        return $installationId;
    }

    /**
     * Return the product image version to include in product tagging.
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     *
     * @return string
     */
    public function getProductImageVersion($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_IMAGE_VERSION, $store);
    }

    /**
     * Return if virtual hosts / pretty urls should be used for products
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     *
     * @return boolean
     */
    public function getUsePrettyProductUrls($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_PRETTY_URL, $store);
    }

    /**
     * Returns the attribute to be used for brand tagging
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     *
     * @return string
     */
    public function getBrandAttribute($store = null)
    {
        return Mage::getStoreConfig(
            self::XML_PATH_BRAND_ATTRIBUTE, $store
        );
    }

    /**
     * Returns the mapped attribute
     *
     * @param string $attribute
     * @param Mage_Core_Model_Store|null $store the store model or null.
     * @return string
     */
    public function getMappedAttribute($attribute, $store = null)
    {
        $xmlPath = self::XML_PATH_ATTRIBUTE_MAP . "/" . $attribute;
        return Mage::getStoreConfig($xmlPath, $store);
    }

    /**
     * Returns the front page URL used for the store during Nosto account
     * installation
     *
     * @param Mage_Core_Model_Store $store the store model
     *
     * @return string
     */
    public function getStoreFrontPageUrl(Mage_Core_Model_Store $store)
    {
        return Mage::getStoreConfig(self::XML_PATH_STORE_FRONT_PAGE_URL, $store);
    }

    /**
     * Saves current front page url of the store into the settings. This is
     * used for validating that Nosto account was actually installed into the
     * current store and Magento installation.
     *
     * @param Mage_Core_Model_Store $store
     */
    public function saveCurrentStoreFrontPageUrl(Mage_Core_Model_Store $store)
    {
        /* @var $urlHelper Nosto_Tagging_Helper_Url */
        $urlHelper = Mage::helper('nosto_tagging/url');
        $frontPageUrl = $urlHelper->getFrontPageUrl($store);
        $this->saveStoreFrontPageUrl($store, $frontPageUrl);
    }

    /**
     * Saves the front page url of the store into the settings.
     *
     * @param Mage_Core_Model_Store $store
     * @param string $url
     */
    public function saveStoreFrontPageUrl(Mage_Core_Model_Store $store, $url)
    {
        /** @var Mage_Core_Model_Config $config */
        $config = Mage::getModel('core/config');
        $config->saveConfig(
            self::XML_PATH_STORE_FRONT_PAGE_URL,
            $url,
            'stores',
            $store->getId()
        );
    }

    /**
     * Return the Nosto cookie value
     *
     * @return string
     */
    public function getCookieId()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return Mage::getModel('core/cookie')->get(self::COOKIE_NAME);
    }

    /**
     * Return the checksum for visitor
     *
     * @return string|null
     */
    public function getVisitorChecksum()
    {
        $coo = $this->getCookieId();
        if ($coo) {
            return hash(self::VISITOR_HASH_ALGO, $coo);
        }

        return null;
    }

    /**
     * Return the checksum for visitor
     *
     * @param string $name the title of the account
     * @return string the cleaned title of the account
     */
    public function cleanUpAccountTitle($name)
    {
        $clean = str_replace(self::$removeFromTitle, '', $name);
        return $clean;
    }

    /**
     * Return the multi currency method in use, i.e. "exchangeRate" or
     * "priceVariation".
     *
     * If "exchangeRate", it means that the product prices in the recommendation
     * is updated through the Exchange Rate API to Nosto.
     *
     * If "priceVariation", it means that the product price variations should be
     * tagged along side the product.
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     *
     * @return string
     */
    public function getMultiCurrencyMethod($store = null)
    {
        if ($store instanceof Mage_Core_Model_Store === false) {
            $store = $this->getStore();
        }

        return Mage::getStoreConfig(self::XML_PATH_MULTI_CURRENCY_METHOD, $store);
    }

    /**
     * Checks if either exchange rates or price variants
     * are used in store.
     *
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     *
     * @return bool
     */
    public function multiCurrencyDisabled($store = null)
    {
        $method = $this->getMultiCurrencyMethod($store);
        return ($method === self::MULTI_CURRENCY_DISABLED);
    }

    /**
     * Check if price variations are enabled
     *
     * @param null $store
     * @return bool
     */
    public function isVariationEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_VARIATION_SWITCH, $store);
    }

    /**
     * Checks if the multi currency method in use is the "exchangeRate", i.e.
     * the product prices in the recommendation is updated through the Exchange
     * Rate API to Nosto.
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     *
     * @return bool
     */
    public function isMultiCurrencyMethodExchangeRate($store = null)
    {
        $method = $this->getMultiCurrencyMethod($store);
        return ($method === self::MULTI_CURRENCY_METHOD_EXCHANGE_RATE);
    }

    /**
     * Returns if the scheduled currency exchange rate update is enabled.
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     *
     * @return bool
     */
    public function isScheduledCurrencyExchangeRateUpdateEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_SCHEDULED_CURRENCY_EXCHANGE_RATE_UPDATE_ENABLED, $store);
    }

    /**
     * Returns on/off setting for product API
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     * @return boolean
     */
    public function getUseProductApi($store = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_USE_PRODUCT_API, $store);
    }

    /**
     * Returns on/off setting for sending customer data to Nosto
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     * @return bool
     */
    public function getSendCustomerData($store = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_SEND_CUSTOMER_DATA, $store);
    }

    /**
     * Returns on/off setting for SKUs
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     * @return boolean
     */
    public function getUseSkus($store = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_USE_SKUS, $store);
    }

    /**
     * Returns on/off setting for tagging product's date published
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     * @return boolean
     */
    public function getTagDatePublished($store = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_TAG_DATE_PUBLISHED, $store);
    }

    /**
     * Returns on/off setting for custom fields
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     * @return boolean
     */
    public function getUseCustomFields($store = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_USE_CUSTOM_FIELDS, $store);
    }

    /**
     * Returns on/off setting for alternate image urls
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     * @return boolean
     */
    public function getUseAlternateImages($store = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_USE_ALTERNATE_IMAGES, $store);
    }

    /**
     * Returns on/off setting for inventory level
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     * @return boolean
     */
    public function getUseInventoryLevel($store = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_USE_INVENTORY_LEVEL, $store);
    }

    /**
     * Returns on/off setting for sending inventory level after purchase
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     * @return boolean
     */
    public function getSendInventoryLevelAfterPurchase($store = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_SEND_INVENTORY_LEVEL_AFTER_PURCHASE, $store);
    }

    /**
     * Returns on/off setting for using low stock
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     * @return boolean
     */
    public function getUseLowStock($store = null)
    {
        return (bool)Mage::getStoreConfig(self::XML_PATH_USE_LOW_STOCK, $store);
    }

    /**
     * Returns is the sending add to cart event to nosto enabled
     * This feature has been removed so it will return false
     *
     * @param Mage_Core_Model_Store $store
     * @return bool
     * @deprecated
     */
    public function getSendAddToCartEvent($store)
    {
        return false;
    }

    /**
     * Returns exchange rate cron frequency
     *
     * For possible return values
     * @see Nosto_Tagging_Model_System_Config_Source_Cron_Frequency
     *
     * @return string
     */
    public function getExchangeRateCronFrequency()
    {
        return Mage::getStoreConfig(self::XML_PATH_EXCHANGE_RATE_CRON_FREQUENCY);
    }

    /**
     * Return the attributes to be tagged in Nosto tags
     *
     * @param string $tagId the name / identifier of the tag (e.g. tag1, tag2).
     * @param mixed $store the store model or null.
     *
     * @throws Nosto_NostoException
     * @return array
     */
    public function getAttributesToTag($tagId, $store = null)
    {
        if (!in_array($tagId, self::$validTags)) {
            throw new Nosto_NostoException(sprintf('Invalid tag identifier %s', $tagId));
        }

        $tagPath = self::XML_PATH_CUSTOM_TAGS . $tagId;
        $tags = Mage::getStoreConfig($tagPath, $store);
        return explode(',', $tags);
    }

    /**
     * Return the ratings and reviews provider
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     *
     * @return string
     */
    public function getRatingsAndReviewsProvider($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_RATING_PROVIDER, $store);
    }

    /**
     * Return the restore cart redirect location
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     *
     * @return string
     */
    public function getRestoreCartRedirectLocation($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_RESTORE_CART_LOCATION, $store);
    }

    /**
     * Set the ratings and reviews provider
     *
     * @param string $provider
     * @param Mage_Core_Model_Store|null $store the store model or null.
     */
    public function setRatingsAndReviewsProvider($provider, $store = null)
    {
        if ($store === null) {
            $store = $this->getStore();
        }

        /** @var Mage_Core_Model_Config $config */
        $config = Mage::getModel('core/config');
        $config->saveConfig(
            self::XML_PATH_RATING_PROVIDER,
            $provider,
            'stores',
            $store->getId()
        );
    }

    /**
     * Returns all store views for the installation
     *
     * @return Mage_Core_Model_Store[]
     */
    public function getAllStoreViews()
    {
        $response = array();
        /** @var Mage_Core_Model_Website $website */
        foreach (Mage::app()->getWebsites() as $website) {
            /** @var Mage_Core_Model_Store_Group $group */
            foreach ($website->getGroups() as $group) {
                /** @noinspection PhpUndefinedMethodInspection */
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $response[] = $store;
                }
            }
        }

        return $response;
    }

    /**
     * Returns an array of store config in each store view
     *
     * @param $path
     * @return array
     */
    public function getConfigInAllStores($path)
    {
        $stores = $this->getAllStoreViews();
        $values = array();
        /* @var Mage_Core_Model_Store $store */
        foreach ($stores as $store) {
            /** @noinspection PhpUndefinedMethodInspection */
            $storeId = $store->getStoreId();
            if ($storeId) {
                $values[$storeId] = Mage::getStoreConfig($path, $store);
            }
        }

        return $values;
    }

    /**
     * Returns the version of Nosto extension
     *
     * @return string
     * @suppress PhanTypeMismatchArgumentInternalProbablyReal
     */
    public function getExtensionVersion()
    {
        $version = (string)Mage::getConfig()->getNode('modules/Nosto_Tagging/version');
        if (self::NOSTO_RC_VERSION) {
            $version .= sprintf('-RC%d', self::NOSTO_RC_VERSION);
        }
        return $version;
    }

    /**
     * Wrapper to return the current store
     *
     * @param null|string|bool|int|Mage_Core_Model_Store $id
     * @return Mage_Core_Model_Store|null
     */
    public function getStore($id = null)
    {
        try {
            return Mage::app()->getStore($id);
        } catch (Mage_Core_Model_Store_Exception $e) {
            NostoLog::exception($e);
        }

        return null;
    }

}

