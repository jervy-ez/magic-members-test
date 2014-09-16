<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * asset hooks and callbacks
 *
 * @package MagicMembers
 * @since 2.7.0
 */
/**
 * enque screen wise front assets 
 *
 * @param string hook_suffix (file name)
 * @todo add all case
 */ 
function mgm_enqueue_scripts(){
	global $wp_version;	
}
add_action('wp_enqueue_scripts ', 'mgm_enqueue_scripts', 10);

function mgm_print_styles(){
	global $wp_styles;
	
	// mgm_pr($wp_styles);
}
add_action('wp_print_styles', 'mgm_print_styles', 10);
/**
 * enque screen wise admin assets 
 *
 * @param string hook_suffix (file name)
 * @todo add all case
 */
function mgm_admin_enqueue_scripts($hook_suffix){
	global $wp_version,$wp_styles,$wp_scripts;

	// check and disable jquery ui
	if( (isset($_GET['page']) && preg_match('#^mgm/admin#', $_GET['page'])) /*|| defined('MGM_ADMINUI_SCREEN')*/ ){
		// remove style by handler
		$style_handles = array('jquery-ui-css','jquery-ui','aiow-plugin-css','smoothness');
		// reset
		foreach ($style_handles as $style_handle) {
			if( isset($wp_styles->registered[$style_handle]) ){
				unset($wp_styles->registered[$style_handle]);
			}
		}
		// remove script by handler
		$script_handles = array('jquery-ui');
		// reset
		foreach ($script_handles as $script_handle) {
			if( isset($wp_scripts->registered[$script_handle]) ){
				unset($wp_scripts->registered[$script_handle]);
			}
		}
	}
	// screen
	$screen_id = get_current_screen()->id;	
	// edit taxonomy
	if(preg_match('/^edit-/', $screen_id)){
		if( 'edit-tags.php' == $hook_suffix){
			$screen_id = 'edit-taxonomy';
		}
	}
	// echo $screen_id;
	// post screen
	switch($screen_id){
		case 'post':// post edit,add
		case 'page':// page @todo custompage
		// case 'edit-post':// post listing
			// load respective jQueryUI	
			$jqueryui_version =  sprintf('js/jquery/jquery.ui/jquery-ui-%s.min.js', mgm_get_jqueryui_version());
			// load ui
			wp_enqueue_script('mgm-jquery-ui', (MGM_ASSETS_URL . $jqueryui_version), array('jquery'));
			// custom scripts
			wp_enqueue_script('mgm-helpers', MGM_ASSETS_URL . 'js/helpers.js'); 				
			
			// ui css	
			wp_enqueue_style('mgm-jqueryui-css', MGM_ASSETS_URL . 'css/default/mgm/jquery.ui.css' );	
			wp_enqueue_style('mgm-widgets-css', MGM_ASSETS_URL . 'css/admin/mgm.widgets.css' );				
		break;
		case 'edit-category':
		case 'edit-post_tag':
		case 'edit-taxonomy':
			// styles
			wp_enqueue_style('mgm-adminui-css', MGM_ASSETS_URL . 'css/admin/mgm.adminui.css' );	
			// wp 3.3+ fix
			if ( mgm_compare_wp_version( '3.3', '>' ) ){
				wp_enqueue_style('mgm-adminui-wp3fix-css', MGM_ASSETS_URL . 'css/admin/mgm.adminui.wp3fix.css' );
			}
		break;
		case 'users':// list
		// case 'user':// add
		case 'user-edit':// edit
			wp_enqueue_style('mgm-users-css', MGM_ASSETS_URL . 'css/admin/mgm.users.css' );
		break;		
		case 'dashboard':
			wp_enqueue_style('mgm-dashboard-css', MGM_ASSETS_URL . 'css/admin/mgm.dashboard.css' );
		break;
		case 'profile_page_mgm/membership_details':
		case 'profile_page_mgm/membership_contents':
			// profile pages
			// ui css	
			wp_enqueue_style('mgm-ui-css', MGM_ASSETS_URL . 'css/default/mgm/jquery.ui.css' );								
			// styles
			wp_enqueue_style('mgm-adminui-css', MGM_ASSETS_URL . 'css/admin/mgm.adminui.css' );	
			// styles
			wp_enqueue_style('mgm-wp-profile-css', MGM_ASSETS_URL . 'css/admin/mgm.wp-profile.css' );	
		break;
		case 'toplevel_page_mgm/admin':// mgm admin ui
		
			// load respective jQueryUI	
			$jqueryui_version =  sprintf('js/jquery/jquery.ui/jquery-ui-%s.min.js', mgm_get_jqueryui_version());
			// load ui
			wp_enqueue_script('mgm-jquery-ui', (MGM_ASSETS_URL . $jqueryui_version), array('jquery'));	
			
			// helpers scripts		
			wp_enqueue_script('mgm-jquery-validate', (MGM_ASSETS_URL . 'js/jquery/jquery.validate.pack.js'));
			wp_enqueue_script('mgm-jquery-metadata', (MGM_ASSETS_URL . 'js/jquery/jquery.metadata.js'));
			wp_enqueue_script('mgm-jquery-form', (MGM_ASSETS_URL . 'js/jquery/jquery.form.js'));  
			
			// custom scripts
			wp_enqueue_script('mgm-helpers', MGM_ASSETS_URL . 'js/helpers.js'); 		
			wp_enqueue_script('mgm-string', MGM_ASSETS_URL . 'js/string.js'); 						
			wp_enqueue_script('mgm-jquery-helpers', MGM_ASSETS_URL . 'js/jquery/jquery.helpers.js'); 		
				
			// helpers scripts		
			wp_enqueue_script('mgm-jquery-ajaxupload', MGM_ASSETS_URL . 'js/jquery/jquery.ajaxfileupload.js'); // ?? is used
			wp_enqueue_script('mgm-jquery-scrollto', MGM_ASSETS_URL . 'js/jquery/jquery.scrollTo-min.js');
			wp_enqueue_script('mgm-jquery-corner', MGM_ASSETS_URL . 'js/jquery/jquery.corner.js');	
			// wp_enqueue_script('mgm-jquery-tools', MGM_ASSETS_URL . 'js/jquery/jquery.tools.min.js');				
			// wp_enqueue_script('mgm-jquery-tools', MGM_ASSETS_URL . 'js/jquery/messi/messi.min.js');				
			wp_enqueue_script('mgm-nicedit', MGM_ASSETS_URL . 'js/nicedit/nicedit.js');		
			wp_enqueue_script('mgm-checkboxtree', MGM_ASSETS_URL . 'js/jquery/jquery.tree.js');			
			wp_enqueue_script('mgm-excanvas-min', MGM_ASSETS_URL . 'js/flot/excanvas.min.js');			
			wp_enqueue_script('mgm-jquery-flot', MGM_ASSETS_URL . 'js/flot/jquery.flot.js');					
			
			// ui css	
			wp_enqueue_style('mgm-ui-css', MGM_ASSETS_URL . 'css/default/mgm/jquery.ui.css' );								
			// styles
			wp_enqueue_style('mgm-adminui-css', MGM_ASSETS_URL . 'css/admin/mgm.adminui.css' );	
			// wp 3.3+ fix
			if ( mgm_compare_wp_version( '3.3', '>' ) ){
				wp_enqueue_style('mgm-adminui-wp3fix-css', MGM_ASSETS_URL . 'css/admin/mgm.adminui.wp3fix.css' );
			}
			// other
			// wp_enqueue_style('mgm-admin-overlay-css', MGM_ASSETS_URL . 'css/admin/jquery.overlay.css' );	
			// wp_enqueue_style('mgm-admin-overlay-css', MGM_ASSETS_URL . 'css/admin/messi/messi.min.css' );	
			wp_enqueue_style('mgm-admin-checkboxtree-css', MGM_ASSETS_URL . 'css/admin/jquery.tree.css' );
		break;
		case 'widgets':
			// styles
			wp_enqueue_style('mgm-widgets-css', MGM_ASSETS_URL . 'css/admin/mgm.widgets.css' );	
		break;
		case 'profile_page_mgm/profile':
		case 'profile_page_mgm/membership/content':
			// styles
			wp_enqueue_style('mgm-adminui-css', MGM_ASSETS_URL . 'css/admin/mgm.adminui.css' );
			wp_enqueue_style('mgm-pages-css', MGM_ASSETS_URL . 'css/default/mgm.pages.css' );
		break;
	}	
}
// add action
add_action('admin_enqueue_scripts', 'mgm_admin_enqueue_scripts', 10, 1); 

