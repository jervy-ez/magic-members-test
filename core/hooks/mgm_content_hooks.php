<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * content hooks and callbacks
 *
 * @package MagicMembers
 * @since 1.0
 */
  
// [content filters] not implemented
// add_filter('the_excerpt'            , 'mgm_filter_excerpt', 1); 
// Issue #1329
add_filter('mgm_sidebar_widget_text_title','do_shortcode', 11);
add_filter('mgm_sidebar_widget_text_text','do_shortcode', 11);

// [content protection]
// what it does: adds protection to post automatically, manual [private] tags are parsed by shortcode parser
add_filter('the_excerpt'               , 'mgm_excerpt_protection', 12); // protect, execute after do_shortcode aka shortcode processor
add_filter('the_content'               , 'mgm_content_protection', 12); // protect, execute after do_shortcode aka shortcode processor
// if buddy press is enabled we are protecting the content.
if( mgm_is_plugin_active('buddypress/bp-loader.php') ){
	add_filter( 'bp_activity_get'      , 'mgm_bp_activity_content_protection',12); //protect, buddypress activity stream.
}

// [download] - issue #1702 - short code available so here commented
// issue #1766 -if we use third level nested shorted download its not working so uncommented again.
add_filter('the_excerpt'               , 'mgm_download_parse'); // download tag
add_filter('the_content'               , 'mgm_download_parse'); // download tag
add_filter('wp_list_pages_excludes'    , 'mgm_list_pages_excludes');// exclude pages from pages menu
add_filter('wp_list_pages'             , 'mgm_list_pages');//add custom menu in pages menu

// [hide categories]
add_action('pre_get_posts'             , 'mgm_hide_protected', 12);
// [nested tags parse]
if( bool_from_yn(mgm_get_setting('enable_nested_shortcode_parsing')) ) {	
	add_filter('the_excerpt'           , 'do_shortcode',12); // nested shortcode parsing, after default do_shortcode() 11 in wp-includes/shortcodes.php
	add_filter('the_content'           , 'do_shortcode',12); // nested shortcode parsing, after default do_shortcode() 11 in wp-includes/shortcodes.php
}
// add_filter('the_content'			   , 'mgm_load_payment_jsfiles');//load payment js/css files, discarded
add_action('mgm_url_router_post_process', 'mgm_guest_lockdown');
// [footer credits]
add_action('mgm_footer_credits'        , 'mgm_print_footer_credits');
add_action('wp_footer'                 , 'mgm_footer_credits');
add_filter('retrieve_password_title'   , 'mgm_modify_retrieve_password_emailsubject');//modify password reset email subject
add_filter('retrieve_password_message' , 'mgm_modify_retrieve_password_emailbody');//modify password reset email body
add_filter('password_reset_title'      , 'mgm_modify_lost_password_emailsubject');//modify lost password reset email subject
add_filter('password_reset_message'    , 'mgm_modify_lost_password_emailbody',10,3);//modify lost password reset email body
//add_action('mgm_filter_scripts'	   , 'mgm_filter_scripts');//to remove duplicate scripts from $wp_scripts
add_action('wp_head'		   		   , 'mgm_filter_scripts', 100);//to remove duplicate scripts from $wp_scripts
//add_action('admin_head'		   	   , 'mgm_filter_scripts', 100);//to remove duplicate scripts from $wp_scripts
add_action('admin_enqueue_scripts'	   , 'mgm_filter_scripts', 100);//to remove duplicate scripts from $wp_scripts
add_action('wp_before_admin_bar_render', 'mgm_profile_admin_bar_link' );//modify admin bar my edit profile link to custom profile url
// add_action('mgm_facebook_logout'    , 'mgm_facebook_logout_link');// Facebook logout link hook
// feed content, needed for displaying protection propeorly, blocked
// add_filter('the_content_feed'       , 'mgm_feed_content_protection',10);
// add_filter('the_excerpt_rss'        , 'mgm_feed_content_protection',10);
// add_filter('mgm_post_update'        , 'mgm_post_update_test',10,2);
add_filter('template_include'          , 'mgm_template_include');
// add_action('template_redirect'      , 'mgm_template_redirect');
// add_filter('mgm_custom_pages'       , 'mgm_add_new_custom_page');
// wp https ssl
// add_filter('force_ssl'              , 'mgm_wphttps_force_ssl', 10, 3);

//bbpress forum content protection check
if( mgm_is_plugin_active('bbpress/bbpress.php') ){	
	add_filter('bbp_has_forums',  'mgm_bbp_content_protection');
	add_filter('bbp_has_topics',  'mgm_bbp_content_protection');
	add_filter('bbp_has_replies', 'mgm_bbp_content_protection');
}

/**
 * bbpress user no access to create topics/replies
 */
function mgm_bbp_user_no_access ($args){
	return '';
}
/**
 * bbpress content protection for forums/topics/replies
 */
function mgm_bbp_content_protection($have_posts){
	//bbp forum id
	$forum_id = bbp_get_forum_id();	
	//check - issue #1743
	if($forum_id){
		//check user access
		if(is_super_admin() || mgm_user_has_access($forum_id,false)) {
			//return
			return $have_posts;
		}else {	
			$have_posts = null;
			//no access for create topics/replies
			add_filter( 'bbp_current_user_can_access_create_topic_form','mgm_bbp_user_no_access');
			add_filter( 'bbp_current_user_can_publish_replies','mgm_bbp_user_no_access');
	 	}
	}    
 	return $have_posts;
}

/**
 * add short codes
 */
function mgm_add_content_shortcodes(){
	// 'user_profile_edit', 
	// [content shortcodes]
	$content_shortcodes = array('private','private_or','private_and','user_profile','user_subscription','user_unsubscribe','user_other_subscriptions',
								'user_upgrade','user_purchase_another_membership','user_subscribe','user_register','user_has_access',
								'user_account_is','user_contents_by_membership','user_lostpassword','user_login','user_field',
								'logout_link','transactions','no_access','payperpost_pack','payperpost','packages','membership_details',
								'membership_contents','posts_for_membership','membership_extend_link','subscription_packs','download_error',
								'user_id_is','user_pack_is','user_payment_history','user_list','user_facebook_login','user_public_profile',
								'user_facebook_registration','user_purchased_contents','user_purchasable_contents');  
	// add callback for all
	foreach($content_shortcodes as $shortcode){
		// add callback
		add_shortcode($shortcode, 'mgm_shortcode_parse');
	}
	// add callback for download
	$shortcode_download  =  mgm_get_class('system')->get_setting('download_hook', 'download');
	add_shortcode($shortcode_download, 'mgm_shortcode_download_parse');	
}
// add
mgm_add_content_shortcodes();

/**
 * add post type colmnss
 */
function mgm_add_posts_columns(){
	// add
	add_filter( 'manage_posts_columns'       , 'mgm_manage_posts_columns', 10, 2 );// add column
	add_filter( 'manage_pages_columns'       , 'mgm_manage_posts_columns', 10 );// add column		
	add_action( 'manage_posts_custom_column' , 'mgm_manage_posts_custom_column', 10, 2 );// add colum row	
	add_action( 'manage_pages_custom_column' , 'mgm_manage_posts_custom_column', 10, 2 );// add colum row	
}
// add
mgm_add_posts_columns();

/** 
 * admin bar profile link change 
 */
function mgm_profile_admin_bar_link() {

	global $wp_admin_bar, $user_identity;
    $user_id = get_current_user_id();

	$system_obj = mgm_get_class('system'); 
	
	$url = $system_obj->get_setting('profile_url');
	
	//issue #1213
	$flag = true;
	
	// if custom profile URL is empty & buddypress is active it will take default buddypress profile urls.
	if( mgm_is_plugin_active('buddypress/bp-loader.php') && empty($url)){
		$flag =false;
	}

	if($flag) {
		
		if(empty($url))
			$url =  get_site_url().'/profile';
			
		$wp_version_check = mgm_compare_wp_version( '3.3.1', '>=' );
		// ((version_compare($GLOBALS['wp_version'], '3.3.1')) >= 0) ? true : false;	
	
		// for WP 3.3 versionion
	    if($wp_version_check) {
		    if ( 0 != $user_id ) {
		        $avatar = get_avatar( get_current_user_id(), 16 );
			    $avatarbig = get_avatar( get_current_user_id(), 64 );
		        $id = 'my-account';
		        $wp_admin_bar->add_menu( array( 'id' => $id, 'title' => $avatar . $user_identity,  'href' => $url, 'meta'=>array() ) );
				$wp_admin_bar->add_menu( array( 'parent' => 'user-actions', 'id' => 'user-info', 'title' => $avatarbig .'<span class="display-name">'.$user_identity.'</span>',  'href' => $url, 'meta'=>array() ) );
		        $wp_admin_bar->add_menu( array( 'parent' => 'user-actions', 'id' => 'edit-profile', 'title' => __( 'Edit My Profile' ), 'href' => $url, 'meta'=>array()) );
		        $wp_admin_bar->add_menu( array( 'parent' => 'user-actions', 'id' => 'logout', 'title' => __( '<strong>Log Out</strong>' ), 'href' => wp_logout_url(), 'meta'=>array() ) );
		    }    
	    }else {
			// for WP 3.2.1 version 
			if ( 0 != $user_id ) {
		        $avatar = get_avatar( get_current_user_id(), 16 );
		        $id = ( ! empty( $avatar ) ) ? 'my-account-with-avatar' : 'my-account';
		        $wp_admin_bar->add_menu( array( 'id' => $id, 'title' => $avatar . $user_identity,  'href' => $url, 'meta'=>array() ) );
		        //$wp_admin_bar->add_menu( array( 'parent' => $id, 'id' => 'user-info', 'title' => $avatar, 'href' => $url) );
		        $wp_admin_bar->add_menu( array( 'parent' => $id, 'id' => 'edit-profile', 'title' => __( 'Edit My Profile' ), 'href' => $url, 'meta'=>array()) );
		        $wp_admin_bar->add_menu( array( 'parent' => $id, 'id' => 'logout', 'title' => __( '<strong>Log Out</strong>' ), 'href' => wp_logout_url(), 'meta'=>array() ) );
		    }
	    }
	
		 // if buddy press is enabled we are overwritting css - issue #1169.
		//if( mgm_is_plugin_active('buddypress/bp-loader.php') ){
		    
			$css_file = MGM_ASSETS_URL . 'css/admin/mgm.adminbar.css'; 
		    $css_link_format = '<link rel="stylesheet" href="%s" type="text/css" media="all" />';
		    $css_link = sprintf($css_link_format, $css_file);
		    
		    echo $css_link;
		//}   
	}
}

/**
 * Buddy press activity stream content protecion.
 */
function mgm_bp_activity_content_protection($activity){

	// get user id
	$user_id = get_current_user_id();
	
	// protecting activity based on user	
	foreach ($activity['activities'] as $key =>$obj){
		if($user_id != $obj->user_id && !is_admin()) {
			$activity['activities'][$key]->content = mgm_content_protection($obj->content);	
		}
	}
			
	return $activity;
}
/**
 * parse shortcodes
 *
 * @param array @args
 * @param string @content
 * @param string @tag
 * @return string $content
 */
