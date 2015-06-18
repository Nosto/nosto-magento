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
 * @copyright Copyright (c) 2013-2015 Nosto Solutions Ltd (http://www.nosto.com)
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
     * Path to store config product image version setting.
     */
    const XML_PATH_IMAGE_VERSION = 'nosto_tagging/general/image_version';

    /**
     * Path to store config multi currency method setting.
     */
    const XML_PATH_MULTI_CURRENCY_METHOD = 'nosto_tagging/general/multi_currency_method';

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
            Mage::app()->getCacheInstance()->cleanType('config');
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
        return Mage::getStoreConfig(self::XML_PATH_MULTI_CURRENCY_METHOD, $store);
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
     * Checks if the multi currency method in use is the "priceVariation", i.e.
     * the product price variations should be tagged along side the product.
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     *
     * @return bool
     */
    public function isMultiCurrencyMethodPriceVariation($store = null)
    {
        $method = $this->getMultiCurrencyMethod($store);
        return ($method === self::MULTI_CURRENCY_METHOD_PRICE_VARIATION);
    }

    /**
     * Checks if the store has any other currency configured than the base one.
     *
     * @param Mage_Core_Model_Store|null $store the store model or null.
     *
     * @return bool
     */
    public function getStoreHasMultiCurrency($store = null)
    {
        if (is_null($store)) {
            $store = Mage::app()->getStore();
        }
        $currencyCodes = $store->getAvailableCurrencyCodes(true);
        $baseCurrencyCode = $store->getBaseCurrencyCode();
        foreach ($currencyCodes as $currencyCode) {
            if ($currencyCode !== $baseCurrencyCode) {
                return true;
            }
        }
        return false;
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
}
