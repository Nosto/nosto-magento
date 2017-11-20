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
 * Meta data class which holds information about the current filtering.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Filter
{
    const NOSTO_PRICE_FROM = 'nosto_price_from';
    const NOSTO_PRICE_TO = 'nosto_price_to';

    protected $customFieldsFilter = array();
    protected $priceRangeFilter = array();
    protected $categoriesFilter = array();

    /**
     * Loads the filter data from the active session.
     * @param array $filters array of Mage_Catalog_Model_Layer_Filter_Item
     * @param Nosto_Tagging_Block_Filter $filterBlock
     */
    public function loadData($filters, Nosto_Tagging_Block_Filter $filterBlock)
    {
        $this->customFieldsFilter = array();
        $this->categoriesFilter = array();

        /** @var \Mage_Catalog_Model_Layer_Filter_Item $filter */
        foreach ($filters as $filter) {
            $model = $filter->getFilter();
            if ($model instanceof Mage_Catalog_Model_Layer_Filter_Price) {
                $this->loadPriceRange($filter);
                continue;
            }

            if ($model instanceof Mage_Catalog_Model_Layer_Filter_Category) {
                $this->loadCategoriesFilter($filter);
                continue;
            }

            if ($model
                && $model->getAttributeModel()
                && $model->getAttributeModel()->getAttributeCode()
            ) {
                $value = $filterBlock->stripTags($filter->getLabel());
                if ($value) {
                    $this->customFieldsFilter[$model->getAttributeModel()->getAttributeCode()] = $value;
                }
            }
        }

        Mage::dispatchEvent(
            Nosto_Tagging_Helper_Event::EVENT_NOSTO_PRODUCT_LOAD_AFTER,
            array(
                'filter' => $this,
                'magentoFilters' => $filters
            )
        );
    }

    protected function loadPriceRange(Mage_Catalog_Model_Layer_Filter_Item $filter)
    {
        $data = $filter->getData();
        if ($data && array_key_exists('value', $data)) {
            $value = $data['value'];
            if (is_array($value)) {
                $this->priceRangeFilter = array();
                if (array_key_exists(0, $value) && $value[0] !== '') {
                    $this->priceRangeFilter[self::NOSTO_PRICE_FROM] = $value[0];
                }
                if (array_key_exists(1, $value) && $value[1] !== '') {
                    $this->priceRangeFilter[self::NOSTO_PRICE_TO] = $value[1];
                }
                /* @var Nosto_Tagging_Helper_Data $helper */
                $helper = Mage::helper('nosto_tagging');
                //Always tag the price filter in base currency if multi-currency is enabled
                //because it is the currency to be store in the nosto
                if (!$helper->multiCurrencyDisabled(Mage::app()->getStore())) {
                    /* @var Nosto_Tagging_Helper_Price $nostoPriceHelper */
                    $nostoPriceHelper = Mage::helper('nosto_tagging/price');
                    $this->priceRangeFilter = array_map(function ($price) use ($nostoPriceHelper) {
                        return $nostoPriceHelper->convertFromCurrentToBaseCurrency(
                            $price,
                            Mage::app()->getStore()
                        );
                    }, $this->priceRangeFilter);
                }
            }
        }
    }

    protected function loadCategoriesFilter(Mage_Catalog_Model_Layer_Filter_Item $filter)
    {
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');

        $categoryId = $filter->getValueString();
        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            if ($category instanceof Mage_Catalog_Model_Category) {
                $this->categoriesFilter[] = $helper->buildCategoryString($category);
            }
        }
    }

    /**
     * @return array
     */
    public function getCustomFieldsFilter()
    {
        return $this->customFieldsFilter;
    }

    /**
     * @param array $customFieldsFilter
     */
    public function setCustomFieldsFilter($customFieldsFilter)
    {
        $this->customFieldsFilter = $customFieldsFilter;
    }

    /**
     * @return array
     */
    public function getPriceRangeFilter()
    {
        return $this->priceRangeFilter;
    }

    /**
     * @param array $priceRangeFilter
     */
    public function setPriceRangeFilter($priceRangeFilter)
    {
        $this->priceRangeFilter = $priceRangeFilter;
    }

    /**
     * @return array
     */
    public function getCategoriesFilter()
    {
        return $this->categoriesFilter;
    }

    /**
     * @param array $categoriesFilter
     */
    public function setCategoriesFilter($categoriesFilter)
    {
        $this->categoriesFilter = $categoriesFilter;
    }
}
