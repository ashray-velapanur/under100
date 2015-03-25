<?php
class GaussDev_Follow_Model_Api extends Mage_Api_Model_Resource_Abstract
{
	private $helper;



	public function __construct(){
		$this->helper=Mage::helper('GaussDev_Follow');

	}


	public function newFollowers($arg){
		$uid = $arg['uid'];
        if(!isset($uid)) return (array("error"=>"400")); //Malformed request.
        if(empty($uid)) return(array("error"=>"402"));	//Empty uid
        $response=$this->helper->getNewFollowers($uid);
        return $response;
	}

	public function clearNewFollowers($arg){
		$uid = $arg['uid'];
        if(!isset($uid)) return (array("error"=>"400")); //Malformed request.
        if(empty($uid)) return(array("error"=>"402"));	//Empty uid
        $response=$this->helper->clearNewFollowers($uid);
        return $response;
	}
	
	public function getFollowers($arg){
        if(!isset($arg['uid'])) return (array("error"=>"400")); //Malformed request.
        if(empty($arg['uid'])) return(array("error"=>"402"));	//Empty uid
        $uid=$arg['uid'];
        $response=$this->helper->getFollowers($uid);
        return $response;
	}



	public function addFollower($arg){
		if(!isset($arg['uid'])) return (array("error"=>"400")); //Malformed request.
		if(empty($arg['uid'])) return (array("error"=>"402"));	//Empty uid
		if(!isset($arg['fuid'])) return (array("error"=>"400")); //Malformed request.
		if(empty($arg['fuid'])) return (array("error"=>"412"));	//Empty fuid
		$uid=$arg['uid'];
		$fuid=$arg['fuid'];
		$response=$this->helper->addFollower($uid,$fuid);
		return $response;
	}


	public function getFollowing($arg){
		if(!isset($arg['uid'])) return (array("error"=>"400")); //Malformed request.
		if(empty($arg['uid'])) return (array("error"=>"402"));	//Empty uid
		$uid=$arg['uid'];
		$response=$this->helper->getFollowing($uid);
		return $response;
	}

    public function info(){
    	return "place holder for info ....";
    }




    private function inArray($needle,&$haystack){
    	foreach ($haystack as $stack){
    		if($stack["follow_uid"]==$needle) return true;

    	}
    	return false;
    }
}