/**
 * enque screen wise wp-login assets 
 *
 * @param void
 */
function mgm_login_enqueue_scripts(){
	global $wp_version;	
	
	// load ui	
	// wp_enqueue_script('mgm-login-js', MGM_ASSETS_URL . 'js/wp-login.js', array('jquery'));		
}	
// add action
add_action('login_enqueue_scripts', 'mgm_login_enqueue_scripts', 10);

function mgm_login_footer_scripts(){
	global $wp_version;	
	
	if(bool_from_yn(mgm_get_setting('enable_email_as_username'))):?>
	<script language="javascript">
		jQuery(document).ready(function(){
			if ( document.getElementById('loginform') )
				document.getElementById('loginform').childNodes[1].childNodes[1].childNodes[0].nodeValue = '<?php echo esc_js( __( 'Username or Email', 'email-login' ) ); ?>'; 		
			
	    });
	</script>
	<?php
	endif;
}
add_action('login_footer', 'mgm_login_footer_scripts');

/**
 * enque screen wise wp site assets 
 *
 * @param void
 */
function mgm_wp_enqueue_scripts(){
	global $wp_version;	
	
	// jquery
	wp_enqueue_script('jquery');
}	 
// add action
add_action('wp_enqueue_scripts', 'mgm_wp_enqueue_scripts', 11);
/***
if (version_compare(get_bloginfo('version'), '3.3', '<')) {
	mgm_wp_enqueue_scripts();
}else{
	add_action('wp_enqueue_scripts', 'mgm_wp_enqueue_scripts', 11);
}
**/
// end of file