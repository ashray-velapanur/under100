<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var $connection Varien_Db_Adapter_Pdo_Mysql */
$connection = $installer->getConnection();
$tableName = $installer->getTable('notifications/notification');

$connection->dropTable($tableName);
$table = $connection->newTable($tableName)
                    ->addColumn(
                        'entity_id',
                        Varien_Db_Ddl_Table::TYPE_INTEGER,
                        null,
                        array(
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary'  => true
                        )
                    )
                    ->addColumn(
                        'notify_id',
                        Varien_Db_Ddl_Table::TYPE_INTEGER,
                        null,
                        array('nullable' => false, 'unsigned' => true),
                        'ID of notified customer'
                    )
                    ->addColumn('is_saved', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null, array('nullable' => false, 'default' => 0))
                    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array('nullable' => false))
                    ->addColumn('data_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('unsigned' => true, 'nullable' => true))
                    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array('nullable' => false));
$connection->createTable($table);
$connection->addForeignKey(
    $this->getFkName('notifications/notification', 'notify_id', 'customer/entity', 'entity_id'),
    $this->getTable('notifications/notification'),
    'notify_id',
    $this->getTable('customer/entity'),
    'entity_id'
);
$installer->endSetup();