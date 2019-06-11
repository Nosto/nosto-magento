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
 * Helper class for getting category related product data from Nosto
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Service_Recommendation_Category
    extends Nosto_Tagging_Model_Service_Recommendation_Base
{
    const NOSTO_PREVIEW_COOKIE = 'nostopreview';

    /**
     * Returns an array of product ids sorted by relevance
     *
     * @param Nosto_Object_Signup_Account $nostoAccount
     * @param string $nostoCustomerId
     * @param string $category
     *
     * @param string $type
     * @return array
     */
    public function getSortedProductIds(
        Nosto_Object_Signup_Account $nostoAccount,
        $nostoCustomerId,
        $category,
        $type
    )
    {
        $productIds = array();
        $featureAccess = new Nosto_Service_FeatureAccess($nostoAccount);
        if (!$featureAccess->canUseGraphql()) {
            return $productIds;
        }
        if ($type === Nosto_Tagging_Model_Category_Config::NOSTO_PERSONALIZED_KEY) {
            $recoOperation = new Nosto_Operation_Recommendation_CategoryBrowsingHistory(
                $nostoAccount,
                $nostoCustomerId
            );
        } else {
            $recoOperation = new Nosto_Operation_Recommendation_CategoryTopList(
                $nostoAccount,
                $nostoCustomerId
            );
        }
        $recoOperation->setCategory($category);

        $previewModeCookie = Mage::getModel('core/cookie')
            ->get(self::NOSTO_PREVIEW_COOKIE);
        if ($previewModeCookie !== null && $previewModeCookie === "true") {
            $recoOperation->setPreviewMode(true);
        }

        try {
            $result = $recoOperation->execute();
            foreach ($result as $item) {
                if ($item->getProductId() && is_numeric($item->getProductId())) {
                    $productIds[] = $item->getProductId();
                }
            }
        } catch (\Exception $e) {
            Nosto_Tagging_Helper_Log::exception($e);
        }
        return $productIds;
    }
}
