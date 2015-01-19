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
 * @copyright Copyright (c) 2013-2015 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * 'Remove Nosto' form block.
 * Creates the html form needed for submitting 'Remove Nosto' requests to the admin controller.
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Block_Adminhtml_Form_Account_Remove extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'nosto_remove_account_form',
            'action' => $this->getUrl('*/*/removeAccount'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));
        $form->setUseContainer(true);
        $form->addField('store', 'hidden', array(
            'name' => 'store',
            'value' => $this->getRequest()->getParam('store', 0),
        ));
        $form->addField('nosto_remove_account_submit', 'submit', array(
            'class' => 'form-button',
            'name' => 'nosto_remove_account_submit',
            'value' => $this->__('Remove Nosto'),
        ));
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Gets the Nosto account name from the parent block, which should be Nosto_tagging_Block_Adminhtml_Wizard.
     *
     * @return string the account name or empty string if not found in parent.
     */
    public function getAccountName()
    {
        $parent = $this->getParentBlock();
        if ($parent instanceof Nosto_tagging_Block_Adminhtml_Wizard) {
            return $parent->getAccount()->name;
        }
        return '';
    }
}
