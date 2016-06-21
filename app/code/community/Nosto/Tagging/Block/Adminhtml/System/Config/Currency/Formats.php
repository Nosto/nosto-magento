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
 * Info block to show the current configured currency formats for the viewed
 * store scope on the system config page.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Adminhtml_System_Config_Currency_Formats extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('nostotagging/system/config/currency/formats.phtml');
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Returns a list of Zend_Currency objects for selected store scope,
     * configured for the stores locale.
     * These can be used to format the price string in the view file.
     *
     * @return array the currency objects per store.
     */
    public function getCurrencyFormats()
    {
        $formats = array();
        $storeId = $this->getRequest()->getParam('store');
        /** @var Mage_Core_Model_Store[] $stores */
        if (!empty($storeId)) {
            $stores = array(Mage::app()->getStore($storeId));
        } else {
            $stores = Mage::app()->getStores();
        }
        foreach ($stores as $store) {
            $formats[$store->getName()] = array();
            $currencyCodes = $store->getAvailableCurrencyCodes(true);
            if (is_array($currencyCodes) && count($currencyCodes) > 0) {
                $locale = $store->getConfig('general/locale/code');
                foreach ($currencyCodes as $currencyCode) {
                    try {
                        $formats[$store->getName()][] = new Zend_Currency($currencyCode, $locale);
                    } catch (Zend_Exception $e) {
                        continue;
                    }
                }
            }
        }
        return $formats;
    }
}
