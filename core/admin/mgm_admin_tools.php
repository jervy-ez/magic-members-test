<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin tools module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin_tools extends mgm_controller{
 	
	// construct
	function __construct()
	{		
		$this->mgm_admin_tools();
	}
	
	// construct php4
	function mgm_admin_tools()
	{		
		// load parent
		parent::__construct();
	}
	
	// index
	function index(){
		// data
		$data = array();
		// load template view
		$this->load->template('tools/index', array('data'=>$data));		
	}
	
	// data migrate
	function data_migrate(){				
		global $wpdb;	
		// local
		extract($_POST);
				
		// execute
		if(isset($migrate_execute) && !empty($migrate_execute)){
			// execute
			echo $this->_do_data_migrate();		
			// exit
			exit();
		}
		
		// data
		$data = array();
		// system
		$data['system_obj'] 	  = mgm_get_class('system');
		$data['filetypes'] 	  = array('csv' => 'CSV','xls' => 'XLS');
		$data['import_limit'] = 2000;
		// load template view
		$this->load->template('tools/data_migrate', array('data'=>$data));		
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
			$newname    = preg_replace('/(.*)\.(xml)$/i', $uniquename.'.$2', $oldname);
			$filepath   = MGM_FILES_IMPORT_DIR . $newname;
			// upload
			if(move_uploaded_file($_FILES[$file_element]['tmp_name'], $filepath)){
				// file				
				$import_file  = array('name' => $newname, 'path' => MGM_FILES_IMPORT_DIR . $newname);	
				// status
				$status  = 'success';	
				$message = sprintf(__('Import file [%s] uploaded successfully, please hit the MIGRATE button to start migration.','mgm'),$newname);
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
	
	// import users upload
	function importusers_file_upload() {
		// file
		$file_element = 'import_users';
		// init
		$filedata = array();
		// init messages
		$status  = 'error';	
		$newname = '';
		$message = __('import file upload failed.','mgm');
		// upload check
		if (is_uploaded_file($_FILES[$file_element]['tmp_name'])) {
			// random filename
			$uniquename = substr(microtime(),2,8);
			// paths
			$oldname    = strtolower($_FILES[$file_element]['name']);
			$newname    = preg_replace('/(.*)\.(csv|xml)$/i', $uniquename.'.$2', $oldname);
			$filepath   = MGM_FILES_IMPORT_DIR . $newname;
			// upload
			if(move_uploaded_file($_FILES[$file_element]['tmp_name'], $filepath)){
				// chmod
				@chmod($filepath, 0775);
				// file				
				$import_file  = array('name' => $newname, 'path' => $newname);	
				// status
				$status  = 'success';	
				$message = sprintf(__('Import file [%s] uploaded successfully, please hit the Import Users button to start import.','mgm'),$newname);
			}
		}		
		// send ouput		
		@ob_end_clean();	
		// PRINT
		echo json_encode(array('status'=>$status,'message'=>$message, 'file'=>$import_file,'post'=>$_POST));
		// end out put			
		@ob_flush();
		exit();
	}	
	
	// core setup
	function core_setup(){	
		global $wpdb;	
		// local
		extract($_POST);
				
		// execute
		if(isset($core_setup_execute) && !empty($core_setup_execute)){
			// switch
			if($core_setup_execute=='core_switch'){
				// execute
				echo $this->_do_core_switch();
			}else if($core_setup_execute=='core_env'){
			// environment
				echo $this->_do_core_environment();
			}
			// exit
			exit();
		}			
		
		// data
		$data = array();			
		// load template view
		$this->load->template('tools/core_setup', array('data'=>$data));	
	}		
	
	// upgrade
	// @deprecated
	function upgrade(){	
		global $wpdb;	
		// local
		extract($_POST);				
		// execute
		if(isset($upgrade_execute) && !empty($upgrade_execute)){
			// execute
			echo $this->_do_upgrade();
			// exit
			exit();
		}		
		// data
		$data = array();
		// load template view
		$this->load->template('tools/upgrade', array('data'=>$data));		
	}	
	
	// system_reset
	function system_reset(){		
		global $wpdb;	
		// local
		extract($_POST);
				
		// execute
		if(isset($reset_execute) && !empty($reset_execute)){
			// execute
			echo $this->_do_system_reset();
			// exit
			exit();
		}
		
		// data
		$data = array();
		// opts
		$data['reset_opts'] = array('settonly'           => __('All Settings','mgm'),
									'settntable'         => __('All Settings & Tables','mgm'),									
									'licensereset'       => __('License Info','mgm'),
									'dashcachereset'     => __('Dashboard Cache','mgm'),
									'sidebarwidgetreset' => __('Sidebar Widget Settings','mgm'),
									'fullreset'          => __('Deactivate & Delete all Data','mgm'),
						  	  );
		// load template view
		$this->load->template('tools/system_reset', array('data'=>$data));		
	}
	
	// logs
	function logs(){
		global $wpdb;
		// data
		$data = array();
		// get transaction logs
		$data['transactions_logs'] = $wpdb->get_results("SELECT * FROM `".TBL_MGM_TRANSACTION."` WHERE `module` IS NOT NULL ORDER BY `transaction_dt` DESC LIMIT 0, 20");
		// get api logs
		$data['api_logs'] = $wpdb->get_results("SELECT * FROM `".TBL_MGM_REST_API_LOG."` ORDER BY `create_dt` DESC LIMIT 0, 20");
		// load template view
		$this->load->template('tools/logs', array('data'=>$data));
	}
	
	// mgm dependencies
	function dependencies(){
		// data
		$data = array();
		// checks
		$data['checks'] = array('curl' 	=> array('key' => 'curl','callback'=>'check_curl_support','label'=> __('PHP Curl Library','mgm')), 
								'hash' 	=> array('key' => 'hash','callback'=>'check_hash_support','label'=>__('PHP Hash Library','mgm')),
								'simplexml' => array('key' => 'simplexml','callback'=>'check_simplexml_support','label'=>__('PHP SimpleXML Library','mgm')),
								'mbstring' 	=> array('key' => 'mbstring','callback'=>'check_mbstring_support','label'=> __('PHP Mbstring Library','mgm')), 
								'mysql' => array('key' => 'mysql','callback'=>'check_mysql_support','label'=>__('MySQL minimum version 5+','mgm')),
								'phpversion' => array('key' => 'phpversion','callback'=>'check_php_version_support','label'=>__('PHP 5+ and minimum version 5.2.0','mgm')),
								'wpversion' => array('key' => 'wpversion','callback'=>'check_wp_version_support','label'=>__('Wordpress minimum version 3+','mgm')),
								'http_ranges' => array('key' => 'httpranges','callback'=>'check_http_range_support','label'=>__('HTTP RANGES for heavy download streaming','mgm')),
								'mgmdbtables' => array('key' => 'mgmdbtables','callback'=>'check_dbtables_loaded','label'=>__('Magic Members Database Tables Loaded','mgm'))
					        );
						                         
		$this->load->template('tools/dependencies', array('data'=>$data));
	}
	
	// logs
	function system_health(){
		global $wpdb;
		// data
		$data = array();
		/*// get transaction logs
		$data['transactions_logs'] = $wpdb->get_results("SELECT * FROM `".TBL_MGM_TRANSACTION."` WHERE `module` IS NOT NULL ORDER BY `transaction_dt` DESC LIMIT 0, 20");
		// get api logs
		$data['api_logs'] = $wpdb->get_results("SELECT * FROM `".TBL_MGM_REST_API_LOG."` ORDER BY `create_dt` DESC LIMIT 0, 20");*/
		// load template view
		$this->load->template('tools/system_health', array('data'=>$data));
	}
	
	// PRIVATE -------------------------------------------------------------------
	// do system reset
	function _do_system_reset(){
		global $wpdb, $mgm_init;
		extract($_POST);
		
		// track
		$status   = 'error';
		$message  = __('Reset failed', 'mgm'); 
		$redirect = '';
		// take option
		switch($reset_type){
			case 'settntable':
				// user meta			
				$wpdb->query("DELETE FROM `{$wpdb->usermeta}` WHERE `meta_key` LIKE 'mgm_%' OR `meta_key` LIKE '_mgm_%'");
				// post meta				
				$wpdb->query("DELETE FROM `{$wpdb->postmeta}` WHERE `meta_key` LIKE '_mgm_%' ");
				// loop tables
				foreach( mgm_get_tables() as $table ){
					// do not clear countries table
					if($table == TBL_MGM_COUNTRY )
						continue;
					// truncate	 
					$wpdb->query('TRUNCATE ' . $table );
				}
				// set messages
				$status   = 'success';
				$message  = __('Settings and Table reset completed successfully.', 'mgm');			
			case 'settonly':
				// option meta
				$wpdb->query("DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE 'mgm_%' AND option_name NOT IN('mgm_version','mgm_upgrade_id','mgm_auth','mgm_auth_options')");				
				// set messages
				if($reset_type == 'settonly'){
					$status  = 'success';
					$message = __('Settings reset completed successfully.', 'mgm');
				}
			break;			
			case 'fullreset':
				// plugin basename
				$plugin = untrailingslashit(MGM_PLUGIN_NAME);
				// if active
				if (is_plugin_active($plugin)) {
					// post meta explicitly				
					$wpdb->query("DELETE FROM `{$wpdb->postmeta}` WHERE `meta_key` LIKE '_mgm_%' ");
					// deactivate first
					deactivate_plugins($plugin, true);
					// remove all
					$mgm_init->deactivate(true); 	
					// send deactivation
					mgm_get_class('auth')->notify_deactivation();
					// sleep 2 sec
					sleep(2);
					// redirect
					$status   = 'success';
					$message  = __('MagicMembers deactivated successfully. You will be redirected to Plugins page.', 'mgm');
					$redirect = 'plugins.php?deactivate=true&plugin_status=active&paged=1'; 
				}else{
					$status  = 'error';
					$message = __('MagicMembers already deactivated','mgm');
				}				
			break;
			case 'licensereset':
				// delete option
				delete_option('mgm_auth_options');
				delete_option('mgm_auth');// old key
				// clear dashboard cache
				mgm_delete_transients();
				// response
				$status  = 'success';
				$message = __('License reset completed successfully.', 'mgm');
				$redirect = 'admin.php?page=mgm/admin'; 
			break;
			case 'dashcachereset':
				// clear dashboard cache
				mgm_delete_transients();
				// response
				$status  = 'success';
				$message = __('Dashboard cache reset completed successfully.', 'mgm');
			break;
			case 'sidebarwidgetreset':
				// delete option
				delete_option('mgm_sidebar_widget_options');
				delete_option('mgm_sidebar_widget');// old key
				// response
				$status  = 'success';
				$message = __('Sidebar Widget Settings reset completed successfully.', 'mgm');
			break;
		}
					
		// response
		return json_encode(array('status'=>$status, 'message'=>$message, 'redirect'=>$redirect));		
	}
	
	// data migrate: unfinished
	function _do_data_migrate() {
		global $wpdb;	
		// local
		extract($_POST);		
		
		// track
		$status  = 'error';
		$message = __('Migration failed. ', 'mgm'); 
		$export  = array();
		
		// update
		if(isset($migrate_execute) && !empty($migrate_execute)){
			// type
			switch($migrate_type){
				case 'import':
					// import
					if($this->_do_import()){
						// status
						$status  = 'success';
						$message = __('Migration completed successfully.', 'mgm');
					}
				break;
				case 'export':
					// get
					if($file = $this->_do_export()){
						// export
						$export = array('download_url'=>admin_url('admin.php?page=mgm/admin/files&type=download&file='.urlencode($file)));
						// status
						$status = 'success';
						$message = __('Migration completed successfully.', 'mgm');
					}else{
						// error
						$message .= __('Export file creation failed.','mgm');
					}
				break;	
				case 'import_users':			
					// response
					$response = $this->_do_import_users();	
					// log
					// mgm_log($response, __FUNCTION__);	
					// set			
					if(isset($response['status']) && $response['status'] == true ) {
						$status = 'success';	
						$message = sprintf(__('Import completed successfully. %d users imported', 'mgm', $response['users_count']));
					}else 
						$message = isset($response['error']) ? $response['error'] : __('Error while importing.', 'mgm');
						
					// mgm_log($message, __FUNCTION__);					
				break;
				//check import is completed 
				//If the a lot of records are there, then the server might not respond prperly tp the ajax request
				//So This will check the uploaded files exists or not
				//File exists means import is being done
				case 'import_status':
					$status  = 'success';
					$message = '';	
					$timeout = 5;//in seconds
					$limit = 900;					
					sleep($timeout);				
					if(isset($import_users) && file_exists(MGM_FILES_IMPORT_DIR . $import_users)) {						
						$status  = 'incomplete';						
						$message = '';
						if(isset($retry)) {
							$time_elapsed = $retry * $timeout;
							if($time_elapsed >= $limit) {
								$status  = 'error';
								$message = __('Server returned an empty response. Please check whether the users got imported.');	
							}
						}
					}else {
						$message = __('Import completed successfully.', 'mgm');
					}
					
				break;	
			}			
		}
		// response
		return json_encode(array_merge(array('status'=>$status, 'message'=>$message),$export));		
	}	
	
	// import
	// import
	function _do_import(){
		global $wpdb;	
		// local
		extract($_POST);		
		if(isset($export_sections) && !empty($export_sections)) {
			// xml
			$xml_settings = simplexml_load_file($import_file);
			//system object
			$system_obj = mgm_get_class('system');
			//loop
			foreach ($export_sections as $export_section){				
				//check
				switch ($export_section) {
					case 'general_settings':
						//loop
						foreach ($xml_settings->general_settings as $xml_general){							
							$name = (string)$xml_general->attributes()->name;
							$value = (string)$xml_general->attributes()->value;							
							//check
							if(isset($name) && !empty($name)){								
								// set var
								if(isset($system_obj->setting[$name]) && $name !='extend_dir' && $name !='affiliate_id'){
									//value
									$value =maybe_unserialize( $value );
									// array
									if(is_array($value)){
										$system_obj->setting[$name] = (array)$value;
									}else{
										$system_obj->setting[$name] = addslashes($value);		
									}
									// update
									$system_obj->save();									
								}
								if($name =='extend_dir' && !empty($value)) {
									update_option('mgm_extend_dir', $value);
								}
								if($name =='affiliate_id' && !empty($value)) {
									update_option('mgm_affiliate_id', intval($value));
								}
							}							
						}						
					break;
					case 'messages_settings':
						//loop
						foreach ($xml_settings->messages_settings as $xml_message){				
							$name = (string)$xml_message->attributes()->name;
							$value = (string)$xml_message->attributes()->value;							
							if(isset($name) && !empty($name)){
								// set template
								$system_obj->set_template($name, $value);// save
								// copy to custom fields	
								if($name == 'subs_intro'){
									mgm_get_class('member_custom_fields')->update_field_value('subscription_introduction',$value);	
								}
								// tos
								if($name == 'tos'){
									mgm_get_class('member_custom_fields')->update_field_value('terms_conditions',$value);	
								}
							}							
						}																			
					break;
					case 'emails_settings':
						//loop
						foreach ($xml_settings->emails_settings as $xml_email){				
							$name = (string)$xml_email->attributes()->name;
							$value = (string)$xml_email->attributes()->value;							
							if(isset($name) && !empty($name)){
								// set template
								$system_obj->set_template($name, $value);// save
							}							
						}											
					break;
					case 'content_protection_settings':
						//loop
						foreach ($xml_settings->content_protection_settings as $xml_content_protection){							
							$name = (string)$xml_content_protection->attributes()->name;
							$value = (string)$xml_content_protection->attributes()->value;							
							//check
							if(isset($name) && !empty($name)){								
								// set var
								if(isset($system_obj->setting[$name])){
									//value
									$value =maybe_unserialize( $value );
									// array
									if(is_array($value)){
										$system_obj->setting[$name] = (array)$value;
									}else{
										$system_obj->setting[$name] = addslashes($value);		
									}
									// update
									$system_obj->save();									
								}
							}							
						}						
					break;										
				}
			}
			return true;		
		}
		return false;	
	}
	
	// parse
	function _parse_import_file(){
		// init fix		
		@ini_set('html_errors', 0);
		//@ini_set('log_errors',1);
		//@ini_set('error_log', MGM_FILES_LOG_DIR . 'error_log.txt');
		@ini_set('display_errors', 0);		
		@ini_set('memory_limit', '536870912');		//512M
		@set_time_limit(900); //15 minutes
		// extract
		extract($_POST);	
		
		//test 
		global $wpdb; 
		// init data
		$header = $users = array();
		// flag
		$continue = false;	
		// response
		$response = array('status' => 'error', 'message'=>__('Please upload CSV/XLS file', 'mgm'));			
		// imported file
		$i_filepath = MGM_FILES_IMPORT_DIR . $import_users;
		// ext
		$extension = pathinfo($i_filepath, PATHINFO_EXTENSION);			
		
		// enable forced gc
		// if(function_exists('gc_enable')) gc_enable();		
		// mgm_log('IMPORT MEMORY PEAK1: ' . memory_get_peak_usage(true)/(1024*1024));
		// mgm_log($i_filepath. ' EXT: ' . $extension. ' READABLE: ' . is_readable($i_filepath), __FUNCTION__);
		
		// uploaded ext
		switch (strtolower($extension)) {
			//CSV 
			case 'csv':			
				// read csv	
				if($handle = @fopen($i_filepath, 'r')){
					// loop
					while( $data = fgetcsv($handle,null,',')) {
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
							$users[] = $row;
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
				
			//XLS Parsing:	
			case 'xls':
				// init xls
				$obj_xls = new Spreadsheet_Excel_Reader();				
				// encoding
				$obj_xls->setOutputEncoding('CP1251');		
				// read file		
				$obj_xls->read($i_filepath);							
				// check sheet
				if(!empty($obj_xls->sheets)) {	
					// loop				
					for ($i = 1; $i <= $obj_xls->sheets[0]['numRows']; $i++) {	
						// set header					
						if(empty($header)){
							$header = $obj_xls->sheets[0]['cells'][$i];
						}else {
							// $user_count++;
							// update rowws for empty cells:	
							$row = array();		
							// loop				
							foreach ($header as $key => $val) {									
								// create an array with header value as index:
								$row[$val] = (!isset($obj_xls->sheets[0]['cells'][$i][$key])) ? null : trim($obj_xls->sheets[0]['cells'][$i][$key]);
							}
							// set user
							$users[] = $row;	
							// unset
							unset($row);
							// check limit reached:
							// if(($user_count+1) >= $row_limit) break;						
						}
						
						// unset						
						unset($obj_xls->sheets[0]['cells'][$i]);
						// ref colecter 
						// if(function_exists('gc_collect_cycles')) gc_collect_cycles();					
					}
					// reindex header once done:
					$header = array_values($header);
					// continue
					$continue = true;
				}
				// unset
				unset($obj_xls);
			break;				
		}				
		
		// delete uploaded file:					
		if(file_exists($i_filepath)) {
			@unlink($i_filepath);
		}
		
		//	mgm_log('IMPORT MEMORY PEAK2: ' . memory_get_peak_usage(true)/(1024*1024));
		
		// response
		if(count($users)>0){
			$response['status'] = 'success';			
		}
		
		// set
		$response['users']  = $users;
		$response['header'] = $header;
		
		// return 
		return $response;
	}
	
	// import users
	function _do_import_users() {
		//test 
		global $wpdb; 
		// extract
		extract($_POST);	
		
		// import fag
		define('MGM_DOING_USERS_IMPORT', TRUE);		
		
		// parese
		$i_response = $this->_parse_import_file();
		
		// log
		// mgm_log($response, __FUNCTION__);
		
		// stop 
		// $response['status'] = 'error';
		
		// process data:		
		if( $i_response['status'] == 'success' && in_array('user_email', $i_response['header']) ) {	
		// success	
			// extract
			$i_users  = $i_response['users'];
			$i_header = $i_response['header'];
			// object
			$mgm_packs = mgm_get_class('mgm_subscription_packs');
			$mgm_roles = mgm_get_class('mgm_roles');	
			// types
			$membership_types = mgm_get_class('membership_types')->get_membership_types();
			$memtypes = mgm_get_class('membership_types');
			// check users
			if(!empty($i_users)) {
			// users found	
				// init
				$row_count = count($i_users);
				$col_count = count($i_header);
				// limits
				$row_limit = 2000;
				$user_count = 0;
				// log
				// mgm_log(sprintf('Rows: [%d] Cols: [%d]',$row_count, $col_count), __FUNCTION__);
				// user fields												
				$user_fields = array('first_name', 'last_name', 'user_nicename', 'user_url', 'display_name', 'nickname', 
									 'user_firstname', 'user_lastname', 'user_description', 'user_registered');				
				// flag
				$update_count = $user_count = 0;	
				// new users
				$new_users = array();	
				$specialchars = array(',','\'','"',"\n\r","\n",'\\','/','$','`','(',')',' '," ");
				
				// custom fields
				$cf_register_page = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_register'=>true, 'on_profile'=>true)));
				// count
				$cf_count = count($cf_register_page);
				// exclude
				$cf_exclude_names = array('subscription_introduction','subscription_options','terms_conditions','privacy_policy','description',
				                          'payment_gateways','password_conf','autoresponder');
				// types
				$cf_exclude_types = array('html', 'label', 'captcha');
				// loop
				foreach($i_users as $i_user) {
					// init					
					$update_user = $insert_user 
					             = $is_membership_update 
					             = $is_multiple_membership_update 
								 = $multiple_membership_exists 
								 = $update_role 
								 = false;
					// pack			 
					$pack = array();			 
					// increment
					$user_count++;
					// remove N/A ? 
					// $i_user = str_ireplace('N/A', '', $i_user);					
					// init					 
					
					// id 
					$id 			 = (isset($i_user['ID']) && is_numeric($i_user['ID'])) ? $i_user['ID'] : ''; 					
					$email 			 = str_replace($specialchars, '', sanitize_email($i_user['user_email'])); 
					$user_login 	 = str_replace($specialchars, '', sanitize_user($i_user['user_login'])); 
					$user_password 	 = isset($i_user['user_password']) ? $i_user['user_password'] : '';
					// Issue #1559: Standardize membership type name to use machine name
					$membership_type = $memtypes->get_type_code($i_user['membership_type']);
					$pack_id 	 = isset($i_user['pack_id']) ? sanitize_user($i_user['pack_id']) : ''; 																			
					
					// log
					// mgm_log(sprintf('step 1: user_count: [%d] id: [%s] email: [%s] user_login: [%s] user_password: [%s]', $user_count, $id, $email, $user_login, $user_password), __FUNCTION__);
															
					// user id not valid					
					if(!is_numeric($id)) {	
						// check login/email					
						if(!empty($user_login) && !empty($email)) {	
							// user								
							$_user = get_user_by('login', $user_login);							
							// if update and different email
							if(isset($_user->ID) && $_user->user_email != $email) {
								// log
								// mgm_log(sprintf('step 2: %s %s', $_user->user_email, $email), __FUNCTION__);
								// continue
								continue;
							}	
							
							// fresh insert/registration:	
							if(!$_user) {			
								// password					
								$user_password = (!empty($user_password)) ? $user_password : wp_generate_password();
								$user_password = str_replace($specialchars, '', $user_password);
								// trim 
								$user_password = trim($user_password);	
								
								// log
								// mgm_log('step 2.1 user_password: '. $user_password, __FUNCTION__);
								
								// create 														
								$id = wp_create_user( $user_login, $user_password, $email );
									
								// log
								// mgm_log(sprintf('step 3: %s, %s ', $user_password, $id), __FUNCTION__);	
														
								// check error
								if(is_wp_error($id)) {	
									// log
									// mgm_log(sprintf('step 4: %s', print_r($id->get_error_messages(),1)), __FUNCTION__);
									// unset 
									unset($id);	
									// continue
									continue;		
								}						
								// set new user								
								$new_users[$id]['email']      = $email; 
								$new_users[$id]['user_login'] = $user_login; 

								// update option		
								update_user_option( $id, 'default_password_nag', true, true );	
								// flag							
								$insert_user = true;
							}else{ 
							// set id to user
								$id = $_user->ID;	
							}
							// unset
							unset($_user);																
						}else {		
						// login/email not present
							// log
							// mgm_log(sprintf('step 5: %s, %s ', $user_login, $email), __FUNCTION__);	
							// continue					
							continue;//skip the record
						}
					}else{ 	
					// update
						$update_user = true;	
					}
															
					// get User object:
					$user = new WP_user($id);
					// log
					// mgm_log(sprintf('step 6: %s ', print_r($user,1)), __FUNCTION__);	
					
					//issue #700
					$format = mgm_get_date_format('date_format_short');
					// ------------------------------------------
					// user to mgm member
					if(isset($user->ID) && $user->ID > 0 ) {								
						// get mgm object:									
						$member = mgm_get_member($user->ID);	
						// update custom fields:
						if(!empty($member)) {
							// update pack id if not supplied and already exists
							// This is to make pack_id optional: issue#: 807
							if(!is_numeric($pack_id) && isset($member->pack_id) && $member->pack_id > 0 )
								$pack_id = $member->pack_id; 
									
							// update misc fields:
							if(!isset($member->rss_token) || (isset($member->rss_token) && empty($member->rss_token))){
								$member->rss_token = mgm_create_rss_token();
							}	
							// init
							$user_password_enc = mgm_encrypt_password($user_password, $user->ID, $member->rss_token);	
							// mgm_log($user_password_enc, __FUNCTION__);							
							// check						
							if($cf_count > 0) {
								// loop custom fields
								foreach ($cf_register_page as $field) {	
									// key
									$key = $field['name'];	
									// mgm_log($key, __FUNCTION__);						
									// skip unwanted fields
									if(in_array($field['name'], $cf_exclude_names) || in_array($field['type'], $cf_exclude_types)) {
										// log
										// mgm_log(sprintf('step 7: %s ', $field['name']), __FUNCTION__);	
										// continue
										continue;
									}	
									
									// init
									$val = '';
									//issue #700
									// check
									if(isset($i_user[$key]) && !empty($i_user[$key]) && preg_match('/date/i', $key)) {										
									// validate date
										if(mgm_is_valid_date($i_user[$key]) && mgm_format_inputdate_to_mysql($i_user[$key],$format)) {
											$val = $i_user[$key];
										}						
									}elseif($key == 'email') {
									// email and username custom fields
										$val = $email;
									}elseif ($key == 'username') {
									// username
										$val = $user_login;
									}elseif ($key == 'password') {
									// password
										if(!empty($user_password_enc)) {
											// set
											$val = $user_password_enc ;
											// log
											// mgm_log(sprintf('step 7.1: %s ', $user_password_enc), __FUNCTION__);	
										}										
									}else{
										$val = isset($i_user[$key]) ? $i_user[$key] : '' ;
									}
									// If checkbox, then serialize the value: Issue #1070
									if($field['type'] == 'checkbox' && !empty($val)) {
										$val = serialize(explode("|", $val));
									}
									// update fields:
									if(!empty($val) || !isset($member->custom_fields->{$key})){
									// set
										$member->custom_fields->{$key} = $val;
									}	
									
									// unset
									unset($field,$val);		
								}
							}// custom fields updated
							
							// log
							// mgm_log(sprintf('step 8: %s ', print_r($member,1)), __FUNCTION__);	
										
							// update membership: main mgm_member object
							if(!empty($membership_type) && is_numeric($pack_id)) {								
								// pack
								if($pack = $mgm_packs->get_pack($pack_id)) {
								// valid pack
									$member->pack_id = $pack_id;								
								}else {
									// log
									// mgm_log(sprintf('step 9: %s ', print_r($pack,1)), __FUNCTION__);	
									// error:																		
									continue;
								}	
								
								// membership types:
								$sel_type = '';
								// loop
								foreach ($membership_types as $key => $type ) {
									// check
									if($membership_type == $key || $membership_type == $type) {
									// match
										$sel_type = $key;  break;
									}
								}
								
								// check
								if(!empty($sel_type)){
									$membership_type = $sel_type;
								}else { 		
									// log
									// mgm_log(sprintf('step 10: %s ', $sel_type), __FUNCTION__);
									// continue							
									continue;
								}
										
								// to distinguish between primary membership and other membership(Y/N)
								if( !isset($i_user['other_membership']) || (isset($i_user['other_membership']) && $i_user['other_membership'] != 'Y' ) ) {									
									// set
									$member->membership_type = $membership_type;
									// update current membership:
									$_response = $this->_update_member_object($member, $pack, $i_user);
									// check
									if(!$_response['status']) {
										// log
										// mgm_log(sprintf('step 11: %s ', print_r($_response,1)), __FUNCTION__);
										// skip the row
										continue;
									}
									// set
									$member = $_response['mgm_member'];	
									// check guest								
									if(strtolower($member->membership_type) == 'guest'){
									// default
										$member->other_membership_types = array();	
									}else{ 
									// flag
										$update_role = true;	
									}										  																
								}else {									
									// init
									$multiple_updated = false;								
									// if multiple mgm_member object:
									if(isset($member->other_membership_types) && !empty($member->other_membership_types)) {
										// loop
										foreach ((array) $member->other_membership_types as $key => $member ) {
											// convert
											$member = mgm_convert_array_to_memberobj($member, $user->ID);
											// type
											if($member->membership_type == $membership_type) {
												// check
												$_response = $this->_update_member_object($member, $pack, $i_user);
												// check
												if(!$_response['status']) {
													// log
													// mgm_log(sprintf('step 12: %s ', print_r($_response,1)), __FUNCTION__);
													// skip the row:																																	
													continue;
												}	
												// make sure array is saved:
												$_response['mgm_member'] = mgm_convert_memberobj_to_array($_response['mgm_member']);	
												// set										
												$member->other_membership_types[$key] = $_response['mgm_member'];
												// flag
												$multiple_updated = true;												// break
												break;
											}
										}
									}
									//else {mgm_log('skip other_memberships 3', __FUNCTION__);}	
										// add new to mother_membership_types object:
									if(!$multiple_updated) {
										// update
										$_response = $this->_update_member_object( new stdClass, $pack, $i_user);
										// check
										if(!$_response['status']) {
											// log
											// mgm_log(sprintf('step 13: %s ', print_r($_response,1)), __FUNCTION__);
											// skip the row:																																		
											continue;
										}
										// set
										$_response['mgm_member'] = mgm_convert_memberobj_to_array($_response['mgm_member']);											    
										// set
										$member->other_membership_types[] = $_response['mgm_member'];
										// flag
										$update_role = true;											
									}
																	
								}
							}
							
							
							
							// payment type:
							if(!isset($member->payment_type) || (isset($member->payment_type) && empty($member->payment_type)))
								$member->payment_type = 'subscription';
							
							// update password:	
							if(!empty($user_password)) {
								// issue#: 672
								// generate iss#688 
								/*if(empty($user_password_enc)) {
									// set
									$user_password_enc = mgm_encrypt_password($user_password, $user->ID);
									// log
									// mgm_log(sprintf('step 7.2: regenarete password: %s ', $user_password_enc), __FUNCTION__);
								}*/	
								// set
								$member->user_password = $user_password_enc;						
								// md5
								// $user_password_md5 = wp_hash_password($user_password);
								//mgm_log($wpdb->prepare("UPDATE ".$wpdb->users." SET user_pass = %s WHERE ID = %d", $user_password_md5, $user->ID), __FUNCTION__);
								// db update
								//$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->users." SET user_pass = %s WHERE ID = %d", $user_password_md5, $user->ID) );
								// new user
								if($insert_user) $new_users[$id]['user_password'] = $user_password;	
							}					
							// save mgm_member object:
							$member->save();									
							// update role:
							if($update_role) {									
								// update role/change order		 						
								$mgm_roles->add_user_role($user->ID, $pack['role']);
							}		
							// log
							// mgm_log(sprintf('step 14: %s ', print_r($member,1)), __FUNCTION__);																																
						}
						// update other user fields:
						$user_extra = array();
						if(!empty($user_password)) {
							$user_extra['user_pass'] = $user_password;
						}
						// loop
						foreach ($i_user as $key => $value) {
							// check
							if(in_array($key, $user_fields) && !empty($value)) {
								if ($key == 'user_registered') {
									if(mgm_is_valid_date($value) && $mysql_date = mgm_format_inputdate_to_mysql($value)) {
										$user_extra[$key] = $mysql_date;
									}
								}
								else {
									// set
									$user_extra[$key] = $value;
								}
							}
						}		
						// update				
						if(!empty($user_extra)) {
							// set
							$user_extra['ID'] = $user->ID;
							// update
							wp_update_user($user_extra);		
						}	
						// update										
						$update_count++;
						
						// check here:
						unset($member, $user, $user_extra);						
					}
					// check limit reached:
					if($user_count >= $row_limit) {
						// check
						if($row_count > $row_limit ) {
						// set
							$response['message'] = sprintf(__('( Import stopped at: %s as limit( %d ) reached. )', 'mgm'), $email, $row_limit);
						}
						// break;
						break;				
					}
					// ------------------------------------------
					 
					// unset
					unset($i_user);		
					// debug			
					// if(function_exists('gc_collect_cycles')) gc_collect_cycles();
					// wait	
					if(!($user_count%25)) sleep(1);	
				}// end imported users loop
				//	mgm_log('IMPORT MEMORY PEAK2.5: ' . memory_get_peak_usage(true)/(1024*1024));
				
				// free unwanted resources
				unset($cf_register_page, $cf_exclude_names, $user_fields, $mgm_packs, $mgm_roles, $user_count);
				
				// debug
				// if(function_exists('gc_collect_cycles')) gc_collect_cycles();
				
				// done importing, mail and notify
				if($update_count) {
					// unset			
					unset($update_count);
					// set response
					$response['status'] = true;
					$response['users_count'] = count($new_users);
					// send admin notification:
					// send to admin 					
					if(!empty($new_users)) {
						$system_obj = mgm_get_class('system');
						// message
						$message  = sprintf('(%d) %s  %s: <br/><br/>', count($new_users), __( 'New user registration on your blog', 'mgm'), get_option('blogname'));
						// loop
						foreach ($new_users as $user_id => $n_user) {	
							// set
							$message .= sprintf('%s: %s <br/>', __('Username', 'mgm'), $n_user['user_login']);
							$message .= sprintf('%s: %s <br/>', __('E-mail', 'mgm'), $n_user['email']);
							$message .= "-----------------------------------<br/><br/>";
							// unset
							unset($n_user);
							// send email to the user:
							// mgm_new_user_notification($user_id, $new['user_password'],false);
						}
						// unset
						unset($new_users);
						// check
						if(isset($response['message'])) {
							// set
							$message .= $response['message'];
							$message .= "-----------------------------------<br/><br/>";
						}
						// admin email:
						@mgm_mail(get_option('admin_email'), sprintf('[%s] %s', __('New User Registration','mgm'), get_option('blogname')), $message);						
						// unset
						unset($message);
						// debug
						// if(function_exists('gc_collect_cycles')) gc_collect_cycles();
					}										
				}else {
				// none updated
					$response['error'] = __('No users imported', 'mgm');
				}
			}else {
			// no users
				$response['error'] = __('Empty records', 'mgm');
			} 
		}else {
		// no users
			$response['error'] = __('Error in processing users', 'mgm');			
		}	
		
		// mgm_log('IMPORT MEMORY PEAK3: ' . memory_get_peak_usage(true)/(1024*1024));
		// mgm_log('$response:' . mgm_array_dump($response, true));
		// return 
		return $response;
	}
	
	// update
	function _update_user_as_member()
	{
		
	}
	
	//create/update mgm_member ubject
	function _update_member_object($member, $pack, $data, $insert = true) {		
		$arr_resp = array('status' => true);		
		$duration_exprs = mgm_get_class('subscription_packs')->get_duration_exprs();
		$arr_status = array(MGM_STATUS_NULL, MGM_STATUS_ACTIVE, MGM_STATUS_EXPIRED, MGM_STATUS_PENDING, 
							MGM_STATUS_TRIAL_EXPIRED, MGM_STATUS_CANCELLED, MGM_STATUS_ERROR, MGM_STATUS_AWAITING_CANCEL);
		// if trial on		
		if ($pack['trial_on']) {
			$member->trial_on            = (!empty($data['trial_on'])) ? $data['trial_on'] : (isset($member->trial_on) && $member->trial_on ? $member->trial_on : $pack['trial_on']);
			$member->trial_cost          = (!empty($data['trial_cost'])) ? $data['trial_cost'] : (isset($member->trial_cost) && $member->trial_cost ? $member->trial_cost : $pack['trial_cost']);
			$member->trial_duration      = (!empty($data['trial_duration'])) ? $data['trial_duration'] : (isset($member->trial_duration) && $member->trial_duration ? $member->trial_duration : $pack['trial_duration']);
			$member->trial_duration_type = (!empty($data['trial_duration_type'])) ? $data['trial_duration_type'] : (isset($member->trial_duration_type) && $member->trial_duration_type ? $member->trial_duration_type : $pack['trial_duration_type']);
			$member->trial_num_cycles    = (!empty($data['trial_num_cycles'])) ? $data['trial_num_cycles'] : (isset($member->trial_num_cycles) ? $member->trial_num_cycles : $pack['trial_num_cycles']);
		}		
		// duration
		if(!empty($data['duration'])) {			
			if(is_numeric($data['duration'])) {
				$member->duration = $data['duration'];				
			}else {				
				$arr_resp['status'] = false;
				$arr_resp['error'][] = __('Invalid Duration', 'mgm');
			}
		}elseif($insert) {			
			$member->duration = $pack['duration'];
		}
		//duration type:
		if(!empty($data['duration_type'])) {			
			if(in_array($data['duration_type'], array('d','w','m','y','l','dr'))){				
				$member->duration_type =  $data['duration_type'];
			}else {				
				$arr_resp['status'] = false;
				$arr_resp['error'][] = __('Invalid Duration Type', 'mgm');
			}
		}elseif ($insert) {			
			$member->duration_type   = $pack['duration_type'];
		}
		//duration type:
		if(!empty($data['amount'])) {			
			if(is_numeric($data['amount'])) {				
				$member->amount   =  number_format($data['amount'],2,'.','');
			}else {				
				$arr_resp['status'] = false;
				$arr_resp['error'][] = __('Invalid Amount', 'mgm');
			}
		}elseif ($insert) {			
			$member->amount   = $pack['cost'];
		}											
		//amount:
		if(!empty($data['hide_old_content'])) {
			$member->hide_old_content   =  $data['hide_old_content'];
		}elseif ($insert) {
			$member->hide_old_content   = $pack['hide_old_content'];
		}	
		//$member->currency        = (!empty($data['currency'])) ? $data['currency'] : $system_obj->setting['currency'];		
		$member->membership_type = $data['membership_type'];	
		//status
		if(!empty($data['status'])) {
			if(in_array($data['status'], $arr_status))
				$member->status   =  $data['status'];
			else {
				$arr_resp['status'] = false;
				$arr_resp['error'][] = __('Invalid Status', 'mgm');
			}	
		}elseif ($insert) {
			//to prevent updating active/expired user status
			//if(isset($member->status) && !in_array($member->status, array(MGM_STATUS_ACTIVE, MGM_STATUS_EXPIRED)))
			$member->status   = MGM_STATUS_ACTIVE;
		}	
		
		if(!empty($data['status_str'])) {
			$member->status_str   =  $data['status_str'];
		}elseif ($insert) {
			$member->status_str   =  __('Last payment was successful','mgm');
		}
		
		//join date:
		if (!empty($data['join_date'])) {
			if(mgm_is_valid_date($data['join_date']) && $mysql_date = mgm_format_inputdate_to_mysql($data['join_date'])) {
				$member->join_date = strtotime($mysql_date);
			}else {
				$arr_resp['status'] = false;
				$arr_resp['error'][] = __('Invalid Joining Date', 'mgm');
			}
		}elseif($insert) {
			// do not overwrite if already set
			if (empty($member->join_date))
				$member->join_date = strtotime('now');			
		}
			
		//last pay date:
		if (!empty($data['last_pay_date'])) {
			if(mgm_is_valid_date($data['last_pay_date']) && $mysql_date = mgm_format_inputdate_to_mysql($data['last_pay_date'])) {				
				$member->last_pay_date = $mysql_date;
			}else {
				$arr_resp['status'] = false;
				$arr_resp['error'][] = __('Invalid Last Pay Date', 'mgm');
			}				
		}elseif($insert) {
			// do not overwrite if already set
			if (empty($member->last_pay_date))			
				$member->last_pay_date = date('Y-m-d');
		}		
			
		//expiry date:		
		if (!empty($data['expire_date'])) {
			if(mgm_is_valid_date($data['expire_date']) && $mysql_date = mgm_format_inputdate_to_mysql($data['expire_date']))
				$member->expire_date = $mysql_date;
			else {
				$arr_resp['status'] = false;
				$arr_resp['error'][] = __('Invalid Last Expiry Date', 'mgm');
			}			
		}elseif($insert) {				
			$time = strtotime('now');			
			//if not lifetime:
			// if($pack['duration_type'] != 'l') {
			if(in_array($pack['duration_type'], array_keys($duration_exprs))) {// take only date exprs
				$time = strtotime("+{$pack['duration']} {$duration_exprs[$pack['duration_type']]}", $time);							
				// formatted
				$member->expire_date = date('Y-m-d', $time);					
			}else 
				$member->expire_date = '';				
		}
		//if lifetime:
		if($pack['duration_type'] == 'l' && $member->status == MGM_STATUS_ACTIVE) {
			$member->expire_date = '';
			if(isset($member->status_reset_on))
				unset($member->status_reset_on);
			if(isset($member->status_reset_as))
				unset($member->status_reset_as);	
		}
		//active number of cycles:
		if(isset($data['active_num_cycles']) && !empty($data['active_num_cycles']))
			$member->active_num_cycles = $data['active_num_cycles'];
			
		//autoresponder subscription:
		if(isset($data['autoresponder']) && !empty($data['autoresponder'])) {			
			$member->autoresponder = $data['autoresponder'];	
			$member->subscribed = 'Y';	
		}
		//payment_info
		//module:
		if(isset($data['payment_info_module']) && !empty($data['payment_info_module'])) {			
			if(!isset($member->payment_info))
				$member->payment_info = new stdClass();
			$member->payment_info->module = $data['payment_info_module'];				
		}
		//subscr_id
		if(isset($data['payment_info_subscr_id']) && !empty($data['payment_info_subscr_id'])) {			
			if(!isset($member->payment_info))
				$member->payment_info = new stdClass();
			$member->payment_info->subscr_id = $data['payment_info_subscr_id'];				
		}
		//txn_type
		if(isset($data['payment_info_txn_type']) && !empty($data['payment_info_txn_type'])) {			
			if(!isset($member->payment_info))
				$member->payment_info = new stdClass();
			$member->payment_info->txn_type = $data['payment_info_txn_type'];				
		}
		//txn_id
		if(isset($data['payment_info_txn_id']) && !empty($data['payment_info_txn_id'])) {			
			if(!isset($member->payment_info))
				$member->payment_info = new stdClass();
			$member->payment_info->txn_id = $data['payment_info_txn_id'];				
		}
		
		if($arr_resp['status']) {			
			$arr_resp['mgm_member'] = $member;			
		}
		
		//object fields:
//		$member->code = 'mgm_member';
//		$member->name = 'Member Lib';
//		$member->description = 'Member Lib';		
		
		//check this:
		$duration_exprs = null;
		unset($duration_exprs);
		$arr_status = null;
		unset($arr_status);
		if(function_exists('gc_collect_cycles'))
			gc_collect_cycles();
		
		return $arr_resp;
	}
	
	// export
	function _do_export(){
		global $wpdb;	
		// local
		extract($_POST);
		// create
		$migrate = & new mgm_migrate();
		// version
		$version = mgm_get_class('auth')->get_product_info('product_version');
		// file
		$filepath = MGM_FILES_EXPORT_DIR . 'export-'.$version.'-'.time().'.xml';
		// create
		$status = $migrate->create($filepath,$export_sections);		
		// return 
		return $filepath;
	}
	
	// core switch
	function _do_core_switch(){
		// track
		$status   = 'error';
		$message  = __('Core switch failed', 'mgm'); 
		$redirect = '';
		// response
		return json_encode(array('status'=>$status, 'message'=>$message));		
	}
	
	// core environment
	function _do_core_environment(){
		// local
		extract($_POST);
		// track
		$status   = 'error';
		$message  = __('Core environment setup failed.', 'mgm'); 
		$redirect = '';				
		// update
		if(isset($core_setup_execute) && !empty($core_setup_execute)){
			// update
			if(isset($_POST['jqueryui_version'])){
				update_option('mgm_jqueryui_version', $_POST['jqueryui_version']);
			}
			if(isset($_POST['disable_core_jquery'])){
				update_option('mgm_disable_core_jquery', $_POST['disable_core_jquery']);
			}else {
				//issue #1269
				update_option('mgm_disable_core_jquery', 'N');				
			}			
			// track
			$status   = 'success';
			$message  = __('Core environment setup completed successfully.', 'mgm'); 
			$redirect = 'admin.php?page=mgm/admin'; 
		}

		// response
		return json_encode(array('status'=>$status, 'message'=>$message, 'redirect'=>$redirect));
	}
	
	// upgrade execute
	function _do_upgrade(){
		// track
		$status   = 'error';
		$message  = __('Upgrade failed', 'mgm'); 
		$redirect = '';
		// response
		return json_encode(array('status'=>$status, 'message'=>$message));				
	}

	
}
// return name of class 
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin_tools.php
