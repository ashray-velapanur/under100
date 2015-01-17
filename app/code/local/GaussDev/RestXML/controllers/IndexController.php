<?php
class GaussDev_RestXML_IndexController extends Mage_Core_Controller_Front_Action{
	private $url;
	
	public function _construct(){
		$this->url= Mage::getBaseUrl();
	}
		
	
	
	
	public function getImageAction(){
		$img=$this->getRequest()->getParam('img');
// 		Zend_Debug::dump($img);
		$_product = Mage::getModel('catalog/product')->load($img);
		//$url= urlencode(Mage::helper('catalog/image')->init($_product, 'image')->resize(600,640));
		$url=array();
		foreach ($_product->getMediaGalleryImages() as $image) {
			$url[]= urlencode(Mage::helper('catalog/image')->init($_product, "small_image",$image->getFile())->resize(700,700));
		}
		if(empty($url)){
			echo json_encode(array("url"=>""));
		} else {
			echo json_encode(array("url"=>$url));
		}
		
		
	}
	
    public function IndexAction() {
    	$params = array(
    			'siteUrl' => $this->url.'oauth',
    			'requestTokenUrl' => $this->url.'oauth/initiate', 
    			'accessTokenUrl' => $this->url.'oauth/token',
    			'authorizeUrl' => $this->url.'oauth/authorize',  //This URL is used only if we authenticate as Admin user type
    			'consumerKey' => 'c00951ba0c012546a8cb52793d35fe06', //Consumer key registered in server administration
    			'consumerSecret' => 'e790b284c8ad19db586b6ad08b60b855', //Consumer secret registered in server administration
    			'callbackUrl' => $this->url.'restxml/index/callback', //Url of callback action below
    	);
    	
    	 
    	$oAuthClient = Mage::getModel('gaussdev_restxml/oauth_client');
    	$oAuthClient->reset();
    	
    	$oAuthClient->init($params);
    	$oAuthClient->authenticate();	
    	return;
	  
    }
    
    
    public function callbackAction() {
    
    	$oAuthClient = Mage::getModel('gaussdev_restxml/oauth_client');
    	$params = $oAuthClient->getConfigFromSession();
    	$oAuthClient->init($params); 
    	$state = $oAuthClient->authenticate();
    
    	if ($state == GaussDev_RestXML_Model_OAuth_Client::OAUTH_STATE_ACCESS_TOKEN) {
    		$acessToken = $oAuthClient->getAuthorizedToken();
    	}
//     	if(!isset($acessToken)) echo 403;  return;
    	$restClient = $acessToken->getHttpClient($params);
    	// Set REST resource URL
    	$restClient->setUri($this->url.'api/rest/products');
    	// In Magento it is neccesary to set json or xml headers in order to work
    	$restClient->setHeaders('Accept', 'application/json');
    	// Get method
    	$restClient->setMethod(Zend_Http_Client::GET);
    	//Make REST request
    	$response = $restClient->request();
    	// Here we can see that response body contains json list of products
    	Zend_Debug::dump($response);
    
    	return;
    }
}