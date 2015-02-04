<?php
class GaussDev_Like_Model_Api extends Mage_Api_Model_Resource_Abstract
{
	private $helper;

	//TODO: fix faults

	public function __construct(){
		$this->helper=Mage::helper('GaussDev_Like');

	}

	public function likeProduct($arg){
        $productID=$arg['productID'];
        $uid=$arg['uid'];
		if(!isset($productID)) return (array("error"=>"400")); //Malformed request.
        if(empty($productID)) return (array("error"=>"401"));;	//Empty product id
        if(!isset($uid)) return (array("error"=>"400")); //Malformed request.
        if(empty($uid)) return (array("error"=>"402"));	//Empty uid
        $response=$this->helper->addLike($productID, $uid);
        return $response;
	}


	public function newLikes($arg) {
        $uid=$arg['uid'];
        if(!isset($uid)) return (array("error"=>"400")); //Malformed request.
        if(empty($uid)) return (array("error"=>"402"));	//Empty uid
        $response=$this->helper->getNewLikes($uid);
        return $response;
	}

	public function getLikes($arg)
    {
        Mage::Log("incoming SOAP request:-->");
        Mage::Log($arg);
        if(!isset($arg['productID'])) return (array("error"=>"400")); //Malformed request.
        if(empty($arg['productID'])) return (array("error"=>"401"));;	//Empty request
        $output=array();
        foreach ($arg['productID'] as $productID){
        	$output[$productID]=$this->helper->countLikes($productID);
       	}
       	return $output;
	}


	public function getLiked($arg){
		if(!isset($arg['uid'])) return array(array("error"=>"400")); //Malformed request.
		if(empty($arg['uid'])) return array(array("error"=>"401"));	//Empty request
		$uid=$arg['uid'];
		return $this->helper->getLiked($uid);
	}

// 	public function info(){
//         return "place holder for info ....";
//     }

}