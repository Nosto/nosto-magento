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
 * Nosto meta block.
 * Used to render meta tag elements to page <head> section.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Meta extends Mage_Core_Block_Template
{
    /**
     * Render meta tags if the module is enabled for the current store.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::helper('nosto_tagging')->isModuleEnabled()
            || !Mage::helper('nosto_tagging/account')->existsAndIsConnected()
        ) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Returns the version number of the Nosto extension.
     *
     * @return string the module version.
     */
    public function getVersionModule()
    {
        // Path is hard-coded to be like in "etc/config.xml".
        return (string)Mage::getConfig()
            ->getNode('modules/Nosto_Tagging/version');
    }

    /**
     * Returns the 2-letter ISO code (ISO 639-1) for the shop language.
     *
     * @return string the language ISO code.
     */
    public function getLanguageIsoCode()
    {
        return substr(
            Mage::app()->getStore()->getConfig('general/locale/code'), 0, 2
        );
    }
}