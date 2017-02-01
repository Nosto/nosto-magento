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
 * Extension system setting backend model for saving the scheduled currency
 * exchange rate frequency, e.g. turns the setting into valid cron syntax.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_System_Config_Backend_Currency_Exchange_Rate_Cron extends Mage_Core_Model_Config_Data
{
    /**
     * Path to the cron expression.
     */
    const CRON_STRING_PATH = 'crontab/jobs/nostotagging_currency_exchange_rate_update/schedule/cron_expr';

    /**
     * Generates the cron configuration for updating exchange rates update to
     * Nosto.
     *
     * Note that if cron is ran Hourly the hour field is not posted at all
     * as the hour selector gets disabled when frequency is set to hourly
     *
     * @inheritdoc
     */
    protected function _afterSave()
    {
        $time = $this->getData('groups/scheduled_currency_exchange_rate_update/fields/time/value');
        $frequency = $this->getData('groups/scheduled_currency_exchange_rate_update/fields/frequency/value');

        $weekly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
        $monthly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;
        $hourly = Nosto_Tagging_Model_System_Config_Source_Cron_Frequency::CRON_HOURLY;
        $cronExpr = implode(
            ' ',
            array(
                ($frequency === $hourly) ? (int)$time[0] : (int)$time[1], # Minute
                ($frequency === $hourly) ? '*' : (int)$time[0], # Hour
                ($frequency === $monthly) ? '1' : '*',  # Day of the Month
                '*',                                    # Month of the Year
                ($frequency === $weekly) ? '1' : '*',   # Day of the Week
            )
        );

        try {
            /** @var Mage_Core_Model_Config_Data $model */
            $model = Mage::getModel('core/config_data');
            $model = $model->load(self::CRON_STRING_PATH, 'path');
            $model = $model->setValue($cronExpr);
            $model = $model->setPath(self::CRON_STRING_PATH);
            $model->save();
        } catch (Exception $e) {
            throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));
        }
    }
}
