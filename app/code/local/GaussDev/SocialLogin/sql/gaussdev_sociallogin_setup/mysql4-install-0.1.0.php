<?php
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
                   ->newTable($installer->getTable('gaussdev_sociallogin/oauth'))
                   ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
                       array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true))
                   ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
                       array('nullable' => false, 'unsigned' => true))
                   ->addColumn('provider', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array('nullable' => false))
                   ->addColumn('uid', Varien_Db_Ddl_Table::TYPE_BIGINT, null,
                       array('nullable' => false, 'unsigned' => true));
$installer->getConnection()->createTable($table);

$installer->endSetup();
