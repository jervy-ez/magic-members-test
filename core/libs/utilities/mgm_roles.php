<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// ----------------------------------------------------------------------- 
/**
 * Magic Members roles utility class
 *
 * @package MagicMembers
 * @since 2.5
 */ 
class mgm_roles {
	public $admin_role			 = 'administrator';
	public $basic_role			 = 'subscriber';
	public $default_roles 		 = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
	public $default_levels 		 = array('level_0','level_1', 'level_2','level_3','level_4','level_5','level_6','level_7','level_8','level_9','level_10');
	public $blocked_capabilities = array('install_plugins','delete_plugins', 'delete_users','delete_themes','level_4','level_5','level_6','level_7','level_8','level_9','level_10');
	public $use_db				 = true;
	public $add_capability		 = false;
	// capabilities specific to MGM
	private $arr_custom_capabilities = array(
		'mgm_root', // MGM main link access
		'mgm_home', // MGM Dashboard
		// Members tab
		'mgm_members' => array( 
			'mgm_member_list',
			'mgm_subscription_options',
			'mgm_coupons',
			'mgm_addons',
			'mgm_roles_capabilities'
		), 
		// Content Control tab
		'mgm_content_control' => array(
			'mgm_protection',
			'mgm_downloads',
			'mgm_pages',
			'mgm_custom_fields'
		), 
		// Content Control tab
		'mgm_ppp' => array(		
			'mgm_post_packs',									
			'mgm_post_purchases',
			'mgm_addon_purchases'
		), 
		// MGM Payment Settings tab
		'mgm_payment_settings' ,
		// Autoresponders tab 
		'mgm_autoresponders',
		// Reports tab 
		'mgm_reports'=> array(											
			'mgm_sales',
			'mgm_earnings',
			'mgm_projection',
			'mgm_payment_history'
			
		), 
		// Misc. Settings tab
		'mgm_misc_settings' => array(											
			'mgm_general',
			'mgm_post_settings',
			'mgm_message_settings',
			'mgm_email_settings',
			'mgm_autoresponder_settings',
			'mgm_rest_API_settings',
		), 
		// Tools tab
		'mgm_tools' => array(											
			'mgm_data_migrate',
			'mgm_core_setup',
			//'mgm_upgrade',
			'mgm_system_reset',
			'mgm_logs',
			'mgm_dependency'
		),
		// Admin Widget Dashboard Statistics
		'mgm_widget_dashboard_statistics' ,
		// Admin Widget Dashboard membership options
		'mgm_widget_dashboard_membership_options'
	);
										
	public $role_type = 'mgm';
	
	// construct
	function __construct(){
		// php4 proxy
		$this->mgm_roles();
	}
	
	// php4 construct
	function mgm_roles(){
		// stuff
	}
	
