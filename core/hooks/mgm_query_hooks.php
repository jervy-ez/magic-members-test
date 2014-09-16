<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * query hooks and callbacks
 *
 * @package MagicMembers
 * @since 1.0
 */ 
// generate rewrite
add_action('generate_rewrite_rules', 'mgm_rewrite_rules');
// add query vars
add_filter('query_vars', 'mgm_rewrite_queryvars' );
// flush rewrite
// add_action('init', 'mgm_flush_rewrite_rules');
// parse query hook for param loads
add_action('parse_query', 'mgm_parse_query');		
// parse protected
add_action('send_headers', 'mgm_url_router');
// remove mga canonical redirect, for RESTAPI
add_filter('mga_can_redirect_canonical','mgm_disable_redirect_canonical');	

/**
 * add rules 
 */
function mgm_rewrite_rules( $wp_rewrite ){	
	// named 
	if(get_option('permalink_structure') != ''){	
		// array, issue#: 364
		if(!$download_slug = mgm_get_class('system')->setting['download_slug']){
			// default
			$download_slug = 'download';		
		}
		// set parsable vars
		$_query_vars = array_merge(mgm_get_payment_page_query_vars(), array('protect', $download_slug) );
		// loop
		foreach($_query_vars as $query_var){
			// if not a page 
			if(get_page_by_path($query_var) == NULL){
				// set rule
				$new_rules[$query_var . '$']    = 'index.php?' . $query_var . '=1';	
				$new_rules[$query_var . '(.*)'] = 'index.php?' . $query_var . '=' . $wp_rewrite->preg_index(1) ;
			}
		}	
		// add rules
		$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
	}
}

// add query vars
function mgm_rewrite_queryvars( $query_vars ){ 
	// array, issue#: 364
	if(!$download_slug = mgm_get_class('system')->setting['download_slug']){
		// default
		$download_slug = 'download';	
	}
	// set parsable vars
	$_query_vars = array_merge(mgm_get_payment_page_query_vars(), array('protect', $download_slug) );
	// loop
	foreach($_query_vars as $query_var){
		// if not a page
		if(get_page_by_path($query_var)==NULL){
			// add 
			array_push($query_vars, $query_var);
		}
	}
	// return
	return $query_vars;
}

// flush rewrite rules: discarded for wp 3+
/*******************************************
function mgm_flush_rewrite_rules(){
   global $wp_rewrite;
   $wp_rewrite->flush_rules();
}
********************************************/

// payment tracking
function mgm_parse_query(){
 	global $wpdb;		
	
	// pre process hook for parse query
	do_action('mgm_parse_query_pre_process');	
		 	
 	//check file uploads: 	
 	if( $file_upload = mgm_request_var('file_upload', '', true) ) {
		// option
 		switch ($file_upload) {
 			case 'image':
 				mgm_photo_file_upload();
 			break;
 		}
		// no process further
 		exit;
 	}
 	
	// payment process --------------------
	// default
	$process_payments = false;		
	// check
	foreach(mgm_get_payment_page_query_vars() as $query_var){
		// set if
		if( $isset_query_var = mgm_get_query_var($query_var) ){
			// process
			$process_payments = true; break;
		}
	}
	
	// If buddy press registration page is used. issue #1085
	if(!$process_payments) $process_payments = mgm_is_bp_registration();
	
	// check
	if( $process_payments ) {	
		// payment html
		mgm_get_transaction_page_html(false);
		// exit
		exit();				
	}
		
	// download flag // wp-ecommerce also uses download as slug, check
	if(!$download_slug = mgm_get_class('system')->get_setting('download_slug')) $download_slug = 'download';
	
	// download call 
	if( $isset_download_slug = mgm_get_query_var($download_slug) ) {	
		// get method
		$code = mgm_request_var('code', '', true); 		
		// check								
		mgm_download_file($code);		
		// exit 		
		exit();
	}	
}

/**
 * router for url protection, API calls
 * 
 */
