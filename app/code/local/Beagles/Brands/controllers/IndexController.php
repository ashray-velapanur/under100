<?php
class Beagles_Brands_IndexController extends Mage_Core_Controller_Front_Action {        

    public function indexAction() {
        var_dump(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA));
    }

}