<?php

class Radweb_Stripe_Block_Adminhtml_Sales_Order_Grid_Renderer_State extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {

        $result = parent::render($row);


        $order = Mage::getModel('sales/order')->load($row->getId());
        $paymentDetails = $order->getPayment();
        $payment_method = $paymentDetails->getMethod();

        //$info = $payment->getData('info');

        if($payment_method == 'radweb_stripe')
            {

            $addressStatus = $paymentDetails->getData('address_status');
            $zipStatus = $paymentDetails->getData('cc_status');
            $cvvStatus = $paymentDetails->getData('cc_avs_status');

            if($addressStatus == 'failed' || $zipStatus == 'failed' || $cvvStatus == 'failed')
                    {

                    $img = $this->getSkinUrl('radweb/stripe/images/warning.png');
                    $result = '<img src="' . $img . '" title="You should verify transaction details as one or more checks have failed." />';

                    }
                    else
                    {

                    $img = $this->getSkinUrl('radweb/stripe/images/passed.png');
                    $result = '<img src="' . $img . '" title="All transaction checks have been passed successfully." />';

                    }
            }

        return $result;
    }


}
