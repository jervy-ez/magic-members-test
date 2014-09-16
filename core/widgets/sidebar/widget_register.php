<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// ----------------------------------------------------------------------- 
/**
 * register widget : multiple instance
 * frontend instance
 *
 * @param array $args
 * @param array $widget_args
 * @return void
 * @since 1.0
 */
function mgm_sidebar_widget_registration($args, $widget_args = 1){
	global $wpdb, $user_ID, $current_user, $mgm_sidebar_widget;
	extract($args, EXTR_SKIP);
	
	if (is_numeric($widget_args))
		$widget_args = array('number'=>$widget_args);	
	$widget_args = wp_parse_args($widget_args, array('number'=>-1));
	extract($widget_args, EXTR_SKIP);	

	// options init
	$options = $mgm_sidebar_widget->register_widget;

	// mgm_pr($options);

	// check
	if (!isset($options[$number])) {
		return;
	}
	//skip widget if BUDDYPRESS is loaded
	if(defined('BP_VERSION')) {
		return;
	}
	

	//skip registation page:
	if(in_array(trailingslashit(mgm_current_url()),array(trailingslashit(mgm_get_custom_url('register'))), trailingslashit(mgm_get_custom_url('register', true))) )	
		return;
	

	// skip if on transactions page:	
	foreach(mgm_get_payment_page_query_vars() as $query_var) {
		// set if
		if( $isset_query_var = mgm_get_query_var($query_var) ){
			return;
		}
	}
	

	// check
	if((isset($_GET['method']) && preg_match('/payment_/', $_GET['method'] ))) {
		return;
	}	
	
	
	// set
	$title             = (isset($options[$number]['title']) ? $options[$number]['title'] : __('Magic Members - Register','mgm'));
	$intro             = (isset($options[$number]['intro']) ? $options[$number]['intro'] : '');
	$use_custom_fields = (isset($options[$number]['use_custom_fields']) ? $options[$number]['use_custom_fields']: true);
	//Issue #777
	$default_subscription_pack = (isset($options[$number]['default_subscription_pack']) ? $options[$number]['default_subscription_pack']: false);
	
	// user looged in
	if (!$user_ID) {
		// if hide on custom register page 
		$post_id = get_the_ID();
		// post custom register	
		if($post_id>0){
			// if match
			if( get_permalink($post_id) == mgm_get_custom_url('register') )
				return "";
		}	
		
		// start actual widget
		echo $before_widget;
		
		if ($title) {
			echo $before_title . $title . $after_title;
		}
					
		// echo $intro;
		
		echo mgm_sidebar_user_register_form($use_custom_fields, $default_subscription_pack);
		
		echo $after_widget;
	}
	
}

/**
 * register widget : multiple instance
 * admin instance
 *
 * @param array $widget_args
 * @return void
 * @since 1.0
 */
