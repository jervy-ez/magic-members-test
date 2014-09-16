<?php
/** 
 * Notify user transaction merge/update
 */
global $wpdb;
//sql
$sql = "SELECT `id`,`data` FROM `".TBL_MGM_TRANSACTION."` WHERE `payment_type`='subscription_purchase' AND `status`='Active'";
//result
$results = $wpdb->get_results($sql);
//fetch
if($results){
	// loop
	foreach( $results as $row ){
		//check
		if(is_object($row)){
			// decode
			$data = json_decode($row->data, true);
			//check
			if(isset($data['notify_user']) && $data['notify_user'] == true ) {
				$data['notify_user'] = false;
				mgm_update_transaction(array('data'=>json_encode($data)), $row->id);
			}
		}
	}
}