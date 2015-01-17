<?php

$setup = new Mage_Catalog_Model_Resource_Setup('core_setup');
$attr = array(
    'attribute_model'            => null,
    'backend'                    => null,
    'type'                       => 'text',
    'table'                      => null,
    'frontend'                   => null,
    'input'                      => 'text',
    'label'                      => 'Original Product URL',
    'frontend_class'             => 'validate-url',
    'source'                     => null,
    'required'                   => '0',
    'user_defined'               => '1',
    'default'                    => null,
    'unique'                     => '0',
    'note'                       => null,
    'input_renderer'             => null,
    'global'                     => '1',
    'visible'                    => '1',
    'searchable'                 => '0',
    'filterable'                 => '0',
    'comparable'                 => '0',
    'visible_on_front'           => '0',
    'is_html_allowed_on_front'   => '0',
    'is_used_for_price_rules'    => '0',
    'filterable_in_search'       => '0',
    'used_in_product_listing'    => '0',
    'used_for_sort_by'           => '0',
    'is_configurable'            => '0',
    'apply_to'                   => null,
    'visible_in_advanced_search' => '0',
    'position'                   => '0',
    'wysiwyg_enabled'            => '0',
    'used_for_promo_rules'       => '0',
    'option'                     => array('values' => array(),),
);
$setup->addAttribute('catalog_product', 'product_origin_url', $attr);
$attribute = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'product_origin_url');
$attribute->setStoreLabels(array());
$attribute->save();
