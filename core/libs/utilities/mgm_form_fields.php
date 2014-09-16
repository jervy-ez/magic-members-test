<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members form fields generation utility class
 *
 * @package MagicMembers
 * @since 2.5
 */ 
class mgm_form_fields{
	// config
	var $config = array();
	
	// construct
	function __construct($config=array()){
		// php4
		$this->mgm_form_fields($config);
	}
	
	// php4 construct
	function mgm_form_fields($config=array()){
		// defaults
		$config = !empty($config) ? $config : array('wordpres_form'=>false);
		// set 
		$this->set_config($config);
	}
	
	// set_config
	function set_config($config=array()){		
		// set
		$this->config = $config;		
	}
	
	// get_config
	function get_config($key,$default=''){
		// set
		return (isset($this->config[$key])) ? $this->config[$key] : $default;
	}
	
	// generate element
	function get_field_element($field, $name='custom_field', $value=''){
		// check first callback by name
		if(method_exists($this, 'field_'.$field['name'].'_callback')){
			return call_user_func_array(array($this, 'field_'.$field['name'].'_callback'), array($field,$name,$value));
		}
		// check element by type
		if(method_exists($this, 'field_type_'.$field['type'])){
			return call_user_func_array(array($this, 'field_type_'.$field['type']), array($field,$name,$value));
		}				
		// error
		if(isset($field['name'])){
			// leave error
			return sprintf(__('No formatter for %s', 'mgm'), $field['type']);
		}
		// return 
		return '';
	}
	
	// by type /////////////////////////////////////////////////////////////////////////////////////////
	// input type
	function field_type_input($field,$name,$value){
		// value filter
		$value = $this->_filtered_value($field,$name,$value);		
		// readonly
		$readonly = (isset($field['attributes']) && isset($field['attributes']['readonly']) && (bool)$field['attributes']['readonly']==true) ? 'readonly="readonly"' : '';
		// classes, default name
		$classes = array($name);
		// required
		if(isset($field['attributes']) && $field['attributes']['required']){
			$classes[] = 'required';
		}
		// extra
		if(isset($field['attributes']) && isset($field['attributes']['class'])){
			$classes[] = $field['attributes']['class'];
		}
		// join 
		$class = implode(' ',$classes);
		// return
		return sprintf('<input type="%s" name="%s" value="%s" class="%s" %s/>',$field['type'],$this->_get_element_name($field,$name),$value,$class,$readonly);	
	}
	
	// text
	function field_type_text($field,$name,$value){
		// return
		return $this->field_type_input($field,$name,$value);		
	}
	
	// hidden
	function field_type_hidden($field,$name,$value){
		// return
		return $this->field_type_input($field,$name,$value);		
	}	
	
	// password
	function field_type_password($field,$name,$value){
		// return
		return $this->field_type_input($field,$name,$value);	
	}
	
	// textarea
	function field_type_textarea($field,$name, $value){		
		// value filter - issue #1427
		//$value = $this->_filtered_value($field,$name,$value);
		// readonly
		$readonly = (isset($field['attributes']) && $field['attributes']['readonly']) ?'readonly="readonly"':'';
		// classes, default name
		$classes = array($name);
		// required
		if(isset($field['attributes']) && $field['attributes']['required']){
			$classes[] = 'required';
		}
		// extra		
		$classes[] = 'mgm_field_textarea';	
		// extra
		if(isset($field['attributes']) && isset($field['attributes']['class'])){
			$classes[] = $field['attributes']['class'];
		}	
		// join 
		$class = implode(' ',$classes);
		if ($posted = $this->_posted_value($field, $name)) {
			$value = $posted;
		}
		// return
		return sprintf('<textarea name="%s" class="%s" %s>%s</textarea>',$this->_get_element_name($field,$name),$class,$readonly,$value);
	}
	
	// checkbox
	function field_type_checkbox($field,$name,$value){
		// options
		$options = preg_split('/[;,]+/', $field['options']);
		// check
		if(count($options)) {
			// value
			$value = $this->_filtered_value($field,$name,$value);
			// return
			return mgm_make_checkbox_group(sprintf('%s[]',$this->_get_element_name($field,$name)),$options,$value,MGM_VALUE_ONLY,'','div');
		}	
		// return default
		return $this->field_type_input($field,$name,$value);	
	}
	
