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
 * Helper class for converting between primitive and typed product objects.
 *
 * This is used for backwards compatibility when merchants have extended the
 * product model that uses primitive values for prices, dates etc. The new
 * typed product model deals with objects for these values. In order to keep
 * the old extensions working, we initialize the primitive version of the model
 * and then convert it to a typed version before using it in API calls and views.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Product_Converter extends Mage_Core_Helper_Abstract
{
    /**
     * Converts Nosto_Tagging_Model_Meta_Product into strongly typed Nosto_Tagging_Model_Meta_Product_Typed object
     *
     * @param Nosto_Tagging_Model_Meta_Product $product
     *
     * @return Nosto_Tagging_Model_Meta_Product_Typed
     */
    public function convertToTypedObject(Nosto_Tagging_Model_Meta_Product $product)
    {
        $object = new Nosto_Tagging_Model_Meta_Product_Typed();
        $object->setUrl($product->getUrl());
        $object->setProductId($product->getProductId());
        $object->setName($product->getName());
        $object->setImageUrl($product->getImageUrl());
        try {
            $object->setPrice(new NostoPrice($product->getPrice()));
        } catch (NostoInvalidArgumentException $E) {
            // Omit invalid data
        }
        try {
            $object->setListPrice(new NostoPrice($product->getListPrice()));
        } catch (NostoInvalidArgumentException $E) {
            // Omit invalid data
        }
        try {
            $object->setCurrency(new NostoCurrencyCode($product->getCurrency()));
        } catch (NostoInvalidArgumentException $E) {
            // Omit invalid data
        }
        try {
            $object->setAvailability(new NostoProductAvailability($product->getAvailability()));
        } catch (NostoInvalidArgumentException $E) {
            // Omit invalid data
        }
        foreach ($product->getCategories() as $categoryString) {
            if (is_string($categoryString)) {
                $category = new NostoCategory($categoryString);
            } elseif ($categoryString instanceof NostoCategoryInterface) {
                $category = $categoryString;
            }
            $object->addCategory($category);
        }
        $object->setThumbUrl($product->getThumbUrl());
        $object->setShortDescription($product->getShortDescription());
        $object->setDescription($product->getDescription());
        $object->setBrand($product->getBrand());
        $object->setTags($product->getTags());
        try {
            $object->setDatePublished(new NostoDate(strtotime($product->getDatePublished())));
        } catch (NostoInvalidArgumentException $E) {
            // Omit invalid data
        }
        $variations = $product->getPriceVariations();
        if (!empty($variations)) {
            $object->setPriceVariations($variations);
        }
        $object->setVariationId($product->getVariationId());

        return $object;
    }

    /**
     * Converts typed Nosto_Tagging_Model_Meta_Product_Typed into Nosto_Tagging_Model_Meta_Product that uses only scalar attributes
     *
     * @param Nosto_Tagging_Model_Meta_Product_Typed $product
     *
     * @return Nosto_Tagging_Model_Meta_Product
     */
    public function convertToPrimitiveObject(Nosto_Tagging_Model_Meta_Product_Typed $product)
    {
        $object = new Nosto_Tagging_Model_Meta_Product();
        $object->setUrl($product->getUrl());
        $object->setProductId($product->getProductId());
        $object->setName($product->getName());
        $object->setImageUrl($product->getImageUrl());
        if ($product->getPrice() instanceof NostoPrice) {
            $object->setPrice($product->getPrice()->getPrice());
        } elseif (is_int($product->getPrice())) {
            $object->setPrice($product->getPrice());
        }
        if ($product->getListPrice() instanceof NostoPrice) {
            $object->setListPrice($product->getListPrice()->getPrice());
        } elseif (is_numeric($product->getListPrice())) {
            $object->setListPrice($product->getListPrice());
        }
        if ($product->getCurrency() instanceof NostoCurrency) {
            $object->setCurrency($product->getCurrency()->getCode());
        } elseif (is_numeric($product->getCurrency())) {
            $object->setCurrency($product->getCurrency());
        }
        if ($product->getAvailability() instanceof NostoProductAvailability) {
            $object->setAvailability($product->getAvailability()->getAvailability());
        } elseif (is_string($product->getAvailability())) {
            $object->setAvailability($product->getAvailability());
        }
        foreach ($product->getCategories() as $category) {
            if ($category instanceof NostoCategoryInterface) {
                $object->addCategory($category->getPath());
            } elseif (is_string($category) || is_integer($category)) {
                $object->addCategory($category);
            }
        }
        $object->setThumbUrl($product->getThumbUrl());
        $object->setShortDescription($product->getShortDescription());
        $object->setDescription($product->getDescription());
        $object->setBrand($product->getBrand());
        $object->setTags($product->getTags());
        if ($product->getDatePublished() instanceof NostoDate) {
            $object->setDatePublished(
                date(NostoDateFormat::YMD,$product->getDatePublished()->getTimestamp())
            );
        }
        $variations = $product->getPriceVariations();
        if (!empty($variations)) {
            $object->setPriceVariations($variations);
        }
        $object->setVariationId($product->getVariationId());

        return $object;
    }
}
