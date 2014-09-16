<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members addon helpers
 *
 * @package MagicMembers
 * @version 1.0 
 * @since 2.6.0
 */
 
/**
 * Magic Members add addon
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param array data
 * @return array addon created
 */
 function mgm_add_addon($data){
 	// object
	$a_obj = mgm_get_utility_class('addons');
	// return 
	return $a_obj->add($data);
 }
 
 /**
 * Magic Members update addon
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int id
 * @param array data
 * @return array addon updated
 */
 function mgm_update_addon($id, $data){
 	// object
	$a_obj = mgm_get_utility_class('addons');
	// return 
	return $a_obj->update($id, $data);
 }
 
/**
 * Magic Members delete addon
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string code
 * @return array addon deleted
 */
 function mgm_delete_addon($id){
 	// object
	$a_obj = mgm_get_utility_class('addons');
	// return 
	return $a_obj->delete($id);
 }
 
 /**
 * Magic Members delete all addon
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string code
 * @return array addon deleted
 */
 function mgm_delete_all_addon(){
 	// object
	$a_obj = mgm_get_utility_class('addons');
	// return 
	return $a_obj->delete_all();
 }
 
/**
 * Magic Members get addon
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string code
 * @return array addon
 */
 function mgm_get_addon($id){
 	// object
	$a_obj = mgm_get_utility_class('addons');
	// return 
	return $a_obj->get($id);
 }
 
 /**
 * Magic Members get all addon
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string none
 * @return array addons
 */
 function mgm_get_all_addon(){
 	// object
	$a_obj = mgm_get_utility_class('addons');
	// return 
	return $a_obj->get_all();
 }
 
/**
 * Magic Members get addon count
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param none
 * @return array addon
 */
 function mgm_get_addon_count(){
 	// object
	$a_obj = mgm_get_utility_class('addons');
	// return 
	return $a_obj->get_count();
 }
 
/**
 * Magic Members get all membership type for combo
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string none
 * @return array subscription packages
 */
 function mgm_get_all_addon_combo($skip=array()){
 	// object
	$addons = mgm_get_all_addon();
	// combo
	$combo = array();
	// loop
	if($addons){
		// loop
		foreach($addons as $addon){
			// skip
			if(in_array($addon->id, $skip)) continue;
			// set
			$combo[$addon->id] = $addon->name;
		}
	}
	// return 
	return $combo;
 }
  
/**
 * Magic Members get addon users
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int id
 * @return array addon
 */
 function mgm_get_addon_options($id){
 	// object
	$a_obj = mgm_get_utility_class('addons');
	// return 
	return $a_obj->get_options($id);
 }
 
/**
 * Magic Members get addon users
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int id
 * @return array addon
 */
 function mgm_get_addon_options_combine($ids=array()){
 	// object
	$a_obj = mgm_get_utility_class('addons');
	// return 
	return $a_obj->get_options_combine($ids);
 }
 
/**
 * Magic Members get addon users
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int id
 * @return array addon
 */
 function mgm_get_addon_options_only($option_ids){
 	// object
	$a_obj = mgm_get_utility_class('addons');
	// return 
	return $a_obj->get_options_only($option_ids);
 }
 
 function mgm_get_post_purchase_addon_options_html($addons){
 	$addon_options_html = '';
	if($addon_options = mgm_get_addon_options_combine($addons)){
		$addon_name = '';
		foreach($addon_options as $addon_option){
			// header
			if($addon_option['name'] != $addon_name){
				$addon_options_html .= sprintf('<dt>%s</dt>', $addon_option['name']);
				$addon_name = $addon_option['name'];
			}
			// label
			$addon_label = sprintf('%s - %s %s',$addon_option['option'],$addon_option['price'],$currency);
			// set
			$addon_options_html .= sprintf('<dd><input type="checkbox" name="addon_options[]" class="checkbox" value="%d">%s</dd>', $addon_option['option_id'], $addon_label);
		}	
	}
	// set
	$addon_options_html = sprintf('%s <dl class="mgm-post-purchase-addons">%s</dl>', __('Available Addons:', 'mgm'), $addon_options_html);
	// filter
	return $addon_options_html = apply_filters('post_purchase_addon_options_html', $addon_options_html);
 }
 
 function mgm_get_register_addon_options_html($element_name){
	$addon_options_html = '';
	if($addon_options = mgm_get_addon_options_combine()){
		$addon_name = '';
		foreach($addon_options as $addon_option){
			// header
			if($addon_option['name'] != $addon_name){
				$addon_options_html .= sprintf('<dt>%s</dt>', $addon_option['name']);
				$addon_name = $addon_option['name'];
			}
			// label
			$addon_label = sprintf('%s - %s %s',$addon_option['option'],$addon_option['price'],$currency);
			// set
			$addon_options_html .= sprintf('<dd><input type="checkbox" name="%s" class="checkbox" value="%d">%s</dd>', $element_name, $addon_option['option_id'], $addon_label);
		}	
	}
	// set
	$addon_options_html = sprintf('<dl class="mgm-post-purchase-addons">%s</dl>', $addon_options_html);
	// filter
	return $addon_options_html = apply_filters('register_addon_options_html', $addon_options_html);
 }
 // end file /core/libs/helpers/mgm_addon_helper.php