	// checkbox group
	function field_type_checkboxg($field,$name,$value){
		// options
		$options = preg_split('/[;,]+/', $field['options']);
		// check
		if(count($options)) {
			// value
			$value = $this->_filtered_value($field,$name,$value);
			// return
			return mgm_make_checkbox_group(sprintf('%s[]',$this->_get_element_name($field,$name)),$options,$value,MGM_VALUE_ONLY,'','div');
		}	
		// return default
		return $this->field_type_input($field,$name,$value);	
	}

	// radio
	function field_type_radio($field,$name,$value){
		// options
		$options = preg_split('/[;,]+/', $field['options']); 		
		// check
		if(count($options)){
			// value
			$value = $this->_filtered_value($field,$name,$value);	
			// return
			return mgm_make_radio_group(sprintf('%s',$this->_get_element_name($field,$name)),$options,$value,MGM_VALUE_ONLY,'','div');
		}	
		// return default
		return $this->field_type_input($field,$name,$value);	
	}
	
	// select
	function field_type_select($field,$name,$value,$options=NULL,$type=MGM_VALUE_ONLY,$sel_match='DEFAULT',$size=1){
		// get options
		$options = ($options) ? $options : preg_split('/[;,]+/',$field['options']); 
		// value
		$value = $this->_filtered_value($field,$name,$value);	
		// make options
		$options = mgm_make_combo_options($options,$value,$type,false,$sel_match);		
		// readonly
		$readonly = (isset($field['attributes']) && $field['attributes']['readonly']) ?'readonly="readonly"':'';
		// options
		if(isset($field['attributes']) && $field['attributes']['readonly'] && is_array($options) && isset($options[$value]) && !empty($options[$value]))
			$options = array(array_search($value, $options) => $value);
		// classes, default name
		$classes = array($name);
		// required
		if(isset($field['attributes']) && $field['attributes']['required']){
			$classes[] = 'required';
		}			
		// extra
		if(isset($field['attributes']) && isset($field['attributes']['class'])){
			$classes[] = $field['attributes']['class'];
		}
		// join 
		$class = implode(' ',$classes);
		// multiple
		$multiple = '';
		if( $size > 1 ) {
			$multiple = sprintf( 'multiple size="%d"', $size);
			// return
			return sprintf('<select name="%s[]" class="%s" %s %s>%s</select>',$this->_get_element_name($field,$name),$class,$readonly,$multiple,$options);
		}
		// return
		return sprintf('<select name="%s" class="%s" %s %s>%s</select>',$this->_get_element_name($field,$name),$class,$readonly,$multiple,$options);
	}
	
	// select multiple
	// @todo set property for size
	function field_type_selectm($field,$name,$value,$options=NULL,$type=MGM_VALUE_ONLY,$sel_match='DEFAULT'){
		return $this->field_type_select($field, $name, $value, $options, $type, $sel_match, 4);	
	}
		
	// label
	function field_type_label($field,$name,$value){
		// return
		$value = esc_attr( mgm_stripslashes_deep( ( ( isset($value) && !empty( $value) ) ? $value : $field['value']) ) );	
		// return
		return sprintf('<label class="mgm_field_label">%s</label>',$value);
	}
	
	// html
	function field_type_html($field,$name,$value){
		// value
		$value = $value ? $value : $field['value'];
		//Commented the below line for issue #1066
		//$value = esc_attr( mgm_stripslashes_deep( ( ( isset($value) && !empty( $value) ) ? $value : $field['value']) ) );	
		$value  = html_entity_decode(mgm_stripslashes_deep($value));
		// return
		return sprintf('<div class="mgm_field_html">%s</div>',$value);
	}
	
	// image
	function field_type_image($field,$name,$value){		
		// init		  
		$content = '';		
		// make sure readonly if not admin
		$read_only = ($field['attributes']['readonly'] && !is_super_admin()) ? true : false;
		// check
		if(!empty($value)) {
			$url   = $value;  			
			$image = sprintf('<img src="%s" alt="%s" >', $url, basename($url) );
			// set
			if(!empty($value) && !$read_only) {
				$image .= sprintf('&nbsp;<span onclick="delete_upload(this,\'%s\',\'%s\')"><img style="cursor:pointer;" src="'.MGM_ASSETS_URL . '/images/icons/cross.png" alt="%s" title="%s"></span>', $this->_get_element_name($field,$name),$field['name'], __('Delete','mgm'), __('Delete','mgm') );
			}
			$content = $image;
		}
		// check		
		if(empty($content) && !$read_only) {
			$type     = 'file';
			$content .= sprintf('<input type="%s" name="%s" id="%s" class="mgm_field_file">',$type, $this->_get_element_name($field,$name), $this->_get_element_id($field,$name));
			$content .= sprintf('&nbsp;<img id="%s" src="%s" alt="%s" title="%s">', 'uploader_loading_'.$field['name'], esc_url( admin_url( 'images/wpspin_light.gif' ) ), __('Loading','mgm'), __('Loading','mgm') );
		}
		
		$type     = 'hidden';		
		$content .= sprintf('<br/><input type="%s" name="%s" id="%s" %s>', $type, $this->_get_element_name($field,$name), $this->_get_element_id($field,$name), (!empty($value) ? ' value="'.esc_attr($url).'" ' : ''));
		// return 
		return sprintf('<div class="mgm_field_image"><div class="mgm_file_browse_wrapper">%s</div></div>', $content);
	}
	
