<?php
/** 
 * Patch for updating AlertPay Payment gateway label changes
 * Alertpay has been Renamed to Payza
 */  

$alertpay = mgm_get_module('alertpay','payment');
// name
$alertpay->name = 'Payza';
// logo
$alertpay->logo = MGM_MODULE_BASE_URL . 'payment/alertpay/assets/payza.png'; 
// description
$alertpay->description = __('A comprehensive all-in-one solution for your payment needs. Payza is an account-based payment processor allowing just about '. 
                         	'anyone with an email address to securely send and receive money with their credit card or bank account. .', 'mgm');
// end points
$alertpay->end_points = array(	'test'        => 'https://sandbox.payza.com/sandbox/payprocess.aspx',
								'live'        => 'https://secure.payza.com/checkout',
								'unsubscribe' => 'https://api.payza.com/svc/api.svc/CancelSubscription'); // cancel subscription
// save changes
$alertpay->save();
