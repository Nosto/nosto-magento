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
 * @copyright Copyright (c) 2013-2019 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Meta data class which holds information about the exchange rates of a store
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Collection_Rates extends Nosto_Object_ExchangeRateCollection
{
    /**
     * Loads the currencies and exchange rates from a store
     *
     * @param Mage_Core_Model_Store|null $store the store to get the exchange rates for.
     * @return bool
     */
    public function loadData(Mage_Core_Model_Store $store = null)
    {
        $currencyCodes = $store->getAvailableCurrencyCodes(true);
        $baseCurrencyCode = $store->getBaseCurrencyCode();

        /** @var Mage_Directory_Model_Currency $currency */
        $currency = Mage::getModel('directory/currency');
        $rates = $currency->getCurrencyRates($baseCurrencyCode, $currencyCodes);
        foreach ($rates as $code => $rate) {
            if ($baseCurrencyCode === $code) {
                continue; // Skip base currency.
            }
            parent::addRate($code, new Nosto_Object_ExchangeRate($code, $rate));
        }

        return true;
    }
}
