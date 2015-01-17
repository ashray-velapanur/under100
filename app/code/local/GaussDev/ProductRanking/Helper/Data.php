<?php
class GaussDev_ProductRanking_Helper_Data extends Mage_Core_Helper_Abstract{
	private $startPrice=0;
	private $endPrice= 100;
	private $connectionWrite;
	private $connectionRead;

	public function __construct(){
		if(is_numeric($start = Mage::getSingleton('core/session')->getPriceStart())) $this->startPrice=$start;
		if(is_numeric($end = Mage::getSingleton('core/session')->getPriceEnd())) $this->endPrice=$end;
		$this->connectionRead=  Mage::getSingleton('core/resource')->getConnection('core_read'); // instantiate read connection on start
	}

	public function getProducts($page=1){
		//$this->calculateScore();
		// THIS IS PLACEHOLDER FOR FUTURE PRODUCT RANKINGS.
		$products = Mage::getModel('catalog/category')->load(2)
			->getProductCollection()
			->addAttributeToSelect('*') // add all attributes - optional
			->addAttributeToFilter('status', 1) // enabled
			->addAttributeToFilter('visibility', 4) //visibility in catalog,search
			->addAttributeToFilter('price',array ('from' => $this->startPrice, 'to' =>  $this->endPrice))
			->addAttributeToFilter('sku', array('nlike' => "%deal%"))
			->setOrder('popularityscore', 'DESC') //sets the order by price
			->setPageSize(18)
			->setCurPage($page);
		Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($products);
		return $products;
	}


	public function getRow($currentProduct){		//7
		$virtualBlock= floor($currentProduct/10);
		if($virtualBlock<1) {
			if($currentProduct<5) return 1;
			if($currentProduct<8) return 2;
			return 3;

		}else {
			$firstRowEnd=$virtualBlock*10+4;
			if($currentProduct<$firstRowEnd ) return 1;
			$firstRowEnd=$virtualBlock*10+4+3;
			if($currentProduct<$firstRowEnd ) return 2;
			$firstRowEnd=$virtualBlock*10+4+3+2;
			if($currentProduct<$firstRowEnd ) return 3;
		}
		return 1;
	}



	public function applyFilter(){
		if(isset($_POST['priceStart'])  && isset($_POST['priceEnd']) && !empty($_POST['priceEnd'])){
			$this->startPrice=Mage::app()->getRequest()->getPost('priceStart');
			$this->endPrice=Mage::app()->getRequest()->getPost('priceEnd');

			Mage::getSingleton('core/session')->setPriceStart($this->startPrice);
			Mage::getSingleton('core/session')->setPriceEnd($this->endPrice);




		}
		return array("start"=>$this->startPrice,"end"=>$this->endPrice);
	}


	public function calculateScore(){



		$totalPoints = array();


		$likes = Mage::helper("GaussDev_Like")->countAllLikes();
		$likesTotal=array();
		foreach ($likes as $like){
			$likesTotal[$like["productID"]]=$like['total']*2.5;
		}
		// OK :)



		$clicksTotal=array();
		$vigLocation=Mage::getBaseDir('var').DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."viglinks.csv";
		$vigFileHandle= fopen($vigLocation, "r");
		$first=true;
		while (($data = fgetcsv($vigFileHandle)) !== FALSE) {
			if($first){
				if($data[0] == "Outgoing Url") $first=false;
				continue;
			}

			$url = $data[0];
			if(empty($url)) continue;
			$clicks =   $data[4];
			$sql = "SELECT entity_id FROM `catalog_product_entity_text` WHERE `value` = ?" ;
			$result = $this->connectionRead->fetchOne($sql, $url);
			if(!$result || empty($result)) continue;
			$clicksTotal[$result] = $clicks * 3;
		}
		fclose($vigFileHandle);



		$commentsTotal=array();
		$sql = "SELECT count(`entity_id`) as comments,`product_id` FROM `gaussdev_comments` GROUP BY `product_id` ORDER BY comments DESC";
		$comments=  $this->connectionRead->fetchAll($sql) ;
		foreach ($comments as $comment){
			$commentsTotal[$comment['entity_id']]= $comment['comments'] * 1.5;
		}


		$lists= Mage::helper("GaussDev_Multilist")->countAllLists();
		$listsTotal=array();
		foreach ($lists as $list){
			$listsTotal[$list["productID"]]=$list['total'];
		}



		$totalPoints = $likesTotal;
		foreach ($clicksTotal as $productID => $points){
			if(empty($productID)) continue;
			if(isset($totalPoints[$productID])) $totalPoints[$productID] += $points; else $totalPoints[$productID]=$points;
		}
		foreach ($commentsTotal as $productID => $points){
			if(empty($productID)) continue;
			if(isset($totalPoints[$productID])) $totalPoints[$productID] += $points; else $totalPoints[$productID]=$points;
		}
		foreach ($listsTotal as $productID => $points){
			if(empty($productID)) continue;
			if(isset($totalPoints[$productID])) $totalPoints[$productID] += $points; else $totalPoints[$productID]=$points;
		}





		$products = Mage::getModel('catalog/category')->load(2)
		->getProductCollection()
		->addAttributeToSelect('id,popularityscore') // add all attributes - optional
		->addAttributeToFilter('status', 1) // enabled
		->addAttributeToFilter('visibility', 4) //visibility in catalog,search
		->addAttributeToFilter('price',array ('from' => $this->startPrice, 'to' =>  $this->endPrice))
		->addAttributeToFilter('sku', array('nlike' => "%deal%"));


		foreach ($products as $product){
			if(isset($totalPoints[$product->getId()])) {
				$product->setData('popularityscore', $totalPoints[$product->getId()]);
				$product->save();
			}
		}


		/*$pointsFileLocation=Mage::getBaseDir('var').DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."points.csv";
		$pointsFileHandle= fopen($pointsFileLocation, "w");
		foreach ($totalPoints as  $productID => $points){
			fputcsv($pointsFileHandle, array($productID,$points));
		}
		fclose($pointsFileHandle);*/
		return true;
	}
}
