<?php
$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');


$prodEntityTypeId = $setup->getEntityTypeId('catalog_product');
$setup->updateAttribute($prodEntityTypeId, 'created_at', 'frontend_label', 'Date');
$setup->updateAttribute($prodEntityTypeId, 'created_at', 'used_for_sort_by', 1);

$installer->endSetup();