function mgm_shortcode_parse($args, $content, $tag) {	
	// current_user
	$current_user = wp_get_current_user();		
	// system
	$system_obj = mgm_get_class('system'); 
	// issue#: 859
	// add <p> to the beggining and </p> to the end of content
	// as WP pass $content with incomplete p tags
	$content = '<p>' . $content . '</p>';
	// remove any '<p></p> found 
	$content = str_replace(array('<p></p>'),'', $content);
	// @todo test with force_balance_tags();  
	// tag block
	switch ($tag) {
		case 'private':			
			// [private] protected content [/private]
			if (mgm_protect_content() || mgm_post_is_purchasable()) {		
				//issue #1687
				if(mgm_content_post_access_delay($args)){					
					$content  = mgm_replace_postdealy_content($content);
				}else{
					$content  = mgm_replace_content_tags($tag, $content, $args);
				}			
			}
		break;
		case 'private_or':
			// [private_or#member] protected content [/private_or]
			// [private_or membership_type="member"] protected content [/private_or]	
			$membership_type = (isset($args['membership_type'])) ? $args['membership_type'] : str_replace('#', '', mgm_array_shift($args));
			// match
			if($membership_type) $content = mgm_replace_content_tags($tag, $content, $membership_type);
		break;
		case 'private_and':
			// [private_and#member] protected content [/private_and]
			// [private_and membership_type="member"] protected content [/private_and]	
			$membership_type = (isset($args['membership_type'])) ? $args['membership_type'] : str_replace('#', '', mgm_array_shift($args));
			// match
			if($membership_type) $content = mgm_replace_content_tags($tag, $content, $membership_type);
		break;	
		case 'payperpost_pack':
			// [payperpost_pack#1] : 1 = pack_id, packs to be created in MGM -> PayPerPost -> Post Packs, use the id here
			// [payperpost_pack id=1] : 1 = pack_id	
			$pack_id = (isset($args['id'])) ? $args['id'] : str_replace('#', '', mgm_array_shift($args));
			// match
            if ($pack_id) $content = mgm_replace_content_tags($tag, $content, $pack_id);            
		break;	
		case 'payperpost':
			// [payperpost#1] : 1 = post_id
			// [payperpost id=1] : 1 = post_id						
            $pack_id = (isset($args['id'])) ? $args['id'] : str_replace('#', '', mgm_array_shift($args));
			// match
			if($pack_id) $content = mgm_replace_content_tags($tag, $content, $pack_id);            
		break;
		case 'subscription_packs':
			// subscription packs / payment gateways
			$content = mgm_sidebar_register_links($current_user->user_login, true, 'page');// @todo test
		break;
		case 'user_unsubscribe':
			// user unsubscribe 
			$content = mgm_user_unsubscribe_info(null,$args);// view current user			
		break;
		case 'user_other_subscriptions':
			// other subscriptions
			$content = mgm_user_other_subscriptions_info();
		break;	
		case 'membership_details':		
			// user subscription 
			$content = mgm_membership_details();// view current user
		break;
		case 'user_upgrade':
			// user upgrade membership
			$content = mgm_get_upgrade_buttons($args);
		break;
		case 'user_purchase_another_membership':
			// purchase another subscription
			$content = mgm_get_purchase_another_subscription_button($args);
		break;
		case 'user_subscribe':
		case 'user_register':					
			// named
			if($method = mgm_get_var('method', '', true)){
				// method
				switch($method){
					case 'login':
						$content = mgm_user_login_form(false);		
					break;
					case 'lostpassword':
						$content = mgm_user_lostpassword_form(false);		
					break;
					default:
						if(preg_match('/^payment/', $method)){
							$content = mgm_transactions_page($args);
						}
					break;
				}
			}else{
				$content = mgm_user_register_form($args);	
			}					
		break;		
		case 'user_profile':
			// user profile 
			$content = mgm_user_profile_form(NULL,false,$args);// view
		break;	
		case 'user_public_profile':
			// user profile 
			$content = mgm_user_public_profile($args);// view
		break;	
		case 'transactions':
			// user payments/transactions			
			$content = mgm_transactions_page($args);
		break;	
		case 'user_contents_by_membership':	
			// user contents by membership level	
			$content = mgm_membership_content_page();	
		break;
		case 'user_lostpassword':
			// user lost password form
			$content = mgm_user_lostpassword_form(false);
		break;	
		case 'user_login':
			// user login form
			$content = mgm_user_login_form(false);
		break;		
		case 'user_field':
			// user field
			$content = __('Experimental', 'mgm');
		break;	
		case 'membership_contents':
			// membership contents 
			$content = mgm_membership_contents();// view current user
		break;	
		case 'logout_link':
			// custom logout link
			// [logout_link#Logout]	
			// [logout_link label="Logout"]	
			$label = (isset($args['label'])) ? $args['label'] : str_replace('#', '', mgm_array_shift($args));					
			// match
			$content = mgm_logout_link($label);
		break;	
		case 'membership_extend_link': //INCOMPLETE
			// membership extend link
			// [membership_extend_link#Extend]	
			// [membership_extend_link label="Extend"]	
			$label = (isset($args['label'])) ? $args['label'] : str_replace('#', '', mgm_array_shift($args));	
			// match
			$content = mgm_membership_extend_link($label);
		break;		
		case 'download_error': 
			// content
 			$content = (isset($_GET['error_code'])) ? mgm_download_error($_GET['error_code']) : '';	 			
		break;						
		case 'user_payment_history': 
			// content
 			$content = mgm_user_payment_history();// view current user
		break;
		case 'user_list': 
			// content
 			$content = mgm_generate_member_list($args);
		break;
		case 'user_facebook_login': 
			// content
 			$content = mgm_generate_facebook_login();
 		break;
		case 'user_facebook_registration': 
			// content
 			$content = mgm_generate_facebook_registration();
 		break;
		case 'user_purchased_contents': 
			// content
 			$content = mgm_generate_purchased_contents();
 		break;
		case 'user_purchasable_contents': 
			// content
 			$content = mgm_generate_purchasable_contents();
 		break;
		/*case 'addon':
			// content
 			$content = mgm_purchase_addons($args);
		break;*/
		default:			
			// default, which are not shortcode but content tags 
			$args = str_replace('#', '', mgm_array_shift($args));
			// match
			$content = mgm_replace_content_tags($tag, $content, $args);
		break;
	}
	// return
	return $content;
}

/**
 * replace shortcode tag
 *
 * @since 1.0
 * @param string $function tagname
 * @param string $matches the content
 * @param mixed $argument
 * @return string $return
 */
function mgm_replace_content_tags($function, $matches, $argument = false ) {
	global $user_data;
	// current_user
	$current_user = wp_get_current_user();
	// init
	$return = '';	
	// tag 	
	switch ($function) {		
		case 'private':
			// [private] protected content [/private]
			$return = mgm_replace_post($matches);
		break;					
		case 'private_or':
			// [private_or] protected content [/private_or]
			if (mgm_user_has_access() || mgm_user_has_access($argument)){
                $return = mgm_replace_post($matches);
			}
		break;				
		case 'private_and':
			// [private_and] protected content [/private_and]	
			if (mgm_user_has_access() && mgm_user_has_access($argument)){
                $return = mgm_replace_post($matches);
			}
		break;					
		case 'user_has_access':
			// [user_has_access#123] : 123 = post_id
			if (mgm_user_has_access($argument)) {
				$return = $matches;
			}
		break;		
		case 'user_account_is':
			// [user_account_is#member] :  member = membership level OR
			// [user_account_is#member|free|guest]
			$user_id = false;
			// member from token
			if (!$current_user && isset($_GET['token']) && mgm_use_rss_token()) {
				// get user
				$current_user = mgm_get_user_by_token(strip_tags($_GET['token']));
			}
			// set 
			$user_id = $current_user->ID;
			// user membership type
			$user_membership_types = mgm_get_subscribed_membershiptypes($user_id);
			// membership types passed
			$membership_types = array_map('strtolower', explode('|', $argument));	
			
			//issue #1174
			if(is_super_admin()) {
				$return = $matches;
			}elseif($user_id > 0){
				// validate
				// If the user is active issue #1143
				if (mgm_is_member_active($user_id) && array_diff($membership_types, $user_membership_types) != $membership_types) { // if any match found
					$return = $matches;
				}
			}else {
				if (array_diff($membership_types, $user_membership_types) != $membership_types) { // if any match found
					$return = $matches;
				}
			}
			
			
		break;
		case 'user_id_is':			
			// [user_id_is#123]:	123 =  User Id OR
			// [user_id_is#123|234|456]
			$user_ids = explode('|', $argument);
			if (mgm_user_id_is($user_ids)){                
                $return = $matches;
			}
		break;
		case 'user_pack_is':			
			// [user_pack_is#123]:	123 =  Pack Id OR
			// [user_pack_is#123|234|456]
			$pack_ids = explode('|', $argument);
			if (mgm_user_pack_is($pack_ids)){                
                $return = $matches;
			}
		break;		
		case 'no_access':
			// [no_access]
			if (!mgm_user_has_access()) {
				$return = $matches;
			}
		break;
       	case 'payperpost_pack':
			// [payperpost_pack#1] : 1 = pack_id, packs to be created in MGM -> PayPerPost -> Post Packs, use the id here
            $return = mgm_parse_postpack_template($argument);
	    break; 	
		case 'payperpost':
			// [payperpost#1] : 1 = pack_id
            $return = mgm_parse_post_template($argument);
	    break;	
       	case 'posts_for_membership':
			// check
       		if(!empty($argument)) {
				// types
				$membership_types = array_map('strtolower', explode('|', $argument));	
				// return								
	       		$return = mgm_posts_for_membership($membership_types);
       		}
       		break;
		// no tag
		default:
			// default
			$return = mgm_replace_post($matches);
		break;
	}
	// return as it is, #788 issue
	return $return;// mgm_stripslashes_deep($return);
}

/**
 * post replace
 * 
 * @param array $matches
 */
function mgm_replace_post($matches) {	
	global $wpdb,$post;
	// current_user
	$current_user = wp_get_current_user();
	// get system
	$system_obj = mgm_get_class('system');

	// returns nothing in the event of an empty string
	if ($matches == '')  return '';		
	
	// if excerpt display and enable_excerpt_protection is disbled , bypass protection: issue# 887
	if( !bool_from_yn($system_obj->get_setting('enable_excerpt_protection')) && !is_single()&& (is_archive() || is_home() || is_search())) {			
		return mgm_replace_message_tags($matches);
	}	
	
	// init
	$return = '';
	
	// die('A');
    // MARK FOR NESTED SHORT CODE ERROR ISS:66
	// user has access copes with validation against user level and ppp
	if (mgm_user_has_access()) { // user has access	
		// feed, do not add span around
		$return = (is_feed()) ? $matches : sprintf('<div class="mgm_private_access">%s</div>', $matches) ;		
	} else {
		// check if Private Tag Redirection set ( Content Control -> Access -> Private Tag Redirection Settings)
		if (mgm_check_redirect_condition($system_obj)) { // redirect for user set 	
			// return and exit other processing
			return mgm_no_access_redirect($system_obj);	
		}else{
		// no redirect set
			if (!mgm_post_is_purchasable()) { // user has no access and post is not purchable	
				// logged in
				if ($current_user->ID > 0) {
					$return = mgm_stripslashes_deep($system_obj->get_template('private_text_no_access', array(), true));					
				} else {
				// not logged in
					$return = mgm_private_text_tags(mgm_stripslashes_deep($system_obj->get_template('private_text', array(), true)));	
				}	
			}else{// post is purchasable				
				// get button
				//$return = mgm_get_post_purchase_button();
				//issue #1397
				$return = mgm_parse_post_template();
			}// end purchasable check
			
		}// end redirect condition check
		
		// wrap with css class if not feed
		$return = (is_feed()) ? $return : sprintf('<div class="mgm_private_no_access">%s</div>', $return);		
	}// end access check
	
	// additionally filter message tags	
	return mgm_replace_message_tags($return);	
}

/**
 * hide protected post/pages from apearing in list/feeds etc
 *
 * @param object $query
 * @return object $query
 * @since 1.0
 */
