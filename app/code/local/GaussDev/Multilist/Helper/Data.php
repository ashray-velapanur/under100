<?php
/**
 * MultiList - custom magnento module for Under100
 *
 * @author seb
 * @copyright Sebastijan Placento - 9a3bsp@gmail.com , GaussDevelopment gauss-development.com
 */
class GaussDev_Multilist_Helper_Data extends Mage_Core_Helper_Abstract
{
	private $connectionRead;
	private $connectionWrite;

	public $imageFile;


	//TODO: Escape SQL!!!

	public function __construct(){
		$this->connectionRead=  Mage::getSingleton('core/resource')->getConnection('core_read'); // instantiate read connection on start
	}

	public function getLists($uid) {
        $sqlLists = "SELECT * FROM `gaussdev_lists` WHERE `uid`=?";
        $lists = $this->connectionRead->fetchAll($sqlLists, array($uid));
        $response = array();
        foreach ($lists as $list) {
        	$name = $list['name'];
        	$id = $list['id'];
        	$image = $list['image'];
        	$response[] = array('id'=>$id, 'name'=>$name, 'image'=>$image);
        }
        return $response;
	}

	public function countLists($uid=null){
		if(empty($uid) || is_null($uid) ){
			if(Mage::getSingleton('customer/session')->isLoggedIn()){
				$session = Mage::getSingleton('customer/session');
				$uid= $session->getId();
			} else return 403;
		}
		if(!is_numeric($uid)) return null;
		$sql = "SELECT count(`id`) as 'total' FROM `gaussdev_lists` WHERE `uid`=?";
		$result = $this->connectionRead->fetchOne($sql,$uid);
		if(!$result || empty($result)) return 0; else return $result;

	}

    public function getNewListAdds($uid) {
        $sql = "SELECT * FROM `beagles_new_list_adds` WHERE `uid`=?";
        $newListAdds = $this->connectionRead->fetchAll($sql, array($uid));
        $response = array();
        foreach ($newListAdds as $newListAdd) {
	        $sqlLists = "SELECT `uid` FROM `gaussdev_lists` WHERE `id`=?";
	        $uidList = $this->connectionRead->fetchOne($sqlLists, array($newListAdd['lid']));
            $name = Mage::getModel('customer/customer')->load($uidList)->getName();
            $timestamp = $newListAdd['timestamp'];
            $productName = Mage::getModel('catalog/product')->load($newListAdd['pid'])->getName();
            $response[] = array("uid"=>$uidList, "name"=>$name, "timestamp"=>$timestamp, "productName"=>$productName);
        }
        return $response;
    }

	public function getAddedToList($uid) {
        $sql = "SELECT `count` FROM `new_list_adds` WHERE `uid`=?";
        $count = $this->connectionRead->fetchOne($sql, array($uid));
        if ($count) {
            return array("newListAdds"=>$count);
        } else {
            return array("newListAdds"=>0);
        }
	}

	public function clearAddedToList($uid) {
        $sql = "DELETE FROM `beagles_new_list_adds` WHERE `uid` = ?";
        $this->writeToDb($sql, false, array($uid));
	}

	public function countListItems($listID){
		$sql = "SELECT count(`id`) as 'total' FROM `gaussdev_listsItems` WHERE `list_fk`=?";
		$result = $this->connectionRead->fetchOne($sql,$listID);
		if(!$result || empty($result)) return 0; else return $result;
	}

	public function getItemLists($uid=NULL,$itemID){
		if(empty($uid) || is_null($uid) ){
			if(Mage::getSingleton('customer/session')->isLoggedIn()){
				$session = Mage::getSingleton('customer/session');
				$uid= $session->getId();
			} else return 403;
		}
		if(!is_numeric($uid)) return null;
		if(empty($itemID) || !is_numeric($itemID)) return null;
		$sql = "SELECT *
				FROM `gaussdev_lists` l
				JOIN  `gaussdev_listsItems` li
				ON li.`list_fk` = l.`id`
				WHERE li.`productID` = ? AND l.`uid` = ?";
		return $this->connectionRead->fetchAll($sql,array($itemID,$uid));

	}

    private function updateListAdds($uid, $pid, $lid) {
        $timestamp = time();
        $sql = "INSERT INTO `beagles_new_list_adds`(`uid`, `lid`, `pid`,`timestamp`) VALUES (?,?,?,?)";
        $this->writeToDb($sql, true, array($uid, $lid, $pid, $timestamp));
    }


	public function additem($listId, $itemId){
		if(empty($listId) || empty($itemId)) return false;
		$sql = "INSERT INTO `gaussdev_listsItems`( `list_fk`, `productID`) VALUES (?,?)" ;

        $ownerId = Mage::getModel('catalog/product')->load($itemId)->getProductOwnerId();
        if ($ownerId) {
        	$this->updateListAdds($ownerId, $itemId, $listId);
        }

		return $this->writeToDb($sql,true,array($listId, $itemId));

	}


	public function flashItem($item){
		if(empty($uid) || is_null($uid) ){
			if(Mage::getSingleton('customer/session')->isLoggedIn()){
				$session = Mage::getSingleton('customer/session');
				$uid= $session->getId();
			} else return 403;
		}
		if(!is_numeric($uid)) return null;
		if(empty($item) || !is_numeric($item)) return null;
		$lists=$this->getItemLists($uid,$item);
		foreach ($lists as $list){
			$this->deleteItem($list['list_fk'], $item);
		}
		return true;
	}


	public function createList($uid,$name){
		if(empty($uid) || empty($name)) return false;
		$sql = "INSERT INTO `gaussdev_lists`( `uid`, `name`) VALUES (?,?)";
		return $this->writeToDb($sql,true,array($uid,strip_tags($name)));
	}


