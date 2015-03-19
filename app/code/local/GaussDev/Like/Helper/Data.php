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
            $response[] = Mage::helper('gaussdev')->getProductDetails($product, $uid);
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
            $lid = $newLike['lid'];
            $pid = $newLike['pid'];
            $name = Mage::getModel('customer/customer')->load($lid)->getName();
            $timestamp = $newLike['timestamp'];
            $productName = Mage::getModel('catalog/product')->load($pid)->getName();
            $response[] = array("uid"=>$lid, "name"=>$name, "timestamp"=>$timestamp, "productName"=>$productName, "productId"=>$pid);
        }
        return $response;
    }

    public function clearNewLikes($uid) {
        $sql = "DELETE FROM `beagles_new_likes` WHERE `uid` = ?";
        $this->writeToDb($sql, false, array($uid));
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

        if ($ownerId AND $ownerId != $uid){
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
