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
 * Helper class for Nosto related setups
 *
 * @category Nosto
 * @package  Nosto_Tagging
 * @author   Nosto Solutions Ltd <magento@nosto.com>
 */
class Nosto_Tagging_Helper_Setup extends Mage_Core_Helper_Abstract
{
    /**
     * Adds nosto_customer_reference as to the Customer entity
     *
     * @param $installer Nosto_Tagging_Model_Resource_Setup
     * @param $reset bool if set to true the existing nosto_customer_ref is removed
     *
     * @return void
     */
    public function addNostoCustomerReferenceEav(Nosto_Tagging_Model_Resource_Setup $installer, $reset = false)
    {
        $installer->startSetup();
        $entity = 'customer';
        $attributeExists = $installer->getAttribute(
            $entity,
            Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME
        );
        if ($attributeExists && $reset === true) {
            $installer->removeAttribute(
                $entity,
                Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME
            );
            $attributeExists = false;
        }
        if (!$attributeExists)
        {
            $attributeDefinition = array(
                "type" => "varchar",
                "label" => "Nosto Customer Reference",
                "input" => "text",
                "visible" => true,
                "required" => false,
                "unique" => true,
                "note" => "Unique customer reference for Nosto"
            );
            $installer->addAttribute(
                $entity,
                Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME,
                $attributeDefinition
            );

            $attribute = Mage::getSingleton("eav/config")->getAttribute(
                $entity,
                Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME
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
    }

    /**
     * Changes the nosto_customer_reference frontend input type to text
     *
     * @param $installer Nosto_Tagging_Model_Resource_Setup
     *
     * @return void
     */
    public function alterCustomerReferenceInputType(Nosto_Tagging_Model_Resource_Setup $installer)
    {
        $installer->updateAttribute(
            'customer',
            Nosto_Tagging_Helper_Data::NOSTO_CUSTOMER_REFERENCE_ATTRIBUTE_NAME,
            array(
                'frontend_input' => 'text',
            )
        );
    }
}
