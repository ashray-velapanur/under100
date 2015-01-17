<?php
$installer = $this;

$installer->startSetup();

//username setup
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId = $setup->getEntityTypeId('customer_address');
$attributeSetId = $setup->getDefaultAttributeSetId($entityTypeId);

$attrCode = 'address_name';

$setup->addAttribute($entityTypeId, $attrCode, array(
    'type'             => 'varchar',
    'input'            => 'text',
    'label'            => 'Address Alias',
    'global'           => true,
    'visible'          => true,
    'required'         => false,
    'default'          => '',
    'unique'           => false,
    'user_defined'     => true,
    'visible_on_front' => true,
));

$attribute = Mage::getSingleton("eav/config")->getAttribute($entityTypeId, $attrCode);

$setup->addAttributeToSet($entityTypeId, $attributeSetId, 'General', $attrCode);

$attribute->save();

$installer->endSetup();