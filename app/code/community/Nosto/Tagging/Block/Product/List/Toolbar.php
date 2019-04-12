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
 * Block for extending category page and adding Nosto relevance sorting
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Product_List_Toolbar
    extends Mage_Catalog_Block_Product_List_Toolbar
{
    /**
     * Add logic for handling the Nosto product relevance
     *
     * @param Varien_Data_Collection $collection
     * @return Mage_Catalog_Block_Product_List_Toolbar
     * @throws Mage_Core_Model_Store_Exception
     */
    public function setCollection($collection)
    {
        /* @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');
        /* @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        $store = Mage::app()->getStore();
        if (($this->getCurrentOrder() !== Nosto_Tagging_Model_Category_Config::NOSTO_PERSONALIZED_KEY
            && $this->getCurrentOrder() !== Nosto_Tagging_Model_Category_Config::NOSTO_TOPLIST_KEY)
            || !$dataHelper->getUsePersonalizedCategorySorting($store)
            || !$accountHelper->find($store)
        ) {
            return parent::setCollection($collection);
        }
        $this->_collection = $collection;
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        $this->_collection->setCurPage($this->getCurrentPage());
        $sortIds = array_reverse($this->getSortIds($this->getCurrentOrder()));
        if (!empty($sortIds)) {
            $orderByIds = sprintf(
                'FIELD(product_id, %s) DESC',
                implode(',', $sortIds)
            );
            $this->_collection->getSelect()->order($orderByIds);
        }

        return $this;
    }

    /**
     * Returns the product ids
     *
     * @param string $type
     * @return array
     */
    protected function getSortIds($type)
    {
        /* @var Nosto_Tagging_Model_Service_Recommendation_Category $categoryService */
        $categoryService = Mage::getModel('nosto_tagging/service_recommendation_category');
        $nostoVisitId = Mage::getModel('core/cookie')->get(Nosto_Tagging_Helper_Data::COOKIE_NAME);
        $category = Mage::registry('current_category');
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        $category = $helper->buildCategoryString($category);
        /** @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('nosto_tagging/account');
        $account = $helper->find();

        return $categoryService->getSortedProductIds(
            $account,
            $nostoVisitId,
            $category,
            $type
        );
    }
}
