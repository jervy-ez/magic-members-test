<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members subscription helpers
 *
 * @package MagicMembers
 * @version 1.0
 * @since 2.6.0
 */
 
/**
 * Magic Members add subscription package
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string $membership_type
 * @param array $pack optional pack data
 * @return array subscription package created
 */
 function mgm_add_subscription_package($membership_type, $pack=array()){
 	// object
	$sp_obj = mgm_get_class('subscription_packs');
	// return 
	return $sp_obj->add($membership_type, $pack);
 }
 
/**
 * Magic Members update subscription package
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int $id
 * @param array $pack
 * @return array subscription package updated
 */
 function mgm_update_subscription_package($id, $pack=array()){
 	// object
	$sp_obj = mgm_get_class('subscription_packs');
	// return 
	return $sp_obj->update($id, $pack);
 }
 
/**
 * Magic Members delete subscription package
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int $id
 * @return array subscription package deleted
 */
 function mgm_delete_subscription_package($id){
 	// object
	$sp_obj = mgm_get_class('subscription_packs');
	// return 
	return $sp_obj->delete($id);
 }
 
/**
 * Magic Members delete all subscription package
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param none
 * @return bool success
 */
 function mgm_delete_all_subscription_package(){
 	// object
	$sp_obj = mgm_get_class('subscription_packs');
	// return 
	return $sp_obj->delete_all();
 } 
 
/**
 * Magic Members get subscription package
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int $id
 * @return array subscription package
 */
 function mgm_get_subscription_package($id){
 	// object
	$sp_obj = mgm_get_class('subscription_packs');
	// return 
	return $sp_obj->get($id);
 }
 
/**
 * Magic Members get all subscription package
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string none
 * @return array subscription packages
 */
 function mgm_get_all_subscription_package(){
 	// object
	$sp_obj = mgm_get_class('subscription_packs');
	// return 
	return $sp_obj->get_all();
 }
 
 /**
 * Magic Members get subscription package
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param none
 * @return array subscription package
 */
 function mgm_get_default_subscription_package(){
 	// init
	$_pack = array();
 	// get all packs
 	$packs = mgm_get_all_subscription_package();
	// check
	if($packs){		
		// loop
		foreach($packs as $pack){
			// match
			if(isset($pack['default_assign']) && (bool)$pack['default_assign'] == true){
				$_pack = $pack; break;
			}
		}
	}
	// return 
	return $_pack;
 }
 // end file /core/libs/helpers/mgm_subscription_package_helper.php
