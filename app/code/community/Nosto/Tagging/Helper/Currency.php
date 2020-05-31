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
 * Helper class for common currency operations.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Currency extends Mage_Core_Helper_Abstract
{
    /* List of zero decimal currencies in compliance with ISO-4217 */
    protected $_zeroDecimalCurrencies = array(
        'XOF',
        'BIF',
        'XAF',
        'CLP',
        'KMF',
        'DJF',
        'GNF',
        'ISK',
        'JPY',
        'KRW',
        'PYG',
        'RWF',
        'UGX',
        'UYI',
        'VUV',
        'VND',
        'XPF'
    );

    /**
     * Parses the format for a currency into a Nosto currency object.
     *
     * @param string $locale the locale to get the currency format in.
     * @param string $currencyCode the currency ISO 4217 code to get the currency in.
     * @return Nosto_Object_Format the parsed currency.
     * @throws Zend_Currency_Exception
     * @throws Zend_Locale_Exception
     */
    public function getCurrencyObject($locale, $currencyCode)
    {
        $currency = new Zend_Currency($locale, $currencyCode);
        $symbols = Zend_Locale_Data::getList($locale, 'symbols');
        $format = $this->getCleanFormatFromLocale($locale);
        $precision = $this->getPrecision($format, $currencyCode);

        $currencySymbol = $currency->getSymbol();
        if ($currencySymbol === null) {
            // If the symbol is missing for the current locale, use the ISO code.
            $currencySymbol = $currencyCode;
        }

        return new Nosto_Object_Format(
            $this->isSymbolBeforeAmount($format),
            $currencySymbol,
            $symbols['decimal'],
            $symbols['group'],
            $precision
        );
    }

    /**
     * Returns currency format from locale without currency symbol and
     * and any other characters than ["0", "#", ".", ","]
     *
     * @param $locale
     * @return null|string|string[]
     * @throws Zend_Locale_Exception
     */
    protected function getCleanFormatFromLocale($locale)
    {
        $format = $this->buildFormatFromLocale($locale);
        return $this->clearCurrencyFormat($format);
    }

    /**
     * Check if the currency symbol is before or after the amount.
     * Returns true is symbol is before the amount.
     *
     * @param $format
     * @return bool
     */
    protected function isSymbolBeforeAmount($format)
    {
        return strpos(trim($format), '¤') === 0;
    }

    /**
     * Returns the complete currency format including the symbol for the given locale.
     *
     * @param $locale
     * @return bool|string
     * @throws Zend_Locale_Exception
     */
    protected function buildFormatFromLocale($locale)
    {
        $format = Zend_Locale_Data::getContent($locale, 'currencynumber');
        // Remove extra part, e.g. "¤ #,##0.00; (¤ #,##0.00)" => "¤ #,##0.00".
        if (($pos = strpos($format, ';')) !== false) {
            $format = substr($format, 0, $pos);
        }

        return $format;
    }

    /**
     * Remove all other characters than "0", "#", "." and ",",
     *
     * @param $format
     * @return null|string|string[]
     */
    protected function clearCurrencyFormat($format)
    {
        return preg_replace('/[^0#.]/', '', $format);
    }

    /**
     * Calculates the amount of decimal digits for the given format and currency code.
     * If the currency code has no decimal part according to ISO-4217 returns 0.
     *
     * @param $format
     * @param $currencyCode
     * @return bool|int
     */
    protected function getPrecision($format, $currencyCode)
    {
        if (in_array($currencyCode, $this->_zeroDecimalCurrencies, false)) {
            return 0;
        }

        $precision = 0;
        if (($decimalPos = strpos($format, '.')) !== false) { // @codingStandardsIgnoreLine
            $precision = (strlen($format) - (strrpos($format, '.') + 1));
        } else {
            $decimalPos = strlen($format);
        }

        $decimalFormat = substr($format, $decimalPos);
        if (($pos = strpos($decimalFormat, '#')) !== false) {
            $precision = strlen($decimalFormat) - $pos - $precision;
        }

        return $precision;
    }
}
