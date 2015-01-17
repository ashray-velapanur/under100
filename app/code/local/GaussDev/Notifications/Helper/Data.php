<?php

class GaussDev_Notifications_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getName($customer)
    {
        if (is_numeric($customer)) {
            $customer = Mage::getModel('customer/customer')->load($customer);
        }
        if (!$customer->getId()) {
            return 'Someone';
        }

        return trim($customer->getUsername() ?: "{$customer->getFirstname()} {$customer->getLastname()}");
    }

    public function getGooglePushApiKey()
    {
        return 'AIzaSyC5dgxyRneFnjoh--n5AVPcnZQXJwOMOaQ';
    }

    public function getApnsCert()
    {
        return Mage::getModuleDir('data', 'GaussDev_Notifications') . DS . 'u100distribution.pem';
    }

    public function sendGcm($deviceIds, array $data)
    {
        $headers = array(
            'Authorization:key=' . $this->getGooglePushApiKey(),
            'Content-Type:application/json'
        );

        $fields = array('registration_ids' => $deviceIds, 'data' => $data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }

    public function sendApn($deviceId, $text)
    {
        $message = new Zend_Mobile_Push_Message_Apns();
        $message->setAlert($text);
        $message->setBadge(1);
        $message->setSound('default');
        $message->setId(time());
        $message->setToken($deviceId);

        $apns = new Zend_Mobile_Push_Apns();
        $apns->setCertificate($this->getApnsCert());
        $apns->connect(Zend_Mobile_Push_Apns::SERVER_PRODUCTION_URI);

        $apns->send($message);

        $apns->close();
    }
}