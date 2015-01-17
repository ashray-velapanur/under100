<?php

class GaussDev_Comments_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param $productId
     *
     * @return int
     */
    public function commentCount($productId)
    {
        return Mage::getModel('gaussdev_comments/comment')
                   ->getCollection()
                   ->addFieldToFilter('product_id', $productId)
                   ->getSize();
    }

    /**
     * @param $productId
     * @param $customerId
     *
     * @return bool
     */
    public function isCommented($productId, $customerId)
    {
        return (bool)Mage::getModel('gaussdev_comments/comment')
                         ->getCollection()
                         ->addFieldToFilter('product_id', $productId)
                         ->addFieldToFilter('customer_id', $customerId)
                         ->getSize();
    }
}