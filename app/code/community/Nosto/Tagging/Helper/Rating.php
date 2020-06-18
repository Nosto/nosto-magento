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
     * A list of out-of-the-box supported ratings and reviews providers
     * Note that these modules need to be enabled also
     * The first one will be set as nosto review and rating provider if it is installed and enabled,
     * or the next one will checked and so on
     * @var array
     */
    protected static $_ratingProviders = array(
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
        return self::$_ratingProviders;
    }

    /**
     * Returns installed and supported ratings and reviews providers
     *
     * @return array
     */
    public function getActiveRatingProviders()
    {
        $installed = array();
        foreach ($this->getSupportedRatingProviders() as $provider => $config) {
            /** @var Mage_Core_Helper_Data $coreHelper */
            $coreHelper = Mage::helper('core');
            if ($coreHelper->isModuleEnabled($config[self::FIELD_MODULE])
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
     * @return string|null
     */
    public function getModuleNameByProvider($provider)
    {
        $module = null;
        if (!empty(self::$_ratingProviders[$provider])
            && !empty(self::$_ratingProviders[$provider][self::FIELD_MODULE])
        ) {
            $module = self::$_ratingProviders[$provider][self::FIELD_MODULE];
        }

        return $module;
    }

    /**
     * Tries to load class for handling the ratings and reviews for the
     * given provider
     *
     * @param $provider
     * @return string|null
     */
    public function loadClass($provider)
    {
        $module = null;
        if (!empty(self::$_ratingProviders[$provider])
            && !empty(self::$_ratingProviders[$provider][self::FIELD_MODULE])
        ) {
            $module = self::$_ratingProviders[$provider][self::FIELD_MODULE];
        }

        return $module;
    }

    /**
     * Enable review and rating
     * @param Mage_Core_Model_Store $store scope
     */
    public function enableReviewAndRating(Mage_Core_Model_Store $store)
    {
        /** @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');
        $ratingProviders = $this->getActiveRatingProviders();
        //Get the first one from the array
        foreach ($ratingProviders as $key => $ratingProvider) {
            $dataHelper->setRatingsAndReviewsProvider($key, $store);
            return;
        }
    }
}
