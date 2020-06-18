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
 * Current category tagging block.
 * Adds meta-data to the HTML document for the current catalog category
 * (including parent categories).
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Category extends Mage_Core_Block_Template
{
    /**
     * @var string Cached category string.
     */
    protected $_category;

    /**
     * Render category string as hidden meta data if the module is enabled for
     * the current store.
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('nosto_tagging/account');
        /** @var Nosto_Tagging_Helper_Module $moduleHelper */
        $moduleHelper = Mage::helper('nosto_tagging/module');
        $category = $this->getCategory();
        if (!$helper->existsAndIsConnected()
            || $category === null
            || !$moduleHelper->isModuleEnabled()
        ) {
            return '';
        }

        return $category->toHtml();
    }

    /**
     * Return the current product category
     *
     * @return null|Nosto_Object_Category
     */
    public function getCategory()
    {
        try {
            $category = Mage::registry('current_category');
            if ($category) {
                return Nosto_Tagging_Model_Meta_Category_Builder::build($category);
            }

            return null;
        } catch (\Exception $e) {
            Nosto_Tagging_Helper_Log::exception($e);
            return null;
        }
    }
}
