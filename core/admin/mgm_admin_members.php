<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin members module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin_members extends mgm_controller{
 	
	// construct
	function __construct(){
		$this->mgm_admin_members();
	}	
	
	// php4
	function mgm_admin_members()
	{		
		// load parent
		parent::__construct();
	}
	
	// index
	function index(){
		// data
		$data = array();		
		// load template view
		$this->load->template('members/index', array('data'=>$data));		
	}
	
	// members -------------------------------------------
	// members index/tabs
	function members(){		
		global $wpdb;
		// data
		$data = array();							
		// load template view
		$this->load->template('members/member/index', array('data'=>$data));	
	}
	
	// manage
	function member_manage(){
		global $wpdb;
		// data
		$data = array();							
		// load template view
		$this->load->template('members/member/manage', array('data'=>$data));	
	}
	
	// members list
	function member_list(){		
		global $wpdb;		
		// system
		$system_obj = mgm_get_class('system');	
		// getting super admin ids - issue#1219
		$super_adminids = mgm_get_super_adminids();		
		// pager
		$pager = new mgm_pager();		
		// data
		$data = array();			
		// search fields
		$data['search_fields'] = array(''=> __('Select','mgm'), 'username'=> __('Username','mgm'), 'id'=> __('User ID','mgm'), 
		                              'email'=> __('User Email','mgm'), 'first_name' => __('First Name','mgm') ,'last_name' => __('Last Name','mgm'), 
									  'membership_type'=> __('Membership Type','mgm'), 'reg_date'=> __('Registration Date','mgm'), 
									  'last_payment'=> __('Last Payment','mgm'), 'expire_date'=> __('Expiration Date','mgm'), 
									  'join_date'=> __('Join Date','mgm'),'fee'=> __('Fee','mgm'), 'status'=> __('Status','mgm'),
									  'transaction_id'=> __('Transaction ID','mgm'), 'payment_module'=>__('Payment Module','mgm'), 'pack_id'=> __('Pack ID','mgm'));
		// sort fields							  
		$data['sort_fields'] = array( 'id'=> __('User ID','mgm'),'username'=> __('Username','mgm'), 'email'=> __('User Email','mgm'),
		                             'reg_date'=> __('Registration Date','mgm'),'last_pay_date'=> __('Last Pay Date','mgm'));	
		
		// filter
		$sql_filter = $data['search_field_name'] = $data['search_field_value'] = '';
		$search_field_name = mgm_post_var('search_field_name');
		
		//issue #1311
		$sformat = mgm_get_date_format('date_format_short');

		// check
		if(!empty($search_field_name)) {
			// post
			$search_field_value     = mgm_post_var('search_field_value');
			$search_field_value_two = mgm_post_var('search_field_value_two');
			
			// view data	
			$data['search_field_name'] 	    = $search_field_name;// for display						
			//issue #1281
			$data['search_field_value']     = htmlentities($search_field_value, ENT_QUOTES, "UTF-8");// for display
			$data['search_field_value_two'] = htmlentities($search_field_value_two, ENT_QUOTES, "UTF-8");// for display
			
			// issue#: 219					
			$search_field_value     = mgm_escape($search_field_value);// for sql
			// end date value
			$search_field_value_two = mgm_escape($search_field_value_two);// for sql		
			
			//current date
			$curr_date = mgm_get_current_datetime();
			$current_date = $curr_date['timestamp'];		
			
			// by field
			switch($search_field_name){
				case 'username':
					// issue#: 347(LIKE SEARCH)
					$sql_filter = " AND `user_login` LIKE '%{$search_field_value}%'";			
				break;	
				case 'id':
					$sql_filter = " AND `ID` = '".(int)$search_field_value."'";	
				break;
				case 'email':
					// issue#: 347(LIKE SEARCH)
					$sql_filter = " AND `user_email` LIKE '%{$search_field_value}%'";			
				break;	
				case 'membership_type':
					// members
					$members    = mgm_get_members_with('membership_type', $search_field_value);					
					//super admins check - issue#1219
					$members = array_diff($members,$super_adminids);
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";			
				break;	
				case 'reg_date':
					if(empty($search_field_value)){
						$search_field_value = date('Y-m-d',$current_date);
					}
					if(empty($search_field_value_two)){
						$search_field_value_two = date('Y-m-d',$current_date);
					}

					// convert 
					$search_field_value = mgm_format_inputdate_to_mysql($search_field_value,$sformat);	
					$search_field_value_two = mgm_format_inputdate_to_mysql($search_field_value_two,$sformat);	
					// set filter				
					// $sql_filter = " AND DATE_FORMAT(user_registered,'%Y-%m-%d') = '{$search_field_value}'";					
					$sql_filter = " AND DATE_FORMAT(user_registered,'%Y-%m-%d') BETWEEN '{$search_field_value}' AND '{$search_field_value_two}'";					
					// AND transaction_dt BETWEEN  '$start_date' AND  '$end_date'";
				break;	
				case 'last_payment':
					// date1
					if(empty($search_field_value)){
						$search_field_value = date('Y-m-d',$current_date);
					}
					// date2
					if(empty($search_field_value_two)){
						$search_field_value_two = date('Y-m-d',$current_date);
					}
					// convert
					$date_one = mgm_format_inputdate_to_mysql($search_field_value,$sformat);
					$date_two = mgm_format_inputdate_to_mysql($search_field_value_two,$sformat);	
					// members
					$members  = mgm_get_members_between_two_dates ('last_pay_date', $date_one,$date_two);
					//super admins check - issue#1219
					$members = array_diff($members,$super_adminids);					
					// convert
					// $search_field_value = mgm_format_inputdate_to_mysql($search_field_value);	
					// members
					// $members = mgm_get_members_with('last_pay_date', $search_field_value);
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";
				break;
				case 'expire_date':
					// date1
					if(empty($search_field_value)){
						$search_field_value = date('Y-m-d',$current_date);
					}
					// date2
					if(empty($search_field_value_two)){
						$search_field_value_two = date('Y-m-d',$current_date);
					}
					// convert
					$date_one = mgm_format_inputdate_to_mysql($search_field_value,$sformat);
					$date_two = mgm_format_inputdate_to_mysql($search_field_value_two,$sformat);	
					// members
					$members  = mgm_get_members_between_two_dates ('expire_date', $date_one,$date_two);
					//super admins check - issue#1219
					$members = array_diff($members,$super_adminids);															
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";
				break;
				case 'join_date':
					// date1
					if(empty($search_field_value)){
						$search_field_value = date('Y-m-d',$current_date);
					}
					// date2
					if(empty($search_field_value_two)){
						$search_field_value_two = date('Y-m-d',$current_date);
					}
					// convert
					$date_one = mgm_format_inputdate_to_mysql($search_field_value,$sformat);
					$date_two = mgm_format_inputdate_to_mysql($search_field_value_two,$sformat);	
					// members
					$members  = mgm_get_members_between_two_dates ('join_date', $date_one,$date_two);
					//super admins check - issue#1219
					$members = array_diff($members,$super_adminids);														
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
										
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";
				break;
				case 'fee':
					// members
					$members    = mgm_get_members_with('amount', $search_field_value);
					//super admins check - issue#1219
					$members = array_diff($members,$super_adminids);										
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";
				break;
				case 'status':
					// members
					$members    = mgm_get_members_with('status', $search_field_value);
					//super admins check - issue#1219
					$members = array_diff($members,$super_adminids);									
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";
				break;
				case 'first_name':
				case 'last_name':
					// members
					$members    = mgm_get_members_with($search_field_name, $search_field_value);
					//super admins check - issue#1219
					$members = array_diff($members,$super_adminids);										
					//check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";
				break;
				case 'transaction_id':
					// members
					$members    = mgm_get_members_with('transaction_id', $search_field_value);
					//super admins check - issue#1219
					$members = array_diff($members,$super_adminids);									
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";
				break;
				case 'payment_module':
					// members
					$members    = mgm_get_members_with('payment_module', $search_field_value);
					//super admins check - issue#1219
					$members = array_diff($members,$super_adminids);									
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";
					// show nice name
					$data['search_field_value'] = mgm_get_module($search_field_value)->name;
				break;
				case 'pack_id':
					// members
					$members    = mgm_get_members_with('pack_id', $search_field_value);
					//super admins check
					$members = array_diff($members,$super_adminids);										
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";
				break;				
			}
		}

		//super admins check - issue#1219
		$super_admin_in = (count($super_adminids)==0) ? 0 : (implode(',', $super_adminids));
		// page limit		
		$data['page_limit'] = isset($_REQUEST['page_limit']) ? (int)$_REQUEST['page_limit'] : 20;
		// page no
		$data['page_no']    = isset($_REQUEST['page_no']) ? (int)$_REQUEST['page_no'] : 1;		
		// limit
		$sql_limit = $pager->get_query_limit($data['page_limit']);
		
		//init
		$custom_user_list = array();
		$member_custom_sort = false;
		
		// order
		$sql_order = $data['sort_field'] = $data['sort_type'] = '';
		// sort
		$sort_field_name = mgm_post_var('sort_field_name');
		$sort_type       = mgm_post_var('sort_type');
		// check
		if(isset($sort_field_name)){
			//issue#: 219
			$data['sort_field'] = $sort_field_name;
			$data['sort_type']  = $sort_type;
			// by name
			switch($sort_field_name){
				case 'username':
					$sql_order_by = "user_login";
				break;
				case 'id':
					$sql_order_by = "ID";
				break;
				case 'email':
					$sql_order_by = "user_email";
				break;
				case 'membership_type':
				break;
				case 'reg_date':
					$sql_order_by = "user_registered";
				break;
				case 'last_pay_date':					
					
					$sql_order_by = mgm_member_sort($sort_field_name,$sort_type,$sql_filter,$super_adminids);
					
					//mgm_log(mgm_array_dump($sql_order_by,true),__FUNCTION__);					

					// limit
					$lim = str_replace('LIMIT','',$sql_limit);
					$lim = explode(',',$lim);
					// init
					$temp_array = array();
					// loop
					for($i=trim($lim[0]); $i< ($lim[0]+$lim[1]); $i++ ){
						if(!empty($sql_order_by[$i]))
							$temp_array[] =$sql_order_by[$i];
					}
					$in_order = (count($temp_array)==0) ? 0 : (implode(',', $temp_array));
					// order
					if(!empty($temp_array)) {
			 			// set
						$sql_order = " ORDER BY FIELD( ID, {$in_order} ) ";
					}else {
						$sql_order ='';
					}
					// sql
					$sql = "SELECT * FROM `{$wpdb->users}` WHERE ID != 1 AND `ID` IN ({$in_order}) {$sql_order}";
					
					//mgm_log($sql,__FUNCTION__);
					//  list
					$custom_user_list = $wpdb->get_results($sql);
					
					unset($sql_order_by);	
					// flag
					$member_custom_sort = true;
					break;				
			}			
			// set
			if(isset($sql_order_by)) $sql_order = " ORDER BY {$sql_order_by} {$sort_type}";			
		}

		// issue #1119
		if(!isset($sql_order_by)) $sql_order = " ORDER BY ID desc";		
		
		// get members		
		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM `{$wpdb->users}` WHERE ID NOT IN ($super_admin_in) {$sql_filter} {$sql_order} {$sql_limit}";	
		//list		
		$user_list =   $wpdb->get_results($sql);
		// users
		if($member_custom_sort){
			// users
			$data['users'] = $custom_user_list;
		}else {
			// users
			$data['users'] = $user_list;
		}								
		// page url
		$data['page_url']   = 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=member_list';
		// get page links
		$data['page_links'] = $pager->get_pager_links($data['page_url']);	
		// total pages
		$data['page_count'] = $pager->get_page_count();
		// total rows/results
		$data['row_count']  = $pager->get_row_count();
		// search term
		$search_term = '';
		// search provided
		if( !empty($data['search_field_value']) ){			
			if(!empty($data['search_field_value_two'])){
				$search_term = sprintf('where <b>%s</b> between <b>%s</b> and <b>%s</b> dates', 				
				(isset($data['search_fields'][$search_field_name]) ? $data['search_fields'][$search_field_name] : ''), 
				$data['search_field_value'],$data['search_field_value_two']);
			}else {
				$search_term = sprintf('where <b>%s</b> is <b>%s</b>', (isset($data['search_fields'][$search_field_name]) ? $data['search_fields'][$search_field_name] : ''), $data['search_field_value']);
			}			
		}	
		// message
		$data['message'] = sprintf(__('%d %s matched %s','mgm'), $data['row_count'], ($data['row_count']>1 ? 'members' : 'member'), $search_term);	
		// modules
		$data['payment_modules'] = $system_obj->get_active_modules('payment');
		// load template view
		$this->load->template('members/member/lists', array('data'=>$data));
	}
	
	// members update
	function member_update(){				
		global $wpdb;
		// extract
		extract($_POST);
		//date format
		$format = mgm_get_date_format('date_format_short');
		// system
		$system_obj = mgm_get_class('system');		
		// save
		if(isset($update_member_info)){
			// update options
			$update_options = mgm_post_var('update_opt');		
			// success counter
			$success = 0;
			// init
			$post_users = '';		
			// selected
			if (isset($members) && !empty($members)) {
				// users
				$post_users = mgm_post_var('members');
			}
			// selected
			if(isset($ps_mem) && !empty($ps_mem)) {	
				foreach ($ps_mem as $uid =>$ot_mem) {
					$post_users[] = $uid;
				}
			}
			// unique
			if (!empty($post_users)) {
				$post_users = array_unique($post_users);
			}	
			// updated
			$updated_users = array();	
			// loop selected users
			foreach ($post_users as $k=>$user_id) {
				
				// count
				if(isset($_POST['ps_mem'][$user_id]) && !empty($_POST['ps_mem'][$user_id])){
					$ps_mem_count = count($_POST['ps_mem'][$user_id]);				
				}else {
					$ps_mem_count = 0; 
				}
				
				// updating the main subscription of a member
				if (isset($_POST['members']) && !empty($_POST['members']) && in_array($user_id, $_POST['members'])) {
					// member object					 
 					if( !$member = mgm_get_member($user_id) ) $member = new stdClass();	 								
				 	// check
					if(!$member) continue;
					// previous membership 
					$previous_membership = clone $member;
					// Unset unwanted fields if new assignment	
					if ('new' == mgm_post_var('insert_new_level')) {
						// Create member object afresh
						$member = new mgm_member();
						// Strip id and options from default fields
						unset($member->id,$member->options);
						
					}
					// IMP: maintain the update sequence
					
					// update membership_type
					if (in_array('membership_type', $update_options)) {	
						$member->membership_type =  mgm_post_var('upd_membership_type');
					}
					
					// update hide_old_content
					if (in_array('hide_old_content', $update_options)) {	
						$member->hide_old_content = mgm_post_var('upd_hide_old_content');
					}
								
					// update status		
					if (in_array('status', $update_options)) {	
						// process
						$return = $this->_member_update_status($member, $user_id, $system_obj);
						// make return local
						extract($return);
					}		
								
					// update expire_date
					if (in_array('expire_date', $update_options)) {	
						$this->_member_update_expire_date($member, $format);
					}						
					
					// update payment module info
					if (in_array('payment_module_info', $update_options)) {
						$this->_member_update_payment_module_info($member);					
					}				
					
					// update subscription, should be at last, it overwrites all of the above***
					if (in_array('subscription', $update_options)) {
						// process
						$return = $this->_member_update_subscription($member, $user_id, $system_obj, $previous_membership);
						// make return local
						extract($return);
					}
					
					// update end, save object				
					if($this->_save_main_member_object($user_id, $member, $previous_membership)){
						// track
						$success++;	
						// keep track of user for another membership update
						$updated_users[] = $user_id;
					}	
										
					// status change
					if(isset($new_status) && !empty($new_status)){
						do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'admin_update_member', $member->pack_id);
					}
					// reset object
					unset($member, $previous_membership);						
				}
				
				// updating the other subscription of a member
 				if($ps_mem_count >= 1) {
					// loop
 					for ($i=0; $i< $ps_mem_count; $i++ ) {
						// member object
						$member = mgm_get_member_another_purchase($user_id, $_POST['ps_mem'][$user_id][$i]);	 
						// init
	 					if(empty($member)) $member = new stdClass();	 		
					 	// check
						if(empty($member)) continue;
						// perv 
						$previous_membership = clone $member;
						// Unset unwanted fields if new assignment
 						if ('new' == mgm_post_var('insert_new_level')) {
 							// Create member object afresh
							$member = new mgm_member();
							// Strip id and options from default fields
							unset($member->id,$member->options);
						}
						// IMP: maintain the update sequence
						
						// update membership_type
						if (in_array('membership_type', $update_options)) {	
							$member->membership_type = mgm_post_var('upd_membership_type');
						}
						
						// update hide_old_content
						if (in_array('hide_old_content', $update_options)) {	
							$member->hide_old_content =  mgm_post_var('upd_hide_old_content');
						}
						
						// update status						
						if (in_array('status', $update_options)) {	
							// process
							$return = $this->_member_update_status($member, $user_id, $system_obj);
							// make return local
							extract($return);
						}						
						
						// update expire_date
						if (in_array('expire_date', $update_options)) {	
							$this->_member_update_expire_date($member, $format);
						}						

						// update payment module info
						if (in_array('payment_module_info', $update_options)) {
							$this->_member_update_payment_module_info($member);					
						}
												
						// update subscription, should be at last, it overwrites all of the above***
						if (in_array('subscription', $update_options)) {	
							// process
							$return = $this->_member_update_subscription($member, $user_id, $system_obj, $previous_membership);
							// make return local
							extract($return);
						}
						// update end, save object				
						if($this->_save_member_object($user_id, $member, $previous_membership)){
							// track
							$success++;	
							// keep track of user for another membership update
							$updated_users[] = $user_id;
						}
						
						// action
						if(isset($new_status) && !empty($new_status)){
							do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'admin_update_member', $member->pack_id);
						}				
						
						// reset object
						unset($member,$previous_membership);	
					}
 				} 				
			}		
			
			// saved
			if ($success) {
				$user_count = count(array_unique($updated_users));
				$message = sprintf(__('Successfully updated %d %s', 'mgm'), $user_count, ($user_count>1? 'members' : 'member'));
				$status  = 'success';
			}else{
				$message = __('Error while updating members', 'mgm');
				$status  = 'error';
			}
			// return response
			echo json_encode(array('status'=>$status, 'message'=>$message)); exit();
		}	
		// data
		$data = array();	
		// modules
		$data['payment_modules'] = $system_obj->get_active_modules('payment');
		// set
		$data['enable_multiple_level_purchase']	= bool_from_yn($system_obj->get_setting('enable_multiple_level_purchase')); 					
		// load template view
		$this->load->template('members/member/update', array('data'=>$data));	
	}
	
	// member update status
	function _member_update_status(&$member, $user_id, $system_obj){
		// return				
		$return = array('old_status' => $member->status);
		// set new status
		$member->status = $return['new_status'] = mgm_post_var('upd_status');
		// override_rebill_status_check
		$override_rebill_status_check = mgm_post_var('override_rebill_status_check');
		// disable payment/rebill status check
		if( bool_from_yn($override_rebill_status_check) ){
			$member->last_payment_check = 'disabled';
		}
		// active for manualpay
		if($member->status == MGM_STATUS_ACTIVE){
			// for manual pay
			if($member->payment_info->module == 'mgm_manualpay'){
				// MARK status reset for manual pay upgrade
				$member->status_reset_on = NULL;
				// unset
				unset($member->status_reset_on);
				// mark as paid
				$member->status_str = __('Last payment was successful','mgm');
				// send user notification: issue#: 537
				if($return['old_status'] == MGM_STATUS_PENDING) {
					
					// transaction status -issue #1287
					mgm_update_transaction_status($member->transaction_id, $member->status, $member->status_str);
					
					// userdata
					$userdata = get_userdata($user_id); 
					$blogname = get_option('blogname');
					
					//issue #1263
					if($member->subscribed == 'Y')
						do_action('mgm_return_subscription_payment', array('user_id' => $user_id, 'acknowledge_ar' => true, 'mgm_member' => $member)); 
					
					// subject
					$subject = $system_obj->get_template('payment_active_email_template_subject', array('blogname'=>$blogname), true);	
					// param
					$b_args = array('blogname'=>$blogname,'name'=>mgm_stripslashes_deep($userdata->display_name), 
									'email'=>$userdata->user_email,'admin_email'=>$system_obj->get_setting('admin_email'));			
					// body	
					$message = $system_obj->get_template('payment_active_email_template_body', $b_args, true);	
					//issue #862
					$subject = mgm_replace_email_tags($subject, $user_id) ;
					$message = mgm_replace_email_tags($message, $user_id) ;
					// mail																				
					mgm_mail($userdata->user_email, $subject, $message); //send an email to the buyer
					
					// send notification - issue #1758
					if($system_obj->setting['enable_new_user_email_notifiction_after_user_active'] == 'Y') {
						$user_pass = mgm_decrypt_password($member->user_password, $user_id);
						do_action('mgm_register_user_notification', $user_id, $user_pass);
					}					
					// unset
					unset($userdata,$message);															  
				}
			}else{
				// mark as paid
				$member->status_str = __('Last rebill cycle processed manually','mgm');
			}
		}
		
		// return
		return $return;
	}
	
	// member update expire date
	function _member_update_expire_date(&$member, $format){
		// lifetime
		if($member->duration_type != 'l') {
			//Issue # 704
			$member->expire_date = mgm_format_inputdate_to_mysql( mgm_post_var('upd_expire_date'), $format);
			// duration
			if (empty($member->duration)) {
				// split					
				list($expire_year, $expire_month, $expire_day) = explode('-', $member->expire_date);						
				// set
				$member->duration_type = 'd';
				// duration
				$member->duration =  ceil((mktime(0,0,0,$expire_month, $expire_day, $expire_year) - time()) / 86400);
			}
		}
	}
	
	// member update subscription
	function _member_update_subscription(&$member, $user_id, $system_obj, $previous_membership){
		// return
		$return = array();
		// getpack	
		$subs_pack   = mgm_decode_package(mgm_post_var('upd_subscription_pack'));
		$pack_obj    = mgm_get_class('subscription_packs');
		$packdetails = $pack_obj->get_pack($subs_pack['pack_id']);
		// if trial on		
		if (isset($subs_pack['trial_on'])) {
			$member->trial_on            = $subs_pack['trial_on'];
			$member->trial_cost          = $subs_pack['trial_cost'];
			$member->trial_duration      = $subs_pack['trial_duration'];
			$member->trial_duration_type = $subs_pack['trial_duration_type'];
			$member->trial_num_cycles    = $subs_pack['trial_num_cycles'];
		}
		// duration
		$member->duration          = $subs_pack['duration'];
		$member->duration_type     = strtolower($subs_pack['duration_type']);
		$member->active_num_cycles = $packdetails['num_cycles'];
		$member->amount            = $subs_pack['cost'];
		$member->currency          = $system_obj->setting['currency'];
		$member->membership_type   = $subs_pack['membership_type'];	
		$member->pack_id           = $subs_pack['pack_id'];	
		// old status
		$return['old_status']      = $member->status;
		// set new status
		$member->status            = $return['new_status'] = MGM_STATUS_ACTIVE;
		// set status str
		$member->status_str        = __('Subscription update successful','mgm');					
		// old type match and join date update
		// $old_membership_type = mgm_get_user_membership_type($user_id, 'code');
		// update if new subscription  OR guest user 
		/*
		$is_new_pack = (   strtolower($previous_membership->membership_type) == 'guest' 
		                || (mgm_post_var('insert_new_level') == 'new') 
						|| (isset($previous_membership->pack_id) && $previous_membership->pack_id != strtolower($member->pack_id)) 
						|| empty($member->join_date) 
					   );
		*/
		$is_new_pack = empty($member->join_date) || 'new' == mgm_post_var('insert_new_level') ;
		// if new subscription pack is assigned 
		// update join date
		if ($is_new_pack) {
			$member->join_date = time(); // type join date as different var						
		}
		// old content hide
		//$member->hide_old_content = $subs_pack['hide_old_content']; 
		//issue #1100
		$member->hide_old_content = $packdetails['hide_old_content']; 

		// time
		$time = time();	
		// last pay date
		if (!isset($member->last_pay_date))	$member->last_pay_date = date('Y-m-d', $time);	
		// expiration date
		if( !bool_from_yn(mgm_post_var('no_expiration_date_update', 'N')) ){		
			// expire					
			if ($member->expire_date && $member->last_pay_date != date('Y-m-d', $time)) {
				// expiry
				$expiry = strtotime($member->expire_date);
				// greater
				if ($expiry > 0) {
					// time check
					if ($expiry > $time) {
						// update
						$time = $expiry;
					}
				}
			}				
			// duration types expanded
			$duration_exprs = $pack_obj->get_duration_exprs();
			// time
			if(in_array($member->duration_type, array_keys($duration_exprs))) {// take only date exprs
				// time 
				$time = strtotime("+{$member->duration} {$duration_exprs[$member->duration_type]}", $time);							
				// formatted
				$time_str = date('Y-m-d', $time);				
				// date extended				
				if (!$member->expire_date || strtotime($time_str) > strtotime($member->expire_date) || mgm_post_var('insert_new_level') == 'new') {
					// This is to make sure that expire date is not copied from the selected members if any
					$member->expire_date = $time_str;										
				}
			}
			
			//date range - issue #1190
			if($member->duration_type == 'dr') {// el = /date range
				
				if(time() < strtotime($packdetails['duration_range_start_dt']) || time() > strtotime($packdetails['duration_range_end_dt'])){				
					$member->status = MGM_STATUS_EXPIRED;
				}	
						
				$member->expire_date = $packdetails['duration_range_end_dt'];
			}																		
		}		
		// if lifetime:
		if($subs_pack['duration_type'] == 'l' && $member->status == MGM_STATUS_ACTIVE) {
			$member->expire_date = '';
			if(isset($member->status_reset_on)) unset($member->status_reset_on);
			if(isset($member->status_reset_as))	unset($member->status_reset_as);	
		}
		
		// autoresponder - issue #1266
		if( !bool_from_yn(mgm_post_var('subscribe_to_autoresponder', 'N')) && isset($_POST['subscribe_to_autoresponder'])){
			// set
			$member->subscribed    = 'Y';
			$member->autoresponder = $system_obj->active_modules['autoresponder'];
			// call
			mgm_autoresponder_send_subscribe($user_id, $member);		
		}
		
		// return 
		return $return;
	}	
	
	// member update payment module info
	function _member_update_payment_module_info(&$member){
		// init
		$p_updated = false;	
		// module
		$module = mgm_get_module(mgm_post_var('payment_module')); 
		// prepare
		$p_updated = $module->update_tracking_fields($_POST, $member);	
	}	
	
	// members bulk update
	function member_bulk_update(){
		global $wpdb;
		// init
		$response = array('status'=>'error','message'=>__('Error!', 'mgm'));		
		// save
		if($action = mgm_post_var('_action')){
			// on action
			switch($action){
				case 'check_rebill_status':
					$response = $this->_members_check_rebill_status();
				break;
				case 'delete':
					// delete
					$response = $this->_delete_members();
				break;
			}
		}
		// return response
		echo json_encode($response);exit();
	}
	
	// members data export
	function member_export() {		
		global $wpdb;
		// error -- use WP_DEBUG with WP_DEBUG_LOG 
		// if(!WP_DEBUG) error_reporting(0);
		// extract
		extract($_POST);
		// log
		// mgm_log($_POST, __FUNCTION__);
		
		// get format	
		$sformat = mgm_get_date_format('date_format_short');	
		
		// process
		if(isset($export_member_info)){
			// init
			$success = 0;
			// type			
			$membership_type = (isset($bk_membership_type)) ? $bk_membership_type : 'all';			
			// status		
			$membership_status = (isset($bk_membership_status)) ? $bk_membership_status : 'all';			
			// date
			$date_start = (isset($bk_date_start)) ? $bk_date_start : '';	
			$date_end   = (isset($bk_date_end)) ? $bk_date_end : '';
			// query inut
			$query = '';
			// selected only
			if(isset($bk_only_selected)){
				// check
				if(isset($bk_selected_members) && is_array($bk_selected_members)){
					$query = " AND `id` IN(" . mgm_map_for_in($bk_selected_members) .")";
				}
			}
			
			// start date
			if($date_start){
				// Issue #700
				// convert to mysql date
				$date_start = strtotime(mgm_format_inputdate_to_mysql($date_start,$sformat));	
				// end date			
				if($date_end){		
					// Issue #700
					// convert to mysql date			
					$date_end = mgm_format_inputdate_to_mysql($date_end,$sformat);					
					$date_end = strtotime($date_end);
					// issue#" 492
					$query .= " AND UNIX_TIMESTAMP(user_registered) >= '{$date_start}' 
					            AND UNIX_TIMESTAMP(DATE_FORMAT(user_registered, '%Y-%m-%d')) <= '{$date_end}'";
				}else{
					$query .= " AND UNIX_TIMESTAMP(user_registered) >= '{$date_start}'";
				}
			}else if($date_end){
				// Issue #700
				// convert to mysql date
				$date_end = strtotime(mgm_format_inputdate_to_mysql($date_end,$sformat));
				// query
				$query .= " AND UNIX_TIMESTAMP(DATE_FORMAT(user_registered, '%Y-%m-%d')) <= '{$date_end}' ";
			}
			// all users	
			$sql = 'SELECT ID, user_login, user_email, user_registered, display_name FROM `' . $wpdb->users . '` 
			        WHERE ID <> 1 ' . $query . ' ORDER BY `user_registered` ASC';		
			// users
			$users = $wpdb->get_results($sql);			
			// filter
			$export_users = array();
			// date
			$current_date = time();	

			//issue #844 	
			$skip_fileds = array('subscription_introduction','coupon','privacy_policy','payment_gateways','terms_conditions',
								 'subscription_options','autoresponder','captcha');
			// check - issue #1382
			if(isset($bk_users_to_import)){	
				$custom_fields = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_register'=>true,'on_profile'=>true)));
				$import_user_fileds = array('user_login','user_email','pack_id','membership_type');
				foreach($custom_fields as $field){ 
					if(!in_array($field['name'],$skip_fileds))
						$import_user_fileds[]=$field['name'];
				}
			}			
			// Custom fields	
			$cf_profile_pg = mgm_get_class('member_custom_fields');
			$to_unserialize = array();	
			foreach (array_unique($cf_profile_pg->sort_orders) as $id) :
				foreach($cf_profile_pg->custom_fields as $field):
					// issue #954: show the field only if it is enabled for profile page
					if ($field['id'] == $id && $field['type'] == 'checkbox'):
						$to_unserialize[]= $field['name'];
					endif;
				endforeach;
			endforeach;					 
			// loop
			foreach ($users as $user) {
				// user cloned
				$user_obj = clone $user;
				
				// member
				$member = mgm_get_member($user->ID);	
				
				// check 
				if(!isset($bk_inactive)) $bk_inactive = false;
									
				// check search parameters:
				if($this->_get_membership_details($member, $bk_msexp_dur_unit, $bk_msexp_dur, $membership_type, $current_date, $bk_inactive, $membership_status )) {
					// merge 
					if(method_exists($member,'merge_fields')){					
						$user = $member->merge_fields($user);
					}		

					// log
					// mgm_log($user, __FUNCTION__);
			
					// issue #844 	
					foreach ($skip_fileds as $skip_filed){
						unset($user->$skip_filed);
					}

					// format dates
					$user->user_registered = date($sformat, strtotime($user->user_registered));	
					$user->last_pay_date   = (int)$user->last_pay_date>0 ? date($sformat, strtotime($user->last_pay_date)) : 'N/A';	
					$user->expire_date     = (!empty($user->expire_date)) ? date($sformat, strtotime($user->expire_date)) : 'N/A';		
					$user->join_date       = (int)$user->join_date>0 ? date($sformat, $user->join_date) : 'N/A';		
					
					// issue#: 672
					// DO not show actual password: #1002
					// $user->user_password = mgm_decrypt_password($member->user_password, $user->ID);					
					$user->rss_token   	   = $member->rss_token;	
					
					// unset password
					unset($user->password,$user->password_conf);
					
					// unserialize checkbox values
					if (count($to_unserialize)) {
						foreach( $to_unserialize as $chkname) {
							if (isset($user->{$chkname}) && !empty($user->{$chkname})) {
								$chk_val = @unserialize($user->{$chkname});
								if (is_array($chk_val)) {
									$user->{$chkname} = implode("|", $chk_val);
								}
							}
						}
					}
					// check - issue #1382
					if(isset($bk_users_to_import)){						
						$importuser = new stdClass();												
						foreach ($import_user_fileds as $import_user_filed){						
							if(isset($user->$import_user_filed)) 
								$importuser->$import_user_filed = $user->$import_user_filed;
							if($import_user_filed =='pack_id') 
								$importuser->$import_user_filed = $member->pack_id;
						}
						$export_users[] = $importuser;
						unset($importuser);					
					}else {
						$export_users[] = $user;
					}						
				}
				
				// consider multiple memberships as well:
				if(isset($member->other_membership_types) && is_array($member->other_membership_types) && count($member->other_membership_types) > 0) {
					// loop
					foreach ($member->other_membership_types as $key => $memtypes) {
						// types
						if(is_array($memtypes)) $memtypes = mgm_convert_array_to_memberobj($memtypes, $user->ID);
						// check search parameters:
						if($this->_get_membership_details($memtypes, $bk_msexp_dur_unit, $bk_msexp_dur, $membership_type, $current_date, $bk_inactive, $membership_status )) {
							// copy
							$user_mem = clone $user_obj;	
							// add custom fields as well:
							if(!empty($member->custom_fields)) {
								// loop
								foreach ($member->custom_fields as $index => $val) {
									// custom field
									if($index == 'birthdate' && !empty($val)) {
										// convert saved date to input field format
										$val = mgm_get_datepicker_format('date', $val);
									}
									// set
									$user_mem->{$index} = $val;
								}
							}
							
							// check types
							if( is_object($memtypes) && method_exists($memtypes,'merge_fields')){
							// merge		
								$user_mem = $memtypes->merge_fields($user_mem);	
							}else {
							// convert to array
								$data = mgm_object2array($memtypes);
								// check payment
								if(isset($memtypes->payment_info) && count($memtypes->payment_info) > 0) {
									// loop payments
									foreach ($memtypes->payment_info as $index => $val){
									// set
										$data['payment_info_' . $index] = str_replace('mgm_', '', $val);
									}	
								}
								// loop data
								foreach ($data as $index => $val) $user_mem->{$index} = $val;
							}

							//issue #844 	
							foreach ($skip_fileds as $skip_filed){
								unset($user->$skip_filed);
							}
							
							// format dates
							$user_mem->user_registered = date($sformat, strtotime($user_mem->user_registered));	
							$user_mem->last_pay_date   = (int)$memtypes->last_pay_date>0 ? date($sformat, strtotime($memtypes->last_pay_date)) : 'N/A';	
							$user_mem->expire_date     = (!empty($memtypes->expire_date)) ? date($sformat, strtotime($memtypes->expire_date)) : 'N/A';		
							$user_mem->join_date       = (int)$memtypes->join_date > 0 ? date($sformat, $memtypes->join_date) : 'N/A';		

							// check - issue #1382
							if(isset($bk_users_to_import)){						
								$importuser = new stdClass();												
								foreach ($import_user_fileds as $import_user_filed){						
									if($user_mem->$import_user_filed) 
										$importuser->$import_user_filed = $user_mem->$import_user_filed;
									if($import_user_filed =='pack_id') 
										$importuser->$import_user_filed = $memtypes->pack_id;
								}
								$export_users[] = $importuser;
								unset($importuser);				
							}else {
								$export_users[] = $user_mem;
							}	
							// unset 
							unset($user_mem);
						}
					}
				}				
				
			}// end for	
			
			mgm_log('export_users : '.mgm_array_dump($export_users,true));
	
			
			// default response
			$response = array('status'=>'error','message' => __('Error while exporting members. Could not find any member with requested search parameters.', 'mgm'));
			
			// check
			if (($expcount = count($export_users))>0) {
				// Issue #1559: standardization of Membership type
				for($k =0; $k < $expcount; $k++) {
					if (isset($export_users[$k]->membership_type)) {
						$export_users[$k]->membership_type = strtolower($export_users[$k]->membership_type);
					}
				}
				// success
				$success = count($export_users);
				// create
				if($bk_export_format == 'csv'){
					$filename= mgm_create_csv_file($export_users, 'export_users');			
				}else{
					$filename= mgm_create_xls_file($export_users, 'export_users');			
				}
				// src
				$file_src = MGM_FILES_EXPORT_URL . $filename;				
				// message
				$response['message'] = sprintf(__('Successfully exported %d %s.', 'mgm'), $success, ($success>1 ? 'users' : 'user'));
				$response['status']  = 'success';
				$response['src']     = $file_src;// for download iframe 
			}
			// return response
			echo json_encode($response); exit();
		}	
		
		// data
		$data = array();							
		// load template view
		$this->load->template('members/member/export', array('data'=>$data));	
	}
		
	// subscriptions -------------------------------------------------
	// subscription_options
	function subscription_options(){		
		// data
		$data = array();		
		// load template view
		$this->load->template('members/subscription/options', array('data'=>$data));	
	}
	
	// subscription_packages_list, 
	function subscription_packages_list(){
		// data
		$data = array();		
		// roles
		$wproles = new WP_Roles();
		$roles   = array_reverse($wproles->role_names);	
		$system_obj = mgm_get_class('system');
		$payment_modules = $system_obj->get_active_modules('payment');
		$currency = $system_obj->get_setting('currency');
		// check if any module supports trial setup i.e. paypal authorizenet
		$supports_trial = false;
		// log
		// mgm_log($payment_modules, __FUNCTION__);
		// loop
		foreach($payment_modules as $payment_module){
			// check
			if( $module_obj = mgm_is_valid_module($payment_module, 'payment', 'object') ){
				// check
				if( $module_obj->is_trial_supported() ){
				// set
					$supports_trial = true; break;
				}
			}
		}
		// membership_types
		$obj_mt = mgm_get_class('membership_types');
		$obj_sp = mgm_get_class('subscription_packs');
		// membership_types
		$membership_types = $obj_mt->get_membership_types();
		// subscription_packs
		$subscription_packs = $obj_sp->get_packs();		
		//remove
		$arr_packdta = $free_packs = array();					
		// packages
		foreach($membership_types as $type_code=>$type){				
			// package
			$membership_package = '';
			// pack data
			$pack_data = array();
			// roles
			$pack_data['roles'] = $roles;
			// supports_trial
			$pack_data['supports_trial'] = $supports_trial;
			// payment_modules
			$pack_data['payment_modules'] = $payment_modules;			
			// loop
			foreach($subscription_packs as  $i=>$pack){								
				// show when match type
				if ($pack['membership_type'] != $type_code) continue;
				
				// get free packs
				if ( $pack['membership_type'] == 'free' ) {
					$free_packs[$pack['id']] = true;
				}
				// remove
				$arr_packdta[] = $pack['membership_type'] ."[".$pack['id']."]". $pack['description']; 				
				// set
				$pack_data['pack_ctr'] = $i+1;				
				// default values
				$pack['num_cycles'] = (isset($pack['num_cycles'])) ? $pack['num_cycles'] : 1 ;
				$pack['role']       = (isset($pack['role'])) ? $pack['role'] : 'subscriber';
				$pack['default']    = (int)(isset($pack['default']) ? $pack['default'] : 0);
				// currency
				if(!isset($pack['currency'])) $pack['currency'] = $currency;
				// set pack
				$pack_data['pack'] = $pack;	
				// objects
				$pack_data['obj_sp'] = $obj_sp;	
				// packs
				$pack_data['packages'] = mgm_get_subscription_packages($obj_sp, NULL, array($pack['id']));
				// get html
				$membership_package .= ($this->load->template('members/subscription/package', array('data'=>$pack_data), true));
			}
			// get html
			$data['membership'][$type_code] = $membership_package;			
		}
		// membership types
		$data['membership_types'] = $membership_types;
		// active modules
		$data['payment_modules'] = $payment_modules;
		//check free module is enabled
		$data['free_module_enabled'] = in_array('mgm_free', $payment_modules) && (mgm_get_module('mgm_free')->is_enabled() ) ? 1 : 0;
		$data['enable_multiple_level_purchase'] = $system_obj->setting['enable_multiple_level_purchase'];
		$data['free_packs'] = $free_packs;
		
		// load template view
		$this->load->template('members/subscription/packages_list', array('data'=>$data));	
	}
	
	// subscription_package : single for new pack
	function subscription_package(){
		global $wpdb;	
		// extract
		extract($_POST);
		// roles
		$wproles = new WP_Roles();
		$roles   = array_reverse($wproles->role_names);	
		$payment_modules = mgm_get_class('system')->get_active_modules('payment');		
		// check if any module supports trial setup i.e. paypal authorizenet
		$supports_trial = false;
		foreach($payment_modules as $payment_module){
			if( mgm_get_module($payment_module,'payment')->is_trial_supported() ){
				$supports_trial = true;
				break;
			}
		}		
		// get object
		$obj_sp = mgm_get_class('subscription_packs');
		// create empty pack
		$new_pack = $obj_sp->add_pack($type);		
		// pack data
		$pack_data = array();
		// roles
		$pack_data['roles'] = $roles;
		// supports_trial
		$pack_data['supports_trial']  = $supports_trial;
		// payment_modules
		$pack_data['payment_modules'] = $payment_modules;		
		// set
		$pack_data['pack_ctr'] = key($new_pack);	
		// pack 
		$pack = current($new_pack);			
		// def values
		$pack['num_cycles'] = (isset($pack['num_cycles'])) ? $pack['num_cycles'] : 1 ;
		$pack['role']       = (isset($pack['role'])) ? $pack['role'] : 'subscriber';
		$pack['default']    = (int)(isset($pack['default']) ? $pack['default'] : 0);	
		// pack
		$pack_data['pack']  = $pack;		
		// objects
		$pack_data['obj_sp'] = $obj_sp;	
		// packs
		$pack_data['packages'] = mgm_get_subscription_packages($obj_sp, NULL, array($pack['id']));
						
		// load template view
		$this->load->template('members/subscription/package', array('data'=>$pack_data));	
	}	
	
	// subscription_packages_update
	function subscription_packages_update(){
		// get object
		$obj_sp = mgm_get_class('subscription_packs');
		// roles obj
		$obj_role = new mgm_roles();
		// init
		$_packs = array();
		// init
		$arr_new_role_users  = $arr_users_main_role = array();		
		// loop
		foreach($_POST['packs'] as $pack) {			
			// set modules
			if(!isset($pack['modules'])){
				$pack['modules'] = array();
			}			
			// check role changed:
			$prev_pack = $obj_sp->get_pack($pack['id']);
			// check
			if(isset($prev_pack['role']) && isset($pack['role']) && $prev_pack['role'] != $pack['role']) {
				// find users with the package:
				if(!isset($uid_all)) $uid_all = mgm_get_all_userids();
				// cap
				$arr_users = mgm_get_users_with_package($pack['id'], $uid_all); 	
				// check			
				if(!empty($arr_users)) {		
					// loop			
					foreach ($arr_users as $uid) {						
						//add role to old users
						$user = new WP_User($uid);	
						// check
						if(in_array($user->roles[0], array($prev_pack['role'])))
							$arr_users_main_role[$uid] = $pack['role'];
						else 
							$arr_users_main_role[$uid] = $user->roles[0];
						// add new role:
						$obj_role->add_user_role($uid, $pack['role'],false, false );
						$arr_new_role_users[] = $uid;	
					}
				}
			}
			// num_cycles
			$pack['num_cycles'] = (int)$pack['num_cycles'] ; 
			// limited flag
			if(isset($pack['num_cycles']) && $pack['num_cycles'] == 2){
				// set limited
				$pack['num_cycles'] = (int)$pack['num_cycles_limited'];
				// unset
				unset($pack['num_cycles_limited']);
			}
			// duration_type
			if($pack['duration_type'] == 'dr'){
				$pack['duration_range_start_dt'] = ((int)$pack['duration_range_start_dt'] > 0) ? date('Y-m-d', strtotime($pack['duration_range_start_dt'])) : '';
				$pack['duration_range_end_dt']   = ((int)$pack['duration_range_end_dt'] > 0) ? date('Y-m-d', strtotime($pack['duration_range_end_dt'])) : '';
			}else{
				$pack['duration_range_start_dt'] = $pack['duration_range_end_dt'] = '';
			}
			
			// unset for safety			
			if(empty($pack['duration_range_start_dt']))	unset($pack['duration_range_start_dt']);
			if(empty($pack['duration_range_end_dt'])) unset($pack['duration_range_end_dt']);			
			
			// switch back
			if($pack['duration_type'] == 'dr' && (!isset($pack['duration_range_start_dt']) && !isset($pack['duration_range_end_dt']))){
				$pack['duration_type'] = 'l'; // make it lifetime
			}
			
			// val int
			$pack['duration']   = (int)$pack['duration'] ;
			$pack['sort']       = (int)$pack['sort'] ;
			$pack['preference'] = (int)$pack['preference'] ;		
			
			// lifetime EL
			if(isset($pack['duration_type']) && $pack['duration_type'] == 'l')//lifetime:
				$pack['duration'] = $pack['num_cycles'] = 1;	
				
			// trial
			if(isset($pack['trial_on']) && (bool)$pack['trial_on'] == true){
				$pack['trial_num_cycles'] = (int)$pack['trial_num_cycles'] ;
				$pack['trial_duration']   = (int)$pack['trial_duration'] ;	
			}
			
			// update active on pages:
			foreach ($obj_sp->get_active_options() as $option => $val) {
				// check
				if(!isset($pack['active'][$option]) || empty($pack['active'][$option]) )
					$pack['active'][$option] = 0; 
			}						
			// set
			$_packs[] = $pack;
		}				
		// save
		$obj_sp->set_packs($_packs);
		// save to database
		$obj_sp->save();
		
		//remove excess roles from user if updated role	
		if(count($arr_new_role_users) > 0) {
			// users
			$arr_new_role_users = array_unique($arr_new_role_users);	
			// loop		
			foreach ($arr_new_role_users as $uid) {
				// move
				mgm_remove_excess_user_roles($uid);
				// highlight role:
				if(isset($arr_users_main_role[$uid])) {					
					$obj_role->highlight_role($uid, $arr_users_main_role[$uid]);					
				}
			}
		}
		// message
		$message = sprintf(__('Successfully updated %d subscription packages.', 'mgm'), count($_packs));
		$status  = 'success';
		// return response		
		echo json_encode(array('status'=>$status, 'message'=>$message));		
	}
	
	// subscription_package_delete
	function subscription_package_delete(){
		// extract
		extract($_POST);
		// get object
		$obj_sp = mgm_get_class('subscription_packs');		
		// empty
		$packs = array();
		// flag
		$deleted = false;
		//pack associated active users count
		$pack_active_users_count = mgm_get_members_with('pack_id', $id,array('status'=>MGM_STATUS_ACTIVE),'count');
		//check
		if($pack_active_users_count == 0) {		
			// loop
			foreach($obj_sp->packs as $i=>$pack){
				// match
				if(isset($pack['id'])){
				// new version 2.0 branch
					if($pack['id']==$id){
						$deleted = true;
						continue; 
					}	
				}else{
				// old 1.0 branch without pack_id	
					if($i==$index){
						$deleted = true;
						continue; 
					}	
				}			
				// filter				
				$packs[] = $pack;	
			}	
		}
		// update only when deleted
		if($deleted){
			// set 
			$obj_sp->set_packs($packs);		
			// update
			$obj_sp->save();
			// message
			$message = sprintf(__('Successfully deleted subscription package #%d.', 'mgm'), $id);
			$status  = 'success';
		}else{
			if($pack_active_users_count > 0) {
				$message = sprintf(__('There are active users and pack can not be deleted.', 'mgm'), $id);
				$status  = 'error';				
			}else {			
				$message = sprintf(__('Error while removing subscription package #%d. The package not found.', 'mgm'), $id);
				$status  = 'error';
			}
		}		
		
		// return response
		echo json_encode(array('status'=>$status, 'message'=>$message));exit();
	}
	
	// membership_types_list
	function membership_types_list(){
		// data
		$data = array();	
		// get obj
		// $data['mgm_membership_types'] = mgm_get_class('membership_types');	
		// set 
		$data['membership_types'] = mgm_get_all_membership_type();
		// init
		$data['membership_types_combo'] = array();
		// types
		foreach($data['membership_types'] as $membership_type):
			$data['membership_types_combo'][$membership_type['code']] = mgm_stripslashes_deep($membership_type['name']);
		endforeach;
		// load template view
		$this->load->template('members/membership/types_list', array('data'=>$data));	
	}
	
	// membership_type_update
	function membership_type_update(){
		global $wpdb;	
		extract($_POST);
		// init
		$message = $status = '';
		
		// new type -------------------------------------------------------------------
		if(isset($new_membership_type) && !empty($new_membership_type)){
			// new type
			$new_membership_type = trim($new_membership_type);
			// allowed only
			if(strtolower($new_membership_type) != 'none'){
				// set
				$membership_types_obj = mgm_get_class('membership_types');
				// set type, check duplicate
				$success = $membership_types_obj->set_membership_type($new_membership_type);				
				// update
				if($success){
					// add redirect url					
					$n_login_redirect_url  = (isset($new_login_redirect_url)) ? $new_login_redirect_url : '';
					$n_logout_redirect_url = (isset($new_logout_redirect_url)) ? $new_logout_redirect_url : '';
					$n_type_code           = $membership_types_obj->get_type_code($new_membership_type);
					// set url
					$membership_types_obj->set_login_redirect($n_type_code, $n_login_redirect_url);	
					$membership_types_obj->set_logout_redirect($n_type_code, $n_logout_redirect_url);				
					// update
					$membership_types_obj->save();
					// message
					$message = sprintf(__('Successfully created new membership type: %s.', 'mgm'), mgm_stripslashes_deep($new_membership_type));
					$status  = 'success';
				}else{
					$message = sprintf(__('Error while creating new membership type: %s. Duplicate type.', 'mgm'), mgm_stripslashes_deep($new_membership_type));
					$status  = 'error';
				}				
			}else{
				$message = sprintf(__('Error while creating new membership type: %s. Not allowed.', 'mgm'), mgm_stripslashes_deep($new_membership_type));
				$status  = 'error';
			}	
		}
		
		// delete/move account ------------------------------------------------------------
		if(isset($remove_membership_type) && count($remove_membership_type)>0){			
			// get object
			$membership_types_obj = mgm_get_class('membership_types');
			// users 
			$users = mgm_get_all_userids(array('ID'), 'get_results');
			// how many removed
			$removed = 0;			
			// loop			
			foreach($remove_membership_type as $type_code){				
				// unset
				$membership_types_obj->unset_membership_type($type_code);							
				// move
				if(isset($move_membership_type_to[$type_code]) && $move_membership_type_to[$type_code] != 'none'){
					// loop
					foreach($users as $user){
						// get
						$member = mgm_get_member($user->ID);
						// if users with same membershiptype as that of selected
						if($member->membership_type == $type_code) {							
							// set
							$member->membership_type = $move_membership_type_to[$type_code];
							// save
							$member->save();						
						}else {							
							// check if any multiple levels exist:
							if(isset($member->other_membership_types) && is_array($member->other_membership_types) && count($member->other_membership_types) > 0) {
								// loop
								foreach ($member->other_membership_types as $key => $memtypes) {
									// make sure its an object:
									$memtypes = mgm_convert_array_to_memberobj($memtypes, $user->ID);
									// verify
									if($memtypes->membership_type == $type_code) {
										// set
										$memtypes->membership_type = $move_membership_type_to[$type_code];	
										// save
										mgm_save_another_membership_fields($memtypes, $user->ID, $key); break;
									}
								}
							}
						}
						// unset
						unset($member);
					}
				}
			
				// remove packs				
				$subscription_packs = mgm_get_class('subscription_packs');				
				// empty
				$packs = array();
				// set
				foreach( $subscription_packs->packs as $i=>$pack){
					// if membership_type is same as being deleted
					if($pack['membership_type'] == $type_code) {						
						continue; // skip
					}					
					// filtered
					$packs[] = $pack;	
				}	
				// set 				
				$subscription_packs->set_packs($packs);		
				// update
				$subscription_packs->save();					
				// removed
				$removed++;
			}			
			// ends remove pack:
			
			// save		
			$membership_types_obj->save();			
			// message
			$message .= ((!empty($message)) ? '<br>' : '' ) . sprintf(__('Successfully removed %d membership type(s).', 'mgm'), $removed);
			// set status
			$status  = 'success';
		}
		
		// update name/redirects ------------------------------------------------------------------------
						
		// get object
		$membership_types_obj = mgm_get_class('membership_types');
		
		// ge all users
		$users = mgm_get_all_userids(array('ID'), 'get_results');

		// init
		$updated = 0;
		// loop types
		foreach($membership_types_obj->get_membership_types() as $type_code=>$type_name){
			// skip new type, in edit otherwise overwritten
			if(isset($n_type_code) && !empty($n_type_code) && $n_type_code == $type_code) continue;
				
			// urls
			$_login_redirect_url  = (isset($login_redirect_url[$type_code])) ? $login_redirect_url[$type_code] : '';
			$_logout_redirect_url = (isset($logout_redirect_url[$type_code])) ? $logout_redirect_url[$type_code] : '';
			// set urls
			$membership_types_obj->set_login_redirect($type_code, $_login_redirect_url);
			$membership_types_obj->set_logout_redirect($type_code, $_logout_redirect_url);		
			
			// set name	
			if(isset($membership_type_names[$type_code]) && !empty($membership_type_names[$type_code]) && $membership_type_names[$type_code] != $type_name){
				
				//issue #1127
				$new_type_code = $membership_types_obj->get_type_code($membership_type_names[$type_code]);
				//check
				if($new_type_code != $type_code) {					
					// get object
					$obj_sp = mgm_get_class('subscription_packs');
					//update new 
					foreach ($obj_sp->packs as $key => $pack) {	
						
						if($obj_sp->packs[$key]['membership_type'] == $type_code ){
							
							$obj_sp->packs[$key]['membership_type'] = $new_type_code;
							
							$obj_sp->save();
						}					
					}
					
					// loop
					foreach($users as $user){
						// get
						$member = mgm_get_member($user->ID);
						// if users with same membershiptype as that of selected
						if(isset($member->membership_type) && $member->membership_type == $type_code) {							
							// set
							$member->membership_type = $new_type_code;
							// save
							$member->save();						
						}
						// check if any multiple levels exist:
						if(isset($member->other_membership_types) && is_array($member->other_membership_types) && count($member->other_membership_types) > 0) {								
							// loop
							foreach ($member->other_membership_types as $key => $memtypes) {
								// make sure its an object:
								$memtypes = mgm_convert_array_to_memberobj($memtypes, $user->ID);
								// verify
								if($memtypes->membership_type == $type_code) {
									// set
									$memtypes->membership_type = $new_type_code;	
									// save
									mgm_save_another_membership_fields($memtypes, $user->ID, $key);
								}
							}
						}
						// unset
						unset($member);
					}							
					
					//issue #1336
					$membership_posts = mgm_get_posts_for_level($type_code);
										
					if(isset($membership_posts['total']) && $membership_posts['total'] > 0) {		
					
						foreach ($membership_posts['posts'] as $id=>$obj) {
						
							$post_id = $obj->ID;
							// get object
							$post_obj = mgm_get_post($post_id);
							
							// if access set
							if(is_array($post_obj->access_membership_types)){	
							
								$access_membership_types = $post_obj->access_membership_types;
								
								foreach ($post_obj->access_membership_types as $key=>$access_membership_type) {
									
									if($access_membership_type == $type_code){
										//update rename
										$access_membership_types[$key]=$new_type_code;	
										// set
										$post_obj->access_membership_types = $access_membership_types;
									}
								}

							}
							
							// if access delay set
							if(is_array($post_obj->access_delay)){	
							
								$access_delay = $post_obj->access_delay;
								
								if(isset($access_delay[$type_code])) {
									
									$access_delay[$new_type_code] = $access_delay[$type_code];
									
									unset($access_delay[$type_code]);
									
									$post_obj->access_delay = $access_delay;												
								}
							}							
							// apply filter
							$post_obj = apply_filters('mgm_post_update', $post_obj, $post_id);
							// save meta						
							$post_obj->save();							
							// unset
							unset($post_obj);
						}
					}					
					
					//unset 
					$membership_types_obj->unset_membership_type($type_code);
					// set
					$membership_types_obj->set_name($membership_type_names[$type_code], $new_type_code);
				}else {
					// set
					$membership_types_obj->set_name($membership_type_names[$type_code], $type_code);				
				}
			}	
			
			// update
			$updated++;		
		}
		// update
		$membership_types_obj->save();		
		
		// notify
		if(empty($message)){
			// message
			$message = sprintf(__('Successfully updated %d membership type(s).', 'mgm'), $updated);
			// set status
			$status  = 'success';
		}	
		
		// return response		
		echo json_encode(array('status'=>$status, 'message'=>$message));exit();
	}		
	
	// roles & 	roles_capabilities-------------------------------------------------------------------------
	// roles/roles_capabilities tab	
	function roles_capabilities(){	
		global $wpdb;	
		// data
		$data = array();			
		// load template view
		$this->load->template('members/roles_capabilities/index', array('data'=>$data));	
	}
	// roles/roles_capabilities listing
	function roles_capabilities_list() {				
		global $current_user;
		$objrole = new mgm_roles();
		$data['roles'] = $objrole->get_roles();	
		$data['admin_role'] = $objrole->admin_role;	
		$data['role_type'] 	= $objrole->role_type;	
		$data['mgm_cap_hierarchy'] = $objrole->get_custom_capability_hierarchy();			
		// load template view
		$this->load->template('members/roles_capabilities/list', array('data'=>$data));	
	}
	
	// roles/roles_capabilities listing
	function roles_capabilities_list_others() {				
		global $current_user;
		$objrole = new mgm_roles();
		$objrole->role_type = 'others';			
		$data['roles'] 			= $objrole->get_roles();	
		$data['admin_role'] 	= $objrole->admin_role;	
		$data['role_type'] 		= $objrole->role_type;	
		foreach ($objrole->default_roles as $default)
			$arr_default[] = array('role' => $default, 'name' => ucfirst($default)); 	
		$data['default_roles'] 	= $arr_default; 
		$data['mgm_cap_hierarchy'] = $objrole->get_custom_capability_hierarchy();		
		// load template view
		$this->load->template('members/roles_capabilities/list', array('data'=>$data));	
	}
	// roles/roles_capabilities listing
	function roles_capabilities_list_default() {				
		global $current_user;
		$objrole = new mgm_roles();
		$objrole->role_type = 'default';		
		$data['roles'] 			= $objrole->get_roles();	
		$data['admin_role'] 	= $objrole->admin_role;	
		$data['role_type'] 		= $objrole->role_type;	
		foreach ($objrole->default_roles as $default)
			$arr_default[] = array('role' => $default, 'name' => ucfirst($default)); 	
		$data['default_roles'] 	= $arr_default; 
		$data['mgm_cap_hierarchy'] = $objrole->get_custom_capability_hierarchy();		
		// load template view
		$this->load->template('members/roles_capabilities/list', array('data'=>$data));	
	}
	//edit roles:
	function roles_capabilities_edit() {	
		global $wpdb, $current_user;		
		if(isset($_POST['save_roles'])) {			
			$objrole = new mgm_roles();
			extract($_POST);			
			$status = 'error';
			$role_type = '';
			$message = array();	
			if(!empty($rolename)) {
				$error = false;
				foreach ($rolename as $role => $value) {
					//added later to consider only the edited role:
					if($role == $selected_role) {
						$value =  trim(mgm_escape($value));
						if(empty($value)) {
							$message[] = __('Role cannot be blank','mgm');
							$error = true;
						}elseif(!preg_match("/^[A-Za-z0-9_,\s]+$/", $value)) {
							$message[] = __('Role cannot contain special characters.','mgm');
							$error = true;
						}elseif (!$objrole->is_role_unique($value, true, $role)) {
							$message[] = __('Role/capability already exists.','mgm');
							$error = true;
						}
						if(!isset($chk_capability[$role]) || (isset($chk_capability[$role]) && empty($chk_capability[$role]))) {
							$message[] = __('Capability must be selected','mgm');
							$error = true;
						}
						break;	
					}
				}
				if(!$error) {
					//save roles:										
					foreach ($rolename as $role => $value) {
						if($role == $selected_role) {
							$key = $role;
							//save Role name:
							if(!in_array($role, $objrole->default_roles)) {
								//please note: this will return the edited role
								$role = $objrole->edit_role($role, $value);
							}							
							//remove							
							if(!empty($chk_capability[$key])) {
								//save capabilities:
								$arr_previous_caps = $objrole->get_capabilities($role);	
								$arr_new_caps = $chk_capability[$key];
								
								$arr_to_add 	= array_diff($arr_new_caps, $arr_previous_caps);
								$arr_to_remove 	= array_diff($arr_previous_caps, $arr_new_caps);
								
								//add new capabilities:
								if(!empty($arr_to_add)) {
									foreach ($arr_to_add as $cap) {
										$cap =  mgm_escape($cap);
										//grant access
										$objrole->update_capability_role($role, $cap, true);
									}
								}
								
								//remove access if any capabilities unchecked
								if(!empty($arr_to_remove)) {
									foreach ($arr_to_remove as $cap) {
										$cap =  mgm_escape($cap);
										//remove access
										$objrole->update_capability_role($role, $cap, false);
									}
								}
							}
							break;
						}
					}
					$type = $role_type;//from post
					$message[] = __('Successfully saved the changes.','mgm');
					$status = 'success';	
				}
			}					
			echo json_encode(array('status'=>$status, 'message'=>implode("<br/>",$message),'type' => $type));
			exit();
		}		
	}
	//create new role
	function roles_capabilities_add() {
		global $wpdb, $current_user;
		
		$objrole = new mgm_roles();
		$data = array();		
		$arr_caps = $objrole->get_mgm_default_capabilities();
		foreach ($arr_caps as $key => $c)
			$arr_caps[$key] 	= array('capability' => $c, 'name' => ucfirst(str_replace('_', ' ', $c)) );					
		$data['capabilities'] = $arr_caps; 
		$data['mgm_cap_hierarchy'] = $objrole->get_custom_capability_hierarchy();
		if(isset($_POST['add_roles'])) {
			extract($_POST);			
			$status = 'error';	
			$error 	= false;
			$rolename =  trim(mgm_escape($rolename));
			if(empty($rolename)) {
				$message[] = __('Role cannot be blank.','mgm');
				$error = true;
			}elseif(!preg_match("/^[A-Za-z0-9_,\s]+$/", $rolename)) {
				$data['rolename'] = $rolename; 
				$message[] = __('Role cannot contain special characters.','mgm');
				$error = true;
			}elseif (!$objrole->is_role_unique($rolename)) {
				$data['rolename'] = $rolename; 
				$message[] = __('Role/capability already exists.','mgm');
				$error = true;
			}
			if(!isset($chk_capability) || (isset($chk_capability) && empty($chk_capability))) {
				$message[] = __('Capability must be selected.','mgm');
				$error = true;
			}else 
				$data['chk_capability'] = $chk_capability;					
			
			if(!$error) {
				//save roles:				
				if(!in_array($rolename, $objrole->default_roles) && !empty($chk_capability)) {					
					if($objrole->add_role($rolename, $chk_capability)) {
						$message[] = __('Successfully added the new role.','mgm');
						$status = 'success';
					}else {
						$message[] = __('Error in creating role.','mgm');
					}					
				}
			}
			
			echo json_encode(array('status'=>$status, 'message'=>implode("<br/>",$message)));
				
			exit();
		}
		$this->load->template('members/roles_capabilities/add', array('data'=>$data));	
	}
	//delete capabilities
	function roles_capabilities_delete() {
		global $wpdb, $current_user, $wp_roles;
		
		$objrole = new mgm_roles();
		$status = '';
		$message = array();
		extract($_POST);		
		if(isset($role)) {
			$role = mgm_escape($role);
			$new_role = mgm_escape($new_role);
			if($wp_roles->is_role($role)) {				
				if($objrole->remove_role($role, $new_role)) {
					$message[] = __('Successfully deleted the role.','mgm');
					$status = 'success';
				}else {
					$message[] = __('Error in deleting the role.','mgm');
					$status = 'failure';
				}
			}
		}
		
		echo json_encode(array('status'=>$status, 'message'=>implode("<br/>",$message)));
		exit();
	}
	//Move role's users
	 function roles_capabilities_move_users() {
	 	global $wpdb, $current_user, $wp_roles;
		
		$objrole = new mgm_roles();
		$status = '';
		$message = array();
		extract($_POST);		
		if(isset($role)) {
			$role = mgm_escape($role);
			$new_role = mgm_escape($new_role);
			if($wp_roles->is_role($role)) {				
				if($objrole->move_users($role, $new_role)) {				
					$message[] = __('Successfully moved the users.','mgm');
					$status = 'success';
				}else {
					$message[] = __('Error in moving the role.','mgm');
					$status = 'failure';
				}
			}
		}		
		echo json_encode(array('status'=>$status, 'message'=>implode("<br/>",$message)));
		exit();
	 }
	 
	 // private --------------------------------------------
	 // get member object selectively
	 private function _get_member_object($user_id) {	 	
	 	if(isset($_POST['ps_mem'][$user_id]) && !empty($_POST['ps_mem'][$user_id])) {
	 		$member = mgm_get_member_another_purchase($user_id, $_POST['ps_mem'][$user_id][0]);	 
	 		if(empty($member))
	 			return new stdClass();	 		
	 	}else 
	 		$member = mgm_get_member($user_id);
	 		
	 	return $member;
	 }

	 // save main subscription of a member
	 private function _save_main_member_object($user_id, $member, $previous_membership) {
	 	// get pack
	 	$pack = mgm_get_class('subscription_packs')->get_pack($member->pack_id);
		// multiple_level_purchase
		$multiple_level_purchase = bool_from_yn(mgm_get_class('system')->get_setting('enable_multiple_level_purchase'));
		// update options
		$update_options = mgm_post_var('update_opt');	
		// new level
	 	if(in_array('subscription', $update_options) && mgm_post_var('insert_new_level') == 'new') {	
			// save flag
			$save = true; 	
			// guest	 		
	 		if($previous_membership->membership_type == 'guest' && $previous_membership->amount == 0) {
	 			// check selected membership already selected, do not save
	 			if($previous_membership->membership_type == $member->membership_type){ 
					$save = false;
				}						 			
	 		}
			// save	
			if($save){	
				// multiple
				if($multiple_level_purchase){	
					mgm_save_another_membership_fields($member, $user_id);	
				}else{
					$member->save(); 		
				}
			}
	 		// assign role:
	 		$change_order = (isset($_POST['highlight_role']) && (isset($_POST['upd_subscription_pack'])) && $_POST['upd_subscription_pack'] != '-') ? true : false;
			// set
			$obj_role = new mgm_roles();				
			$obj_role->add_user_role($user_id, $pack['role'], $change_order);								
	 	}else {
			// save
			$member->save();
			// status
			if($member->status == MGM_STATUS_EXPIRED) {
		 		// remove role from user:
				mgm_remove_userroles($user_id, $member);
		 	}else {		 		
		 		// mgm role object:
		 		$change_order = (isset($_POST['highlight_role']) && (isset($_POST['upd_subscription_pack'])) && $_POST['upd_subscription_pack'] != '-') ? true : false;
				$obj_role = new mgm_roles();
		 		//update role/change order		 						
				$obj_role->add_user_role($user_id, $pack['role'], $change_order);
		 	}
	 	}
		// return
	 	return true;
	 }	 
	 
	 // save member object
	 private function _save_member_object($user_id, $member, $previous_membership) {
	 	// pack
	 	$pack = mgm_get_class('subscription_packs')->get_pack($member->pack_id);
		// multiple_level_purchase
		$multiple_level_purchase = bool_from_yn(mgm_get_class('system')->get_setting('enable_multiple_level_purchase'));
		// update options
		$update_options = mgm_post_var('update_opt');			
	 	// new level
	 	if(in_array('subscription', $update_options) && mgm_post_var('insert_new_level') == 'new') {	
			// save flag
			$save = true; 			 		
			// guest	
	 		if($previous_membership->membership_type == "guest" && $previous_membership->amount == 0) {
	 			// check selected membership already selected:
	 			if($previous_membership->membership_type == $member->membership_type){
	 				$save = false;
				}	 			
	 		}else {
				// old
	 			$old_subtypes = mgm_get_subscribed_membershiptypes($user_id);
	 			//check selected membership already selected:
	 			if(in_array($member->membership_type, $old_subtypes )){
	 				$save = false;	 	
	 			}else{	
					if(isset($member->custom_fields)) unset($member->custom_fields);
					if(isset($member->other_membership_types) || empty($member->other_membership_types)) {	 				
						unset($member->other_membership_types);	
					}
				}	 							 			
	 		}
			
			// save	
			if($save){	
				// multiple
				if($multiple_level_purchase){	
					mgm_save_another_membership_fields($member, $user_id);	
				}else{
					$member->save(); 		
				}
			}
			
	 		// assign role:
	 		$change_order = (isset($_POST['highlight_role']) && (isset($_POST['upd_subscription_pack'])) && $_POST['upd_subscription_pack'] != '-') ? true : false;
	 		// set
			$obj_role = new mgm_roles();				
			$obj_role->add_user_role($user_id, $pack['role'], $change_order);								
			
	 	}else {
		 	if(isset($_POST['ps_mem'][$user_id]) && !empty($_POST['ps_mem'][$user_id])) {
		 		
		 		if(isset($member->custom_fields))
	 				unset($member->custom_fields);
	 			if(isset($member->other_membership_types) || empty($member->other_membership_types)) {	 				
	 				unset($member->other_membership_types);	
	 			}
	 			
	 			$prev_index = (isset($_POST['ps_mem_index'][$user_id][$previous_membership->membership_type])) ? $_POST['ps_mem_index'][$user_id][$previous_membership->membership_type] : null;	 			
	 			
	 			//uncomment
		 		mgm_save_another_membership_fields($member, $user_id, $prev_index );
		 	}else {		 		
				$member->save();
		 	}		 	
		 	if($member->status == MGM_STATUS_EXPIRED) {
		 		//remove role from user:
				mgm_remove_userroles($user_id, $member);
		 	}else {		 		
			 	//if($member->membership_type != $previous_membership->membership_type) {//check this condition
			 		//mgm role object:
			 		$change_order = (isset($_POST['highlight_role']) && (isset($_POST['upd_subscription_pack'])) && $_POST['upd_subscription_pack'] != '-') ? true : false;
				 	
					$obj_role = new mgm_roles();
			 		//update role/change order		 						
					$obj_role->add_user_role($user_id, $pack['role'], $change_order);
			 	//}
		 	}
	 	}
	 	 	
	 	return true;
	 }
	 
	 // get details
	private function _get_membership_details($member, $bk_msexp_dur_unit, $bk_msexp_dur, $membership_type, $current_date, $bk_inactive, $membership_status) {
		
		if($bk_msexp_dur_unit && $bk_msexp_dur){
			// expire					
			$expire_date = $member->expire_date;				
			$date_diff   = strtotime($expire_date) - $current_date;				
			$days        = floor($date_diff/(60*60*24));
			// days
			switch($bk_msexp_dur){
				case 'month':
					$bkmsexp_days = ($bk_msexp_dur_unit*30);
				break;
				case 'week':
					$bkmsexp_days = ($bk_msexp_dur_unit*7);
				break;
				case 'day':
					$bkmsexp_days = $bk_msexp_dur_unit;
				break;
			}					
			// skip if range matches
			if(($days <= 0) || $days > $bkmsexp_days){
				return false;
			}						
		}// end expire
		// membership_type - issue #1431
		if ($membership_type != 'all' ){
			//issue #1617
			if(strtolower(trim($member->membership_type)) != strtolower(trim($membership_type))  || is_super_admin( $member->id )){
				return false;
			}
		}
		
		// membership_status
		if ($membership_status != 'all' ){
			if($member->status != $membership_status){
				return false;
			}
		}
		
		/// excluding expired and guest users
		if (isset($bk_inactive) && $bk_inactive==true) {			
			$expire_date = $member->expire_date;	
			if($expire_date != '') {
				$date_diff   = strtotime($expire_date) - $current_date;	
				if($date_diff <= 0) {
					return false;
				}
			}
			$membership_type = $member->membership_type;
			//issue #1617
			if(strtolower(trim($membership_type)) == 'guest') {
				return false;
			}
			
		}		
		//OK
		return true;	
	}
	
	// delete members
	private function _delete_members(){
		global $current_user,$wpdb;
		extract($_POST);
		// init
		$message = __('Member delete failed', 'mgm');
		$status  = 'error';
		// check
		if(isset($current_user->ID) && $current_user->ID != 0 && is_numeric($current_user->ID) ) {			
			// check permission			
			if(!current_user_can( 'delete_users' )) {
				$message = __('You can&#8217;t delete users.', 'mgm');
			}else {						
				//Issue #775
				if(isset($ps_mem) && !empty($ps_mem)) {	
					$membership_level_deleted = 0;
					foreach ($ps_mem as $uid =>$ot_mem) {	
						// check
						if((int)($uid)>0) {	
							// get user							
							$user = new WP_User($uid);	
							// check						
							if(isset($user->ID) && $user->ID != 0 && (int)$user->ID>0 ) {	
								$uid = mgm_escape($uid);
								$user = new WP_User($uid);	
								$member = mgm_get_member($user->ID);
								for($i=0; $i<count($ot_mem);$i++){
									foreach ($member->other_membership_types as $key =>$other_membership_types) {
										//issue #1027
										if(!empty($other_membership_types))
											extract($other_membership_types);
										//mgm_log('key '.$key.' membership_type : '.$membership_type);	
										if($membership_type == $ot_mem[$i]){
											//mgm_log(mgm_array_dump($member->other_membership_types[$key],true));	
											$member->other_membership_types[$key]=null;
											$member->save();
											$membership_level_deleted ++;
										}	
									}
								}
							}
						}
					}
				}

				// users check		
				if(isset($members) && !empty($members)) {	
					// ctr					
					$deleted = 0;
					// loop users
					foreach ($members as $uid) {							
						//delete user
						$uid = mgm_escape($uid);
						// check
						if((int)($uid)>0) {	
							// get user							
							$user = new WP_User($uid);		
							// check						
							if(isset($user->ID) && $user->ID != 0 && (int)$user->ID>0 ) {	
								// permission								
								if(current_user_can('delete_user', $user->ID) && $current_user->ID != $user->ID ){
									// multisite
									if ( is_multisite() ) {
										if(wpmu_delete_user($user->ID)) $deleted++;	
									}else{
									// general
										if(wp_delete_user($user->ID)) $deleted++;
									}	
									// delete transactions
									// mgm_delete_user_transactions($user->ID);	// @todo test before release								 
								}	
							}
							// unset
							unset($user);								
						}
					}
					
					// message
					$item = ($deleted > 1) ? 'members' : 'member';
					// set
					if(!$deleted) {
						$message = sprintf(__('Error while deleting %s.', 'mgm'), $item);
					}elseif ($deleted && count($members) != $deleted) {
						$message = sprintf(__('Partially deleted %d %s.', 'mgm'), $deleted, $item);
					}else {
						$message = sprintf(__('Successfully deleted %d %s.', 'mgm'), $deleted, $item);
						$status  = 'success';
					}
				}
				//Issue #775
				if(!isset($members) && isset($ps_mem) && !empty($ps_mem)){	
					// message
					$item = ($membership_level_deleted > 1) ? 'membership levels' : 'membership level';
					$message = sprintf(__('Successfully deleted %d %s.', 'mgm'), $membership_level_deleted, $item);
					$status  = 'success';
				}
				
			}			
		}
		// return 
		return array('status'=>$status, 'message'=>$message);
	}
	
	// check members rebill status
	private function _members_check_rebill_status(){
		global $current_user,$wpdb;
		// extract
		extract($_POST);
		// init
		$message = __('Member rebill status check failed', 'mgm');
		$status  = 'error';
		// check
		if(isset($current_user->ID) && $current_user->ID != 0 && is_numeric($current_user->ID) ) {	
			// users check		
			if(isset($members) && !empty($members)) {	
				// define
				if(!defined('DOING_QUERY_REBILL_STATUS')) define('DOING_QUERY_REBILL_STATUS', 'manual');
				// ctr					
				$updated = 0;
				// loop users
				foreach ($members as $uid) {							
					//delete user
					$uid = mgm_escape($uid);
					// check
					if((int)($uid)>0) {	
						// get user							
						$user = new WP_User($uid);		
						// check						
						if(isset($user->ID) && (int)$user->ID > 0 ) {	
							// member data								
							$member = mgm_get_member($user->ID);	
							// reset disabled, force recheck
							$member->last_payment_check = '';							
							// check 
							if(apply_filters('mgm_module_rebill_status', $user->ID, $member)){								
								// update
								$updated++;	
							}							
							// update
							mgm_update_payment_check_state($user->ID, 'manual');
						}
						// unset
						unset($user, $member);								
					}
				}
				
				// message
				$item = ($updated > 1) ? 'members' : 'member';
				// set
				if(!$updated) {
					$message = sprintf(__('Error while updating %s rebill status.', 'mgm'), $item);
				}elseif ($updated && count($members) != $updated) {
					$message = sprintf(__('Partially updated rebill status of %d %s .', 'mgm'), $updated, $item);
				}else {
					$message = sprintf(__('Successfully updated rebill status of %d %s.', 'mgm'), $updated, $item);
					$status  = 'success';
				}
			}						
		}
		// return 
		return array('status'=>$status, 'message'=>$message);
	}
	/**
	 * patch member options for users
	 */	
