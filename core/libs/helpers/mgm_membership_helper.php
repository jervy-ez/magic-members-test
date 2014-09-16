<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members membership helpers
 *
 * @package MagicMembers
 * @version 1.0 
 * @since 2.6.0
 */
 
/**
 * Magic Members add membership type
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string name
 * @param string login_redirect
 * @param string logout_redirect 
 * @return array membership type created
 */
 function mgm_add_membership_type($name, $login_redirect='', $logout_redirect=''){
 	// object
	$mt_obj = mgm_get_class('membership_types');
	// return 
	return $mt_obj->add($name, $login_redirect, $logout_redirect);
 }
 
/**
 * Magic Members update membership type
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string code
 * @param string name
 * @param string login_redirect
 * @param string logout_redirect 
 * @return array membership type updated
 */
 function mgm_update_membership_type($code, $name, $login_redirect, $logout_redirect){
 	// object
	$mt_obj = mgm_get_class('membership_types');
	// return 
	return $mt_obj->update($code, $name, $login_redirect, $logout_redirect);
 }
 
/**
 * Magic Members delete membership type
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string code
 * @return array membership type deleted
 */
 function mgm_delete_membership_type($code){
 	// object
	$mt_obj = mgm_get_class('membership_types');
	// return 
	return $mt_obj->delete($code);
 }
 
 /**
 * Magic Members delete all membership type
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param none
 * @return bool success|failure
 */
 function mgm_delete_all_membership_type(){
 	// object
	$mt_obj = mgm_get_class('membership_types');
	// return 
	return $mt_obj->delete_all();
 }
 
/**
 * Magic Members get membership type
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string code
 * @return array membership type
 */
 function mgm_get_membership_type($code){
 	// object
	$mt_obj = mgm_get_class('membership_types');
	// return 
	return $mt_obj->get($code);
 }
 
/**
 * Magic Members get all membership type
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string none
 * @return array subscription packages
 */
 function mgm_get_all_membership_type(){
 	// object
	$mt_obj = mgm_get_class('membership_types');
	// return 
	return $mt_obj->get_all();
 }
 
/**
 * Magic Members get all membership type for combo
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string none
 * @return array subscription packages
 */
 function mgm_get_all_membership_type_combo($skip=array()){
 	// object
	$membership_types = mgm_get_all_membership_type();
	// combo
	$combo = array();
	// loop
	if($membership_types){
		// loop
		foreach($membership_types as $membership_type){
			// skip
			if(in_array($membership_type['code'], $skip)) continue;
			// set
			$combo[$membership_type['code']] = $membership_type['name'];
		}
	}
	// return 
	return $combo;
 }
 
/**
 * Magic Members check duplicate membership type
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string name
 * @param string code
 * @return bool
 */
 function mgm_is_duplicate_membership_type($name, $code=NULL){
 	// object
	$mt_obj = mgm_get_class('membership_types');
	// return 
	return $mt_obj->is_duplicate($name, $code);
 }
 
/**
 * Magic Members get membership type name
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string $code
 * @return string $name
 */
 function mgm_get_membership_type_name($code){
 	// object
	$mt_obj = mgm_get_class('membership_types');
	// return 
	return $mt_obj->get_type_name($code);
 }

 // end file /core/libs/helpers/mgm_membership_helper.php
