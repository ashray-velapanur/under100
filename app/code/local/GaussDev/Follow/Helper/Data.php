<?php

class GaussDev_Follow_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $connectionRead;
    private $connectionWrite;

    public function __construct()
    {
        $this->connectionRead = Mage::getSingleton('core/resource')->getConnection('core_read'); // instantiate read connection on start
    }

    public function addFollower($uid, $followUid)
    {
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
        $sql = "SELECT `id` FROM `gaussdev_follow` WHERE `follow_uid`=? AND `uid`=?";
        $id = $this->connectionRead->fetchOne($sql,array($followUid, $uid));
        if ($id != false) {
            $sql = "DELETE FROM `gaussdev_follow` WHERE `id`=?";

            return $this->writeToDb($sql, false, $id);
        }
        $sql = "INSERT INTO `gaussdev_follow`(`follow_uid`, `uid`) VALUES (?,?)";

        $insertedId = $this->writeToDb($sql, true, array($followUid, $uid));

        try {
            Mage::getModel('notifications/notification')->setType('user_followed')->setNotifyId($followUid)->setDataId($insertedId)->save();
        } catch (Exception $e) {
        }

        return $insertedId;
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