	// captcha 
	function field_type_captcha($field,$name,$value=''){
		// captcha
		$recaptcha = mgm_get_class('recaptcha')->recaptcha_get_html();
		// return
		return sprintf('<div class="mgm_field_captcha">%s</div>', $recaptcha);
	}

	// datepicker
	function field_type_datepicker($field,$name,$value){
		// extra class
		if(!$field['attributes']['readonly']) 
			$field['attributes']['class'] = 'mgm_date';
		// return
		return $this->field_type_input($field,$name,$value);
	}
	
	// by type end/////////////////////////////////////////////////////////////////////////////////////////
	
	// by name ////////////////////////////////////////////////////////////////////////////////////////////	
	
	// autoresponder
	function field_autoresponder_callback($field,$name,$value){
		// selectd on post		
		$value = $this->_posted_value($field,$name,$value);		
		// pre checked values, only when these are set as default value in custom field, 
		// it will be pre-checked, otherwise only posted value will be used
		$pre_checked_values = array('Y', 'y', 'YES', 'yes', 'TRUE', 'true', '1');// @deprected as of #1309 
		// allow only checkbox/radio/hidden fields
		switch ($field['type']) {
			case 'checkbox':
				// checked
				// $checked = (in_array($value, $pre_checked_values) || ($value == $field['value'])) ? 'checked="true"' : '';
				$checked = ( isset($field['attributes']['auto_checked']) && (bool)$field['attributes']['auto_checked'] == true ) ? 'checked="checked"'  : '';
				// html
				$html = sprintf('<input type="checkbox" name="%s" value="%s" align="absmiddle" %s/>',$this->_get_element_name($field,$name),$field['value'],$checked);
			break;				
			case 'radio':
				// split
				$options = preg_split('/[;,]+/', $field['options']);
				// check
				if (!empty($options)) {
					// loop through optons and print each radios
					foreach($options as $option) {
						// checked
						$checked = (in_array($value, $pre_checked_values) || ($value == $option)) ? 'checked="true"' : '';
						// html
						$html .= sprintf('<input type="radio" name="%s" value="%s" align="absmiddle" %s/>&nbsp;%s',$this->_get_element_name($field,$name), $option, $checked, $option);
					}
				}				
			break;	
			case 'hidden':		
				$checked = ( isset($field['attributes']['auto_checked']) && (bool)$field['attributes']['auto_checked'] == true ) ? 'checked="checked"'  : '';		
				$html = sprintf('<input type="hidden" name="%s" value="%s" align="absmiddle" %s/>',$this->_get_element_name($field,$name),$field['value'],$checked);
			break;	
			default:
				$html = '';	
			break;	
		}
		
		// return
		return $html;
	}
	// show public profile
	function field_show_public_profile_callback($field,$name,$value){
		// selectd on post		
		$_posted_value = $this->_posted_value($field,$name);	
		// allow only checkbox
		switch ($field['type']) {
			case 'checkbox':
				// checked
				$checked = ( isset($value) && $value == 'Y' || isset($_posted_value) && $_posted_value == 'Y' ) ? 'checked="checked"'  : '';
				// html
				$html = sprintf('<input type="checkbox" name="%s" value="%s" align="absmiddle" %s/>',$this->_get_element_name($field,$name), $field['value'],$checked);
			break;	
			default:
				$html = '';	
			break;	
		}		
		// return
		return $html;
	}	
	// birthdate
	function field_birthdate_callback($field,$name,$value){
		// extra class
		if(!$field['attributes']['readonly']) 
			$field['attributes']['class'] = 'mgm_date';
		// return
		return $this->field_type_input($field,$name,$value);
	}
	
