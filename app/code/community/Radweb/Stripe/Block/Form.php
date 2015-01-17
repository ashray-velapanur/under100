<?php

/**
 * Stripe payment form block
 *
 * @category    Radweb
 * @package     Radweb_Stripe
 * @author      Artur Salagean <info@radweb.co.uk>
 * @copyright   Radweb (http://radweb.co.uk)
 * 
 */

class Radweb_Stripe_Block_Form extends Mage_Payment_Block_Form_Cc
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('radweb/stripe/form.phtml');
    }

}

?>