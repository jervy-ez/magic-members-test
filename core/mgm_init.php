<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members Init class
 *
 * @package MagicMembers
 * @since 2.0
 */
class mgm_init{	
	// loaded files
	var $_loaded_files = array();
	// files to load
	var $_files        = array();
	
	// construct
	function __construct(){
		// php 4
		$this->mgm_init();
	}
	
	// php4 construct
	function mgm_init(){
		// session initialization:
		if(isset($this) && is_object($this)){			
			add_action('init', array($this, '_initialize_session'));
		}
		// define internal constants
		$this->_constants();	
		// load library files	
		$this->_autoload();			
		// create dirs
		$this->_dirs();		
	}
	
	// setup
	function setup(){		
		// check environment
		if($this->_check_environment()){
		// loaded 
			add_action('plugins_loaded', array($this, 'plugins_loaded'), 100);
		}
	}
		
	// loaded
	function plugins_loaded(){			
		// do the conversion	
		$this->_conversion();		
		// check cookie
		mgm_check_cookie();		
		// email filters
		// $this->_set_mail_filters();	deprecated, using ondemand filters in mgm_mail()		
		// add after verify, must be accessible to non admins too		
		$mgm_auth = mgm_get_class('auth');		
		// verify
		if($mgm_auth->verify()){			
			// cron_schedules hook to add new schedules mgm uses
			add_filter('cron_schedules', 'mgm_add_cron_schedules');
			// cron - used for reminder mails, with this any page hit will fire the cron, not limited to admin 			
			foreach(mgm_get_defined_schedules() as $schedule_name => $event_name){	
				add_action($event_name, str_replace('mgm_', 'mgm_process_', $event_name) ); // mgm_process_daily_schedule
			}						
		}		
		// wp-admin actions						
		if (is_admin()){ 	  
	 	 	// add menu	
	 	 	add_action('admin_menu', array($this, 'admin_menu'));	
			// ajax handler
			add_action('wp_ajax_mgm_admin_ajax_action', array($this, 'admin_ajax_action'));// handler "mgm_admin_ajax_action"
			// adjust ui conflicts always
			$this->_adjust_ui_conflicts();							
			// scripts, load scripts and css only when mgm interface is loaded/edit post page
			// if(mgm_if_load_admin_scripts()) add_action('init', array($this, 'admin_load_scripts')); // deprecated, read #1158			
			// activation hook
			register_activation_hook(MGM_PLUGIN_NAME, array($this, 'activate'));	
			// deactivation hook
			register_deactivation_hook(MGM_PLUGIN_NAME, array($this, 'deactivate'));
			// force activation once
			$this->activate();	
		}/*else {					
			// check version and include jquery. as calling wp_enqueue_scripts() directly is not allowed after 3.3
			// deprecated, moved to hooks/mgm_asset_hooks.php
			if (version_compare(get_bloginfo('version'), '3.3', '<')) {
				wp_enqueue_script('jquery');
			}else{
				add_action('wp_enqueue_scripts', array($this, '_include_jquery'));
			}
		}*/
		
		// show messages if any 
		if (is_super_admin() ){			
			// daily schedule not installed and mgm version found, plugin is installed but cron is not installed
			if( !wp_get_schedule('mgm_daily_schedule') && get_option('mgm_version') ){	
				add_action('admin_notices', array('mgm_notice','daily_schedule_not_setup'));
			}		
		}
		// global variable for dynamic scripts;		
		global $mgm_scripts;	
	}	
	
