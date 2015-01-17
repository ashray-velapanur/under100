<?php

class GaussDev_Comments_Adminhtml_Comments_GridController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        try {
            $this->_title($this->__('Catalog'))->_title($this->__('Product comments'));
            $this->loadLayout();
            $this->_setActiveMenu('catalog');
            $this->renderLayout();
        } catch (Exception $e) {
        }
    }

    public function deletedAction()
    {
        $comment = Mage::getModel('gaussdev_comments/comment')->load($this->getRequest()->getParam('id'));
        $comment->setIsDeleted($comment->getIsDeleted() ? false : true)->save();
        $this->_redirectReferer();
    }

    public function spamAction()
    {
        $comment = Mage::getModel('gaussdev_comments/comment')->load($this->getRequest()->getParam('id'));
        $comment->setIsSpam($comment->getIsSpam() ? false : true)->save();
        $this->_redirectReferer();
    }

    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()
                                           ->createBlock('gaussdev_comments/adminhtml_catalog_grid')
                                           ->toHtml());
    }
}