function mgm_url_router($wp){
	global $wpdb,$route,$window_title;	
	
	// trim
	$current_uri = trim($_SERVER['REQUEST_URI']);
	
	// pre process hook for url router
	do_action('mgm_url_router_pre_process');	
	
	// proxy protector for all files in mgm/downloads - more will be added later	
	if(isset($_GET['protect']) && isset($_GET['file'])){
		// get method
		$file    = strip_tags($_GET['file']); // file	
		$protect = strip_tags($_GET['protect']);// protected folder
		// check								
		mgm_stream_file($file,$protect); 
		// exit
		exit;
	}
	
	// check admin 
	if(!is_super_admin()){		
		// TODO, improve code for less query, WARNING, not to use direct URI, posibility of SQL injection	
		// having all is better to protet all scenario
		// sql
		$sql = "SELECT url,membership_types FROM `".TBL_MGM_POST_PROTECTED_URL."` WHERE `post_id` IS NULL ORDER BY LENGTH(`url`) DESC";
		// direct urls
		$direct_urls = $wpdb->get_results($sql);		
		// check
		if($direct_urls){
			// loop
			foreach($direct_urls as $direct_url){
				// url path only
				$uri = trim(parse_url($direct_url->url,PHP_URL_PATH));
				// append end
				if(substr($uri,-1) == '*'){
					$uri = preg_quote(str_replace('*','',$uri), '/') . '(.*)';
				}elseif(substr($uri,-4) == ':any'){
					$uri = preg_quote(str_replace(':any','',$uri), '/') . '(.*)';
				}else{
					$uri = preg_quote($uri,'/');
				}
				// pattern
				$uri_pattern = "#{$uri}#i";			
				// match
				if(!empty($uri) && (strcasecmp($uri, $current_uri) == 0 || preg_match($uri_pattern, $current_uri))){
					// membership types
					$membership_types = json_decode($direct_url->membership_types,true);
					// check
					$current_user = wp_get_current_user();
					// access
					$access = false;
					// check
					if($current_user->ID){
						// get member
						$user_membership_types = array();
						// default
						$user_membership_types[] = mgm_get_user_membership_type($current_user->ID,'code');
						// multiple
						$user_membership_types[] = mgm_get_subscribed_membershiptypes($current_user->ID);
						// loop 
						if(is_array($membership_types)){
							// loop
							foreach($membership_types as $membership_type){
								// check
								if(in_array($membership_type,$user_membership_types)){
									// set
									$access = true; break;
								}
							}
						}
					}else {
						//issue #1173
						if(is_array($membership_types) && !$current_user->ID ){
							// loop
							foreach($membership_types as $membership_type){
								// check
								if($membership_type == 'guest'){
									// set
									$access = true; break;
								}
							}
						}
					}					
					
					// add filter
					if(!$access){
						add_filter('the_content', 'mgm_url_content_protection');	
					}				
				}
			}
		}	
	}
	
	// rest api request
	if(mgm_is_restapi_request($current_uri) && mgm_api_access_allowed()){						
		// forward to api handler
		mgm_restapi_server::init(); exit;		
	}		
	
	// post process hook for url router
	do_action('mgm_url_router_post_process');	
}

/**
 * disable redirect if api 
 */
function mgm_disable_redirect_canonical($redirect_url){
	// return
	return (mgm_is_restapi_request($redirect_url) && mgm_api_access_allowed());
}

/**
 * check if api request
 */
function mgm_is_restapi_request($current_uri){
	// return
	return preg_match('/^' . preg_quote(MGM_API_URI_PREFIX, '/') . '/', $current_uri);
}
/**
 * url content protection
 */
function mgm_url_content_protection($content){
	// return 'Protected';
	$system_obj = mgm_get_class('system');
	// check
	$current_user = wp_get_current_user();
	// message code	
	if($current_user->ID){// logged in user
		$message_code = mgm_post_is_purchasable() ? 'private_text_purchasable' : 'private_text_no_access';
	}else{// logged out user
		$message_code = mgm_post_is_purchasable() ? 'private_text_purchasable_login' : 'private_text';
	}
	// protected_message	
	$protected_message = sprintf('<div class="mgm_private_no_access">%s</div>',mgm_private_text_tags(mgm_stripslashes_deep($system_obj->get_template($message_code, array(), true))));			
	// filter message
	$protected_message = mgm_replace_message_tags($protected_message);
	
	// return
	return $content = $protected_message;
}

// end file	