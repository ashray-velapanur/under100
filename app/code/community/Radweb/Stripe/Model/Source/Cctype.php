<?php
/**
 * Payment CC Types Source Model
 *
 * @category	Radweb
 * @package		Radweb_Stripe
 * @author		Artur Salagean <info@radweb.co.uk>
 * @copyright	Radweb (http://radweb.co.uk)
 * @license		http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Radweb_Stripe_Model_Source_Cctype extends Mage_Payment_Model_Source_Cctype
{
	protected $_allowedTypes = array('AE','VI','MC','DI','JCB','DIN');

}
