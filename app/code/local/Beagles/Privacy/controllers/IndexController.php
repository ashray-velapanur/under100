<?php
class Beagles_Privacy_IndexController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {
        echo '...privacy working!';
    }

    public function privacyAction() {
        $this->loadLayout();
        $myBlock = $this->getLayout()->createBlock('core/template');
        $myBlock->setTemplate('privacy/privacy_policy.phtml');
        $myHtml = $myBlock->toHtml();
        $this->getResponse()->setHeader('Content-Type', 'text/html')->setBody($myHtml);
    }

    public function termsAction() {
        $this->loadLayout();
        $myBlock = $this->getLayout()->createBlock('core/template');
        $myBlock->setTemplate('privacy/terms_of_service.phtml');
        $myHtml = $myBlock->toHtml();
        $this->getResponse()->setHeader('Content-Type', 'text/html')->setBody($myHtml);
    }
}