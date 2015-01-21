<?php
class Beagles_Rewards_IndexController extends Mage_Core_Controller_Front_Action {        

    public function indexAction() {

        echo 'Hello Index!';
        $date = new Zend_Date("2010-12-06 08:20:15");
        var_dump($date);
    }

    public function updateAction() {
        $pid = intval($this->getRequest()->getPost('pid'));
        $uid = intval($this->getRequest()->getPost('uid'));
        $url = strval($this->getRequest()->getPost('url'));
        Mage::log('... here');
        Mage::log($url);
        if (!$pid || !$uid) {
            return;
        }
        $currTimestamp = strval(time());
        $clicks = Mage::getModel('rewards/clicks');
        $clicks->setPid($pid);
        $clicks->setUid($uid);
        $clicks->setUrl($url);
        $clicks->setDatetime($currTimestamp);
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