	public function editList($uid,$name,$listID){
		if(empty($name) || empty($listID) ) return false;
		if(empty($uid) || is_null($uid) ){
			if(Mage::getSingleton('customer/session')->isLoggedIn()){
				$session = Mage::getSingleton('customer/session');
				$uid= $session->getId();
			} else return 403;
		}
		if(!is_numeric($uid) || !is_numeric($listID) ) return null;
		$sql = "UPDATE `gaussdev_lists` SET `name`=? WHERE `id`=?";
		return $this->writeToDb($sql,false,array(strip_tags($name),$listID));
	}

	public function getListInfo($uid=NULL){
		if(empty($uid) || is_null($uid) ){
			if(Mage::getSingleton('customer/session')->isLoggedIn()){
				$session = Mage::getSingleton('customer/session');
				$uid= $session->getId();
			} else return 403;
		}
		if(!is_numeric($uid)) return null;
		$sql = "SELECT * FROM `gaussdev_lists` WHERE `uid`= ?";
		$result = $this->connectionRead->fetchAll($sql,$uid);
		return $result;
	}

	public function getItems($listID){
		if(empty($listID)) return 400;
		if(!is_numeric($listID)) return null;
        $productIds = Mage::getResourceModel('catalog/product_collection')->addAttributeToFilter('status', '1')->getAllIds();
        $productIdsBind = '';
        $bind = array($listID);
        foreach ($productIds as $_pid) {
            $productIdsBind .= '?,';
            $bind[] = $_pid;
        }
		$sql = "SELECT * FROM `gaussdev_listsItems` WHERE `list_fk`= ? AND `productID` IN ({$productIdsBind})";
		$result = $this->connectionRead->fetchAll($sql,$bind);
		return $result;
	}

	public function getListProductIds($listID){
		if(empty($listID)) return 400;
		if(!is_numeric($listID)) return null;
		$sql = "SELECT productID FROM `gaussdev_listsItems` WHERE `list_fk`= ?";
		$result = $this->connectionRead->fetchCol($sql,$listID);
		return $result;
	}


	public function deleteItem($listID, $item){
		if(empty($listID) || empty($item)) return false;
		$sql = "DELETE FROM `gaussdev_listsItems` WHERE `list_fk` = ? AND  `productID` = ?";
		return $this->writeToDb($sql,false,array($listID, $item));
	}

	public function deleteList($uid, $listID){
		if(empty($uid) || empty($listID)) return false;
		$sql = "DELETE FROM `gaussdev_lists` WHERE `uid` = ? AND  `id` = ?";
		return $this->writeToDb($sql,false,array($uid,$listID));
	}


	private function writeToDb($sql,$isInsert=false,$binds=array()){  //we don't want to have checks all arround code. This is access function for DB which will be way simpler to use.
		if(empty($this->connectionWrite)) {
			$this->connectionWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
		}
		$connection = $this->connectionWrite;
		if($connection->query($sql, $binds)) {
			if($isInsert) return $connection->lastInsertId();
			return true;
		} else return false;
	}

	/**
	 * @param $listId
	 * @param $fileContent (file_get_contents sa weba ili base64_decode mobilni request)
	 * @param $mime
	 *
	 * @return bool
	 * @throws Exception
	 */
    public function changeImage($listId, $fileContent, $mime)
	{
		$mimeTypes = array('image/jpeg' => 'jpg', 'image/gif' => 'gif', 'image/png' => 'png');
		if (!$listId || !$fileContent || !array_key_exists($mime, $mimeTypes)) {
			return false;
		}
		do {
			$fileName = base_convert(md5(uniqid() . mt_rand()), 16, 35) . '.' . $mimeTypes[strtolower($mime)];
			$attrPath = Mage::getBaseDir('media') . DS . 'multilist';
			$path = $attrPath . Varien_File_Uploader::getDispretionPath($fileName);
			$file = $path . DS . $fileName;
		} while (file_exists($file));

		$ioAdapter = new Varien_Io_File();
		$ioAdapter->checkAndCreateFolder($path);
		$ioAdapter->open(array('path' => $path));
		$ioAdapter->write($fileName, $fileContent, 0666);
		unset($fileContent);
		try {
			new Varien_Image($file);
		} catch (Exception $e) {
			$ioAdapter->rm($path);
			throw $e;
		}
		$sql = "SELECT `image` FROM `gaussdev_lists` WHERE `id`= ?";
		$original = $this->connectionRead->fetchOne($sql,$listId);
		$toDelete = (bool)$original;

		if ($toDelete) {
			$oldFile = $attrPath . $original;
			$ioFile = new Varien_Io_File();
			if ($ioFile->fileExists($oldFile)) {
				$ioFile->rm($oldFile);
			}
		}
		$imageFile = str_replace($attrPath, '', $file);
		$sql = "UPDATE `gaussdev_lists` SET `image`=? WHERE `id`=?;";
		$this->imageFile=$imageFile;
		return $this->writeToDb($sql,false,array($imageFile,$listId));
	}

	public function getImageUrl($path)
	{
		$placeholder = Mage::getDesign()->getSkinUrl('images/placeholders/list-placeholder-183.jpg');
		if (empty($path)) {
			return $placeholder;
		} else {
			$fullpath = Mage::getBaseDir('media') . DS . 'multilist' . $path;
			if (!file_exists($fullpath)) {
				return $placeholder;
			}
			$url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'multilist' . $path;

			return $url;
		}
	}

	/**
	 * @return product id with lists IDs.
	 */
	public function countAllLists(){
		$sql = "SELECT count(`list_fk`)  as 'total',productID FROM `gaussdev_listsItems` GROUP BY productID ORDER BY total DESC";
		$result = $this->connectionRead->fetchAll($sql);
		if(!$result || empty($result)) return 0; else return $result;
	}
}
