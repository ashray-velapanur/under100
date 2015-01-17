<?php

/**
 * @method GaussDev_Comments_Model_Comment setEntityId(int $value)
 * @method int getCustomerId()
 * @method GaussDev_Comments_Model_Comment setCustomerId(int $value)
 * @method int getProductId()
 * @method GaussDev_Comments_Model_Comment setProductId(int $value)
 * @method int getParentId()
 * @method GaussDev_Comments_Model_Comment setParentId(int $value)
 * @method string getMessage()
 * @method GaussDev_Comments_Model_Comment setMessage(string $value)
 * @method int getReports()
 * @method GaussDev_Comments_Model_Comment setReports(int $value)
 * @method int getLikes()
 * @method GaussDev_Comments_Model_Comment setLikes(int $value)
 * @method string getCreatedAt()
 * @method GaussDev_Comments_Model_Comment setCreatedAt(string $value)
 * @method int getIsDeleted()
 * @method GaussDev_Comments_Model_Comment setIsDeleted(int $value)
 * @method int getIsSpam()
 * @method GaussDev_Comments_Model_Comment setIsSpam(int $value)
 */
class GaussDev_Comments_Model_Comment extends Mage_Core_Model_Abstract
{

    const SORT_OLDEST = 'oldest';
    const SORT_BEST = 'best';

    public function incrementLikes()
    {
        $oldLikes = (int)$this->getLikes();
        $this->setLikes($oldLikes + 1);

        return $this;
    }

    public function incrementReports()
    {
        $oldReports = (int)$this->getReports();
        $this->setReports($oldReports + 1);

        return $this;
    }

    public function decrementLikes()
    {
        $oldLikes = (int)$this->getLikes();
        $this->setLikes($oldLikes - 1);

        return $this;
    }

    public function getSortedCommentsData($productId, $customerId, $sort = '', $curPage = 1, $pageSize = 20)
    {
        Varien_Profiler::start(__METHOD__);

        switch (strtolower($sort)) {
            case self::SORT_OLDEST:
                $attr = 'created_at';
                $order = Varien_Data_Collection::SORT_ORDER_ASC;
                break;
            case self::SORT_BEST:
                $attr = 'likes';
                $order = Varien_Data_Collection::SORT_ORDER_DESC;
                break;
            default:
                $attr = 'created_at';
                $order = Varien_Data_Collection::SORT_ORDER_DESC;
        }

        $collection = Mage::getModel('gaussdev_comments/comment')
                          ->getCollection()
                          ->setLoadRepliesCollection(true)
                          ->getCustomerLiked($customerId)
                          ->addOrder($attr, $order)
                          ->addFieldToFilter('product_id', $productId)
                          ->addFieldToFilter('parent_id', array('null' => true))
                          ->setCurPage($curPage)
                          ->setPageSize($pageSize);

        Varien_Profiler::stop(__METHOD__);

        return $collection;
    }

    protected function _construct()
    {
        $this->_init('gaussdev_comments/comment');
    }

    protected function _beforeSave()
    {
        if (!$this->getId()) {
            $this->setCreatedAt(date(Varien_Db_Adapter_Pdo_Mysql::TIMESTAMP_FORMAT));
        }
        if ($this->getMessage()) {
            $this->setMessage(strip_tags($this->getMessage()));
        }
        $this->setParentId($this->getParentId() ?: new Zend_Db_Expr('NULL'));
        parent::_beforeSave();
    }

    public function afterLoad($args = array())
    {
        $loadLiked = isset($args['getCustomerLiked']) && is_numeric($args['getCustomerLiked']);
        parent::afterLoad();
        $tags = Mage::getResourceModel('gaussdev_comments/tag_collection')->addFieldToFilter('comment_id', $this->getId());
        $this->setData('tags', $tags);
        if (isset($args['loadRepliesCollection']) && $args['loadRepliesCollection'] == true) {
            $replies = Mage::getModel('gaussdev_comments/comment')
                           ->getCollection()
                           ->getCustomerLiked($loadLiked ? $args['getCustomerLiked'] : false)
                           ->addOrder('created_at', Varien_Data_Collection::SORT_ORDER_DESC)
                           ->addFieldToFilter('parent_id', $this->getId());
            $this->setData('replies', $replies);
        }
        $this->setProfileImage(Mage::helper('gaussdev_customerimages')->getUrl($this->getCustomerId()));
        if ($loadLiked) {
            if (Mage::registry('currentCustomerLikedComments') === null) {
                Mage::register('currentCustomerLikedComments',
                               Mage::getModel('gaussdev_comments/like')->getLikesByCustomerAndProduct($this->getProductId(),
                                                                                                      $args['getCustomerLiked']));
            }
            if (in_array($this->getId(), Mage::registry('currentCustomerLikedComments'))) {
                $this->setCurrentCustomer(array('liked' => true));
            } else {
                $this->setCurrentCustomer(array('liked' => false));
            }
        }
    }

    public function _afterSave()
    {
        try {
            if ($this->getParentId()) {
                $notifyId = Mage::getModel('gaussdev_comments/comment')->load($this->getParentId())->getCustomerId();
                Mage::getModel('notifications/notification')
                    ->setType('comment_replied')
                    ->setNotifyId($notifyId)
                    ->setDataId($this->getId())
                    ->save();
            }
        } catch (Exception $e) {
        }
        try {
            $notifyId = Mage::getModel('catalog/product')->load($this->getProductId())->getProductOwnerId();
            if ($notifyId) {
                Mage::getModel('notifications/notification')
                    ->setType('post_commented')
                    ->setNotifyId($notifyId)
                    ->setDataId($this->getId())
                    ->save();
            }
        } catch (Exception $e) {
        }

        return parent::_afterSave();
    }

    public function toArray(array $arrAttributes = array())
    {
        $arrAttributes = array('tags', 'replies');
        foreach ($arrAttributes as $attribute) {
            if (isset($this->_data[$attribute]) && is_object($this->_data[$attribute])
                && method_exists($this->_data[$attribute], 'toArray')
            ) {
                $this->_data[$attribute] = $this->_data[$attribute]->toArray();
            }
        }

        return $this->getData();
    }
}