	// activate
	function activate(){
		global $wpdb;				
		// get auth
		$auth = mgm_get_class('auth');
		// verify key		
		if($auth->verify()){					
			// migration once / it will take care 	
			require_once('migration/mgm_migrate.php');											
			
			// check and set each schedules
			foreach(mgm_get_defined_schedules() as $schedule_name => $event_name){			
				// set up daily cron event, once			
				if( !wp_next_scheduled($event_name) ){
					// add
					wp_schedule_event(time(), $schedule_name, $event_name); // the name of event/schedule hook	
				}			
			}
			// create pages run always now, only when new pages added it will execute
			mgm_create_custom_pages();	
			// run others once
			if(!get_option('mgm_version') || (version_compare(get_option('mgm_upgrade_id'), '1.8', '<'))){// add version/upgrade compare, transaction added on 1.8 and pages later
				// create pages
				// mgm_create_custom_pages();				
				// enable active modules
				$payment_modules = mgm_get_class('system')->get_active_modules('payment');
				// active modules
				if($payment_modules){
					// loop
					foreach($payment_modules as $module){
						// install modules
						mgm_get_module($module, 'payment')->enable();// enable only
					}
				}	
				// update
				update_option('mgm_version', $auth->get_product_info('product_version')); // version
				update_option('mgm_build', $auth->get_product_info('product_build')); // build			
			}				
		}			
	}	
	
	// deactivate
	function deactivate($force=false){
		global $wpdb;		
		// if remove by force
		if($force){
			// uninstall options	
			$wpdb->query("DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE 'mgm_%' ");
			// user meta			
			$wpdb->query("DELETE FROM `{$wpdb->usermeta}` WHERE `meta_key` LIKE 'mgm_%' OR `meta_key` LIKE '_mgm_%'");
			// post meta				
			$wpdb->query("DELETE FROM `{$wpdb->postmeta}` WHERE `meta_key` LIKE '_mgm_%' ");
			// tables			
			foreach(mgm_get_tables() as $table){		
				$wpdb->query("DROP TABLE IF EXISTS `{$table}`");		
			}
		}
		// clear event hooks
		foreach(mgm_get_defined_schedules() as $schedule_name => $event_name){	
			wp_clear_scheduled_hook($event_name);
		}
		// clear dashboard cache
		mgm_delete_transients();
	}
	
	// admin menu
	function admin_menu(){	
		// default
		$page = 'mgm/admin';
		// page
		if ( isset($_GET['page']) && preg_match('#^mgm/admin#',$_GET['page']) ){
			$page = strip_tags($_GET['page']);
			// flag
			define('MGM_ADMINUI_SCREEN', true);
		}
		
		// current user
		$current_user = wp_get_current_user();
		// current user rolw
		$current_user_role = (isset($current_user->roles[0])) ? $current_user->roles[0] : 'subscriber';
		// add main menu
		// check the Primary capability mgm_root is enabled for the logged in user
		// if enabled , make Plugin link accessible for the user role
		if (mgm_is_mgm_menu_enabled('primary', 'mgm_root')) {    		
			add_menu_page(__('Magic Members','mgm'), __('Magic Members','mgm'), $current_user_role, $page, array($this, 'admin_load_ui'), MGM_ASSETS_URL.'images/icons/status_offline.png');
		}				
		// add after verify
		if(mgm_get_class('auth')->verify() && !is_super_admin()){
			// profile menu
			add_submenu_page('profile.php', __('Membership Details','mgm'), __('Membership Details','mgm'), $current_user_role, 'mgm/membership_details',array($this, 'admin_load_ui'));
			// restricted
			add_submenu_page('profile.php', __('Membership Contents','mgm'), __('Membership Contents','mgm'), $current_user_role, 'mgm/membership_contents',array($this, 'admin_load_ui'));		
		}
	}
	
	// loads page on action
	function admin_load_ui($page=NULL) {						
		// page
		if(!$page) $page = strip_tags($_GET['page']) ;	
		// default
		$method = null;		
		// particular pages
		switch($page){
			case 'mgm/membership_details':	
			case 'mgm/membership_contents':
				// method
				$method = str_replace('mgm/', '', $page);
				//$page   = str_replace('mgm/', 'mgm/admin/', $page);
				$page   = 'mgm_admin';
			break;
		}			
		// set page
		$page_name = str_replace('/', '_', $page);	// mgm_admin_%classname%
		// page file
		$page_file = MGM_CORE_DIR . 'admin' . DIRECTORY_SEPARATOR . $page_name . '.php';
		$error = __('Error loading Controller!','mgm');
		// check file
		if(file_exists($page_file)){							
			// load page class 					
			$page_class = @include_once($page_file); 				
			// echo $page_class;
			if(class_exists($page_class)){
				// object
				$page_class_obj = new $page_class;
				// load
				$page_class_obj->init($method);
				// exit
				return ;		
			}else{
				$error = sprintf(__('Controller class "%s" does not exist!','mgm'), $page_class);
			}
		}else{			
			$error = sprintf(__('Controller file "%s" does not exist!','mgm'), $page_file);
		}		
		// error
		die($error);			
	}	
	
