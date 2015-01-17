<?php
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

/** @var $connection Varien_Db_Adapter_Pdo_Mysql */
$connection = $installer->getConnection();
$table = $connection->newTable($installer->getTable('gaussdev_comments/comment'))
                    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
                        array('identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true))
                    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
                        array('nullable' => false, 'unsigned' => true))
                    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
                        array('nullable' => false, 'unsigned' => true))
                    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('unsigned' => true))
                    ->addColumn('message', Varien_Db_Ddl_Table::TYPE_TEXT, '1000', array('nullable' => false))
                    ->addColumn('reports', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
                        array('nullable' => false, 'default' => 0, 'unsigned' => true))
                    ->addColumn('likes', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
                        array('nullable' => false, 'default' => 0, 'unsigned' => true))
                    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array('nullable' => false))
                    ->addColumn('is_deleted', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,
                        array('nullable' => false, 'default' => 0))
                    ->addColumn('is_spam', Varien_Db_Ddl_Table::TYPE_BOOLEAN, null,
                        array('nullable' => false, 'default' => 0));
$connection->createTable($table);
$connection->changeTableEngine($installer->getTable('gaussdev_comments/comment'), 'MyISAM');
$connection->addIndex($installer->getTable('gaussdev_comments/comment'),
    $installer->getIdxName('gaussdev_comments/comment', 'message', Varien_Db_Adapter_Interface::INDEX_TYPE_FULLTEXT),
    'message', Varien_Db_Adapter_Interface::INDEX_TYPE_FULLTEXT);

$installer->endSetup();