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
 * Block for editing Nosto's currency exchange cron frequency
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Adminhtml_System_Config_Currency_CronFrequency
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * The select name
     */
    const HOUR_SELECT_NAME = 'groups[scheduled_currency_exchange_rate_update][fields][time][value][]';

    /**
     * Array of the available frequency options
     *
     * @var array
     */
    private $options;

    /**
     * Form element to be rendered
     *
     * @var Varien_Data_Form_Element_Abstract
     */
    private $element;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(
            'nostotagging/system/config/currency/cronFrequency.phtml'
        );
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->element = $element;
        $data = $this->element->getData();
        if (isset($data['values']) && is_array($data['values'])) {
            $this->options = $data['values'];
        } else {
            Mage::log(
                'Could not find any options for cron frequency',
                Zend_Log::ERR,
                Nosto_Tagging_Model_Base::LOG_FILE_NAME
            );
        }

        return $this->_toHtml();
    }

    /**
     * Returns the options for exchange rate cron frequency
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns the id of the select id
     *
     * @return array
     */
    public function getSelectId()
    {
        return $this->element->getHtmlId();
    }

    /**
     * Returns the name of the select tag
     *
     * @return array
     */
    public function getSelectName()
    {
        return $this->element->getName();
    }

    /**
     * Returns the name of the hour select field
     *
     * @return array
     */
    public function getHourSelectName()
    {
        return self::HOUR_SELECT_NAME;
    }

    /**
     * Returns the current value
     *
     * @return array
     */
    public function getCurrentValue()
    {
        /* @var Nosto_Tagging_Helper_Data $configHelper */
        $configHelper = Mage::helper('nosto_tagging');
        return $configHelper->getExchangeRateCronFrequency();
    }
}
