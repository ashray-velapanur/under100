<?php

class GaussDev_Follow_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $connectionRead;
    private $connectionWrite;

    public function __construct()
    {
        $this->connectionRead = Mage::getSingleton('core/resource')->getConnection('core_read'); // instantiate read connection on start
    }

    public function getNewFollowers($uid) {
        $sql = "SELECT * FROM `beagles_new_followers` WHERE `uid`=?";
        $newFollowers = $this->connectionRead->fetchAll($sql, array($uid));
        $response = array();
        foreach ($newFollowers as $newFollower) {
            $fid = $newFollower['fid'];
            $name = Mage::getModel('customer/customer')->load($fid)->getName();
            $timestamp = $newFollower['timestamp'];
            $response[] = array("uid"=>$fid, "name"=>$name, "timestamp"=>$timestamp);
        }
        return $response;
    }

    public function clearNewFollowers($uid) {
        $sql = "DELETE FROM `beagles_new_followers` WHERE `uid` = ?";
        $this->writeToDb($sql, false, array($uid));
    }

    private function updateNewFollowers($uid, $fid) {
        $timestamp = time();
        $sql = "INSERT INTO `beagles_new_followers`(`uid`, `fid`, `timestamp`) VALUES (?,?,?)";
        $this->writeToDb($sql, true, array($uid, $fid, $timestamp));
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
        $this->updateNewFollowers($followUid, $uid);
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

        $response = array();

        foreach ($this->connectionRead->fetchAll($sql, $uid) as $follower) {
            $name = Mage::getModel('customer/customer')->load($follower['uid'])->getName();
            $response[] = array('uid'=>$follower['uid'], 'name'=>$name);
        }

        return $response;
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

        $response = array();

        foreach ($this->connectionRead->fetchAll($sql, $uid) as $follower) {
            $name = Mage::getModel('customer/customer')->load($follower['follow_uid'])->getName();
            $response[] = array('follow_uid'=>$follower['follow_uid'], 'name'=>$name);
        }

        return $response;
    }
}


