<?php
class GaussDev_Multilist_Model_Api extends Mage_Api_Model_Resource_Abstract
{
	private $helper;

	//TODO: fix faults

	public function __construct(){
		$this->helper=Mage::helper('GaussDev_Multilist');

	}


	public function getlistinfo($arg){
		if(!isset($arg['uid'])) return array(array("error"=>"400")); //Malformed request.
		if(empty($arg['uid'])) return array(array("error"=>"401"));	//Empty request
		$uid=$arg['uid'];
		$response= $this->helper->getListInfo($uid);
		foreach ($response as &$list){
			$list['count']= $this->helper->countListItems($list['id']);
			if(!isset($list['id'])  ){
				mage::log("error in multilistAPI :");
				mage::log($list);
			}
		}
		return array("response"=>$response);
	}


	public function createlist($arg){
		if(!isset($arg['uid'])) return array(array("error"=>"400")); //Malformed request.
		if(empty($arg['uid'])) return array(array("error"=>"401"));	//Empty request
		if(!isset($arg['name'])) return array(array("error"=>"402")); //Malformed request.
		if(empty($arg['name'])) return (array("error"=>"403"));;	//Empty product id

		$name=$arg['name'];
		$uid=$arg['uid'];
		$response =$this->helper->createList($uid,$name);
		if($response) return array("response"=>1,"id"=>$response); else  return (array("error"=>"500"));


	}

	public function uploadimage($arg){
		mage::log($arg);
		if(!isset($arg['uid'])) return array(array("error"=>"400")); //Malformed request.
		if(empty($arg['uid'])) return array(array("error"=>"401"));	//Empty value
		if(!isset($arg['image'])) return array(array("error"=>"400")); //Malformed request.
		if(empty($arg['image'])) return array(array("error"=>"402"));	//Empty value
		if(!isset($arg['listId'])) return array(array("error"=>"400")); //Malformed request.
		if(empty($arg['listId'])) return array(array("error"=>"403"));	//Empty value
		if(!isset($arg['mime_type'])) return array(array("error"=>"400")); //Malformed request.
		if(empty($arg['mime_type'])) return array(array("error"=>"404"));	//Empty value
		$listId=$arg['listId'];
		$uid=$arg['uid'];
		$image=$arg['image'];
		$mime_type=$arg['mime_type'];
		$userLists=$this->helper->getListInfo($uid);
		// CHECK user is real owner of list: (fast & stupid method):
		$valid=false;
		foreach ($userLists as $l){
			if($l['id']==$listId){
				$valid=true;
				break;
			}
		}
		if(!$valid) return (array("error"=>"500"));
		$response = $this->helper->changeImage($listId,base64_decode($image), $mime_type);
		if($response) return array("response"=>1,"image"=>$this->helper->imageFile); else  return (array("error"=>"500"));
	}




	public function editlist($arg){
		if(!isset($arg['uid'])) return array(array("error"=>"400")); //Malformed request.
		if(empty($arg['uid'])) return array(array("error"=>"401"));	//Empty request
		if(!isset($arg['name'])) return array(array("error"=>"400")); //Malformed request.
		if(empty($arg['name'])) return (array("error"=>"402"));;	//Empty product id
		if(!isset($arg['listId'])) return array(array("error"=>"400")); //Malformed request.
		if(empty($arg['listId'])) return array(array("error"=>"403"));	//Empty request
		$listId=$arg['listId'];
		$name=$arg['name'];
		$uid=$arg['uid'];
		$userLists=$this->helper->getListInfo($uid);
		// CHECK user is real owner of list: (fast & stupid method):
		$valid=false;
		foreach ($userLists as $l){
			if($l['id']==$listId){
				$valid=true;
				break;
			}
		}
		if(!$valid) return (array("error"=>"500"));
		// end check
		$response= $this->helper->editList($uid,$name,$listId);
		if($response) return array("response"=>1); else  return (array("error"=>"500"));
	}

	public function additem($arg){
		if(!isset($arg['listId'])) return array(array("error"=>"400")); //Malformed request.
		if(empty($arg['listId'])) return array(array("error"=>"401"));	//Empty request
		if(!isset($arg['itemId'])) return array(array("error"=>"402")); //Malformed request.
		if(empty($arg['itemId'])) return (array("error"=>"403"));;	//Empty product id

		$listId=$arg['listId'];
		$itemId=$arg['itemId'];

		$response= $this->helper->additem($listId,$itemId );
		if($response) return array("response"=>1); else  return (array("error"=>"500"));
	}


	public function getItems($arg){
		if(!isset($arg['listId'])) return array(array("error"=>"400")); //Malformed request.
		if(empty($arg['listId'])) return array(array("error"=>"401"));	//Empty request
		$listId=$arg['listId'];
		$response= $this->helper->getItems($listId );
		if($response) return array("response"=>$response); else  return (array("error"=>"500"));
	}

	public function getallitems($arg){
		$response=array();
		if(!isset($arg['listId'])) return array(array("error"=>"400")); //Malformed request.
		if(empty($arg['listId'])) return array(array("error"=>"401"));	//Empty request

		foreach($arg as $ar){
			$listId=$ar['listId'];
			$response[]= $this->helper->getItems($listId );
		}
		if($response) return array("response"=>$response); else  return (array("error"=>"500"));
	}


	public function deleteItem($arg){
		if(!isset($arg['listId'])) return array(array("error"=>"400")); //Malformed request.
		if(empty($arg['listId'])) return array(array("error"=>"401"));	//Empty request
		if(!isset($arg['itemId'])) return array(array("error"=>"402")); //Malformed request.
		if(empty($arg['itemId'])) return (array("error"=>"403"));;	//Empty product id
		$listId=$arg['listId'];
		$itemId=$arg['itemId'];
		$response= $this->helper->deleteItem($listId,$itemId );

		if($response) return array("response"=>1); else  return (array("error"=>"500"));
	}


	public function deleteList($arg){
		if(!isset($arg['uid'])) return array(array("error"=>"400")); //Malformed request.
		if(empty($arg['uid'])) return array(array("error"=>"401"));	//Empty request
		if(!isset($arg['listId'])) return array(array("error"=>"402")); //Malformed request.
		if(empty($arg['listId'])) return (array("error"=>"403"));;	//Empty product id
		$listId=$arg['listId'];
		$uid=$arg['uid'];
		$response= $this->helper->deleteList($uid,$listId );
		if($response) return array("response"=>1); else  return (array("error"=>"500"));


	}
}
