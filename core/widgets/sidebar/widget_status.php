<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * status widget : multiple instance
 * front end instance
 *
 * @param array $args
 * @param array $widget_args
 * @return void
 * @since 1.0
 */
function mgm_sidebar_widget_status($args, $widget_args = 1){
	global $wpdb, $user_ID, $current_user, $mgm_sidebar_widget;
	extract($args, EXTR_SKIP);
	
	if (is_numeric($widget_args))
		$widget_args = array('number'=>$widget_args);	
	$widget_args = wp_parse_args($widget_args, array('number'=>-1));
	extract($widget_args, EXTR_SKIP);	

	$options = $mgm_sidebar_widget->status_widget;
	
	
	if (!isset($options[$number])) {
		return;
	}
	
	$title            = (isset($options[$number]['title']) ? $options[$number]['title']:__('Magic Members','mgm'));
	$logged_out_intro = (isset($options[$number]['logged_out_intro']) ? stripslashes($options[$number]['logged_out_intro']):$mgm_sidebar_widget->default_text['logged_out_intro']);
	$hide_logged_out  = (isset($options[$number]['hide_logged_out']) ? stripslashes($options[$number]['hide_logged_out']):false);

	// packs -issue#: 714
	$packs = mgm_get_class('subscription_packs');		
	
	if ($user_ID) {		

		echo $before_widget;

		if (trim($title)) {
			echo $before_title . $title . $after_title;
		}
	
		//issue#: 539		
		$member = mgm_get_member($user_ID);
		
		// check pack - issue#: 714
		$subs_pack = null;		

		if($member->pack_id){
			$subs_pack = $packs->get_pack($member->pack_id);
		}
		
		$uat = $member->membership_type;
		if (!$uat) {
			$uat = 'free';
		}
		
		$user_status = $member->status;
		
		if ($user_status != MGM_STATUS_ACTIVE || strtolower($uat) == 'free') {
			
			
			
			$inactive_intro = (isset($options[$number]['inactive_intro']) ? $options[$number]['inactive_intro']:$mgm_sidebar_widget->default_text['inactive_intro']);
			echo $inactive_intro;
			mgm_sidebar_register_links();

		} else {
			if ($expiry = $member->expire_date) {
				//issue#: 692
				$sformat = mgm_get_date_format('date_format_short');
				$expiry   = date($sformat, strtotime($expiry));			
				//$date = explode('-', $expiry);
				//$expiry = date(get_option('date_format'), mktime(0,0,0,$date[1], $date[2], $date[0]));
			} else {
				$expiry = __('None', 'mgm');
			}

			$active_intro = $mgm_sidebar_widget->default_text['active_intro'];
			if (isset($options[$number]['active_intro'])) {
				$active_intro = $options[$number]['active_intro'];
			}
				
			$active_intro = str_replace('[membership_type]', mgm_get_class('membership_types')->get_type_name($uat), $active_intro);
			$active_intro = str_replace('[expiry_date]', $expiry, $active_intro);
			
			// check hidden subscription pack - issue#: 714
			if((isset($subs_pack['hidden']) && $subs_pack['hidden'] != 1 ) || !isset($subs_pack['hidden'])){
				
				echo $active_intro;
			}

			mgm_render_my_purchased_posts($user_ID);
		}
		
		echo $after_widget;
	} else {
		if (!$hide_logged_out) {
			echo $before_widget;
			
			if (trim($title)) {
				echo $before_title . $title . $after_title;
			}
		
			echo $logged_out_intro;
			echo mgm_get_login_register_links();
			echo $after_widget;
		}
	}
}

/**
 * status widget : multiple instance
 * admin instance
 *
 * @param array $widget_args
 * @return void
 * @since 1.0
 */
