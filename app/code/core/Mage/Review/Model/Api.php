<?php
class Mage_Review_Model_Api extends Mage_Api_Model_Resource_Abstract{
 	public function info(){
         return 
         "
         		Reviews API. Methods: \n
         		review.getreview : parameter productID (int id)\n
         ";
     }
     
     
    public function getReview($arg){
    	if(!isset($arg['productID'])) return (array("error"=>"400")); //Malformed request.
    	if(empty($arg['productID'])) return (array("error"=>"401"));;	//Empty request
    	$productID=$arg['productID'];
    	$_product = Mage::getModel('catalog/product')->load($productID);  
    	
    	$summaryData = Mage::getModel('review/review_summary')
	    	->setStoreId($storeId)
	    	->load($productID);
    	$data= $summaryData->getData();
    	if(!isset($data['reviews_count']) || empty($data['reviews_count']) )  return array(array("error"=>"500")); // return standard GaussDev error ID
    		
    		
    	$entityPKValue=$data["entity_pk_value"];
    	$reviewcollection = Mage::getModel('review/review')->getCollection()
	    		->addStoreFilter(Mage::app()->getStore()->getId())
	    		->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
	    		->addFieldToFilter('entity_id', Mage_Review_Model_Review::ENTITY_PRODUCT)
	    		->addFieldToFilter('entity_pk_value', array('in' => $entityPKValue))
	    		->setDateOrder()
	    		->addRateVotes()
	    		;
    		
    	$_items = $reviewcollection->getItems();
    	$comments=array();
    	$voteScore=0;
    	$i=0;
    	foreach ($_items as $item){
    		$voteObj=$item->getData();
    		$voteArr=$voteObj['rating_votes']->getData();
    		
    		$voteDetail=$item->getOrigdata();
    		$voteDetail['score']=$voteArr[0]['value'];
    		$comments[]=$voteDetail;
    		
    		$voteScore += $voteArr[0]['value'];
    		$i++;
    	}
    	
    	
    	
    	$reviewscount=$i;
    	$reviewsPercent=$voteScore/$i;
    	 
		$result=array("count"=>$reviewscount, "score"=>$reviewsPercent,"comments"=>$comments) ;		
		return array("response"=>$result); 
    }

}