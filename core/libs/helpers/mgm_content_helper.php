<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members content helpers
 *
 * @package MagicMembers
 * @version 1.0 
 * @since 2.6.0
 * @status future
 */
 
/**
 * Magic Members add content protection
 *
 * @package MagicMembers
 * @since 2.6.0 
 * @param int post_id
 * @param array data
 * @return array content protection created
 */
 function mgm_add_content_protection($post_id, $data){
 	// object
	$ct_obj = mgm_get_utility_class('contents');
	// return 
	return $ct_obj->add($post_id, $data);
 }
 
 /**
 * Magic Members update content protection
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int post_id
 * @param array data
 * @return array content protection updated
 */
 function mgm_update_content_protection($post_id, $data){
 	// object
	$ct_obj = mgm_get_utility_class('contents');
	// return 
	return $ct_obj->update($post_id, $data);
 }
 
/**
 * Magic Members delete content protection
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int post_id
 * @return bool deleted
 */
 function mgm_delete_content_protection($post_id){
 	// object
	$ct_obj = mgm_get_utility_class('contents');
	// return 
	return $ct_obj->delete($post_id);
 }
 
 /**
 * Magic Members delete all content protection
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param none
 * @return bool deleted
 */
 function mgm_delete_all_content_protection(){
 	// object
	$ct_obj = mgm_get_utility_class('contents');
	// return 
	return $ct_obj->delete_all();
 }
 
/**
 * Magic Members get content protection
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int post_id
 * @return array protected content
 */
 function mgm_get_content_protection($post_id){
 	// object
	$ct_obj = mgm_get_utility_class('contents');
	// return 
	return $ct_obj->get($post_id);
 }
 
/**
 * Magic Members get all content protection
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param none
 * @return array protected contents
 */
 function mgm_get_all_content_protection(){
 	// object
	$ct_obj = mgm_get_utility_class('contents');
	// return 
	return $ct_obj->get_all();
 }
 // end file /core/libs/helpers/mgm_content_helper.php
