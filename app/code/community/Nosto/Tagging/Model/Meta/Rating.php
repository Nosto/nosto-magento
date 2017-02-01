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
 * A class to load the ratings.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
abstract class Nosto_Tagging_Model_Meta_Rating
    implements Nosto_Tagging_Model_Meta_Rating_Interface
{
    /**
     * @var int
     */
    protected $reviewCount;

    /**
     * @var float
     */
    protected $rating;

    /**
     * @inheritdoc
     */
    public function getReviewCount()
    {
        return $this->reviewCount;
    }

    /**
     * @inheritdoc
     */
    public function setReviewCount($reviewCount)
    {
        $this->reviewCount = (int)$reviewCount;
    }

    /**
     * @inheritdoc
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @inheritdoc
     */
    public function setRating($rating)
    {
        $this->rating = (float)$rating;
    }

    /**
     * @inheritdoc
     */
    abstract public function init(
        Mage_Catalog_Model_Product $product,
        Mage_Core_Model_Store $store
    );
}
