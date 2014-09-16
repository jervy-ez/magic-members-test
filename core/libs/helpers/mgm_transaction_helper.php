<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members transaction helpers
 *
 * @package MagicMembers
 * @version 1.0
 * @since 2.6.0
 */
 
/**
 * Magic Members add transaction
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param array $data
 * @param array $options
 * @return array $transaction
 */
 function mgm_add_transaction($data, $options = NULL){
 	// object
	$t_obj = mgm_get_utility_class('transactions');
	// return 
	return $t_obj->add($data, $options);
 }
 
/**
 * Magic Members update transaction
 *
 * @package MagicMembers
 * @since 2.6.0 
 * @param array $data
 * @param int $id
 * @return int $affected
 */
 function mgm_update_transaction($data, $id){
 	// object
	$t_obj = mgm_get_utility_class('transactions');
	// return 
	return $t_obj->update($data, $id);
 }
 
/**
 * Magic Members update transaction
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int $id
 * @param string $status
 * @param string $status_text
 * @return int $affected
 */
 function mgm_update_transaction_status($id, $status, $status_text){
 	// object
	$t_obj = mgm_get_utility_class('transactions');
	// return 
	return $t_obj->update_status($id, $status, $status_text);
 }
 
/**
 * Magic Members delete transaction
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int id
 * @return bool (success|failure)
 */
 function mgm_delete_transaction($id){
 	// object
	$t_obj = mgm_get_utility_class('transactions');
	// return 
	return $t_obj->delete($id);
 }
 
/**
 * Magic Members delete all transaction
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param none
 * @return bool (success|failure)
 */
 function mgm_delete_all_transaction(){
 	// object
	$t_obj = mgm_get_utility_class('transactions');
	// return 
	return $t_obj->delete_all();
 }

/**
 * Magic Members delete all transaction of user
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param none
 * @return bool (success|failure)
 */
 function mgm_delete_user_transactions($user_id){
 	// object
	$t_obj = mgm_get_utility_class('transactions');
	// return 
	return $t_obj->delete_all(array('user_id' => $user_id));
 } 

/**
 * Magic Members get transaction
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int id
 * @return array $transaction
 */
 function mgm_get_transaction($id){
 	// object
	$t_obj = mgm_get_utility_class('transactions');
	// return 
	return $t_obj->get($id);
 }
 
/**
 * Magic Members get transaction payment type
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int id
 * @return string $payment_type
 */
 function mgm_get_transaction_payment_type($id){
 	// object
	$t_obj = mgm_get_utility_class('transactions');
	// return 
	return $t_obj->get_payment_type($id);
 }
 
/**
 * Magic Members get transaction by option
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string $option_name
 * @param string $option_value
 * @return array $transaction
 */
 function mgm_get_transaction_by_option($option_name,$option_value){
 	// object
	$t_obj = mgm_get_utility_class('transactions');
	// return 
	return $t_obj->get_by_option($option_name,$option_value);
 }
 
 /**
 * Magic Members get transaction id by option
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string $option_name
 * @param string $option_value
 * @return array $transaction
 */
 function mgm_get_transaction_id_by_option($option_name,$option_value){
 	// object
	$t_obj = mgm_get_utility_class('transactions');
	// return 
	return $t_obj->get_id_by_option($option_name,$option_value);
 } 
/**
 * Magic Members get all transaction
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string none
 * @return array $transactions
 */
 function mgm_get_all_transaction(){
 	// object
	$t_obj = mgm_get_utility_class('transactions');
	// return 
	return $t_obj->get_all();
 }

/**
 * Magic Members add transaction option
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param array $data
 * @return int $affected
 */
 function mgm_add_transaction_option($data){
 	// object
	$t_obj = mgm_get_utility_class('transactions');
	// return 
	return $t_obj->add_option($data);
 } 

 /**
 * Magic Members update transaction option
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param array $data
 * @return int $affected
 */
 function mgm_update_transaction_option($data){
 	// object
	$t_obj = mgm_get_utility_class('transactions');
	// return 
	return $t_obj->update_option($data);
 } 
 
 /**
 * Magic Members get transaction option
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param array $data
 * @return int $affected
 */
 function mgm_get_transaction_option($transaction_id, $option_name){
 	// object
	$t_obj = mgm_get_utility_class('transactions');
	if (!empty($t_obj) && method_exists($t_obj, 'get_option')) {
		// return
		return $t_obj->get_option($transaction_id, $option_name);
	}
	else {
		return null;
	}
 } 
 // end file /core/libs/helpers/mgm_transaction_helper.php
