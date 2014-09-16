<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * login widget : multiple instance
 * front end instance
 *
 * @param array $args
 * @param array $widget_args
 * @return void
 * @since 1.0
 */
function mgm_sidebar_widget_login($args, $widget_args = 1){
	global $user_ID, $current_user, $mgm_sidebar_widget;

	// if hide on custom login page 
	$post_id = get_the_ID();
	// post custom register	
	if($post_id>0){
		// if match
		if( get_permalink($post_id) == mgm_get_custom_url('login') )
			return "";
	}
	
	// actual widget
	extract($args, EXTR_SKIP);
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract($widget_args, EXTR_SKIP);	

	// get widget options
	$options = $mgm_sidebar_widget->login_widget;
	
	// validate
	if (!isset($options[$number])) {
		return;
	}	
	// home url
	$home_url                 = home_url();
	
	// get options
	$title_logged_in          = (isset($options[$number]['title_logged_in']) ? $options[$number]['title_logged_in']:__('Magic Membership Details','mgm'));
	$title_logged_out         = (isset($options[$number]['title_logged_out']) ? $options[$number]['title_logged_out']:__('Login','mgm'));
	$profile_text 		      = (isset($options[$number]['profile_text']) ? $options[$number]['profile_text']:__('Profile','mgm'));
	$membership_details_text  = (isset($options[$number]['membership_details_text']) ? $options[$number]['membership_details_text']:__('Membership Details','mgm'));
	$membership_contents_text = (isset($options[$number]['membership_contents_text']) ? $options[$number]['membership_contents_text']:__('Membership Contents','mgm'));
	$logout_text              = (isset($options[$number]['logout_text']) ? $options[$number]['logout_text']:__('Logout','mgm'));
	$register_text            = (isset($options[$number]['register_text']) ? $options[$number]['register_text']:__('Register','mgm'));
	$lostpassword_text        = (isset($options[$number]['lostpassword_text']) ? $options[$number]['lostpassword_text']:__('Lost your Password?','mgm'));
	$logged_out_intro         = (isset($options[$number]['logged_out_intro']) ? stripslashes($options[$number]['logged_out_intro']):'');
	
	// logged in user view
	if ($user_ID) {
		echo $before_widget;
		
		if (trim($title_logged_in)) {
			echo $before_title . $title_logged_in . $after_title;
		}
		
		//>=WP2.7 = DB9872
		if (get_option('db_version') >= 9872) {
			$logout_url = wp_logout_url($home_url);
		} else {
			//$logout_url = trailingslashit($home_url) . 'wp-login.php?action=logout';
			$logout_url = add_query_arg(array('action' => 'logout'), mgm_get_custom_field_array('login'));
		}
		
		// @todo check the actual reason
		$membership_details_link 	= mgm_get_custom_url('membership_details');
		$membership_contents_link 	= mgm_get_custom_url('membership_contents');
		$profile_link 				= mgm_get_custom_url('profile');
		
		// issue #945
		// $system_obj = mgm_get_class('system');
		// $membership_details_link 	= esc_html($system_obj->get_setting('membership_details_url'));
		// $membership_contents_link 	= esc_html($system_obj->get_setting('membership_contents_url'));	
		
		// set tmpl
		$logged_in_template = (isset($options[$number]['logged_in_template']) ? $options[$number]['logged_in_template'] : $mgm_sidebar_widget->default_text['logged_in_template']);
		$logged_in_template = str_replace('[display_name]', $current_user->display_name, $logged_in_template);
		$logged_in_template = str_replace('[membership_details_url]', $membership_details_link, $logged_in_template);		
		$logged_in_template = str_replace('[membership_details_link]', sprintf('<a href="%s">%s</a>',$membership_details_link, $membership_details_text), $logged_in_template);		
		$logged_in_template = str_replace('[membership_contents_url]', $membership_contents_link, $logged_in_template);		
		$logged_in_template = str_replace('[membership_contents_link]', sprintf('<a href="%s">%s</a>',$membership_contents_link, $membership_contents_text), $logged_in_template);		
		$logged_in_template = str_replace('[profile_url]', $profile_link, $logged_in_template);		
		$logged_in_template = str_replace('[profile_link]', sprintf('<a href="%s">%s</a>',$profile_link, $profile_text), $logged_in_template);

		//Issue #825
		$logged_in_template = str_replace('[logout_url]', $logout_url, $logged_in_template);
		$logged_in_template = str_replace('[logout_link]', '<a href="' . $logout_url . '">' . $logout_text . '</a>', $logged_in_template);

		echo $logged_in_template;

		echo $after_widget;
	} else {
		echo $before_widget;
		
		if (trim($title_logged_out)) {
			echo $before_title . $title_logged_out . $after_title;
		}		

		echo $logged_out_intro;

		echo mgm_sidebar_user_login_form($register_text, $lostpassword_text);

		echo $after_widget;
	}
}

