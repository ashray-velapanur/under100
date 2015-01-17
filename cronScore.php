<?php
include "app/Mage.php";
mage::app();
$productModel = Mage::getModel('GaussDev_ProductRanking_Model_Standard');
$productModel->cron();