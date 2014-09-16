<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * plugin upgrader hooks and callbacks
 *
 * @package MagicMembers
 * @since 2.6
 * @development 
 */
// add filters
if( get_option('mgm_auto_upgrader_api') == 'Active' ): // check from api
	/**
	 * get plugin information api
	 *
	 * @param bool $false
	 * @param string $action
	 * @param object $args
	 * @return string $response
	 */
	function mgm_plugins_api( $false, $action, $args ) {  
		// slug
		$plugin_slug = untrailingslashit(MGM_PLUGIN_NAME); 
	  	
		// Check if this plugins API is about this plugin - issue #1481   
		if( (isset($args->slug) && $args->slug != $plugin_slug) || isset($args->search) || isset($args->browse) || isset($args->user) )  
			return false;  
	  
		// create new action
		$args->action = 'get_information';  
		
		// check
		if( ($response = mgm_get_class('auth')->get_information_api($args)) != FALSE ){		
			// If there is a new version, return the response 
			if( isset($response->new_version) ){
				// return  
				return $response;  
			}
		}		
	  	
	  	// nothing
	  	return false;  		  
	}  
	// add filter
	add_filter( 'plugins_api', 'mgm_plugins_api', 10, 3 ); 
	 
	/**
	 * plugin upgrade api
	 *
	 * @param object $transient
	 * @return string $response
	 */      
	function mgm_update_plugins( $transient ) {  	
		// Check if the transient contains the 'checked' information  
		// If no, just return its value without hacking it  
		if ( empty( $transient->checked ) )  
			return $transient;  
	  
		// The transient contains the 'checked' information  
		// Now append to it information form your own API  
		$plugin_slug = untrailingslashit(MGM_PLUGIN_NAME);   
		//plugin version
		$plugin_version = (isset($transient->checked[$plugin_slug]))?$transient->checked[$plugin_slug]:'';		
		// set up post
		$args = array(
			'action'      => 'check_version',
			'plugin_slug' => $plugin_slug,
			'version'     => $plugin_version
		);
		
		// append auth params
		if(($response = mgm_get_class('auth')->check_version_api($args)) != FALSE){		
			// If there is a new version, modify the transient  
			if( isset($response->new_version) ){
				if( version_compare( $response->new_version, $plugin_version, '>' ) ) {
				 	$transient->response[$plugin_slug] = $response; 
				} 
			}
		}	  
		
		// return
		return $transient;    
	}  
	// add upgrade filter
	add_filter( 'pre_set_site_transient_update_plugins', 'mgm_update_plugins' ); 
endif;

/**
 * add new meta info to plugin
 *
 * @param array $plugin_meta
 * @param string $plugin_file
 * @param array $plugin_data
 * @param string $status
 * @return array $plugin_meta
 */  
function mgm_plugin_row_meta($plugin_meta, $plugin_file, $plugin_data, $status){
	// add mgm meta
	if($plugin_file == untrailingslashit(MGM_PLUGIN_NAME)){
		$product_url   = mgm_get_class('auth')->get_product_url();
		$plugin_meta[] = sprintf('<a href="%s">%s</a>', $product_url, __('Purchase License', 'mgm'));
	}
	// return
	return $plugin_meta;
}
// add filter
add_filter('plugin_row_meta', 'mgm_plugin_row_meta', 10, 4);

/**
 * add new meta links to plugin
 *
 * @param array $actions
 * @param string $plugin_file
 * @param array $plugin_data
 * @param mixed $context
 * @return array $actions
 */ 
function mgm_plugin_action_links($actions, $plugin_file, $plugin_data, $context){
	// not activated
	if(!mgm_get_class('auth')->verify()){
		// add
		$actions['license'] = sprintf('<img src="%s" align="absmiddle"> <a href="admin.php?page=mgm/admin">%s</a>', MGM_ASSETS_URL .'images/icons/key.png', __('License', 'mgm'));
	}	
		
	// return
	return $actions;
}
// add filter
add_filter('plugin_action_links_' . untrailingslashit(MGM_PLUGIN_NAME), 'mgm_plugin_action_links', 10, 4);

/**
 * add new row to plugin list screen
 *
 * @param string $plugin_file
 * @param array $plugin_data
 * @param string $status
 * @return array $plugin_meta
 */ 
function mgm_after_plugin_row($plugin_file, $plugin_data, $status){	
	// class
	$id = $class = $data ='';
	// row
	$row = sprintf('<tr id="%s" class="%s"><td colspan=3>%s</td></tr>', $id, $class, $data);
	// print
	print $row;
}
// add filter
// add_action('after_plugin_row_' . untrailingslashit(MGM_PLUGIN_NAME), 'mgm_after_plugin_row', 10, 3);

// core/hooks/plugin_upgrader_hooks.php
// end of file