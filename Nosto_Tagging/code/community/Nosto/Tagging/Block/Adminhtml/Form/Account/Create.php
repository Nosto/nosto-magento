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
 * @category    design
 * @package     base_default
 * @copyright   Copyright (c) 2013 Nosto Solutions Ltd (http://www.nosto.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Nosto_Tagging_Block_Adminhtml_Form_Account_Create extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	 * @inheritdoc
	 */
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
			'id'        => 'nosto_create_account_form',
			'action'    => $this->getUrl('*/*/createAccount'),
			'method'    => 'post',
			'enctype'   => 'multipart/form-data'
		));
		$form->setUseContainer(true);
		$form->addField('nosto_store_id', 'hidden', array(
			'name' => 'nosto_store_id',
			'value' => $this->getRequest()->getParam('store', 0),
		));
		$form->addField('nosto_create_account_email', 'text', array(
			'label' => 'Email', // todo: translatable
			'name' => 'nosto_create_account_email',
			'value' => 'todo@nosto.com', // todo: take from logged in admin user profile
		));
		$form->addField('nosto_terms_and_conditions', 'note', array(
			'text' => 'By creating a new account you agree to Nosto\'s <a href="http://www.nosto.com/terms" target="_blank">Terms and Conditions</a>' // todo: translatable
		));
		$form->addField('nosto_create_account_submit', 'submit', array(
			'class' => 'form-button',
			'name' => 'nosto_create_account_submit',
			'value' => 'Create Nosto', // todo: translatable
		));
		$this->setForm($form);

		return parent::_prepareForm();
	}
} 