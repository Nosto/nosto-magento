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
 * Nosto page-type block.
 * Defines the page-type so they can be used for the popup triggers
 *
 * @method string getPageType() Return the type of the page (defined in layout).
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Pagetype extends Mage_Core_Block_Template
{
    /**
     * Default type assigned to the page if none is set in the layout xml.
     */
    const DEFAULT_TYPE = 'unknown';

    /**
     * Render HTML for the page type if the module is enabled for the current
     * store.
     *
     * @return string
     */
    protected function _toHtml()
    {
        /* @var Nosto_Tagging_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('nosto_tagging');
        /* @var Nosto_Tagging_Helper_Account $accountHelper */
        $accountHelper = Mage::helper('nosto_tagging/account');
        if (!$dataHelper->isModuleEnabled()
            || !$accountHelper->existsAndIsConnected()
        ) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Return the page-type of the current page. If none is defined in the layout xml,
     * then set a default one.
     *
     * @return string
     */
    public function getPageTypeName()
    {
        $type = $this->getPageType();
        if ($type === null) {
            $type = self::DEFAULT_TYPE;
        }
        return $type;
    }
}
