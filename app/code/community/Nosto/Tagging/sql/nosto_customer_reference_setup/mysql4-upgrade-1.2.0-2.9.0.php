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
 * This install script will run only when installing the version 2.9.0 or above
 *
 * Adds custom attribute `nosto_customer_reference` for the Custom object
 *
 */

/* @var $installer Nosto_Tagging_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entity = 'customer';
// ToDo - remove me
//$installer->removeAttribute(
//    $entity,
//    Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME
//);

$code = Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME;
$attr = $installer->getAttribute(
    $entity,
    Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME
);

if (!$attr)
{
    $entityTypeId     = $setup->getEntityTypeId('customer');
    $attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
    $attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);


    $installer->addAttribute(
        $entity,
        Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME,
        array(
            "type"     => "varchar",
            "backend"  => "",
            "label"    => "Nosto Customer Reference",
            "input"    => "text",
            "source"   => "",
            "visible"  => true,
            "required" => false,
            "default" => "",
            "frontend" => "",
            "unique"     => true,
            "note"       => "Unique customer reference for Nosto"
        )
    );

    $attribute = Mage::getSingleton("eav/config")->getAttribute(
        "customer",
        "nosto_customer_reference"
    );

    $setup->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME,
        '999'  //sort_order
    );

    $used_in_forms=array("adminhtml_customer");
    $attribute->setData("used_in_forms",$used_in_forms)
        ->setData("is_used_for_customer_segment", false)
        ->setData("is_system", 1)
        ->setData("is_user_defined", 0)
        ->setData("is_visible", 1)
        ->setData("sort_order", 100);

    $attribute->save();
}
$installer->endSetup();