	//fetch roles:
	function get_roles() {
		global $wp_roles, $current_user;
		$wp_roles->use_db = $this->use_db;		
		$arr_roles = array();
		$i = 0;		
		//get only mgm and default roles:		
		$arr_mgmroles 		= $this->_get_mgm_roles();
		$arr_defaultroles 	= $this->_get_default_roles();		
		$arr_capabilities 	= $this->get_all_capabilities(array_merge($arr_mgmroles, $arr_defaultroles));				
		
		foreach ($wp_roles->roles as $role => $content) {
			if($this->role_type == 'mgm') {
				if(!in_array($role, $arr_mgmroles)) continue;
			}elseif ($this->role_type == 'default') {
				if(!in_array($role, $arr_defaultroles)) continue;
			}elseif ($this->role_type == 'others') {
				if(in_array($role, array_merge( $arr_mgmroles, $arr_defaultroles ))) continue;					
			}			
				 			
			$obrole = $wp_roles->get_role($role);
			$arr_roles[$i]['role'] = $role;
			$arr_roles[$i]['name'] = $content['name'];
			$arr_roles[$i]['permitted'] = (in_array($role, $current_user->roles) || in_array($this->admin_role, $current_user->roles ) ) ? 1 : 0;
			$arr_roles[$i]['is_systemrole'] = in_array($role, $this->default_roles ) ? 1 : 0;
			
			if(!empty($arr_capabilities)) {
				$j = 0;
				foreach ($arr_capabilities as $cap => $capcontent) {					
					$capability = is_numeric($cap) ? $capcontent : $cap;
					$capability_name = ucfirst(str_replace('_', ' ', $capability));					
					$arr_roles[$i]['capabilities'][$j]['blocked'] = in_array($capability, $this->blocked_capabilities) ? 1 : 0 ;
					$arr_roles[$i]['capabilities'][$j]['capability'] = $capability;
					$arr_roles[$i]['capabilities'][$j]['name'] = $capability_name;
					if($obrole->has_cap($capability)) {
						$belongsto = 1;
					}else {
						$belongsto = 0;
					}
					$arr_roles[$i]['capabilities'][$j]['belongsto'] = $belongsto;					
					$j++;
				}
			}
			$i++;
		}		
		return $arr_roles;
	}
	//get full set of capabilities
	function get_all_capabilities($arrRoles = array()) {
		global $wp_roles;
		$wp_roles->use_db = $this->use_db;
		$capabilities = array();
		$arr_custom_caps = $this->get_custom_capabilities();				
		foreach($wp_roles->role_objects as $r => $role) {
			if($role->capabilities) {				
				//include only capabilities which belong to roles				
				if(!empty($arrRoles)) {
					if(($this->role_type == 'mgm' || $this->role_type == 'default')) {
						if(!in_array($r, $arrRoles)) continue;
					}elseif($this->role_type == 'others') {
						if(in_array($r, $arrRoles)) continue;
					}					
				}
		      foreach($role->capabilities as $cap => $content) {
		      	$cap = is_numeric($cap) ? $content : $cap;
		      	// skip default custom capabilities as they will get appeneded again at the end
		      	if (in_array($cap, $arr_custom_caps))
		      		continue;
		        $capabilities[$cap] = $cap;		       
		      }
			}
	    }
	    //remove levels
	    $capabilities = array_diff($capabilities, $this->default_levels);
	    $capabilities = array_unique($capabilities);
	    sort($capabilities);
	    
	    // append mgm cpabilities
	    if (!empty($this->arr_custom_capabilities))			
			$capabilities = array_merge($capabilities, $arr_custom_caps);
		
	    return $capabilities;
	}
	//fetch capability for a role
	function get_capabilities($role) {
		global $wp_roles;
		$arr_return = array(); 
		$wp_roles->use_db = $this->use_db;
		$arr_role = $wp_roles->get_role($role);
		if($arr_caps = $arr_role->capabilities) {			
			foreach ($arr_caps as $key => $value) {
				if(!in_array($key, $this->default_levels))
					$arr_return[] = $key; 
			}
		}
		return $arr_return;
	}
	
	//edit/rename role
	function edit_role($oldRole, $newRole) {
		global $wp_roles;
		$wp_roles->use_db = $this->use_db;
		$new_role = str_replace(" ", "_", strtolower($newRole));
		if(!in_array($oldRole, $this->default_roles )) {
			if( $new_role != $oldRole ) {
				if($wp_roles->is_role($oldRole) && !$wp_roles->is_role($new_role) ) {					
					//check role name is same as before:
					if( $wp_roles->role_names[ $oldRole ] != $newRole) {
						$objold = $wp_roles->get_role($oldRole);
						//create new role with old role's capabilites:
						$wp_roles->add_role($new_role, $newRole, $objold->capabilities );
						//add role to db:
						$arr_roles = get_option('mgm_created_roles');
						if(empty($arr_roles)) $arr_roles = array();
						array_push($arr_roles, $new_role);
						update_option('mgm_created_roles', $arr_roles);	
													
						//update users with new role(delete previous role)
						$this->remove_role( $oldRole, $new_role );
						return $new_role;
					}
				}
			}
		}	
		return $oldRole;	
	}
	
