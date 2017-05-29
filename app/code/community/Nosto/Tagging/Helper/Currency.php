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
 * Helper class for common currency operations.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Currency extends Mage_Core_Helper_Abstract
{
    /**
     * Parses the format for a currency into a Nosto currency object.
     *
     * @param string $locale the locale to get the currency format in.
     * @param string $currencyCode the currency ISO 4217 code to get the currency in.
     * @return Nosto_Object_Format the parsed currency.
     */
    public function getCurrencyObject($locale, $currencyCode)
    {
        $currency = new Zend_Currency($locale, $currencyCode);
        $format = Zend_Locale_Data::getContent($locale, 'currencynumber');
        $symbols = Zend_Locale_Data::getList($locale, 'symbols');

        // Remove extra part, e.g. "¤ #,##0.00; (¤ #,##0.00)" => "¤ #,##0.00".
        if (($pos = strpos($format, ';')) !== false) {
            $format = substr($format, 0, $pos);
        }
        // Check if the currency symbol is before or after the amount.
        $symbolPosition = strpos(trim($format), '¤') === 0;

        // Remove all other characters than "0", "#", "." and ",",
        $format = preg_replace('/[^0\#\.,]/', '', $format);
        // Calculate the decimal precision.
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

        // If the symbol is missing for the current locale, use the ISO code.
        $currencySymbol = $currency->getSymbol();
        if ($currencySymbol === null) {
            $currencySymbol = $currencyCode;
        }

        return new Nosto_Object_Format(
            $symbolPosition,
            $currencySymbol,
            $symbols['decimal'],
            $symbols['group'],
            $precision
        );
    }
}
