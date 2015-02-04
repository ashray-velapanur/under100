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

	public function resetNewFollowers($arg){
		$uid = $arg['uid'];
        if(!isset($uid)) return (array("error"=>"400")); //Malformed request.
        if(empty($uid)) return(array("error"=>"402"));	//Empty uid
        $response=$this->helper->resetNewFollowers($uid);
        return $response;
	}
	
	public function getFollowers($arg){
        if(!isset($arg['uid'])) return (array("error"=>"400")); //Malformed request.
        if(empty($arg['uid'])) return(array("error"=>"402"));	//Empty uid
        $uid=$arg['uid'];
        $response=$this->helper->getFollowers($uid);
        $followersCollection = Mage::getModel('customer/customer')->getCollection()
 			->addAttributeToFilter('entity_id', array('in' => $response))
			->addAttributeToSelect('*');

        $responseArray=array();

        $following=$response=$this->helper->getFollowing($uid);
        foreach ($followersCollection as $follower){
        	//if(in_array($follower->getID(), $following)) $following=1; else $following=0;
        	$responseArray[]=array("name" => $follower->getName(), "id"=>$follower->getId(),"image" => $follower->getProfileImage() ,"following"=>$this->inArray($follower->getId(), $following));
        }

        if($response) return array("response"=>$responseArray); else return array(array("error"=>"500"));
	}



	public function addFollower($arg){
		Mage::log('... here');
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
		$followersCollection = Mage::getModel('customer/customer')->getCollection()
 			->addAttributeToFilter('entity_id', array('in' => $response))
			->addAttributeToSelect('*');

        $responseArray=array();
        foreach ($followersCollection as $follower){
        	$responseArray[]=array("name" => $follower->getName(), "id"=>$follower->getId(),"image" => $follower->getProfileImage() );
        }
		if($response) return array("response"=>$responseArray); else  return (array("error"=>"500"));



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