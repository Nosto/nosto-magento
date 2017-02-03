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
 * Meta data class which holds information about an order.
 * This is used during the order confirmation API request and the order
 * history export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Rating_Mage_Rating extends Nosto_Tagging_Model_Meta_Rating
{
    /**
     * @inheritdoc
     */
    public function init(
        Mage_Catalog_Model_Product $product,
        Mage_Core_Model_Store $store
    )
    {
        /* @var Mage_Rating_Model_Rating $review_summary */
        /** @noinspection PhpUndefinedMethodInspection */
        $ratingSummary = Mage::getModel('review/review_summary')
            ->setStoreId($store->getId())
            ->load($product->getId());
        if (
            $ratingSummary instanceof Mage_Review_Model_Review_Summary
            && $ratingSummary->getRatingSummary()
        ) {
            $this->setRating(
                number_format(
                    round(
                        $ratingSummary->getRatingSummary()/20,
                        1
                    ),
                    1
                )
            );
            $this->setReviewCount($ratingSummary->getReviewsCount());
        }
    }
}
