<?php
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

$foreignKeyName = $installer->getConnection()
                            ->getForeignKeyName($installer->getTable('gaussdev_sociallogin/oauth'), 'customer_id',
                                $installer->getTable('customer/entity'), 'entity_id');
$installer->getConnection()
          ->addForeignKey($foreignKeyName, $installer->getTable('gaussdev_sociallogin/oauth'), 'customer_id',
              $installer->getTable('customer/entity'), 'entity_id');

$installer->endSetup();
