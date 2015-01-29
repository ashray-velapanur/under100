<?php
class GaussDev_Like_Model_Api extends Mage_Api_Model_Resource_Abstract
{
	private $helper;

	//TODO: fix faults

	public function __construct(){
		$this->helper=Mage::helper('GaussDev_Like');

	}

	public function likeProduct($arg){
		if(!isset($arg['productID'])) return (array("error"=>"400")); //Malformed request.
        if(empty($arg['productID'])) return (array("error"=>"401"));;	//Empty product id
        if(!isset($arg['uid'])) return (array("error"=>"400")); //Malformed request.
        if(empty($arg['uid'])) return (array("error"=>"402"));	//Empty uid
        $productID=$arg['productID'];
        $uid=$arg['uid'];
        $response=$this->helper->addLike($productID, $uid);
 if($response) return array("response"=>$this->helper->countLikes($productID)); else return array(array("error"=>"Product already liked."));


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