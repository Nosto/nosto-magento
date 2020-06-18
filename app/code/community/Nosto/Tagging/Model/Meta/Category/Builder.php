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
 * Meta data class which holds information about a category
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Category_Builder
{
    public static function build(Mage_Catalog_Model_Category $category)
    {
        $nostoDataHelper = new Nosto_Tagging_Helper_Data();

        $nostoCategory = new Nosto_Object_Category();
        $nostoCategory->setCategoryString($nostoDataHelper->buildCategoryString($category));
        $nostoCategory->setId($category->getEntityId());
        $nostoCategory->setParentId($category->getParentId());
        $nostoCategory->setName($category->getName());
        $nostoCategory->setUrl($category->getUrl());
        $nostoCategory->setImageUrl($category->getImageUrl());
        $nostoCategory->setLevel($category->getLevel());
        $nostoCategory->setVisibleInMenu(self::getCategoryVisibleInMenu($category));

        Mage::dispatchEvent(
            Nosto_Tagging_Helper_Event::EVENT_NOSTO_CATEGORY_LOAD_AFTER,
            array(
                'category' => $nostoCategory,
                'magentoCategory' => $category
            )
        );

        return $nostoCategory;
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @return bool
     */
    protected static function getCategoryVisibleInMenu(Mage_Catalog_Model_Category $category)
    {
        $visibleInMenu = $category->getIncludeInMenu();

        return $visibleInMenu === "1";
    }
}