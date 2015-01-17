<?php

class GaussDev_Comments_Block_Product_Comments extends Mage_Catalog_Block_Product_View_Abstract
{
    public function getAllComments()
    {
        try {
            $productId = $this->getRequest()->getParam('id');
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            $sort = $this->getRequest()->getParam('sort');
            $curPage = $this->getRequest()->getParam('current_page');
            $pageSize = $this->getRequest()->getParam('page_size');

            $comments = Mage::getModel('gaussdev_comments/comment')
                            ->getSortedCommentsData($productId, $customerId, $sort, $curPage, $pageSize);

            return $comments;

        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);

            return array();
        }
    }

}