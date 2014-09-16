<?php
/** 
 * Objects merge/update
 */ 
if(!class_exists('mgm_ccbill'))
	mgm_import_dependency(MGM_MODULE_BASE_DIR . '/payment/ccbill/mgm_ccbill');
 // saved object
 $mgm_ccbill_cached = mgm_get_option('ccbill');

 // edit var
 if($mgm_ccbill_cached->supports_trial == 'N'){
 	$mgm_ccbill_cached->supports_trial = 'Y'; 	
 }	

 // update
 update_option('mgm_ccbill', $mgm_ccbill_cached);
 
 // ends