	// wp ajax handler
	function admin_ajax_action(){
		global $wpdb; // this is how you get access to the database		
		// page
		$page =  $_REQUEST['page'];		
		// load ui
		$this->admin_load_ui($page);
		// die
		die(); // this is required to return a proper result
	}
		
	// private ------------------------------------------------
	// define core constants
	function _constants(){
		// directory separator
		define('MGM_DS'                 , DIRECTORY_SEPARATOR); 		
				 
		// base dir
 		define('MGM_BASE_DIR'           , WP_PLUGIN_DIR . MGM_DS . 'magicmembers'. MGM_DS);
		define('MGM_BASE_URL'           , WP_PLUGIN_URL . '/magicmembers/'); 
					
		// base names/paths for plugin activation
		define('MGM_PLUGIN_NAME'        , trailingslashit('magicmembers/magicmembers.php') ); // magicmembers/magicmembers.php/
		define('MGM_PLUGIN_CORE_NAME'   , trailingslashit(dirname( plugin_basename( __FILE__ )))); // magicmembers/core
		define('MGM_CORE_DIR'           , plugin_dir_path( __FILE__ ) );// absolute path to this folder , with trailing slash
		define('MGM_CORE_URL'           , plugin_dir_url( __FILE__ ) );	// absolute url to this folder , with trailing slash		  
		
		// assets
		define('MGM_ASSETS_DIR'         , MGM_CORE_DIR . 'assets' . MGM_DS);	
		define('MGM_ASSETS_URL'         , MGM_CORE_URL . 'assets/' );	
		
		// core library
		define('MGM_LIBRARY_DIR'        , MGM_CORE_DIR . 'libs' . MGM_DS );	
		define('MGM_LIBRARY_URL'        , MGM_CORE_URL . 'libs/' );	
			
		// core hooks & processors
		define('MGM_HOOKS_DIR'          , MGM_CORE_DIR . 'hooks' . MGM_DS ); 
		define('MGM_HOOKS_URL'          , MGM_CORE_URL . 'hooks/' );
			
		// core modules base dir
		define('MGM_MODULE_BASE_DIR'    , MGM_CORE_DIR . 'modules' . MGM_DS );	
		define('MGM_MODULE_BASE_URL'    , MGM_CORE_URL . 'modules/' );	
		
		// core plugins
		define('MGM_PLUGIN_BASE_DIR'    , MGM_CORE_DIR . 'plugins' . MGM_DS ); 
		define('MGM_PLUGIN_BASE_URL'    , MGM_CORE_URL . 'plugins/' );
		
		// core widgets
		define('MGM_WIDGET_DIR'         , MGM_CORE_DIR . 'widgets' . MGM_DS ); 
		define('MGM_WIDGET_URL'         , MGM_CORE_URL . 'widgets/' );	
		
		// core api
		define('MGM_API_DIR'            , MGM_CORE_DIR . 'api' . MGM_DS ); 
		define('MGM_API_URL'            , MGM_CORE_URL . 'api/' );				
		
		// get extend dirpath from setting
		if( !$extend_dir = get_option('mgm_extend_dir') ){					
			// update 
			update_option('mgm_extend_dir', trailingslashit(PLUGINDIR . '/magicmembers/extend'));
		}
		
		// extend dir
		if(!defined('MGM_EXTEND_DIR')){
			define('MGM_EXTEND_DIR'     ,  ABSPATH . $extend_dir); 
		}
		
		// extend url
		if(!defined('MGM_EXTEND_URL')){
			define('MGM_EXTEND_URL'     , home_url($extend_dir) ); 
		}
		
		// extended libs dir
		if(!defined('MGM_EXTEND_LIBRARY_DIR')){
			define('MGM_EXTEND_LIBRARY_DIR'    , MGM_EXTEND_DIR . 'libs' . MGM_DS ); 
		}
		// extended libs url
		if(!defined('MGM_EXTEND_LIBRARY_URL')){
			define('MGM_EXTEND_LIBRARY_URL'    , MGM_EXTEND_URL . 'libs/' ); 
		}
		
		// extended modules base dir
		if(!defined('MGM_EXTEND_MODULE_BASE_DIR')){
			define('MGM_EXTEND_MODULE_BASE_DIR'    , MGM_EXTEND_DIR . 'modules' . MGM_DS ); 
		}
		// extended modules base url
		if(!defined('MGM_EXTEND_MODULE_BASE_URL')){
			define('MGM_EXTEND_MODULE_BASE_URL'    , MGM_EXTEND_URL . 'modules/' ); 
		}
		
		// extended plugins base dir
		if(!defined('MGM_EXTEND_PLUGIN_BASE_DIR')){
			define('MGM_EXTEND_PLUGIN_BASE_DIR'    , MGM_EXTEND_DIR . 'plugins' . MGM_DS ); 
		}
		// extended plugins base url
		if(!defined('MGM_EXTEND_PLUGIN_BASE_URL')){
			define('MGM_EXTEND_PLUGIN_BASE_URL'    , MGM_EXTEND_URL . 'plugins/' ); 
		}
		
		// extended widgets dir
		if(!defined('MGM_EXTEND_WIDGET_DIR')){
			define('MGM_EXTEND_WIDGET_DIR'    , MGM_EXTEND_DIR . 'widgets' . MGM_DS ); 
		}
		// extended widgets url
		if(!defined('MGM_EXTEND_WIDGET_URL')){
			define('MGM_EXTEND_WIDGET_URL'    , MGM_EXTEND_URL . 'widgets/' ); 
		}
		
		// wp uploads dir
		if(!defined('WP_UPLOAD_DIR')){
			define('WP_UPLOAD_DIR'            , WP_CONTENT_DIR . MGM_DS . 'uploads' . MGM_DS );
		}
		
		// wp uploads url
		if(!defined('WP_UPLOAD_URL')){
			define('WP_UPLOAD_URL'            , WP_CONTENT_URL . '/uploads/' );		
		}
		
		// mgm files
		define('MGM_FILES_DIR'                , WP_UPLOAD_DIR . 'mgm' . MGM_DS);		
		define('MGM_FILES_URL'                , WP_UPLOAD_URL . 'mgm/' );		
			
		// download files
		define('MGM_FILES_DOWNLOAD_DIR'       , MGM_FILES_DIR . 'downloads' . MGM_DS);		
		define('MGM_FILES_DOWNLOAD_URL'       , MGM_FILES_URL . 'downloads/' );	
		
		// export files
		define('MGM_FILES_EXPORT_DIR'         , MGM_FILES_DIR . 'exports' . MGM_DS);		
		define('MGM_FILES_EXPORT_URL'         , MGM_FILES_URL . 'exports/' );	
		
		// export files
		define('MGM_FILES_IMPORT_DIR'         , MGM_FILES_DIR . 'imports' . MGM_DS);		
		define('MGM_FILES_IMPORT_URL'         , MGM_FILES_URL . 'imports/' );	
			
		// log files
		define('MGM_FILES_LOG_DIR'            , MGM_FILES_DIR . 'logs' . MGM_DS);		
		define('MGM_FILES_LOG_URL'            , MGM_FILES_URL . 'logs/' );		
		// module files
		define('MGM_FILES_MODULE_DIR'         , MGM_FILES_DIR . 'modules' . MGM_DS);		
		define('MGM_FILES_MODULE_URL'         , MGM_FILES_URL . 'modules/' );	
		
		// image files
		define('MGM_FILES_UPLOADED_IMAGE_DIR' , MGM_FILES_DIR . 'images' . MGM_DS);		
		define('MGM_FILES_UPLOADED_IMAGE_URL' , MGM_FILES_URL . 'images/' );	
	}
	
