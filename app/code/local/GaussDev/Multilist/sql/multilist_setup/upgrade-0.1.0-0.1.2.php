<?php
$installer = $this;

$installer->startSetup();

$installer->run('ALTER TABLE `gaussdev_lists` ADD `image` VARCHAR(255) NULL;');

$installer->endSetup(); 