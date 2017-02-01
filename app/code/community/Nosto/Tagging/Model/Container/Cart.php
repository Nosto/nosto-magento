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
 * Cart Container model.
 * Used to keep cart tagging block up to date when Magento EE FPC is used.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
/** @noinspection PhpUndefinedClassInspection */
class Nosto_Tagging_Model_Container_Cart extends Enterprise_PageCache_Model_Container_Advanced_Quote
{
    /**
     * Get identifier from cookies.
     *
     * @deprecated since 1.12.0.0
     * @return string
     */
    protected function _getIdentifier()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedClassInspection */
        return $this->_getCookieValue(
            Enterprise_PageCache_Model_Cookie::COOKIE_CART, ''
        )
        . '_'
        . $this->_getCookieValue(
            Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER, ''
        );
    }

    /**
     * @inheritdoc
     */
    protected function _renderBlock()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedClassInspection */
        $block = $this->_getPlaceHolderBlock();
        /** @noinspection PhpUndefinedFieldInspection */
        Mage::dispatchEvent(
            'render_block', array(
                'block' => $block,
                'placeholder' => $this->_placeholder
            )
        );
        /** @noinspection PhpUndefinedMethodInspection */
        return $block->toHtml();
    }
}
