<?php
/** 
 * Objects merge/update
 */ 
 // saved object
 $mgm_ideal_cached = mgm_get_option('ideal');
 
 // set new vars
 // merchant id
 if(!isset($mgm_ideal_cached->setting['merchant_id'])){
 	$mgm_ideal_cached->setting['merchant_id'] = '';
 }
  // subscription id
 if(!isset($mgm_ideal_cached->setting['sub_id'])){
 	$mgm_ideal_cached->setting['sub_id'] = '';
 }
  // language
 if(!isset($mgm_ideal_cached->setting['language'])){
 	$mgm_ideal_cached->setting['language'] = '';
 }
   // language
 if(!isset($mgm_ideal_cached->setting['aquirer'])){
 	$mgm_ideal_cached->setting['aquirer'] = '';
 }
  // old username setting
 if(isset($mgm_ideal_cached->setting['username'])){
 	unset($mgm_ideal_cached->setting['username']);
 }
 //remove product mapping
 if($mgm_ideal_cached->requires_product_mapping == 'Y')
 	$mgm_ideal_cached->requires_product_mapping = 'N';
 //remove support trial	
 if($mgm_ideal_cached->supports_trial == 'Y')
 	$mgm_ideal_cached->supports_trial = 'N'; 
 // update
 update_option('mgm_ideal', $mgm_ideal_cached);
 
 // ends