/**
 * login widget : multiple instance
 * admin instance
 *
 * @param array $widget_args
 * @return void
 * @since 1.0
 */
function mgm_sidebar_widget_login_admin($widget_args = 1 ) {
	global $wp_registered_widgets, $mgm_sidebar_widget;
	static $updated = false;
	
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	// options init
	$options = $mgm_sidebar_widget->login_widget;
	
	// default
	if (!is_array($options)) {
		$options = array();
	}
	
	// m_sidebar
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
				if ( 'mgm_widget_login' == $wp_registered_widgets[$_widget_id]['callback'] ) {
					$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
					if (!in_array("login-$widget_number", mgm_post_var('widget-id'))) {// the widget has been removed.
						unset($options[$widget_number]);
					}
				}
			}			
		}
		
		// update
		foreach ((array)mgm_post_var('mgm_widget_login') as $widget_number=>$mgm_widget_login) {
			if (!isset($mgm_widget_login['title_logged_in']) && isset($options[$widget_number])) {// user clicked cancel
				continue;
			}
			
			// set vars
			$title_logged_in          = isset($mgm_widget_login['title_logged_in']) ? stripslashes($mgm_widget_login['title_logged_in']) : '';
			$title_logged_out         = isset($mgm_widget_login['title_logged_out']) ? stripslashes($mgm_widget_login['title_logged_out']) : '';
			$profile_text 			  = isset($mgm_widget_login['profile_text']) ? stripslashes($mgm_widget_login['profile_text']) : '';
			$membership_details_text  = isset($mgm_widget_login['membership_details_text']) ? stripslashes($mgm_widget_login['membership_details_text']) : '';
			$membership_contents_text = isset($mgm_widget_login['membership_contents_text']) ? stripslashes($mgm_widget_login['membership_contents_text']) : '';
			$logout_text              = isset($mgm_widget_login['logout_text']) ? stripslashes($mgm_widget_login['logout_text']) : '';
			$register_text            = isset($mgm_widget_login['register_text']) ? stripslashes($mgm_widget_login['register_text']) : '';
			$lostpassword_text        = isset($mgm_widget_login['lostpassword_text']) ? stripslashes($mgm_widget_login['lostpassword_text']) : '';
			$logged_out_intro         = isset($mgm_widget_login['logged_out_intro']) ? stripslashes($mgm_widget_login['logged_out_intro']) : ''; 			
			$logged_in_template       = isset($mgm_widget_login['logged_in_template']) ? stripslashes($mgm_widget_login['logged_in_template']) : '';
			
			// set
			$options[$widget_number]  = compact('title_logged_in', 'title_logged_out','profile_text','membership_contents_text', 'membership_details_text', 
												'logout_text','register_text','logged_out_intro','lostpassword_text','logged_in_template');
		}
		// set
		$mgm_sidebar_widget->login_widget = $options;
		// save
		$mgm_sidebar_widget->save();
		// updated
		$updated = true;
	}

	// get selected	
	if (-1 == $number) {
		$number                  = '%i%';
		$title_logged_in         = __('Membership Details','mgm');
		$title_logged_out        = __('Login','mgm');		
		$profile_text 			 = __('Profile','mgm');
		$membership_contents_text= __('Membership Contents','mgm');	
		$membership_details_text = __('Membership Details','mgm');			
		$logout_text             = __('Logout','mgm');
		$register_text           = __('Register','mgm');
		$lostpassword_text       = __('Lost your Password?','mgm');
		$logged_out_intro        = '';
		$logged_in_template      = $mgm_sidebar_widget->default_text['logged_in_template'];		
	} else {
		$title_logged_in         = stripslashes($options[$number]['title_logged_in']);
		$title_logged_out        = stripslashes($options[$number]['title_logged_out']);		
		$profile_text 			 = stripslashes($options[$number]['profile_text']);		
		$membership_contents_text= stripslashes($options[$number]['membership_contents_text']);		
		$membership_details_text = stripslashes($options[$number]['membership_details_text']);		
		$logout_text             = stripslashes($options[$number]['logout_text']);
		$register_text           = stripslashes($options[$number]['register_text']);
		$lostpassword_text       = stripslashes($options[$number]['lostpassword_text']);
		$logged_out_intro        = stripslashes($options[$number]['logged_out_intro']);
		$logged_in_template      = stripslashes($options[$number]['logged_in_template']);		
	}	
		
	// print
	$html = '<p>' . __('When logged out the user will see a login form. Removing the text from the "Register link text" or "Lost password link text" will subsequently remove the links they produce.', 'mgm') . '</p>
	<input type="hidden" name="mgm_widget_login['.$number.'][submit]" id="mgm-login-widget-submit-'.$number.'" value="1" />
	<div class="mgm_margin_bottom_5px">
		<div><label for="mgm-login-widget-widget-title"><strong>' . __('Widget Title (Logged in):','mgm') . '</strong></div>
		<input class="mgm_width_300px" value="' . $title_logged_in . '" id="mgm-login-widget-widget-title-logged-in-'.$number.'" name="mgm_widget_login['.$number.'][title_logged_in]" /></label>
	</div>
	<div class="mgm_margin_bottom_5px">
		<div><label for="mgm-login-widget-widget-title-logged-out"><strong>' . __('Widget Title (Logged out):','mgm') . '</strong></div>
		<input class="mgm_width_300px" value="' . $title_logged_out . '" id="mgm-login-widget-widget-title-logged-out-'.$number.'" name="mgm_widget_login['.$number.'][title_logged_out]" /></label>
	</div>
	<div class="mgm_margin_bottom_5px">
		<div><label for="mgm-login-widget-profile-text"><strong>' . __('Profile link text:','mgm') . '</strong></div>
		<input class="mgm_width_300px" value="' . $profile_text . '" id="mgm-login-widget-profile-text-'.$number.'" name="mgm_widget_login['.$number.'][profile_text]" /></label>
	</div>
	<div class="mgm_margin_bottom_5px">
		<div><label for="mgm-login-widget-membership-details-text"><strong>' . __('Membership Details link text:','mgm') . '</strong></div>
		<input class="mgm_width_300px" value="' . $membership_details_text . '" id="mgm-login-widget-membership-details-text-'.$number.'" name="mgm_widget_login['.$number.'][membership_details_text]" /></label>
	</div>
	<div class="mgm_margin_bottom_5px">
		<div><label for="mgm-login-widget-membership-contents-text"><strong>' . __('Membership Contents link text:','mgm') . '</strong></div>
		<input class="mgm_width_300px" value="' . $membership_contents_text . '" id="mgm-login-widget-membership-contents-text-'.$number.'" name="mgm_widget_login['.$number.'][membership_contents_text]" /></label>
	</div>
	<div class="mgm_margin_bottom_5px">
		<div><label for="mgm-login-widget-logout-text"><strong>' . __('Logout text:','mgm') . '</strong></div>
		<input class="mgm_width_300px" value="' . $logout_text . '" id="mgm-login-widget-logout-text-'.$number.'" name="mgm_widget_login['.$number.'][logout_text]" />
		</label>
	</div>
	<div class="mgm_margin_bottom_5px">
		<div><label for="mgm-login-widget-register-text"><strong>' . __('Register link text:','mgm') . '</strong></div>
		<input class="mgm_width_300px" value="' . $register_text . '" id="mgm-login-widget-register-text-'.$number.'" name="mgm_widget_login['.$number.'][register_text]" />
		</label>
	</div>
	<div class="mgm_margin_bottom_5px">
		<div><label for="mgm-login-widget-lostpassword-text"><strong>' . __('Lost password link text:','mgm') . '</strong></div>
		<input class="mgm_width_300px" value="' .$lostpassword_text . '" id="mgm-login-widget-lostpassword-text-'.$number.'"	name="mgm_widget_login['.$number.'][lostpassword_text]" /></label>
	</div>
	<div class="mgm_margin_bottom_5px">				
		<label for="mgm-login-widget-logged-out-intro">
			<div><strong>' . __('Logged Out Introduction','mgm') . '</strong></div>
			<textarea rows="2" cols="50" id="mgm-login-widget-logged-out-intro-'.$number.'" name="mgm_widget_login['.$number.'][logged_out_intro]">' . esc_html($logged_out_intro) . '</textarea>
		</label>
	</div>
	<div class="mgm_margin_bottom_5px">				
		<label for="mgm-login-widget-logged-in-template">
			<div><strong>' . __('Logged In Template','mgm') . '</strong> - Use the following hooks: [display_name], [profile_url], [profile_link], [membership_details_url], [membership_details_link],[membership_contents_url], [membership_contents_link], [logout_url], [logout_link]</div>
			<textarea rows="6" cols="50" id="mgm-login-widget-logged-in-template-'.$number.'" name="mgm_widget_login['.$number.'][logged_in_template]">' . $logged_in_template . '</textarea>
		</label>
	</div>
	';	
	// print
	print $html;
}