	//create a new role:
	function add_role($role_name, $capabilities) {
		global $wp_roles;
		// use db
		$wp_roles->use_db = $this->use_db;
		// trim name
		$role_name = trim($role_name);
		// spaces removed
		$role = str_replace(' ', '_', strtolower($role_name));	
		// not already assigned	
		if(!in_array($role, $this->default_roles )) {
			// valid
			if(!$wp_roles->is_role($role) ) {
				// cap
				if(!empty($capabilities)) $capabilities = $this->_assign_true_to_keys($capabilities);
				// add new rolw
				$wp_roles->add_role( $role, $role_name, $capabilities );
				//add role to db:
				$roles = get_option('mgm_created_roles');
				// init
				if(empty($roles)) $roles = array();
				// add
				array_push($roles, $role);
				// update
				update_option('mgm_created_roles', $roles);
				// return
				return true;
			}
		}		
		// return
		return false;
	}
	
	//remove/delete a role:
	function remove_role($roleToRemove, $newRole = '') {
		global $wp_roles;		
		if(empty($newRole)) $newRole = $this->basic_role;
		$wp_roles->use_db = $this->use_db;
		if( !in_array($roleToRemove, $this->default_roles) ) {
			//update users with new role
			$arr_users = $this->_get_user_ids();
			foreach ($arr_users as $uid) {
				$user = new WP_User($uid);
				if(in_array($roleToRemove, $user->roles)) {
					//add new role to the user:
					$user->roles = $this->_assign_true_to_keys($user->roles);					
					$user->add_role($newRole);					
					//remove old role:
					$user->roles = $this->_assign_true_to_keys($user->roles);					
					$user->remove_role($roleToRemove);
				}
			}			
			$wp_roles->remove_role($roleToRemove);
			
			//update mgm packages:			
			if($newRole != '') {
				$update_pack = 0;
				$packs_obj = mgm_get_class('subscription_packs');				
				if(isset($packs_obj->packs) && count($packs_obj->packs) > 0) {
					$pack_count = count($packs_obj->packs);
					for($i = 0;  $i < $pack_count; $i++ ) {
						if(isset($packs_obj->packs[$i]['role']) && $packs_obj->packs[$i]['role'] == $roleToRemove) {
							$packs_obj->packs[$i]['role'] = $newRole;
							$update_pack++;
						}
					}
				}
				if($update_pack > 0) {					
					// update_option('mgm_subscription_packs', $packs_obj);
					$packs_obj->save();
				}
			}			
			
			//update db:
			$arr_roles = get_option('mgm_created_roles');	
			if(empty($arr_roles)) $arr_roles = array();		
			update_option('mgm_created_roles', array_diff($arr_roles, array($roleToRemove)));
			return true;	
		}
		return false;
	}
	//move Role's users to another role:
	function move_users($roleToRemove, $newRole = '') {
		global $wp_roles;		
		if(empty($newRole)) $newRole = $this->basic_role;
		$wp_roles->use_db = $this->use_db;		
		//update users with new role
		$arr_users = $this->_get_user_ids();
		foreach ($arr_users as $uid) {
			$user = new WP_User($uid);
			if(in_array($roleToRemove, $user->roles)) {
				//add new role to the user:
				$user->roles = $this->_assign_true_to_keys($user->roles);					
				$user->add_role($newRole);					
				//remove old role:
				$user->roles = $this->_assign_true_to_keys($user->roles);					
				$user->remove_role($roleToRemove);
			}
		}					
		return true;		
	}
	//add capability:
	function add_capability() {
		if(!$this->add_capability)
			return;
		global $wp_roles;		
		$role = $wp_roles->get_role($this->admin_role);
		$arr_capabilities = $this->get_all_capabilities();
		foreach ($this->arr_custom_capabilities as $cap) {	
			if(!in_array($cap, $arr_capabilities)) {		
				$role->add_cap($cap);
				//update db:
				$arr_caps = get_option('mgm_capabilities');	
				if(!is_array($arr_caps)) $arr_caps = array();		
				update_option('mgm_created_roles', array_push($arr_caps, $cap));	
			}
		}		
	}
	// assign a capability to a role:
	function update_capability_role($role, $capability, $access = true) {
		global $wp_roles;
		$wp_roles->use_db = $this->use_db;
		//give access:
		if( $access ) {
			$wp_roles->add_cap($role, $capability, true);			
		}else {
			$wp_roles->remove_cap($role, $capability);			
		}		
	}
	//get userids - sitewide
	private function _get_user_ids() {		
	    global $wpdb;	    
	    //from cache
		$uids = wp_cache_get('all_user_ids', 'users');	 
		if(!$uids) {	    
			//$uids = $wpdb->get_col('SELECT ID from ' . $wpdb->users);
			$uids = mgm_get_all_userids();
	    	wp_cache_set('all_user_ids', $uids, 'users');
		}	    	    
	    return $uids;
	}
	//flip array and assign true to keys
	private function _assign_true_to_keys($roles) {
		$roles = array_flip($roles);
		foreach ($roles as $key => $value)
			$roles[$key] = true;
		return $roles;	
	}
	//check role is unique
	function is_role_unique($rolename, $edit = false, $prevRole = null) {
		global $wp_roles;			
		$rolename = trim($rolename);	
		$rolename_rep = str_replace(" ", "_", strtolower($rolename));
		foreach ($wp_roles->role_names as $role => $name) {			
			if(!$edit && ($rolename_rep == $role || $rolename == $name) ) {
				return false;			
			}elseif( $edit && $prevRole != $role && ($rolename_rep == $role || $rolename == $name) ) {
				return false;	
			}elseif(in_array($rolename_rep,$this->get_all_capabilities()))
				return false;
		}
		return true;
	}
	//get mgm and default roles
	function _get_mgm_roles() {		
		$arr_mgm_roles = get_option( 'mgm_created_roles' );
		if(!is_array($arr_mgm_roles))
			$arr_mgm_roles = array();		
		return $arr_mgm_roles;
	}
	//default roles
	function _get_default_roles() {
		return $this->default_roles;
	}	
	//default capabilities
	function get_mgm_default_capabilities() {
		$arr_mgmroles 		= $this->_get_mgm_roles();
		$arr_defaultroles 	= $this->_get_default_roles();		
		return $this->get_all_capabilities(array_merge($arr_mgmroles, $arr_defaultroles));
	}
	//assign role to user:
	function add_user_role($user_id, $role, $update_order = true, $remove_role = true) {
		global $wp_roles;	
		// db
		$wp_roles->use_db = $this->use_db;
		// user
		$user = new WP_User($user_id);
		// check
		if(!empty($role)) {		
			// not exist	
			if(!in_array($role, $user->roles)) { 	
				// assign			
				$user->roles = $this->_assign_true_to_keys($user->roles);
				// add
				$user->add_role($role);		
				// set role #789 to execute spf hook called by action "set_user_role", is it double?		
				$user->set_role($role);			
			}
			// check to remove any unwanted roles
			if($remove_role) {				
				mgm_remove_excess_user_roles($user_id);			
			}			
			// change role order			
			if($update_order) {				
				$this->highlight_role($user_id, $role);
			}				
		}				
	}
	
