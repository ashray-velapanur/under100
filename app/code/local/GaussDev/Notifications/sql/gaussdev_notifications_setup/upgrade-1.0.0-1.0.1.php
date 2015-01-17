<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $installer->getTable('notifications/notification'),
    'rendered_text',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => false,
        'comment'  => 'Rendered Notification Text'
    )
);
$installer->getConnection()->addColumn(
    $installer->getTable('notifications/notification'),
    'rendered_image_url',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => false,
        'comment'  => 'Rendered Notification Image Url'
    )
);

foreach (Mage::getResourceModel('notifications/notification_collection') as $_notification) {
    $_notification->setRerenderData(1)->save();
}

$installer->endSetup();