/*	
	// New version of member export - (Due to performance issue this is implemented), under testing process in customer site http://www.pathoma.com/.
	//This code is supporting nearly 40K + users export at a time
	function mgm_patch_partial_user_member_export($start, $limit, $cond) {
		global $wpdb;
		// all users	
		$sql = 'SELECT u.ID, u.user_login, u.user_email, u.user_registered, u.display_name,um.meta_value FROM ' . $wpdb->users . ' u JOIN '.$wpdb->usermeta.' um';			
		$sql .= ' ON u.ID=um.user_id WHERE u.ID <> 1 ' . $cond .' ORDER BY u.ID LIMIT '. $start.','.$limit;		
		//mgm_log($sql,__FUNCTION__);
		// users
		return $wpdb->get_results($sql);
	}	

	// members data export

	function member_export() {		
		//increased memory limit
		@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', '2048M' ) );
		@set_time_limit(0);		
		
		global $wpdb;
		extract($_POST);
		// get format	
		$sformat = mgm_get_date_format('date_format_short');	
		// process
		if(isset($export_member_info)){
			// init
			$success = 0;
			// type			
			$membership_type = (isset($bk_membership_type)) ? $bk_membership_type : 'all';			
			// status		
			$membership_status = (isset($bk_membership_status)) ? $bk_membership_status : 'all';			
			// date
			$date_start = (isset($bk_date_start)) ? $bk_date_start : '';	
			$date_end   = (isset($bk_date_end)) ? $bk_date_end : '';
			// query inut
			$query = '';
			// selected only
			if(isset($bk_only_selected)){
				// check
				if(isset($bk_selected_members) && is_array($bk_selected_members)){
					$query = " AND `u.id` IN(" . mgm_map_for_in($bk_selected_members) .")";
				}
			}
			// start date
			if($date_start){
				// convert to mysql date
				$date_start = strtotime(mgm_format_inputdate_to_mysql($date_start,$sformat));	
				// end date			
				if($date_end){		
					// convert to mysql date			
					$date_end = mgm_format_inputdate_to_mysql($date_end,$sformat);					
					$date_end = strtotime($date_end);
					// issue#" 492
					$query .= " AND UNIX_TIMESTAMP(u.user_registered) >= '{$date_start}' AND UNIX_TIMESTAMP(DATE_FORMAT(u.user_registered, '%Y-%m-%d')) <= '{$date_end}'";
				}else{
					$query .= " AND UNIX_TIMESTAMP(u.user_registered) >= '{$date_start}'";
				}
			}else if($date_end){
				// convert to mysql date
				$date_end = strtotime(mgm_format_inputdate_to_mysql($date_end,$sformat));
				// query
				$query .= " AND UNIX_TIMESTAMP(DATE_FORMAT(u.user_registered, '%Y-%m-%d')) <= '{$date_end}' ";
			}

			// filter
			$export_users = array();
			// date
			$current_date = time();	
			//issue #844 	
			$skip_fileds = array('subscription_introduction','coupon','privacy_policy','payment_gateways','terms_conditions',

								 'subscription_options','autoresponder','captcha','meta_value');

			// check - issue #1382
			$custom_fields = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_register'=>true,'on_profile'=>true)));

			if(isset($bk_users_to_import)){	

				$import_user_fileds = array('user_login','user_email','pack_id','membership_type');

				foreach($custom_fields as $field){ 

					if(!in_array($field['name'],$skip_fileds))

						$import_user_fileds[]=$field['name'];

				}

			}			

			// Custom fields	

			$cf_profile_pg = mgm_get_class('member_custom_fields');

			$to_unserialize = array();	

			foreach (array_unique($cf_profile_pg->sort_orders) as $id) :

				foreach($cf_profile_pg->custom_fields as $field):

					// issue #954: show the field only if it is enabled for profile page

					if ($field['id'] == $id && $field['type'] == 'checkbox'):

						$to_unserialize[]= $field['name'];

					endif;

				endforeach;

			endforeach;					 

			// query
			$query .= " AND um.meta_key = 'mgm_member_options' ";			
			// sql
			$tr_sql = 'SELECT count(*) FROM ' . $wpdb->users . ' u JOIN '.$wpdb->usermeta.' um ON u.ID=um.user_id WHERE u.ID <> 1  ' . $query ;
			$count  = $wpdb->get_var($tr_sql);
			// member obj
			$member_obj = mgm_get_class('mgm_member');							
			$default_fields = $member_obj->_default_fields();			
			$profile_fields = mgm_get_config('default_profile_fields', array());
			
			$start = 0;
			$limit = 1000;
			//count	
			if($count) {
				
				for( $i = $start; $i < $count; $i = $i + $limit ) {
					
					$users = $this->mgm_patch_partial_user_member_export($i, $limit, $query);
					
					foreach ($users as $user) {		
						// user cloned		
						$user_obj = clone $user;						
						$member = unserialize($user->meta_value);
						// convert
						$member = mgm_convert_array_to_memberobj($member, $user->ID);
						// check 
						if(!isset($bk_inactive)) $bk_inactive = false;
						// check search parameters:		
						if($this->_get_membership_details($member, $bk_msexp_dur_unit, $bk_msexp_dur, $membership_type, $current_date, $bk_inactive, $membership_status )) {							
							// merge defaults
							foreach($default_fields as $field=>$value){ 
								// if not set
								if(!isset($user->$field)){
									// set
									$user->{$field} = $member->{$field};			
								}
							}							
							// merge custom_fields
							foreach($custom_fields as $field){ 
								// if not set in main object
								if(!isset($user->$field['name'])){
									// if set in object
									if(isset($member->custom_fields->$field['name'])){
										$user->$field['name'] = $member->custom_fields->$field['name'];
									}else{
									// default
										$user->$field['name'] = 'N/A';
									}
								}
							}
							
							foreach(get_object_vars($member_obj) as $field=>$value){
								// skip some 
								if(in_array($field, array('id','code','name','description','saving'))) continue;			
								// loop
								foreach($profile_fields as $p_field=>$p_field_options){
									if (method_exists($user, 'exists')) {
										if ( ! $user->exists() )
							                  continue;					
							            if ( $user->has_prop( $wpdb->prefix . $p_field ) ) // Blog specific
											$result = $user->get( $wpdb->prefix . $p_field );
										elseif ( $user->has_prop( $p_field ) ) // User specific and cross-blog
											$result = $user->get( $p_field );
										else
											$result = false;
					
										if($result){
											$user->$p_field = $result;	
										}
									}
								}
														
								// if not set
								 if(!isset($user->$field) && $field != 'custom_fields'){
									// string value
									if(is_string($value)){
										// strip _mgm				
										$value = str_replace('mgm_', '', $value);				
										// set
										$user->{$field} = $value;	
									}else if(is_object($value)){
									// object value
										// loop
										foreach(get_object_vars($value) as $field2=>$value2){
											// only take first level
											if(is_string($value2)){
												// strip _mgm		
												$value2 = str_replace('mgm_', '', $value2);
												$field2 = $field . '_' . $field2;						
												// set
												$user->{$field2} = $value2;	
											}	
										}
									}
								}	
							}							
													
							foreach ($skip_fileds as $skip_filed){		
								unset($user->$skip_filed);		
							}		
							// format dates		
							$user->user_registered = date($sformat, strtotime($user->user_registered));			
							$user->last_pay_date   = (int)$user->last_pay_date>0 ? date($sformat, strtotime($user->last_pay_date)) : 'N/A';			
							$user->expire_date     = (!empty($user->expire_date)) ? date($sformat, strtotime($user->expire_date)) : 'N/A';				
							$user->join_date       = (int)$user->join_date>0 ? date($sformat, $user->join_date) : 'N/A';				
							$user->rss_token   	   = $member->rss_token;	
							// unset password		
							unset($user->password,$user->password_conf);		
							// unserialize checkbox values		
							if (count($to_unserialize)) {		
								foreach( $to_unserialize as $chkname) {		
									if (isset($user->{$chkname}) && !empty($user->{$chkname})) {		
										$chk_val = @unserialize($user->{$chkname});		
										if (is_array($chk_val)) {		
											$user->{$chkname} = implode("|", $chk_val);		
										}		
									}		
								}		
							}		
							// check - issue #1382		
							if(isset($bk_users_to_import)){								
								$importuser = new stdClass();														
								foreach ($import_user_fileds as $import_user_filed){								
									if($user->$import_user_filed) 		
										$importuser->$import_user_filed = $user->$import_user_filed;		
									if($import_user_filed =='pack_id') 		
										$importuser->$import_user_filed = $member->pack_id;		
								}		
								$export_users[] = $importuser;		
								unset($importuser);							
							}else {		
								$export_users[] = $user;		
							}								
						}						
						// consider multiple memberships as well:		
						if(isset($member->other_membership_types) && is_object($member->other_membership_types) && !empty($member->other_membership_types) ) {		
							// loop		
							foreach ($member->other_membership_types as $key => $memtypes) {		
								// types		
								$memtypes = mgm_convert_array_to_memberobj($memtypes, $user->ID);		
								// check search parameters:		
								if($this->_get_membership_details($memtypes, $bk_msexp_dur_unit, $bk_msexp_dur, $membership_type, $current_date, $bk_inactive, $membership_status )) {		
									// copy		
									$user_mem = clone $user_obj;			
									// add custom fields as well:		
									if(!empty($member->custom_fields)) {		
										// loop		
										foreach ($member->custom_fields as $index => $val) {		
											// custom field		
											if($index == 'birthdate' && !empty($val)) {		
												// convert saved date to input field format		
												$val = mgm_get_datepicker_format('date', $val);		
											}		
											// set		
											$user_mem->{$index} = $val;		
										}		
									}				
									// check types		
									if( is_object($memtypes) && method_exists($memtypes,'merge_fields')){		
										// merge				
										$user_mem = $memtypes->merge_fields($user_mem);			
									}else {		
										// convert to array		
										$data = mgm_object2array($memtypes);		
										// check payment		
										if(isset($memtypes->payment_info) && count($memtypes->payment_info) > 0) {		
											// loop payments		
											foreach ($memtypes->payment_info as $index => $val){		
												// set		
												$data['payment_info_' . $index] = str_replace('mgm_', '', $val);		
											}		
										}		
										// loop data		
										foreach ($data as $index => $val) $user_mem->{$index} = $val;		
									}		
									//issue #844 			
									foreach ($skip_fileds as $skip_filed){		
										unset($user->$skip_filed);		
									}		
									// format dates		
									$user_mem->user_registered = date($sformat, strtotime($user_mem->user_registered));			
									$user_mem->last_pay_date   = (int)$memtypes->last_pay_date>0 ? date($sformat, strtotime($memtypes->last_pay_date)) : 'N/A';			
									$user_mem->expire_date     = (!empty($memtypes->expire_date)) ? date($sformat, strtotime($memtypes->expire_date)) : 'N/A';				
									$user_mem->join_date       = (int)$memtypes->join_date > 0 ? date($sformat, $memtypes->join_date) : 'N/A';				
									// check - issue #1382		
									if(isset($bk_users_to_import)){								
										$importuser = new stdClass();														
										foreach ($import_user_fileds as $import_user_filed){		
											if($user_mem->$import_user_filed) 		
												$importuser->$import_user_filed = $user_mem->$import_user_filed;		
											if($import_user_filed =='pack_id') 
												$importuser->$import_user_filed = $memtypes->pack_id;		
										}		
										$export_users[] = $importuser;	
										unset($importuser);						
									}else {		
										$export_users[] = $user_mem;		
									}			
									// unset 		
									unset($user_mem);		
								}		
							}		
						}
						unset($user);															
					}
					usleep(10000);
				}
			}		
			//mgm_log('export_users : '.mgm_array_dump($export_users,true));
			// default response
			$response = array('status'=>'error','message' => __('Error while exporting members. Could not find any member with requested search parameters.', 'mgm'));
			// check
			if (count($export_users)>0) {
				// success
				$success = count($export_users);
				// create
				if($bk_export_format == 'csv'){
					$filename= mgm_create_csv_file($export_users, 'export_users');			
				}else{
					$filename= mgm_create_xls_file($export_users, 'export_users');			
				}
				// src
				$file_src = MGM_FILES_EXPORT_URL . $filename;				
				// message
				$response['message'] = sprintf(__('Successfully exported %d %s.', 'mgm'), $success, ($success>1 ? 'users' : 'user'));
				$response['status']  = 'success';
				$response['src']     = $file_src;// for download iframe 
			}
			// return response
			echo json_encode($response); exit();
		}	
		// data
		$data = array();							
		// load template view
		$this->load->template('members/member/export', array('data'=>$data));	

	}	
*/	
 }
// return name of class 
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin_members.php
