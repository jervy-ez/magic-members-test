<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * text widget : multiple instance
 * front end instance
 *
 * @param array $args
 * @param array $widget_args
 * @return void
 * @since 1.0
 */
function mgm_sidebar_widget_text($args, $widget_args = 1){
	global $mgm_sidebar_widget, $user_ID;
	extract($args, EXTR_SKIP);
	
	if (is_numeric($widget_args))
		$widget_args = array('number'=>$widget_args);	
	$widget_args = wp_parse_args($widget_args, array('number'=>-1));
	extract($widget_args, EXTR_SKIP);	

	$options = $mgm_sidebar_widget->text_widget;
	
	if (!isset($options[$number])) {
		return;
	}
		
	$user_memtypes = array();
	$available_to    = explode('|', $options[$number]['access_membership_types']);
	//$membership_type = strtolower(mgm_get_user_membership_type(false, 'code'));
	// issue#: 843

	// fetch subscribed membership types
	$access = false;
	// Issue #1029
	if(empty($options[$number]['access_membership_types']) || (count($available_to) === 1 && 'guest' == strtolower($available_to[0])) && !$user_ID) {
		$access = true;
	}

	if (!$access && $user_ID) {
		if (!is_super_admin()) {
			$user_memtypes = mgm_get_subscribed_membershiptypes($user_ID);			
			foreach ($available_to as $available) {
				//if ($membership_type == strtolower($available)) {
				if (in_array(strtolower($available), $user_memtypes)) {	
					$access = true;
					break;
				}
			}
		}else
			$access = true;	
	}
	
	// has access
	if ($access) {
		$title = apply_filters('mgm_sidebar_widget_text_title', $options[$number]['title']);
		$text  = apply_filters('mgm_sidebar_widget_text_text', $options[$number]['text']);
		?>
		<?php echo $before_widget; ?>
		<?php if (!empty($title)) { echo $before_title . $title . $after_title; } ?>
		<div class="textwidget"><?php echo $text; ?></div>
		<?php echo $after_widget;
	}
}

/**
 * text widget : multiple instance
 * admin instance
 *
 * @param array $widget_args
 * @return void
 * @since 1.0
 */
