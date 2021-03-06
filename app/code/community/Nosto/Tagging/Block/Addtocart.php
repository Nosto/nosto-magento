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
 * Adds the `add-to-cart` script.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Addtocart extends Mage_Core_Block_Template
{
    /**
     * Render script if the module is enabled for the current store.
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var Nosto_Tagging_Helper_Account $helper */
        $helper = Mage::helper('nosto_tagging/account');
        /** @var Nosto_Tagging_Helper_Module $moduleHelper */
        $moduleHelper = Mage::helper('nosto_tagging/module');
        if (!$moduleHelper->isModuleEnabled()
            || !$helper->existsAndIsConnected()
        ) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Returns the url for add to cart controller
     *
     * @return string
     */
    public function getAddToCartUrl()
    {
        /** @var Nosto_Tagging_Helper_Url $urlHelper */
        $urlHelper = Mage::helper('nosto_tagging/url');
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        $store = $helper->getStore();

        return $urlHelper->getAddToCartUrl($store);
    }

    /**
     * Returns the url for add to cart controller
     *
     * @return string
     */
    public function getAddSkuToCartUrl()
    {
        /** @var Nosto_Tagging_Helper_Url $urlHelper */
        $urlHelper = Mage::helper('nosto_tagging/url');
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        $store = $helper->getStore();

        return $urlHelper->getNostoAddToCartUrl($store);
    }

    /**
     * Returns the url for add to cart controller
     *
     * @return string
     */
    public function getAddMultipleProductsToCartUrl()
    {
        /** @var Nosto_Tagging_Helper_Url $urlHelper */
        $urlHelper = Mage::helper('nosto_tagging/url');
        /** @var Nosto_Tagging_Helper_Data $helper */
        $helper = Mage::helper('nosto_tagging');
        $store = $helper->getStore();

        return $urlHelper->getNostoAddMultipleProductsToCartUrl($store);
    }
}