	// library files
	function _autoload(){		
		// localazing the label issue# 617
		// load language domain	
		load_plugin_textdomain( 'mgm', false, MGM_PLUGIN_CORE_NAME . 'lang' );	
			
		// set path to core/libs
		set_include_path(get_include_path(). PATH_SEPARATOR . implode(MGM_DS, array(MGM_CORE_DIR,'libs')));
		// read extend configure
		if(file_exists(MGM_EXTEND_DIR . 'configure.php')) require_once(MGM_EXTEND_DIR . 'configure.php');
		
		// core namespace
		$core_namespace = 'mgm_';		
		// scan core dirs, only auto loaded resources included here, maintain sequence		
		$core_dirs = array('configs', 'core', 'components', 'classes', 'utilities', 'functions', 'helpers');
		// loop core dirs	
		foreach($core_dirs as $core_dir){	
			// scan
			$this->_add_files(MGM_LIBRARY_DIR . $core_dir . MGM_DS . $core_namespace . '*.php');
		}			
		
		// extend namespace
		$extend_namespace = (isset($mgm_config['ext']['prefix'])) ? $mgm_config['ext']['prefix'] : 'mgmx_';	
				
		// scan extend dirs, only auto loaded resources
		$extend_dirs = array('core', 'components', 'classes', 'functions', 'widgets', 'helpers');
		// loop core dirs	
		foreach($extend_dirs as $extend_dir){	
			// extended core files
			$this->_add_files(MGM_EXTEND_LIBRARY_DIR . $extend_dir . MGM_DS . $extend_namespace . '*.php');
		}		
		
		// load core / extend files
		$this->_load_files(true);			
			
		// init widgets later so that overload can actually work
		$this->_init_widgets();
		
		// if auth, load core hooks / widgets
		if( mgm_get_class('auth')->verify() ){			
			// widget files
			$this->_add_files(MGM_WIDGET_DIR . $core_namespace . 'widget_*.php');	
			// hooks files
			$this->_add_files(MGM_HOOKS_DIR  . $core_namespace . '*.php');	
			// load files
			$this->_load_files();	
		}else{
		// just load the assets
			// hooks files
			$this->_add_file(MGM_HOOKS_DIR  . $core_namespace . 'asset_hooks.php');	
			// load files
			$this->_load_files();			
		}			
	}
	