	// username
	function field_username_callback($field,$name,$value){
		// value
		if( empty($value) && isset($_POST['user_login']) ){
			$value = $_POST['user_login'];
		}	
		// esc
		$value = esc_attr( mgm_stripslashes_deep( $value ) );
		
		// readonly
		$readonly = (isset($field['attributes']['readonly']) && $field['attributes']['readonly']) ?'readonly="readonly"':'';
		// classes, default name
		$classes = array($name);
		// required
		if($field['attributes']['required']){
			$classes[] = 'required';
		}			
		// join 
		$class = implode(' ',$classes);
		// default field
		$html  = sprintf('<input type="text" name="user_login" id="user_login2" class="%s" value="%s" %s/>',$class,$value,$readonly);
		// hide on default
		if($this->get_config('wordpres_form')){
			// hide default field
			$html.= '<script language="javascript">jQuery(document).ready(function(){jQuery("#user_login").parent().remove();});</script>';
		}
		// return
		return $html;
	}
	
	// user_login
	function field_user_login_callback($field,$name,$value){
		// return
		return $this->field_username_callback($field,$name,$value);
	}
	
	// email
	function field_email_callback($field,$name,$value){			
		// value
		if( empty($value) && isset($_POST['user_email']) ){
			$value = $_POST['user_email'];
		}	
		// esc
		$value = esc_attr( mgm_stripslashes_deep( $value ) );
		
		// readonly
		$readonly = (isset($field['attributes']['readonly']) && $field['attributes']['readonly']) ?'readonly="readonly"':'';
		// classes, default name
		$classes = array($name);
		// required
		if($field['attributes']['required']){
			$classes[] = 'required';
		}
		// extra
		if(isset($field['attributes']['class'])){
			$classes[] = $field['attributes']['class'];
		}			
		// join 
		$class = implode(' ',$classes);
		// default field
		$html  = sprintf('<input type="email" name="user_email" id="user_email2" class="%s" value="%s" %s/>',$class,$value,$readonly);
		// hide on default
		if($this->get_config('wordpres_form')){
			// hide default field
			$html.= '<script language="javascript">jQuery(document).ready(function(){jQuery("#user_email").parent().remove();});</script>';
		}
		// return
		return $html;
	}
	
	// user_login
	function field_user_email_callback($field,$name,$value){
		// return
		return $this->field_email_callback($field,$name,$value);
	}

	//email confirm  - issue #1315
	function field_email_conf_callback($field,$name,$value){
		// value
		if( empty($value) && isset($_POST['user_email_conf']) ){
			$value = $_POST['user_email_conf'];
		}	
		// esc
		$value = esc_attr( mgm_stripslashes_deep( $value ) );
		
		// readonly
		$readonly = (isset($field['attributes']['readonly']) && $field['attributes']['readonly']) ?'readonly="readonly"':'';
		// classes, default name
		$classes = array($name);
		// required
		if($field['attributes']['required']){
			$classes[] = 'required';
		}
		// extra
		if(isset($field['attributes']['class'])){
			$classes[] = $field['attributes']['class'];
		}			
		// join 
		$class = implode(' ',$classes);
		// default field
		$html  = sprintf('<input type="email" name="user_email_conf" id="user_email_conf" class="%s" value="%s" %s/>',$class,$value,$readonly);

		// return
		return $html;
	}
	
	// password
	function field_password_callback($field,$name,$value){
		// esc
		$value = esc_attr( mgm_stripslashes_deep( $value ) );
		
		// classes, default name
		$classes = array($name);
		// required
		if($field['attributes']['required']){
			$classes[] = 'required';
		}			
		// join 
		$class = implode(' ',$classes);			
		// input html
		$html = sprintf('<input type="password" name="user_password" id="user_password" class="%s" value="%s" />',$class,$value);		
		// return
		return $html;
	}
	
	// password confirm
	function field_password_conf_callback($field,$name,$value){	
		// esc
		$value = esc_attr( mgm_stripslashes_deep( $value ) );
			
		// classes, default name
		$classes = array($name);
		// required
		if($field['attributes']['required']){
			$classes[] = 'required';
		}			
		// join 
		$class = implode(' ',$classes);				
		// html
		$html = sprintf('<input type="password" name="user_password_conf" id="user_password_conf" class="%s" value="%s" />',$class,$value);			
		// return 
		return $html;	
	}
	
