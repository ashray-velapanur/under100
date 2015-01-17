<?php
/* @var $this Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

/** @var $connection Varien_Db_Adapter_Pdo_Mysql */
$connection = $installer->getConnection();

$tableName = $this->getTable('gaussdev_comments/tags');

$table = $connection->newTable($tableName)
                    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
                        array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true))
                    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
                        array('nullable' => true, 'unsigned' => true))
                    ->addColumn('comment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
                        array('nullable' => false, 'unsigned' => true))
                    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array('nullable' => false))
                    ->addForeignKey($this->getFkName('gaussdev_comments/tags', 'comment_id',
                            'gaussdev_comments/comment', 'entity_id'), 'comment_id',
                        $this->getTable('gaussdev_comments/comment'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE,
                        Varien_Db_Ddl_Table::ACTION_CASCADE)
                    ->addForeignKey($this->getFkName('gaussdev_comments/tags', 'customer_id', 'customer/entity',
                            'entity_id'), 'customer_id', $this->getTable('customer/entity'), 'entity_id',
                        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE);

$connection->createTable($table);

$connection->addIndex($tableName, $this->getIdxName('gaussdev_comments/tags', array('customer_id', 'comment_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE), array('position', 'comment_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);

$installer->endSetup();