function mgm_hide_protected($query) {
	global $post;
	
	// do not run when admin section loaded #1459
	if( is_admin() ){
		return $query;
	}

	// if loading from feed	
	if (is_feed() && isset($_GET['token']) && mgm_use_rss_token()) {
	// get user by rss token, only for feed	
		$user = mgm_get_user_by_token(strip_tags($_GET['token']));	
	}else {
		// current user
		if( function_exists( 'wp_get_current_user' ) ){
			$user = wp_get_current_user();
		}else{
			global $user_ID;
			// pick
			$user = get_userdata($user_ID);
		}
	}		
	
	// get system
	$system_obj = mgm_get_class('system');
	
	// flag
	$run_cat_notin = $run_term_notin = false;		
	// user is not a spider
	if (!mgm_is_a_bot()) {
	 	// hide post	
		$hide_posts = mgm_content_exclude_by_user($user->ID, 'post');// hide post

		// set filter								
		if (is_array($hide_posts) && !empty($hide_posts)) {								
			$query->set('post__not_in', array_unique($hide_posts)); // set negation			
		}		

	 	// hide cats	
		$hide_cats = mgm_content_exclude_by_user($user->ID, 'category');//hide cats		
				
		// set filter					
		if (is_array($hide_cats) && !empty($hide_cats)) {
			// flag				
			$run_cat_notin = true;				
			//category not found redirection									
			//skip admin and home 
			//consider only posts:	
			if(!is_super_admin() && !is_home() && is_single() ) {	
				// url
				$category_access_redirect_url = $system_obj->get_setting('category_access_redirect_url');				
				//skip if same url:
				if(!empty($category_access_redirect_url) && trailingslashit(mgm_current_url()) != trailingslashit($category_access_redirect_url)) {										
					//check returned category ids belongs to the loaded post:
					if(isset($post->ID) && is_numeric($post->ID)) {
						//get post categories
						$post_cats = wp_get_post_categories($post->ID);
						// loop
						foreach ($post_cats as $cat) {
							//redirect if post category exists in blocked categories
							if(in_array($cat, $hide_cats)) {
								//redirect:								
								mgm_redirect($category_access_redirect_url);exit;
							}
						}
					}					
				}
			}
			
			//issue#: 510					
			if($run_cat_notin) {
				// set	
				$query->set('category__not_in', array_unique($hide_cats)); // set negation					
				// issue#: 510
				if(substr(get_bloginfo('version'), 0,3) > 3.0 && !is_page()) {					
					//Note: selectively attach the filter to not apply in other scenarios
					//issue #1600
					$post_name = $query->query_vars['name'];					
					if(empty($post_name)) {
						$current_uri = trim($_SERVER['REQUEST_URI']);
						$uri = explode('?', $current_uri);
						$uriArr = explode('/',$uri[0]);
					
						if (!empty($uriArr)){
							$post_name = $uriArr[1];
						}
					}
														
					$member  = mgm_get_member($user->ID);
					$membership_type = $member->membership_type;
					$membership_type = (empty($membership_type)) ? 'guest' : $membership_type;
					$arr_memberships = mgm_get_subscribed_membershiptypes($user->ID, $member);
					if(!in_array($membership_type, $arr_memberships)) $arr_memberships[] = $membership_type;
					
					$accessible = false;
					$post_data = mgm_get_post_data_by_name($post_name);
					// check found
					if( isset($post_data->ID) ){
						$post_obj = mgm_get_post($post_data->ID);	
						if(count(array_intersect($post_obj->access_membership_types, $arr_memberships)) > 0){
							$accessible = true;
						}
					}	
					//not accessible add filter
					if(!$accessible) {
						//to filter posts as per category__not_in values
						add_filter('posts_search', 'mgm_attach_category_not_in');					
					}
				}					
			}
			// if on category archive listing page: check cateory is accessible, if not redirect to category_access_redirect_url setting url 
			if(!is_super_admin() && !is_home() && is_category() ) {	
				// url			
				$the_url = mgm_current_url();
				//get archived category details
				$loaded_cat = get_category_by_path($the_url, false);
				
				//getting subcategory by path - issue #1578
				if(empty($loaded_cat)){					
					$flag = false;					
					$url_segments= preg_split('#/#',$the_url);					
					foreach ($url_segments as $key=> $url_segment) {
						if(strtolower($url_segment) == 'category') {
							$flag = $key;
						}
					}					
					if($flag){
						if(!empty($url_segments[$flag+2])){
							$slug = $url_segments[$flag+2];
							$loaded_cat = get_category_by_slug($slug);
						}
					}
				}
								
				// url
				if(!isset($category_access_redirect_url))
					$category_access_redirect_url = $system_obj->get_setting('category_access_redirect_url');		
				// issue #: 657
				// if the loaded category cannot be accessed by the user, and if category_access_redirect_url is set, redirect				
				if(isset($loaded_cat->cat_ID) && in_array($loaded_cat->cat_ID, $hide_cats) && !empty($category_access_redirect_url) && trailingslashit($the_url) != trailingslashit($category_access_redirect_url))
				{
					// redirect:								
					mgm_redirect($category_access_redirect_url);exit;	
				}
			}
		}		
	 } // endif
	
	 // hide terms	
	 $hide_terms = mgm_content_exclude_by_user($user->ID, 'taxonomy');//hide terms			 
	
	 // set filter					
	 if (is_array($hide_terms) && !empty($hide_terms)) {	
	 	// flag				
	    $run_term_notin = true;	
		
		// set filter
		$query->set('tag__not_in', array_unique($hide_terms)); // set negation	
		
		// set in search
		if(substr(get_bloginfo('version'), 0,3) > 3.0 && !is_page()) {
			//note: selectively attach the filter to not apply in other scenarios
			add_filter('posts_search', 'mgm_attach_tag_not_in');//to filter posts as per tag__not_in values	
		}	
	 } 
	
	 // term check
	 if($run_cat_notin || $run_term_notin){
	 	add_filter('list_terms_exclusions', 'mgm_exclude_terms'); // terms		
	 }
	 // return 
	 return $query;
}
/**
 * Filter to exclude the posts belong to category__not_in categories
 * Fix for the issue: (category__not_in doesn't seem to filter posts in MGM context)
 *
 * @param string $search
 * @return unknown
 */
function mgm_attach_category_not_in($search) {
	global $wpdb;
	if( $category__not_in = mgm_get_query_var('category__not_in') ) {				
		$where = sprintf(' AND ( %s.ID NOT IN (
									SELECT object_id
									FROM %s 
									WHERE term_taxonomy_id IN (%s)
							   ) ) ', $wpdb->posts, $wpdb->term_relationships, implode(',', $category__not_in)) ;
		$search .= $where;
	}
	
	return $search;
}

/**
 * Filter to exclude the posts belong to category__not_in categories
 * Fix for the issue: (category__not_in doesn't seem to filter posts in MGM context)
 *
 * @param string $search
 * @return unknown
 */
function mgm_attach_tag_not_in($search) {
	global $wpdb;
	if( $tag__not_in = mgm_get_query_var('tag__not_in') ) {				
		$where = sprintf(' AND ( %s.ID NOT IN ( SELECT object_id FROM %s WHERE term_taxonomy_id IN (%s)) ) ', 
		                $wpdb->posts, $wpdb->term_relationships, implode(',', $tag__not_in)) ;
		$search .= $where;
	}
	
	return $search;
}

/**
 * exclude post/pages by membership type
 *
 * @param int $user_id
 * @param string $content_type
 * @return string
 */
function mgm_content_exclude_by_user($user_id = 0, $content_type='category') {
	// not for admin
	if(is_super_admin()) 
		return array();
	
	// global
	global $wpdb;
	// system
	$system_obj = mgm_get_class('system');
	// protecction	
	$content_hide_by_membership = $system_obj->get_setting('content_hide_by_membership');	
	// get member
	$member  = mgm_get_member($user_id);
	$user        = wp_get_current_user();	
	$temp_member = new stdClass();	
	$membership_type = $member->membership_type;
	// set default
	$membership_type = (empty($membership_type)) ? 'guest' : $membership_type;
	//get user membership types: multiple level membership issue#: 400 modification
	$arr_mt = mgm_get_subscribed_membershiptypes($user_id, $member);
	// store
	if(!in_array($membership_type, $arr_mt)) $arr_mt[] = $membership_type;
	
	// on type
	switch($content_type){
		case 'category':
		case 'taxonomy':
			// category
			if (!$hide_terms = wp_cache_get($content_type . '_exclusion_' . $user_id, 'users')) {				
				// exclude protected terms 
				$hide_terms = array();
				// get post terms settings
				$post_terms = mgm_get_class('post_' . $content_type);					
				// loop set				
				foreach($post_terms->get_access_membership_types() as $term_id=>$membership_types ) {
					// exclude
					if($membership_types){ // not set public access						
						// multiple level membership issue#: 400 modification
						if(array_diff($membership_types, $arr_mt) != $membership_types)	continue;
						// hide
						$hide_terms[] = $term_id;
					}			
				}
				// set cache
				wp_cache_set($content_type . '_exclusion_' . $user_id, $hide_terms, 'users');
			}
			// return
			return $hide_terms; 
			// end check
		break;		
		case 'post':
			// post
			// $content_hide_by_membership = $system_obj->get_setting('content_hide_by_membership');
			// no check if not required
			if(!bool_from_yn($content_hide_by_membership)){
				return array();
			}			
			// check
			if (!$hide_posts = wp_cache_get('post_exclusion_' . $user_id, 'users')) {				
				// exclude protected posts 
				$hide_posts = array();
				// fetch all posts
				$posts = $wpdb->get_results("SELECT ID FROM `{$wpdb->posts}` WHERE `post_type` NOT IN('revision','attachment')");
				// check
				if($posts){
					// loop
					foreach($posts as $post){
						// get post
						$post_obj     = mgm_get_post($post->ID);
						$access_delay = $post_obj->access_delay;
						// check types
						if(is_array($post_obj->access_membership_types) && count($post_obj->access_membership_types)){
							// default
							$access = false;
							// check							
							foreach($post_obj->access_membership_types as $a_membership_type){
								// match								
								// multiple level membership issue#: 400 modification
								if(in_array($a_membership_type, $arr_mt)) {
									// done
									$access = true; 
									// check protection
									if(bool_from_yn($content_hide_by_membership)) {
										// temp
										$temp_member->membership_type = $a_membership_type;
										//deny access if delay: issue#: 516
										if(mgm_check_post_access_delay($temp_member, $user, $access_delay)){
											//OK: 
											break;	
										}else {											
											$access = false; 		
										}
									}									
								}									
							}
							//issue #841
							if (bool_from_yn($post_obj->purchasable)){
								$access = true; 		
							}
							
							// protect
							if(!$access){
								$hide_posts[] = $post->ID;
							}												
						}
						// unset
						unset($post_obj);
					}
				}				
				// set cache
				wp_cache_set('post_exclusion_' . $user_id, $hide_posts, 'users');
			}
			// return
			return $hide_posts; 
			// end check
		break;
	}		
	// empty
	return array();	
}

/**
 * exclude terms, cates, tags
 *
 * @param string $exclusions
 * @return string $exclusions
 */
function mgm_exclude_terms($exclusions = null) {
	global $user_ID;
	// if loading from feed	
	if (is_feed() && isset($_GET['token']) && mgm_use_rss_token()) {
	// get user by rss token, only for feed	
		$user = mgm_get_user_by_token(strip_tags($_GET['token']));
		$user_ID = $user->ID;	
	}
	elseif (!$user_ID){
		$current_user = wp_get_current_user();	
		$user_ID = $current_user->ID;
	}
	
	// cats	
	if ($hide_cats = mgm_content_exclude_by_user($user_ID, 'category')) {
		// loop
		foreach((array)$hide_cats as $term_id ) {
			$exclusions .= 'AND t.term_id <> ' . intval($term_id) . ' ';
		}
	}
	
	// taxonomy	
	if ($hide_taxonomies = mgm_content_exclude_by_user($user_ID, 'taxonomy')) {
		// loop
		foreach((array)$hide_taxonomies as $term_id ) {
			$exclusions .= 'AND t.term_id <> ' . intval($term_id) . ' ';
		}
	}
	
	// return
	return $exclusions;
}

/**
 * hide pages from list/menu, called by template tag wp_list_pages()
 * 
 * @param array $excluded
 * @return array $excluded
 */
function mgm_list_pages_excludes($excluded) {
	global $wpdb, $user_ID;
	// if loading from feed	
	if (is_feed() && isset($_GET['token']) && mgm_use_rss_token()) {
	// get user by rss token, only for feed	
		$user = mgm_get_user_by_token(strip_tags($_GET['token']));
		$user_ID = $user->ID;	
	}
	// current_user
	elseif(!$user_ID){
		$current_user = wp_get_current_user();	
		$user_ID = $current_user->ID;
	}
	
	// get system object	
	$system_obj = mgm_get_class('system');
	// update			
	$excluded_pages = $system_obj->get_setting('excluded_pages');
	// exclude
	if (is_array($excluded_pages) && is_array($excluded)) {
		$excluded = array_merge($excluded, $excluded_pages);// give preference to user settings
	}	
		
	// hide post
	$hide_posts = mgm_content_exclude_by_user($user_ID, 'post');
	// check
	if($hide_posts && is_array($excluded)){
		$excluded = array_merge($excluded, $hide_posts);// give preference to mgm settings
	}
	
	// default pages, by session
	$hide_pages = mgm_exclude_default_pages();
	// check
	if($hide_pages && is_array($excluded)){
		$excluded = array_merge($excluded, $hide_pages);// give preference to mgm settings
	}
	
	// return array
	return $excluded;
}

/**
 * add items to pages menu, called by template tag wp_list_pages()
 *
 * @param string $output
 * @return string $output
 */
function mgm_list_pages($output){
	// get system object	
	$system_obj = mgm_get_class('system');
	// update			
	$enable_logout_link= $system_obj->get_setting('enable_logout_link');

	// append logout
	if ( is_user_logged_in() ) {
		// check if custom login published
		if( mgm_is_custom_page_published('login') ){	
			// enabled
           if( bool_from_yn($enable_logout_link) ){				
		   		// link
				$logout_links = sprintf('<a href="%s" title="%s">%s</a>', wp_logout_url(), __('Logout','mgm'), __('Logout','mgm'));
				// add filter
				$logout_links = apply_filters('mgm_logout_links', $logout_links);
				// output
				$output .= sprintf('<li class="page_item">%s</li>', $logout_links);
           }
		}
	}
	// return output
	return $output;
}

/**
 * get postpack template
 *
 * @param int $postpack_id
 * @param bool $guest_purchase
 * @return string $template
 */
