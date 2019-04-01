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

use Nosto_Tagging_Helper_Rating as RatingHelper;

/**
 * Extension system setting source model for choosing which attributes should
 * be added to tags
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 * @suppress PhanUnreferencedClass
 */
class Nosto_Tagging_Model_System_Config_Source_Ratings_Provider
{
    /**
     * Returns all available product attributes
     *
     * @return array the options.
     */
    public function toOptionArray()
    {
        /* @var Nosto_Tagging_Helper_Rating $nostoHelperRatings */
        $nostoHelperRatings = Mage::helper('nosto_tagging/rating');
        $ratingProviders = $nostoHelperRatings->getActiveRatingProviders();
        $options = array(
            array(
                'value' => 0,
                'label' => 'Not in use'
            )
        );
        foreach ($ratingProviders as $key => $ratingProvider) {
            $option = array(
                'value' => $key,
                'label' => $ratingProvider[RatingHelper::FIELD_DESCRIPTION]
            );
            $options[] = $option;
        }

        return $options;
    }
}
