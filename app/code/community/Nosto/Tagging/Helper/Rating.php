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
    /**
     * The ratings and reviews provider name for Yotpo
     */
    const RATING_PROVIDER_YOTPO = 'yotpo';
    /**
     * The ratings and reviews provider name for Yotpo
     */
    const RATING_PROVIDER_MAGENTO = 'magento';

    /**
     * A list of out-of-the-box supported ratings and reviews providers
     * Note that these modules need to be enabled also
     * @var array
     */
    private static $ratingProviders = array(
        'yotpo' => array(
            'description' => 'Use Yotpo for ratings and reviews',
            'image_url' => '',
            'module' => 'Yotpo_Yotpo'
        ),
        'magento' => array(
            'description' => 'Use Magento\'s native ratings and reviews',
            'image_url' => '',
            'module' => 'Magento_Rating'
        ),
    );

    /**
     * Returns and array of supported rating providers
     *
     * @return array
     */
    public function getSupportedRatingProviders()
    {
        return self::$ratingProviders;
    }

    /**
     * Returns installed and supported ratings and reviews providers
     *
     * @return array
     */
    public function getActiveRatingProviders()
    {
        $installed = array();
        foreach ($this->getSupportedRatingProviders() as $provider=>$config) {
            if ($provider === self::RATING_PROVIDER_MAGENTO
                || Mage::helper('core')->isModuleEnabled($config['module'])
            ) {
                $installed[$provider] = $config;
            }
        }

        return $installed;
    }

    /**
     * Returns the module name for the given provider
     *
     * @param $provider
     * @return null
     */
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

    /**
     * Tries to load class for handling the ratings and reviews for the
     * given provider
     *
     * @param $provider
     * @return null
     */
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