function mgm_get_postpack_template($postpack_id, $guest_purchase=false,$postpack_post_id='',$message='pre_button'){
	// current_user
	$current_user = wp_get_current_user();
	// system
	$system_obj = mgm_get_class('system'); 	
	// currency   
	$currency      = $system_obj->setting['currency'];
	$pack_template = $system_obj->get_template('ppp_pack_template');
	//issue #1177
	$currency_sign = mgm_get_currency_symbols($system_obj->setting['currency']);		

	// get pack
	$postpack      = mgm_get_postpack($postpack_id);
	// default
	if (!$pack_template) {
		$pack_template = '<div><div><h3>[pack_name] - [pack_cost] [pack_currency]</h3></div><div>[pack_description]</div><div>[pack_posts]</div><div>[pack_buy_button]</div></div>';
	}
	
	// post
	$post_string = '';
	$cost = mgm_convert_to_currency($postpack->cost);
	$show_button = false;// if all posts purchased, dont show button	

	// template
	$template = str_replace('[pack_name]', $postpack->name, $pack_template);
	$template = str_replace('[pack_cost]', $cost, $template);
	$template = str_replace('[pack_description]', $postpack->description, $template);
	$template = str_replace('[pack_currency]', $currency, $template);
	//issue #1177
	$template = str_replace('[currency_sign]', $currency_sign, $template);
	// user or guest
	$user_id = isset($current_user->ID) ? $current_user->ID : null;

	// list of posts
	if ($postpack_posts = mgm_get_postpack_posts($postpack_id)) {
		// log
		// mgm_log($postpack_posts, __FUNCTION__);
		// init string
		$post_string = '<ul>';
		// loop
		foreach ($postpack_posts as $i=>$pack_post) {
			// check if user has purchased
			$access = mgm_user_has_purchased_post($pack_post->post_id, $user_id);
			// set button mode
			if( ! $access ){
				// enable button
				$show_button = true;
			}
			// get post
			$post = get_post($pack_post->post_id);
			// append
			$post_string .= sprintf('<li><a href="%s">%s</a></li>', get_permalink($post->ID), $post->post_title);
		}  
		// end
		$post_string .= '</ul>';
	}else{
		$post_string .= __('No posts added to this Pack', 'mgm');
	}
	// display
	$template = str_replace('[pack_posts]', $post_string, $template);	
	// get button
	$buy_button = ($show_button) ? mgm_get_postpack_purchase_button($postpack_id, $guest_purchase, $postpack_posts,$postpack_post_id) : '';
	// is register & purchase postpack
	if($message =='pre_register'){
		// template
		return str_replace('[pack_buy_button]', '', $template);
	}
	// template
	return str_replace('[pack_buy_button]', $buy_button, $template);
}

/**
 * parse and output postpack buy button
 *
 * @since 2.0
 *
 * @param int $postpack_id
 * @return string $html
 */
function mgm_parse_postpack_template($postpack_id) {	
	global $post;			
	
	// logged in 
	if(is_user_logged_in()){
	// return
		return mgm_get_postpack_template($postpack_id);
	}
	
	// guest token - issue #1396
	if(isset($_GET['guest_token'])){
		
		$pack_purchased = false;
		
		// list of posts
		if ($postpack_posts = mgm_get_postpack_posts($postpack_id)) {
			// init string
			$post_string = '<ul>';
			// loop
			foreach ($postpack_posts as $i=>$pack_post) {
				// check if user has purchased
				$access = mgm_user_has_purchased_post($pack_post->post_id,NULL, strip_tags($_GET['guest_token']));
				// set
				if($access){
					// get post
					$post = get_post($pack_post->post_id);
					$post_url = get_permalink($post->ID).'?guest_token=' . strip_tags($_GET['guest_token']);
					// append
					$post_string .= sprintf('<li><a href="%s">%s</a></li>',$post_url, $post->post_title);
					
					$pack_purchased = true;
				}

			}  
			// end
			$post_string .= '</ul>';
		}
		
		if($pack_purchased)
			return $post_string;	
	}
	
	// show links
	// login url
	$login_url = ($post->ID>0) ? mgm_get_custom_url('login', false, array('redirect_to' => get_permalink($post->ID))) : '';
	// template
	$template = mgm_get_template('private_text_template', array(), 'templates');
	// message
	$message = mgm_get_template('private_text_purchasable_pack_login', array(), 'messages');
	
	//issue #863
	$message  = str_replace('[purchase_options]', '', $message);
	$message  = __($message,'mgm');
	$message .= '[purchase_options]';
	
	// message - issue #1396
	$message = mgm_get_content_purchase_options($message, $login_url, $post->ID, $postpack_id);		
	// message
	$message = str_replace('[message]', $message, $template);
	// return 
	return sprintf('<div class="mgm_private_no_access">%s</div>',$message);			   
}	

/**
 * parse and output post buy button
 *
 * @since 2.0
 *
 * @param mixed $post_id
 * @return string $html
 */
function mgm_parse_post_template($post_id=null) {	
	global $wpdb;				

	//issue #1397
	if(!isset($post_id) || $post_id == null || empty($post_id)) {
		$post_id = get_the_ID();
	}
	
	// post		
	$post = get_post($post_id);	
	// issue #1397
	// post title 
	// $post_title = sprintf('<ul><li><a href="%s">%s</a></li></ul>', get_permalink($post_id), $post->post_title);
	
	// logged in 
	if(is_user_logged_in()){
		// return
		//return sprintf('<div>%s</div>%s', $post->post_title, mgm_get_post_purchase_button($post_id));
		//issue #1706
		return sprintf('%s',mgm_get_post_purchase_button($post_id));
	}		
	
	// login url
	$login_url = ($post->ID>0) ? mgm_get_custom_url('login', false, array('redirect_to' => get_permalink($post->ID))) : '';
	// template
	$template = mgm_get_template('private_text_template', array(), 'templates');
	// message
	$message = mgm_get_template('private_text_purchasable_login', array(), 'messages');
	
	//issue #863
	$message  =  str_replace('[purchase_options]', '', $message);
	$message  = __($message, 'mgm');
	$message .= '[purchase_options]';
	
	// message
	$message = mgm_get_content_purchase_options($message, $login_url, $post_id);		
	// message
	$message = str_replace('[message]', $message, $template);
	// return 
	//return sprintf('<div>%s</div><div class="mgm_private_no_access">%s</div>', $post->post_title, $message); 
	//issue #1706
	return sprintf('<div class="mgm_private_no_access">%s</div>',$message); 
}	

/**
 * filter excerpt
 *
 * @param string $content
 * @return string $content 
 */
function mgm_filter_excerpt($content){
	// remove tags
	$content = $text = preg_replace( '|\[(.+?)\](.+?\[/\\1\])?|s', '', $content);
	// return 
	return $content;
}

/**
 * excerpt protection
 *
 * @param string $content
 * @return string $content
 */
function mgm_excerpt_protection($content){	
	// check protection
	return mgm_content_protection_check($content,'excerpt');	
}

/**
 * content protection
 *
 * @param string $content
 * @return string $content
 */
function mgm_content_protection($content){		
	// check protection
	// prevent recheck if coming from the_excerpt() function for is_archive/is_search/is_search
	// issue #925
    $system_obj = mgm_get_class('system');   
	// check theme setting
	$using_the_excerpt_in_theme = bool_from_yn($system_obj->get_setting('using_the_excerpt_in_theme', 'N'));
	// check 
	if ($using_the_excerpt_in_theme && (is_archive() || is_home() || is_search())) {
		// return
		return $content;
	}
	// default
	return mgm_content_protection_check($content,'content');
}

/**
 * check comments protection
 *
 * @param string $file
 * @param string $type
 * @return string $file
 */
function mgm_hide_comments($file){
	if(is_super_admin() || mgm_user_has_access()) {		
		return $file;
	}else {
		return mgm_get_page_template('empty_comments',false);
	}
}

/**
 * check protection
 *
 * @param string $content
 * @param string $type
 * @return string $content
 */
function mgm_content_protection_check($content, $type='excerpt'){
	global $wpdb,$post;	
	
	// system
	$system_obj = mgm_get_class('system');
	// by pass content protection for excerpt if setting is off	
		
	// get user
	$user = wp_get_current_user();	
	// to disable showing multiple private messages on home page listing(issue#: 384), disabled due to #429
	// $show_message = true; // (is_home() && $type == 'content')  ? false : true;
	// filter first
	$content = mgm_replace_message_tags($content);
	// filter payment messages
	$content = mgm_replace_payment_message_tags($content);
	
	// no check for admin or if user has access or user logged in
	if(is_super_admin() || mgm_user_has_access() /*|| $user->ID*/) return $content;
	
	//comments protection check
	if($system_obj->setting['enable_comments_protection'] == 'Y'){
		//hook
		add_filter('comments_template', 'mgm_hide_comments');
	}	
	//to honour MORE(<!--more-tag-->) tag: issue#: 671	
	//case: home page listing,category listing,archives	
	if($type == 'excerpt' || (!is_single() && $type == 'content' && (is_archive() || is_home() || is_search()))) {			
		//check the content has more tag: eg: The link: http://magicmediagroup.com/ppp/#more-540
		if(preg_match("/\/#more-".$post->ID."/", $content) || preg_match("/<!--more-->/", $content)) {				
			return $content;
		}
		// if excerpt display and enable_excerpt_protection is disbled , bypass protection:issue#: 887
		if ( !bool_from_yn($system_obj->get_setting('enable_excerpt_protection')) ) {		
			return $content;
		}
	}
	
	
	// for full / or part protection, honor manual private tag setting via post interface or , mgm settings	
	// protection level
	$protection_level = $system_obj->setting['content_protection'];
	
	// no check for post/page set as no access redirect, custom register/login urls 		
	if($post->ID){
		// get permalink
		$permalink = get_permalink( $post->ID );
		// no_access_urls
		$no_access_urls = array('no_access_redirect_loggedin_users','no_access_redirect_loggedout_users');
		// init
		$return = false;
		// loop
		foreach($no_access_urls as $no_access_url){
			// get setting
			$no_access_url_is = $system_obj->setting[$no_access_url];
			// match
			if(!empty($no_access_url_is) && $permalink == trailingslashit($no_access_url_is)){
				// set flag
				$return = true; break;
			}
		}		
		// return 
		if($return)	return $content;
		
		// check urls
		$custom_pages_url = $system_obj->get_custom_pages_url(); 
		// check
		foreach($custom_pages_url as $key=>$page_url){
			// match
			if(!empty($page_url) && $permalink == trailingslashit($page_url)){
				// set flag
				$return = true; break;
			}			
		}
		// return 
		if($return)	return $content;
		
		// get post object
		$post_obj = mgm_get_post($post->ID);				
	}		
	
	// post_is_purchasable
	$post_is_purchasable = mgm_post_is_purchasable();
	
	// is post_is_purchasable and expired
	if($post_is_purchasable && $user->ID && mgm_is_user_purchased_post_expired($post->ID, $user->ID)){
		$protected_message = __('Purchased Post expired, Please re-purchase to access the post', 'mgm');
	}else{		
		// message code	
		if($user->ID){// logged in user
			$message_code = $post_is_purchasable ? 'private_text_purchasable' : 'private_text_no_access';
		}else{// logged out/guest user
			$message_code = $post_is_purchasable ? 'private_text_purchasable_login' : 'private_text';
		}
		// protected_message	
		$protected_message = sprintf('<div class="mgm_private_no_access">%s</div>',mgm_private_text_tags(mgm_stripslashes_deep($system_obj->get_template($message_code, array(), true))));			
		// filter message
		$protected_message = mgm_replace_message_tags($protected_message);
	}
	
	// return $content;
	// check
	switch($protection_level){
		case 'full': // full protection	
			// check redirect condition
			// Skip content protection if manually protected:(To honour private tags)
			// Double check as the next line was removed before
			if(!mgm_is_manually_protected($content)) {
				// had redirect
				if (!mgm_check_redirect_condition($system_obj)) {
					$content = $protected_message;
				}else{
				// default
					$content = mgm_no_access_redirect($system_obj);
				}
			}					
		break;
		case 'partly':// partly protection		
			// Skip content protection if manually protected:(To honour private tags)
			// Double check as the next line was removed before
			if( !mgm_is_manually_protected($content) ) {
				// check if custom page is loaded
				$custompage_loaded = mgm_is_custompage_loaded();					
				// how many words to allow
				$allowed_word_limit = (int)$system_obj->get_setting('public_content_words'); 
				$allow_html         = bool_from_yn($system_obj->get_setting('content_protection_allow_html')); 									
				// apply if only more than 0				
				if(!$custompage_loaded && $allowed_word_limit > 0) {// #125 iss / issue#: 510
					// check redirect condition
					if ( !mgm_check_redirect_condition($system_obj) ) {	// redirect if set
						// on type
						switch($type){
							case 'excerpt':		
								//issue #1059
								$content_post = get_post($post->ID);
								$my_content = $content_post->post_content;
								if(preg_match("/<!--nextpage-->/", $my_content)) {	
									$content = str_replace('<!--nextpage-->','',$my_content);
								}
								// already parsed by shortcode						
								if(preg_match('#<div class="mgm_private_no_access">(.*?)<\/div\>#s', $content, $match)) {	
									// get message only									
									$prev_message = $match[0];
									// remove message
									$content = preg_replace('#<div class="mgm_private_no_access">(.*?)<\/div\>#s', '', $content);		
									// get words
									$content = mgm_words_from_content($content, $allowed_word_limit, $allow_html);
									// append
									if($allowed_word_limit < 50) $content .= $prev_message;
								}else {
								// not processed	
									// get words
									$content = mgm_words_from_content($content, $allowed_word_limit, $allow_html);
									// append
									if($allowed_word_limit < 50) $content .= $protected_message;		
								}								
							break;
							case 'content':			
								//issue #1059
								$content_post = get_post($post->ID);
								$my_content = $content_post->post_content;
								if(preg_match("/<!--nextpage-->/", $my_content)) {	
									$content = str_replace('<!--nextpage-->','',$my_content);
								}
								// already parsed by shortcode
								// issue #: 450
								if(preg_match('#<div class="mgm_private_no_access">(.*?)<\/div\>#s', $content, $match)) {	
									// get message only																	
									$prev_message = $match[0];
									// remove message
									$content = preg_replace('#<div class="mgm_private_no_access">(.*?)<\/div\>#s', '' , $content);								
									// get words
									$content = mgm_words_from_content($content, $allowed_word_limit, $allow_html);
									// add message
									$content .= $prev_message;
								}else {				
									// get words
									$content = mgm_words_from_content($content, $allowed_word_limit, $allow_html) . $protected_message;		
								}
							break;
						}
					}else{
					// default
						$content = mgm_no_access_redirect($system_obj);
					}	
				}
			}			
		break;
		case 'none':// no protection, trim all private tags, honor [private] tags			
			// just check purchasable, other wise trim			
			if(!$post_is_purchasable){							
				// remove tags 
				$content = str_replace(array('[private]','[/private]','[private_or]','[/private_or]','[private_and]','[/private_and]'),'',$content);
			}			
		break;	
		default:	
			// disable protection		
			$content = str_replace(array('[private]','[/private]','[private_or]','[/private_or]','[private_and]','[/private_and]'),'',$content);
		break;		
	}	
	
	// issue#: 450
	if($post_is_purchasable && !mgm_is_buynow_form_included($content)) {		
		
		//issue #1397
		if(mgm_is_manually_protected($content)? false: true) {
			$return = mgm_parse_post_template($post->ID);
		}else {
			//issue #1537
			if(is_super_admin() || $user->ID) {
				$return = mgm_get_post_purchase_button($post->ID, (mgm_is_manually_protected($content)? false: true));
			}			
		}
		// get button
		// replace message tags if any
		$return = mgm_replace_message_tags($return);
		// wrap with css class
		$content .= sprintf('<div class="mgm_private_no_access">%s</div>', $return);
	}	   
 	// return	
	return $content;
}