	// user password
	function field_user_password_callback($field,$name,$value){
		// return
		return $this->field_password_callback($field,$name,$value);
	}
	
	// user_password_conf
	function field_user_password_conf_callback($field,$name,$value){
		// return
		return $this->field_password_conf_callback($field,$name,$value);
	}
	
	// terms_conditions
	function field_terms_conditions_callback($field,$name,$value){
		// copy subscription_introduction
		if(empty($field['value'])){
			$field['value'] = mgm_get_class('system')->get_template('tos', array(), true);
		}
		// esc
		$field['value'] = esc_attr( mgm_stripslashes_deep( $field['value'] ) );
		
		// checked
		$checked = (isset($_POST['mgm_tos']) && $_POST['mgm_tos'] == 1)?'checked="true"':'';
		// return
		$html  = $this->field_type_html($field,$name,$value);
		$html .= sprintf('<input type="checkbox" class="checkbox required" name="mgm_tos" id="mgm_tos" value="1" %s/> &nbsp; <label for="mgm_tos">%s</label>',$checked,__('I agree to the Terms and Conditions.','mgm'));		
		// return
		return $html;
	}
	
	// subscription_introduction
	function field_subscription_introduction_callback($field,$name,$value){
		// copy subscription_introduction
		if(empty($field['value'])){
			$field['value'] = mgm_get_class('system')->get_template('subs_intro', array(), true);
		}
		// esc
		$field['value'] = esc_attr( mgm_stripslashes_deep( $field['value'] ) );
		
		// return
		return $this->field_type_html($field,$name,$value);
	}
	
	// country
	function field_country_callback($field,$name,$value){
		// options
		$options = mgm_field_values(TBL_MGM_COUNTRY, 'code', 'name');
		// default
		if(empty($field['value'])) $field['value'] = 'US';
		// read only
		if($field['attributes']['readonly'] ) $options = array($value => $options[ $value ]);
		// return
		return $this->field_type_select($field,$name,$value,$options,MGM_KEY_VALUE);		
	}
	
	// display_name
	function field_display_name_callback($field,$name,$value){
		// options
		$options = (isset($field['options']) && is_array($field['options'])) ? $field['options'] : mgm_get_user_display_names();	
		// check	
		if(isset($field['attributes']) && $field['attributes']['readonly'] && in_array($value, $options) ) $options = array(array_search($value,$options) => $value);		
		// return
		return $this->field_type_select($field,$name,$value,$options,MGM_VALUE_ONLY);		
	}
	
	// user_url
	function field_user_url_callback($field,$name,$value){
		// fix name
		$field['name'] = 'url';
		// return
		return $this->field_type_input($field,$name,$value);
	}
	
