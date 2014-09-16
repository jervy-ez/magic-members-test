<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members transactions handler utility class
 *
 * @package MagicMembers
 * @since 2.5.0
 */
class mgm_transactions{
	// construct
	public function __construct(){
		// php4 
		$this->mgm_transactions();
	}
	
	// php4 construct
	public function mgm_transactions(){
		// do 
	}	
		
	/**	
	 * add
	 *
	 * @param array $data pack data
	 * @param array $options
	 */
	public function add($data, $options = NULL){
		global $wpdb;
		// init
		$columns = $tran_data = array();
		// payment type
		$columns['payment_type'] = (isset($data['buypost']))? 'post_purchase' : 'subscription_purchase';	
		// user
		// IMPORTANT: user_id has to be passed alogn with pack details, otherwise logged in user id 
		$tran_data['user_id'] = isset($options['user_id']) ? $options['user_id'] : mgm_get_user_id();						
		// register and purchase, capture post id
		if(isset($options['post_id'])) $tran_data['post_id'] = (int)$options['post_id'];
		// register and purchase postpack, capture postpack id & postpack post id
		if(isset($options['postpack_id'])) $tran_data['postpack_id'] = (int)$options['postpack_id'];
		if(isset($options['postpack_post_id'])) $tran_data['postpack_post_id'] = (int)$options['postpack_post_id'];		
		// subscription option : create|upgrade|downgrade|extend
		if($columns['payment_type'] == 'subscription_purchase'){
			// registration flag, @ToDo will use "subscription_option" next onwards			
			// subscription option
			$tran_data['subscription_option'] = (isset($options['subscription_option'])) ? $options['subscription_option'] : 'create';
			// new registration @todo @depracate
			$tran_data['is_registration'] = (isset($options['is_registration']))? 'Y' : 'N';
			// another subscription purchase flag @todo @depracate
			$tran_data['is_another_membership_purchase'] = (isset($options['is_another_membership_purchase']))? 'Y' : 'N';
			// another subscription purchase - if upgrade from prev pack
			// value should be reset once upgrade member object is replaced @todo @depracate
			$tran_data['multiple_upgrade_prev_packid'] = (isset($options['multiple_upgrade_prev_packid']))? $options['multiple_upgrade_prev_packid'] : '';
			// registration user email notification flag after user is active - issue #1468
			$tran_data['notify_user'] = (isset($options['notify_user']))? $options['notify_user'] : false;			
		}
		// others
		// set system currency, will update at module level after module selection
		//issue #1602 
		if(!isset($data['currency']) || empty($data['currency'] )) {
			$tran_data['currency'] = mgm_get_class('system')->get_setting('currency');
		}else {
			$tran_data['currency'] = $data['currency'];
		}
		// ip
		$tran_data['client_ip'] = mgm_get_client_ip_address();				
		// payment email sent flag
		$tran_data['payment_email'] = 0;
		// merge with data
		$tran_data = array_merge($data, $tran_data);
		// set data
		$columns['data'] = json_encode($tran_data);
		// date
		$columns['transaction_dt'] = date('Y-m-d H:i:s');
		// user id
		if(isset($tran_data['user_id']) && (int)$tran_data['user_id'] > 0 ){
			// add
			$columns['user_id'] = $tran_data['user_id'];
		}
		// insert
		$wpdb->insert(TBL_MGM_TRANSACTION, $columns);
		// transaction id
		$id = $wpdb->insert_id;
		// run actions
		do_action('mgm_transaction_item_add', $id,  $columns['payment_type']);// global
		do_action('mgm_transaction_item_add_' . $columns['payment_type'], $id);// individual
		// return 
		return $id;
	}
	
	// update
	public function update($data, $id){
		global $wpdb;
		
		// update
		if((int)$id > 0){
			if( $affected = $wpdb->update(TBL_MGM_TRANSACTION, $data, array('id'=>(int)$id)) ){
				// run action
				do_action('mgm_transaction_item_update', $id);// global
				return $affected;			
			}
		}
		
		// return 
		return false;
	}
	
	// update status
	public function update_status($id, $status, $status_text){
		global $wpdb;
		
		// return
		if( $affected = $wpdb->update(TBL_MGM_TRANSACTION, array('status'=>$status, 'status_text'=>$status_text), array('id'=>(int)$id)) ){
			// run action
			do_action('mgm_transaction_item_update_status', $id, $status);// global
			return $affected;	
		}	

		// return 
		return false;
	}
	
	// delete
	public function delete($id){
		global $wpdb;
		
		// delete main
		if( $wpdb->query($wpdb->prepare("DELETE FROM `".TBL_MGM_TRANSACTION."` WHERE `id`=%d", $id)) ){
			// delete options
			$wpdb->query($wpdb->prepare("DELETE FROM `".TBL_MGM_TRANSACTION_OPTION."` WHERE `transaction_id`=%d", $id));
			// run action
			do_action('mgm_transaction_item_delete', $id);// global
			return true;	
		}

		// return
		return false;
	}
	