// check already protected
function mgm_is_manually_protected($content){
	// init	
	$is_protected = false;	
	// check
	if(preg_match("/\[private\](.*)\[\/private\]/", $content)){
		$is_protected = true;
	}elseif(preg_match("/\[private_or\](.*)\[\/private_or\]/", $content)){
		$is_protected = true;
	}elseif(preg_match("/\[private_and\](.*)\[\/private_and\]/", $content)){
		$is_protected = true;
	}elseif(preg_match('#<div class="mgm_private_no_access">(.*?)<\/div\>#s', $content)){		
		$is_protected = true;
	}
	// return
	return $is_protected;	
}
//check buy now form already included
function mgm_is_buynow_form_included($content) {
	return preg_match('/id="mgm_buypost_form"/', $content);
}

// payment messages
function mgm_replace_payment_message_tags($content){
	// system
	$system_obj = mgm_get_class('system');
		
	// current module
	$module   = mgm_request_var('module', '', true);	
	// object 
	$module_object = NULL;	
	
	// check 
	if($module){
		// module object
		$module_object = mgm_get_module($module, 'payment');
	}
	
	// double check
	if(	is_object($module_object) ){
		// status and message
		if (!isset($_GET['status']) || $_GET['status'] == 'success') {	
			$payment_status_title   = ($module_object->setting['success_title'] ? $module_object->setting['success_title'] : $system_obj->get_template('payment_success_title', array(), true));
			$payment_status_message = ($module_object->setting['success_message'] ? $module_object->setting['success_message'] : $system_obj->get_template('payment_success_message', array(), true));
		} else if (!isset($_GET['status']) || $_GET['status'] == 'cancel') {	
			$payment_status_title   = __('Transaction cancelled','mgm');
			$payment_status_message = __('You have cancelled the transaction.','mgm');
		} else {	
			$payment_status_title   = ($module_object->setting['failed_title'] ? $module_object->setting['failed_title'] : $system_obj->get_template('payment_failed_title', array(), true));
			$payment_status_message = ($module_object->setting['failed_message'] ? $module_object->setting['failed_message'] : $system_obj->get_template('payment_failed_message', array(), true));
		}
		
		// set errors 
		if (isset($_GET['errors'])) {
			$errors = explode('|', strip_tags($_GET['errors']));
			$payment_status_message .= '<p><h3>' . __('Messages', 'mgm') . '</h3>';
			$payment_status_message .= '<div><ul>';
			foreach ($errors as $error) {
				$payment_status_message .= '<li>' . $error . '</li>';
			}
			$payment_status_message .= '</ul>
			</div></p>';
		}		
		// redirect_to post
		if(isset($_GET['post_redirect'])){
			$payment_status_message .= __('<b>You will be redirected to the Post Purchased, please click <a href="'. strip_tags($_GET['post_redirect']) . '"> here </a> if you are not redirected.</b>','mgm');
			$payment_status_message .="<script language=\"Javascript\">var t = setTimeout ( \"window.location='" . strip_tags($_GET['post_redirect']) . "'\", 5000 ); </script>";
		}
		// loop tags
		foreach(array('payment_status_title','payment_status_message') as $tag){
			// set
			$content = str_replace('[['.$tag.']]',mgm_stripslashes_deep(${$tag}),$content);
		}		
	}else{
		// loop tags and clean tags
		foreach(array('payment_status_title','payment_status_message') as $tag){
			// set
			$content = str_replace('[['.$tag.']]','',$content);
		}	
	}	
	// return
	return $content;
}
// footer credits
function mgm_footer_credits(){		
	// affiliate id
	if($affid = get_option('mgm_affiliate_id')){
		// mgm url
		$affiliate_url = 'http://www.magicmembers.com/?affid='.$affid;
		
		// content
		$content ='<div class="mgm_aff_footer"><div class="mgm_aff_link">[powered_by]</div><div class="mgm_aff_clearfix"></div></div>';
				  
		// apply filter		  
		$content = apply_filters('mgm_powered_by', $content);
		
		// place link
		$content = str_replace('[powered_by]',sprintf(__('Powered by Magic Members <a href="%s" target="_blank">Membership Software</a>','mgm'),$affiliate_url), $content);
											
		// print		  
		echo $content;
	}
}
// print 
function mgm_print_footer_credits(){
	// remove action 
	remove_action('wp_footer','mgm_footer_credits');
	// print
	mgm_footer_credits();
}
// loads payment related js/css files
function mgm_load_payment_jsfiles($content) {		
	$id = get_the_ID();		
	if(is_numeric($id)) {
		$post_obj = mgm_get_post($id);				
		if(isset($post_obj->purchasable) && $post_obj->purchasable == 'Y' && !wp_script_is('jquery.metadata')) {						
			$incfiles = '';
			$css_files	= array();
			$css_files[] = MGM_ASSETS_URL . 'css/mgm_cc_fields.css';		
			$css_link_format = '<link rel="stylesheet" href="%s" type="text/css" media="all" />';
			// add
			foreach($css_files as $css_file){
				$incfiles .= sprintf($css_link_format, $css_file)."\n";
			}	
			$js_files = array();
			// jquery from wp distribution
			//$js_files[] = includes_url( '/js/jquery/jquery.js');
			// custom
			if(!wp_script_is('mgm-jquery-validate'))
				if(!mgm_is_script_already_included('jquery.validate.pack.js')) {
					$js_files[] = MGM_ASSETS_URL . 'js/jquery/jquery.validate.pack.js';
					$mgm_scripts[] = 'jquery.validate.pack.js';
				}
			wp_enqueue_script('jquery.metadata', MGM_ASSETS_URL . 'js/jquery/jquery.metadata.js');
			if(!wp_script_is('mgm-jquery-metadata'))	
				if(!mgm_is_script_already_included('jquery.metadata.js')) {
					$js_files[] = MGM_ASSETS_URL . 'js/jquery/jquery.metadata.js';
					$mgm_scripts[] = 'jquery.metadata.js';
				}
			if(!wp_script_is('mgm-helpers'))
				$js_files[] = MGM_ASSETS_URL . 'js/helpers.js';
			$js_script_format = '<script type="text/javascript" src="%s"></script>';
			if($js_files)
				foreach($js_files as $js_file){
					$incfiles .= sprintf($js_script_format, $js_file)."\n";
				}
			$content .= $incfiles;
			unset($js_files);
			unset($css_files);
			unset($incfiles);
		}
	}
	return $content;	
}
// update password reset email body
function mgm_modify_retrieve_password_emailbody($message) {		
	global $wpdb, $current_site,$wp_hasher;	
	$system_obj = mgm_get_class('system');
	$blogname = is_multisite() ? $current_site->site_name : wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	if ( strpos($_POST['user_login'], '@') ) {
		$user_data = get_user_by('email',trim($_POST['user_login']));	
	} else {
		$login = trim($_POST['user_login']);
		$user_data = get_user_by('login', $login);
	}
	$user_login = $user_data->user_login;	
	//just fetch the key from db as it is already updated
	$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
	
	//issue #1464
	if($system_obj->get_setting('enable_default_wp_lost_password') == "Y") {
		
		//issue #1665
		if( mgm_compare_wp_version('3.7', '>=') ){
			// Generate something random for a password reset key.
			$key = wp_generate_password( 20, false );
			do_action( 'retrieve_password_key', $user_login, $key );
		
			// Now insert the key, hashed, into the DB.
			if ( empty( $wp_hasher ) ) {
				require_once ABSPATH . 'wp-includes/class-phpass.php';
				$wp_hasher = new PasswordHash( 8, true );
			}
			$hashed = $wp_hasher->HashPassword( $key );
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );
		}
		//password link	
	   	$passwordlink = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
	   
	}else {
		$passwordlink =  mgm_get_custom_url('login', false, array('action' => 'rp', 'key' => $key, 'login' => rawurlencode($user_login) ));
	}
	// subject
	$body = $system_obj->get_template('retrieve_password_email_template_body', array('blogname'=>$blogname,
																				'siteurl' => network_site_url(),
																				'username' => $user_login,
																				'passwordlink' => $passwordlink 
																				),true);
	//issue #862
	$body = mgm_replace_email_tags($body,$user_data->ID) ;
																				
	//apply mgm content type
	add_filter('wp_mail_content_type', 'mgm_get_mail_content_type', 10);		
	// return																	
	return $body;
}
// update password reset email subject
function mgm_modify_retrieve_password_emailsubject() {
	global $wpdb, $current_site;
	$system_obj = mgm_get_class('system');
	$blogname = is_multisite() ? $current_site->site_name : wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	
	// subject
	$subject = $system_obj->get_template('retrieve_password_email_template_subject', array('blogname'=>$blogname), true);

	//issue #862
	if ( strpos($_POST['user_login'], '@') ) {
		$user_data = get_user_by('email',trim($_POST['user_login']));	
	} else {
		$login = trim($_POST['user_login']);
		$user_data = get_user_by('login', $login);
	}
	$subject = mgm_replace_email_tags($subject,$user_data->ID) ;
	
	return $subject;
}
// update lost password reset email body
function mgm_modify_lost_password_emailbody($body, $new_password) {		
	global $wpdb, $current_site;
	$system_obj = mgm_get_class('system');
	$username = strip_tags($_GET['login']); 
	$blogname = is_multisite() ? $current_site->site_name : wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);	
	// subject
	$body = $system_obj->get_template('lost_password_email_template_body', 
				array(	'blogname' => $blogname,
						'loginurl' => (mgm_get_custom_url('login')),
						'username' => $username,
						'password' => $new_password 
						), true);
	if(empty($body)) {
		$body  = sprintf(__('Username: %s'), $username) . "\r\n";
		$body .= sprintf(__('Password: %s'), $new_password) . "\r\n";
		$body .= (mgm_get_custom_url('login')) . "\r\n";
	
		if ( is_multisite() )
			$blogname = $current_site->site_name;
		else
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	}

	//issue #862
	if ( strpos($_GET['login'], '@') ) {
		$user_data = get_user_by('email',trim($_POST['user_login']));	
	} else {
		$login = trim(strip_tags($_GET['login']));
		$user_data = get_user_by('login', $login);
	}
	$body = mgm_replace_email_tags($body,$user_data->ID) ;	
	
	// apply mgm content type
	add_filter('wp_mail_content_type', 'mgm_get_mail_content_type', 10);
	// return
	return $body;
}
// update lost password reset email subject
function mgm_modify_lost_password_emailsubject() {
	global $wpdb, $current_site;
	$system_obj = mgm_get_class('system');
	$blogname = is_multisite() ? $current_site->site_name : wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	
	// subject
	$subject = $system_obj->get_template('lost_password_email_template_subject', array('blogname'=>$blogname), true);
	if(empty($subject)) {
		$subject = sprintf( __('[%s] Your new password','mgm'), $blogname );
	}

	//issue #862
	if ( strpos($_GET['login'], '@') ) {
		$user_data = get_user_by('email',trim($_POST['user_login']));	
	} else {
		$login = trim(strip_tags($_GET['login']));
		$user_data = get_user_by('login', $login);
	}
	$subject = mgm_replace_email_tags($subject,$user_data->ID) ;
	
	return $subject;
}
/** 
 * post pack buy button
 *
 * @param int postpack id
 * @param bool guest purchase
 * @return string button 
 */