	// subscription_options
	function field_subscription_options_callback($field,$name,$value) {			
		// default -issue #1234
		$dispaly_as_selectbox = true;
		// not select
		if($field['type'] != 'select') {
			$dispaly_as_selectbox = false;
		}
		// get object
		$packs_obj = mgm_get_class('subscription_packs');	
		// get mgm_system
		$system_obj = mgm_get_class('system');	
		// args
		$args = $this->get_config('args', array());			
		// selected subscription	
		$selected_subs = mgm_get_selected_subscription($args);													
		// packs
		$packs = $packs_obj->get_packs('register', true, $selected_subs);
		// total
		$total_amount = 0;			
		// total calc
		foreach ($packs as $pack) {					
			$total_amount += $pack['cost'];
		}													
		// active payment modules
		$a_payment_modules = $system_obj->get_active_modules('payment');
		// no active module
		if (count($a_payment_modules) == 0 && $total_amount > 0) {
		// return	
			return  sprintf('<p>%s</p>', __('There are no payment gateways active. Please contact the administrator.','mgm'));
		}

		// process next
		// init html
		$html = $subsription_options = '';	
		// payment_module
		$payment_modules = array(); 
		// loop
		foreach($a_payment_modules as $payment_module){
			// skip free/trial				
			// issue#: 483
			if(in_array($payment_module, array('mgm_free','mgm_trial'))) continue;											
			// increment 
			$payment_modules[] = $payment_module;
		}
		// init
		$options = $selected = '';		
		// pack to modules
		$pack_modules = array();						
		// loop packs
		foreach ($packs as $pack) {	
			// reset
			$checked = mgm_select_subscription($pack, $selected_subs);		

			// skip other packs when a package sent as selected
			if($selected_subs !== false && empty($checked) ) continue;
			
			// subs encrypted
			$subs_enc = mgm_encode_package($pack);

			// set modules for pack
			$pack_modules[$subs_enc] = $pack['modules'];

			// posted select iss#732
			if(isset($_POST['mgm_subscription'])){
				// match
				if($subs_enc == $_POST['mgm_subscription']){
					// check
					$checked = ' checked="checked"';
					// selected
					$selected = ' SELECTED';						
				}else{
					$checked = $selected = '';
				}
			}
			
			// hidden on single package
			if( isset($pack['hidden_single']) && (bool)$pack['hidden_single'] == true ){
				$html .= sprintf('<input type="hidden" name="mgm_subscription" value="%s" />', $subs_enc);	
				$html .= '<style type="text/css">.subscription_options_box{display: none}</style>';				
			}else{
				//options - issue #1234
				$opt_id = 'mgm_' . $pack['membership_type'] . $pack['id'];									
				// pack_desc
				$pack_desc =  mgm_stripslashes_deep($packs_obj->get_pack_desc($pack));

				// issue #338:(enable free gateway for cost=0)
				if ((strtolower($pack['membership_type']) == 'free' || ($pack['cost'] == 0 && mgm_get_module('mgm_free')->is_enabled() )) && in_array('mgm_free', $a_payment_modules)) {
					// get html
					$this->_get_field_subscription_options_html($subs_enc, $opt_id, $selected, $checked, $pack, $dispaly_as_selectbox, $pack_desc, $options, $html);
				// trial		  
				}elseif (strtolower($pack['membership_type']) == 'trial' && in_array('mgm_trial', $a_payment_modules)) {
					// get html
					$this->_get_field_subscription_options_html($subs_enc, $opt_id, $selected, $checked, $pack, $dispaly_as_selectbox, $pack_desc, $options, $html);
				}else{										
					// paid subscription active
					if(count($payment_modules)){
						// check cost and hide false
						if ($pack['cost']){
							// get html
							$this->_get_field_subscription_options_html($subs_enc, $opt_id, $selected, $checked, $pack, $dispaly_as_selectbox, $pack_desc, $options, $html);
						}// end if
					}elseif($pack['cost'] > 0){						
						// set message
						$html .= sprintf('<div class="message" style="margin:10px 0px; overflow: auto;color:red;font-weight:bold">%s</div>',__('Please enable a payment module to allow ' . mgm_stripslashes_deep($packs_obj->get_pack_desc($pack)) ,'mgm'));											
					}// end paid											
				} 		
			}// end hide check
			
		}// end pack loop

		// mgm_pr($pack_modules);

		//issue #1234 and issue #1476
		if( $dispaly_as_selectbox && ! empty($options) ){	
			// class
			$classes = array($name);
			// required
			if(isset($field['attributes']) && $field['attributes']['required']){
				$classes[] = 'required';
			}			
			// extra
			if(isset($field['attributes']) && isset($field['attributes']['class'])){
				$classes[] = $field['attributes']['class'];
			}
			// join 
			$class = implode(' ', $classes);
			// set
			$html .= sprintf('<div class="mgm_subs_wrapper"><select name="mgm_subscription" id="mgm_subscription" class="%s" rel="mgm_subscription_options">%s</select></div><div id="mgm_payment_bindlist"></div>', $class, $options);
		}

		// add pack modules as json data, may consider jquery data later
		if( ! empty($pack_modules) ){
			$html .= sprintf('<script language="javascript">var mgm_pack_modules = %s</script>', json_encode($pack_modules));
		}
		
		// return
		return empty($html) ? sprintf('<p style="color:red"><b>%s</b></p>',__('No subscription options available','mgm')) : $html;
	}
	
	

