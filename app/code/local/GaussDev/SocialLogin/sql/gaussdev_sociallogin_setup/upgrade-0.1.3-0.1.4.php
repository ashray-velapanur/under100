<?php
$installer = $this;

$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId = $setup->getEntityTypeId('customer');
$attributeSetId = $setup->getDefaultAttributeSetId($entityTypeId);

$attrCode = 'has_random_password';

$setup->addAttribute($entityTypeId, $attrCode, array(
    'type'             => 'int',
    'input'            => '',
    'label'            => '',
    'global'           => true,
    'visible'          => false,
    'required'         => false,
    'default'          => '0',
    'unique'           => false,
    'user_defined'     => true,
    'visible_on_front' => false,
));

$attribute = Mage::getSingleton("eav/config")->getAttribute($entityTypeId, $attrCode);

$setup->addAttributeToSet($entityTypeId, $attributeSetId, 'General', $attrCode);

$attribute->setData('is_system', 0)
          ->setData('is_user_defined', 1)
          ->setData('is_visible', 0)
          ->setData('sort_order', 100);
$attribute->save();

$customerIds = array_unique(Mage::getResourceSingleton('gaussdev_sociallogin/oauth_collection')
                                ->distinct(true)
                                ->addFieldToSelect('customer_id')
                                ->getColumnValues('customer_id'));
$customerCollection = Mage::getResourceSingleton('customer/customer_collection')
                          ->addAttributeToFilter('entity_id', array('in' => $customerIds));

foreach ($customerCollection as $_customer) {
    $cust = Mage::getModel('customer/customer')->load($_customer->getId())->setHasRandomPassword(1);
    $uniqueEmails[] = $cust->getEmail();
    if (!in_array($cust->getEmail(), $uniqueEmails)) {
        $cust->save();
    }
}


$installer->endSetup();