function mgm_get_postpack_purchase_button($postpack_id=NULL, $guest_purchase=false,  $postpack_posts=array(), $postpack_post_id=NULL){
	global $post;		
	// get current post id, the page where post pack were listed, used to redirect		
	if(empty($postpack_post_id))
		$postpack_post_id = get_the_ID();
	// init
	$button ='';
	
	//issue #867
	$css_group = mgm_get_css_group();
	if($css_group !='none') {
		// css link
		$button .= '<link rel="stylesheet" href="'. MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.form.fields.css'.'" type="text/css" media="all" />';
	}
	
	// system
	$system_obj = mgm_get_class('system'); 
	// get payment modules
	$a_payment_modules = $system_obj->get_active_modules('payment');
	// init 
	$payment_modules = array();			
	// when active
	if($a_payment_modules){
		// loop
		foreach($a_payment_modules as $payment_module){
			// not trial
			if(in_array($payment_module, array('mgm_free','mgm_trial'))) continue;				
			// store
			$payment_modules[] = $payment_module;					
		}
	}		
	
	// check
	if (count($payment_modules)>0) {	
		// in transactions url set
		if(isset($system_obj->setting['transactions_url']) && !empty($system_obj->setting['transactions_url'])){		
			// base url
			$baseurl = $system_obj->setting['transactions_url'];		
		}else{
			// base url
			$baseurl = mgm_home_url('transactions');
		}
		// post url
		$post_payment_url = add_query_arg(array('method'=>'payment_purchase'), $baseurl);
		// post pack
		$GLOBALS['buttons_postpack_id'] = $postpack_id;
		// hook
		add_filter( 'mgm_payment_gateways_as_custom_field', 'mgm_payment_gateways_as_custom_field_on_postpurchase' );
		// custom_fields
		$custom_fields = mgm_get_partial_fields(array('on_postpurchase'=>true),'mgm_postpurchase_field');	
		// button 
		$button_code = sprintf('<input class="button" type="submit" name="btnsubmit" value="%s">', __('Buy Now','mgm'));
		// filter
		$button_code = apply_filters('post_purchase_button_html', $button_code);
		//issue #1250
		$button .= mgm_subscription_purchase_errors();		
		// button
		$button .= '<div class="mgm_custom_filed_table">
					 <form name="mgm_buypostpack_form" id="mgm_buypostpack_form" class="mgm_form" method="post" action="'.$post_payment_url.'">
						' . $custom_fields . '
						' . $button_code . '
						<input type="hidden" name="postpack_id" value="'.$postpack_id.'">
						<input type="hidden" name="postpack_post_id" value="'.$postpack_post_id.'">
						<input type="hidden" name="form_action" value="'. post_permalink($postpack_post_id) .'">
						<input type="hidden" name="guest_purchase" value="'.$guest_purchase.'">
					 </form>
				   </div>';		
		
	}else{
		$button .= '<div class="mgm_no_payment_gateway">' . __('No Payment Gateway available.', 'mgm') . '</div>';
	}
	
	// return 
	return $button;
}

/** 
 * post buy button
 *
 * @param int post id
 * @param bool show error message
 * @param bool guest purchase
 * @return string button 
 */
function mgm_get_post_purchase_button($post_id=NULL, $show_message=true, $guest_purchase=false){
	// current user
	$current_user = wp_get_current_user();
	// get system
	$system_obj = mgm_get_class('system');
	
	// guest purchase - issue #1355
	if ( ! $guest_purchase ) {
		$guest_purchase = bool_from_yn( mgm_get_setting('enable_guest_content_purchase') );		
	}
	
	// get current post id		
	if( ! $post_id ) $post_id = get_the_ID();
	
	// currency
	$currency = $system_obj->get_setting('currency');
	
	//issue #867
	$css_group = mgm_get_css_group();
	// css
	if($css_group !='none') {
		// css link
		$return = '<link rel="stylesheet" href="'. MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.form.fields.css'.'" type="text/css" media="all" />';
	}
			
	// if user logged in
	if ($current_user->ID > 0 || $guest_purchase === TRUE) {
		// get active payment modules
		$a_payment_modules = $system_obj->get_active_modules('payment');
		// init 
		$payment_modules = array();			
		// when active
		if($a_payment_modules){
			// loop
			foreach($a_payment_modules as $payment_module){
				// not trial
				if(in_array($payment_module, array('mgm_free','mgm_trial'))) continue;				
				// store
				$payment_modules[] = $payment_module;					
			}
		}
		
		// if show
		if( $show_message ){
			// text
			$private_text_purchasable = mgm_stripslashes_deep($system_obj->get_template('private_text_purchasable', array(), true));
			// def return
			$return .= sprintf('<div class="post_purchase_select_gateway">%s</div>', $private_text_purchasable);
		}				
				
		// some module active
		if ( count($payment_modules) > 0 ) {
			// in transactions url set
			if( ! $transactions_url = $system_obj->get_setting('transactions_url')){
				// base url
				$transactions_url = mgm_home_url('transactions'); 
			}			
			// post url
			$post_payment_url = add_query_arg(array('method'=>'payment_purchase'), $transactions_url);
			// hook
			add_filter( 'mgm_payment_gateways_as_custom_field', 'mgm_payment_gateways_as_custom_field_on_postpurchase' );
			// custom fields
			$custom_fields = mgm_get_partial_fields(array('on_postpurchase'=>true), 'mgm_postpurchase_field');			
			// button 
			$button_code = sprintf('<input class="button" type="submit" name="btnsubmit" value="%s">', __('Buy Now','mgm'));
			// filter
			$button_code = apply_filters('post_purchase_button_html', $button_code);
			// init
			$addon_options_html = '';
			// addons			
			if( $addons = mgm_get_post($post_id)->get_addons() ){
				$addon_options_html = mgm_get_post_purchase_addon_options_html($addons);
			}
			// issue #1250
			$button = mgm_subscription_purchase_errors();
			// button
			$button .= '<div class="mgm_custom_filed_table">
						<form name="mgm_buypost_form" id="mgm_buypost_form" class="mgm_form" method="post" action="' . $post_payment_url . '">
							' . $custom_fields . '
							' . $addon_options_html . '
							' . $button_code . '
							<input type="hidden" name="post_id" value="'.$post_id.'">
							<input type="hidden" name="form_action" value="'. post_permalink($post_id) .'">
							<input type="hidden" name="guest_purchase" value="'.$guest_purchase.'">
					    </form>
					    </div>';		
			// return 
			$return .= $button;
		}else{
			$return .= sprintf('<div class="mgm_no_payment_gateway">%s</div>',  __('No Payment Gateway available.', 'mgm') );
		}							
	} else {
		// message
		if( $show_message ){
			// text 
			$private_text_purchasable_login = mgm_private_text_tags(mgm_stripslashes_deep($system_obj->get_template('private_text_purchasable_login', array(), true)));
			// only when show message
			$return .= sprintf('<div class="post_purchase_select_gateway">%s</div>', $private_text_purchasable_login);	
		}		
	}
	
	// return 
	return $return;
}

/**
 * filters post purchase modules on the fly 
 * hooks to mgm_payment_gateways_as_custom_field
 *
 * @param array $modules
 * @return array $modules
 * @since 1.8.36
 */
function mgm_payment_gateways_as_custom_field_on_postpurchase($modules){
	global $post;

	if( isset($GLOBALS['buttons_postpack_id']) ){
		$post_pack = mgm_get_postpack($GLOBALS['buttons_postpack_id']);
		unset($GLOBALS['buttons_postpack_id']);
		if( isset( $post_pack->modules) ){
			return (array)json_decode($post_pack->modules, true);
		}	
	}else if( isset($post->ID) && (isset($_REQUEST['method'])!='guest_purchase')){
		$mgm_post = mgm_get_post( $post->ID );
		if( isset( $mgm_post->allowed_modules) ){
			return (array)$mgm_post->allowed_modules;
		}
	}else if((isset($_REQUEST['method']) =='guest_purchase') && isset($_REQUEST['post_id']) && !empty($_REQUEST['post_id'])){
		$mgm_post = mgm_get_post($_REQUEST['post_id']);
		if( isset( $mgm_post->allowed_modules) ){
			return (array)$mgm_post->allowed_modules;
		}
	}

	return $modules;
}
/**
 * get user display names
 */
function mgm_get_user_display_names(){
	// current user
	$current_user = wp_get_current_user();
	// init
	$display_names = array();
	// set 
	$display_names['display_username'] = $current_user->user_login;
	$display_names['display_nickname'] = $current_user->nickname;
	// first name
	if ( !empty($current_user->first_name) )
		$display_names['display_firstname'] = $current_user->first_name;
	if ( !empty($current_user->last_name) )
		$display_names['display_lastname'] = $current_user->last_name;
	if ( !empty($current_user->first_name) && !empty($current_user->last_name) ) {
		$display_names['display_firstlast'] = mgm_str_concat($current_user->first_name, $current_user->last_name);
		$display_names['display_lastfirst'] = mgm_str_concat($current_user->last_name, $current_user->first_name);
	}
	// set
	if ( !in_array( $current_user->display_name, array_values($display_names) ) ) // Only add this if it isn't duplicated elsewhere
		$display_names = array_merge(array( 'display_displayname' => $current_user->display_name ),  $display_names);
		
	$display_names = array_map( 'trim', $display_names );
	$display_names = array_unique( $display_names );
	
	// return
	return $display_names;
}

/**
 * validate and save profile data
 *
 * @param int user id
 * @return int user id
 */
