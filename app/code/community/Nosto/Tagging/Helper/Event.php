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
 * Helper class for event handling
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Event extends Mage_Core_Helper_Abstract
{
    /**
     * event name, fired after nosto product object loaded
     */
    const EVENT_NOSTO_PRODUCT_LOAD_AFTER = 'nosto_product_load_after';

    /**
     * event name, fired after nosto order object loaded
     */
    const EVENT_NOSTO_ORDER_LOAD_AFTER = 'nosto_order_load_after';

    /**
     * event name, fired after nosto shopping cart object loaded
     */
    const EVENT_NOSTO_CART_LOAD_AFTER = 'nosto_cart_load_after';

    /*
     * event name, fired after nosto category object loaded
     */
    const EVENT_NOSTO_CATEGORY_LOAD_AFTER = 'nosto_category_load_after';
}
