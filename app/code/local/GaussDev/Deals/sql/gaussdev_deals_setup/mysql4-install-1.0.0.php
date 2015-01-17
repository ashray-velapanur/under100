<?php
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$entityTypeId = $setup->getEntityTypeId('catalog_category');
$attributeSetId = $setup->getDefaultAttributeSetId($entityTypeId);

$setup->addAttributeGroup($entityTypeId, $attributeSetId, 'Deals');
$attributeGroup = $setup->getAttributeGroup($entityTypeId, $attributeSetId, 'Deals');
$attributeGroupId = $attributeGroup['attribute_group_id'];

$attrCode = 'deal_image';
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('catalog_category', $attrCode, array(
    'type'             => 'varchar',
    'input'            => 'image',
    'backend'          => 'catalog/category_attribute_backend_image',
    'group'            => 'Deals',
    'label'            => 'Deal Image',
    'visible'          => 1,
    'required'         => 0,
    'user_defined'     => 1,
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible_on_front' => 1,
));
$setup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attrCode);

$attrCode2 = 'deal_mobile_image';
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('catalog_category', $attrCode2, array(
    'type'             => 'varchar',
    'input'            => 'image',
    'backend'          => 'catalog/category_attribute_backend_image',
    'group'            => 'Deals',
    'label'            => 'Deal Mobile Image',
    'visible'          => 1,
    'required'         => 0,
    'user_defined'     => 1,
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible_on_front' => 1,
));
$setup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attrCode2);

$installer->endSetup();
