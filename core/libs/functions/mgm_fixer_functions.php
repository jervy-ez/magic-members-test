<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members fixer functions
 *
 * @package MagicMembers
 * @subpackage Facebook
 * @since 2.6
 */

function mgm_fix_general_clases(){
	// classes
	$classes = array('auth','system','membership_types','member_custom_fields','post_category','schedular','sidebar_widget',
					 'subscription_packs'); 
	
	// classes
	mgm_fix_classes($classes,'class');
}
// payment modules
function mgm_fix_payment_modules(){
	// payment modules
	$modules = array('1shoppingcart','2checkout','alertpay','authorizenet','ccbill','clickbank','epoch','eway',
					 'free','ideal','manualpay','moneybookers','ogone','paypal','paypalpro','sagepay','trial',
					 'worldpay','zombaio');
	// payment modules
	mgm_fix_classes($modules,'payment');					 

}
// autoresponder modules
function mgm_fix_autoresponder_modules(){
	// autoresponder modules						 
	$modules = array('autoresponseplus','aweber','constantcontact','getresponse','gvo','icontact','mailchimp');	
	// autoresponder modules
	mgm_fix_classes($modules,'autoresponder');
}
// fix users
function mgm_fix_users(){
	global $wpdb;	
	// get last converted user
	if(!$last_converted_user_id = get_option('mgm_last_converted_user_id')){
		$last_converted_user_id = 1;
	}
	// sql
	$sql = "SELECT `user_id` FROM `{$wpdb->usermeta}` WHERE `meta_key` = 'mgm_member' AND `user_id` > '{$last_converted_user_id}' ORDER BY `user_id` ASC LIMIT 0,5000";	
	// member objects		
	$users = $wpdb->get_results($sql);		
	// check
	if($users && count($users)>0){
		// fix		
		$last_converted_user_id = mgm_fix_member_classes($users);
		// update
		update_option('mgm_last_converted_user_id', $last_converted_user_id);
	}else{
		// mark as completed
		update_option('mgm_converted_users', time());
	}		
}
// fix posts
function mgm_fix_posts(){
	global $wpdb;
	// get last converted post
	if(!$last_converted_post_id = get_option('mgm_last_converted_post_id')){
		$last_converted_post_id = 1;
	}
	// sql
	$sql = "SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_mgm_post' AND `post_id` > '{$last_converted_post_id}' ORDER BY `post_id` ASC LIMIT 0,500";
	// post objects
	$posts = $wpdb->get_results($sql);		
	// check
	if($posts && count($posts)>0){
		// fix		
		$last_converted_post_id = mgm_fix_post_classes($posts);
		// update
		update_option('mgm_last_converted_post_id', $last_converted_post_id);
	}else{
		// mark as completed
		update_option('mgm_converted_posts', time());
	}	
}
// fix all classes
function mgm_fix_class_conversion(&$object=NULL){	
	// fix general classes 
	mgm_fix_general_clases();
	// fix payment modules
	mgm_fix_payment_modules();
	// fix autoresponder modules
	mgm_fix_autoresponder_modules();	
	// fix users
	mgm_fix_users();	
	// fix posts
	mgm_fix_posts();
} 

// fix general classes 
function mgm_fix_classes($classes,$type='class'){
	// fix classes
	foreach($classes as $class){
		// key
		$class_name = 'mgm_'.$class;	
		// check
		if(!get_option($class_name.'_options')){							
			// object
			if($class_obj = mgm_get_cached_object($class_name,$type)){				
				// get new class/module
				switch($type){
					case 'payment':
					case 'autoresponder':
						$new_class_obj = mgm_get_module($class_name,$type);
					break;
					case 'class':
					default:
						$new_class_obj = mgm_get_class($class_name);
					break;	
				}
				// apply fix
				//$new_class_obj->apply_fix($class_obj);
				// unset both
				//unset($class_obj,$new_class_obj);
				//issue #: 533
				if(is_object($new_class_obj) && method_exists($new_class_obj, 'apply_fix' )) {					
					$new_class_obj->apply_fix($class_obj);
					unset($class_obj,$new_class_obj);
				}
			}
		}
	}
}

// fix member classes 
function mgm_fix_member_classes($users){
	// key			
	$class_name = 'mgm_member';
	$option     = sprintf('%s_options', $class_name);
	// last id
	$last_id = NULL;
	// fix classes
	foreach($users as $user){		
		// check
		if(!mgm_get_user_option($option,$user->user_id)){									
			// object
			if($class_obj = mgm_get_cached_object($class_name,'member',$user->user_id)){							
				// get new class/module				
				$new_class_obj = mgm_get_member($user->user_id);	
				// apply fix
				$new_class_obj->apply_fix($class_obj);
				// unset both
				unset($class_obj,$new_class_obj);
			}
		}
		// set
		$last_id = $user->user_id;
	}
	// return
	return $last_id;
}

// fix post classes  
function mgm_fix_post_classes($posts){
	// key			
	$class_name = '_mgm_post';
	$option     = sprintf('%s_options', $class_name);
	// last id
	$last_id = NULL;
	// fix classes
	foreach($posts as $post){		
		// check
		if(!get_post_meta($post->post_id,$option)){									
			// object
			if($class_obj = mgm_get_cached_object($class_name,'post',$post->post_id)){							
				// get new class/module				
				$new_class_obj = mgm_get_post($post->post_id);	
				// apply fix
				$new_class_obj->apply_fix($class_obj);
				// unset both
				unset($class_obj,$new_class_obj);
			}
		}
		// set
		$last_id = $post->post_id;
	}
	// return
	return $last_id;
}

// wrapper to capture cached class
function mgm_get_cached_object($class_name,$type,$id=false){
	// on type
	switch($type){
		case 'payment':
		case 'autoresponder':
			return mgm_get_module($class_name,$type,true);
		break;
		case 'member':
			return mgm_get_member($id,true);// cached from db
		break;
		case 'post':
			return mgm_get_post($id,true);// cached from db
		break;
		case 'class':
		default:
			return mgm_get_option($class_name);
		break;	
	}
	// error
	return false;
}
// end file /core/libs/functions/mgm_fixer_functions.php