function mgm_user_profile_update( $user_id ) {
	global $wpdb;
	// get user
	if ( $user_id > 0 ) {		
		$user_data = get_userdata( $user_id );
	} 	
	
	// error
	if(!$user_data->ID) return $user_id;
	
	// flag to control callback re calling via hooks clash, iss#705
	define('MGM_DOING_USERS_PROFILE_UPDATE', TRUE)	;
	
	// set aside member object
	$member = mgm_get_member($user_id);	
		
	// create empty user
	$user = new stdClass;	
	// set id
	$user->ID = $user_data->ID;
		
	// sanitize user login
	if ( isset( $_POST['user_login'] ) )
		$user->user_login = sanitize_user($_POST['user_login'], true);
	
	// asnitize email and copy	
	if ( isset( $_POST['user_email'] ))
		$user->user_email = sanitize_text_field( $_POST['user_email'] );
	
	// urls
	if ( isset( $_POST['mgm_profile_field']['url'] ) ) {
		if ( empty ( $_POST['mgm_profile_field']['url'] ) || $_POST['mgm_profile_field']['url'] == 'http://' ) {
			$user->user_url = '';
		} else {
			$user->user_url = esc_url_raw( $_POST['mgm_profile_field']['url'] );
			$user->user_url = preg_match('/^(https?|ftps?|mailto|news|irc|gopher|nntp|feed|telnet):/is', $user->user_url) ? $user->user_url : 'http://'.$user->user_url;
		}
	}
	if ( isset( $_POST['mgm_profile_field']['first_name'] ) )
		$user->first_name = sanitize_text_field( $_POST['mgm_profile_field']['first_name'] );
	if ( isset( $_POST['mgm_profile_field']['last_name'] ) )
		$user->last_name = sanitize_text_field( $_POST['mgm_profile_field']['last_name'] );
	if ( isset( $_POST['mgm_profile_field']['nickname'] ) )
		$user->nickname = sanitize_text_field( $_POST['mgm_profile_field']['nickname'] );
	if ( isset( $_POST['mgm_profile_field']['display_name'] ) )
		$user->display_name = sanitize_text_field( $_POST['mgm_profile_field']['display_name'] );	
	if ( isset( $_POST['mgm_profile_field']['description'] ) )
		$user->description = trim( $_POST['mgm_profile_field']['description'] );		
	
	// init errors
	$errors = new WP_Error();	
	
	// check user login
	if ( isset( $_POST['user_login'] ) && !validate_username( $_POST['user_login'] ) )
		$errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.','mgm' ));
	
	// user login duplicate
	if ( ( $owner_id = username_exists( $user->user_login ) ) && $owner_id != $user->ID )
		$errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is already registered. Please choose another one.','mgm' ));	
	
	// nickname
	//!isset( $_POST['mgm_profile_field']['nickname'] ) || - issue #1207 
	if ( ( isset( $_POST['mgm_profile_field']['nickname'] ) && empty( $_POST['mgm_profile_field']['nickname'] )))
		$errors->add( 'nickname', __( '<strong>ERROR</strong>: You must provide a Nick Name.','mgm' ));
	
	// email - issue #1207 
	if (isset( $_POST['user_email'] ) && empty( $user->user_email ) ) {
		$errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please enter an e-mail address.','mgm' ), array( 'form-field' => 'email' ) );
	} elseif ( isset( $_POST['user_email'] ) && !is_email( $user->user_email ) ) {
		$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The e-mail address isn&#8217;t correct.','mgm' ), array( 'form-field' => 'email' ) );
	} elseif ( isset( $_POST['user_email'] ) && ( $owner_id = email_exists($user->user_email) ) && $owner_id != $user->ID ) {
		$errors->add( 'email_exists', __('<strong>ERROR</strong>: This email is already registered, please choose another one.','mgm'), array( 'form-field' => 'email' ) );
	}		
	
	// password:
	$pass1 = $pass2 = '';
	if ( isset( $_POST['user_password'] ))
		$pass1 =  sanitize_text_field( $_POST['user_password']);
	if ( isset( $_POST['user_password_conf'] ))
		$pass2 =  sanitize_text_field( $_POST['user_password_conf'] );
	/* checking the password has been typed twice */	
	do_action_ref_array( 'check_passwords', array ( $user->user_login, & $pass1, & $pass2 ));

	//issue #1207 
	if(isset( $_POST['user_password']) &&  isset($_POST['user_password_conf'])) {
		if ( empty($pass1) && !empty($pass2) )
			$errors->add( 'pass', __( '<strong>ERROR</strong>: You entered your new password only once.','mgm' ), array( 'form-field' => 'pass1' ) );
		elseif ( !empty($pass1) && empty($pass2) )
			$errors->add( 'pass', __( '<strong>ERROR</strong>: You entered your new password only once.','mgm' ), array( 'form-field' => 'pass2' ) );
	}

	/* Check for "\" in password */ 
	//issue #1207
	if(isset( $_POST['user_password']) &&  isset($_POST['user_password_conf'])) {
		if ( false !== strpos( stripslashes($pass1), "\\" ) )
			$errors->add( 'pass', __( '<strong>ERROR</strong>: Passwords may not contain the character "\\".','mgm' ), array( 'form-field' => 'pass1' ) );
	}	
	// get default fields
	$profile_fields = mgm_get_config('default_profile_fields', array());
	// get active custom fields on profile page
	$cf_profile_page = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_profile'=>true)));
	//init - issue #1573
	$show_membership_fields_arr = array();		
	if(isset($_REQUEST['membership']) && !empty($_REQUEST['membership'])){	
		// membership
		$membership = $_REQUEST['membership'];
		// get active custom fields on register
		$cf_profile_by_membership_types = mgm_get_class('member_custom_fields')->get_fields_where(array('attributes'=>array('profile_by_membership_types'=>true)));
		//mgm_pr($cf_profile_by_membership_types);
		//check
		if(!empty($cf_profile_by_membership_types)){
			//loop
			foreach ($cf_profile_by_membership_types as $cf_profile_by_membership_type) {
				//membership_type
				$membership_types_string = $cf_profile_by_membership_type['attributes']['profile_membership_types_field_alias'];
				//check
				if (preg_match('/\b' . $membership . '\b/', $membership_types_string)) {
					$show_fields_arr[]=$cf_profile_by_membership_type['name'];
					$show_membership_fields_arr[]=$cf_profile_by_membership_type;
					if($cf_profile_by_membership_type['name'] =='password'){
						foreach ($cf_profile_by_membership_types as $cf_profile_by_membership) {
							if($cf_profile_by_membership['name'] =='password_conf'){							
								$show_membership_fields_arr[]=$cf_profile_by_membership;
							}
						}						
					}
				}
			}	
		}
	}
	//merge - issue #1573
	if(isset($show_membership_fields_arr) && is_array($show_membership_fields_arr) && !empty($show_membership_fields_arr)){
		$cf_profile_page = array_merge($cf_profile_page,$show_membership_fields_arr);
	}
	//Profile page password filed is default .#issue 799
	$falg =0;
	$pass_field ='';
	foreach($cf_profile_page as $field){
		if($field['name']=='password'){
			$falg =1;
			//issue #973
			$pass_field =$field;
		}
	}
	if($falg==0)
		$cf_profile_page[]=array('name' => 'password','label' => 'Password','type' => 'password','system' => 1);	

	//issue #973 & issue #1207
	if(isset( $_POST['user_password']) &&  isset($_POST['user_password_conf']) && !empty($pass1) && 
		!empty($pass2) && 
		(isset($pass_field['attributes']['password_min_length']) && $pass_field['attributes']['password_min_length'] == true ) || 
		(isset($pass_field['attributes']['password_max_length']) && $pass_field['attributes']['password_max_length'] == true)
		){
		if(strlen($pass1) < $pass_field['attributes']['password_min_length_field_alias'] ||
			strlen($pass2) < $pass_field['attributes']['password_min_length_field_alias'] ){
			
			$errors->add('pass', 
			sprintf(__('<strong>ERROR</strong>:Password is too short, minimum %d characters.','mgm'),
			$pass_field['attributes']['password_min_length_field_alias']),array( 'form-field' => 'pass1' ));
			
		}elseif(strlen($pass1) > $pass_field['attributes']['password_max_length_field_alias'] || 
			strlen($pass2) > $pass_field['attributes']['password_max_length_field_alias'] ){
			
			$errors->add('pass', 
			sprintf(__('<strong>ERROR</strong>:Password is too long, minimum %d characters.','mgm'),
			$pass_field['attributes']['password_max_length_field_alias']),array( 'form-field' => 'pass1' ));	
			
		}
		elseif ( $pass1 != $pass2 ){
			$errors->add( 'pass', 
				__( '<strong>ERROR</strong>: Please enter the same password in the two password fields.','mgm' ), 
				array( 'form-field' => 'pass1' ) );
		}		
	}							
	/* checking the password has been typed twice the same */
	elseif ( isset( $_POST['user_password']) &&  isset($_POST['user_password_conf']) && $pass1 != $pass2 ){
		$errors->add( 'pass', 
			__( '<strong>ERROR</strong>: Please enter the same password in the two password fields.','mgm' ), 
			array( 'form-field' => 'pass1' ) );
	}
	// confirm email - issue #1315
	if(isset($_POST['user_email_conf']) && empty($_POST['user_email_conf'])) {
		$errors->add($field['name'],__('<strong>ERROR</strong>: Please type your confirm e-mail address.','mgm'));
	}elseif (isset($_POST['user_email_conf']) &&  ! is_email( $_POST['user_email_conf'] ) ) {
		$errors->add( 'invalid_email_conf', __( '<strong>ERROR</strong>: The confirm email address isn&#8217;t correct.','mgm' ) );
	}elseif (isset($_POST['user_email_conf']) && is_email( $_POST['user_email'] ) && $_POST['user_email_conf'] != $_POST['user_email']){
		$errors->add($field['name'], 
			__('<strong>ERROR</strong>: E-mail does not match. Please re-type.','mgm'));										
	}
	
	//issue #1207
	$m_pass = '';
	// set
	if(!empty($pass1) || !empty($pass2)) {

		if(!empty($pass1) && !empty($pass2)) {
			$user->user_pass = wp_hash_password($pass1);
			$m_pass = $pass1;
			
		}elseif (!empty($pass1)){
			$user->user_pass = wp_hash_password($pass1);
			$m_pass = $pass1;

		}else {
			$user->user_pass = wp_hash_password($pass2);
			$m_pass = $pass2;
		}
		
		//issue #703
		//$user->user_pass = $pass1;
		$member->user_password = mgm_encrypt_password($m_pass, $user->ID);
	}
	
	// loop
	foreach($cf_profile_page as $field){		
		// skip default fields, validated already
		if(in_array($field['name'], array('username','email','password','password_conf','email_conf'))) continue;			
		// skip html
		if($field['type'] == 'html' || $field['type'] == 'label') continue;							
		// check register and required		
		if((bool)$field['attributes']['required'] === true){		
			// error
			$error_codes = $errors->get_error_codes();
			// validate other				
			if ( (!isset($_POST['mgm_profile_field'][$field['name']])) || (empty($_POST['mgm_profile_field'][$field['name']])) ) {
				//issue #703
				$errors->add($field['name'], __('<strong>ERROR</strong>: You must provide a ','mgm').mgm_stripslashes_deep($field['label']).'.');
			}			
		}
	}	
	// Allow plugins to return their own errors.
	do_action_ref_array('user_profile_update_errors', array ( &$errors, $update, &$user ) );
	
	// error 
	if ( $errors->get_error_codes() ) return $errors;	
		
	// init pass	
	$user_password = '';
	// system - issue #1237
	$system_obj = mgm_get_class('system');
	$short_format = (!empty($system_obj->setting['date_format_short'])) ? $system_obj->setting['date_format_short'] : MGM_DATE_FORMAT_SHORT;
	
	// update custom fields values:		
	if (isset($_POST['mgm_profile_field'])) {		
		// loop fields		
		foreach($cf_profile_page as $field){
			// skip html
			if($field['type'] == 'html' || $field['type'] == 'label' || $field['name'] == 'password_conf') continue;
			// set					
			if(isset($_POST['mgm_profile_field'][ $field['name'] ])) {
				// value
				$value = $_POST['mgm_profile_field'][ $field['name'] ];			
				// birthdate	
				if($field['name'] == 'birthdate') {
					//convert to mysql date format(to standardise the date format) -issue #1237
					$value = mgm_format_inputdate_to_mysql($value,$short_format);					
				}elseif($field['name'] == 'password') {
					// pass iss#705
					$user_password = $value;
					// issue#: 672
					$value = mgm_encrypt_password($value, $user_id);
				}elseif($field['type'] == 'checkbox' && is_array($value)) {
					//$value = implode(" ", $value);
					//issue #1070
					$value = serialize($value);
				}
				// set
				$member->custom_fields->$field['name'] = $value;
			}elseif(isset($_POST[$field['name']])) {
				// value
				$value = $_POST[$field['name']];
				// birthdate
				if($field['name'] == 'birthdate') {
					// convert to mysql date format(to standardise the date format) - issue #1237
					$value = mgm_format_inputdate_to_mysql($value,$short_format);
				}elseif($field['name'] == 'password') {
					// pass iss#705
					$user_password = $value;
					//issue#: 672
					$value = mgm_encrypt_password($value, $user_id);
				}elseif($field['type'] == 'checkbox' && is_array($value)) {
					//issue #1070
					$value = serialize($value);
					//$value = implode(" ", $value);
				}
				// set		
				$member->custom_fields->$field['name'] = $value;
			}elseif($field['name'] == 'password' && !empty($pass1)) {
				// pass iss#705
				$user_password = $pass1;
				// value
				$value = mgm_encrypt_password($pass1, $user_id);
				// set
				$member->custom_fields->$field['name'] = $value;
			}elseif($field['type'] == 'checkbox' && isset($member->custom_fields->$field['name'])) {
				// If no value selected
				$member->custom_fields->$field['name'] = '';
			}
		}			
	}
	// update 		
	$member->save();
	
	//issue #1207
	if (!empty($m_pass)){
		// pass iss#705
		$user_password = $m_pass;
	}
	
	
	
	// iss#705
	// userdata to update, leave password here
	$userdata = get_object_vars( $user ) ;
	// unset encoded password from userdata
	unset($userdata['user_pass']);
	// update password
	if(!empty($user_password)){		
		// set
		$userdata['user_pass'] = $user_password;		
	}
	
	// save main user data & return user id
	return $user_id = wp_update_user( $userdata );
}

/**
 *  exclude default mgm custom pages when need
 * 
 * @param void
 * @return array $hide_pages
 */
function mgm_exclude_default_pages(){	
	// system
	$system_obj = mgm_get_class('system');
	// init
	$hide_pages = array();
	// get all pages
	$pages = get_pages();		
	// count
	if(count($pages)){
		// user logged in
		if( is_user_logged_in() ){
			$hide_urls = array('login','register','lostpassword','userprofile');
		}else{
			$hide_urls = array('profile','membership_contents','membership_details','userprofile');
		}

		// other
		$hide_urls[] = 'transactions';

		// register disabled
		if ( ! get_option('users_can_register') ){
			$hide_urls[] = 'register';
		}	
		// make unique
		$hide_urls = array_unique($hide_urls);
		// permalink
		foreach($pages as $page){
			// permalink
			$permalink = trailingslashit(get_permalink($page->ID));
			// pages to hide
			foreach($hide_urls as $url){
				// match
				if(trailingslashit($system_obj->setting[$url.'_url']) == $permalink){
					// hide
					$hide_pages[] = $page->ID;							
				}
			}			
		}

		// hide by slug
		foreach ($hide_urls as $slug) {
			if( $page = get_page_by_path($slug) ){
				// hide
				$hide_pages[] = $page->ID;	
			}
		}
	}					
	// return 
	return $hide_pages;
}