	// delete all
	public function delete_all($where=null){
		global $wpdb;
		// init
		$deleted = 0 ;
		// no filter, delete all
		if( is_null($where) ){
			// delete main
			if( $deleted = $wpdb->query("DELETE FROM `".TBL_MGM_TRANSACTION."` WHERE 1") ){
				// delete options
				$wpdb->query("DELETE FROM `".TBL_MGM_TRANSACTION_OPTION."` WHERE 1");
				// run action
				do_action('mgm_transaction_item_delete_all');// global
			}			
		}else{
			// sql
			$where_array = array();
			foreach($where as $field=>$value){
				$where_array[] = " (`{$field}` = '{$value}') ";// eq operatir support only 
			}
			$where_sql = '';
			if( ! empty($where_array) ){
				$where_sql = ' AND ' . implode(' AND', $where_array);
			}
			// fetch 
			$transactions = $wpdb->get_results("SELECT `id` FROM  `".TBL_MGM_TRANSACTION."` WHERE 1 {$where_sql} ");
			// check 
			if($transactions){
				// loop
				foreach($transactions as $transaction){
					// delete main					
					if( $wpdb->query("DELETE FROM `".TBL_MGM_TRANSACTION_OPTION."` WHERE `id` = '{$transaction->id}' ") ){
						// delete options
						$wpdb->query("DELETE FROM `".TBL_MGM_TRANSACTION_OPTION."` WHERE `transaction_id` = '{$transaction->id}' ");	
						// run action
						do_action('mgm_transaction_item_delete', $transaction->id);// global
						// coun
						$deleted++;
					}
				}
			}
		}		
		
		// return 
		return ($deleted);
	}
	
	// get 
	public function get($id){
		global $wpdb;
		// check
		if((int)$id>0){
			// row
			$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".TBL_MGM_TRANSACTION."` WHERE id=%d", $id), ARRAY_A);		
			// reset data
			if(isset($row['id'])){
				// decode
				$row['data'] = json_decode($row['data'],true);
				// return
				return $row;
			}
		}
		// error
		return false;
	}		
	
	// get 
	public function get_payment_type($id){
		// global
		global $wpdb;
		
		// check
		if((int)$id>0){
			// transaction
			$payment_type = $wpdb->get_var($wpdb->prepare("SELECT `payment_type` FROM `".TBL_MGM_TRANSACTION."` WHERE `id`=%d", $id));
			// switch for old format
			if($payment_type == 'post_purchase'){
				return 'buypost';
			}else if($payment_type == 'subscription_purchase'){
				return 'subscription';	
			}else{
				return 'other';
			}		
		}
		
		// error
		return 'other';
	}
	
	// get by option=>value
	public function get_by_option($option_name,$option_value){
		// global
		global $wpdb;
		// sql
		$sql = $wpdb->prepare("SELECT `transaction_id` FROM `".TBL_MGM_TRANSACTION_OPTION."` WHERE `option_name` =%s AND `option_value`=%s", $option_name, $option_value);		
		// insert
		$transaction_id = $wpdb->get_var($sql);
		// return 
		if(isset($transaction_id) && (int)$transaction_id > 0){
			return $this->get($transaction_id);
		}
		// error
		return false;
	}
	
	// get id by option=>value
	public function get_id_by_option($option_name,$option_value){
		// global
		global $wpdb;
		// sql
		$sql = $wpdb->prepare("SELECT `transaction_id` FROM `".TBL_MGM_TRANSACTION_OPTION."` WHERE `option_name` =%s AND `option_value`=%s", $option_name, $option_value);		
		// insert
		return $transaction_id = $wpdb->get_var($sql);
	}
	
	// get all
	public function get_all(){
		global $wpdb;
		// rows
		$rows = array();
		// results
		$results = $wpdb->get_results("SELECT * FROM `".TBL_MGM_TRANSACTION."` WHERE 1", ARRAY_A);		
		// reset data
		if($results){
			// loop
			foreach( $results as $row ){
				// decode
				$row['data'] = json_decode($row['data'], true);
				// set
				$rows[] = $row;
			}
		}
		// return
		return $rows;
	}	
	
	// options -------------------------------------------------------------

	/**
	 * array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>$option_value)
	 */
	public function add_option($data){		
		// global
		global $wpdb;
		// get
		$option_id = $wpdb->get_var($wpdb->prepare("SELECT `id` FROM `".TBL_MGM_TRANSACTION_OPTION."` WHERE `transaction_id`=%d AND `option_name`=%s", $data['transaction_id'], $data['option_name']));		
		// update
		if(isset($option_id) && (int)$option_id > 0){
		// update
			return $wpdb->update(TBL_MGM_TRANSACTION_OPTION, $data, array('id'=>$option_id));		
		}else{
		// insert		
			$wpdb->insert(TBL_MGM_TRANSACTION_OPTION, $data);
			// return 
			return $wpdb->insert_id;
		}
		// id
		return false;		
	}	
	
	/**
	 * array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>$option_value)
	 */
	public function update_option($data){		
		// global
		global $wpdb;
		// get
		$option_id = $wpdb->get_var($wpdb->prepare("SELECT `id` FROM `".TBL_MGM_TRANSACTION_OPTION."` WHERE `transaction_id`=%d AND `option_name`=%s", $data['transaction_id'], $data['option_name']));		
		// update
		if(isset($option_id) && (int)$option_id > 0){
		// update
			return $wpdb->update(TBL_MGM_TRANSACTION_OPTION, $data, array('id'=>$option_id));		
		}
		// id
		return false;		
	}	

	// get option
	public function get_option($transaction_id, $option_name){
		global $wpdb;
		// check
		if((int)$transaction_id>0){
			// row
			$option_value = $wpdb->get_var($wpdb->prepare("SELECT `option_value` FROM `".TBL_MGM_TRANSACTION_OPTION."` WHERE `transaction_id`=%d AND `option_name`=%s", $transaction_id, $option_name));		
			// reset data
			if(isset($option_value)){
				// decode
				if(($option_value_decoded = mgm_is_json_encoded($option_value)) !== FALSE){
					$option_value = $option_value_decoded;
				}
				// return
				return $option_value;
			}
		}
		// error
		return false;
	}	
}
// end of file core/libs/utilities/mgm_transactions.php