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
 * @copyright Copyright (c) 2013-2015 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer Container model.
 * Used to keep customer tagging block up to date when Magento EE FPC is used.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Container_Customer extends Enterprise_PageCache_Model_Container_Customer
{
    /**
     * Get identifier from cookies.
     *
     * @deprecated since 1.12.0.0
     * @return string
     */
    protected function _getIdentifier()
    {
        return $this->_getCookieValue(
            Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER, ''
        )
        . '_'
        . $this->_getCookieValue(
            Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER_LOGGED_IN, ''
        );
    }

    /**
     * @inheritdoc
     */
    protected function _getCacheId()
    {
        return 'CONTAINER_NOSTO_TAGGING_CUSTOMER_'
        . md5(
            $this->_placeholder->getAttribute('cache_id')
            . $this->_getIdentifier()
        );
    }

    /**
     * @inheritdoc
     */
    protected function _renderBlock()
    {
        $block = $this->_getPlaceHolderBlock();
        Mage::dispatchEvent(
            'render_block', array(
                'block' => $block,
                'placeholder' => $this->_placeholder
            )
        );
        return $block->toHtml();
    }
}
