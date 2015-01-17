<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
CREATE TABLE IF NOT EXISTS `gaussdev_follow` (
  `id` int(10) unsigned NOT NULL COMMENT '4294967295 records max',
  `uid` int(10) unsigned NOT NULL COMMENT 'my uid',
  `follow_uid` int(10) unsigned NOT NULL COMMENT 'follow this uid',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 