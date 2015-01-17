<?php
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()->dropColumn($installer->getTable('gaussdev_parser/host'), 'name_xpath');

$installer->endSetup();