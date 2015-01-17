<?php

class GaussDev_Comments_Model_Like extends Mage_Core_Model_Abstract
{

    public function loadByFilter($customerId, $commentId)
    {
        if (!$customerId || !$commentId) {
            return $this;
        }

        return $this->getCollection()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('comment_id', $commentId)
                    ->fetchItem() ?: $this;
    }

    public function getLikesByCustomerAndProduct($productId, $customerId)
    {
        $mageDB = Mage::getSingleton('core/resource');
        $select = Mage::getModel('gaussdev_comments/like')
                      ->getCollection()
                      ->getSelect()
                      ->reset(Zend_Db_Select::COLUMNS)
                      ->columns('comment_id')
                      ->join(array('comment' => $mageDB->getTableName('gaussdev_comments/comment')),
                          'comment.entity_id = main_table.comment_id', null)
                      ->where('comment.product_id = ?', $productId)
                      ->where('main_table.customer_id = ?', $customerId);

        return $select->query()->fetchAll(Zend_Db::FETCH_COLUMN);
    }

    protected function _construct()
    {
        $this->_init('gaussdev_comments/like');
    }
}