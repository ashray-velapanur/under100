<?php
$installer = $this;

$installer->startSetup();

Mage::getModel('eav/entity_attribute')->loadByCode('customer_address', 'telephone')->setIsRequired(0)->save();

$installer->endSetup();