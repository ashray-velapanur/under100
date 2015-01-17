<?php
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
                   ->newTable($installer->getTable('gaussdev_parser/host'))
                   ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
                       array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,))
                   ->addColumn('hostname', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array('nullable' => false,))
                   ->addColumn('name_xpath', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array('nullable' => false,))
                   ->addColumn('price_xpath', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array('nullable' => false,));
$installer->getConnection()->createTable($table);

$installer->endSetup();