function mgm_sidebar_widget_status_admin($widget_args=1) {
	global $wp_registered_widgets, $mgm_sidebar_widget;
	static $updated = false;

	if (is_numeric($widget_args))
		$widget_args = array('number'=>$widget_args);
		
	$widget_args = wp_parse_args($widget_args, array('number'=>-1));
	extract($widget_args, EXTR_SKIP);	

	$options = $mgm_sidebar_widget->status_widget;
	
	if (!is_array($options)) {
		$options = array();
	}
	
	// sidebar
	$m_sidebar = mgm_post_var('sidebar');
	
	// update
	if (!$updated && !empty($m_sidebar)) {
		$sidebar = (string) $m_sidebar;

		$sidebars_widgets = wp_get_sidebars_widgets();
		if (isset($sidebars_widgets[$sidebar])) {
			$this_sidebar =& $sidebars_widgets[$sidebar];
		} else {
			$this_sidebar = array();
		}

		foreach ($this_sidebar as $_widget_id) {
			// check
			if(  isset( $wp_registered_widgets[$_widget_id]['callback'] ) && isset( $wp_registered_widgets[$_widget_id]['params'][0]['number'] ) ){
				if ( 'mgm_widget_status' == $wp_registered_widgets[$_widget_id]['callback'] ) {
					$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
					if (!in_array("status-$widget_number", mgm_post_var('widget-id'))) {// the widget has been removed.
						unset($options[$widget_number]);
					}
				}
			}			
		}

		foreach ((array)mgm_post_var('mgm_widget_status') as $widget_number=>$mgm_widget_status) {
			if (!isset($mgm_widget_status['title']) && isset($options[$widget_number])) {// user clicked cancel
				continue;
			}
			
			// set vars
			$title         	  = isset($mgm_widget_status['title']) ? stripslashes($mgm_widget_status['title']) : '';
			$active_intro     = isset($mgm_widget_status['active_intro']) ? stripslashes($mgm_widget_status['active_intro']) : '';
			$inactive_intro   = isset($mgm_widget_status['inactive_intro']) ? stripslashes($mgm_widget_status['inactive_intro']) : '';
			$logged_out_intro = isset($mgm_widget_status['logged_out_intro']) ? stripslashes($mgm_widget_status['logged_out_intro']) : '';
			$hide_logged_out  = isset($mgm_widget_status['hide_logged_out']) ? (int)$mgm_widget_status['hide_logged_out'] : false;			
			// set
			$options[$widget_number] = compact('title', 'active_intro', 'inactive_intro', 'logged_out_intro', 'hide_logged_out');
		}
		
		$mgm_sidebar_widget->status_widget = $options;
		// update_option('mgm_sidebar_widget', $mgm_sidebar_widget);
		$mgm_sidebar_widget->save();
		// updated
		$updated = true;
	}

	// get selected	
	if (-1 == $number) {
		$number            = '%i%';
		$title             = __('Membership Status','mgm');
		$active_intro      = trim($mgm_sidebar_widget->default_text['active_intro']);
		$inactive_intro    = trim($mgm_sidebar_widget->default_text['inactive_intro']);	
		$logged_out_intro  = trim($mgm_sidebar_widget->default_text['logged_out_intro']);	
		$hide_logged_out   = 0;			
	} else {
		$title             = stripslashes($options[$number]['title']);
		$active_intro      = stripslashes($options[$number]['active_intro']);		
		$inactive_intro    = stripslashes($options[$number]['inactive_intro']);
		$logged_out_intro  = stripslashes($options[$number]['logged_out_intro']);
		$hide_logged_out   = (int)$options[$number]['hide_logged_out'];			
	}	
	
	// html
	$html = '<input type="hidden" name="mgm_sidebar_widget_submit" id="mgm_sidebar_widget_submit" value="1" />
				<p>
				<div class="mgm_margin_bottom_5px">
				<label for="mgm_sidebar_widget_title">
					<div><strong>' . __('Widget Title','mgm') . '</strong></div>
					<input class="mgm_width_300px" type="text" value="' . $title . '" id="mgm_widget_status_title_'.$number.'" name="mgm_widget_status['.$number.'][title]" />
				</label>
				</div>
				<div class="mgm_margin_bottom_5px">
				<label for="mgm_sidebar_widget_active_intro">
					<div><strong>' . __('User Active Introduction','mgm') . '</strong> - Use [membership_type] and [expiry_date]</div>
					<textarea rows="6" cols="50" id="mgm_widget_status_active_intro_'.$number.'" name="mgm_widget_status['.$number.'][active_intro]">' . $active_intro . '</textarea>
				</label>
				</div>
				<div class="mgm_margin_bottom_5px">				
				<label for="mgm_sidebar_widget_inactive_intro">
					<div><strong>' . __('User Inactive Introduction','mgm') . '</strong></div>
					<textarea rows="6" cols="50" id="mgm_widget_status_inactive_intro_'.$number.'" name="mgm_widget_status['.$number.'][inactive_intro]">' . $inactive_intro . '</textarea>
				</label>
				</div>
				<div class="mgm_margin_bottom_5px">				
				<label for="mgm_sidebar_widget_logged_out_intro">
					<div><strong>' . __('User Logged Out Introduction','mgm') . '</strong></div>
					<textarea rows="6" cols="50" id="mgm_widget_status_logged_out_intro_'.$number.'" name="mgm_widget_status['.$number.'][logged_out_intro]">' . $logged_out_intro . '</textarea>
				</label>
				</div>
				<div class="mgm_margin_bottom_5px">				
				<label for="mgm_sidebar_widget_hide_logged_out">
					<div><strong>' . __('Hide widget when user logged out?','mgm') . '</strong>
					<input type="checkbox" id="mgm_widget_status_hide_logged_out_'.$number.'" name="mgm_widget_status['.$number.'][hide_logged_out]" value="1" ' . ($hide_logged_out ? 'checked="checked"':'') . ' />
				</div>
				</label>
				</div>				
			</p>';
	// print
	print $html;					
}

