<?php

/**
 * Radweb Stripe
 *
 * @category    Radweb
 * @package     Radweb_Stripe
 * @author      Artur Salagean <info@radweb.co.uk>
 * @copyright   Radweb (http://radweb.co.uk)
 * 
 */

class Radweb_Stripe_Model_Source_Payment
{


	public function __construct()
	{


	}

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
    	$lists = array();

      $lists [0]= array('value' => 0, 'label' => 'Authorize');
      $lists [1]= array('value' => 1, 'label' => 'Authorize and Capture');

        return $lists;
    }

}
