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

/**
 * Helper class for logging.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Log extends Mage_Core_Helper_Abstract
{
    /**
     * Nosto's custom log
     */
    const NOSTO_LOG_FILE = 'nostotagging.log';

    /**
     * Magento's default exception log
     */
    const MAGENTO_EXCEPTION_LOG_FILE = 'exception.log';

    /**
     * Writes a log message into a file
     *
     * @param string $message
     * @param int $level
     * @param string $log
     * @param array|null $attributes
     */
    protected static function write($message, $level, $log, array $attributes = null)
    {
        if (is_array($attributes) && !empty($attributes)) {
            $strippedAttributes = array();
            foreach ($attributes as $attribute) {
                if (is_scalar($attribute)) {
                    $strippedAttributes[] = $attribute;
                } elseif (is_array($attribute)) {
                    $strippedAttributes[] = implode(',', $attribute);
                }
            }

            $message = vsprintf($message, $strippedAttributes);
        }

        Mage::log(
            $message,
            $level,
            $log
        );
    }

    /**
     * Writes info into the log
     *
     * @param string $message
     * @param array|null $attributes
     */
    public static function info($message, array $attributes = null)
    {
        return self::write(
            $message,
            Zend_Log::DEBUG,
            self::NOSTO_LOG_FILE,
            $attributes
        );
    }

    /**
     * Writes warning into the log
     *
     * @param string $message
     * @param array|null $attributes
     */
    public static function warning($message, array $attributes = null)
    {
        return self::write(
            $message,
            Zend_Log::WARN,
            self::NOSTO_LOG_FILE,
            $attributes
        );
    }

    /**
     * Writes error into the log
     *
     * @param string $message
     * @param array|null $attributes
     */
    public static function error($message, array $attributes = null)
    {
        return self::write(
            $message,
            Zend_Log::ERR,
            self::MAGENTO_EXCEPTION_LOG_FILE,
            $attributes
        );
    }

    /**
     * Writes deperecated info into the log
     *
     * @param string $message
     * @param array|null $attributes
     */
    public static function deprecated($message, array $attributes = null)
    {
        return self::write(
            $message,
            Zend_Log::INFO,
            self::NOSTO_LOG_FILE,
            $attributes
        );
    }

    /**
     * Writes exception into the log
     *
     * @param Exception $exception
     */
    public static function exception(Exception $exception)
    {
        return self::write(
            'Got exception %s with message: %s, error code: %s)',
            Zend_Log::ERR,
            self::MAGENTO_EXCEPTION_LOG_FILE,
            array(
                get_class($exception),
                $exception->getMessage(),
                $exception->getCode()
            )
        );
    }
}
