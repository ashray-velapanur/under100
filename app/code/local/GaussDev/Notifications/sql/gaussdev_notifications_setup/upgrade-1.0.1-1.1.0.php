<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/** @var $connection Varien_Db_Adapter_Pdo_Mysql */
$connection = $installer->getConnection();
$tableName = $installer->getTable('notifications/device');

$connection->dropTable($tableName);
$table = $connection->newTable($tableName)->addColumn(
    'entity_id',
    Varien_Db_Ddl_Table::TYPE_INTEGER,
    null,
    array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true
    )
)->addColumn(
    'customer_id',
    Varien_Db_Ddl_Table::TYPE_INTEGER,
    null,
    array('nullable' => true, 'unsigned' => true),
    'ID of notified customer'
)->addColumn(
    'device_id',
    Varien_Db_Ddl_Table::TYPE_TEXT,
    '4k',
    array('nullable' => false)
)->addColumn(
    'type',
    Varien_Db_Ddl_Table::TYPE_TEXT,
    50,
    array('nullable' => false)
);
$connection->createTable($table);
$connection->addForeignKey(
    $this->getFkName('notifications/device', 'customer_id', 'customer/entity', 'entity_id'),
    $this->getTable('notifications/device'),
    'customer_id',
    $this->getTable('customer/entity'),
    'entity_id'
);

$connection->addColumn(
    $installer->getTable('notifications/notification'),
    'is_push_sent',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'nullable' => false,
        'default'  => 0,
        'comment'  => 'Is Push Sent'
    )
);
$installer->endSetup();