function mgm_sidebar_widget_text_admin($widget_args=1) {
	global $wp_registered_widgets, $mgm_sidebar_widget;
	static $updated = false;

	if (is_numeric($widget_args))
		$widget_args = array('number'=>$widget_args);
		
	$widget_args = wp_parse_args($widget_args, array('number'=>-1));
	extract($widget_args, EXTR_SKIP);	

	$options = $mgm_sidebar_widget->text_widget;
	if (!is_array($options)) {
		$options = array();
	}
	
	// m_sidebar
	$m_sidebar = mgm_post_var('sidebar');
	
	// updated
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
				if ( 'mgm_widget_text' == $wp_registered_widgets[$_widget_id]['callback'] ) {
					$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
					if (!in_array("text-$widget_number", mgm_post_var('widget-id'))) {// the widget has been removed.
						unset($options[$widget_number]);
					}
				}
			}
		}

		foreach ((array)mgm_post_var('mgm_widget_text') as $widget_number=>$mgm_widget_text) {
			if (!isset($mgm_widget_text['text']) && isset($options[$widget_number])) {// user clicked cancel
				continue;
			}
			// title
			$title = isset($mgm_widget_text['title']) ? strip_tags(stripslashes($mgm_widget_text['title'])) : '';
			// text
			if (current_user_can('unfiltered_html')) {
				$text = isset($mgm_widget_text['text']) ? stripslashes($mgm_widget_text['text']) : '';
			} else {
				$text = isset($mgm_widget_text['text']) ? stripslashes(wp_filter_post_kses($mgm_widget_text['text'])) : '';
			}
			// types
			$access_membership_types = isset($mgm_widget_text['access_membership_types']) ? implode('|', $mgm_widget_text['access_membership_types']) : '';
			// set
			$options[$widget_number] = compact('title', 'text', 'access_membership_types');
		}
		// set
		$mgm_sidebar_widget->text_widget = $options;
		// update_option('mgm_sidebar_widget', $mgm_sidebar_widget);
		$mgm_sidebar_widget->save();
		// updated
		$updated = true;
	}
	
	// get available membership types
	$membership_types = mgm_get_class('membership_types')->membership_types;	
	// selected
    $selected_membership_types = array();
	// get selected	
	if (-1 == $number) {
		$number = '%i%';
		$title  = '';
		$text   = '';		
		$selected_membership_types = implode(';', $membership_types);
	} else {
		$title = esc_attr($options[$number]['title']);
		$text  = format_to_edit($options[$number]['text']);
		if(isset($options[$number]['access_membership_types'])){
			$selected_membership_types = explode('|',$options[$number]['access_membership_types']);
		}
	}

	echo '<p>'.__('Available to','mgm').':<br />';
	
	foreach ((array)$membership_types as $type_code=>$type_name) {
		if(is_array($selected_membership_types)){
			$c = (in_array($type_code, $selected_membership_types) ? 'checked="checked"':'');
		}else{
			$c ='';
		}

		echo '<input type="checkbox" id="mgm_widget_text_' . $number . '" class="checkbox" name="mgm_widget_text[' . $number . '][access_membership_types][]" value="' . $type_code . '" ' . $c . ' />
			  &nbsp;&nbsp;<label class="mgm_font_italic" for="' . __($type_code,'mgm') . '">' . __($type_name,'mgm') . '</label>&nbsp;&nbsp;';
	}

	echo '</p>';
	?>
	<p>
		<label><?php _e('Title','mgm')?>:</label> 		
		<input class="widefat" id="mgm_widget_text_<?php echo $number; ?>" name="mgm_widget_text[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
	</p>
	<p>
		<label><?php _e('Text','mgm')?>: </label>
		<textarea class="widefat" rows="16" cols="20" id="mgm_widget_text_<?php echo $number; ?>" name="mgm_widget_text[<?php echo $number; ?>][text]"><?php echo $text; ?></textarea>
		<input type="hidden" name="mgm_widget_text[<?php echo $number; ?>][submit]" value="1" />
	</p>
	<?php	
}

/**
 * text widget : register with wordpress
 *
 * @param void
 * @return void
 * @since 1.0
 */
function mgm_sidebar_widget_text_register() {
	global $wp_registered_widgets, $mgm_sidebar_widget;
	
	// $options[$number]['title']
	if ( !$options = $mgm_sidebar_widget->text_widget )
		$options = array();
	
	// defaults
	$widget_ops  = array();
	$control_ops = array('width' => 400, 'id_base' => 'mgm_sidebar_widget_text');
	
	// widget name
	$name = __('Magic Members Text','mgm');
	$registered = false;
	foreach ( array_keys($options) as $o ) {
	// Old widgets can have null values for some reason
		if (isset($options[$o]['title']) ) {
			$registered = true;
			$id = "mgm_sidebar_widget_text-$o"; 
			// register			
			wp_register_sidebar_widget( $id, $name, 'mgm_sidebar_widget_text', $widget_ops, array( 'number' => $o ) );
			wp_register_widget_control( $id, $name, 'mgm_sidebar_widget_text_admin', $control_ops, array( 'number' => $o ) );
		}
	}
	// If there are none, we register the widget's existence with a generic template
	if ( !$registered ) {
		wp_register_sidebar_widget( 'mgm_sidebar_widget_text-1', $name, 'mgm_sidebar_widget_text', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'mgm_sidebar_widget_text-1', $name, 'mgm_sidebar_widget_text_admin', $control_ops, array( 'number' => -1 ) );
	}
}
// register text widget
mgm_sidebar_widget_text_register();
