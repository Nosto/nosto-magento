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
 * Product tagging block.
 * Adds meta-data to the HTML document for the currently viewed product.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Product extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * @var Nosto_Tagging_Model_Meta_Product runtime cache for the product meta.
     */
    protected $_product;

    /**
     * Render product info as hidden meta data if the module is enabled for the
     * current store.
     * If it is a "bundle" product with fixed price type, then do not render.
     * These are not supported due to their child products not having prices
     * available.
     *
     * @return string
     * @throws Mage_Core_Exception
     */
    protected function _toHtml()
    {
        /** @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('nosto_tagging/account');
        /** @var Nosto_Tagging_Helper_Module $moduleHelper */
        $moduleHelper = Mage::helper('nosto_tagging/module');
        if (!$helper->existsAndIsConnected()
            || $this->getMetaProduct() === null
            || !$moduleHelper->isModuleEnabled()
        ) {
            return '';
        }

        $nostoProduct = $this->getMetaProduct();
        if ($nostoProduct instanceof Nosto_AbstractObject) {
            return $nostoProduct->toHtml();
        }

        return '';
    }

    /**
     * Helper method that checks if the product object has been overidden
     *
     * @return bool a boolean value indicating the state
     * @throws Mage_Core_Exception
     */
    public function isOveridden()
    {
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        return !($this->getMetaProduct() instanceof \Nosto_Tagging_Model_Meta_Product);
    }

    /**
     * Returns the product meta data to tag.
     *
     * @return Nosto_Tagging_Model_Meta_Product the meta data.
     * @throws Mage_Core_Exception
     */
    public function getMetaProduct()
    {
        if ($this->_product === null) {
            /* @var Nosto_Tagging_Helper_Data $dataHelper */
            $dataHelper = Mage::helper('nosto_tagging');
            try {
                $store = $dataHelper->getStore();
                $model = Nosto_Tagging_Model_Meta_Product_Builder::build(
                    $this->getProduct(),
                    $store
                );
                if ($model instanceof Nosto_Tagging_Model_Meta_Product) {
                    $this->_product = $model;
                }
            } catch (Nosto_NostoException $e) {
                Nosto_Tagging_Helper_Log::exception($e);
            }
        }

        return $this->_product;
    }

    /**
     * Returns the current category under which the product is viewed.
     *
     * @return string the category path or empty if not found.
     */
    public function getCurrentCategory()
    {
        $category = Mage::registry('current_category');
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');

        return $helper->buildCategoryString($category);
    }
}
