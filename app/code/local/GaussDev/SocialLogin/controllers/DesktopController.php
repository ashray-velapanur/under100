<?php

class GaussDev_SocialLogin_DesktopController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();
        $visitor = Mage::getSingleton('core/session')->getVisitorData();
        $redirect_url = isset($visitor['http_referer']) ? $visitor['http_referer'] : Mage::getBaseUrl();
        $url = $this->getRequest()->getParam('_burl');
        if ($url) {
            $redirect_url = $url;
        }
        Mage::getSingleton('customer/session')->setBeforeAuthUrl($redirect_url);
    }

    public function googleAction()
    {
        $this->_redirect('*/oauth/auth', array('google' => true));
    }

    public function facebookAction()
    {
        $this->_redirect('*/oauth/auth', array('facebook' => true));
    }

    public function instagramAction()
    {
        $this->_redirect('*/oauth/auth', array('instagram' => true));
    }

    public function twitterAction()
    {
        $this->_redirect('*/oauth/auth', array('twitter' => true));
    }
}