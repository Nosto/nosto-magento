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
 * Extension system setting backend model for saving the scheduled currency
 * exchange rate frequency, e.g. turns the setting into valid cron syntax.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Model_System_Config_Backend_Currency_Method extends Mage_Core_Model_Config_Data
{
    /**
     * @inheritdoc
     */
    protected function _afterSave()
    {
        $storeIds = array();
        if ($this->getScope() === 'stores' && $storeId = $this->getScopeId()) {
            $storeIds[] = $storeId;
        } else {
            foreach (Mage::app()->getStores() as $store) {
                $storeIds[] = $store->getId();
            }
        }
        Mage::dispatchEvent(
            'nosto_account_settings_after_save',
            array(
                'store_ids' => array_unique($storeIds),
            )
        );

        return parent::_afterSave();
    }
}
