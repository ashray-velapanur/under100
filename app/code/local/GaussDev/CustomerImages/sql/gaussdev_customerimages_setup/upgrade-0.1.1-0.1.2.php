<?php
$installer = $this;

$installer->startSetup();

//username setup
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId = $setup->getEntityTypeId('customer');
$attributeSetId = $setup->getDefaultAttributeSetId($entityTypeId);

$attrCode = 'username';

$setup->addAttribute($entityTypeId, $attrCode, array(
    'type'             => 'varchar',
    'input'            => 'text',
    'label'            => 'Username',
    'global'           => true,
    'visible'          => true,
    'required'         => false,
    'default'          => '',
    'unique'           => true,
    'user_defined'     => true,
    'visible_on_front' => true,
));

$attribute = Mage::getSingleton("eav/config")->getAttribute($entityTypeId, $attrCode);

$setup->addAttributeToSet($entityTypeId, $attributeSetId, 'General', $attrCode);

$used_in_forms = array();

$used_in_forms = array(
    'adminhtml_customer',
    'checkout_register',
    'customer_account_create',
    'customer_account_edit'
);

$attribute->setData('used_in_forms', $used_in_forms)
          ->setData('is_used_for_customer_segment', true)
          ->setData('is_system', 0)
          ->setData('is_user_defined', 1)
          ->setData('is_visible', 1)
          ->setData('sort_order', 100);
$attribute->save();


//bio setup
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId = $setup->getEntityTypeId('customer');
$attributeSetId = $setup->getDefaultAttributeSetId($entityTypeId);


$attrCode = 'bio';

$setup->addAttribute($entityTypeId, $attrCode, array(
    'type'             => 'text',
    'input'            => 'textarea',
    'label'            => 'Bio',
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

$used_in_forms = array();

$used_in_forms = array(
    'adminhtml_customer',
    'checkout_register',
    'customer_account_create',
    'customer_account_edit'
);

$attribute->setData('used_in_forms', $used_in_forms)
          ->setData('is_used_for_customer_segment', true)
          ->setData('is_system', 0)
          ->setData('is_user_defined', 1)
          ->setData('is_visible', 1)
          ->setData('sort_order', 100);
$attribute->save();


$installer->endSetup();