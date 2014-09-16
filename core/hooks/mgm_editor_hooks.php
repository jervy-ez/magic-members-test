<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * hooks and callbacks for editor customization
 *
 * @package MagicMembers
 * since 2.5.1
 */

/**
 * hook to create button
 *
 */ 
function mgm_add_editor_buttons() {
	// Don't bother doing this stuff if the current user lacks permissions
	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
		return;
	
	// Add only in Rich Editor mode
	if ( get_user_option('rich_editing') == 'true' && MGM_EDITOR_PLUGIN == 'On') {
		add_filter('mce_external_plugins' , 'mgm_register_editor_plugin');
		add_filter('mce_buttons'          , 'mgm_register_editor_button');
	}
}

/**
 * hook to register editor buton
 *
 */ 	
function mgm_register_editor_button($buttons) {
	// push
	//array_push($buttons, 'separator', 'mgmdownload');
	array_push($buttons, '|', 'mgmdownload');
	array_push($buttons, '|', 'mgmshortcode');
	// return
	return $buttons;
}

/**
 * hook to register editor plugin
 *
 */ 
function mgm_register_editor_plugin($plugin_array) {
	// push
	$plugin_array['mgmdownload'] = MGM_ASSETS_URL.'js/editor/plugins/downloads/editor_plugin.js';
	$plugin_array['mgmshortcode'] = MGM_ASSETS_URL.'js/editor/plugins/shortcodes/shortcodes_editor_plugin.js';
	// return 
	return $plugin_array;
}

/**
 * attach hook to create button
 *
 */  
add_action('init', 'mgm_add_editor_buttons');
 
 
 
// core/hooks/editor_hooks.php
// end of file