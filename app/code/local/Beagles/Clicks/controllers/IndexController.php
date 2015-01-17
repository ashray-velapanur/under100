<?php
class Beagles_Clicks_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
    	echo '... working';
	}

    public function testModelAction() {
        echo 'Setup!';
        $auth = Mage::getModel('clicks/clicks');
        $auth->load('10');
        $data = $auth->getData();
        echo $data;
        Mage::log($data);

    }
}
