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
    const RATING_PROVIDER_YOTPO = 'Yotpo_Yotpo';
    /**
     * The ratings and reviews provider name for Yotpo
     */
    const RATING_PROVIDER_MAGENTO = 'Mage_Rating';

    /**
     * Array key for provider description
     */
    const FIELD_DESCRIPTION = 'description';

    /**
     * Array key for provider module name
     */
    const FIELD_MODULE = 'module';

    /**
     * Array key for provider module name
     */
    const FIELD_CONFIG_PATH = 'config_path';

    /**
     * A list of out-of-the-box supported ratings and reviews providers
     * Note that these modules need to be enabled also
     * @var array
     */
    protected static $ratingProviders = array(
        self::RATING_PROVIDER_YOTPO => array(
            self::FIELD_DESCRIPTION => 'Yotpo Product Reviews',
            self::FIELD_MODULE => 'Yotpo_Yotpo'

        ),
        self::RATING_PROVIDER_MAGENTO => array(
            self::FIELD_DESCRIPTION => 'Magento\'s native product reviews',
            self::FIELD_MODULE => 'Mage_Rating'
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
            if (
                Mage::helper('core')->isModuleEnabled($config[self::FIELD_MODULE])
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
            && !empty(self::$ratingProviders[$provider][self::FIELD_MODULE])
        ) {
            $module = self::$ratingProviders[$provider][self::FIELD_MODULE];
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
            && !empty(self::$ratingProviders[$provider][self::FIELD_MODULE])
        ) {
            $module = self::$ratingProviders[$provider][self::FIELD_MODULE];
        }
        return $module;
    }
}
