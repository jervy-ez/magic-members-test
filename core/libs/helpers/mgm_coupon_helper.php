<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members coupon helpers
 *
 * @package MagicMembers
 * @version 1.0 
 * @since 2.6.0
 */
 
/**
 * Magic Members add coupon
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param array data
 * @return array coupon created
 */
 function mgm_add_coupon($data){
 	// object
	$c_obj = mgm_get_utility_class('coupons');
	// return 
	return $c_obj->add($data);
 }
 
 /**
 * Magic Members update coupon
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int id
 * @param array data
 * @return array coupon updated
 */
 function mgm_update_coupon($id, $data){
 	// object
	$c_obj = mgm_get_utility_class('coupons');
	// return 
	return $c_obj->update($id, $data);
 }
 
/**
 * Magic Members delete coupon
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string code
 * @return array coupon deleted
 */
 function mgm_delete_coupon($id){
 	// object
	$c_obj = mgm_get_utility_class('coupons');
	// return 
	return $c_obj->delete($id);
 }
 
 /**
 * Magic Members delete all coupon
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string code
 * @return array coupon deleted
 */
 function mgm_delete_all_coupon(){
 	// object
	$c_obj = mgm_get_utility_class('coupons');
	// return 
	return $c_obj->delete_all();
 }
 
/**
 * Magic Members get coupon
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string code
 * @return array coupon
 */
 function mgm_get_coupon($id){
 	// object
	$c_obj = mgm_get_utility_class('coupons');
	// return 
	return $c_obj->get($id);
 }
 
 /**
 * Magic Members get all coupon
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string none
 * @return array coupons
 */
 function mgm_get_all_coupon(){
 	// object
	$c_obj = mgm_get_utility_class('coupons');
	// return 
	return $c_obj->get_all();
 }
 
/**
 * Magic Members get coupon count
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param none
 * @return array coupon
 */
 function mgm_get_coupon_count(){
 	// object
	$c_obj = mgm_get_utility_class('coupons');
	// return 
	return $c_obj->get_count();
 }
 
 /**
 * Magic Members get coupon users
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int id
 * @return array coupon
 */
 function mgm_get_coupon_users($id){
 	// object
	$c_obj = mgm_get_utility_class('coupons');
	// return 
	return $c_obj->get_users($id);
 }
 // end file /core/libs/helpers/mgm_coupon_helper.php
