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
 * Nosto element block.
 * Used to render placeholder elements that display the product recommendations.
 *
 * @method string getDivId() Return the id of the element (defined in layout).
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Element extends Mage_Core_Block_Template
{
    /**
     * Default id assigned to the element if none is set in the layout xml.
     */
    const DEFAULT_ID = 'missingDivIdParameter';

    /**
     * Render HTML placeholder element for the product recommendations if the
     * module is enabled for the current store.
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
     * Return the id of the element. If none is defined in the layout xml,
     * then set a default one.
     *
     * @return string
     */
    public function getElementId()
    {
        $id = $this->getDivId();
        if ($id === null) {
            $id = self::DEFAULT_ID;
        }
        return $id;
    }
}