function mgm_sidebar_widget_registration_admin($widget_args=1) {
	global $wp_registered_widgets, $mgm_sidebar_widget;
	static $updated = false;
	
	if (is_numeric($widget_args))
		$widget_args = array('number'=>$widget_args);		
	$widget_args = wp_parse_args($widget_args, array('number'=>-1));
	extract($widget_args, EXTR_SKIP);	
	
	// options init
	$options = $mgm_sidebar_widget->register_widget;
	
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
			if( isset( $wp_registered_widgets[$_widget_id]['callback'] ) && isset( $wp_registered_widgets[$_widget_id]['params'][0]['number'] ) ){
				if ( 'mgm_widget_registration' == $wp_registered_widgets[$_widget_id]['callback'] ) {
					$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
					if (!in_array("registration-$widget_number", mgm_post_var('widget-id'))) {// the widget has been removed.
						unset($options[$widget_number]);
					}
				}
			}			
		}
		
		// update
		foreach ((array)mgm_post_var('mgm_widget_registration') as $widget_number=>$mgm_widget_registration) {
			if (!isset($mgm_widget_registration['title']) && isset($options[$widget_number])) {// user clicked cancel
				continue;
			}
			
			// set vars
			$title             = isset($mgm_widget_registration['title']) ? stripslashes($mgm_widget_registration['title']) : '';
			$intro             = isset($mgm_widget_registration['intro']) ? stripslashes($mgm_widget_registration['intro']) : '';
			$use_custom_fields = isset($mgm_widget_registration['use_custom_fields']) ? $mgm_widget_registration['use_custom_fields'] : false;			
			//Issue #777		
			$default_subscription_pack = isset($mgm_widget_registration['default_subscription_pack']) ? $mgm_widget_registration['default_subscription_pack'] : 'free';			
			// set
			$options[$widget_number] = compact('title', 'intro', 'use_custom_fields','default_subscription_pack');
		}
		
		// update
		$mgm_sidebar_widget->register_widget = $options;
		// save
		$mgm_sidebar_widget->save();
		// updated
		$updated = true;
	}
	
	// get selected	
	if (-1 == $number) {
		$number            = '%i%';
		// convert to js expression
		$js_number         = '_i_';
		$title             = __('Register','mgm');
		$intro             = trim($mgm_sidebar_widget->default_text['active_intro']);
		$use_custom_fields = false;
		$default_subscription_pack = 'free';
	} else {
		// convert to js expression
		$js_number         = $number;
		$title             = stripslashes($options[$number]['title']);
		$intro             = stripslashes($options[$number]['intro']);		
		$use_custom_fields = $options[$number]['use_custom_fields'];		
		$default_subscription_pack = $options[$number]['default_subscription_pack'];			
	}	

	// Issue #777
	$subscription_pack_list = sprintf('<option value="-">%s</option>', __('Select','mgm'));
	foreach ($packages = mgm_get_subscription_packages() as $pack) {	
		if($default_subscription_pack == $pack['key']){
			$subscription_pack_list	.= sprintf('<option selected="selected" value="%s">%s</option>', $pack['key'], $pack['label']);
		}else {		
			$subscription_pack_list	.= sprintf('<option value="%s">%s</option>', $pack['key'], $pack['label']);
		}
	}
	
	// generate html
	$html = '<input type="hidden" name="mgm_widget_registration['.$number.'][submit]" id="mgm_widget_registration_submit_'.$js_number.'" value="1" />
			 <p>
				<div class="mgm_margin_bottom_5px">
					<label for="mgm_register_sidebar_widget_title">
						<div><strong>' . __('Widget Title','mgm') . '</strong></div>
						<input class="mgm_width_300px" type="text" name="mgm_widget_registration['.$number.'][title]" id="mgm_widget_registration_title_'.$js_number.'"  value="' . $title . '"/>
					</label>
				</div>
				<div class="mgm_margin_bottom_5px">
					<label for="mgm_register_sidebar_widget_use_custom_fields">						
						<input class="mgm_width_30px" type="checkbox" ' . ($use_custom_fields? 'checked="checked"':'') . ' 
						name="mgm_widget_registration['.$number.'][use_custom_fields]" id="mgm_widget_registration_use_custom_fields_'.$js_number.'" value="1"/>
						<strong>' . __('Use Custom Fields in form?','mgm') . '</strong>
					</label>
				</div>
				<div class="mgm_margin_bottom_5px" id="cusFldDropdown_'.$number.'">
					<label for="mgm_register__widget_default_subscription_pack">						
						<div><strong>' . __('Select default subscription pack ','mgm') . '</strong></div>
						<select class="mgm_width_300px" name="mgm_widget_registration['.$number.'][default_subscription_pack]" 
						id="mgm_widget_registration_default_subscription_pack_'.$js_number.'">'.$subscription_pack_list.'</select>
					</label>
				</div>
				<div class="mgm_margin_bottom_5px">
					<label for="mgm_register_sidebar_widget_active_intro">
						<div><strong>' . __('Introduction','mgm') . '</strong></div>
						<textarea rows="6" cols="50" name="mgm_widget_registration['.$number.'][intro]" id="mgm_widget_registration_intro_'.$js_number.'" >' . $intro . '</textarea>
					</label>
				</div>
			 </p>';

	// script
	$html .= '<script language="javascript">
	jQuery(document).ready(function(){			
		if(jQuery("#mgm_widget_registration_use_custom_fields_'.$js_number.'").val() == 1){
			//issue #1298
			if(jQuery("#mgm_widget_registration_use_custom_fields_'.$js_number.'").is(":checked")){			
				jQuery("#cusFldDropdown_'.$js_number.'").hide();
			}
		}else{
			jQuery("#cusFldDropdown_'.$js_number.'").show();
		}
	
		jQuery("#mgm_widget_registration_use_custom_fields_'.$js_number.'").click(function() {			
			if(this.checked){	
				jQuery("#cusFldDropdown_'.$js_number.'").hide();
				jQuery("#mgm_widget_registration_use_custom_fields_'.$js_number.'").val(1);
			}else{
				jQuery("#cusFldDropdown_'.$js_number.'").show();
				jQuery("#mgm_widget_registration_use_custom_fields_'.$js_number.'").val(1);				  
			}
		});
	});	
	</script>';	
	
	// print
	print $html;	
}

/**
 * register widget : register with wordpress
 *
 * @param void
 * @return void
 * @since 1.0
 */
function mgm_sidebar_widget_registration_register() {
	global $wp_registered_widgets, $mgm_sidebar_widget;
	
	// $options[$number]['title']
	if ( !$options = $mgm_sidebar_widget->register_widget )
		$options = array();
	
	// defaults
	$widget_ops  = array();
	$control_ops = array('width' => 400, 'id_base' => 'mgm_sidebar_widget_registration');
	
	// widget name
	$name = __('Magic Members Register','mgm');
	$registered = false;
	foreach ( array_keys($options) as $o ) {
	// Old widgets can have null values for some reason
		if (isset($options[$o]['title']) ) {
			$registered = true;
			$id = "mgm_sidebar_widget_registration-$o"; 
			// register			
			wp_register_sidebar_widget( $id, $name, 'mgm_sidebar_widget_registration', $widget_ops, array( 'number' => $o ) );
			wp_register_widget_control( $id, $name, 'mgm_sidebar_widget_registration_admin', $control_ops, array( 'number' => $o ) );
		}
	}
	// If there are none, we register the widget's existence with a generic template
	if ( !$registered ) {
		wp_register_sidebar_widget( 'mgm_sidebar_widget_registration-1', $name, 'mgm_sidebar_widget_registration', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'mgm_sidebar_widget_registration-1', $name, 'mgm_sidebar_widget_registration_admin', $control_ops, array( 'number' => -1 ) );
	}
}
// register registration widget
mgm_sidebar_widget_registration_register();
