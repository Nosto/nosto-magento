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
 * Helper class for working with the cache
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Cache extends Mage_Core_Helper_Abstract
{
    /**
     * Flushes the Magento caches, not all of them but some of them, normally after creating an
     * account or connecting with nosto.
     */
    public function flushCache()
    {
        Mage::app()->getCacheInstance()->cleanType('config');
        Mage::app()->getCacheInstance()->cleanType('layout');
        Mage::app()->getCacheInstance()->cleanType('block_html');
    }

    /**
     * Flushes the Magento caches, specifically the configuration cache
     */
    public function flushConfigCache()
    {
        Mage::app()->getCacheInstance()->cleanType('config');
    }
}
