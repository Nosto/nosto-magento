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
 * Extends system setting source model for choosing exchange cron frequency
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_System_Config_Source_Cron_Frequency extends Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency
{
    const CRON_HOURLY = 'H';

    /**
     * Returns the frequency options
     *
     * @return array the options.
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        if (empty($options[self::CRON_HOURLY])) {
            array_unshift(
                $options,
                array(
                    'label' => Mage::helper('cron')->__('Hourly'),
                    'value' => self::CRON_HOURLY,
                )
            );
        }

        return $options;
    }
}
