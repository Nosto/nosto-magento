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
 * @category  design
 * @package   adminhtml_default_default
 * @author    Nosto Solutions Ltd <magento@nosto.com>
 * @copyright Copyright (c) 2013-2015 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Nosto "update exchange rates" button block.
 *
 * Adds the button to update currency exchange rates in Nosto to the system
 * config page.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Adminhtml_System_Config_Multi_Currency_Exchange_Rate_Button extends Nosto_Tagging_Block_Adminhtml_System_Config_Ajax_Button
{
    /**
     * @inheritdoc
     */
    public function getButtonId()
    {
        return 'nostotagging_update_exchange_rates_button';
    }

    /**
     * @inheritdoc
     */
    public function getButtonUrl()
    {
        /** @var Mage_Adminhtml_Helper_Data $helper */
        $helper = Mage::helper('adminhtml');
        return $helper->getUrl('adminhtml/nosto/ajaxUpdateExchangeRates');
    }

    /**
     * @inheritdoc
     */
    public function getButtonOnClick()
    {
        return 'updateExchangeRates';
    }
}
