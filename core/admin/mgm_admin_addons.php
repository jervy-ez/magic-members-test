<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin addons module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin_addons extends mgm_controller{
 	
	// construct
	function __construct()
	{		
		$this->mgm_admin_addons();
	}
	
	// construct php4
	function mgm_admin_addons()
	{		
		// load parent
		parent::__construct();
	}
	
	// index
	function index(){
		global $wpdb;	
		// data
		$data = array();			
		// load template view
		$this->load->template('addons/index', array('data'=>$data));
	}
		
	// list
	function lists(){
		global $wpdb;					
		// pager
		$pager = new mgm_pager();					
		// data
		$data = array();		
		// search fields
		$data['search_fields'] = array(''=> __('Select','mgm'), 'id'=> __('ID','mgm'), 'name'=> __('Name','mgm'), 'description'=> __('Description','mgm'), 
		                               'create_dt' => __('Created','mgm'), 'expire_dt'=> __('Expires','mgm'));
									   
		// sort fields							  
		$data['sort_fields'] = array('id'=> __('ID','mgm'), 'name'=> __('Name','mgm'), 'create_dt'=> __('Created','mgm'),'expire_dt'=> __('Expires','mgm'));										   

		// filter
		$sql_filter = $data['search_field_name'] = $data['search_field_value'] = '';		

		// check
		if(isset($_POST['search_field_name'])) {
			// issue#: 219
			$search_field_name  = $_POST['search_field_name']; // for sql			
			$search_field_value = esc_sql($_POST['search_field_value']);// for sql
			// view data	
			$data['search_field_name'] 	= $_POST['search_field_name'];
			//issue #1281												
			$data['search_field_value'] = htmlentities($_POST['search_field_value'], ENT_QUOTES, "UTF-8");// for display
			// by field
			switch($search_field_name){
				case 'id':
					$sql_filter = " AND (`id` = '".(int)$search_field_value."')";	
				break;
				case 'name':
					$sql_filter = " AND (`name` LIKE '%{$search_field_value}%')";			
				break;	
				case 'description':
					$sql_filter = " AND (`description` LIKE '%{$search_field_value}%')";				
				break;	
				case 'create_dt':
					// convert 
					$search_field_value = mgm_format_inputdate_to_mysql($search_field_value);	
					// set filter				
					$sql_filter = " AND (DATE_FORMAT(`create_dt`,'%Y-%m-%d') = '{$search_field_value}')";
				break;	
				case 'expire_dt':
					// convert 
					$search_field_value = mgm_format_inputdate_to_mysql($search_field_value);	
					// set filter				
					$sql_filter = " AND (DATE_FORMAT(`expire_dt`,'%Y-%m-%d') = '{$search_field_value}')";
				break;
			}
		}
		
		// default 
		$sort_field = 'create_dt';
		// type
		$sort_type = 'DESC';
		// sort field
		if(isset($_POST['sort_field'])){			
			$sort_field = $_POST['sort_field'];
		}	
		// sort type
		if(isset($_POST['sort_type'])){
		 	$sort_type = $_POST['sort_type'];
		}				
		// set
		$data['sort_field'] = $sort_field;
		// set
		$data['sort_type'] = $sort_type;	
		// set
		$sql_order_by = "ORDER BY `{$sort_field}` {$sort_type}";
		// page limit		
		$data['page_limit'] = isset($_REQUEST['page_limit']) ? (int)$_REQUEST['page_limit'] : 20;// 20
		// page no
		$data['page_no'] = isset($_REQUEST['page_no']) ? (int)$_REQUEST['page_no'] : 1;		
		// limit
		$sql_limit = $pager->get_query_limit($data['page_limit']);
		//sql	
		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM `" . TBL_MGM_ADDON."` WHERE 1 {$sql_filter} {$sql_order_by} {$sql_limit}";		
		// addons
		$data['addons'] = $wpdb->get_results($sql);	
		// log
		// mgm_log($wpdb->last_query, __FUNCTION__);
		// page url
		$data['page_url']   = 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=lists';
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
			$search_term = sprintf('where <b>%s</b> is <b>%s</b>', (isset($data['search_fields'][$search_field_name]) ? $data['search_fields'][$search_field_name] : ''), $data['search_field_value']);
		}	
		// message
		$data['message'] = sprintf(__('%d %s matched %s', 'mgm'), $data['row_count'], ($data['row_count']>1 ? __('addons', 'mgm') : __('addon', 'mgm')), $search_term);			
		// load template view
		$this->load->template('addons/lists', array('data'=>$data));		
	}
	
	// add
	function add(){	
		global $wpdb;	
		extract($_POST);
		
		// save
		if(isset($save_addon)){			
			// response
			$response = array('status'=>'error', 'message'=>__('Addon create failed, Unknown error!', 'mgm'));
			// check duplicate
			if( mgm_is_duplicate(TBL_MGM_ADDON, array('name')) ){
				// set message
				$response['message'] = sprintf(__('Error while creating new addon: %s, same name exists!', 'mgm'), $name);
			}else{
				// fields
				$fields = array('name','description','expire_dt','create_dt');
				// colums
				$column_data = array();				
				// expire dt format
				if(isset($expire_dt) && !empty($expire_dt)){					
					$expire_dt = date('Y-m-d H:i:s', strtotime(mgm_format_inputdate_to_mysql($expire_dt, mgm_get_date_format('date_format_short'))));
				}else{
					$expire_dt = NULL;
				}
				// create_dt
				$create_dt = date('Y-m-d H:i:s');
				// loop
				foreach($fields as $field){
					// check
					if (isset( ${$field} ) && !is_null( ${$field} ) ){
						$column_data[$field] = trim( ${$field} ); 
					}
				}
				// insert	
				$affected = $wpdb->insert(TBL_MGM_ADDON, $column_data);				
				// save
				if ( $affected ) {	
					// add options
					$addon_id = $wpdb->insert_id;
					// check
					if(!isset($addon_options)) $addon_options = array();
					// save options
					$this->_save_addon_options($addon_id, $addon_options, 'insert');
					// response				
					$response = array('status'=>'success', 'message'=>sprintf(__('Successfully created new addon: "%s"', 'mgm'), $name));
				}else{
					$response = array('status'=>'error', 'message'=>sprintf(__('Error while creating new addon: "%s"', 'mgm'), $name));					
				}				
			}	
			// return response
			echo json_encode($response); exit();
		}	
		
		// data
		$data = array();
		// addon_options
		$data['addon_options'] = array(array('option'=>'','price'=>''));		
		// load template view
		$this->load->template('addons/add', array('data'=>$data));		
	}	
	
	// edit
	function edit(){	
		global $wpdb;	
		extract($_POST);
		
		// save
		if(isset($save_addon)){			
			// response
			$response = array('status'=>'error', 'message'=>__('Addon update failed, Unknown error!', 'mgm'));
			// check duplicate
			if( mgm_is_duplicate(TBL_MGM_ADDON, array('name'), "id <> '{$id}'") ){
				$response['message'] = sprintf(__('Error while updating addon: %s, same code exists!', 'mgm'),  $name);
			}else{
				// fields
				$fields = array('name','description','expire_dt');
				// colums
				$column_data = $column_null_data = array();					
				// expire dt format
				if(isset($expire_dt) && !empty($expire_dt)){					
					$expire_dt = date('Y-m-d H:i:s', strtotime(mgm_format_inputdate_to_mysql($expire_dt, mgm_get_date_format('date_format_short'))));
				}else{
					$expire_dt = NULL;
				}				
				// loop
				foreach($fields as $field){
					// check
					if (isset( ${$field} ) && !is_null( ${$field} ) ){
						$column_data[$field] = trim( ${$field} ); 						
					}else{
						$column_null_data[$field] = 'NULL';	// need string to track					
					}
				}
				// affected				
				$affected = 0;
				// update
				if( $wpdb->update(TBL_MGM_ADDON, $column_data, array('id'=>$id)) ){
					$affected++;
				}				
				// null
				if(!empty($column_null_data)){
					// column_data2					
					$column_data2_a = mgm_implode_a(',', array_keys($column_null_data), array_values($column_null_data));
					// update
					if( $wpdb->query( "UPDATE `" . TBL_MGM_ADDON . "` SET {$column_data2_a} WHERE `id`='{$id}' ") ){
						$affected++;
					}
				}	
				// check
				if(!isset($addon_options)) $addon_options = array();
				// save options
				if($this->_save_addon_options($id, $addon_options, 'update')){
					$affected++;
				}				
				// save				
				if ( $affected ) {					
					// response
					$response = array('status'=>'success', 'message'=>sprintf(__('Successfully updated addon: "%s"', 'mgm'), $name));					
				}else{
					$response = array('status'=>'error', 'message'=>sprintf(__('Error while updating addon: "%s"', 'mgm'), $name));					
				}
			}	
			// return response
			echo json_encode($response); exit();
		}	
		
		// data
		$data = array();
		// addon
		$data['addon'] = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".TBL_MGM_ADDON."` WHERE `id`=%d", $id));
		$data['addon_options'] = $wpdb->get_results($wpdb->prepare("SELECT `option`,`price` FROM `".TBL_MGM_ADDON_OPTION."` WHERE `addon_id`=%d", $id), ARRAY_A);	
		// empty
		if(empty($data['addon_options'])){
			$data['addon_options'] = array(array('option'=>'','price'=>''));		
		}
		// load template view
		$this->load->template('addons/edit', array('data'=>$data));		
	}	
	
	// delete 
	function delete(){
		global $wpdb;	
		extract($_POST);		
		// sql
		$affected = $wpdb->query($wpdb->prepare("DELETE FROM `" . TBL_MGM_ADDON . "` WHERE `id` = %d", $id)); 			
		// delete
	    if ( $affected ) {   
			// delet options
			$wpdb->query($wpdb->prepare("DELETE FROM `" . TBL_MGM_ADDON_OPTION . "` WHERE `addon_id` = %d", $id));
			// response 	    
			$response = array('status'=>'success','message'=>__('Successfully deleted addon', 'mgm'));
	    }else{
			// response
			$response = array('status'=>'error','message'=>__('Error while deleting addon', 'mgm'));
		}
		// return response
		echo json_encode($response);
	}
		
	// bulk update
	function bulk_update(){
		global $wpdb;
		extract($_POST);
		// init
		$response = array('status'=>'error','message'=>__('Error!', 'mgm'));		
		// save
		if(isset($_action)){
			// on action
			switch($_action){				
				case 'delete':
					// delete
					$response = $this->_delete_multiple();
				break;
			}
		}
		// return response
		echo json_encode($response); exit();
	}	
	
	// options
	function options(){
		global $wpdb;	
		extract($_POST);
		// save
		if(isset($save_addon_options)){					
			// response
			$response = array('status'=>'error', 'message'=>__('Addon options update failed, Unknown error!', 'mgm'));	
			// affected
			$affected = 0;	
			// check
			if(!isset($addon_options)) $addon_options = array();
			// save options
			if($this->_save_addon_options($id, $addon_options, 'update')){
				$affected++;
			}				
			// save				
			if ( $affected ) {					
				// response
				$response = array('status'=>'success', 'message'=>__('Successfully updated addon options', 'mgm'));					
			}else{
				$response = array('status'=>'error', 'message'=>__('Error while updating addon options', 'mgm'));					
			}	
			// return response
			echo json_encode($response); exit();
		}
		// data
		$data = array();
		// addon
		$data['addon'] = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".TBL_MGM_ADDON."` WHERE `id`=%d", $id));
		// users
		$data['addon_options'] = $wpdb->get_results($wpdb->prepare("SELECT `option`,`price` FROM `".TBL_MGM_ADDON_OPTION."` WHERE `addon_id`=%d", $id), ARRAY_A);	
		// empty
		if(empty($data['addon_options'])){
			$data['addon_options'] = array(array('option'=>'','price'=>''));		
		}
		// load template view
		$this->load->template('addons/options', array('data'=>$data));
	}
	
	// purchases
	function purchases(){
		// data
		$data = array();
		// load template view
		$this->load->template('addons/purchases/index', array('data'=>$data));	
	}
	
	// post purchase manage
	function purchase_manage(){
		// init
		$data = array();		
		// load template view
		$this->load->template('addons/purchases/manage', array('data'=>$data));
	}
	
	// purchase lists
	function purchase_lists(){		
		global $wpdb;
		// pager
		$pager = new mgm_pager();
		// data
		$data = array();
		// search fields
		$data['search_fields'] = array(''=> __('Select','mgm'), 'user_login'=> __('Username','mgm'), 
		                              'purchase_dt'=> __('Purchase Date','mgm'), 'addon_option' => __('Addon','mgm'));
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
					$sql_filter = " AND (`user_login` LIKE '%{$search_field_value}%')";			
				break;	
				case 'addon_option':
					$sql_filter = " AND (`option` LIKE '%{$search_field_value}%')";			
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
					$sql_filter = " AND (DATE_FORMAT(`purchase_dt`,'%Y-%m-%d') BETWEEN '{$search_field_value}' AND '{$search_field_value_two}')";
				break;	
			}
		}
		// oredr
		$sql_order = " ORDER BY `user_login`, `purchase_dt`";

		// page limit		
		$data['page_limit'] = isset($_REQUEST['page_limit']) ? (int)$_REQUEST['page_limit'] : 20;// 20
		// page no
		$data['page_no'] = isset($_REQUEST['page_no']) ? (int)$_REQUEST['page_no'] : 1;		
		// limit
		$sql_limit = $pager->get_query_limit($data['page_limit']);

		// sql
		$sql = "SELECT SQL_CALC_FOUND_ROWS A.id, A.purchase_dt, B.option AS addon_option, C.user_login
		        FROM `" . TBL_MGM_ADDON_PURCHASES."` A 
				LEFT JOIN " . TBL_MGM_ADDON_OPTION . " B ON (B.id = A.addon_option_id) 
				LEFT JOIN " . $wpdb->users . " C ON (C.ID = A.user_id) WHERE 1 
				{$sql_filter} {$sql_order} {$sql_limit}";	
				
		// store		
		$data['addon_purchases'] = $wpdb->get_results($sql);	
		// page url
		$data['page_url']   = 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=purchase_lists';
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
		$this->load->template('addons/purchases/lists', array('data'=>$data));	
	}
	
	// purchase export
	function purchase_export(){		
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
					$sql_filter = " AND (`user_login` LIKE '%{$search_field_value}%')";			
				break;	
				case 'addon_option':
					$sql_filter = " AND (`option` LIKE '%{$search_field_value}%')";			
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
					$sql_filter = " AND (DATE_FORMAT(`purchase_dt`,'%Y-%m-%d') BETWEEN '{$search_field_value}' AND '{$search_field_value_two}')";
				break;	
			}
		}
		// order
		$sql_order = " ORDER BY `user_login`, `purchase_dt`";
		
		// sql
		$sql = "SELECT SQL_CALC_FOUND_ROWS A.id, A.purchase_dt, B.option AS addon_option, C.user_login
		        FROM `" . TBL_MGM_ADDON_PURCHASES."` A 
				LEFT JOIN " . TBL_MGM_ADDON_OPTION . " B ON (B.id = A.addon_option_id) 
				LEFT JOIN " . $wpdb->users . " C ON (C.ID = A.user_id) WHERE 1 
				{$sql_filter} {$sql_order} ";				
		// store		
		$data['addon_purchases'] = $wpdb->get_results($sql);
		// date format
		$date_format = mgm_get_date_format('date_format');	
		// init
		$purchases = array();
		// check		
		if(count($data['addon_purchases'])>0): 
			//purchases
			foreach($data['addon_purchases'] as $purchase):	
				// int			
				$row = new stdClass();		
				// export fields
				$row->username      = $purchase->user_login;
				$row->addon_option  = $purchase->addon_option;				
				$row->purchase_date	= date($date_format, strtotime($purchase->purchase_dt));
				// cache
				$purchases[] = $row;	
				// unset 
				unset($row);
	 		endforeach;
	 	endif;
	 	
	 	// default response
		$response = array('status'=>'error','message'=>__('Error while exporting addon purchases.', 'mgm'));
		// check
		if (count($purchases)>0) {
			// success
			$success = count($purchases);
			// create
			if(mgm_post_var('export_format') == 'csv'){
				$filename= mgm_create_csv_file($purchases, 'addon_purchases');			
			}else{
				$filename= mgm_create_xls_file($purchases, 'addon_purchases');			
			}			
			// src
			$file_src = MGM_FILES_EXPORT_URL . $filename;		
			// message
			$message = sprintf(__('Successfully exported %d addon %s.', 'mgm'), $success, ($success>1 ? 'purchases' : 'purchase'));
			// init
			$response = array('status'=>'success','message'=>$message,'src'=>$file_src);			
		}
		// return response
		echo json_encode($response); exit();
	}
	
	// purchase delete
	function purchase_delete(){
		global $wpdb;	
		extract($_POST);		
		// sql
		$sql = $wpdb->prepare("DELETE FROM `" . TBL_MGM_ADDON_PURCHASES . "` WHERE `id` = %d", $id); 		
		// delete
	    if ($wpdb->query($sql)) {    	    
			$message = __('Successfully deleted addon purchase record.', 'mgm');
			$status  = 'success';
	    }else{
			$message = __('Error while deleting addon purchase record.', 'mgm');
			$status  = 'error';
		}
		// return response		
		echo json_encode(array('status'=>$status, 'message'=>$message));		
	}
	
	// private ----------------------------------------------------------
	// delete multiple
	private function _delete_multiple(){
		global $wpdb;	
		extract($_POST);
		// init
		$affected = 0;		
		$response = array('status'=>'error','message'=>__('No addons selected to delete','mgm'));		
		// addons
		if(isset($addons)){
			// in 
			$addons_in = mgm_map_for_in($addons);
			// sql			
			$affected = $wpdb->query("DELETE FROM `" . TBL_MGM_ADDON . "` WHERE `id` IN ($addons_in)");
			// execute
			if( $affected ) {
				// options
				$wpdb->query("DELETE FROM `" . TBL_MGM_ADDON_OPTION . "` WHERE `addon_id` IN ($addons_in)");
				// response 
				$response['status']  = 'success';
				$response['message'] = sprintf('%d %s', $affected, _n( 'Addon successfully deleted.', 'Addons successfully deleted.', $affected, 'mgm' ));	
			}else {
				$response['message'] = _n( 'Error while deleting selected addon.', 'Error while deleting selected addons.', count($addons), 'mgm' );
			}
		}					
		// return 
		return $response;	
	}	
	
	// save option
	private function _save_addon_options($addon_id, $addon_options, $action='insert'){
		global $wpdb;	
		// affected
		$affected = 0;
		// update
		if($action == 'update'){
			// delete options
			$affected = $wpdb->query($wpdb->prepare("DELETE FROM `" . TBL_MGM_ADDON_OPTION . "` WHERE `addon_id` = %d", $addon_id));
		}		
		// add
		if(count($addon_options) > 0){
			foreach($addon_options as $addon_option){
				$affected = $wpdb->insert(TBL_MGM_ADDON_OPTION, array('addon_id'=>$addon_id,'option'=>$addon_option['option'],'price'=>$addon_option['price']));
			}
		}
		// return 
		return $affected;
	}	
 }	
// return name of class 
return basename(__FILE__, '.php');
// end file /core/admin/mgm_admin_addons.php	