	//reverse role order:
	private function _reverse_roles($user_id) {
		global $wp_roles;	
		$wp_roles->use_db = $this->use_db;
		$user = new WP_User($user_id);		
		$user->caps = array_reverse($user->caps);
		update_user_meta( $user->ID, $user->cap_key, $user->caps );	
	}
	//replace $remove_role with $default_role;
	function replace_user_role($user_id, $remove_role, $default_role ) {
		global $wp_roles;	
		$wp_roles->use_db = $this->use_db;
		$user = new WP_User($user_id);	
		//remove user role:
		$user->remove_role($remove_role);		
		//add default role:
		$this->add_user_role($user_id, $default_role,false);		
	}
	//to highlight a selected role: set the role's index as 0
	function highlight_role($user_id, $role) {
		global $wp_roles;	
		$wp_roles->use_db = $this->use_db;
		$user = new WP_User($user_id);								
		$caps = array_keys($user->caps);		
		if(!empty($caps) && count($caps) > 0 && in_array($role, $caps)) {			
			$first_role = $caps[0];
			if($first_role == $role)
				return;
			$role_index = array_search($role, $caps);
			$caps[$role_index] = $first_role;			
			$caps[0] = $role;
			$new_cap = array();		
			foreach ($caps as $cap)
				$new_cap[$cap] = true;
				
			$user->caps = $new_cap; 			
			update_user_meta( $user->ID, $user->cap_key, $user->caps );				
		}
	}
	//test function:
	function print_role($user_id) {
		$roles = $this->get_user_role($user_id);
		// mgm_log('PRINTING USER ROLES:');		
		if(!empty($roles)) {			
			mgm_log(mgm_array_dump($roles, true));
		}
	}
	//fetch user role:
	function get_user_role($user_id) {
		global $wp_roles;	
		
		$wp_roles->use_db = $this->use_db;
		$user = new WP_User($user_id);			
		if(!empty($user->roles))
			return $user->roles;
			
		return array();	
	}
	//directly remove role from user
	function remove_userrole($user_id, $role) {
		global $wp_roles;	
		$wp_roles->use_db = $this->use_db;
		$user = new WP_User($user_id);	
		//remove user role:
		$user->remove_role($role);
	}	
	// fetch custom primary and secondary capabilities
	function get_custom_capabilities($primary_cap = null) {
		$arr_cap = array();
		// fetch capabilities of given primary capability
		if (!empty($primary_cap)) {
			$arr_cap = array_keys($this->arr_custom_capabilities[$primary_cap]);
		}else {			
			foreach ($this->arr_custom_capabilities as $primary => $secondary) {
				// to consider empty secondary array
				$primary = is_string($primary) ? $primary : $secondary;
				array_push($arr_cap, $primary); 
				if (is_array($secondary))
					$arr_cap = array_merge($arr_cap, $secondary);	
			}
		}
		
		return $arr_cap;
	}
	// fetch capabilities of the loggedin user
	function get_loggedinuser_custom_capabilities($user_id) {
		$capabilities = array();
		$roles = $this->get_user_role($user_id);		
		if (!empty($roles)) {
			foreach($roles as $role) {
				$role_capability = $this->get_capabilities($role);
				if (!empty($role_capability))
					$capabilities = array_merge($capabilities, $role_capability);
			}
		}
		
		if (!empty($roles))
			$capabilities = array_unique($capabilities);
			
		return $capabilities;
	}
	// Custom capability hierarchy (root/primary/secondary)
	function get_custom_capability_hierarchy() {
		$hierarchy = array();
		foreach ($this->arr_custom_capabilities as $primary => $secondary) {			
			// to consider empty secondary array
			$primary = is_string($primary) ? $primary : $secondary;
			if($primary == 'mgm_root') 
				$hierarchy[$primary] = 'root';
			elseif(is_string($primary)) {
				$hierarchy[$primary] = 'primary';
				// if widgets
				if(preg_match("/mgm_widget/", $primary))
					$hierarchy[$primary] = 'admin widget';
				if(is_array($secondary)) {
					foreach ($secondary as $sec) {
						$hierarchy[$sec] = 'secondary';
					}	
				}
			}
		}
		
		return $hierarchy;
	}
}
// core/libs/utilities/mgm_roles.php