// unchecked
function mgm_replace_message_tags($message,$user_id=NULL) {
	// get user
	if(!$user_id){
		// cusrrent user
		$current_user = wp_get_current_user();
		// set 
		$user_id = $current_user->ID;
	}		
	// int
	$logged_in = (isset($current_user) && $current_user->ID>0) ? true : false;
	// user
	if ($user_id > 0) {			
		// get user
		$user         = get_userdata($user_id);
		// mgm member
		$member   = mgm_get_member($user_id); 
		// set
		$username     = $user->user_login;
		$name         = mgm_str_concat($user->first_name, $user->last_name);
		$email        = $user->user_email;
		$url          = $user->user_url;
		$display_name = $user->display_name;
		$first_name   = $user->first_name;
		$last_name    = $user->last_name;
		$description  = $user->description;
		$nickname     = $user->nickname;
				
		// get active custom fields
		$custom_fields = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_register'=>true,'on_profile'=> true,'on_public_profile'=> true)));
		// init
		$custom_field_tags = array();
		// loop
		foreach($custom_fields as $custom_field){
			// if already set skip it
			if(!isset(${$custom_field['name']}) || (isset(${$custom_field['name']}) && empty(${$custom_field['name']}))){
				// check
				if(isset($member->custom_fields->$custom_field['name'])){
					// skip password always
					if($custom_field['name']=='password') continue;
					// value
					$value = $member->custom_fields->$custom_field['name'];
					// country
					if($custom_field['name']=='country') $value = mgm_country_from_code($value);
					// set
					$custom_field_tags[$custom_field['name']] = $value ;
				}
			}	
		}		
	}else{
		// get active custom fields
		$custom_fields = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_register'=>true,'on_profile'=> true,'on_public_profile'=> true)));
		// init
		$custom_field_tags = array();
		// loop
		foreach($custom_fields as $custom_field){
			// set
			$custom_field_tags[$custom_field['name']] = '';
		}
	}	

	/*
	 * [[purchase_cost]] = Cost and currency of a purchasable post
	 * [[login_register]] = Login or register form
	 * [[login_register_links]] = Links for login and register
	 * [[login_link]] = Login link only
	 * [[facebook_login_button]] = Facebook login button	 
	 * [[register_link]] = Register link only
	 * [[membership_types]] = A list of membership levels that can see this post/page
	 * [[duration]] = number of days that the user will have access for
	 * [[username]] = username
	 * [[name]] = name / username
	 * [[register]] = register form
	 */
    // post
	$post_id    = get_the_ID();
	// vars
	$system_obj = mgm_get_class('system');
	$currency   = $system_obj->setting['currency'];
	$post_obj   = mgm_get_post($post_id);  
	$duration   = $post_obj->get_access_duration();
	if (!$duration) $duration = __('unlimited', 'mgm');	
	
	$purchase_cost = $post_obj->purchase_cost;	
	
	$currency_sign = mgm_get_currency_symbols($system_obj->setting['currency']);		
	
	// these function calls are called repeadtedly as filter is used in multiple places
	// call only when tag present in message
	
	// [login_register_links]
	if(preg_match('/[[login_register_links]]/',$message)){
		$login_register_links = (!$logged_in ? mgm_get_login_register_links():'');
	}
	// [login_link]
	if(preg_match('/[[login_link]]/',$message)){
		$login_link = (!$logged_in ? mgm_get_login_link():'');
	}	
	// [facebook_login_button]
	if(preg_match('/[[facebook_login_button]]/',$message)){
		$facebook_login_button = (!$logged_in ? mgm_generate_facebook_login():'');
	}	
	// [register_link]	
	if(preg_match('/[[register_link]]/',$message)){	
		$register_link = (!$logged_in ? mgm_get_register_link():'');
	}
	// [login_register]
	if(preg_match('/[[login_register]]/',$message)){
		$login_register = (!$logged_in ? mgm_sidebar_user_login_form(__('Register','mgm')):'');
	}
	// [register]
	if(preg_match('/[[register]]/',$message)){
		$register = (!$logged_in ? mgm_user_register_form():'');
	}
	// membership type
	if (!$membership_types = $post_obj->get_access_membership_types()) {
		// purchasble
		if (mgm_post_is_purchasable($post_id)) {
			$membership_types = 'Purchasable Only';
		} else {
		// access 
			$membership_types = 'No access';
		}
	}else{
		// get object
		$membership_types_obj = mgm_get_class('membership_types');
		// init array
		$ms_types_array = array();
		// loop
		foreach($membership_types as $membership_type){
			// set
			if(isset($membership_types_obj->membership_types[ $membership_type ])){
				$ms_types_array[] = $membership_types_obj->membership_types[ $membership_type ];
			}
		}
		// reset					
		$membership_types = implode(', ', $ms_types_array);
		// unset
		unset($ms_types_array);			
	}
	
	// loop defined
	$tags = array('purchase_cost','login_register','login_register_links','login_link','register_link','membership_types',
				  'duration','register','username','name','email','url','display_name','first_name','last_name',
				  'description','nickname','facebook_login_button','currency_sign');				  
	// loop
	foreach($tags as $tag){
		// check
		if(!isset(${$tag})) ${$tag} = '';
		// set
		$message = str_replace('[['.$tag.']]', ${$tag}, $message);
	}	
			
	// custom_field_tags
	if(is_array($custom_field_tags)){
		// loop
		foreach($custom_field_tags as $tag=>$value){
			// check
			if(!isset($value)) $value = '';					
			// set
			$message = str_replace('[['.$tag.']]', $value, $message);
		}	
	}
	// return
	return $message;
}
//remove manually included scripts from $wp_scripts object
function mgm_filter_scripts() { 
	
	global $mgm_scripts, $wp_scripts;	//mgm_scripts is the array to hold mgm scripts loaded at runtime.	
	if(is_array($wp_scripts->registered) && is_array($mgm_scripts)) {
		$mgm_scripts = array_unique($mgm_scripts);				
		foreach ($wp_scripts->registered as $key => $obj) {			
			$file = basename($obj->src);
			if(in_array($file, $mgm_scripts)) {	//This will prevent library scripts from loading multiple times							
				wp_deregister_script( $key );				
			}
		}
	}
	//specifically remove jquery files:
	//incomplete	
	//return;
	$arr_jquery = array();
	$arr_exceptions = array('jquery.ajaxfileupload.js',
							'jquery-ui-1.7.3.min.js',
							'jquery.form.js',
							'jquery.scrollTo-min.js',
							'jquery.validate.pack.js',
							'jquery.corner.js');
	//issue #1589
	if( mgm_is_plugin_active('simple-press/sp-control.php') ){				
		$arr_sp_exceptions =array(	'jquery.ui.plupload.js',
									'jqueryFileTree.js',
									'jquery.captcha.js',
									'jquery.js',
									'jquery.tools.min.js');		
		$arr_exceptions = array_merge($arr_exceptions, $arr_sp_exceptions);		
	}							
	//issue #1637
	if(function_exists('wp_get_theme') && strtolower(wp_get_theme()) == 'oxygen'){
		$arr_ox_theme_exceptions =array('jquery.masonry.min.js');
		$arr_exceptions = array_merge($arr_exceptions, $arr_ox_theme_exceptions);
	}
	//issue #1670		
	if(function_exists('wp_get_theme') && strtolower(wp_get_theme()) == 'holly eriksson'){
		$arr_he_theme_exceptions =array('jquery.prettyPhoto.js','jquery.flexslider-min.js',
		'jquery.tweet.js');
		
		$arr_exceptions = array_merge($arr_exceptions, $arr_he_theme_exceptions);
	}
	//issue #1677
	if(function_exists('wp_get_theme') && strtolower(wp_get_theme()) == 'salutation'){
		$arr_fb_theme_exceptions =array('jquery.colorbox-min.js');
		$arr_exceptions = array_merge($arr_exceptions, $arr_fb_theme_exceptions);
	}
	//issue #1695
	if(function_exists('wp_get_theme') && strtolower(wp_get_theme()) == 'cgbloggernew'){
		$arr_cgblogger_theme_exceptions =array('jquery.js');
		$arr_exceptions = array_merge($arr_exceptions, $arr_cgblogger_theme_exceptions);
	}
	//issue #1717	
	if( mgm_is_plugin_active('easy-digital-downloads/easy-digital-downloads.php') || mgm_is_plugin_active('fusion-core/fusion-core.php') ){
		$arr_edd_exceptions =array('chosen.jquery.min.js');
		$arr_exceptions = array_merge($arr_exceptions, $arr_edd_exceptions);	
	}			
	//loop			
	foreach ($wp_scripts->registered as $key => $obj) {
		$file = basename($obj->src);
		//if(preg_match('/jquery./', $file) || preg_match('/jquery-/', $file)) {
		if(preg_match('/jquery./', $file) || preg_match('/jquery-/', $file)) {
			if(in_array($file, $arr_jquery) && !in_array($file, $arr_exceptions)) {							
				wp_deregister_script( $key );
			}else
				$arr_jquery[] = $file;
		}
	}
}

/**
 * filter feed content
 *
 * @param object system
 * @return boolean redirect status
 * @since 2.5.2
 */
function mgm_feed_content_protection($content){	
	// return 
	return $content;
}
/**
 * geust lockdown
 */
function mgm_guest_lockdown(){
	// not for admin
	if(is_super_admin() || is_user_logged_in()) return true;	
	
	// system
	$system_obj = mgm_get_class('system');
	// check
	if(bool_from_yn($system_obj->get_setting('enable_guest_lockdown'))){
		// current url
		$current_url = mgm_get_current_url();
		// allowed urls
		$allowed_urls  = array();
		// redirect
		if($lockdown_redirect_url = $system_obj->get_setting('guest_lockdown_redirect_url')){
			$allowed_urls[] = $lockdown_redirect_url;
		}
		// known urls
		$known_urls = array('login','register','lostpassword','transactions','purchase_content','purchase_subscription','payments');
		// login
		foreach($known_urls as $url){
			$allowed_urls[] = mgm_get_custom_url($url);
		}
		//issue #1224
		$upload_url = site_url('upload?file_upload=image');
		$allowed_urls[] = $upload_url;
		
		// As /payments is still being used in module urls
		$allowed_urls[] = untrailingslashit(get_option('siteurl')) . '/payments';
		// make unique
		$allowed_urls =  array_unique($allowed_urls);
		// default redirect
		$redirect = true;
		// allowed
		foreach($allowed_urls as $allowed_url){
			// remove trailing slash as some of the payment gateway notify urls don't have trailing slash after transaction url
			$allowed_url = untrailingslashit($allowed_url);
			// match exact or pattenr
			if($allowed_url == $current_url || preg_match("#^".preg_quote($allowed_url,'/')."#", $current_url)){
				$redirect = false; break;
			}
		}		
		
		// redirect
		if($redirect){
			// first
			if($redirect_url = array_shift($allowed_urls)){	
				// leave favicon
				if(!preg_match('/\.ico$/', $current_url)){	
					// log
					// mgm_log($current_url . ' == ' .$redirect_url, __FUNCTION__);
					// redirect
					wp_redirect($redirect_url);exit;
				}
				return true;
			}
		}				
	}
	// return
	return true;
}

/**
 * add custom colums to post/page/post type UI
 *
 * @param array $columns
 * @param string $post_type
 * @return array $columns
 */
function mgm_manage_posts_columns($columns, $post_type='page'){
	// new
	$new_columns = array('access_level' => __('Access Level','mgm'), 'purchasable' => __('Purchasable?','mgm'));
	
	// return
	return array_merge( $columns, $new_columns);	
}

/**
 * add custom colums row to post/page/post type UI
 *
 * @param array $column
 * @param init $post_id
 * @return void
 */
function mgm_manage_posts_custom_column( $column, $post_id ) {	
	// column
	switch ( $column ) {
		case 'access_level':
			// post object
			$post_obj = mgm_get_post($post_id);
			// fetch
			$access_levels = $post_obj->get_access_membership_types();
			// check
			echo (empty($access_levels)) ? __('Public', 'mgm') : implode(', ', $access_levels);
		break;	
		case 'purchasable':
			// post object
			$post_obj = mgm_get_post($post_id);
			// check
			echo $post_obj->is_purchasable() ? __('Yes','mgm') : __('No','mgm');
		break;
	}
}

/**
 * override default template
 *
 * @param array $column
 * @param init $post_id
 * @return void
 */
function mgm_template_include($template){
	// check override
	if( bool_from_yn( mgm_get_setting('override_theme_for_custom_pages') ) )
	{ 
		// name
		$name = mgm_get_query_var('name') ;
		// switch
		switch( $name ){
			case 'register':
			case 'profile':
			case 'lost_password':
			case 'login':
				$content = mgm_get_query_post_content(); // @todo check #BUG_PENDING
				// check
				if( mgm_is_custom_page_published($name, $content) ){
					// if template exists in theme only
					if($c_template = mgm_get_page_template($name, false, true)){
						$template = $c_template;
					}	
				}
			break;	
		}
	}
	// return
	return $template;
}

// force wp https ssl
function mgm_wphttps_force_ssl( $force_ssl, $post_id = 0, $url = '' ) {
	// return		
	return $force_ssl = true;		
}
/**
 * add new page
 */
/*function mgm_add_new_custom_page($pages){
	// return	
	return array_merge($pages, array('post_title'=>'Users', 'post_content'=>'[user_list]', 'post_name'=>'users'));
}*/
// core/hooks/content_hooks.php
// end of file
