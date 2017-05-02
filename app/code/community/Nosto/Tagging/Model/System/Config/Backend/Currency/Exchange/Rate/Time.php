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
 * Alters saving the cron schedule for exhange rates
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_System_Config_Backend_Currency_Exchange_Rate_Time
    extends Mage_Core_Model_Config_Data
{

    /**
     * If the cron is ran hourly the first drop down field is not sent as it's
     * disabled in Magento's Nosto settings. We need to set an artificial value
     * for the "minute" in order to render the selected form values correctly.
     *
     * @inheritdoc
     */
    protected function _beforeSave()
    {
        $values = $this->getValue();
        if (is_array($values) && count($values) < 3) {
            array_unshift($values, '00');
            $this->setValue($values);
        }
    }
}
