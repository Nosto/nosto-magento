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
     * Path to store config scheduled currency exchange rate update enabled setting.
     */
    const XML_PATH_SCHEDULED_CURRENCY_EXCHANGE_RATE_UPDATE_ENABLED = 'nosto_tagging/scheduled_currency_exchange_rate_update/enabled';

    /**
     * Multi currency method option for currency exchange rates.
     */
    const MULTI_CURRENCY_METHOD_EXCHANGE_RATE = 'exchangeRate';

    /**
     * Multi currency method option for price variations in tagging.
     */
    const MULTI_CURRENCY_METHOD_PRICE_VARIATION = 'priceVariation';

    /**
     * No multi currency
     */
    const MULTI_CURRENCY_DISABLED = 'disabled';

    /**
     * Path to store config for using the product API or not.
     */
    const XML_PATH_USE_PRODUCT_API = 'nosto_tagging/general/use_product_api';

    /*
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

    /*
     * @var int the product attribute type id
     */
    const PRODUCT_TYPE_ATTRIBUTE_ID = 4;

    /*
     * @var string Nosto customer reference attribute name
     */
    const NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME = 'nosto_customer_reference';

    /*
     * @var string Nosto customer reference attribute name
     */
    const XML_PATH_EXCHANGE_RATE_CRON_FREQUENCY = 'nosto_tagging/scheduled_currency_exchange_rate_update/frequency';

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
    public static $validTags = array(
        'tag1',
        'tag2',
        'tag3'
    );

    /**
     * List of attributes that cannot be added to tags due to data type and
     * Magento's internal processing of attributes
     *
     * @var array
     */
    public static $notValidAttributesForTags = array(
        'group_price',
        'tier_price',
        'media_gallery',
    );

    /**
     * @inheritdoc
     */
    public function quoteEscape($data, $addSlashes = false)
    {
        if ($addSlashes === true) {
            $data = addslashes($data); //@codingStandardsIgnoreLine
        }
        return htmlspecialchars($data, ENT_QUOTES, null, false);
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
            foreach ($ids as $id) {
                if (isset($categories[$id]) && $categories[$id]->getName()) {
                    $data[] = $categories[$id]->getName();
                } else {
                    $data = array();
                    break;
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
            $installationId = bin2hex(NostoCryptRandom::getRandomString(32));
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
        return Mage::getStoreConfig(self::XML_PATH_BRAND_ATTRIBUTE, $store);
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
     * @return string
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
            $store = Mage::app()->getStore();
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
     * Returns product updates should be sent via API to Nosto
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     * @return boolean
     */
    public function getUseProductApi($store = null)
    {
        $useApi = (bool)Mage::getStoreConfig(self::XML_PATH_USE_PRODUCT_API, $store);
        return $useApi;
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
     * Returns the product attributes that can be used in Nosto tags
     *
     * @return array  ['value' => $code, 'label' => $label]
     */
    public function getProductAttributeOptions()
    {
        $resourceModel = Mage::getResourceModel(
            'catalog/product_attribute_collection'
        );
        $attributes = $resourceModel
            ->addFieldToFilter(
                'entity_type_id',
                self::PRODUCT_TYPE_ATTRIBUTE_ID
            )
            ->setOrder(
                'attribute_code',
                Varien_Data_Collection::SORT_ORDER_ASC
            );
        // Add single empty option as a first option. Otherwise multiselect
        // cannot not be unset in Magento.
        $attributeArray = array(
            array(
                'value' => 0,
                'label' => 'None'
            )
        );
        foreach($attributes as $attribute) {
            $code = $attribute->getData('attribute_code');
            if (in_array($code, self::$notValidAttributesForTags)) {
                continue;
            }
            $label = $attribute->getData('frontend_label');
            $attributeArray[] = array(
                'value' => $code,
                'label' => sprintf('%s (%s)', $code, $label)
            );
        }

        return $attributeArray;
    }

    /**
     * Return the attributes to be tagged in Nosto tags
     *
     * @param string $tag_id the name / identifier of the tag (e.g. tag1, tag2).
     * @param Mage_Core_Model_Store|null $store the store model or null.
     *
     * @throws NostoException
     *
     * @return array
     */
    public function getAttributesToTag($tag_id, $store = null)
    {
        if (!in_array($tag_id, self::$validTags)) {
            throw new NostoException(
                sprintf('Invalid tag identifier %s', $tag_id)
            );
        }
        $tag_path = self::XML_PATH_CUSTOM_TAGS . $tag_id;
        $tags = Mage::getStoreConfig($tag_path, $store);
        return explode(',', $tags);
    }
}