	// load files
	function _load_files($reset=false){		
		// check
		if( $this->_files ){
			// loop for base/object file
			foreach($this->_files as $_file){					
				// check
				if(in_array(basename($_file), array('mgm_base.php', 'mgm_object.php'))){						 			
					// include
					@include_once( $_file );
					// store
					$this->_loaded_files[] = basename($_file);
				}
			}			
			// load others
			foreach($this->_files as $_file){							 			
				// check loaded
				if(in_array(basename($_file),$this->_loaded_files)) continue;
				// include				
				@include_once( $_file );					
				// store				
				$this->_loaded_files[] = basename($_file);
			}
		}	
		
		// reset
		$this->_files = array();
	}
	
	// init widgets
	function _init_widgets(){
		global $mgm_widgets;
		// check
		if($mgm_widgets){
			// loop
			foreach($mgm_widgets as $mgm_widget){
				// init
				$mgm_widget->init();
			}
		}
	}		

	/**
	 * add email filters
	 * @deprecated
	 * @see mgm_mail()
	 */
	function _set_mail_filters(){		
		// filters
		// add_filter('wp_mail_from'        , 'mgm_get_mail_from'        , 10);
		// issue#1346
		// add_filter('wp_mail_from_name'   , 'mgm_get_mail_from_name'   , 10);
		// issue#: 473, set html type in certain types only
		// add_filter('wp_mail_content_type', array($this, 'mgm_get_mail_content_type'), 10);
		// add_filter('wp_mail_charset'     , 'mgm_get_mail_charset'     , 10);	
	}
	
