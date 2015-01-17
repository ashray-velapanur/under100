<?php
/* @var $this Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

/** @var $connection Varien_Db_Adapter_Pdo_Mysql */
$connection = $installer->getConnection();

$connection->dropIndex($installer->getTable('gaussdev_comments/comment'),
    $installer->getIdxName('gaussdev_comments/comment', 'message', Varien_Db_Adapter_Interface::INDEX_TYPE_FULLTEXT));

$connection->changeTableEngine($installer->getTable('gaussdev_comments/comment'), 'InnoDB');

$connection->modifyColumn($installer->getTable('gaussdev_comments/comment'), 'customer_id', array(
    'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'unsigned' => true,
    'nullable' => true,
    'default'  => null,
));

$connection->addForeignKey($this->getFkName('gaussdev_comments/comment', 'product_id', 'catalog/product', 'entity_id'),
    $this->getTable('gaussdev_comments/comment'), 'product_id', $this->getTable('catalog/product'), 'entity_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE, true);
$connection->addForeignKey($this->getFkName('gaussdev_comments/comment', 'customer_id', 'customer/entity', 'entity_id'),
    $this->getTable('gaussdev_comments/comment'), 'customer_id', $this->getTable('customer/entity'), 'entity_id',
    Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE, true);
$connection->addForeignKey($this->getFkName('gaussdev_comments/comment', 'parent_id', 'gaussdev_comments/comment',
        'entity_id'), $this->getTable('gaussdev_comments/comment'), 'parent_id',
    $this->getTable('gaussdev_comments/comment'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);

$connection->addForeignKey($this->getFkName('gaussdev_comments/like', 'comment_id', 'gaussdev_comments/comment',
        'entity_id'), $this->getTable('gaussdev_comments/like'), 'comment_id',
    $this->getTable('gaussdev_comments/comment'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE, true);
$connection->addForeignKey($this->getFkName('gaussdev_comments/like', 'customer_id', 'customer/entity', 'entity_id'),
    $this->getTable('gaussdev_comments/like'), 'customer_id', $this->getTable('customer/entity'), 'entity_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE, true);
$connection->addIndex($this->getTable('gaussdev_comments/like'),
    $this->getIdxName('gaussdev_comments/like', array('customer_id', 'comment_id'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE), array('customer_id', 'comment_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE);

$installer->endSetup();
