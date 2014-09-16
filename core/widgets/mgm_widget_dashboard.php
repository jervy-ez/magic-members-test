<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Dashboard Widgets
 *
 * @package MagicMembers
 * @since 3.0 
 */

/**
 * Dashboard Statistics
 */
function mgm_dashboard_widget_statistics() {?>
	
	<div id="mgm-dashboard-stats-widget">
		<?php echo mgm_get_loading_icon('Loading', 'block');?>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery('#mgm-dashboard-stats-widget').load(ajaxurl+'?action=mgm_admin_ajax_action&page=mgm/admin&method=wp_dashboard_widget_statistics')	;
		});
	</script>
	<?php		
} 

/**
 * add dashboard widgets
 */
function mgm_add_dashboard_widgets() {
	// check dashboard is enabled
	if(mgm_is_mgm_menu_enabled('primary', 'mgm_widget_dashboard_statistics')) {
		wp_add_dashboard_widget('mgm_dashboard_widget_statistics', __('Magic Members Statistics','mgm'), 'mgm_dashboard_widget_statistics');	
	}
} 

/**
 * add dashboard widgets to hook
 */
add_action('wp_dashboard_setup', 'mgm_add_dashboard_widgets' );

function mgm_add_dashboard_styles(){
	
    echo '<!-- custom admin css -->
          <link rel="stylesheet" type="text/css" href="' . MGM_ASSETS_URL . 'css/admin/mgm.dashboard.css" />
          <!-- /end custom adming css -->';
}
// add_action('admin_head', 'mgm_add_dashboard_styles');

// end file core/widgets/mgm_widget_dashboard.php