/**
 * status widget : register with wordpress
 *
 * @param void
 * @return void
 * @since 1.0
 */
function mgm_sidebar_widget_status_register() {
	
	global $wp_registered_widgets, $mgm_sidebar_widget;
	
	//skip if on transactions page:
	foreach(mgm_get_payment_page_query_vars() as $query_var) {
		// set if
		if( $isset_quey_var = mgm_get_query_var($query_var) ){
			return;
		}
	}
	// payment
	if((isset($_GET['method']) && preg_match('/payment_/', $_GET['method'] ))) {
		return;
	}
	
	// $options[$number]['title']
	if ( !$options = $mgm_sidebar_widget->status_widget )
		$options = array();
	
	// defaults
	$widget_ops  = array();
	$control_ops = array('width' => 400, 'id_base' => 'mgm_sidebar_widget_status');
	
	// widget name
	$name = __('Magic Members Status','mgm');
	$registered = false;
	foreach ( array_keys($options) as $o ) {
	// Old widgets can have null values for some reason
		if (isset($options[$o]['title']) ) {
			$registered = true;
			$id = "mgm_sidebar_widget_status-$o"; 
			// register			
			wp_register_sidebar_widget( $id, $name, 'mgm_sidebar_widget_status', $widget_ops, array( 'number' => $o ) );
			wp_register_widget_control( $id, $name, 'mgm_sidebar_widget_status_admin', $control_ops, array( 'number' => $o ) );
		}
	}
	// If there are none, we register the widget's existence with a generic template
	if ( !$registered ) {
		wp_register_sidebar_widget( 'mgm_sidebar_widget_status-1', $name, 'mgm_sidebar_widget_status', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'mgm_sidebar_widget_status-1', $name, 'mgm_sidebar_widget_status_admin', $control_ops, array( 'number' => -1 ) );
	}
}
// register status widget
mgm_sidebar_widget_status_register();

/**
 * render purchased posts
 *
 * @param int $user_id
 * @param bool $sidebar
 * @param bool $return
 * @return string $html
 * @since 1.0
 */
function mgm_render_my_purchased_posts($user_id, $sidebar=true, $return=false) {
	global $wpdb;

	$html = '';
	
	$prefix = $wpdb->prefix;
	$sql = 'SELECT pp.post_id, p.post_title AS title
			FROM `' . TBL_MGM_POST_PURCHASES . '` pp 
			JOIN ' . $prefix . 'posts p ON (p.id = pp.post_id)
			WHERE pp.user_id = ' . $user_id;
	//echo $sql;		
	$results = $wpdb->get_results($sql,'ARRAY_A');

	if (!$sidebar) {
		if (count($results[0])) {
			$html .= '<div class="div_table"><div class="row"><div class="cell">Post Title</div></div>';

			foreach ($results as $result) {
				$link = get_permalink($result['post_id']);
				$title = $result['title'];
				if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
					$title = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($title);
				}

				$html .= '<div class="row"><div class="cell"><a href="' . $link . '">' . $title . '</a></div></div>';
			}

			$html .= '</div>';

		}
	} else {
		if (isset($results[0]) && count($results[0]) > 0) {
			$html .= '<div class="mgm_render_my_purchased_posts_div">Purchased Posts</div>';
			
			foreach ($results as $result) {
				$link = get_permalink($result['post_id']);

				$title = $result['title'];
				if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
					$title = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($title);
				}

				$html .= '<div><a href="' . $link . '">' . $title . '</a></div>';
			}
		}
	}
	
	if ($return) {
		return $html;
	} else {
		echo $html;
	}
}