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
 * @copyright Copyright (c) 2013-2015 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Data Transfer object representing a product.
 * This is used during the order confirmation API request and the product
 * history export.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Product extends Nosto_Tagging_Model_Meta_Product_Abstract implements NostoProductInterface
{
    /**
     * Product "can be directly added to cart" tag string.
     */
    const PRODUCT_ADD_TO_CART = 'add-to-cart';

    /**
     * @var string|int the variation currently in use.
     */
    protected $_variationId;

    /**
     * @var Nosto_Tagging_Model_Meta_Product_Variation[] the product variations.
     */
    protected $_variations = array();

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('nosto_tagging/meta_product');
    }

    /**
     * Loads the Data Transfer object.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store|null $store the store to get the product data for.
     */
    public function loadData(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store = null)
    {
        if (is_null($store)) {
            $store = Mage::app()->getStore();
        }

        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        /** @var Nosto_Tagging_Helper_Price $priceHelper */
        $priceHelper = Mage::helper('nosto_tagging/price');

        $this->setUrl($this->buildUrl($product, $store));
        $this->setProductId($product->getId());
        $this->setName($product->getName());
        $this->setImageUrl($this->buildImageUrl($product, $store));
        $price = $priceHelper->getProductFinalPriceInclTax($product);
        $this->setPrice(new NostoPrice($price));
        $listPrice = $priceHelper->getProductPriceInclTax($product);
        $this->setListPrice(new NostoPrice($listPrice));
        $this->setCurrency(new NostoCurrencyCode($store->getBaseCurrencyCode()));
        $this->setAvailability(new NostoProductAvailability(
            $product->isAvailable()
                ? NostoProductAvailability::IN_STOCK
                : NostoProductAvailability::OUT_OF_STOCK
        ));
        $this->setCategories($this->buildCategories($product));

        // Optional properties.

        $descriptions = array();
        if ($product->hasData('short_description')) {
            $descriptions[] = $product->getData('short_description');
        }
        if ($product->hasData('description')) {
            $descriptions[] = $product->getData('description');
        }
        if (count($descriptions) > 0) {
            $this->setDescription(implode(' ', $descriptions));
        }

        if ($product->hasData('manufacturer')) {
            $this->setBrand($product->getAttributeText('manufacturer'));
        }
        if (($tags = $this->buildTags($product, $store)) !== array()) {
            $this->setTag1($tags);
        }

        if ($product->hasData('created_at')) {
            if (($timestamp = strtotime($product->getData('created_at')))) {
                $this->setDatePublished(new NostoDate($timestamp));
            }
        }

        if ($helper->getStoreHasMultiCurrency($store)) {
            $this->setVariationId($store->getBaseCurrencyCode());
            if ($helper->isMultiCurrencyMethodPriceVariation($store)) {
                $this->setVariations($this->buildVariations($product, $store));
            }
        }
    }

    /**
     * Build the product variations.
     *
     * These are the different prices for the product's supported currencies.
     * Only used when the multi currency method is set to 'priceVariation'.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store      $store the store model.
     *
     * @return array
     */
    protected function buildVariations(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        $variations = array();

        /** @var Nosto_Tagging_Helper_Price $priceHelper */
        $priceHelper = Mage::helper('nosto_tagging/price');
        $currencyCodes = $store->getAvailableCurrencyCodes(true);
        foreach ($currencyCodes as $currencyCode) {
            // Skip base currency.
            if ($currencyCode === $store->getBaseCurrencyCode()) {
                continue;
            }
            try {
                /** @var Nosto_Tagging_Model_Meta_Product_Variation $variation */
                $variation = Mage::getModel('nosto_tagging/meta_product_variation');

                $variation->setVariationId($currencyCode);
                $variation->setAvailability($this->_availability);
                $variation->setCurrency(new NostoCurrencyCode($currencyCode));

                $price = $priceHelper->getProductFinalPriceInclTax($product);
                $price = $store->getBaseCurrency()->convert($price, $currencyCode);
                $variation->setPrice(new NostoPrice($price));

                $listPrice = $priceHelper->getProductPriceInclTax($product);
                $listPrice = $store->getBaseCurrency()->convert($listPrice, $currencyCode);
                $variation->setListPrice(new NostoPrice($listPrice));

                $variations[] = $variation;
            } catch (Exception $e) {
                // The variation cannot be obtained if there are no
                // exchange rates defined for the currency and Magento will
                // throw and exception. Just ignore this and continue.
                continue;
            }
        }

        return $variations;
    }

    /**
     * Builds the "tag1" tags.
     *
     * These include any "tag/tag" model names linked to the product, as well
     * as a special "add-to-cart" tag if the product can be added to the
     * cart directly without any choices, i.e. it is a non-configurable simple
     * product.
     * This special tag can then be used in the store frontend to enable a
     * "add to cart" button in the product recommendations.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store      $store the store model.
     *
     * @return array
     */
    protected function buildTags(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        $tags = array();

        if (Mage::helper('core')->isModuleEnabled('Mage_Tag')) {
            $tagCollection = Mage::getModel('tag/tag')
                ->getCollection()
                ->addPopularity()
                ->addStatusFilter(Mage_Tag_Model_Tag::STATUS_APPROVED)
                ->addProductFilter($product->getId())
                ->setFlag('relation', true)
                ->addStoreFilter($store->getId())
                ->setActiveFilter();
            foreach ($tagCollection as $tag) {
                /** @var Mage_Tag_Model_Tag $tag */
                $tags[] = $tag->getName();
            }
        }

        if (!$product->canConfigure()) {
            $tags[] = self::PRODUCT_ADD_TO_CART;
        }

        return $tags;
    }

    /**
     * Builds the absolute store front url for the product page.
     *
     * The url includes the "___store" GET parameter in order for the Nosto
     * crawler to distinguish between stores that do not have separate domains
     * or paths.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store      $store the store model.
     *
     * @return string
     */
    protected function buildUrl(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        // Unset the cached url first, as it won't include the `___store` param
        // if it's cached. We need to define the specific store view in the url
        // in case the same domain is used for all sites.
        $product->unsetData('url');
        return $product
            ->getUrlInStore(
                array(
                    '_nosid' => true,
                    '_ignore_category' => true,
                    '_store' => $store->getCode(),
                )
            );
    }

    /**
     * Builds the product absolute image url for the store and returns it.
     * The image version is primarily taken from the store config, but falls
     * back the the base image if nothing is configured.
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     * @param Mage_Core_Model_Store      $store the store model.
     *
     * @return null|string
     */
    protected function buildImageUrl(Mage_Catalog_Model_Product $product, Mage_Core_Model_Store $store)
    {
        $url = null;
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        $imageVersion = $helper->getProductImageVersion($store);
        $img = $product->getData($imageVersion);
        $img = $this->isValidImage($img) ? $img : $product->getData('image');
        if ($this->isValidImage($img)) {
            // We build the image url manually in order get the correct base
            // url, even if this product is populated in the backend.
            $baseUrl = rtrim($store->getBaseUrl('media'), '/');
            $file = str_replace(DS, '/', $img);
            $file = ltrim($file, '/');
            $url = $baseUrl.'/catalog/product/'.$file;
        }
        return $url;
    }

    /**
     * Return array of categories for the product.
     * The items in the array are strings combined of the complete category
     * path to the products own category.
     *
     * Structure:
     * array (
     *     /Electronics/Computers
     * )
     *
     * @param Mage_Catalog_Model_Product $product the product model.
     *
     * @return array
     */
    protected function buildCategories(Mage_Catalog_Model_Product $product)
    {
        $data = array();

        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        $categoryCollection = $product->getCategoryCollection();
        foreach ($categoryCollection as $category) {
            $categoryString = $helper->buildCategoryString($category);
            if (!empty($categoryString)) {
                $data[] = $categoryString;
            }
        }

        return $data;
    }

    /**
     * Checks if the given image file path is valid.
     *
     * @param string $image the image file path.
     *
     * @return bool
     */
    protected function isValidImage($image)
    {
        return (!empty($image) && $image !== 'no_selection');
    }

    /**
     * Returns the ID of the variation that is currently in use.
     *
     * @return string the variation ID.
     */
    public function getVariationId()
    {
        return $this->_variationId;
    }

    /**
     * Returns the product variations if any exist.
     *
     * @return NostoProductVariationInterface[] the variations.
     */
    public function getVariations()
    {
        return $this->_variations;
    }

    /**
     * Sets the variation the current product prices are displayed in.
     *
     * @param string|int $variationId the variation ID.
     */
    public function setVariationId($variationId)
    {
        $this->_variationId = $variationId;
    }

    /**
     * Sets the variations that exist for this product.
     *
     * @param Nosto_Tagging_Model_Meta_Product_Variation[] $variations the variations.
     */
    public function setVariations(array $variations)
    {
        $this->_variations = array();
        foreach ($variations as $variation) {
            $this->addVariation($variation);
        }
    }

    /**
     * Adds a variation for this product.
     *
     * @param Nosto_Tagging_Model_Meta_Product_Variation $variation the variation.
     */
    public function addVariation(Nosto_Tagging_Model_Meta_Product_Variation $variation)
    {
        $this->_variations[] = $variation;
    }
}