/**
 * login widget : register with wordpress
 *
 * @param void
 * @return void
 * @since 1.0
 */
function mgm_sidebar_widget_login_register() {
	global $wp_registered_widgets, $mgm_sidebar_widget;
	
	// $options[$number]['title']
	if ( !$options = $mgm_sidebar_widget->login_widget )
		$options = array();
	
	// defaults
	$widget_ops  = array();
	$control_ops = array('width' => 400, 'id_base' => 'mgm_sidebar_widget_login');
	
	// widget name
	$name = __('Magic Members Login','mgm');
	$registered = false;
	foreach ( array_keys($options) as $o ) {
	// Old widgets can have null values for some reason
		if (isset($options[$o]['title_logged_in']) ) {
			$registered = true;
			$id = "mgm_sidebar_widget_login-$o"; 
			// register			
			wp_register_sidebar_widget( $id, $name, 'mgm_sidebar_widget_login', $widget_ops, array( 'number' => $o ) );
			wp_register_widget_control( $id, $name, 'mgm_sidebar_widget_login_admin', $control_ops, array( 'number' => $o ) );
		}
	}
	// If there are none, we register the widget's existence with a generic template
	if ( !$registered ) {
		wp_register_sidebar_widget( 'mgm_sidebar_widget_login-1', $name, 'mgm_sidebar_widget_login', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'mgm_sidebar_widget_login-1', $name, 'mgm_sidebar_widget_login_admin', $control_ops, array( 'number' => -1 ) );
	}
}
// register login widget
mgm_sidebar_widget_login_register();