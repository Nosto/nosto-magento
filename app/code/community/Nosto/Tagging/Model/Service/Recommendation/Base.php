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
 * Abstract base class for recommendation services
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
abstract class Nosto_Tagging_Model_Service_Recommendation_Base
{
    const DEFAULT_API_TIMEOUT = 0.5;

    /**
     * Returns the default API timeout for connecting to Nosto API
     * or a value from the ENV
     *
     * Return float
     */
    protected function getConnectTimeout()
    {
        return (float)Nosto_Nosto::getEnvVariable('NOSTO_RECOMMENDATION_API_TIMEOUT', self::DEFAULT_API_TIMEOUT);
    }

    /**
     * Returns the default API timeout for response or a value from the ENV
     *
     * Return float
     */
    protected function getResponseTimeout()
    {
        return (float)Nosto_Nosto::getEnvVariable('NOSTO_RECOMMENDATION_API_TIMEOUT', self::DEFAULT_API_TIMEOUT);
    }
}