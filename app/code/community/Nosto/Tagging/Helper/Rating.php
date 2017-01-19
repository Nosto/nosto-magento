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
 * Helper class for common rating and review operations.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Rating extends Mage_Core_Helper_Abstract
{
    const RATING_PROVIDER_YOTPO = 'yotpo';
    const RATING_PROVIDER_MAGENTO = 'magento';

    private static $ratingProviders = array(
        'yotpo' => array(
            'description' => 'Use Yotpo for ratings and reviews',
            'image_url' => 'https://www.yotpo.com/wp-content/uploads/2015/11/Yotpo-Logo.png',
            'module' => 'Yotpo_Yotpo'
        ),
        'magento' => array(
            'description' => 'Use Magento\'s native ratings and reviews',
            'image_url' => '',
            'module' => 'Magento_Rating'
        ),
    );

    public function getRatingProviders()
    {
        return self::$ratingProviders;
    }

    public function getModuleNameByProvider($provider)
    {
        $module = null;
        if (
            !empty(self::$ratingProviders[$provider])
            && !empty(self::$ratingProviders[$provider]['module'])
        ) {
            $module = self::$ratingProviders[$provider]['module'];
        }
        return $module;
    }

    public function loadClass($provider)
    {
        $module = null;
        if (
            !empty(self::$ratingProviders[$provider])
            && !empty(self::$ratingProviders[$provider]['module'])
        ) {
            $module = self::$ratingProviders[$provider]['module'];
        }
        return $module;
    }
}
