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
 * Nosto "ajax button" block.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
abstract class Nosto_Tagging_Block_Adminhtml_System_Config_Ajax_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('nostotagging/system/config/ajax/button.phtml');
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    /**
     * Returns html for the button.
     *
     * @return string
     */
    public function getButtonHtml()
    {
        return $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'id'  => $this->getButtonId(),
                    'label' => $this->getButtonLabel(),
                    'onclick' => 'javascript:Nosto.'.$this->getButtonOnClick().'(); return false;'
                )
            )
            ->toHtml();
    }

    /**
     * Returns the label for the button.
     *
     * @return string
     */
    protected function getButtonLabel()
    {
        return $this->helper('nosto_tagging')->__('Update Now');
    }

    /**
     * Returns the currency scope params, i.e. the selected store or website.
     *
     * @return array
     */
    protected function getScopeParams()
    {
        $params = array();
        $store = $this->getRequest()->getParam('store');
        $website = $this->getRequest()->getParam('website');
        if (!is_null($store)) {
            $params['store'] = $store;
        } elseif (!is_null($website)) {
            $params['website'] = $website;
        }
        return $params;
    }

    /**
     * Returns the element ID for the button.
     *
     * @return string
     */
    abstract public function getButtonId();

    /**
     * Return ajax url for the button.
     *
     * @return string
     */
    abstract public function getButtonUrl();

    /**
     * Returns the name of the "onclick" callback function.
     *
     * @return string
     */
    abstract public function getButtonOnClick();
}
