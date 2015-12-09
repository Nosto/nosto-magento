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
        if ($product->getPrice()) {
            $object->setPrice(new NostoPrice($product->getPrice()));
        }
        if ($product->getListPrice()) {
            $object->setListPrice(new NostoPrice($product->getListPrice()));
        }
        if ($product->getCurrency()) {
            $object->setCurrency(new NostoCurrencyCode($product->getCurrency()));
        }
        if ($product->getAvailability()) {
            $object->setAvailability(new NostoProductAvailability($product->getAvailability()));
        }
        foreach ($product->getCategories() as $categoryString) {
            $category = new NostoCategory($categoryString);
            $object->addCategory($category);
        }
        $object->setThumbUrl($product->getThumbUrl());
        $object->setShortDescription($product->getShortDescription());
        $object->setDescription($product->getDescription());
        $object->setBrand($product->getBrand());
        $object->setTags($product->getTags());
        if ($product->getDatePublished()) {
            $object->setDatePublished(new NostoDate(strtotime($product->getDatePublished())));
        }
        if (!empty($product->getVariations())) {
            $object->setPriceVariations($product->getPriceVariations());
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
        $object->setPrice($product->getPrice()->getPrice());
        $object->setListPrice($product->getListPrice()->getPrice());
        $object->setCurrency($product->getCurrency()->getCode());
        $object->setAvailability($product->getAvailability()->getAvailability());
        foreach ($product->getCategories() as $category) {
            $object->addCategory($category->getPath());
        }
        $object->setThumbUrl($product->getThumbUrl());
        $object->setShortDescription($product->getShortDescription());
        $object->setDescription($product->getDescription());
        $object->setBrand($product->getBrand());
        $object->setTags($product->getTags());
        $object->setDatePublished(date(NostoDateFormat::YMD,$product->getDatePublished()->getTimestamp()));
        if (!empty($product->getVariations())) {
            $object->setPriceVariations($product->getPriceVariations());
        }
        $object->setVariationId($product->getVariationId());

        return $object;
    }
}
