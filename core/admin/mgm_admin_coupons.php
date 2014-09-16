<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin coupons module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin_coupons extends mgm_controller{
 	
	// construct
	function __construct()
	{		
		$this->mgm_admin_coupons();
	}
	
	// construct php4
	function mgm_admin_coupons()
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
		$this->load->template('coupons/index', array('data'=>$data));
	}
		
	// list
	function lists(){
		global $wpdb;	
		// pager
		$pager = new mgm_pager();					
		// data
		$data = array();		
		// search fields
		$data['search_fields'] = array(''=> __('Select','mgm'), 'id'=> __('ID','mgm'), 'name'=> __('Code','mgm'), 
									   'description'=> __('Description','mgm'), 
		                               'create_dt' => __('Created','mgm'), 'expire_dt'=> __('Expires','mgm'));
									   
		// sort fields							  
		$data['sort_fields'] = array('id'=> __('ID','mgm'), 'name'=> __('Code','mgm'), 'create_dt'=> __('Created','mgm'),
									 'expire_dt'=> __('Expires','mgm'));										   

		// filter
		$sql_filter = $data['search_field_name'] = $data['search_field_value'] = '';		

		// check
		if(isset($_POST['search_field_name'])) {
			// issue#: 219
			$search_field_name  = $_POST['search_field_name']; // for sql			
			$search_field_value = mgm_escape($_POST['search_field_value']);// for sql
			// view data	
			$data['search_field_name'] 	= $_POST['search_field_name'];						
			$data['search_field_value'] = htmlentities($_POST['search_field_value'], ENT_QUOTES, "UTF-8");// for display
			// by field
			switch($search_field_name){
				case 'id':
					$sql_filter = " AND `id` = '".(int)$search_field_value."'";	
				break;
				case 'name':
					$sql_filter = " AND `name` LIKE '%{$search_field_value}%'";			
				break;	
				case 'description':
					$sql_filter = " AND `description` LIKE '%{$search_field_value}%'";				
				break;	
				case 'create_dt':
					// convert 
					$search_field_value = mgm_format_inputdate_to_mysql($search_field_value);	
					// set filter				
					$sql_filter = " AND DATE_FORMAT(`create_dt`,'%Y-%m-%d') = '{$search_field_value}'";
				break;	
				case 'expire_dt':
					// convert 
					$search_field_value = mgm_format_inputdate_to_mysql($search_field_value);	
					// set filter				
					$sql_filter = " AND DATE_FORMAT(`expire_dt`,'%Y-%m-%d') = '{$search_field_value}'";
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
		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM `" . TBL_MGM_COUPON."` WHERE 1 {$sql_filter} {$sql_order_by} {$sql_limit}";		
		// coupons
		$data['coupons'] = $wpdb->get_results($sql);	
		// log
		// mgm_log($wpdb->last_query, __FUNCTION__);
		// page url
		$data['page_url']   = 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons&method=lists';
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
		$data['message'] = sprintf(__('%d %s matched %s', 'mgm'), $data['row_count'], ($data['row_count']>1 ? __('coupons', 'mgm') : __('coupon', 'mgm')), $search_term);	
		
		// load template view
		$this->load->template('coupons/lists', array('data'=>$data));		
	}
	
	// add
	function add(){	
		global $wpdb;	
		extract($_POST);
		
		// save
		if(isset($save_coupon)){
			// response
			$response = array('status'=>'error', 'message'=>__('Coupon create failed, Unknown error!', 'mgm'));
			// check duplicate
			if( mgm_is_duplicate(TBL_MGM_COUPON, array('name'))  && $coupon_choice =='single'){
				// set message
				$response['message'] = sprintf(__('Error while creating new coupon: %s, same code exists!', 'mgm'), $name);
			}else{
				// fields
				$fields = array('name','value','description','use_limit','used_count','product','expire_dt','create_dt');
				// colums
				$column_data = array();
				// create value
				$value = $this->_set_value();
				// use limit
				if(isset($use_limit) && is_numeric($use_limit)){
					$use_limit = (int)$use_limit;
				}
				// product
				if(isset($product))	$product = json_encode($product);
				// expire dt format
				if(isset($expire_dt) && !empty($expire_dt)){					
					$expire_dt = date('Y-m-d H:i:s', strtotime(mgm_format_inputdate_to_mysql($expire_dt, mgm_get_date_format('date_format_short'))));
				}else{
					$expire_dt = NULL;
				}
				// create_dt
				$create_dt = date('Y-m-d H:i:s');
				
				if($coupon_choice == 'single'){
					// loop
					foreach($fields as $field){
						// check
						if (isset( ${$field} ) && !is_null( ${$field} ) ){
							$column_data[$field] = trim( ${$field} ); 
						}
					}					
					// save
					if ( $wpdb->insert(TBL_MGM_COUPON, $column_data) ) {
						$response = array('status'=>'success', 'message'=>sprintf(__('Successfully created new coupon: "%s"', 'mgm'), $name));
					}else{
						$response = array('status'=>'error', 'message'=>sprintf(__('Error while creating new coupon: "%s"', 'mgm'), $name));
					}
				}else {
					//number of coupons 
					$total_coupons = $name;					
					$sucess_coupons = 0;
					$duplicate_coupons = 0;
					// genrating coupons
					for ($i=1; $i<= $total_coupons; $i++) {												
						
						$name  = substr(md5(uniqid(mt_rand())), 0, 8);																	
						$source = array('name'=>$name);
						
						if(mgm_is_duplicate(TBL_MGM_COUPON, array('name'),'',$source )){
							$duplicate_coupons ++;
						}else {	
							// loop
							foreach($fields as $field){
								// check
								if (isset( ${$field} ) && !is_null( ${$field} ) ){
									$column_data[$field] = trim( ${$field} ); 
								}
							}
							// save
							if ( $wpdb->insert(TBL_MGM_COUPON, $column_data) ) {
								$sucess_coupons ++;
							}
						}						
					}					
					if(($total_coupons - $duplicate_coupons) > 0) {
						$response = array('status'=>'success', 'message'=>sprintf(__('Successfully created "%s" new coupons: ', 'mgm'), $sucess_coupons));
					}else {
						$response = array('status'=>'error', 'message'=>sprintf(__('Error while creating new coupons ', 'mgm')));
					}					
				}
			}	
			// return response
			echo json_encode($response); exit();
		}	
		
		// data
		$data = array();
		// parse
		$data['value_is'] = $this->_get_value();
		// currency
		$data['currency'] = mgm_get_class('system')->setting['currency'];	
		// load template view
		$this->load->template('coupons/add', array('data'=>$data));		
	}	
	
	// edit
	function edit(){	
		global $wpdb;	
		extract($_POST);
		
		// save
		if(isset($save_coupon)){
			// response
			$response = array('status'=>'error', 'message'=>__('Coupon update failed, Unknown error!', 'mgm'));
			// check duplicate
			if( mgm_is_duplicate(TBL_MGM_COUPON, array('name'), "id <> '{$id}'") ){
				$response['message'] = sprintf(__('Error while updating coupon: %s, same code exists!', 'mgm'),  $name);
			}else{
				// fields
				$fields = array('name','value','description','use_limit','used_count','product','expire_dt');
				// colums
				$column_data = $column_null_data = array();
				// create value
				$value = $this->_set_value();
				// use limit
				if(isset($use_limit) && is_numeric($use_limit)){
					$use_limit = (int)$use_limit;
				}else{
					$use_limit = NULL;
				}
				// product
				if(isset($product)){	
					$product = json_encode($product);
				}else{
					$product = NULL;
				}	
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
				if( $wpdb->update(TBL_MGM_COUPON, $column_data, array('id'=>$id)) ){
					$affected++;
				}				
				// null
				if(!empty($column_null_data)){
					// column_data2					
					$column_data2_a = mgm_implode_a(',', array_keys($column_null_data), array_values($column_null_data));
					// update
					if( $wpdb->query( "UPDATE `" . TBL_MGM_COUPON . "` SET {$column_data2_a} WHERE id='{$id}' ") ){
						$affected++;
					}
				}				
				// save				
				if ( $affected ) {
					$response = array('status'=>'success', 'message'=>sprintf(__('Successfully updated coupon: "%s"', 'mgm'), $name));					
				}else{
					$response = array('status'=>'error', 'message'=>sprintf(__('Error while updating coupon: "%s"', 'mgm'), $name));					
				}
			}	
			// return response
			echo json_encode($response); exit();
		}	
		
		// data
		$data = array();
		// coupon
		$data['coupon'] = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".TBL_MGM_COUPON."` WHERE id=%d", $id));
		// parse
		$data['value_is'] = $this->_get_value($data['coupon']->value);
		// currency
		$data['currency'] = mgm_get_class('system')->setting['currency'];	
		// load template view
		$this->load->template('coupons/edit', array('data'=>$data));		
	}	
	
	// delete 
	function delete(){
		global $wpdb;	
		extract($_POST);		
		// sql
		$sql = $wpdb->prepare("DELETE FROM `" . TBL_MGM_COUPON . "` WHERE id = %d", $id);
 		
		// delete
	    if ($wpdb->query($sql)) {    	    
			$message = __('Successfully deleted coupon: ', 'mgm');
			$status  = 'success';
	    }else{
			$message = __('Error while deleting coupon: ', 'mgm');
			$status  = 'error';
		}
		// return response
		echo json_encode(array('status'=>$status, 'message'=>$message));
	}
	
	// users
	function users(){
		global $wpdb;	
		extract($_POST);
		// data
		$data = array();
		// coupon
		$data['coupon'] = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".TBL_MGM_COUPON."` WHERE id=%d", $id ));
		//user
		$meta_query = array(
							array('key'=>'_mgm_user_register_coupon','value'=>$id,'compare'=>'=','type'=>'UNSIGNED'),
					        array('key'=>'_mgm_user_upgrade_coupon','value'=>$id,'compare'=>'=','type'=>'UNSIGNED'),
					        array('key'=>'_mgm_user_extend_coupon','value'=>$id,'compare'=>'=','type'=>'UNSIGNED'),
					        array('key'=>$id.'_mgm_user_upgrade_coupon','value'=>$id,'compare'=>'=','type'=>'UNSIGNED'),
					        array('key'=>$id.'_mgm_user_extend_coupon','value'=>$id,'compare'=>'=','type'=>'UNSIGNED')
					   		);
		//users
		$data['users'] = array();
		//check
		foreach($meta_query as $key) {
			$data['users'] =  array_merge($data['users'], mgm_get_users_with_meta( array($key), null, null, null)); //'OR' );
		}		
		// load template view
		$this->load->template('coupons/users', array('data'=>$data));
	}	

/*	function users(){

		global $wpdb;	

		extract($_POST);

		// data

		$data = array();

		// coupon

		$data['coupon'] = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".TBL_MGM_COUPON."` WHERE id=%d", $id ));

		//users

		$data['users'] = mgm_coupon_used_users($id);		

		// load template view

		$this->load->template('coupons/users', array('data'=>$data));

	}*/
	
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
	
	// manage coupons
	function manage_coupons(){	
		// data
		$data = array();		
		extract($_POST);		
		//import
		if(isset($coupon_action) && $coupon_action =='import'){
			if(isset($import_file)){
				$response = $this->_do_import($import_file);
				// print
				echo json_encode($response);
				exit();
				
			}
		}
		//export
		if(isset($coupon_action) && $coupon_action =='export'){
			$response = $this->_do_export($export_format,$export_option);
			// print
			echo json_encode($response);
			exit();
		}
		//supported files
		$data['filetypes'] 	  = array('csv' => 'CSV','xls' => 'XLS');
		$data['import_limit'] = 2000;				
		// load template view
		$this->load->template('coupons/manage_coupons', array('data'=>$data));		
	}

	// upload process for imports
	function import_file_upload(){		
		// file
		$file_element = 'import_file';
		// init
		$filedata = array();
		// init messages
		$status  = 'error';	
		$message = __('import file upload failed.','mgm');
		// upload check
		if (is_uploaded_file($_FILES[$file_element]['tmp_name'])) {
			// random filename
			$uniquename = substr(microtime(),2,8);
			// paths
			$oldname    = strtolower($_FILES[$file_element]['name']);
			$newname    = preg_replace('/(.*)\.(csv)$/i', $uniquename.'.$2', $oldname);
			$filepath   = MGM_FILES_IMPORT_DIR . $newname;
			// upload
			if(move_uploaded_file($_FILES[$file_element]['tmp_name'], $filepath)){
				// file				
				$import_file  = array('name' => $newname, 'path' => MGM_FILES_IMPORT_DIR . $newname);	
				// status
				$status  = 'success';	
				$message = sprintf(__('Import file [%s] uploaded successfully, please hit the Import Coupons button to start importing coupons.','mgm'),$newname);
			}
		}	
		// send ouput		
		@ob_end_clean();	
		// print
		echo json_encode(array('status'=>$status,'message'=>$message, 'file'=>$import_file,'post'=>$_POST));
		// end out put			
		@ob_flush();
		exit();
	}	

	// private ---------------------------------------
	// set value string from post data
	function _set_value(){
		//extract
		extract($_POST);
		// init
		$value = '';
		// options
		switch($value_is['options']){
			case 'flat':
				$value = (float)$value_is['flat'];
			break;
			case 'percent':
				$value = (float)$value_is['percent']. '%';
			break;
			case 'sub_pack_bc':// with billing cycle
			case 'sub_pack':// without billing cycle
				// format: 'sub_pack#5_6_M_pro-membership'   -- without billing cycle
				// format: 'sub_pack#5_6_M_pro-membership_0' -- with billing cycle
				// pack post
				$sub_pack = $value_is['sub_pack'];
				// lifetime
				if($sub_pack['duration_type'] == 'l') $sub_pack['duration_unit'] = 1;
				// membership_type
				$sub_pack['membership_type'] = strtolower(preg_replace('/[\_]+/', '-', $sub_pack['membership_type']));				
				// array
				$options = array((float)$sub_pack['cost'], (int)$sub_pack['duration_unit'], $sub_pack['duration_type'], $sub_pack['membership_type']);								 
				// billing cycle
				if(isset($sub_pack['num_cycles'])){
					// num_cycles, 2 is limited
					if((int)$sub_pack['num_cycles'] == 2) $sub_pack['num_cycles'] = (int)$sub_pack['num_cycles_limited'];	
					// append
					$options[] = $sub_pack['num_cycles'];
				}			 
				// set
				$value = 'sub_pack#'. implode('_',  $options);	
			break;
			case 'sub_pack_trial':
				// format: sub_pack_trial#5_6_M_1
				// pack post
				$sub_pack = $value_is['sub_pack_trial'];
				// lifetime
				if($sub_pack['duration_type'] == 'l') $sub_pack['duration_unit'] = 1;				
				// array
				$options = array((float)$sub_pack['cost'], (int)$sub_pack['duration_unit'], $sub_pack['duration_type'], (int)$sub_pack['num_cycles']);
				// set
				$value = 'sub_pack_trial#'. implode('_',  $options);
			break;
		}
		// return 
		return $value;
	}
	
	// get value array from value string
	function _get_value($value=NULL){
		// init
		$value_is = array();
		// default
		$value_is['options'] = 'flat';
		$value_is['flat'] = '';
		$value_is['percent'] = '';
		$value_is['sub_pack']['cost'] = '';
		$value_is['sub_pack']['duration_unit'] = 1;		
		$value_is['sub_pack']['duration_type'] = 'd';
		$value_is['sub_pack']['membership_type'] = '';
		$value_is['sub_pack']['num_cycles'] = 0;
		$value_is['sub_pack']['num_cycles_limited'] = 99;			                  
		$value_is['sub_pack_trial']['cost'] = '';	
		$value_is['sub_pack_trial']['duration_unit'] = 1;	
		$value_is['sub_pack_trial']['duration_type'] = 'd';			
		$value_is['sub_pack_trial']['num_cycles'] = 1;
		// get 
		if(!is_null($value)){
			// parse
			$value_is['options'] = mgm_get_coupon_type($value);
			// mgm_log($value_is['options'], __FUNCTION__);
			// set
			switch($value_is['options']){
				case 'flat':
					// values
					$values = mgm_get_coupon_values('flat', $value);
					// set
					$value_is['flat'] = $values['value'];
				break;
				case 'percent':
					// values
					$values = mgm_get_coupon_values('percent', $value);
					// set
					$value_is['percent'] = $values['value'];
				break;
				case 'sub_pack_bc':// with billing cycle
				case 'sub_pack':// without billing cycle
					// values
					$values = mgm_get_coupon_values('sub_pack', $value);
					// mgm_log($values, __FUNCTION__);
					// set value
					$value_is['sub_pack']['cost']            = $values['new_cost'];
					$value_is['sub_pack']['duration_unit']   = $values['new_duration'];
					$value_is['sub_pack']['duration_type']   = strtolower($values['new_duration_type']);
					$value_is['sub_pack']['membership_type'] = strtolower(str_replace('-', '_', $values['new_membership_type']));
					// billing cycle
					if(isset($values['new_num_cycles'])){
						// options
						$value_is['options'] = 'sub_pack_bc';
						// limited cycle
						if($values['new_num_cycles'] > 1){
							$value_is['sub_pack']['num_cycles'] = 2;
							$value_is['sub_pack']['num_cycles_limited'] = $values['new_num_cycles'];
						}else{
						// ongoing or one-time
							$value_is['sub_pack']['num_cycles'] = (int)$values['new_num_cycles'];
						}	
					}else{
						unset($value_is['sub_pack']['num_cycles']);// = 3;
					}				
				break;
				case 'sub_pack_trial':
					// values
					$values = mgm_get_coupon_values('sub_pack_trial', $value);							
					// set value			
					$value_is['sub_pack_trial']['cost']          = $values['new_cost'];
					$value_is['sub_pack_trial']['duration_unit'] = $values['new_duration'];
					$value_is['sub_pack_trial']['duration_type'] = strtolower($values['new_duration_type']);
					$value_is['sub_pack_trial']['num_cycles']    = $values['new_num_cycles'];
				break;
			}
		}	
		// return
		return $value_is;
	}
	
	// delete coupons
	private function _delete_multiple(){
		global $wpdb;	
		extract($_POST);
		// init
		$affected = 0;		
		$status  = 'error';
		$message = __('No coupons selected to delete','mgm');
		
		// coupons
		if(isset($coupons)){
			// in 
			$coupons_in = mgm_map_for_in($coupons);
			// sql
			$sql = "DELETE FROM `" . TBL_MGM_COUPON . "` WHERE `id` IN ($coupons_in)";
			// execute
			if($affected = $wpdb->query($sql)) {
				$status  = 'success';
				$message = sprintf('%d %s', $affected, _n( 'Coupon successfully deleted.', 'Coupons successfully deleted.', $affected, 'mgm' ));	
			}else {
				$message = _n( 'Error while deleting selected coupon.', 'Error while deleting selected coupons.', count($coupons), 'mgm' );				
			}
		}					
		// return 
		return array('status'=>$status, 'message'=>$message);	
	}	
/*	
	// export - depricated due to performance issues
	function _do_export($export_format,$export_option){		
		global $wpdb;
		
		$status = 'error';			
		$message = __('Error while exporting Coupons.', 'mgm');
		
		// all users	
		//$sql = 'SELECT`id`, `name` FROM `'.TBL_MGM_COUPON.'` WHERE `used_count` > 0 ORDER BY `create_dt` DESC';		
		// coupons
		//$coupons = $wpdb->get_results($sql);
		// users
		if($export_option !='unused')
			$users = mgm_get_all_userids(array('ID'), 'get_results');
		//array
		$export_coupons = array();
		//export options issue #1494
		if($export_option =='used'){
			$query = " AND `used_count` > 0";
		}elseif($export_option =='unused'){
			$query = " AND `used_count` IS NULL";
		}else {
			$query = '';
		}
		// count
		$total = $wpdb->get_var("SELECT COUNT(*) FROM `".TBL_MGM_COUPON."` WHERE 1 " . $query);//
		$perpage = 50;
		// check
		if( $total > $perpage ){
			$pages = ceil($total / $perpage);
		}else{
			$pages = 1;
		}
		// loop pages
		for($i=1;$i<=$pages;$i++){
			//@set_time_limit(300);//300s
			//@ini_set('memory_limit', 134217728);// 128M
			// offset
			$offset = ($i - 1) * $perpage;
			// coupons
			$sql = "SELECT `id`, `name` FROM `".TBL_MGM_COUPON."` WHERE 1 {$query} ORDER BY `create_dt` DESC LIMIT {$offset},{$perpage}";		
			// coupons
			$coupons = $wpdb->get_results($sql);
			// log
			// mgm_log('Page: ' .$i. '. '. $wpdb->last_query, __FUNCTION__);
			// loop
			foreach ($coupons as $coupon) {		

				// found
				if($users && $export_option !='unused'){
					
					$coupon_used = false;
					
					foreach ($users as $user){				
						if ($user->ID){			
							// member
							$member = mgm_get_member($user->ID);				
							// show
							$show = false;
							
							// check
							if( mgm_member_has_coupon($member, $coupon->id) ) {
								$show = true;
							}else{
								if( isset($member->other_membership_types) && !empty($member->other_membership_types)){
									// loop
									foreach ($member->other_membership_types as $key => $member_oth){
										// as object
										$o_mgm_member = mgm_convert_array_to_memberobj($member_oth, $user->ID);								
										// check
										if( mgm_member_has_coupon($o_mgm_member, $coupon->id) ) {
											$show = true; break;
										}
										// unset
										unset($o_mgm_member);
									}		
								}
							}				
							
							// include
							if($show){
								// user
								$user = get_userdata($user->ID)	;
								// int			
								$row = new stdClass();		
								// export fields
								$row->coupon_id             = $coupon->id ;
								$row->coupon_name           = $coupon->name ;
								$row->user_id  		        = $user->ID ;				
								$row->user_login            = $user->user_login ;				
								$row->user_email	        = $user->user_email ;				
								$row->user_membership_type	= ucwords(str_replace('_', ' ', $member->membership_type));				
								// cache
								$export_coupons[] = $row;	
								// unset 
								unset($row);
								$coupon_used = true;
							}
						}		 	
					}
					if(!$coupon_used && $export_option =='all'){
						// int			
						$row = new stdClass();		
						// export fields
						$row->coupon_id             = $coupon->id ;
						$row->coupon_name           = $coupon->name ;			
						$row->user_id  		        = '';				
						$row->user_login            = '';				
						$row->user_email	        = '';				
						$row->user_membership_type	= '';			
						
						// cache
						$export_coupons[] = $row;	
						// unset 
						unset($row);						
					}
				}else {
					// int			
					$row = new stdClass();		
					// export fields
					$row->coupon_id             = $coupon->id ;
					$row->coupon_name           = $coupon->name ;
					$row->user_id  		        = '';				
					$row->user_login            = '';				
					$row->user_email	        = '';				
					$row->user_membership_type	= '';								
					// cache
					$export_coupons[] = $row;	
					// unset 
					unset($row);						
				}
			}
		}
		// log
		// mgm_log($export_coupons, 'export_coupons');

		// check
		if (count($export_coupons)>0) {
			// success
			$success = count($export_coupons);
			// create
			if($export_format == 'csv'){
				$filename = mgm_create_csv_file($export_coupons, 'export_coupons');			
			}else{
				$filename = mgm_create_xls_file($export_coupons, 'export_coupons');			
			}
			// src
			$file_src = MGM_FILES_EXPORT_URL . $filename;				
			// message
			$message = sprintf(__('Successfully exported %d %s.', 'mgm'), $success, ($success > 1 ? 'coupons' : 'coupon'));
			//status
			$status  = 'success';
			//return
			return array('status'=>$status, 'message'=>$message,'action'=>'export','src'=>$file_src);			
		}else{
			$message .= ' ' .__('No coupons found with associated users.','mgm');
		}
		// return
		return array('status'=>$status, 'message'=>$message,'action'=>'export');			
	}
*/
	//export
	function _do_export($export_format,$export_option){		
		global $wpdb;
		
		$status = 'error';			
		$message = __('Error while exporting Coupons.', 'mgm');
		
		// users
		if($export_option !='unused') {
			//$users = mgm_get_all_userids(array('ID'), 'get_results');			
			$start = 0;
			$limit = 1000;
			//user meta fields
			$fields= array('user_id','meta_value');	
			// sql
			$user_sql = "SELECT count(*) FROM `{$wpdb->usermeta}` WHERE `meta_key` = 'mgm_member_options' AND `user_id` <> 1";	
			$count  = $wpdb->get_var($user_sql);
			
			$users = wp_cache_get('all_mgm_user_meta', 'users');						
			
			// if empty read from db:
			if(empty($users)) {
				$users = array();
				if($count) {			
					// read again
					for( $i = $start; $i < $count; $i = $i + $limit ) {						
						$users = array_merge($users, (array)mgm_patch_partial_user_member_options($i, $limit, $fields));
						//a small delay of 0.01 second 
						usleep(10000);					
					}
					wp_cache_set('all_mgm_user_meta', $users, 'users');
				}
			}			
		}
		//mgm_log(mgm_array_dump($users,true),__FUNCTION__);
		//array
		$export_coupons = array();
		//export options issue #1494
		if($export_option =='used'){
			$query = " AND `used_count` > 0";
		}elseif($export_option =='unused'){
			$query = " AND `used_count` IS NULL";
		}else {
			$query = '';
		}
		// count
		$total = $wpdb->get_var("SELECT COUNT(*) FROM `".TBL_MGM_COUPON."` WHERE 1 " . $query);//
		$perpage = 50;
		// check
		if( $total > $perpage ){
			$pages = ceil($total / $perpage);
		}else{
			$pages = 1;
		}
		// loop pages
		for($i=1;$i<=$pages;$i++){
			//@set_time_limit(300);//300s
			//@ini_set('memory_limit', 134217728);// 128M
			// offset
			$offset = ($i - 1) * $perpage;
			// coupons
			$sql = "SELECT `id`, `name` FROM `".TBL_MGM_COUPON."` WHERE 1 {$query} ORDER BY `create_dt` DESC LIMIT {$offset},{$perpage}";		
			// coupons
			$coupons = $wpdb->get_results($sql);
			// log
			// mgm_log('Page: ' .$i. '. '. $wpdb->last_query, __FUNCTION__);
			// loop
			foreach ($coupons as $coupon) {
				// found
				if($users && $export_option !='unused'){
					
					$coupon_used = false;
					
					foreach ($users as $user){				
						if ($user->user_id){			
							// member
							$user_id = $user->user_id;
							$member = unserialize($user->meta_value);				
							$member = mgm_convert_array_to_memberobj($member, $user_id);
							// show
							$show = false;							
							// check
							if( mgm_member_has_coupon($member, $coupon->id) ) {
								$show = true;
							}else{
								if( isset($member->other_membership_types) && 
									!empty($member->other_membership_types)  && 
									count($member->other_membership_types) > 0){
									// loop
									foreach ($member->other_membership_types as $key => $member_oth){
										// as object
										if(is_array($member_oth)) { 
											$o_mgm_member = mgm_convert_array_to_memberobj($member_oth, $user_id);
										}else if(is_object($member_oth)){
											$o_mgm_member = $member_oth;
										}
										// check
										if( mgm_member_has_coupon($o_mgm_member, $coupon->id) ) {
											$show = true; break;
										}
										// unset
										unset($o_mgm_member);
									}		
								}
							}				
							
							// include
							if($show){
								//mgm_log(mgm_array_dump($member,true),__FUNCTION__);
								// user
								$user = get_userdata($user_id)	;
								// int			
								$row = new stdClass();		
								// export fields
								$row->coupon_id             = $coupon->id ;
								$row->coupon_name           = $coupon->name ;
								$row->user_id  		        = $user->ID ;				
								$row->user_login            = $user->user_login ;				
								$row->user_email	        = $user->user_email ;				
								$row->user_membership_type	= ucwords(str_replace('_', ' ', $member->membership_type));				
								// cache
								$export_coupons[] = $row;	
								// unset 
								unset($row);
								$coupon_used = true;
							}
							unset($user);
							unset($member);							
						}		 	
					}
					if(!$coupon_used && $export_option =='all'){
						// int			
						$row = new stdClass();		
						// export fields
						$row->coupon_id             = $coupon->id ;
						$row->coupon_name           = $coupon->name ;			
						$row->user_id  		        = '';				
						$row->user_login            = '';				
						$row->user_email	        = '';				
						$row->user_membership_type	= '';			
						
						// cache
						$export_coupons[] = $row;	
						// unset 
						unset($row);						
					}
				}else {
					// int			
					$row = new stdClass();		
					// export fields
					$row->coupon_id             = $coupon->id ;
					$row->coupon_name           = $coupon->name ;
					$row->user_id  		        = '';				
					$row->user_login            = '';				
					$row->user_email	        = '';				
					$row->user_membership_type	= '';								
					// cache
					$export_coupons[] = $row;	
					// unset 
					unset($row);						
				}
			}
		}
		// log
		// mgm_log($export_coupons, 'export_coupons');

		// check
		if (count($export_coupons)>0) {
			// success
			$success = count($export_coupons);
			// create
			if($export_format == 'csv'){
				$filename = mgm_create_csv_file($export_coupons, 'export_coupons');			
			}else{
				$filename = mgm_create_xls_file($export_coupons, 'export_coupons');			
			}
			// src
			$file_src = MGM_FILES_EXPORT_URL . $filename;				
			// message
			$message = sprintf(__('Successfully exported %d %s.', 'mgm'), $success, ($success > 1 ? 'coupons' : 'coupon'));
			//status
			$status  = 'success';
			//return
			return array('status'=>$status, 'message'=>$message,'action'=>'export','src'=>$file_src);			
		}else{
			$message .= ' ' .__('No coupons found with associated users.','mgm');
		}
		// return
		return array('status'=>$status, 'message'=>$message,'action'=>'export');			
	}

	// import
	function _do_import($i_filepath=''){		
		global $wpdb;

		// init data
		$header = $coupons = array();
		
		$status = 'error';	
		
		$message = __('Error while importing.', 'mgm');											
				
		$extension = pathinfo($i_filepath, PATHINFO_EXTENSION);
		//mgm_log($extension);
		// uploaded ext
		switch (strtolower($extension)) {
			//CSV 
			case 'csv':			
				// read csv	
				if($handle = @fopen($i_filepath, 'r')){
					// loop
					while( $data = fgetcsv($handle,null,';')) {
						//get headers:
						if(empty($header)) {
							$header = $data; 
						}else {
							// $user_count++;
							// update rowws for empty cells:	
							$row = array();		
							// loop				
							foreach ($header as $key => $val) {									
								// create an array with header value as index:									
								$row[$val] = (!isset($data[$key])) ? '' : trim($data[$key]);
							}
							// set user
							$coupons[] = $row;
							// unset
							unset($row);
							// check limit reached:
							// if(($user_count+1) >= $row_limit) break;						
						}
						// unset
						unset($data);					
					}
					// close
					@fclose($handle);
				}					
			break;
		
		}					
		
		// fields
		$fields = array('name','value','description','use_limit','used_count','product','expire_dt','create_dt');
		$sucess_coupons = $duplicate_coupons = 0;
		
		if(!empty($coupons)) {
			
			foreach ($coupons as $coupon) {
				
				if(!isset($coupon['create_dt']) || empty($coupon['create_dt'])){
					// create_dt
					$coupon['create_dt'] = date('Y-m-d H:i:s');
				}
				
				$source = array('name'=>$coupon['name']);
				
				if(mgm_is_duplicate(TBL_MGM_COUPON, array('name'),'',$source )){
					$duplicate_coupons ++;
				}else {	
					// loop
					foreach($fields as $field){
						// check
						if (isset( $coupon[$field] ) && !is_null( $coupon[$field] ) ){
							$column_data[$field] = trim( $coupon[$field]); 
						}
					}
					// save
					if ( $wpdb->insert(TBL_MGM_COUPON, $column_data) ) {
						$sucess_coupons ++;
					}														
				}								
			}
		}
		
		if(isset($sucess_coupons) && $sucess_coupons > 0 ) {
			$status = 'success';	
			$message = sprintf(__('Import completed successfully. %d coupons imported', 'mgm'), $sucess_coupons);
		}
		// delete uploaded file:					
		if(file_exists($i_filepath)) {
			@unlink($i_filepath);
		}

		return array('status'=>$status, 'message'=>$message,'action'=>'import');	
		
	}
		
 }	
// return name of class 
return basename(__FILE__, '.php');
// end file /core/admin/mgm_admin_coupons.php