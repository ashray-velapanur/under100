<?php
class Beagles_Rewards_IndexController extends Mage_Core_Controller_Front_Action {        

    public function indexAction() {

        echo 'Hello Index!';

    }

    public function updateAction() {
        $pid = intval($this->getRequest()->getPost('pid'));
        if (!$pid) {
            return;
        }
        $clicks = Mage::getModel('rewards/clicks')->load($pid);
        if (!$clicks->getData()) {
        	$clicks->setId($pid);
        	$clicks->setCount(1);
        } else {
	        $curr_count = $clicks->getData('count');
	        $clicks->setCount($curr_count + 1);
        }
 		$clicks->save();
    }

	public function testModelAction() {
	    $blogpost = Mage::getModel('rewards/clicks');
        $pid = intval($this->getRequest()->getParam('pid'));
	    $blogpost->load($pid);
	    $data = $blogpost->getData();
	    if (!$data) {
	    	echo 'no data';
	    }
	    var_dump($data);
	}
}