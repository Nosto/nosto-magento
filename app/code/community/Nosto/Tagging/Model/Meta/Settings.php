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
 * Meta data class which holds information about a new Nosto account.
 * This is used during the Nosto account creation.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Settings extends Nosto_Object_Settings
{
    /**
     * Loads the meta data for the given store.
     *
     * @param Mage_Core_Model_Store $store the store view to load the data for.
     * @return bool
     */
    public function loadData(Mage_Core_Model_Store $store)
    {
        /* @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        /* @var Nosto_Tagging_Helper_Url $helperUrl */
        $helperUrl = Mage::helper('nosto_tagging/url');
        $this->setTitle(
            $helper->cleanUpAccountTitle(
                $store->getWebsite()->getName()
                . ' - '
                . $store->getGroup()->getName()
                . ' - '
                . $store->getName()
            )
        );
        $this->setFrontPageUrl($helperUrl->getFrontPageUrl($store));
        $this->setCurrencyCode($store->getBaseCurrencyCode());
        $this->setLanguageCode(substr($store->getConfig('general/locale/code'), 0, 2));
        $this->setUseCurrencyExchangeRates(!$helper->multiCurrencyDisabled($store));
        if (!$helper->multiCurrencyDisabled($store)) {
            $this->setDefaultVariantId($store->getBaseCurrencyCode());
        } elseif ($helper->isVariationEnabled($store)) {
            /* @var Nosto_Tagging_Helper_Variation $variationHelper  */
            $variationHelper = Mage::helper('nosto_tagging/variation');
            $this->setDefaultVariantId($variationHelper->getDefaultVariationId());
        } else {
            $this->setDefaultVariantId("");
        }

        $storeLocale = $store->getConfig('general/locale/code');
        $currencyCodes = $store->getAvailableCurrencyCodes(true);
        if (is_array($currencyCodes) && !empty($currencyCodes)) {
            /** @var Nosto_Tagging_Helper_Currency $currencyHelper */
            $currencyHelper = Mage::helper('nosto_tagging/currency');
            foreach ($currencyCodes as $currencyCode) {
                try {
                    $this->addCurrency(
                        $currencyCode, $currencyHelper->getCurrencyObject($storeLocale, $currencyCode)
                    );
                } catch (\Exception $e) {
                    NostoLog::exception($e);
                    return false;
                }
            }
        }

        return true;
    }
}
