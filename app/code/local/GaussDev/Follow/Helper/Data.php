<?php

class GaussDev_Follow_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $connectionRead;
    private $connectionWrite;

    public function __construct()
    {
        $this->connectionRead = Mage::getSingleton('core/resource')->getConnection('core_read'); // instantiate read connection on start
    }

    private function updateNewFollowerCount($uid) {
        Mage::log('updating new follower count');
        $sql = "SELECT `count` FROM `new_followers` WHERE `uid`=?";
        $count = $this->connectionRead->fetchOne($sql, array($uid));
        if (!$count){
            $sql = "INSERT INTO `new_followers`(`uid`, `count`) VALUES (?,?)";
            $this->writeToDb($sql, true, array($uid, 1));
        } else {
            $sql = "UPDATE `new_followers` SET `count`=? WHERE `uid`=?";
            $this->writeToDb($sql, true, array($count + 1, $uid));
        }
    }

    public function addFollower($uid, $followUid)
    {
        if (empty($uid) || is_null($uid)) {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $session = Mage::getSingleton('customer/session');
                $uid = $session->getId();
            } else {
                return array("success"=>"false", "error"=>"Please Login.");
            }
        }
        if (!is_numeric($uid)) {
            return array("success"=>"false", "error"=>"Invalid User ID.");
        }
        $sql = "SELECT `id` FROM `gaussdev_follow` WHERE `follow_uid`=? AND `uid`=?";
        $id = $this->connectionRead->fetchOne($sql,array($followUid, $uid));
        if ($id) {
            return array("success"=>"false", "error"=>"Already Following.");
        }

        $sql = "INSERT INTO `gaussdev_follow`(`follow_uid`, `uid`) VALUES (?,?)";

        $insertedId = $this->writeToDb($sql, true, array($followUid, $uid));
        $this->updateNewFollowerCount($uid);
        try {
            Mage::getModel('notifications/notification')->setType('user_followed')->setNotifyId($followUid)->setDataId($insertedId)->save();
        } catch (Exception $e) {
        }

        return array("success"=>"true");
    }

    private function writeToDb($sql, $isInsert = false, $bind=null)
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

    public function getFollowers($uid = '')
    {
        if (empty($uid)) {
            $uid = Mage::getSingleton('customer/session')->getCustomerId();
        }
        if (empty($uid)) {
            die("Error getting user.");
        }
        $sql = "SELECT `uid` FROM `gaussdev_follow` WHERE `follow_uid`=?";

        return $this->connectionRead->fetchAll($sql, $uid);
    }

    public function getFollowing($uid = '')
    {
        if (empty($uid)) {
            $uid = Mage::getSingleton('customer/session')->getCustomerId();
        }
        if (empty($uid)) {
            die("Error getting user.");
        }
        $sql = "SELECT `follow_uid` FROM `gaussdev_follow` WHERE `uid`=?";

        return $this->connectionRead->fetchAll($sql, $uid);
    }
}


