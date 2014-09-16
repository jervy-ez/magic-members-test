<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin downloads module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin_downloads extends mgm_controller{
 	
	// construct
	function __construct()
	{		
		$this->mgm_admin_downloads();
	}
	
	// construct php4
	function mgm_admin_downloads()
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
		$this->load->template('downloads/index', array('data'=>$data));
	}
	
	// lists
	function lists(){		
		global $wpdb;	
		// pager
		$pager = new mgm_pager();
		// data
		$data = array();	

		// search fields
		$data['search_fields'] = array(''=> __('Select','mgm'), 'id'=> __('ID','mgm'), 'title'=> __('Title','mgm'), 'filename'=> __('Filename','mgm'), 
		                               'post_date' => __('Posted','mgm'), 'expire_dt'=> __('Expires','mgm'));
									   
		// sort fields							  
		$data['sort_fields'] = array('id'=> __('ID','mgm'), 'title'=> __('Title','mgm'), 'post_date'=> __('Posted','mgm'),'expire_dt'=> __('Expires','mgm'));										   

		// filter
		$sql_filter = $data['search_field_name'] = $data['search_field_value'] = '';

		// check
		if(isset($_POST['search_field_name'])) {
			// issue#: 219
			$search_field_name  = $_POST['search_field_name']; // for sql			
			$search_field_value = mgm_escape($_POST['search_field_value']);// for sql
			// view data	
			$data['search_field_name'] 	= $_POST['search_field_name'];
			//issue #1281						
			$data['search_field_value'] = htmlentities($_POST['search_field_value'], ENT_QUOTES, "UTF-8");// for display
			// by field
			switch($search_field_name){
				case 'id':
					$sql_filter = " AND `id` = '".(int)$search_field_value."'";	
				break;
				case 'title':
					$sql_filter = " AND `title` LIKE '%{$search_field_value}%'";			
				break;	
				case 'filename':
					$sql_filter = " AND (`filename` LIKE '%{$search_field_value}%' OR `real_filename` LIKE '%{$search_field_value}%')";			
				break;	
				case 'post_date':
					// convert 
					$search_field_value = mgm_format_inputdate_to_mysql($search_field_value);	
					// set filter				
					$sql_filter = " AND DATE_FORMAT(`post_date`,'%Y-%m-%d') = '{$search_field_value}'";
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
		$sort_field = 'post_date';
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
		$data['page_limit'] = isset($_REQUEST['page_limit']) ? (int)$_REQUEST['page_limit'] : 10;// 10
		// page no
		$data['page_no'] = isset($_REQUEST['page_no']) ? (int)$_REQUEST['page_no'] : 1;		
		// limit
		$sql_limit = $pager->get_query_limit($data['page_limit']);
		//sql	
		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM `" . TBL_MGM_DOWNLOAD."` WHERE 1 {$sql_filter} {$sql_order_by} {$sql_limit}";		
		// downloads
		$data['downloads'] = $wpdb->get_results($sql);	
		// log
		// mgm_log($wpdb->last_query, __FUNCTION__);
		// page url
		$data['page_url']   = 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/downloads&method=lists';
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
		$data['message'] = sprintf(__('%d %s matched %s', 'mgm'), $data['row_count'], ($data['row_count']>1 ? __('downloads', 'mgm') : __('download', 'mgm')), $search_term);	
		
		// load view
		$this->load->template('downloads/lists', array('data'=>$data));
	}
	
	// add
	function add(){		
		global $wpdb;
		// trim
		array_map('trim', (array)$_POST);
		// extract
		extract($_POST);
		// system 
		$system_obj = mgm_get_class('system');				
		// save
		if(isset($submit_download)){
			// response
			$response = array('status'=>'error','message'=>sprintf(__('Error while creating new download <b>%s</b>!', 'mgm'), $title), 
			                  'download_hook'=>'download_hook');
			// check duplicate
			if(mgm_is_duplicate(TBL_MGM_DOWNLOAD,array('title'))){
				$response['message'] = sprintf(__('Error while creating new download %s, same title exists!', 'mgm'), $title);
			}else{
				// members_only
				$members_only = (isset($members_only)) ? 'Y' : 'N';
				// restrict_acces_ip
				$restrict_acces_ip = (isset($restrict_acces_ip)) ? 'Y' : 'N';
				// is_s3_torrent
				$is_s3_torrent = (isset($is_s3_torrent)) ? 'Y' : 'N';
				// filename
				$filename = (isset($download_file_new)) ? $download_file_new : $direct_url;
				// real name
				$real_filename = (isset($download_file_new_realname)) ? $download_file_new_realname: basename($filename);
				// filesize
				$filesize = mgm_file_get_size($filename);
				// post vars
				$post_date = date('Y-m-d H:i:s');
				// code
				$code = uniqid();	
				// user
				$current_user = wp_get_current_user();					
				// data			
				$data = array('title'=>$title, 'filename'=>$filename, 'real_filename'=>$real_filename, 'filesize'=>$filesize,
							  'post_date'=>$post_date, 'restrict_acces_ip'=>$restrict_acces_ip,'user_id'=>$current_user->ID, 
							  'members_only'=>$members_only, 'code'=>$code, 'is_s3_torrent'=>$is_s3_torrent);
				
				// download limit
				if(isset($download_limit) && (int)$download_limit>0){
					$data['download_limit'] = (int)$download_limit;
				}
				// expire date
				if(isset($expire_dt) && !empty($expire_dt)) {
					$data['expire_dt'] = mgm_format_inputdate_to_mysql($expire_dt);
				}	
				// insert
				if ($wpdb->insert(TBL_MGM_DOWNLOAD, $data)) {				
					// id																																																																								
					if ($id = $wpdb->insert_id) {
						// assoc
						if( bool_from_yn($members_only) ){
							// set
							if (isset($link_to_post_id)) {
								// loop
								foreach ($link_to_post_id as $post_id) {
									// insert
									$wpdb->insert(TBL_MGM_DOWNLOAD_POST_ASSOC, array('download_id'=>$id, 'post_id'=>$post_id));
								}
							}
						}
						//retun id post writing screen only
						if($submit_download){
							$response['download_id'] = $id;
						}
					}
					// set message
					$response['download_hook'] = $system_obj->get_setting('download_hook', 'download');
					$response['message'] = sprintf(__('Download created successfully  <b>%s</b>', 'mgm'), $title);						
					// set status
					$response['status'] = 'success';				
				}
			}	
			// return response			
			echo json_encode($response); exit();
		}	
		
		// data
		$data = array();		
		// get all post types	
		$post_types = mgm_get_post_types();
		
		//get all published posts - issue #1034
		$all_posts = mgm_field_values( $wpdb->posts, 'ID', 'SUBSTR(post_title,1, 100) AS post_title', "AND `post_status` ='publish' AND `post_type` IN ($post_types)", 'post_title');			
		
		//get all scheduled posts
		$scheduled_posts = mgm_field_values( $wpdb->posts, 'ID', 'SUBSTR(post_title,1, 100) AS post_title', "AND `post_status` ='future' AND `post_type` IN ($post_types)", 'post_title');			
		foreach ($scheduled_posts as $k =>$scheduled_post)
			$all_posts[$k] = $scheduled_post .'(S)';

		//get all draft posts				
		$draft_posts = mgm_field_values( $wpdb->posts, 'ID', 'SUBSTR(post_title,1, 100) AS post_title', "AND `post_status` ='draft' AND `post_type` IN ($post_types)", 'post_title');
		foreach ($draft_posts as $k =>$draft_post)
			$all_posts[$k] = $draft_post .'(D)';
		
		//sort by post name	
		asort($all_posts);
		
		//all posts
		$data['posts'] = $all_posts;

		// load template view
		$this->load->template('downloads/add', array('data'=>$data));
	}
	
	// edit
	function edit(){		
		global $wpdb;	
		// trim
		array_map('trim', $_POST);
		// extract
		extract($_POST);
		// system 
		$system_obj = mgm_get_class('system');		
		// save
		if(isset($submit_download)){
			// response
			$response = array('status'=>'error','message'=>sprintf(__('Error while updating download <b>%s</b>!', 'mgm'), $title));
			// check duplicate
			if(mgm_is_duplicate(TBL_MGM_DOWNLOAD,array('title'),"id <> '{$id}'")){
				$response['message'] = sprintf(__('Error while updating download <b>%s</b>, title exists!', 'mgm'), $title);
			}else{
				// set vars
				$members_only = (isset($members_only)) ? 'Y' : 'N';
				// set vars
				$restrict_acces_ip = (isset($restrict_acces_ip)) ? 'Y' : 'N';	
				// is_s3_torrent
				$is_s3_torrent = (isset($is_s3_torrent)) ? 'Y' : 'N';			
				// filename
				$filename = (isset($download_file_new)) ? $download_file_new : $direct_url;
				// real name
				$real_filename = (isset($download_file_new_realname)) ? $download_file_new_realname: basename($filename);
				// filesize
				$filesize = mgm_file_get_size($filename);
				// post vars
				$post_date = date('Y-m-d H:i:s');
				// user
				$current_user = wp_get_current_user();	
				// data			
				$data = array('title'=>$title, 'filename'=>$filename, 'real_filename'=>$real_filename, 'filesize'=>$filesize,
							  'post_date'=>$post_date, 'restrict_acces_ip'=>$restrict_acces_ip,'user_id'=>$current_user->ID, 
							  'members_only'=>$members_only, 'is_s3_torrent'=>$is_s3_torrent);
				// null
				$null_columns = array();
							  
				// download limit
				if(isset($download_limit) && (int)$download_limit>0){
					$data['download_limit'] = (int)$download_limit;
				}else{
					$null_columns[] = "`download_limit` = NULL ";
				}
				
				// expire date
				if(isset($expire_dt) && !empty($expire_dt)) {
					$data['expire_dt'] = mgm_format_inputdate_to_mysql($expire_dt);
				}else{
					$null_columns[] = "`expire_dt` = NULL ";
				}
					
				// code
				if(!isset($code) || (isset($code) && empty($code))) {
					$data['code'] = uniqid();
				}		
				
				// update
				if ($wpdb->update(TBL_MGM_DOWNLOAD, $data, array('id' => $id))) {					
					// update null
					if(count($null_columns) > 0){
						// join
						$set_string = implode(',', $null_columns);
						// clear old				
						$wpdb->query($wpdb->prepare('UPDATE `' . TBL_MGM_DOWNLOAD . '` SET ' . $set_string . ' WHERE `id` = %d', $id));
					}
					// clear old				
					$wpdb->query($wpdb->prepare('DELETE FROM `' . TBL_MGM_DOWNLOAD_POST_ASSOC . '` WHERE `download_id` = %d', $id));
					// save 		
					if( bool_from_yn($members_only) ){		
						if (isset($link_to_post_id)) {
							// loop
							foreach ($link_to_post_id as $post_id) {
								// insert
								$wpdb->insert(TBL_MGM_DOWNLOAD_POST_ASSOC, array('download_id'=>$id, 'post_id'=>$post_id));
							}
						}			
					}
					// set message
					$response['message'] = sprintf(__('Download updated successfully <b>%s</b>', 'mgm'), $title);
					$response['status']  = 'success';				
				}else{
					$response['message'] = sprintf(__('Error while updating download <b>%s</b> Or nothing updated!', 'mgm'), $title);					
				}
			}	
			// return response			
			echo json_encode($response);				
			exit();
		}	
		
		// data
		$data = array();
		// download		
		$data['download'] = $wpdb->get_row($wpdb->prepare("SELECT * FROM `" . TBL_MGM_DOWNLOAD . "` WHERE id = %d", $id));		
		// download_posts
		$data['download_posts'] = mgm_get_download_post_ids($id);		
		// get all post types	
		$post_types = mgm_get_post_types();

		//get all published posts - issue #1034
		$all_posts = mgm_field_values( $wpdb->posts, 'ID', 'SUBSTR(post_title,1, 100) AS post_title', "AND `post_status` ='publish' AND `post_type` IN ($post_types)", 'post_title');			
		
		//get all scheduled posts
		$scheduled_posts = mgm_field_values( $wpdb->posts, 'ID', 'SUBSTR(post_title,1, 100) AS post_title', "AND `post_status` ='future' AND `post_type` IN ($post_types)", 'post_title');			
		foreach ($scheduled_posts as $k =>$scheduled_post)
			$all_posts[$k] = $scheduled_post .'(S)';

		//get all draft posts				
		$draft_posts = mgm_field_values( $wpdb->posts, 'ID', 'SUBSTR(post_title,1, 100) AS post_title', "AND `post_status` ='draft' AND `post_type` IN ($post_types)", 'post_title');
		foreach ($draft_posts as $k =>$draft_post)
			$all_posts[$k] = $draft_post .'(D)';
		
		//sort by post name	
		asort($all_posts);
		
		//all posts
		$data['posts'] = $all_posts;
		
		// hook
		$data['download_hook'] = $system_obj->get_setting('download_hook','download');	
		// slug			
		$data['download_slug'] = $system_obj->get_setting('download_slug', 'download');				
		// load template view
		$this->load->template('downloads/edit', array('data'=>$data));
	}
	
	// delete
	function delete(){
		global $wpdb;	
		// extract
		extract($_POST);		
		
		// get file name
		$filename = $wpdb->get_var($wpdb->prepare("SELECT `filename` FROM `" . TBL_MGM_DOWNLOAD . "` WHERE id = %d", $id));
		// check s3
		if(!mgm_is_s3_file($filename)){		
			// delete file if locally stored
			mgm_delete_file(MGM_FILES_DOWNLOAD_DIR . basename($filename));
		}
		// delete		
		$wpdb->query($wpdb->prepare('DELETE FROM `' . TBL_MGM_DOWNLOAD . '`	WHERE id = %d', $id));
		$wpdb->query($wpdb->prepare('DELETE FROM `' . TBL_MGM_DOWNLOAD_POST_ASSOC . '` WHERE `download_id` = %d', $id));				
		$wpdb->query($wpdb->prepare('DELETE FROM `' . TBL_MGM_DOWNLOAD_LIMIT_ASSOC . '` WHERE `download_id` = %d', $id));
		// return response		
		echo json_encode(array('status'=>'success', 'message'=>__('Download deleted successfully','mgm')));			
		exit();
	}
	
	// file upload
	function file_upload(){		
		// init response
		$response = array('status' => 'error', 'message' => __('file upload failed', 'mgm'));
		// upload, using helper
		if($file_info = mgm_save_file_for_download('download_file')){			
			// response
			$response = array('status'=>'success','message'=>__('file uploaded successfully, it will be attached when you save the data.','mgm'), 'file_info'=>$file_info);	
		}
		// send ouput		
		@ob_end_clean();	
		// print
		echo json_encode($response);
		// end out put			
		@ob_flush();
		// exit
		exit();
	}
	
	// editor
	function editor(){
	 	global $wpdb;
		// init
		$data = array();			
		// hook
		$data['download_hook'] = mgm_get_class('system')->get_setting('download_hook','download');		
		// downloads
		$data['downloads'] = $wpdb->get_results("SELECT  * FROM `" . TBL_MGM_DOWNLOAD . "`");	
		// load template view
		$this->load->template('downloads/editor_list', array('data'=>$data));		
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
	
	// private ----------------------------------------------------------
	// delete multiple
	private function _delete_multiple(){
		global $wpdb;	
		extract($_POST);
		// init
		$affected = 0;		
		$response = array('status'=>'error','message'=>__('No downloads selected to delete','mgm'));		
		// downloads
		if(isset($downloads)){
			// in 
			$downloads_in = mgm_map_for_in($downloads);
			// sql			
			$affected = $wpdb->query("DELETE FROM `" . TBL_MGM_DOWNLOAD . "` WHERE `id` IN ($downloads_in)");
			// execute
			if( $affected ) {
				// post assoc				
				$wpdb->query("DELETE FROM `" . TBL_MGM_DOWNLOAD_POST_ASSOC . "` WHERE `download_id` IN ($downloads_in)");
				// limit assoc				
				$wpdb->query("DELETE FROM `" . TBL_MGM_DOWNLOAD_LIMIT_ASSOC . "` WHERE `download_id` IN ($downloads_in)");
				// attributes				
				// $wpdb->query("DELETE FROM `" . TBL_MGM_DOWNLOAD_ATTRIBUTE . "` WHERE `download_id` IN ($downloads_in)");
				// attribute types
				// $wpdb->query("DELETE FROM `" . TBL_MGM_DOWNLOAD_ATTRIBUTE_TYPE . "` WHERE `download_id` IN ($downloads_in)");
				// response 
				$response['status']  = 'success';
				$response['message'] = sprintf('%d %s', $affected, _n( 'Download successfully deleted.', 'Downloads successfully deleted.', $affected, 'mgm' ));	
			}else {
				$response['message'] = _n( 'Error while deleting selected download.', 'Error while deleting selected download.', count($downloads), 'mgm' );
			}
		}					
		// return 
		return $response;	
	}
	//display list of shortcodes in post window
	function shortcodes(){
	 	global $wpdb;
		// init
		$data = array();

		$data['protect_shortcodes'] =array(	'private'=>'[private]',
											'private_or'=>'[private_or]',
											'private_and'=>'[private_and]',
											'user_account_is'=>'[user_account_is]',
											'user_id_is'=>'[user_id_is]',
											'no_access'=>'[no_access]',
											'user_has_access'=>'[user_has_access]');
				
		$data['purchase_shortcodes'] =array('subscription_packs'=>'[subscription_packs]',	
											'user_upgrade'=>'[user_upgrade]',
											'payperpost'=>'[payperpost]',
											'payperpost_pack'=>'[payperpost_pack]',
											'user_purchase_another_membership'=>'[user_purchase_another_membership]',
											'membership_extend_link'=>'[membership_extend_link]');
		
		$data['other_shortcodes'] =	array(	'user_profile'=>'[user_profile]',
											'user_register'=>'[user_register]',
											'user_list'=>'[user_list]',
											'user_payment_history'=>'[user_payment_history]',
											'membership_contents'=>'[membership_contents]',
											'membership_details'=>'[membership_details]',
											'user_subscription'=>'[user_subscription]',
											'user_other_subscriptions'=>'[user_other_subscriptions]',
											'user_contents_by_membership'=>'[user_contents_by_membership]',
											'posts_for_membership'=>'[posts_for_membership]',
											'user_facebook_login'=>'[user_facebook_login]',
											'user_login'=>'[user_login]',
											'logout_link'=>'[logout_link]',
											'lost_password'=>'[lost_password]');
							
		
			
		// load template view
		$this->load->template('shortcodes/shortcodes_list', array('data'=>$data));			
	}		
 }
// return name of class 
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin_downloads.php 