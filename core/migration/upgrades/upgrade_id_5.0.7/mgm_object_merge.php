<?php
/** 
 * Objects merge/update
 * update getResponse autoresponder endpoint
 */ 
 // read  
$getresponse = mgm_get_module('getresponse', 'autoresponder');

if(!empty($getresponse->end_points['live'])) {
	$getresponse->end_points['live'] = 'http://api2.getresponse.com';
	$getresponse->save();
}
 // end file