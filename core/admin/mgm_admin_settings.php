<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin settings module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin_settings extends mgm_controller{
 	
	// construct
	function __construct()
	{		
		$this->mgm_admin_settings();
	}
	
	// construct php4
	function mgm_admin_settings()
	{		
		// load parent
		parent::__construct();
	}
	
	// index
	function index(){
		// data
		$data = array();
		// load template view
		$this->load->template('settings/index', array('data'=>$data));		
	}
	
	// general
	function general(){				
		// local
		extract($_POST);
	    // log
	    // mgm_log(mgm_array_dump($_POST,true));	   
		// update
		if(isset($settings_update) && !empty($settings_update)){
			// get system object	
			$system_obj = mgm_get_class('system');	
			
			// boolean flds
			$b_flds = array('reminder_days_incremental','use_ssl_paymentpage','enable_autologin','aws_enable_s3',
			                'enable_googleanalytics','enable_post_url_redirection','enable_facebook',
							'enable_process_inactive_users','enable_guest_lockdown', 'share_registration_url_with_bp',
							'aws_enable_qsa');					
			// hidden flds
			$h_flds = array('login_redirect_url','logout_redirect_url','category_access_redirect_url',
			                'autologin_redirect_url','googleanalytics_key','facebook_id','facebook_key',
							'guest_lockdown_redirect_url');
			// array fields
			// $a_flds = array('guest_content_purchase_options_links');
			// update if set			
			foreach($system_obj->setting as $k => $v){
				// set default boolean fields
				if(in_array($k, $b_flds)){
					$_POST[$k] = isset($_POST[$k]) ? $_POST[$k] : 'N';
				}
				// set default hidden fields
				if(in_array($k, $h_flds)){
					$_POST[$k] = isset($_POST[$k]) ? $_POST[$k] : '';
				}
				// set var
				if(isset($_POST[$k])){
					// array
					if(is_array($_POST[$k])){
						$system_obj->setting[$k] = (array)$_POST[$k];
					}else{
						$system_obj->setting[$k] = addslashes($_POST[$k]);		
					}
				}
				
				// aws_qsa_expires
				if('aws_qsa_expires' == $k)	{
					$system_obj->setting[$k] = (int)$_POST['aws_qsa_expires_unit'] . ' ' . $_POST['aws_qsa_expires_expr'];
				}				

				// multiple login time span	
				if('multiple_login_time_span' == $k){
					$system_obj->setting[$k] = (int)$_POST['multiple_login_time_span_unit'] . ' ' . $_POST['multiple_login_time_span_expr'];
				}	
			}														
			// update
			$system_obj->save();
			 
			// affiliate - issue #1758
			if((isset($_POST['use_affiliate_link']) == 'Y' ) && isset($_POST['affiliate_id'])){
				update_option('mgm_affiliate_id', intval($_POST['affiliate_id']));
			}else{
				delete_option('mgm_affiliate_id');
			}							
			// extend dir
			if(!empty($_POST['extend_dir']) && $_POST['extend_dir'] != get_option('mgm_extend_dir')){
			// verify dir
				if(is_dir(ABSPATH . $_POST['extend_dir'])){
					// update 
					update_option('mgm_extend_dir', $_POST['extend_dir']);
				}else{
					// error
					$error = 'The Extend Directory path is not valid! Please create the directory first and update settings.';
				}				
			}else{
				// verify
				if(empty($_POST['extend_dir']) || !is_dir(ABSPATH . get_option('mgm_extend_dir'))){
					// error
					$error = 'The Extend Directory path is not valid! Reverting back to default.';
					// update to default
					update_option('mgm_extend_dir', trailingslashit(PLUGINDIR . '/magicmembers/extend'));
				}
			}
			
			// message
			if(isset($error)){
				$reponse = array('status'=>'error','message'=>__($error,'mgm'));
			}else{
				$reponse = array('status'=>'success','message'=>__('General settings successfully updated.','mgm'));
			}
			// message
			echo json_encode($reponse);			
			// return
			return;
		}
		
		// data
		$data = array();
		// duration_exprs
		$data['qsa_expr'] = array('HOUR'=>__('HOUR','mgm'),'DAY'=>__('DAY','mgm'),'WEEK'=>__('WEEK','mgm'),
		                          'MONTH'=>__('MONTH','mgm'),'YEAR'=>__('YEAR','mgm'));
		// system
		$data['system_obj'] = mgm_get_class('system');		
		// bp
		$data['bp_active'] = mgm_is_plugin_active('buddypress/bp-loader.php');
		// load template view
		$this->load->template('settings/general', array('data'=>$data));		
	}
	
	// posts
	function posts(){	
		global $wpdb;	
		// local
		extract($_POST);
			
		// update
		if(isset($post_setup_save) && !empty($post_setup_save)){	
			// init updatd 
			$updated=0;					
			// get system object	
			$system_obj = mgm_get_class('system');
			
			//$setting['enable_facebook']= $system_obj->setting['enable_facebook'];
			
			// content protection
			$content_protection = $system_obj->setting['content_protection'];
			//Issue #720
			if(isset($add_private_tags)) {
				if ($add_private_tags == 'Y') {
					$system_obj->setting['add_private_tags'] = 'Y';
					$system_obj->save();
				}
			} else {
				$system_obj->setting['add_private_tags'] = 'N';
				$system_obj->save();
			}
			
			// membership types
			if(is_array($access_membership_types)){	
				$membership_types = json_encode($access_membership_types);
			}else{
				$membership_types = json_encode(array());
			}
			// init posts
			$wp_posts = array();
			// posts
			if(isset($posts)) $wp_posts = array_merge($wp_posts, $posts);		
			// pages
			if(isset($pages)) $wp_posts = array_merge($wp_posts, $pages);			
			// custom post types
			if(isset($custom_post_types)) $wp_posts = array_merge($wp_posts, $custom_post_types);					
			// add direct urls
			if($direct_urls){				
				// loop
				foreach($direct_urls as $direct_url_id => $direct_url){		
					// affected
					$affected = false;					
					// insert
					if(!empty($direct_url)){
						// check duplicate
						if(!mgm_is_duplicate(TBL_MGM_POST_PROTECTED_URL, array('url'), '', array('url' => $direct_url))){
							// add
							$affected = $wpdb->insert(TBL_MGM_POST_PROTECTED_URL, array('url'=>$direct_url,'membership_types'=>$membership_types));
						}	
					}						
					// update counter
					if($affected) $updated++;
				} 
			}		
			
			// check
			if($wp_posts){
				// loop
				foreach($wp_posts as $post_id){		
					// get object
					$post_obj = mgm_get_post($post_id);
					//Issue #838
					if (isset($purchasable) == 'Y') {
						// set
						$post_obj->purchasable = $purchasable;
					}
					// check
					if (isset($purchase_cost) && isset($purchasable) == 'Y') {
						// check
						if(!empty($purchase_cost) && $purchase_cost > 0){
							// set
							$post_obj->purchase_cost = $purchase_cost;
						}
					}
					
					// apply filter
					$post_obj = apply_filters('mgm_post_update', $post_obj, $post_id);
					// save meta						
					$post_obj->save();
					
					// if access set
					if(is_array($access_membership_types)){	
						// set
						$post_obj->access_membership_types = $access_membership_types;
						// apply filter
						$post_obj = apply_filters('mgm_post_update', $post_obj, $post_id);
						// save meta						
						$post_obj->save();
						// unset
						unset($post_obj);
						// check duplicate
						if(!mgm_is_duplicate(TBL_MGM_POST_PROTECTED_URL, array('post_id'), '', array('post_id' => $post_id))){
							// add
							$affected = $wpdb->insert(TBL_MGM_POST_PROTECTED_URL, array('url'=>get_permalink($post_id),'post_id'=>$post_id,'membership_types'=>$membership_types));
						}else{
							$affected = $wpdb->update(TBL_MGM_POST_PROTECTED_URL, array('membership_types'=>$membership_types), array('post_id'=>$post_id));
						}
					}
					
					// make private, add [private] tag
					if (mgm_protect_content($content_protection)) {
						// get post
						$wp_post = wp_get_single_post($post_id);
						// Check private tag on/off Issue #720
						if(bool_from_yn($system_obj->setting['add_private_tags'])){
							// double check, not already added
							if(preg_match('/\[private\](.*)\[\/private\]/', $wp_post->post_content) == FALSE){												
								// make content private
								$post_content = sprintf('[private]%s[/private]', $wp_post->post_content);
								// update
								wp_update_post(array('post_content'=>$post_content,'ID'=>$wp_post->ID));	
							}
						}
					}
					// update counter
					$updated++;
				}					
			}
							
			// response
			if($updated){
				$response = array('status'=>'success','message'=>sprintf(__('Post protection successfully updated. %d Post/Page(s) updated.', 'mgm'), $updated));
			}else{
				$response = array('status'=>'error','message'=>sprintf(__('Post protection failed. %d Post/Page(s) selected.', 'mgm'), $updated));
			}	
			// print
			echo json_encode($response);			
			// return
			return;
		}
		// data
		$data = array();
		// member types
		$arr_membershiptypes = array();
		// loop
		foreach (mgm_get_class('membership_types')->membership_types as $code => $name){
			$arr_membershiptypes[ $code ] = mgm_stripslashes_deep($name); 
		}
		// set	
		$data['membership_types'] = $arr_membershiptypes;	
		// posts
		$data['posts'] = mgm_field_values($wpdb->posts, 'ID', 'post_title', "AND (post_content NOT LIKE '%[private]%' OR post_content LIKE '[private]%') AND post_type = 'post' AND post_status = 'publish'");	
		// pages
		$data['pages'] = mgm_field_values($wpdb->posts, 'ID', 'post_title', "AND (post_content NOT LIKE '%[private]%' OR post_content LIKE '[private]%') AND post_type = 'page' AND post_status = 'publish'");	
		// custom post types		
		if($post_types = mgm_get_post_types(true, array('page','post'))){
			$data['custom_post_types'] = mgm_field_values($wpdb->posts, 'ID', "CONCAT(post_title, ' ( ', post_type, ' )') AS post_title", "AND (post_content NOT LIKE '%[private]%' OR post_content LIKE '[private]%') AND post_type IN ($post_types) AND post_status = 'publish'",'post_title');	
		}else{
			$data['custom_post_types'] = array();
		}			
		// posts access
		$data['posts_access'] = $wpdb->get_results(sprintf("SELECT * FROM %s WHERE `post_id` IS NOT NULL ORDER BY id ASC",TBL_MGM_POST_PROTECTED_URL));
		// direct urls access
		$data['direct_urls_access'] = $wpdb->get_results(sprintf("SELECT * FROM %s WHERE `post_id` IS NULL ORDER BY id ASC",TBL_MGM_POST_PROTECTED_URL));
		// load template view
		$this->load->template('settings/posts', array('data'=>$data));	
	}	
	
	// post posts_access_list
	function post_posts_access_list(){
		global $wpdb;
		// init
		$data = array();
		// page urls
		$data['posts_access'] = $wpdb->get_results(sprintf("SELECT * FROM %s WHERE `post_id` IS NOT NULL ORDER BY id ASC",TBL_MGM_POST_PROTECTED_URL));
		// load template view
		$this->load->template('settings/posts/posts_access', array('data'=>$data));	
	}
	
	// post direct_urls_access
	function post_direct_urls_access(){
		global $wpdb;
		// init
		$data = array();
		// page urls
		$data['direct_urls_access'] = $wpdb->get_results(sprintf("SELECT * FROM %s WHERE `post_id` IS NULL ORDER BY id ASC",TBL_MGM_POST_PROTECTED_URL));
		// load template view
		$this->load->template('settings/posts/direct_urls_access', array('data'=>$data));	
	}
	
	// post_access_update
	function post_settings_delete(){
		global $wpdb;	
		extract($_POST);		
		// check
		$post_id = $wpdb->get_var($wpdb->prepare("SELECT `post_id` FROM `".TBL_MGM_POST_PROTECTED_URL . "` WHERE id = %d", $id));		
		// if post
		if((int)$post_id>0){
		// update content
			// get content
			$wp_post = wp_get_single_post($post_id);
			// update
			wp_update_post(array('post_content'=>preg_replace('/\[\/?private\]/','',$wp_post->post_content),'ID'=>$wp_post->ID));
			// remove other Issue #922			
			// get object
			$post_obj = mgm_get_post($post_id);
			// set
			$post_obj->purchasable = 'N';
			$post_obj->purchase_cost = '0.00';
			$post_obj->access_membership_types = array();
			// save meta						
			$post_obj->save();
			// unset
			unset($post_obj);									
		}
		// sql
		$sql = $wpdb->prepare("DELETE FROM `" . TBL_MGM_POST_PROTECTED_URL . "` WHERE id = %d", $id); 		
		// delete
	    if ($wpdb->query($sql)) {    	    
			$message = __('Successfully deleted post settings: ', 'mgm');
			$status  = 'success';
	    }else{
			$message = __('Error while deleting post settings: ', 'mgm');
			$status  = 'error';
		}
		// return response
		echo json_encode(array('status'=>$status, 'message'=>$message));
	}
	
	// messages
	function messages(){	
		global $wpdb;	
		// local
		extract($_POST);
		// update
		if(isset($msgs_update) && !empty($msgs_update)){
			// get system object	
			$system_obj = mgm_get_class('system');
			// update if set
			foreach($_POST['setting'] as $k => $v){		
				// set var
				if(isset($v)){
					// set template
					$system_obj->set_template($k, $v);// save
					// copy to custom fields	
					if($k == 'subs_intro'){
						mgm_get_class('member_custom_fields')->update_field_value('subscription_introduction',$v);	
					}
					// tos
					if($k == 'tos'){
						mgm_get_class('member_custom_fields')->update_field_value('terms_conditions',$v);	
					}
				}
			}		
			// _update_modules
			if(isset($apply_update_to_modules) && $apply_update_to_modules == 'Y'){
				// update
				$this->_update_modules();
			}
			// response
			echo json_encode(array('status'=>'success','message'=>__('Message templates successfully updated.','mgm')));			
			// return
			return;
		}	
		
		
		// data
		$data = array();
		// system
		$data['system_obj'] = mgm_get_class('system');		
		// load template view
		$this->load->template('settings/messages', array('data'=>$data));		
	}
	
	// emails
	function emails(){	
		global $wpdb;	
		// local
		extract($_POST);
		// update
		if(isset($msgs_update) && !empty($msgs_update)){
			// get system object	
			$system_obj = mgm_get_class('system');
			// update if set
			foreach($_POST['setting'] as $k => $v){		
				// set var
				if(isset($v)){
					$system_obj->set_template($k, $v);		
				}
			}						
			// response
			echo json_encode(array('status'=>'success','message'=>__('Email templates successfully updated.','mgm')));
			// return
			return;
		}	
		// data
		$data = array();
		// system
		$data['system_obj'] = mgm_get_class('system');		
		// load template view
		$this->load->template('settings/emails', array('data'=>$data));		
	}
	
	// autoresponders
	function autoresponders(){		
		global $wpdb;
		// make local
		extract($_REQUEST);		
		// update
		if(isset($update) && !empty($update)){			
			// get module
			$module_object = mgm_get_module($module, 'autoresponder');	
			// settings update
			if(!$errors = $module_object->validate()){			
				// settings update
				$response = $module_object->settings_update();	
				// enable and activate module
				$module_object->enable(true);
			}else{
				$response =  json_encode(array('status'=>'error','message'=>sprintf(__('%s settings could not be updated','mgm'),$module_object->name),'errors'=>$errors));
			}
			// print
			echo $response;
			// return
			return;
		}
		
		// data
		$data = array();
		// get available
		$data['available_modules'] = mgm_get_modules('autoresponder');
		// loop
		foreach($data['available_modules'] as $module_name):				
			// get object
			$module_object = mgm_get_module('mgm_'.$module_name, 'autoresponder');				
			// get html
			$data['module'][$module_name]['html'] = $module_object->settings_box();					
		endforeach;		
		// load template view
		$this->load->template('settings/autoresponders', array('data'=>$data));	
	}	
	
	// REST API ------------------------------------------------------------------------------------
	// restapi 
	function restapi(){
		global $wpdb;
		// local
		extract($_POST);		
		// update
		if (isset($settings_update) && !empty($settings_update)) {
			// get system object	
			$system_obj = mgm_get_class('system');					
			// set data
			$system_obj->setting['rest_server_enabled']    = $rest_server_enabled;
			$system_obj->setting['rest_output_formats']    = $rest_output_formats;
			$system_obj->setting['rest_input_methods']     = $rest_input_methods;
			$system_obj->setting['rest_consumption_limit'] = (int)$rest_consumption_limit;
			// save
			$system_obj->save();					
			// message
			echo json_encode(array('status'=>'success','message'=>__('Rest API settings successfully updated.','mgm')));			
			// return
			return;
		}
		// data
		$data = array();		
		// system
		$data['system_obj'] = mgm_get_class('system');
		// load template view
		$this->load->template('settings/restapi', array('data'=>$data));	
	}
	
	// restapi levels list
	function restapi_levels(){
		global $wpdb;
		// data
		$data = array();
		// get list of levels
		$data['levels'] = $wpdb->get_results("SELECT id,level,name,permissions,limits FROM `".TBL_MGM_REST_API_LEVEL."` ORDER BY level ASC");
		// load template view
		$this->load->template('settings/restapi/levels/list', array('data'=>$data));
	}
	
	// restapi level add
	function restapi_level_add(){
		global $wpdb;
		extract($_POST);
		// save
		if(isset($save_level)){
			// check duplicate
			if(mgm_is_duplicate(TBL_MGM_REST_API_LEVEL,array('level'))){
				$message = sprintf(__('Error while creating new api level: %s, same level exists!', 'mgm'), $level);
				$status  = 'error';
			}else{		
				// init
				$sql_fields = array();		
				// permissions
				$permissions = ($permission_type == 'limited') ? json_encode($permissions): '[]';
				// fields
				$sql_fields[] = "level='{$level}'";
				$sql_fields[] = "name='{$name}'";
				$sql_fields[] = "permissions='{$permissions}'";
				// limits
				if(isset($limits) && is_numeric($limits)){
					$sql_fields[] = "limits='{$limits}'";
				}				
				// sql
				$sql = "INSERT INTO `" . TBL_MGM_REST_API_LEVEL . "` SET ".implode(',', $sql_fields);	
				// saved
				if ($wpdb->query($sql)) {
					$message = sprintf(__('Successfully created new level: %s', 'mgm'),  $name);
					$status  = 'success';
				}else{
					$message = sprintf(__('Error while creating new level: %s', 'mgm'),  $name);
					$status  = 'error';
				}
			}	
			// return response
			echo json_encode(array('status'=>$status, 'message'=>$message));
			exit();
		}
		// data
		$data = array();
		// load template view
		$this->load->template('settings/restapi/levels/add', array('data'=>$data));
	}
	
	// restapi level edit
	function restapi_level_edit(){
		global $wpdb;
		// id
		$id = (int)$_POST['id'];
		extract($_POST);
		
		// save
		if(isset($save_level)){
			// check duplicate
			if(mgm_is_duplicate(TBL_MGM_REST_API_LEVEL,array('level'),"id <> '{$id}'")){
				$message = sprintf(__('Error while updating level: %s, same level exists!', 'mgm'),  $level);
				$status  = 'error';
			}else{
				// init
				$sql_fields = array();
				// permissions
				$permissions = ($permission_type == 'limited') ? json_encode($permissions): '[]';
				// fields
				$sql_fields[] = "level='{$level}'";
				$sql_fields[] = "name='{$name}'";
				$sql_fields[] = "permissions='{$permissions}'";
				// limits
				if(isset($limits) && is_numeric($limits)){
					$sql_fields[] = "limits='{$limits}'";
				}else{
					$sql_fields[] = "limits=NULL";
				}		
				// sql
				$sql = "UPDATE `" . TBL_MGM_REST_API_LEVEL . "` SET ".implode(',', $sql_fields)." WHERE id='{$id}' ";		
				// saved
				if ($wpdb->query($sql)) {
					$message = sprintf(__('Successfully updated level: %s', 'mgm'),  $name);
					$status  = 'success';
				}else{
					$message = sprintf(__('Error while updating level: %s', 'mgm'),  $name);
					$status  = 'error';
				}
			}	
			// return response
			echo json_encode(array('status'=>$status, 'message'=>$message));
			exit();
		}	
		// data
		$data = array();
		// get list of levels
		$data['level'] = $wpdb->get_row("SELECT * FROM `".TBL_MGM_REST_API_LEVEL."` WHERE `id` = '{$id}'");
		// permission
		$data['permissions'] = json_decode($data['level']->permissions,true);
		// type		
		$data['permission_type'] = (empty($data['permissions'])) ? 'full' : 'limited'; 		
		// load template view
		$this->load->template('settings/restapi/levels/edit', array('data'=>$data));
	}
	
	// restapi level delete
	function restapi_level_delete(){
		global $wpdb;
		// id
		$id = (int)$_POST['id'];
	
		// save
		if(isset($id)){
			// name
			$name = $wpdb->get_var("SELECT `name` FROM `".TBL_MGM_REST_API_LEVEL."` WHERE `id` = '{$id}'");			
			// sql
			$sql = "DELETE FROM `" . TBL_MGM_REST_API_LEVEL . "` WHERE id='{$id}' ";		
			// saved
			if ($wpdb->query($sql)) {
				$message = sprintf(__('Successfully deleted level: %s', 'mgm'), $name);
				$status  = 'success';
			}else{
				$message = sprintf(__('Error while deleting level: %s', 'mgm'), $name);
				$status  = 'error';
			}			
			// return response
			echo json_encode(array('status'=>$status, 'message'=>$message));
			exit();
		}		
		// error
		echo json_encode(array('status'=>'error', 'message'=>__('Error!, no level id provided.','mgm')));
	}
	
	// restapi keys list
	function restapi_keys(){
		global $wpdb;
		// data
		$data = array();
		// get list of keys
		$data['keys'] = $wpdb->get_results("SELECT id,level,api_key,create_dt FROM `".TBL_MGM_REST_API_KEY."` ORDER BY create_dt DESC");
		// load template view
		$this->load->template('settings/restapi/keys/list', array('data'=>$data));
	}
	
	// restapi key add
	function restapi_key_add(){
		global $wpdb;
		extract($_POST);
		// save
		if(isset($save_key)){
			// check duplicate
			if(mgm_is_duplicate(TBL_MGM_REST_API_KEY,array('api_key'))){
				$message = sprintf(__('Error while creating new api key: %s, same key exists!', 'mgm'), $api_key);
				$status  = 'error';
			}else{				
				// init
				$sql_fields = array();
				// fields
				$sql_fields[] = "api_key='{$api_key}'";
				$sql_fields[] = "level='{$level}'";				
				$sql_fields[] = "create_dt = NOW()";							
				// sql
				$sql = "INSERT INTO `" . TBL_MGM_REST_API_KEY . "` SET ".implode(',', $sql_fields);	
				// saved
				if ($wpdb->query($sql)) {
					$message = sprintf(__('Successfully created new api key: %s', 'mgm'),  $api_key);
					$status  = 'success';
				}else{
					$message = sprintf(__('Error while creating new api key: %s', 'mgm'),  $api_key);
					$status  = 'error';
				}
			}	
			// return response
			echo json_encode(array('status'=>$status, 'message'=>$message));
			exit();
		}
		// data
		$data = array();
		// get levels
		$data['levels'] = $this->_get_levels_list();
		// load template view
		$this->load->template('settings/restapi/keys/add', array('data'=>$data));
	}
	
	// restapi key edit
	function restapi_key_edit(){
		global $wpdb;
		// id
		$id = (int)$_POST['id'];
		extract($_POST);
		
		// save
		if(isset($save_key)){
			// check duplicate
			if(mgm_is_duplicate(TBL_MGM_REST_API_KEY,array('api_key'),"id <> '{$id}'")){
				$message = sprintf(__('Error while updating api key: %s, same api key exists!', 'mgm'),  $api_key);
				$status  = 'error';
			}else{
				// init
				$sql_fields = array();
				// fields
				$sql_fields[] = "api_key='{$api_key}'";
				$sql_fields[] = "level='{$level}'";			
				// sql
				$sql = "UPDATE `" . TBL_MGM_REST_API_KEY . "` SET ".implode(',', $sql_fields)." WHERE id='{$id}' ";		
				// saved
				if ($wpdb->query($sql)) {
					$message = sprintf(__('Successfully updated api key: %s', 'mgm'),  $api_key);
					$status  = 'success';
				}else{
					$message = sprintf(__('Error while updating api key: %s', 'mgm'),  $api_key);
					$status  = 'error';
				}
			}	
			// return response
			echo json_encode(array('status'=>$status, 'message'=>$message));
			exit();
		}	
		// data
		$data = array();
		// get list of levels
		$data['key'] = $wpdb->get_row("SELECT * FROM `".TBL_MGM_REST_API_KEY."` WHERE `id` = '{$id}'");
		// get levels
		$data['levels'] = $this->_get_levels_list();
		// load template view
		$this->load->template('settings/restapi/keys/edit', array('data'=>$data));
	}
	
	// restapi key delete
	function restapi_key_delete(){
		global $wpdb;
		// id
		$id = (int)$_POST['id'];
		
		// save
		if(isset($id)){
			// name
			$api_key = $wpdb->get_var("SELECT `api_key` FROM `".TBL_MGM_REST_API_KEY."` WHERE `id` = '{$id}'");			
			// sql
			$sql = "DELETE FROM `" . TBL_MGM_REST_API_KEY . "` WHERE id='{$id}' ";		
			// saved
			if ($wpdb->query($sql)) {
				$message = sprintf(__('Successfully deleted key: %s', 'mgm'), $api_key);
				$status  = 'success';
			}else{
				$message = sprintf(__('Error while deleting key: %s', 'mgm'), $api_key);
				$status  = 'error';
			}			
			// return response
			echo json_encode(array('status'=>$status, 'message'=>$message));
			exit();
		}		
		// error
		echo json_encode(array('status'=>'error', 'message'=>__('Error!, no key id provided.','mgm')));
	}
	
	// PRIVATE ---------------------------------------------------------------------
	// update modules
	function _update_modules(){
		// get modules
		$modules = mgm_get_modules('payment');
		// loop
		foreach($modules as $module):			
			// instance	
			$module_object = mgm_get_module('mgm_'.$module, 'payment');						
			// update message	
			$module_object->_setup_callback_messages(array(), true); // update from global template			
			// update option			
			$module_object->save();
		endforeach;	
	}	
	
	//get levels
	function _get_levels_list(){
		global $wpdb;
		// list
		$levels = $wpdb->get_results("SELECT `level` FROM `".TBL_MGM_REST_API_LEVEL."` ORDER BY `level` ");
		// init
		$_levels = array();
		// check
		if($levels){
			// loop
			foreach($levels as $level){
				$_levels[] = $level->level;
			}
		}
		// return
		return $_levels;
	}	
 }
// return name of class 
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin_settings.php