	// adjust ui conflicts	
	function _adjust_ui_conflicts(){
		// check page and remove conflicts, mainly css and jQuery 	for admin
		if(isset($_GET['page']) && preg_match('#^mgm/admin#',$_GET['page'])){			
			// remove tubepress ui css
			remove_action('admin_init', array('org_tubepress_env_wordpress_Admin',  'initAction'));// class method
			
			// remove event_espresso ui css
			remove_action('admin_print_styles', 'event_espresso_config_page_styles'); // function
			
			// remove wordpress simple survey ui css		
			remove_action('admin_init', 'wpss_admin_register_init'); // function	
			
			// remove tubepress ui css
			if( has_action('admin_init', 'pp_add_init') ){
				remove_action('admin_init', 'pp_add_init');// Lotus theme
			}
			// remove wp-ad-minister css
			remove_action( 'init', 'administer_load_scripts');			
			//Blubrry PowerPress ui conflict with tabs
			if( has_action('admin_init', 'powerpress_admin_init') ){
				remove_action('admin_init', 'powerpress_admin_init');
			}				
			// wp https ssl
			// add_filter('force_ssl' , array($this,'wphttps_force_ssl'), 10, 3);	
		}
		// always	
		
		// remove headway gzip headers, must be called before init as its hooked into init itself 
		add_filter('headway_gzip', array($this,'_disable_headway_gzip'));	
	}	
	
	// disable headway gzip header for admin
	function _disable_headway_gzip($gzip){
		return false;
	}
		
	// add files
	function _add_files($pattern){
		// get files
		if($_files = glob($pattern)){			
			// add
			$this->_files = array_merge($this->_files, $_files);
		}
	}	
	
	// add file
	function _add_file($file){
		// add
		$this->_files = array_merge($this->_files, array($file));		
	}
	
	//session
	function _initialize_session() {
		if( !session_id() ){
			@session_start();
		}	
	}
	
	// class conversion
	function _conversion(){
		// version_compare(get_option('mgm_build'), '2.22', '<=') || !get_option('mgm_auth_options')
		if(!get_option('mgm_class_conversion')){			
			// fix all
			mgm_fix_class_conversion();// fix
			// update
			update_option('mgm_class_conversion', time());
		}	
		// fix / convert users
		if(!get_option('mgm_converted_users')){
			mgm_fix_users();
		}
		// fix / convert posts
		if(!get_option('mgm_converted_posts')){
			mgm_fix_posts();
		}	
	}
	
	// default dirs
	function _dirs(){					  
		// check root permission
		if(!is_writable(WP_CONTENT_DIR) && !is_writable(MGM_FILES_DIR)){
			add_action('admin_notices', array('mgm_notice','file_stotage_not_writable')); return;
		}
		
		// dirs
		$dirs = array(WP_UPLOAD_DIR, MGM_FILES_DIR, MGM_FILES_EXPORT_DIR, MGM_FILES_IMPORT_DIR,
					  MGM_FILES_DOWNLOAD_DIR, MGM_FILES_LOG_DIR, MGM_FILES_MODULE_DIR, 
					  MGM_FILES_UPLOADED_IMAGE_DIR);
					  
		// dirs			  
		mgm_create_files_dir($dirs);	
	}
	
	// check environment
	function _check_environment(){
		// init
		$env_ok = true;
		// check	
		if(/*is_super_admin() && */get_option('permalink_structure') == ''){
			// notice
			add_action('admin_notices', array('mgm_notice','default_permalink_error')); 
			// set flag
			$env_ok = false;
		}
		// return
		return $env_ok;
	}
	
	// load jquery
	function _include_jquery() {
		wp_enqueue_script('jquery');
	}
}
// return name of class
return basename(__FILE__,'.php');
// end file /core/mgm_init.php