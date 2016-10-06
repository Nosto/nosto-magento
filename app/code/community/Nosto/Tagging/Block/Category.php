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
        if (!Mage::helper('nosto_tagging')->isModuleEnabled()
            || !$helper->existsAndIsConnected()
            || !$this->getCategory()
        ) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Return the current product category string for the category tagging.
     *
     * @return string
     */
    public function getCategory()
    {
        if (!$this->_category) {
            $category = Mage::registry('current_category');
            /** @var Nosto_Tagging_Helper_Data $helper */
            $helper = Mage::helper('nosto_tagging');
            $this->_category = $helper->buildCategoryString($category);
        }

        return $this->_category;
    }
}
