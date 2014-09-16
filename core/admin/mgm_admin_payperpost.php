<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin payperpost module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin_payperpost extends mgm_controller{ 	
	// construct
	function __construct()
	{		
		$this->mgm_admin_payperpost();
	}
	
	// construct php4
	function mgm_admin_payperpost()
	{		
		// load parent
		parent::__construct();
	}
	
	// index
	function index(){
		// data
		$data = array();
		// load template view
		$this->load->template('payperpost/index', array('data'=>$data));	
	}
	
	// post purchases
	function post_purchases(){		
		// data
		$data = array();
		// load template view
		$this->load->template('payperpost/post_purchases/index', array('data'=>$data));	
	}
	
	// post purchase statistics
	function post_purchase_statistics(){		
		global $wpdb;
		// data
		$data = array();
		// sql
		$sql = 'SELECT p.post_title AS title, COUNT(pp.id) AS count
				FROM `' . TBL_MGM_POST_PURCHASES.'` pp 
				JOIN ' . $wpdb->posts . ' p ON (p.id = pp.post_id)
				WHERE is_gift="N" 
				GROUP BY pp.post_id  ORDER BY pp.post_id DESC';
		// mgm_log( $sql );
		// store		
		$data['posts'] = $wpdb->get_results($sql);
		// load template view
		$this->load->template('payperpost/post_purchases/statistics', array('data'=>$data));	
	}
	
	// post purchase manage
	function post_purchase_manage(){
		// init
		$data = array();		
		// load template view
		$this->load->template('payperpost/post_purchases/manage', array('data'=>$data));
	}
	
	// post purchase lists
	function post_purchase_lists(){		
		global $wpdb;
		// pager
		$pager = new mgm_pager();
		// data
		$data = array();
		// search fields
		$data['search_fields'] = array(''=> __('Select','mgm'), 'user_login'=> __('Username/Guest','mgm'), 'is_gift'=> __('Type','mgm'), 
		                              'purchase_dt'=> __('Purchase/Gift Date','mgm'), 'post_title' => __('Post','mgm'));
		// sort fields							  
		$data['sort_fields'] = array('purchase_dt'=> __('Purchase date','mgm'),'post_title'=> __('Post title','mgm'),'user_login'=> __('User login','mgm'));		
		// filter
		$sql_filter = $data['search_field_name'] = $data['search_field_value'] = '';
		// post
		$search_field_name = mgm_post_var('search_field_name');
		
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
			//searc value
			$search_field_value     = esc_sql($search_field_value);// for sql
			// end date value
			$search_field_value_two = esc_sql($search_field_value_two);// for sql		
			
			//current date
			$curr_date = mgm_get_current_datetime();
			$current_date = $curr_date['timestamp'];		
			
			// by field
			switch($search_field_name){
				case 'user_login':
					$sql_filter = " AND (`user_login` LIKE '%{$search_field_value}%' OR `guest_token` LIKE '%{$search_field_value}%')";			
				break;	
				case 'post_title':
					$sql_filter = " AND (`post_title` LIKE '%{$search_field_value}%')";			
				break;	
				case 'is_gift':
					$sql_filter = " AND (`is_gift` = '{$search_field_value}')";					
				break;	
				case 'purchase_dt':
					// date start
					if(empty($search_field_value)){
						$search_field_value = date('Y-m-d',$current_date);
					}
					// date end
					if(empty($search_field_value_two)){
						$search_field_value_two = date('Y-m-d',$current_date);
					}
					// convert 
					$search_field_value = mgm_format_inputdate_to_mysql($search_field_value);	
					$search_field_value_two = mgm_format_inputdate_to_mysql($search_field_value_two);	
					// set
					$sql_filter = " AND (DATE_FORMAT(`pp`.`purchase_dt`,'%Y-%m-%d') BETWEEN '{$search_field_value}' AND '{$search_field_value_two}')";
				break;	
			}
		}
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
				case 'user_login':
					$sql_order_by = "u.user_login";
				break;
				case 'post_title':
					$sql_order_by = "p.post_title";
				break;
				case 'purchase_dt':
					$sql_order_by = "pp.purchase_dt";
				break;				
			}			
			// set
			if(isset($sql_order_by)) $sql_order = " ORDER BY {$sql_order_by} {$sort_type}";				
		}
		// default order
		if(!isset($sql_order_by)) $sql_order = " ORDER BY `pp`.`purchase_dt` DESC";

		// page limit		
		$data['page_limit'] = isset($_REQUEST['page_limit']) ? (int)$_REQUEST['page_limit'] : 20;// 20
		// page no
		$data['page_no'] = isset($_REQUEST['page_no']) ? (int)$_REQUEST['page_no'] : 1;		
		// limit
		$sql_limit = $pager->get_query_limit($data['page_limit']);

		// sql
		$sql = "SELECT SQL_CALC_FOUND_ROWS p.ID AS post_id, p.post_title, pp.purchase_dt, 
		        IF(user_id IS NULL, CONCAT('guest-', guest_token), u.user_login) AS user_login, 
				pp.id, pp.is_gift,pp.is_expire FROM `" . TBL_MGM_POST_PURCHASES."` pp 
				LEFT JOIN " . $wpdb->posts . " p ON (p.id = pp.post_id) 
				LEFT JOIN " . $wpdb->users . " u ON (u.ID = pp.user_id) WHERE 1 
				{$sql_filter} {$sql_order} {$sql_limit}";	
		// store		
		$data['post_purchases'] = $wpdb->get_results($sql);	
		// page url
		$data['page_url']   = 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=post_purchase_lists';
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
			// date range		
			if(!empty($data['search_field_value_two'])){
				// set
				$search_term = sprintf(__('where <b>%s</b> between <b>%s</b> and <b>%s</b> dates','mgm'), 				
									  (isset($data['search_fields'][$search_field_name]) ? $data['search_fields'][$search_field_name] : ''), 
									  $data['search_field_value'], 
									  $data['search_field_value_two']);
			}else {
				$search_term = sprintf(__('where <b>%s</b> is <b>%s</b>','mgm'), 
									  (isset($data['search_fields'][$search_field_name]) ? $data['search_fields'][$search_field_name] : ''), 
									  $data['search_field_value']);
			}			
		}	
		// message
		$data['message'] = sprintf(__('%d %s matched %s','mgm'), $data['row_count'], ($data['row_count']>1 ? 'purchases' : 'purchase'), $search_term);	
		
		// load template view
		$this->load->template('payperpost/post_purchases/lists', array('data'=>$data));	
	}

	// post purchase export
	function post_purchase_export(){		
		global $wpdb;
		// data
		$data = array();
		// filter
		$sql_filter = $data['search_field_name'] = $data['search_field_value'] = '';
		$search_field_name = mgm_post_var('search_field_name');
		
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
			//searc value
			$search_field_value     = esc_sql($search_field_value);// for sql
			// end date value
			$search_field_value_two = esc_sql($search_field_value_two);// for sql		
			
			//current date
			$curr_date = mgm_get_current_datetime();
			$current_date = $curr_date['timestamp'];		
			
			// by field
			switch($search_field_name){
				case 'user_login':
					$sql_filter = " AND (`user_login` LIKE '%{$search_field_value}%' OR `guest_token` LIKE '%{$search_field_value}%')";			
				break;	
				case 'post_title':
					$sql_filter = " AND (`post_title` LIKE '%{$search_field_value}%')";			
				break;	
				case 'is_gift':
					$sql_filter = " AND (`is_gift` = '{$search_field_value}')";					
				break;	
				case 'purchase_dt':
					// date start
					if(empty($search_field_value)){
						$search_field_value = date('Y-m-d',$current_date);
					}
					// date end
					if(empty($search_field_value_two)){
						$search_field_value_two = date('Y-m-d',$current_date);
					}
					// convert 
					$search_field_value = mgm_format_inputdate_to_mysql($search_field_value);	
					$search_field_value_two = mgm_format_inputdate_to_mysql($search_field_value_two);	
					// set
					$sql_filter = " AND (DATE_FORMAT(`pp`.`purchase_dt`,'%Y-%m-%d') BETWEEN '{$search_field_value}' AND '{$search_field_value_two}')";
				break;	
			}
		}
		// order
		$sql_order = " ORDER BY u.user_login, p.post_title";
		
		// sql
		$sql = "SELECT SQL_CALC_FOUND_ROWS p.ID AS post_id, p.post_title, pp.purchase_dt, 
		        IF(user_id IS NULL, CONCAT('guest-', guest_token), u.user_login) AS user_login, pp.id,
		        pp.is_gift,pp.is_expire FROM `" . TBL_MGM_POST_PURCHASES."` pp 
				LEFT JOIN " . $wpdb->posts . " p ON (p.id = pp.post_id) 
				LEFT JOIN " . $wpdb->users . " u ON (u.ID = pp.user_id) WHERE 1 
				{$sql_filter} {$sql_order} ";	
		// log
		// mgm_log($sql);		
		// store		
		$data['post_purchases'] = $wpdb->get_results($sql);	
		// date format
		$date_format = mgm_get_date_format('date_format');
		// init
		$purchases = array();
		// check
		if(count($data['post_purchases'])>0): 
			//purchases
			foreach($data['post_purchases'] as $purchase):
				// int
				$row = new stdClass();
				// type
				$type = ($purchase->is_gift == 'Y') ? __('Gift','mgm') : __('Purchase','mgm');
				// check is_expiry
				if($purchase->is_expire == 'N'):
					$expiry = __('Indefinite', 'mgm');	
				else:
					$expiry = mgm_get_post($purchase->post_id)->get_access_duration();
					$expiry = (!$expiry) ? __('Indefinite', 'mgm') : (date($date_format,(86400*$expiry) + strtotime($purchase->purchase_dt)) . " (" . $expiry . __(' D','mgm').")");	
				endif;	
				// member name
				if(preg_match('/^guest-/',$purchase->user_login)):
					// guest token
					$guest_token = str_replace('guest-','',$purchase->user_login);
					// username
					$username = __('Guest','mgm') . sprintf(' (%s)', $guest_token);
				else:	
					// username
					$username = $purchase->user_login;
				endif;	
				//export fields
				$row->username           	= $username;
				$row->post     				= $purchase->post_title;
				$row->type    				= $type;
				$row->expire_date     		= $expiry;
				$row->purchase_or_gift_date	= date($date_format, strtotime($purchase->purchase_dt));
				// cache
				$purchases[] = $row;	
				// unset 
				unset($row);
	 		endforeach;
	 	endif;
	 	
	 	// default response
		$response = array('status'=>'error','message' => __('Error while exporting post (purchase/gift)s.', 'mgm'));
		// check
		if (count($purchases)>0) {
			// success
			$success = count($purchases);
			// create
			if(mgm_post_var('export_format') == 'csv'){
				$filename= mgm_create_csv_file($purchases, 'post_purchases');			
			}else{
				$filename= mgm_create_xls_file($purchases, 'post_purchases');			
			}			
			// src
			$file_src = MGM_FILES_EXPORT_URL . $filename;				
			// message
			$message = sprintf(__('Successfully exported %d post %s.', 'mgm'), $success, ($success>1 ? 'purchases' : 'purchase'));
			// init
			$response = array('status'=>'success','message'=>$message,'src'=>$file_src);	
		}
		// return response
		echo json_encode($response); exit();
	}
	
	// post purchase delete
	function post_purchase_delete(){
		global $wpdb;	
		extract($_POST);		
		// sql
		$sql = $wpdb->prepare("DELETE FROM `" . TBL_MGM_POST_PURCHASES . "` WHERE id = %d", $id); 		
		// delete
	    if ($wpdb->query($sql)) {    	    
			$message = __('Successfully deleted post purchase record.', 'mgm');
			$status  = 'success';
	    }else{
			$message = __('Error while deleting post purchase record.', 'mgm');
			$status  = 'error';
		}
		// return response		
		echo json_encode(array('status'=>$status, 'message'=>$message));		
	}
	
	// post purchase gift
	function post_purchase_gift(){		
		global $wpdb;	
		extract($_POST);
		
		// save
		if(isset($send_gift)){	
			// user data
			$user = get_userdata($user_id);	
			$post = get_post($post_id);	
			// expire
			if(!isset($is_expire) || empty($is_expire))
				$is_expire = 'Y';  
			
			// sql
			$sql = $wpdb->prepare("REPLACE INTO `" . TBL_MGM_POST_PURCHASES . "` SET `user_id`=%d, `post_id`=%d, 
			       				  `is_gift`=%s,`purchase_dt`=NOW(), `is_expire`=%s", $user_id, $post_id, 'Y', $is_expire);			
			// saved
			if ($wpdb->query($sql)) {
				$message = sprintf(__('Successfully gifted post - "%s" to member - "%s".', 'mgm'), $post->post_title, $user->display_name);
				$status  = 'success';
			}else{
				$message = sprintf(__('Error while gifting post - "%s" to member - "%s".', 'mgm'),$post->post_title, $user->display_name);
				$status  = 'error';
			}
			// return response			
			echo json_encode(array('status'=>$status, 'message'=>$message));				
			exit();
		}
		// data
		$data = array();
		// users
		$data['users'] = mgm_field_values( $wpdb->users, 'ID', 'user_login', "AND ID<>1", 'user_login');	
		// posts		
		$data['posts'] = mgm_get_purchasable_posts();
		// load template view
		$this->load->template('payperpost/post_purchases/gift', array('data'=>$data));	
	}
	
	// postpacks ------------------------------------------------------------------------------------------------------------------
	function postpacks(){		
		// data
		$data = array();
		// load template view
		$this->load->template('payperpost/postpacks/index', array('data'=>$data));		
	}
	
	// postpack list
	function postpack_list(){
		global $wpdb;	
		// data
		$data = array();	
		// postpacks		
	    $data['postpacks'] = $wpdb->get_results('SELECT id, name, description, create_dt, cost FROM `' . TBL_MGM_POST_PACK . '` ORDER BY name');
		// currency
		$data['currency'] = mgm_get_class('system')->setting['currency'];		 
		// load template view
		$this->load->template('payperpost/postpacks/list', array('data'=>$data));		
	}
	
	// postpack add
	function postpack_add(){	
		global $wpdb;	
		extract($_POST);
		
		// save
		if(isset($save_postpack)){			
			// product
			$product = json_encode($product);
			// modules
			$modules = json_encode($modules);
			// sql
			$sql = $wpdb->prepare("INSERT INTO `" . TBL_MGM_POST_PACK . "` SET name=%s, cost=%s, description=%s, product=%s, modules=%s, create_dt=NOW() ", $name, $cost, $description, $product, $modules);		
			// saved
			if ($wpdb->query($sql)) {
				$message = sprintf(__('Successfully created new postpack: %s', 'mgm'),  $name);
				$status  = 'success';
			}else{
				$message = sprintf(__('Error while creating new postpack: %s', 'mgm'),  $name);
				$status  = 'error';
			}
			// return response
			
			echo json_encode(array('status'=>$status, 'message'=>$message));
				
			exit();
		}	
		
		// data
		$data = array();
		// currency
		$data['currency'] = mgm_get_class('system')->setting['currency'];	
		// load template view
		$this->load->template('payperpost/postpacks/add', array('data'=>$data));		
	}	
	
	// postpack edit
	function postpack_edit(){	
		global $wpdb;	
		extract($_POST);
		
		// save
		if(isset($save_postpack)){	
			// product
			$product = json_encode($product);		
			// modules
			$modules = json_encode($modules);
			// sql
			$sql = $wpdb->prepare("UPDATE " . TBL_MGM_POST_PACK . " SET name=%s, cost=%s, description=%s, product=%s, modules=%s WHERE id=%d ", $name, $cost, $description, $product, $modules, $id );	
			// saved
			if ($wpdb->query($sql)) {
				$message = sprintf(__('Successfully updated postpack: %s', 'mgm'),  $name);
				$status  = 'success';
			}else{
				$message = sprintf(__('Error while updating postpack: %s', 'mgm'),  $name);
				$status  = 'error';
			}
			// return response
			
			echo json_encode(array('status'=>$status, 'message'=>$message));
				
			exit();
		}	
		
		// data
		$data = array();
		// postpack
		$postpack = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".TBL_MGM_POST_PACK." WHERE id=%d", $id));
		// products
		$postpack->product = json_decode($postpack->product, true);
		// modules
		$postpack->modules = json_decode($postpack->modules, true);
		// decode
		$data['postpack'] = $postpack;
		// currency
		$data['currency'] = mgm_get_class('system')->setting['currency'];		 
		// load template view
		$this->load->template('payperpost/postpacks/edit', array('data'=>$data));		
	}	
	
	// postpack delete 
	function postpack_delete(){
		global $wpdb;	
		extract($_POST);		
		// sql
		$sql = $wpdb->prepare("DELETE FROM `" . TBL_MGM_POST_PACK . "` WHERE id = %d", $id); 		
		// delete
	    if ($wpdb->query($sql)) {    	    
			$message = __('Successfully deleted postpack.', 'mgm');
			$status  = 'success';
	    }else{
			$message = __('Error while deleting postpack.', 'mgm');
			$status  = 'error';
		}
		// return response
		
		echo json_encode(array('status'=>$status, 'message'=>$message));
		
	}
	
	// postpack posts
	function postpack_posts(){
		global $wpdb;	
		extract($_POST);		
		// postpack
		$postpack = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".TBL_MGM_POST_PACK." WHERE id = %d", $pack_id));
		// save
		if(isset($save_postpack_post)){		
			// marker
			$updated = 0;
			
			// save 				
			if ($posts) {				
				foreach ($posts as $post_id) {
					// clear old
					// $wpdb->query('DELETE FROM `' . TBL_MGM_POST_PACK_POST_ASSOC . '` WHERE pack_id = ' . $pack_id);
					// is added
					$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) AS _C FROM ". TBL_MGM_POST_PACK_POST_ASSOC ." WHERE pack_id=%d AND post_id=%d", $pack_id, $post_id));
					if($count == 0 ){
						$sql = $wpdb->prepare("INSERT INTO `" . TBL_MGM_POST_PACK_POST_ASSOC. "` SET pack_id=%d, post_id=%d, create_dt=NOW() ", $pack_id, $post_id);
						$wpdb->query($sql);
						$updated++;		
					}		
				}
			}			
					
			// saved
			if ($updated) {
				$message = sprintf(__('Successfully associated post to postpack: %s', 'mgm'),  $postpack->name);
				$status  = 'success';
			}else{
				$message = sprintf(__('Error while associated post to postpack: %s', 'mgm'),  $postpack->name);
				$status  = 'error';
			}
			// return response
			
			echo json_encode(array('status'=>$status, 'message'=>$message));
				
			exit();
		}	
		
		// data
		$data = array();
		// postpack
		$data['postpack'] = $postpack;
		// exclude 
		$data['exclude_posts'] = array();
		// fetch 
		$associated_posts = $wpdb->get_results($wpdb->prepare('SELECT post_id FROM `' . TBL_MGM_POST_PACK_POST_ASSOC . '`  WHERE pack_id = %d ', $pack_id));
		if($associated_posts){
			foreach($associated_posts as $a_post){
				$data['exclude_posts'][] = $a_post->post_id;
			}
		}
		// load template view
		$this->load->template('payperpost/postpacks/posts/index', array('data'=>$data));		
	}
	
	// postpack post list
	function postpack_post_list(){
		global $wpdb;	
		extract($_POST);
		
		// data
		$data = array();
		// postpack posts				
        $data['postpack_posts'] = $wpdb->get_results($wpdb->prepare('SELECT id, pack_id, post_id, create_dt FROM `' . TBL_MGM_POST_PACK_POST_ASSOC . '`  WHERE pack_id = %d', $pack_id));
		// load template view
		$this->load->template('payperpost/postpacks/posts/list', array('data'=>$data));		
	}
	
	// postpack post delete 
	function postpack_post_delete(){
		global $wpdb;	
		extract($_POST);		
		// sql
		$sql = $wpdb->prepare("DELETE FROM `" . TBL_MGM_POST_PACK_POST_ASSOC . "` WHERE id = %d", $id); 		
		// delete
	    if ($wpdb->query($sql)) {    	    
			$message = __('Successfully deleted postpack post association.', 'mgm');
			$status  = 'success';
	    }else{
			$message = __('Error while deleting postpack association.', 'mgm');
			$status  = 'error';
		}
		// return response		
		echo json_encode(array('status'=>$status, 'message'=>$message));	
	}
 }
// return name of class 
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin_payperpost.php