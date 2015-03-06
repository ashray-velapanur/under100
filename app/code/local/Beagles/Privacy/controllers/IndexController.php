<?php
class Beagles_Privacy_IndexController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {
        echo '...privacy working!';
    }

    public function privacyAction() {
        $data = array(
            'privacy' => 'This is privacy'
        );
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function termsAction() {
        $data = array(
            'terms' => 'This is terms and conditions'
        );
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}