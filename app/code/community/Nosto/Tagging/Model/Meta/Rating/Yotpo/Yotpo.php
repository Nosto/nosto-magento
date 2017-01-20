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
 * Meta data class which holds information about an order.
 * This is used during the order confirmation API request and the order
 * history export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Rating_Yotpo_Yotpo extends Nosto_Tagging_Model_Meta_Rating
{
    /**
     * Alternative API URL where to fetch reviews
     *
     * @var string
     */
    private static $alternateApi = 'https://api.yotpo.com/products/%s/%s/bottomline?callback={pjson_callback}';

    /**
     * Keeps track if the product in Mage registry is changed
     *
     * @var Mage_Catalog_Model_Product|null
     */
    private $originalRegistryProduct;

    /**
     * The name of the registry entry for product
     */
    const REGISTRY_PRODUCT = 'product';

    /**
     * @inheritdoc
     */
    public function init(
        Mage_Catalog_Model_Product $product,
        Mage_Core_Model_Store $store
    ) {
        try {
            $this->setRegistryProduct($product);
            /* @var Yotpo_Yotpo_Helper_RichSnippets $yotpoHelper */
            $yotpoHelper = Mage::helper('yotpo/RichSnippets');
            if ($yotpoHelper instanceof Yotpo_Yotpo_Helper_RichSnippets) {
                $updateSnippet = false;
                $values = $yotpoHelper->getRichSnippet();
                if (!$values) {
                    $values = $this->getFromBottomLine($product);
                    $updateSnippet = true;
                }

                if (
                    is_array($values)
                    && !empty($values['average_score'])
                    && !empty($values['reviews_count'])
                ) {
                    $this->setRating($values['average_score']);
                    $this->setReviewCount($values['reviews_count']);
                    if ($updateSnippet) {
                        $this->updateRichSnippet($product, $store);
                    }
                }
            }
        } catch (Exception $e) {
            Mage::log(
                sprintf(
                    'Could not find Yotpo helper. Error was: %s',
                    $e->getMessage()
                ),
                Zend_Log::ERR,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );
        }
        $this->resetRegistryProduct();
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array|null
     */
    private function getFromBottomLine(Mage_Catalog_Model_Product $product)
    {
        $rating = null;
        $url = sprintf(
            self::$alternateApi,
            $this->getYotpoAppKey(),
            $product->getId()
        );

        $data = file_get_contents($url);
        $json = json_decode(trim(trim(trim(trim($data, '{pjson_callback}'), '('), ';'),')'));

        if (isset($json->response) && isset($json->response->bottomline)) {
            $rating = array();
            if(!empty($json->response->bottomline->average_score)) {
                $rating["average_score"] = $json->response->bottomline->average_score;
            }
            if(!empty($json->response->bottomline->total_reviews)) {
                $rating["reviews_count"] = $json->response->bottomline->total_reviews;
            }
        }

        return $rating;
    }

    /**
     * Gets merchant's API key for Yotpo
     *
     * @return string
     */
    private function getYotpoAppKey()
    {
        return trim(Mage::getStoreConfig('yotpo/yotpo_general_group/yotpo_appkey',Mage::app()->getStore()));
    }

    /**
     * Sets product to Mage registry
     *
     * @param Mage_Catalog_Model_Product $product
     */
    private function setRegistryProduct(Mage_Catalog_Model_Product $product)
    {
        $this->originalRegistryProduct = Mage::registry(self::REGISTRY_PRODUCT);
        Mage::unregister(self::REGISTRY_PRODUCT);
        Mage::register(self::REGISTRY_PRODUCT, $product);
    }

    /**
     * Resets the product to Mage registry
     */
    private function resetRegistryProduct()
    {
        Mage::unregister(self::REGISTRY_PRODUCT);
        Mage::register(self::REGISTRY_PRODUCT, $this->originalRegistryProduct);
    }

    /**
     * Updates the Yotpo rich snippet
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store $store
     */
    private function updateRichSnippet(
        Mage_Catalog_Model_Product $product,
        Mage_Core_Model_Store $store
    ) {
        /* @var Yotpo_Yotpo_Model_Richsnippet $richSnippet */
        $richSnippet = Mage::getModel('yotpo/richsnippet');
        $snippet = $richSnippet->getSnippetByProductIdAndStoreId(
            $product->getId(),
            $store->getId()
        );

        if ($snippet == null) {
            $snippet = Mage::getModel('yotpo/richsnippet');
            $snippet->setProductId($product->getId());
            $snippet->setStoreId($store->getId());
        }
        $expiration = date('Y-m-d H:i:s', time() + 60*60*24);
        $snippet->setAverageScore($this->getRating());
        $snippet->setReviewsCount($this->getReviewCount());
        $snippet->setExpirationTime($expiration);
        $snippet->save();
    }
}