	/**
	 * Callback for payment_gateways field
	 * prints all active modules, javascript callback and json data shows per pack 
	 * or filtered 
	 *
	 * @param array $field
	 * @param string $name
	 * @param string $value
	 * @return string
	 */	
	function field_payment_gateways_callback($field, $name, $value) {
		// display as		
		$dispaly_as_selectbox = $this->_get_subscription_options_as();
		
		//check subscription options custom_field is enabled:		
		$continue = false;
		// cf
		$obj_customfields = mgm_get_class('member_custom_fields');		
		// subo
		$arr_sub_options = $obj_customfields->get_field_by_name('subscription_options');
		// check
		if(isset($arr_sub_options['id']) && !empty($obj_customfields->sort_orders) && in_array($arr_sub_options['id'], $obj_customfields->sort_orders))
			$continue = true;
			
		// return
		if(!$continue) return '';
				
		$system_obj = mgm_get_class('system');
		// args
		$args = $this->get_config('args', array());				
		// selected_subscription	
		$selected_subs = mgm_get_selected_subscription($args);		
		// get active modules
		$a_payment_modules = $system_obj->get_active_modules('payment');

		/* @todo deprecate load here since gateways are loaded for post purchase also
		// get modules for packs:
		$allpacks = mgm_get_class('subscription_packs')->get_packs();		
		// init
		$pack_modules = array();		
		// loop
		foreach ($allpacks as $allp) {
			// reset
			$include = mgm_select_subscription($allp,$selected_subs);						
			// skip other when a package sent as selected
			if($selected_subs !== false){
				if(empty($include)) continue;
			}
			//issue #1532
			if(is_array($allp['modules'])){
				$pack_modules = array_merge($pack_modules,$allp['modules']);
			}
		}
		// pack
		$pack_modules = array_unique($pack_modules);
		
		mgm_pr($pack_modules);*/

		// payment modules
		$payment_modules = array();			
		// loop
		if( ! empty($a_payment_modules) ) {
			// loop
			foreach($a_payment_modules as $payment_module){
				// not trial
				if( in_array($payment_module, array('mgm_free','mgm_trial')) ) continue;
				// included modules
				// if( ! in_array($payment_module, $pack_modules) ) continue;
				// store
				$payment_modules[] = $payment_module;					
			}
		}
		// make unique
		$payment_modules = apply_filters('mgm_payment_gateways_as_custom_field', array_unique($payment_modules) );
		// check count
		$module_count = count($payment_modules);
		// one active
		$hide_div = ($module_count === 1) ? 'style="display:none;"' : ''; 
		// NOTE: uncomment the below line to enable module display even if only one exists
		// $hide_div = "";		
		// NOTE: do not change the id: mgm_payment_gateways_container
		$html = sprintf('<div id="mgm_payment_gateways_container" class="mgm_payment_wrapper" %s >', $hide_div);
		//loop through payment modules:				
		if( $module_count == 0 ) {
			$html .= sprintf('<div>%s</div>', __('No active payment module', 'mgm') );
		}else {			
			//print each module:
			foreach($payment_modules as $payment_module) {
				//checked: if(selected/only one gateway exists)
				if(isset($_POST['mgm_payment_gateways'])){
					$checked = ($payment_module == $_POST['mgm_payment_gateways']) ?'checked="true"':'';
				}else{
					$checked = ((!empty($value) && $value == $field['value']) || $module_count === 1 )?'checked="true"':'';
				}	
				
				// get obj
				$mod_obj = mgm_get_module($payment_module, 'payment');							
				// html
				$img_url = mgm_site_url($mod_obj->logo);				
				// desc
				$description = mgm_stripslashes_deep($mod_obj->description);
				//module description
				$html .= sprintf(
						'<div id="%s_container" class="mgm_payment_opt_wrapper" %s>
						  	<input type="radio" %s class="checkbox" name="mgm_payment_gateways" value="%s" alt="%s" />
						 	<img class="mgm_payment_opt_customfield_image" src="%s" alt="%s" />
						  	<div class="mgm_paymod_description">%s</div>
						 </div>', $mod_obj->code, $hide_div, $checked, $mod_obj->code, $mod_obj->name, $img_url, 
						 		  $mod_obj->name, $description);
						  
			}
			
			// scripts required for pack buttons:
			// $packs_obj = mgm_get_class('subscription_packs');															
			// packs
			// $packs = $packs_obj->get_packs('register');		
			// $lf = "\n";	
			//script to show/hide appicable module buttons
			$html .= '<script type="text/javascript">jQuery(document).ready(function() {'. PHP_EOL;
			// bind as needed	
			if( $dispaly_as_selectbox ){
				$html .= 'jQuery("select[rel=\'mgm_subscription_options\']").bind("change", function(){ mgm_select_pack_modules("select"); }).change();' . PHP_EOL;
			}else{
				$html .= 'jQuery(":input[rel=\'mgm_subscription_options\']").bind("click", function(){ mgm_select_pack_modules("radio"); });' . PHP_EOL;
				$html .= 'mgm_select_pack_modules("' . ($dispaly_as_selectbox ? 'select' : 'radio')  . '");' . PHP_EOL  ;// @todo consider SELECT
			}	
			// end			
			$html .= '});</script>' . PHP_EOL;			
		}
		// end
		$html .= '</div>';

		// return
		return $html;		
	}
	
