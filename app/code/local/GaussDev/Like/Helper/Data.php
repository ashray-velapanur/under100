<?php

/**
 * Like - custom magnento module for Under100
 *
 * @author    seb
 * @copyright Sebastijan Placento - 9a3bsp@gmail.com , GaussDevelopment gauss-development.com
 */
class GaussDev_Like_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $connectionRead;
    private $connectionWrite;

    public function __construct()
    {
        $this->connectionRead = Mage::getSingleton('core/resource')->getConnection('core_read'); // instantiate read connection on start
    }

    //TODO: Escape SQL!!!

    public function getLiked($uid = null)
    {
        //if(is_array($uid)) $uid=$uid['uid'];
        if (empty($uid) || is_null($uid)) {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $session = Mage::getSingleton('customer/session');
                $uid = $session->getId();
            } else {
                return 403;
            }
        }
        if (!is_numeric($uid)) {
            return null;
        }
        $productIds = Mage::getResourceModel('catalog/product_collection')->addAttributeToFilter('status', '1')->getAllIds();
        $productIdsBind = '';
        $bind = array($uid);
        foreach ($productIds as $_pid) {
            $productIdsBind .= '?,';
            $bind[] = $_pid;
        }
        $productIdsBind = rtrim($productIdsBind, ',');

        $sql = "SELECT `productID` FROM `gaussdev_like` WHERE `uid`=? AND `productID` IN ({$productIdsBind})";
        $result = $this->connectionRead->fetchAll($sql, $bind);
        $response = array();
        foreach ($result as $id) {
            $product = Mage::getModel('catalog/product')->load($id);
            $response[] = array(
                    'product_id'         => $id,
                    'sku'                => $product->getSku(),
                    'name'               => $product->getName(),
                    'set'                => $product->getAttributeSetId(),
                    'type'               => $product->getTypeId(),
                    'category_ids'       => $product->getCategoryIds(),
                    'website_ids'        => $product->getWebsiteIds(),
                    'is_verified'        => (bool)$product->getIsVerified(),
                    'price'              => $product->getPrice(),
                    'description'        => $product->getDescription(),
                    'short_description'  => $product->getShortDescription(),
                    'image'              => $product->getImage(),
                    'small_image'        => $product->getSmallImage(),
                    'thumbnail'          => $product->getThumbnail(),
                    'product_origin_url' => $product->getProductOriginUrl(),
                    'brand'              => $product->getBrand(),
            );
        }
        return $response;
    }

    public function countLikes($productID)
    {
        $sql = "SELECT count(`id`) AS 'total' FROM `gaussdev_like` WHERE `productID`=?";
        $result = $this->connectionRead->fetchOne($sql, $productID);
        if (!$result || empty($result)) {
            return 0;
        } else {
            return $result;
        }
    }

    public function countAllLikes()
    {
        $sql = "SELECT count(`id`)  AS 'total',`productID` FROM `gaussdev_like` GROUP BY `productID` ORDER BY `total` DESC";
        $result = $this->connectionRead->fetchAll($sql);
        if (!$result || empty($result)) {
            return 0;
        } else {
            return $result;
        }
    }

    private function updateNewLikes($uid, $pid, $lid) {
        $timestamp = time();
        $sql = "INSERT INTO `beagles_new_likes`(`uid`, `lid`, `pid`,`timestamp`) VALUES (?,?,?,?)";
        $this->writeToDb($sql, true, array($uid, $lid, $pid, $timestamp));
    }


    public function getNewLikes($uid) {
        $sql = "SELECT * FROM `beagles_new_likes` WHERE `uid`=?";
        $newLikes = $this->connectionRead->fetchAll($sql, array($uid));
        Mage::log($newLikes);
        $response = array();
        foreach ($newLikes as $newLike) {
            Mage::log('... here');
            $timestamp = $newLike['timestamp'];
            $product = Mage::getModel('catalog/product')->load($newLike['pid']);
            $response[] = array(
                    'timestamp'=>$timestamp,
                    'product'=>array(
                    'product_id'         => $newLike['pid'],
                    'sku'                => $product->getSku(),
                    'name'               => $product->getName(),
                    'set'                => $product->getAttributeSetId(),
                    'type'               => $product->getTypeId(),
                    'category_ids'       => $product->getCategoryIds(),
                    'website_ids'        => $product->getWebsiteIds(),
                    'is_verified'        => (bool)$product->getIsVerified(),
                    'price'              => $product->getPrice(),
                    'description'        => $product->getDescription(),
                    'short_description'  => $product->getShortDescription(),
                    'image'              => $product->getImage(),
                    'small_image'        => $product->getSmallImage(),
                    'thumbnail'          => $product->getThumbnail(),
                    'product_origin_url' => $product->getProductOriginUrl(),
                    'brand'              => $product->getBrand(),
            ));
        }
        return $response;
    }

    public function resetNewLikes($uid) {
        $sql = "SELECT `count` FROM `new_likes` WHERE `uid`=?";
        $count = $this->connectionRead->fetchOne($sql, array($uid));
        if ($count) {
            $sql = "DELETE FROM `new_likes` WHERE `uid` = ?";
            $this->writeToDb($sql, false, array($uid));
            return array("success"=>"true");
        }
        return array("success"=>"false");
    }

    public function addLike($productID, $uid)
    {
        $sql = "SELECT `id` FROM `gaussdev_like` WHERE `productID`=? AND `uid`=?";
        $id = $this->connectionRead->fetchOne($sql, array($productID, $uid));
        if ($id) {
            return array("success"=>"false", "error"=>"Already Liked.");
        }
        $sql = "INSERT INTO `gaussdev_like`(`productID`, `uid`) VALUES (?,?)";
        $insertedId = $this->writeToDb($sql, true, array($productID, $uid));

        $ownerId = Mage::getModel('catalog/product')->load($productID)->getProductOwnerId();

        if ($ownerId){
            $this->updateNewLikes($ownerId, $productID, $uid);
        }

        try {
            Mage::getModel('notifications/notification')->setType('post_liked')->setNotifyId($ownerId)->setDataId($insertedId)->save();
        } catch (Exception $e) {
            sleep(1);
        }

        return array("success"=>"true");
    }

    public function checkLiked($productID, $uid = null)
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn() && !$uid) {
            $session = Mage::getSingleton('customer/session');
            $uid = $session->getId();
        }
        if (!$uid) {
            return null;
        }
        $sql = "SELECT `productID` FROM `gaussdev_like` WHERE `uid`= ? AND `productID`= ?";

        return (bool)$this->connectionRead->fetchOne($sql, array($uid, $productID));
    }

    private function writeToDb($sql, $isInsert = false, $bind = null)
    {  //we don't want to have checks all arround code. This is access function for DB which will be way simpler to use.
        if (empty($this->connectionWrite)) {
            $this->connectionWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        }
        $connection = $this->connectionWrite;
        if ($connection->query($sql, $bind)) {
            if ($isInsert) {
                return $connection->lastInsertId();
            }

            return true;
        } else {
            return false;
        }
    }
}
