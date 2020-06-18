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
 * Meta data class which holds information about a bundled item included in an order.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_Meta_Order_Item_Bundled extends Nosto_Tagging_Model_Meta_Order_Item
{

    /**
     * Bundle products will have their chosen child product names added.
     * @inheritdoc
     */
    public function buildItemName(Mage_Sales_Model_Order_Item $item)
    {
        $name = $item->getName();
        $optNames = array();

        $opts = $item->getProductOptionByCode('bundle_options');
        if (is_array($opts)) {
            foreach ($opts as $opt) {
                if (isset($opt['value']) && is_array($opt['value'])) {
                    foreach ($opt['value'] as $val) {
                        $qty = '';
                        if (isset($val['qty']) && is_int($val['qty'])) {
                            $qty .= $val['qty'] . ' x ';
                        }

                        if (isset($val['title']) && is_string($val['title'])) {
                            $optNames[] = $qty . $val['title'];
                        }
                    }
                }
            }
        }

        $name .= !empty($optNames) ? ' (' . implode(', ', $optNames) . ')' : '';
        return $name;
    }
}