	// checkbox
	function field_addon_callback($field,$name,$value){
		// options
		return mgm_get_register_addon_options_html(sprintf('%s[]',$this->_get_element_name($field,$name)));		
	}

	// by name end/////////////////////////////////////////////////////////////////////////////////////////
	
	// internal ///////////////////////////////////////////////////////////////////////////////////////////
	/**
	 * filtered value. developed to protect agaist XSS attacks
	 * clean up fields that have chances to outer world input data i.e. textbox, textarea
	 * leave fixed/admin value fields i.e. html, label etc.
	 */
	function _filtered_value($field, $name='mgm_register_field', $value=''){
		// init
		$_value = '';
		// isset post						
		if( isset($_POST[$name][$field['name']]) ){
			$_value = $_POST[$name][$field['name']];
		}else if( !empty($value) ){
			$_value = $value;
		}else if( isset($field['value']) ){					
		// default
			$_value = $field['value'];	
		}	
		
		// stripslashes
		$_value =  mgm_stripslashes_deep( $_value );		
		
		// js escape
		if( !in_array($field['type'], array( 'html', 'label', 'checkbox', 'radio')) ){// skip these fields
			if (is_array($_value)) {
				foreach ($_value as $key => $value) {
					$_value[esc_js($key)] = esc_js($value);
				}
			}else
				$_value = esc_js( $_value );
		}
		
		if ($field['type'] == 'textarea') {
			$return = esc_textarea( $_value );  	
		}else {
			if (is_array($_value)) {
				foreach ($_value as $key => $value) {
					$_value[esc_attr($key)] = esc_attr($value);
				}
				$return = $_value;
			}else
				$return = esc_attr( $_value );
		}	
		// return
		return $return;
	}
	
	// posted value
	function _posted_value($field,$name='mgm_register_field'){
		// init
		$_value = '';
		// isset post						
		if( isset($_POST[$name][$field['name']]) ){
			$_value = $_POST[$name][$field['name']];
		}
		// no
		return esc_attr( mgm_stripslashes_deep( $_value ) );
	}
	
	// element name
	function _get_element_name($field,$name){
		// return
		return sprintf('%s[%s]',$name,$field['name']);	
	}
	
	// element id
	function _get_element_id($field,$name){
		// return
		return sprintf('%s_%s',$name,$field['name']);	
	}

	/**
	 * get subscription options html		 
	 */
	function _get_field_subscription_options_html($subs_enc, $opt_id, $selected, $checked, $pack, $dispaly_as_selectbox, $pack_desc, &$options, &$html){
		//issue #1234
		if( $dispaly_as_selectbox ){
			$options .= '<option value="'.$subs_enc.'" id="'.$opt_id.'" '.$selected.'>' . $pack_desc . '</option>';			
		}else {
			// html										
			//NOTE: Do not change the mgm_subs_wrapper class. It is being used in payment_gateways Custom field
			$input = sprintf('<input type="radio" %s class="mgm_subs_radio" name="mgm_subscription" id="mgm_subscription_%d" value="%s" rel="mgm_subscription_options"/>', $checked, $pack['id'], $subs_enc);
			//Refer to : function field_payment_gateways_callback()		
			$html .= '<div class="mgm_subs_wrapper '.$pack['membership_type'].' ">
						 <div class="mgm_subs_option '.$pack['membership_type'].'">
							' . $input . '														
						 </div>
						 <div class="mgm_subs_pack_desc '.$pack['membership_type'].'">
							' . $pack_desc . ' 
						 </div>
						 <div class="clearfix '.$pack['membership_type'].'"></div>
						 <div class="mgm_subs_desc '.$pack['membership_type'].'">
							' . mgm_stripslashes_deep($pack['description']) . '
						 </div>
					 </div>';
		}		
	}

	/**
	 * get subscription_options as
	 */
	function _get_subscription_options_as(){
		// get field - issue #1234
		$subscription_options_field = mgm_get_class('member_custom_fields')->get_field_by_name('subscription_options');	
		
		// init
		$dispaly_as_selectbox = false;
		if( $subscription_options_field['type'] == 'select' ) {
			$dispaly_as_selectbox = true;
		}

		// return
		return $dispaly_as_selectbox;
	}
}

// core/libs/utilities/mgm_form_fields.php
