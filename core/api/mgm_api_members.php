<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members api members controller
 *
 * @package MagicMembers
 * @version 1.0
 * @since 2.6.0
 */
 class mgm_api_members extends mgm_api_controller{
 	// construct
	public function __construct(){
		// php4
		$this->mgm_api_members();
	}
	
	// php4
	public function mgm_api_members(){
		// parent
		parent::__construct();
	}
	
	/** 
	 * get members
	 * filters via GET PARAMS,by id, membership_type, status, pack_id
	 *
	 * @param int optional member/user id
	 * @return members 
	 * @verb GET
	 * @action all 	
	 * @url <site>/mgmapi/members.<format>
	 * @url <site>/mgmapi/members/:id.<format>
	 * @since 1.0
	 */
	public function index_get($id=false){
		global $wpdb;		
		// get vars
		$get_vars = $this->request->data['get'];
		
		// start
		$start = (isset($get_vars['start'])) ? (int)$get_vars['start'] : 0 ;
		// rows
		$rows = (isset($get_vars['rows'])) ? (int)$get_vars['rows'] : 100 ;	
		
		// query
		$query_str = '';
		// check
		if(isset($get_vars['id']) && (int)$get_vars['id']>0 || (int)$id>0){		
			// id
			$user_id = isset($get_vars['id']) ? (int)$get_vars['id'] : (int)$id;			
			// str
			$query_str = sprintf("AND `ID` = '%d' ", $user_id);			
		}	
		// check
		if(isset($get_vars['username']) && !empty($get_vars['username'])){		
			// id
			$username = $get_vars['username'];			
			// str
			$query_str .= sprintf("AND `user_login` = '%s' ", $username);			
		}
		// sql
		$sql = "SELECT SQL_CALC_FOUND_ROWS ID,user_login,user_email,user_registered,display_name FROM `{$wpdb->users}` 
		        WHERE ID <> 1 {$query_str} ORDER BY `user_registered` DESC LIMIT {$start},{$rows}";
		// get all users
		$results = $wpdb->get_results($sql);
		// total
		$total_rows = $wpdb->get_var("SELECT FOUND_ROWS() AS row_count");		
		// users
		$users = array();		
		// loop
		if($results){
			// loop
			foreach($results as $row){							
				// role
				$row->role = $this->_get_user_role( $row ); 
				// get member
				$member = mgm_get_member( $row->ID );
				// filter by :status
				if(isset($get_vars['status']) && !empty($get_vars['status'])){
					// check
					if(strtolower($member->status) != strtolower($get_vars['status'])) continue;
				}	
				// filter by :membership_type
				if(isset($get_vars['membership_type']) && !empty($get_vars['membership_type'])){
					// check
					if(strtolower($member->membership_type) != strtolower($get_vars['membership_type'])) continue;
				}	
				// filter by :pack_id
				if(isset($get_vars['pack_id']) && !empty($get_vars['pack_id'])){
					// check
					if(strtolower($member->pack_id) != strtolower($get_vars['pack_id'])) continue;
				} 
				// custom fields
				$row->custom_fields = $this->_get_user_custom_fields( $member );
				// subscriptions
				$row->subscriptions = $this->_get_user_subscriptions( $member, $row );
				// convert
				// $row->id = $row->ID;
				//unset($row->ID);
				// set
				$users[] = $row;
				// unset
				unset($member);
			}
		}	
		
		// base
		$data = array('total_rows'=>$total_rows);
		// data
		if((int)$id>0){
			// message
			$message = sprintf(__('Get member by id#%d response','mgm'), $id, count($users));
			// data
			if($total_rows > 0) $data = $data + array('user'=>array_shift($users));
		}else{
			// rows
			$rows    = ($total_rows < $rows) ? $total_rows : $rows;
			// message
			$message = sprintf(__('Get members response - %d of %d member(s) found, from %d - %d row(s)','mgm'), count($users), $total_rows, $start, $rows);
			// data
			if($total_rows > 0) $data = $data + array('users'=>$users);
		}
		
		// response
		$response = array('status'  => 'success', 
		                  'message' => $message, 
		                  'data'    => $data
						 );
		// return
		return array($response, 200);
	}
	
	/** 
	 * get posts accessible to member by user id
	 *
	 * @param int user id
	 * @return posts 
	 * @verb GET
	 * @action all 	
	 * @url <site>/mgmapi/members/posts.<format>
	 */
	 public function posts($id){
		global $wpdb;		
		// int
		$id = (int)$id;
		
		// posts
		$posts = array();
		$total_rows = 0;
		
		// get member
		if($member = mgm_get_member($id)){
			// get all subscribed membership types
			$membership_types = mgm_get_subscribed_membershiptypes($id, $member);	
			// accessible posts
			$accessible = mgm_get_membership_contents($membership_types, 'accessible', $id);			
			// purchased posts
			$purchased = mgm_get_purchased_posts($id);				
			// purchasable posts
			$purchasable = mgm_get_membership_contents($membership_types, 'purchasable', $id);	
			// total rows		
			$total_rows = $accessible['total_posts'] + $purchased['total_posts'] + $purchasable['total_posts'];
			
			
			// posts
			$posts = array('accessible'=>array('contents'=>$this->_clean_content($accessible['posts'])), 
			               'purchased'=>array('contents'=>$this->_clean_content($purchased['posts'])), 
						   'purchasable'=>array('contents'=>$this->_clean_content($purchasable['posts']))
						  );
		}
		// response
		$response = array('status'  => 'success', 
		                  'message' => sprintf(__('Get posts accessible to member by member id#%d response','mgm'), $id), 
		                  'data'    => array('total_rows'=>$total_rows, 'posts'=>$posts)
					     );	
		// return
		return array($response, 200);
	}	
	
	/** 
	 * export members
	 *
	 * @param int optional user id
	 * @return members 
	 * @verb GET
	 * @action all 	
	 * @url <site>/mgmapi/members/export.<format>
	 * @url <site>/mgmapi/members/:id/export.<format>
	 */
	 public function export($id=false){
		global $wpdb;		
		// int
		$id = (int)$id;
		
		// users
		$users = array();
		$total_rows = 0;
		
		// message
		if($id > 0){
			$message = sprintf(__('Get By ID#%d Member Export Response','mgm'), $id);
			$data    = array('total_rows'=>$total_rows, 'user'=>array_shift($users));
		}else{
			$message = __('Get Members Export Respons','mgm');
			$data    = array('total_rows'=>$total_rows, 'users'=>$users);
		}
		// response
		$response = array('status'  => 'success', 
		                  'message' => $message, 
		                  'data'    => $data
					     );	
		// return
		return array($response, 200);
	}	
	
	/** 
	 * import members
	 * 
	 * @param int user id
	 * @return members 
	 * @verb GET
	 * @action all 	
	 * @url <site>/mgmapi/members/:id/import.<format>
	 */
	 public function import($id=false){
		global $wpdb;		
		// int
		$id = (int)$id;
		
		// users
		$users = array();
		$total_rows = 0;
		
		// message
		if($id > 0){
			$message = sprintf(__('Get By ID#%d Member Import Response','mgm'), $id);
			$data    = array('total_rows'=>$total_rows, 'user'=>array_shift($users));
		}else{
			$message = __('Get Members Import Respons','mgm');
			$data    = array('total_rows'=>$total_rows, 'users'=>$users);
		}
		// response
		$response = array('status'  => 'success', 
		                  'message' => $message, 
		                  'data'    => $data
					     );	
		// return
		return array($response, 200);
	}		
	
	// private ------------------------------------------------------------------------
	
	/** 
	 * get wp role
	 */
	private function _get_user_role($user){
		// user object
		$user = & new WP_User( $user->ID );	
		// return
		return (is_array($user->roles) && !empty($user->roles)) ? array_shift($user->roles) : __('n/a','mgm');
	} 
		
	/** 
	 * get mgm subscriptions
	 */
	private function _get_user_custom_fields(&$member){
		// make array
		// return $custom_fields = mgm_object2array($member->custom_fields);
		$custom_fields = $member->custom_fields;
		// unset
		unset($member->custom_fields, $custom_fields->password, $custom_fields->password_conf);
		// init
		$_custom_fields = array();
		// check
		if( $custom_fields ){
			foreach((array)$custom_fields as $custom_field=>$field_value){
				if( is_string($field_value) ){
					$field_value = trim($field_value);
					$field_value = maybe_unserialize( $field_value );
				}
				$_custom_fields[$custom_field] = $field_value;
			}
		}
		
		// return 
		return $_custom_fields;
	}
	
	/** 
	 * get mgm subscriptions
	 */
	private function _get_user_subscriptions(&$member, $user){
		// log
		// mgm_log($member, __FUNCTION__);
		
		// other_membership_types
		$other_membership_types = $member->other_membership_types;
		
		// unset prefilter
		unset($member->other_membership_types);
				
		// subscriptions
		$subscriptions = array();				
		
		// set
		$subscriptions[] = $this->_get_user_subscription($member, $user);
		
		// parse other_membership_types as subscriptions tree
		if(!empty($other_membership_types)){
			// mgm_log($other_membership_types, 'other_membership_types_'.$user->ID);
			foreach ($other_membership_types as $key => $member_oth){
				// as object
				$o_mgm_member = mgm_convert_array_to_memberobj($member_oth, $user->ID);
				// set
				$subscriptions[] = $this->_get_user_subscription($o_mgm_member, $user);
				// unset
				unset($o_mgm_member);
			}			
		}
				
		// unset
		unset($member);
			  
		// return
		return $subscriptions;
	} 
	
	/**
	 * mgm subscription
	 */
	private function _get_user_subscription(&$member, $user){
		// init
		$subscription = array();
		
		// base active package
		if(isset($member->pack_id) && $pack = mgm_get_pack($member->pack_id)){			
			// unset internal
			unset($pack['default'], $pack['move_members_pack'], $pack['active'], $pack['sort'], 
			     $pack['preference'], $pack['allow_renewal'], $pack['modules'], $pack['id'], $pack['product']);
			// update
			$pack['duration_type_expr'] = mgm_get_pack_duration_expr($pack['duration_type']);
			// sort
			ksort($pack);
			// set
			$subscription['package'] = array_merge(array('id'=>$member->pack_id),$pack);
			// unset
			unset($member->pack_id);
		}	  
				
		// coupons
		$coupons = array();
		// register coupon
		if(isset($member->coupon)){
			// set
			$coupons[] = array('register'=>$member->coupon);
			// unset
			unset($member->coupon);	
		}
				
		// upgrade coupon
		if(isset($member->upgrade)){			
			// set
			$coupons[] = array('upgrade'=>$member->upgrade);
			// unset
			unset($member->upgrade);		
		}
		// extend coupon
		if(isset($member->extend)){
			// set
			$coupons[] = array('extend'=>$member->extend);	
			// unset
			unset($member->extend);
		}			
		// set
		$subscription['coupons'] = $coupons;
		
		// payment				
		if(isset($member->payment_info)){
			// copy
			$payment_info = $member->payment_info;
			// module
			$payment_info->module = (isset($payment_info->module)) ? str_replace('mgm_', '', $payment_info->module) : '';
			// set
			$subscription['payment'] = $member->payment_info;
		}
		// other data, @ToDo add more later
		$other_fields = array('trial_on','trial_cost','trial_duration','trial_duration_type','trial_num_cycles','duration',
		                      'duration_type','amount','currency','join_date','last_pay_date','expire_date','membership_type',
							  'status','status_str','payment_type','active_num_cycles','transaction_id');
		// loop
		foreach($other_fields as $field){
			switch($field){
				case 'join_date':
				// join date
					$subscription[$field] = date('Y-m-d H:i:s', (isset($member->{$field}) && (int)$member->{$field} > 0 ? $member->{$field} : strtotime($user->user_registered)));
				break;				
				default:
					$subscription[$field] = isset($member->{$field}) ?  $member->{$field} : '';
				break;	
			}
		}
		
		// newsletter
		if(isset($member->subscribed) && isset($member->autoresponder)){
			$subscription['newsletter'] = array('module'     => str_replace('mgm_', '', $member->autoresponder), 
			                                    'subscribed' => (bool_from_yn($member->subscribed) ? 'yes' : 'no')
											   );
		}
		
		// return
		return $subscription;
	}
	
	/**
	 * clean content
	 */
	private function _clean_content($posts){
		// init
		$_posts = array();		
		// check
		if(count($posts)>0) { 			
			// loop
			foreach ($posts as $id=>$post) {						
				// trim
				$post->post_content = mgm_strip_shortcode($post->post_content);
				// store
				$_posts[] = $post;
			}
		}
		// return
		return $_posts;
	}
 }
 
 // return name of class 
 return basename(__FILE__,'.php');
// end file /core/api/mgm_api_members.php