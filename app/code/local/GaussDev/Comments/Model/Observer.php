<?php

class GaussDev_Comments_Model_Observer
{

    public function setCommentDeletedAfterCustomerDelete(Varien_Event_Observer $observer)
    {
        $customerId = $observer->getEvent()->getCustomer()->getId();
        $comments = Mage::getModel('gaussdev_comments/comment')
                        ->getCollection()
                        ->addFieldToFilter('customer_id', $customerId);

        Mage::getSingleton('core/resource_iterator')->walk($comments->getSelect(), array(array($this, 'setDeleted')));

        return $this;
    }

    public function setDeleted($args)
    {
        $comment = Mage::getModel('gaussdev_comments/comment');
        $comment->setData($args['row']);
        $comment->setIsDeleted(true);
        $comment->save();
    }

}