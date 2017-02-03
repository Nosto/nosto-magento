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
 * This install script will run only when installing the version 1.2.0 or above,
 * i.e. when no "tagging_setup" entry is present in the `core_resource` table.
 *
 * Creates the db table for matching Magento cart quotes to nosto customer ids.
 *
 * @var Mage_Eav_Model_Entity_Setup $installer
 */

$installer = $this;
$installer->startSetup();

if (!$installer->getConnection()->isTableExists($installer->getTable('nosto_tagging/customer'))) {
    $table = $installer
        ->getConnection()
        ->newTable($installer->getTable('nosto_tagging/customer'))
        ->addColumn(
            'customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
                'identity' => true
            )
        )
        ->addColumn(
            'quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
                'unsigned' => true,
                'nullable' => false
            )
        )
        ->addColumn(
            'nosto_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
                'nullable' => false
            )
        )
        ->addColumn(
            'created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
                'nullable' => false
            )
        )
        ->addColumn(
            'updated_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
                'nullable' => true
            )
        )
        ->addIndex(
            $installer->getIdxName(
                'nosto_tagging/customer', array('quote_id', 'nosto_id'),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            ),
            array('quote_id', 'nosto_id'), array(
                'type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            )
        );
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();
