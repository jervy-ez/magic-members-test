<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');

// -----------------------------------------------------------------------

/**

 * Magic Members misc functions

 *

 * @package MagicMembers

 * @since 2.5

 */

 

/**

 * dump data

 */

function mgm_array_dump($array, $return=false){

	// dump

	$dump = sprintf('<pre>%s</pre>', print_r($array, true));

	// return

	if($return) return $dump;	 

	// print

	echo $dump;	

}



/**

 * alias of dump data

 */

function mgm_pr($array, $return=false){

	// return

	return mgm_array_dump($array, $return);

}



/**

 * log data

 */

function mgm_log($data, $filename=NULL, $suffix=true){

	// log enabled

	// if(defined('MGM_DEBUG_LOG') && MGM_DEBUG_LOG == FALSE) return;

	 

	// line count

	static $line=1;	

	

	// log to same file

	if($suffix){

		// define

		if(!defined('LOG_REQUEST_ID')) define('LOG_REQUEST_ID', date('mdYHis'));

		 // file name 

		$filename = (!$filename) ? LOG_REQUEST_ID : ($filename . '_' . LOG_REQUEST_ID);	

	}else{

	// different files

		$filename = (!$filename) ? date('mdYHis') : $filename;	

	}	

	

	// data, array to string

	if(is_array($data) || is_object($data)) $data = mgm_array_dump($data, true);

	// line feed	

	$crlf = "\n";

	$end_crlf = "\n\r";

	// open

	if($fp = fopen(MGM_FILES_LOG_DIR . $filename . '.log', 'a+')){

		// write

		fwrite($fp, $crlf . ($line++) . '. ['.date('d-m-Y H:i:s').']: ' . $crlf . str_repeat('-',100) . $crlf . $data . $end_crlf);

		// close

		fclose($fp);

		// return success

		return true;

	}

	// error

	return false;

}



/**

 * fetch remote data via http GET

 *

 * @param string $url

 * @param array $data to append in query string

 * @param array $options

 * @param bool $on_error_message (CONNECT_ERROR|WP_ERROR)

 * @return string $response

 */

function mgm_remote_get($request_url, $data=null, $options=array(), $on_error_message='CONNECT_ERROR'){

 	// args

	$args = array();

	

	// data

	if( is_array($data) && !empty($data) )

		$request_url = add_query_arg($data, $request_url);



	// merge		

	if(is_array($options)) $args = array_merge($args, $options);	

	

	// log

	// mgm_log($request_url, __FUNCTION__);	

	

	// request

	$request = wp_remote_get($request_url, $args);

	

	// log

	// mgm_log($request, __FUNCTION__);

	

	// validate, 200 and 302, WP permalink cacuses 302 Found/Temp Redirect often

	if ( is_wp_error( $request ) || !in_array(wp_remote_retrieve_response_code( $request ), array( 200, 302)) )  

		return ($on_error_message !== FALSE || !is_wp_error( $request )) ? __($on_error_message, 'mgm') : $request->get_error_message();  

		

	// return

	return wp_remote_retrieve_body( $request ); 

}



/**

 * fetch remote data via http POST

 *

 * @param string $url

 * @param array $data to post

 * @param array $options

 * @param bool $on_error_message (CONNECT_ERROR|WP_ERROR)

 * @return string $response

 */

function mgm_remote_post($request_url, $data=array(), $options=array(), $on_error_message='CONNECT_ERROR'){

	// args

	$args = array('body' => $data);

	

	// merge		

	if(is_array($options)) $args = array_merge($args, $options);	

	

	// log

	// mgm_log($request_url, __FUNCTION__);



	// log

	// mgm_log($args, __FUNCTION__);



 	// request

	$request = wp_remote_post($request_url, $args);



	// log

	// mgm_log($request, __FUNCTION__);

	

	// validate, 200 and 302, WP permalink cacuses 302 Found/Temp Redirect often

	if ( is_wp_error( $request ) || !in_array(wp_remote_retrieve_response_code( $request ), array( 200, 302)) )  

		return ($on_error_message !== FALSE || !is_wp_error( $request )) ? __($on_error_message, 'mgm') : $request->get_error_message();  

		

	// return

	return wp_remote_retrieve_body( $request ); 

}



/**

 * fetch remote head via http GET

 *

 * @param string $url

 * @param array $options

 * @param bool $on_error_message

 * @return string $response

 */

function mgm_remote_head($request_url, $header=NULL, $options=array(), $on_error_message='failed to connect'){

 	// args

	$args = array();

	

	// merge		

	if(is_array($options)) $args = array_merge($args, $options);	

		

	// request

	$request = wp_remote_head($request_url, $args);

	

	// validate, 200 and 302, WP permalink cacuses 302 Found/Temp Redirect often

	if ( is_wp_error( $request ) || !in_array(wp_remote_retrieve_response_code( $request ), array( 200, 302)) )  

		return ($on_error_message !== FALSE || !is_wp_error( $request )) ? __($on_error_message, 'mgm') : $request->get_error_message();  

		

	// return

	return ($header) ? wp_remote_retrieve_header($request, $header) : wp_remote_retrieve_headers( $request ); 

}



/**

 * get request var

 */

function mgm_request_var($var, $default='', $strip_tags=false) {

	// gpc	

	return mgm_gpc($_REQUEST, $var, $default, $strip_tags);

}



/**

 * get post var

 */

function mgm_post_var($var, $default='', $strip_tags=false) {

	// gpc	

	return mgm_gpc($_POST, $var, $default, $strip_tags);

}



/**

 * get get var

 */

function mgm_get_var($var, $default='', $strip_tags=false) {

	// gpc	

	return mgm_gpc($_GET, $var, $default, $strip_tags);

}



/**

 * get cookie var

 */

function mgm_cookie_var($var, $default='', $strip_tags=false) {

	// gpc	

	return mgm_gpc($_COOKIE, $var, $default, $strip_tags);

}



/**

 * get session var

 */

function mgm_session_var($var, $default='', $strip_tags=false) {

	// gpc	

	return mgm_gpc($_SESSION, $var, $default, $strip_tags);

}



/**

 * get server var

 */

function mgm_server_var($var, $default='', $strip_tags=false) {

	// gpc	

	return mgm_gpc($_SERVER, $var, $default, $strip_tags);

}

/**

 * get GPC var

 */

function mgm_gpc($gpc, $var, $default='', $strip_tags=false){

	// data

	$val = (isset($gpc[$var])) ? $gpc[$var] : $default;		

	// strip

	if ($strip_tags) $val = strip_tags($val);	

	// data

	return $val;

}



/**

 * unset cookie

 */

function mgm_delete_cookie_var($var){	

	// check

	if(isset($_COOKIE[$var])){

		// unset

		unset($_COOKIE[$var]);	

		// set in past

		setcookie($var, '', (time() - 3600), SITECOOKIEPATH);   

	}	

}



/**

 * set cookie

 */

function mgm_set_cookie_var($var, $value, $expire='30 DAY'){

	// set

	setcookie($var, $value, strtotime('+' . $expire), SITECOOKIEPATH); 

}



/**

 * create mask url for making post to self 

 */

function mgm_home_url($base=''){

	// base 

	if('' != $base){	

		// checl permalink	

		if('' == get_option('permalink_structure')){// empty is default query string permalink

		// set			

			$home_url = add_query_arg(array($base=>1), home_url());

		}else{	

		// set

			$home_url = home_url($base);	

		}	

	}

	// return 

	return $home_url;

}



/**

 * make secure if ssl is on or taged

 * @deprecated

 */

function mgm_ssl_url($url){	

	// ssl

	if(( bool_from_yn(mgm_get_setting('use_ssl_paymentpage')) || is_ssl())){

	// replace

		$url = preg_replace('|^http://|', 'https://', $url);// preg_replace('/^http:/', 'https:', $url);

	}

	// return

	return $url;

}



/**

 * get download url

 */

function mgm_download_url($download, $slug=NULL){

	// slug

	if(!$slug) $slug = mgm_get_class('mgm_system')->get_setting('download_slug', 'download');						

	$download_query_arg = array();

	$download_query_arg['code'] = ($download->code ? $download->code : $download->id);

	//issue #1609

	if(isset($_REQUEST['guest_token']) && !empty($_REQUEST['guest_token'])){

		$download_query_arg ['guest_token'] = $_REQUEST['guest_token'];

		$post_id = get_the_id();

		if($post_id) $download_query_arg['post_id'] = $post_id;		

	}

	// return

	return add_query_arg($download_query_arg, mgm_home_url($slug));

}



/**

 * get partial fields

 */

function mgm_get_partial_fields($display=NULL, $name='mgm_upgrade_field'){

	// display

	if(!$display) $display = array('on_upgrade'=>true);

	// get system

	$system_obj = mgm_get_class('system');	

	// init

	$html = '';

	// wordpress register

	$wordpres_form = mgm_check_wordpress_login();

	// 	get row row template

	$form_row_template = $system_obj->get_template('register_form_row_template');	

	// get template row filter, mgm_register_form_row_template for custom

	$form_row_template = apply_filters('mgm_register_form_row_template', $form_row_template);	

	// get mgm_form_fields generator

	$form_fields = new mgm_form_fields(array('wordpres_form'=>$wordpres_form,'form_row_template'=>$form_row_template));

	// user fields on specific page, coupon specially

	$cf_partial = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>$display));

	// found some

	if($cf_partial){

		// loop

		foreach($cf_partial as $field){

			// init

			$form_html = $form_row_template;

			// replace wrapper

			$form_html = str_replace('[user_field_wrapper]', $field['name'].'_box', $form_html);

			// replace label

			$form_html = str_replace('[user_field_label]', ($field['attributes']['hide_label']?'':mgm_stripslashes_deep($field['label'])), $form_html);

			// replace element

			$form_html = str_replace('[user_field_element]', $form_fields->get_field_element($field, $name), $form_html);			

			// append

			$html .= $form_html;		

		}

	}

	// return

	return $html;

}



/**

 * get user from query string

 * @todo use trans_ref to fetch too

 */

function mgm_get_user_from_querystring(){

	// init

	$user = false;



	// check

	if( isset($_GET['username']) && !empty($_GET['username']) ){// login

		$user = get_user_by('login', sanitize_user($_GET['username']) ); 	

	}elseif( isset($_GET['email']) && !empty($_GET['email']) ){// email

		$user = get_user_by('email', sanitize_email($_GET['email']) );

	}elseif( isset($_GET['user_id']) && !empty($_GET['user_id']) ){// id

		$user = get_user_by('id', (int)strip_tags($_GET['user_id']));

	}else{

		$user = new stdClass;

		$user->ID = 0;// nothing

	}	



	// return

	return $user;

}

/**

 * save partial fields

 */

function mgm_save_partial_fields($display=NULL, $name='mgm_upgrade_field', $cost, $is_single=true, $action = 'upgrade', $member = null){

	global $wpdb;

	// set data    

	$user = wp_get_current_user();

	//issue#: 416

	if($user->ID == 0 || !is_numeric($user->ID)) {

		$user = mgm_get_user_from_querystring();						

	}

	// error

	if(!$user) return false;

	

	// display

	if(!$display) $display = array('on_upgrade'=>true);

	// get system

	$system_obj = mgm_get_class('system');

	// member

	$multiple_membership = false;

	if (is_null($member))

		$member = mgm_get_member($user->ID);

	else

		$multiple_membership = true;		

	// user fields on specific page

	$cf_partial = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>$display));

	

	// found some

	if($cf_partial){

		// loop

		foreach($cf_partial as $field){			

			// name switch		

			switch($field['name']){

				case 'coupon': {						

					// validate

					$coupon = mgm_validate_coupon($_POST[$name][$field['name']], $cost);		

					

					if($field['attributes']['required'] && empty($_POST[$name][$field['name']])) {						

						if(!empty($_POST['form_action'])) {

							//redirect back to the form							

							$redirect = add_query_arg(array('error_field' => $field['label'], 'error_type' => 'empty'), $_POST['form_action']);														

							mgm_redirect($redirect);

							exit;

						}

					}

					// valid

					if($coupon!==false){	

						// update_usage

						$update_usage = false;

						// field name in object for ref

						$field_name = str_replace(array('mgm_','_field'),'',$name); // mgm_upgrade_field = > upgrade	

						// single coupon, upgrade/ extend

						if($is_single){

							// if complete_payment use registration coupon fields. issue#: 802

							if ($action == 'complete_payment') {

								if(isset($member->coupon)){

									//$member->coupon = (array) $member->coupon;

									//issue #1109										

									$member->coupon = $coupon;

									// usage

									$update_usage = true;

										

								}								

								// update coupon usage, if not used already

								if(!isset($member->coupon) || (isset($member->coupon) && $member->coupon['id'] != $coupon['id'])){														

									// set

									$member->coupon = $coupon;	

									// usage

									$update_usage = true;

								}								

							}else {

								if(isset($member->{$field_name}['coupon'])) {

									//$member->{$field_name}['coupon'] = (array) $member->{$field_name}['coupon'];	

									//issue #1109

									$member->{$field_name}['coupon'] = $coupon;

									// usage

									$update_usage = true;										

								}								

								// update coupon usage, if not used already

								if(!isset($member->{$field_name}['coupon']) || (isset($member->{$field_name}['coupon']) && $member->{$field_name}['coupon']['id'] != $coupon['id'])){														

									// set

									$member->{$field_name}['coupon'] = $coupon;	

									// usage

									$update_usage = true;

								}		

							}												

						}else{

							// if complete_payment use registration coupon fields. issue#: 802

							if ($action == 'complete_payment') {

								if(!isset($member->coupons)){

									// never added

									$member->coupons = array($coupon['id'] => $coupon);

									// usage

									$update_usage = true;

								}else{

									// not added

									if(!in_array($coupon['id'],array_keys($member->coupons))){

										// never added

										$member->coupons = array_merge($member->coupons,array($coupon['id']=>$coupon));

										// usage

										$update_usage = true;

									}

								}

							}else {

								if(!isset($member->{$field_name}['coupons'])){

									// never added

									$member->{$field_name}['coupons'] = array($coupon['id'] => $coupon);

									// usage

									$update_usage = true;

								}else{

									// not added

									if(!in_array($coupon['id'],array_keys($member->{$field_name}['coupons']))){

										// never added

										$member->{$field_name}['coupons'] = array_merge($member->{$field_name}['coupons'],array($coupon['id']=>$coupon));

										// usage

										$update_usage = true;

									}

								}

							}

						}	

						// update database

						if($update_usage){

							// check

							$member->coupon['update_usage'] = true;

							$member->coupon['coupon_usage_id'] = $coupon['id'];	

							// log

							// mgm_log('update usage of '. $coupon['id'], __FUNCTION__);													

							// will not be triggered by payment

							// if( (float)$coupon['cost'] == 0.00){

								// mgm_update_coupon_usage($coupon['id'], $action);

							//}													

						}	

					}

					//issue #1109

					if(empty($coupon) || $coupon===false){						

						if ($action == 'complete_payment') {

							$member->coupon = array();

						} else {

							$member->coupon['update_usage'] = false;

							$member->coupon['coupon_usage_id'] = false;							

							$member->upgrade['coupon']= array();

						}

					}

				break;

				}

			}

		}		

		// update option

		if ($multiple_membership)

			mgm_save_another_membership_fields($member, $user->ID);

		else	

			$member->save();

	}	

	// return

	if(!$is_single)	return isset($coupon) ? $coupon : false;

	// default		

	return $member;

}



/**

 * save partial fields with purchase

 */

function mgm_save_partial_fields_purchase_more($user_id, $membership_type, $cost, $is_single=true) {

	global $wpdb;	

	// field

	$name = 'mgm_upgrade_field';

	// member

	$member = mgm_get_member($user_id);	

	$key_found = false;

	if(isset($member->other_membership_types) && is_array($member->other_membership_types) && count($member->other_membership_types) > 0) {

		foreach ($member->other_membership_types as $key => $memtypes) {

			$memtypes = mgm_convert_array_to_memberobj($memtypes, $user_id);

			if($memtypes->membership_type == $membership_type ) {

				//reset if already saved

				$key_found = true;

				//return $memtypes;

				break;

			}

		}

	}	

	

	// return

	if(!$key_found) return $member;

		

	// user fields on specific page

	$cf_partial = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=> array('on_upgrade'=>true)));

	// found some

	if($cf_partial){

		// loop

		foreach($cf_partial as $field){		

			// name switch		

			switch($field['name']){

				case 'coupon':	{					

					// validate

					if($field['attributes']['required'] && empty($_POST[$name][$field['name']])) {						

						if(!empty($_POST['form_action'])) {

							//redirect back to the form							

							$redirect = add_query_arg(array('error_field' => $field['label'], 'error_type' => 'empty'), $_POST['form_action']);

							// redirect														

							mgm_redirect($redirect); exit;

						}

					}

					// init, issue #1109

					$member->coupon['update_usage'] = false;

					$member->coupon['coupon_usage_id'] = false;		

					// validate		

					$coupon = mgm_validate_coupon($_POST[$name][$field['name']], $cost);

					// valid

					if($coupon!==false){	

						// update_usage

						$update_usage = false;

						// field name in object for ref

						$field_name = str_replace(array('mgm_','_field'),'',$name); // mgm_upgrade_field = > upgrade

						

						// log

						// mgm_log($coupon, __FUNCTION__);



						// check

						if(isset($member->other_membership_types[$key])){

							$member->other_membership_types[$key] = mgm_convert_array_to_memberobj($member->other_membership_types[$key], $user_id);	

						}

							

						// when reset, treat as update usage #1320	

						if(!is_array($member->other_membership_types[$key]->{$field_name})){

							// set

							$member->other_membership_types[$key]->{$field_name} = array('coupon'=>$coupon);

							

							// usage

							$update_usage = true;



							// log

							// mgm_log('reset first time block', __FUNCTION__);

						}

							



						// log	

						// mgm_log($member->other_membership_types[$key], __FUNCTION__);	

						// single coupon, upgrade/ extend

						if($is_single){		

							// log

							// mgm_log('is single true', __FUNCTION__);	



							

							// update coupon usage, if not used already

							if(!isset($member->other_membership_types[$key]->{$field_name}['coupon']) 

								|| (isset($member->other_membership_types[$key]->{$field_name}['coupon']['id']) 

								&& (int)$member->other_membership_types[$key]->{$field_name}['coupon']['id'] != (int)$coupon['id']) ){														

								// set

								$member->other_membership_types[$key]->{$field_name}['coupon'] = $coupon;	

								// usage

								$update_usage = true;	



								// log

								// mgm_log('is single true first block', __FUNCTION__);							

							}	

												

						}else{

							// log

							// mgm_log('is single false', __FUNCTION__);	

							// check

							if(!isset($member->other_membership_types[$key]->{$field_name}['coupons'])){

								// never added

								$member->other_membership_types[$key]->{$field_name}['coupons'] = array($coupon['id']=>$coupon);

								// usage

								$update_usage = true;

								

								// log

								// mgm_log('is single false first block', __FUNCTION__);	

							}else{

								// not added

								if(!in_array($coupon['id'],array_keys($member->other_membership_types[$key]->{$field_name}['coupons']))){

									// never added

									$member->other_membership_types[$key]->{$field_name}['coupons'] = array_merge($member->other_membership_types[$key]->{$field_name}['coupons'],array($coupon['id']=>$coupon));

									// usage

									$update_usage = true;

									// log

									// mgm_log('is single false second block', __FUNCTION__);	

								}

							}

						}	

						// log

						// mgm_log($member, __FUNCTION__);

						// update database

						if($update_usage){

							//issue #1109

							$member->coupon['update_usage'] = true;

							$member->coupon['coupon_usage_id'] = $coupon['id'];		

							// log																			

							// mgm_log('updated usage of ' . $coupon['id'], __FUNCTION__);

							// will not be triggered by payment

							// if( (float)$coupon['cost'] == 0.00){

								// mgm_update_coupon_usage($coupon['id'], 'purchase_another');

							// }													

						}	

					}// end

				

					break;

				}

			}

		}		

		

		//make sure other_membership_types is array:

		if(isset($member->other_membership_types) && !empty($member->other_membership_types)) {

			$member->other_membership_types = mgm_convert_memberobj_to_array($member->other_membership_types);

		}

			

		$member->save();

	}

	

	// return

	if(!$is_single)	

		return isset($coupon) ? $coupon : false;

	//make sure returns an array to work the previous code	

	//check this:

	$member->other_membership_types[$key] = mgm_convert_array_to_memberobj($member->other_membership_types[$key], $user_id, true, false)	;

	// default		

	return $member->other_membership_types[$key];

}



/**

 * email / wrapper for wp_mail

 * @uses wp_mail() and filters

 *

 * @param string $to

 * @param string $subject

 * @param string $message

 * @param string $headers

 * @param array $attachments

 * @return bool

 */

function mgm_mail($to, $subject, $message, $headers = '', $attachments = array()){	

	// filters to apply

	$filters = array('wp_mail_content_type' => 'mgm_get_mail_content_type', 

					 'wp_mail_from'         => 'mgm_get_mail_from',

					 'wp_mail_from_name'    => 'mgm_get_mail_from_name', 

					 'wp_mail_charset'      => 'mgm_get_mail_charset');

	// add filters

	foreach ($filters as $filter => $callback) {

		// code...

		if( !has_filter($filter, $callback) ){					

			add_filter($filter, $callback, 10);	

		}

	}

	// send mail

	$status = @wp_mail( $to, $subject, $message, $headers, $attachments );



	// apply globally

	if( ! bool_from_yn(mgm_get_setting('email_headers_global')) ){

		// remove filters

		foreach ($filters as $filter => $callback) {

			// code...

			if( has_filter($filter, $callback) ){					

				remove_filter($filter, $callback, 10);	

			}

		}

	}

	

	//return

	return $status;	

}



/**

 * return / set content_type

 */

function mgm_get_mail_content_type($content_type){			

	// return

	return mgm_get_setting( 'email_content_type', $content_type );

}



/**

 * return / set from_email

 */

function mgm_get_mail_from($from_email){

	// return

	return mgm_get_setting( 'from_email', $from_email );

}

	

/**

 * return / set from_name

 */

function mgm_get_mail_from_name($from_name){

	// return

	return mgm_get_setting( 'from_name', $from_name );

}



/**

 * return / set charset

 */

function mgm_get_mail_charset($charset){

	// return

	return mgm_get_setting( 'email_charset', $charset );

}



/**

 * file download path

 */	

function mgm_get_file_url($filename) {

	// return 

	return str_replace(trailingslashit(get_option('siteurl')), str_replace('\\', '/', ABSPATH), $filename);

}



/**

 * get all posts

 */	

function mgm_get_posts(){

	global $wpdb;

	// sql

	$sql = "SELECT `ID`, `post_title` FROM `" . $wpdb->posts . "` WHERE `post_status` = 'publish' AND `post_type` IN ('page','post') ORDER BY `post_title`";

	// return

	return $posts = $wpdb->get_results($sql);

}



/**

 * format currency

 */	

function mgm_format_currency($number){

	// strip 00

	$number = preg_replace('/\.00$/','', $number);

	// format

	if(preg_match('/\.\d+$/',$number))

		$number = number_format($number,2, '.', ',');

	// return	

	return $number;

}



/**

 * get user id

 */	

function mgm_get_user_id() {	

	global $wpdb;

	// by username

	if ( $username = mgm_get_var('username', '', true) ) {		

		$results = $wpdb->get_results($wpdb->prepare("SELECT `ID` FROM `" . $wpdb->users . "` WHERE `user_login` = %s", sanitize_key($username)));		

		$row = array_pop(array_reverse($results));

		$user_ID = $row->ID;

	} else if ( $user_id = mgm_get_var('user_id', '', true) ) {

		$user_ID = (int)$user_id;

	} else {

		$current_user = wp_get_current_user();

		$user_ID = $current_user->ID;

	}

	// return

	return $user_ID;

}



/**

 * show fields

 */	

function mgm_show_fields_result($args_fields, $cf_register_page, $package=NULL) {

	

	$show_fields = explode(',',$args_fields);

	$sb_flag = false;  

	

	foreach ($cf_register_page as $key => $cf_field) {

		

		for ($i=0; $i<count($show_fields); $i++) {

			

			if($cf_field['name'] == 'subscription_options' && $sb_flag==false) {

				$sb_flag = true;

				$cf_show_fields[] = $cf_field;

			}

			if ($cf_field['name'] == $show_fields[$i]) {

				$cf_show_fields[] = $cf_field;

			}

		}

	}

	

	if (!empty($cf_show_fields)) {

		$cf_register_page = $cf_show_fields;

	}

	return $cf_register_page;

}



/**

 * get transactions page

 */	

function mgm_transactions_page($args=array()) {		

	// return 

	return mgm_get_transaction_page_html(true); exit;

}

	

/**

 * get login and register link

 */	

function mgm_get_login_register_links() {

	// html

	return sprintf('<div id="mgm_login_register_links">%s &nbsp; %s</div>', mgm_get_login_link(), mgm_get_register_link());	

}



/**

 * get login link

 */	

function mgm_get_login_link() {

	// permalink

	$permalink    = trailingslashit(get_permalink());

	// login url

	$login_url    = trailingslashit(mgm_get_custom_url('login'));

	// register url

	$register_url = trailingslashit(mgm_get_custom_url('register'));	

	// dont redirect back to login/register

	if( !in_array($permalink, array($login_url, $register_url)) ){

		$login_url = add_query_arg(array('redirect_to'=>$permalink), $login_url);

	}	

	// return	

	return sprintf('<span id="mgm_login_link"><a class="mgm-login-link" href="%s">%s</a></span>', $login_url, __('[ Login ]', 'mgm'));

}



/**

 * get register link

 */	

function mgm_get_register_link() {

	// permalink

	$permalink    = trailingslashit(get_permalink());

	// register url

	$register_url = trailingslashit(mgm_get_custom_url('register'));

	// dont redirect back to login/register

	if($register_url != $permalink){

		$register_url = add_query_arg(array('mgm_redirector'=>$permalink), $register_url);

	}		

	// return

	return sprintf('<span id="mgm_register_link"><a class="mgm-register-link" href="%s">%s</a></span>', $register_url, __('[ Register ]', 'mgm'));

}



/**

 * check post purchasable

 *

 * @param int $post_id

 * @return bool

 */

function mgm_post_is_purchasable($post_id = false, $post_obj = NULL) {	

	// default

	$return = false;

	

	// get post id

	if (!$post_id) $post_id = get_the_ID();



	// post setting object

	if (!$post_obj) $post_obj = mgm_get_post($post_id);	

	

	// is purchasable

	if ( bool_from_yn( $post_obj->purchasable ) ) {

		// check expiry

		if ($expiry = $post_obj->purchase_expiry) {

			// not expired

			if (strtotime($expiry) > time()) {

				$return = true;

			}

		} else {

			$return = true;

		}

	}

	

	// return

	return $return;

}



/**

 * check post protected

 *

 * @param int $post_id

 * @return bool

 */

function mgm_post_is_protected($post_id = false, $post_obj = NULL) {	

	// default

	$return = false;

	

	// get post id

	if (!$post_id) $post_id = get_the_ID();



	// mgm post setting object

	if (!$post_obj) $post_obj = mgm_get_post($post_id);	

	

	// wp post object

	$post = &get_post($post_id);

	

	// access 

	$access_types = $post_obj->get_access_membership_types();

	

	// count

	$access_types_count = count($access_types);

	

	// content

	$content = $post->post_content;

	

	// has tag

	$has_tag = mgm_is_manually_protected($content);

	

	// content protection settings

	$cp_setting = mgm_get_class('system')->get_setting('content_protection');

	

	// protection

	$has_cp = (bool)in_array($cp_setting, array('full','partly'));

	

	// purchasable

	if ($access_types_count > 0 && ($has_tag || $has_cp) ) {

		// return		

		$return = true;			

	}

	

	// return

	return $return;

}



/**

 * get or generate rss token for logged in user

 *

 * @param integer $user_id

 * @return string token code

 * @since 1.0

 */

function mgm_get_rss_token( $user_id = null ) {	

	global $wpdb;

	// user

	if( ! $user_id ){

		$current_user = wp_get_current_user();

		$user_id = $current_user->ID;

	}

	

	// init

	$token = false;

	// check

	if ( $user_id ) {

		// member

		$member = mgm_get_member( $user_id );

		// check

		if ( ! $token = $member->rss_token) {

			// set

			$member->rss_token = $token = mgm_create_rss_token();

			// save

			$member->save();			

			// also update usermeta

			update_user_option($user_id, '_mgm_user_rss_token', $token, true);

		}

	}

	// return

	return $token;

}



/**

 * create rss token

 *

 * @param none

 * @return string token code

 * @since 1.0

 */

function mgm_create_rss_token() {

	// salt

	$salt = 'magicmembersrsstoken';

	// return 

	return md5( mt_rand(10000,15000) . $salt . mt_rand(10000,15000) );

}



/**

 * check if rss token enabled

 *

 * @param none

 * @return boolean enabled status

 * @since 1.0

 */

function mgm_use_rss_token() {	

	// return

	return bool_from_yn( mgm_get_class('system')->get_setting('use_rss_token') );

}



/**

 * get user membership type

 *

 * @param int user id

 * @return string label

 * @since 1.0

 */

function mgm_get_user_membership_type($user_id=false, $return='label') {

	// user

	if (!$user_id) {

		$user_id = mgm_get_user_id();

	}

	// default

	$membership_type = 'guest';

	// user

	if ($user_id) {

		$user = get_userdata($user_id);

		$member = mgm_get_member($user_id);

		$expiry = false;

		$membership_type = 'free';

		// member		

		if (isset($member)) {		

			// loop	

			foreach ($member as $key=>$value) {

				if ($key == 'membership_type' && $value) {

					$membership_type = strtolower($value);

				} else if ($key == 'expiry_date') {

					$expiry = $value;

				}

			}

		}

		// type

		if ($membership_type) {

			if ($member->status != MGM_STATUS_ACTIVE) {

				$membership_type = 'free';

			} else if ($expiry && time() > mysql2date('U', $expiry)) {

				$membership_type = 'free';

			}

		}		

	}

	

	// return

	if($return == 'label'){

		return mgm_get_class('membership_types')->get_type_name($membership_type);

	}elseif($return == 'nicecode'){

		return mgm_get_class('membership_types')->get_type_nicecode($membership_type);	

	}else{

		return $membership_type;

	}

}



/**

 * check if there is any redirect condition on post

 *

 * @param object system

 * @return boolean redirect status

 * @since 1.0

 */

function mgm_check_redirect_condition($system_obj=NULL) {	

	// system

	if(!$system_obj) $system_obj = mgm_get_class('system');

	

	// init

	$return      = false;

	$current_url = mgm_current_url();	

	$admin_membership_page = ( mgm_get_var('page', '', true) == 'mgm/membership/content' ) ? true : false;

	

	// check page

	if (!$admin_membership_page) {	

		// there is redirect setting

		$no_access_redirect_loggedin_users  = trim($system_obj->get_setting('no_access_redirect_loggedin_users'));

		$no_access_redirect_loggedout_users = trim($system_obj->get_setting('no_access_redirect_loggedout_users'));

		$redirect_on_homepage               = bool_from_yn($system_obj->get_setting('redirect_on_homepage'));

		

		// check

		if ( !empty($no_access_redirect_loggedin_users) || !empty($no_access_redirect_loggedout_users) ) {

			// start as redirect

			$return = true;	// enable			



			// user logged in	

			if(is_user_logged_in()){

				// disable redirect for logged in user if there is no url set

				if(empty($no_access_redirect_loggedin_users)){

					$return = false;// disable

				}

			}else{

			// user not logged in, 

				// disable redirect for logged out user if there is no url set

				if(empty($no_access_redirect_loggedout_users)){

					$return = false;// disable

				}

			}

			

			// redirect on home

			if(is_home() && $redirect_on_homepage == TRUE){

				$return = true;// enable

			}		



			// check token request or feed, feed will not use redirect feature

			if ( is_feed() || (mgm_get_var('token', '', true) != '' && mgm_use_rss_token()) ) {

				$return = false;// disable

			}

			//Issue #1242

			// last check, ridirect should only work on single post or a page, blog list will not use this feature

			elseif(!$return && !(is_single() || is_page())){

				$return = true;// enable

			}

		}

	}

	// return

	return $return;

}



/**

 * get current url

 */	

function mgm_current_url() {

	$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";

	$protocol = mgm_strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;

	$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);

	return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];

}



/**

 * get sub str

 */	

function mgm_strleft($s1, $s2) {

	return substr($s1, 0, strpos($s1, $s2));

}



/**

 * convert object to array

 */	

function mgm_object2array( $object ){

	if( !is_object( $object ) && !is_array( $object ) ){

		return $object;

	}

	

	if( is_object( $object ) ){

		$object = get_object_vars( $object );

	}

	

	return array_map( 'mgm_object2array', $object );

}



/**

 * Magic Members parse download tag

 *

 * @package MagicMembers

 * @since 2.5

 * @desc parse download tag embeded in pots/page, works via wp shortcode api 

 * @param string post/page content

 * @return string modified content

 */ 

function mgm_download_parse($content) {

	global $wpdb;

	// get system

	$system_obj = mgm_get_class('system');

	// hook

	$hook = $system_obj->get_setting('download_hook', 'download');

	// slug

	$slug = $system_obj->get_setting('download_slug', 'download');

	// match

	if (substr_count($content,"[" . $hook . "#")) {

		// get downloads	

		$downloads = $wpdb->get_results('SELECT id, title, filename, post_date, members_only, user_id,code FROM `' . TBL_MGM_DOWNLOAD . '`');

		// if has downloads

		if ($downloads) {

			// init

			$patts = $subs = array();			

			// loop

			foreach($downloads as $download) {

				// download url

				$download_url = mgm_download_url($download, $slug);

				// trim last slash

				$download_url = rtrim($download_url, '/');

				

				// Download link

				$link    = '<a href="' . $download_url . '" title="' . $download->title . '" >' . $download->title . '</a>';

				$patts[] = "[" . $hook . "#" . $download->id . "]";

				$subs[]  = $link;

				

				// image

				$download_image_button = sprintf('<img src="%s" alt="%s" />',MGM_ASSETS_URL . 'images/download.gif', $download->title);

				// add filter

				$download_image_button = apply_filters('mgm_download_image_button', $download_image_button, $download->title);

				// Image link

				$link    = '<a href="'.$download_url . '" title="'.$download->title.'">'.$download_image_button.'</a>';

				$patts[] = "[" . $hook . "#" . $download->id . "#image]";

				$subs[]  = $link;

				

				// Button link

				$link    = '<input type="button" name="btndownload-'.$download->id.'" onclick="window.location=\''.$download_url.'\'" title="'.$download->title.'" value="'.__('Download','mgm').'"/>';

				$patts[] = "[" . $hook . "#" . $download->id . "#button]";

				$subs[]  = $link;



				// Download link with filesize

				$link    = '<a href="'.$download_url . '" title="'.$download->title.'" >'.$download->title.' - '.mgm_file_get_size($download->filename).'</a>';

				$patts[] = "[" . $hook . "#" . $download->id . "#size]";

				$subs[]  = $link;



				// Download url only

				$link    = $download_url ;

				$patts[] = "[" . $hook . "#" . $download->id . "#url]";

				$subs[]  = $link;

			}

			// replace

			$content = str_replace($patts, $subs, $content);

		}

	}

	// return

	return $content;

}



/**

 * Magic Members get file size

 *

 * @package MagicMembers

 * @since 2.5

 * @desc get downloadble file size

 * @param string file path

 * @return string size formatted

 */ 

function mgm_file_get_size($fileurl) {

	// init

	$filesize = '';

	// s3 file

	if(mgm_is_s3_file($fileurl)){

		$filesize = mgm_get_s3file_size($fileurl);

	}else{

		// path for  same origin

		if(preg_match('#^' . get_option('siteurl') . '#i', $fileurl)){

			// path

			$filepath = mgm_get_file_url($fileurl);

			// file exists

			if (file_exists($filepath)) $filesize = @filesize($filepath);			 

		}else{		

			$filesize = mgm_remote_head($fileurl, 'content-length', false, false);

		}		

	}	

	// size

	if ((int)$filesize > 0) {

		// bytes array

		$bytes = array('bytes','KB','MB','GB','TB');

		// loop

		foreach($bytes as $byte) {

			// check

			if($filesize > 1024){

				$filesize = $filesize / 1024;

			} else {

				break;

			}

		}

		// return	

		return round($filesize, 2) . ' ' . $byte;

	}

	// error

	return $filesize;

}



/**

 * Magic Members download posts

 * get posts attached to a download

 *

 * @package MagicMembers

 * @since 2.5

 * @param int $download_id

 * @return array $download_posts

 */ 

function mgm_get_download_posts($download_id) {

	global $wpdb;	

	// sql

	$sql = 'SELECT `post_id`,`post_title` FROM `' . TBL_MGM_DOWNLOAD_POST_ASSOC . '` A JOIN `'.$wpdb->posts.'` B ON (A.post_id=B.ID) WHERE `download_id` = %d';

	// fetch

	return $wpdb->get_results($wpdb->prepare($sql, $download_id));

}



/**

 * Magic Members download post ids

 * get posts attached to a download

 *

 * @package MagicMembers

 * @since 2.5

 * @param int $download_id

 * @return array $download_posts_ids

 */ 

function mgm_get_download_post_ids($download_id) {

	// init

	$download_post_ids = array();

	// get

	if($posts = mgm_get_download_posts($download_id)){

		// loop	

		foreach ($posts as $post) {			

			// set

			$download_post_ids[] = $post->post_id;			

		}

	}		

	// return 

	return $download_post_ids;

}



/**

 * checking user downlaod limit check

 */	

function mgm_download_user_limit_check($download_id) {	

	global $wpdb;		

	// current_user

	$current_user = wp_get_current_user();	

	// row

	$row = new stdClass();

	// count

	$row->count = false;

	// sql

	$sql = 'SELECT `count` FROM `' . TBL_MGM_DOWNLOAD_LIMIT_ASSOC . '`  WHERE `download_id` = %d AND `user_id` = %d';

	// fetch

	return $row = $wpdb->get_row($wpdb->prepare($sql, $download_id, $current_user->ID));

}



/**

 * user downlaod limit insert

 */	

function mgm_download_user_limit_insert($download_id) {

	global $wpdb;	

	// current_user

	$current_user = wp_get_current_user();

	// sql

	return $wpdb->insert(TBL_MGM_DOWNLOAD_LIMIT_ASSOC, array('user_id'=>$current_user->ID,'download_id'=>$download_id,'count'=>1));

}



/**

 * update user downlaod limit update

 */	

function mgm_download_user_limit_update($download_id, $count) {

	global $wpdb;	

	// current_user

	$current_user = wp_get_current_user();

	// sql

	return $wpdb->update(TBL_MGM_DOWNLOAD_LIMIT_ASSOC, array('count' => $count), array('user_id'=>$current_user->ID,'download_id'=>$download_id));

}



/**

 * checking ip downlaod limit check

 */

function mgm_download_ip_limit_check($download_id) {	

	global $wpdb;	

	// ip

	$ip_address = mgm_get_client_ip_address();	

	// row

	$row = new stdClass();

	// count

	$row ->count = false;

	// sql

	$sql = 'SELECT `count` FROM `' . TBL_MGM_DOWNLOAD_LIMIT_ASSOC . '`  WHERE `download_id` = %d AND `ip_address` = %s';

	// fetch

	return $row = $wpdb->get_row($wpdb->prepare($sql, $download_id, $ip_address));

}



/**

 * ip downlaod limit insert

 */

function mgm_download_ip_limit_insert($download_id) {	

	global $wpdb ;	

	// fetch clients ip

	$ip_address = mgm_get_client_ip_address();

	// sql

	return $wpdb->insert(TBL_MGM_DOWNLOAD_LIMIT_ASSOC, array('ip_address'=>$ip_address,'download_id'=>$download_id,'count'=>1));

}



/**

 * ip downlaod limit update

 */

function mgm_download_ip_limit_update($download_id, $count) {

	global $wpdb ;	

	// fetch clients ip

	$ip_address = mgm_get_client_ip_address();

	// sql

	return $wpdb->update(TBL_MGM_DOWNLOAD_LIMIT_ASSOC, array('count' => $count), array('ip_address'=>$ip_address,'download_id'=>$download_id));

}



/**

 * getting client ip address

 */

function mgm_get_client_ip_address(){

	// default ip address

	$ip_address = $_SERVER['REMOTE_ADDR'];

	// check client vars

	$client_vars = array('HTTP_CLIENT_IP','HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR');

	// loop

	foreach($client_vars as $client_var){

		// check

		if (isset($_SERVER[$client_var]) && !empty($_SERVER[$client_var])) {

			$ip_address = $_SERVER[$client_var]; break;

		}

	}

	// return

	return $ip_address;

}



/**

 * download error codes

 */

function mgm_download_error($code, $return=false){

	// error

	$error = '';

	// code

	switch ($code){

		case 1: 

			$error = __('You can not download this file because your download limit exceeded.','mgm');

		break;

		case 2: 

			$error = __('You can not download this file because it does not exist. Please notify the Administrator.', 'mgm');

		break;

		case 3: 

			$error = __('You can not download this file because it expired.','mgm');

		break;

		case 4: 

			$error = __('You can not download this file because you do not have access.','mgm');

		break;

	}	

	// return 

	if($return) return $error;

	// print

	echo $error;

}



/**

 * Magic Members verify file download

 *

 * @package MagicMembers

 * @since 2.5

 * @desc verify file download

 * @param string download code

 * @return none

 */ 

function mgm_download_file($code) {

	global $wpdb;

	

	// current_user

	$current_user = wp_get_current_user();

	// system

	$system_obj = mgm_get_class('system');

	// url

	$no_access_redirect_download = $system_obj->get_setting('no_access_redirect_download');

	// redirect

	$do_redirect = (empty($no_access_redirect_download)) ? false : true;	

	// allow default

	$allow_download = true;

	// data fetch

	if ($download = mgm_get_download_data($code)) {

		// for members

		if ( bool_from_yn($download->members_only) ) {

			// reset as restricted

			$allow_download = false;

			// user check

			if ($current_user->ID) {

				// allow admin

				if (is_super_admin()) { // is_super_admin

					$allow_download = true;

				}else{

					// get post mapped

					$posts = mgm_get_download_post_ids($download->id);

					// loop	

					foreach ($posts as $post_id) {

						// only  when user has access to mapped post

						if (mgm_user_has_access($post_id)) {

							// set access

							$allow_download = true;

							// skip

							break;

						}

					}

					//check download included in guest restrict via post/page access issue #1609

					if(!$allow_download && isset($_REQUEST['guest_token']) && isset($_REQUEST['post_id'])){

						// only  when user has access to mapped post

						if (mgm_user_has_access($_REQUEST['post_id'])) {

							// set access

							$allow_download = true;

						}												

					}

										

					// download limit user member access issue #902

					if(!empty($download->download_limit) && (int)$download->download_limit > 0 && $allow_download){

						

						$download_limit = mgm_download_user_limit_check($download->id);	

			

						if(empty($download_limit)){

							mgm_download_user_limit_insert($download->id);

						}else {

							if($download_limit->count < $download->download_limit){

								// count

								$count = ($download_limit->count + 1);

								// update

								mgm_download_user_limit_update($download->id,$count) ;

							}else {

								$allow_download = false;

								// redirect	

								if($do_redirect) mgm_redirect(add_query_arg(array('error_code'=>1), $no_access_redirect_download));

								// show mesage if redirect does not set

								mgm_download_error(1);

								exit;

							}

						}

					}					

				} 

			}else {

				//check download included in guest restrict via post/page access issue #1609

				if(!$allow_download && isset($_REQUEST['guest_token']) && isset($_REQUEST['post_id'])){

					

					// only  when user has access to mapped post

					if (mgm_user_has_access($_REQUEST['post_id'])) {

						// set access

						$allow_download = true;

					}												

				}			

			}		// end member restriction check			

		}else{			

			// download limit user member access issue #902

			if ($current_user->ID) {

			

				// download limit user member access issue #902

				if(!empty($download->download_limit) && (int)$download->download_limit > 0){

					

					$download_limit = mgm_download_user_limit_check($download->id);	

				

					if(empty($download_limit)){

						mgm_download_user_limit_insert($download->id);

					}else {

						if($download_limit->count < $download->download_limit){

							$count = ($download_limit->count + 1);

							mgm_download_user_limit_update($download->id,$count) ;

						}else {

							$allow_download = false;

							// redirect	

							if($do_redirect) mgm_redirect(add_query_arg(array('error_code'=>1), $no_access_redirect_download));

							// show mesage if redirect does not set

							mgm_download_error(1);

							exit;

						}

					}

				}

			}else {

				

				if ( bool_from_yn($download->restrict_acces_ip) ) {				

					// download limit ip member access issue #902

					if(!empty($download->download_limit) && (int)$download->download_limit > 0){

						

						$download_limit = mgm_download_ip_limit_check($download->id);	

					

						if(empty($download_limit)){

							mgm_download_ip_limit_insert($download->id);

						}else {

							if($download_limit->count < $download->download_limit){

								$count = ($download_limit->count + 1);

								mgm_download_ip_limit_update($download->id,$count) ;

							}else {

								$allow_download = false;

								// redirect								

								if($do_redirect) mgm_redirect(add_query_arg(array('error_code'=>1), $no_access_redirect_download));

								// show mesage if redirect does not set

								mgm_download_error(1);

								exit;

							}

						}

					}

				}

			}			

		}

		

		// check expire

		$download_expired = false;

		// allowed alreay

		if($allow_download){			

			// expire date

			if(!is_null($download->expire_dt)){

				// expired

				if(intval($download->expire_dt) && time() > strtotime($download->expire_dt)){

					$download_expired = true;					

				}

			}

		}

		

		// allowed

		if ($allow_download && !$download_expired) {

			// check if s3 resource

			if(mgm_is_s3_file($download->filename)){

				//decode - issue #1727

				$download->filename =urldecode($download->filename);

				// expired

				$aws_qsa_expires = $system_obj->get_setting('aws_qsa_expires', '1 HOUR');

				// check if torrent 

				if(bool_from_yn($download->is_s3_torrent)){

					// redirect to amazon secure url								

					if($torent_url = mgm_get_s3torent_url($download->filename, $aws_qsa_expires)){

						wp_redirect($torent_url); exit;

					}

				}else{

					// check

					if( bool_from_yn($system_obj->get_setting('aws_enable_qsa', 'N')) ){

						// redirect to amazon secure url								

						if($token_url = mgm_get_s3token_url($download->filename, $aws_qsa_expires)){

							wp_redirect($token_url); exit;

						}

					}

				}

				// download as usual

				mgm_stream_download_s3($download->filename); exit;

			}else{

			// filepath

				$filepath = mgm_get_abs_file($download->filename);						

				// check

				if (file_exists($filepath)) {									

					// do the  download									

					mgm_stream_download($filepath); 					

					// delete if s3 file

					if(mgm_is_s3_file($filepath)){ // old code kept

						// delete

						mgm_delete_file($filepath);

					}

					// exit

					exit();			

				} else {			

					// redirect	

					if($do_redirect) mgm_redirect(add_query_arg(array('error_code'=>2), $no_access_redirect_download));

					// show mesage if redirect does not set

					mgm_download_error(2);

					exit();

				}

			}

		} else {

			// redirect	

			$code = ($download_expired ? '3' : '4');	

			// redirect

			if($do_redirect) mgm_redirect(add_query_arg(array('error_code'=>$code), $no_access_redirect_download));			

			// show mesage if redirect does not set

			mgm_download_error($code);

			exit();

		}

	} else {	

		// redirect	

		if($do_redirect) mgm_redirect(add_query_arg(array('error_code'=>4), $no_access_redirect_download));

		// show mesage if redirect does not set

		mgm_download_error(4);

		exit;

	}

}



/**

 * Magic Members force download

 *

 * @package MagicMembers

 * @since 2.5

 * @desc force download

 * @param string filepath

 * @return none

 * @deprecated

 */ 

function mgm_force_download($file){

	global $mgm_mimes;

	

	// file name	

	$filename = basename($file);

	// the file extension

	$fparts = explode('.', $filename);

	$extension = end($fparts);

		

	// default mime if we can't find it

	if ( ! isset($mgm_mimes[$extension])){

		$mime = 'application/octet-stream';

	}else{

		$mime = (is_array($mgm_mimes[$extension])) ? $mgm_mimes[$extension][0] : $mgm_mimes[$extension];

	}

	// ie

	if (strstr(mgm_server_var('HTTP_USER_AGENT'), "MSIE")){

		header('Content-Type: "'.$mime.'"');

		header('Content-Disposition: attachment; filename="'.$filename.'"');

		header('Expires: 0');

		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

		header("Content-Transfer-Encoding: binary");

		header('Pragma: public');

		header("Content-Length: ".@filesize($file));

	}else{

		header('Content-Type: "'.$mime.'"');

		header('Content-Disposition: attachment; filename="'.$filename.'"');

		header("Content-Transfer-Encoding: binary");

		header('Expires: 0');

		header('Pragma: no-cache');

		header("Content-Length: ".@filesize($file));

	}	

	// print

	print file_get_contents($file);

	// exit

	exit();

}



/**

 * Magic Members stream download - with restart functionlity

 *

 * @package MagicMembers

 * @since 2.5

 * @desc force download with restart functionlity, modified for FDM support, issue #876

 * @param string filepath

 * @return none

 */ 

function mgm_stream_download($filepath){

	global $mgm_mimes;

	

	// check connection

	if( connection_status()!= 0 ) return false;

	

	// see if the file exists

    if (!is_file($filepath)){ die("<b>404 File not found!</b>"); }

	// size

	$size = @filesize($filepath);	

	$fileinfo = @pathinfo($filepath);

	// error

	if ($size == 0 ) { die('Empty file! download aborted'); }

	// workaround for IE filename bug with multiple periods / multiple dots in filename

    // that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe	

	// set filename

	$filename = (strstr(mgm_server_var('HTTP_USER_AGENT'), "MSIE")) ? preg_replace('/\./', '%2e', $fileinfo['basename'], substr_count($fileinfo['basename'],'.') - 1) : $fileinfo['basename'];		

	// extension

	$file_extension = strtolower($fileinfo['extension']);

	// default mime if we can't find it

	if ( isset($mgm_mimes[$file_extension]) ){

	// from mimes

		$content_type = (is_array($mgm_mimes[$file_extension])) ? $mgm_mimes[$file_extension][0] : $mgm_mimes[$file_extension];		

	}else{

	// default

		$content_type = 'application/force-download';//'application/octet-stream';

	}

	

	// flag

	$is_resume = true;

	$range = '';

	// check if http_range is sent by browser (or download manager)

    if($is_resume && isset($_SERVER['HTTP_RANGE'])){

		// split

        list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);		

		// check

        if ($size_unit == 'bytes'){

            // multiple ranges could be specified at the same time, but for simplicity only serve the first range

            // http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt

            list($range, $extra_ranges) = explode(',', $range_orig, 2);

        }

    }

	

    //figure out download piece from range (if set)

    list($seek_start, $seek_end) = explode('-', $range, 2);



    // set start and end based on range (if set), else set defaults

    // also check for invalid ranges.

    $seek_end = (empty($seek_end)) ? ($size - 1) : min(abs(intval($seek_end)),($size - 1));

    $seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);

    //issue #1375

    session_write_close();	

	//add headers if resumable

    if ($is_resume){

        // Only send partial content header if downloading a piece of the file (IE workaround)

        if ($seek_start > 0 || $seek_end < ($size - 1)) {

			// header 

            header('HTTP/1.1 206 Partial Content');

			// log				

			// mgm_log("Partial Content {$seek_start}-{$seek_end}/{$size}", __FUNCTION__ . '_size_range');	

        }

		// others

        header('Accept-Ranges: bytes');

        header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$size);

    }



    //headers for IE Bugs (is this necessary?)

    //header("Cache-Control: cache, must-revalidate");  

    //header("Pragma: public");



    header('Content-Type: ' . $content_type);

    header('Content-Disposition: attachment; filename="' . $filename . '"');

    header('Content-Length: '.($seek_end - $seek_start + 1));



    //open the file

    $fp = @fopen($filepath, 'rb');

    // seek to start of missing part

    @fseek($fp, $seek_start);

    //mgm_log(memory_get_peak_usage(true)."before download", __FUNCTION__);

    //start buffered download

    while(!feof($fp)){

         //reset time limit for big files

        @set_time_limit(0);

        @ini_set('memory_limit', 1073741824);	// 1024M

        print(@fread($fp, 1024*8));        

        @ob_flush();

        @flush();

        // sleep

		@usleep(1000);	

		// flush	

		@ob_end_flush();	//keep this as there were some memory related issues(#545)

    }

	// close

    @fclose($fp);   

    //mgm_log(memory_get_peak_usage(true)."after download", __FUNCTION__);

    exit;

	

	// end	

	return((connection_status()==0) && !connection_aborted());

}



/**

 * Magic Members stream download - with restart functionlity

 *

 * @package MagicMembers

 * @since 2.5

 * @desc force download with restart functionlity

 * @param string filepath

 * @return none

 * @deprecated 

 */ 

function mgm_stream_download_old($filepath){



	global $mgm_mimes;



	// check connection



	if( connection_status()!= 0 ) return false;



	// extended server configurations:



	@ini_set('max_execution_time' , 7200);



	@ini_set('upload_max_filesize', 1048576000);// 1000M



	@ini_set('post_max_size'      , 1048576000);// 1000M		



	// set speed



	$max_speed = 2000;



	$do_stream = false;



	$filename = basename($filepath);	



	$extension = strtolower(end(explode('.',$filename)));



	// default mime if we can't find it



	if ( !isset($mgm_mimes[$extension]) ){



		$content_type = 'application/octet-stream';



	}else{



		$content_type = (is_array($mgm_mimes[$extension])) ? $mgm_mimes[$extension][0] : $mgm_mimes[$extension];



	}



	// start headers



	header("Cache-Control: public");



	header("Content-Transfer-Encoding: binary\n");



	header("Content-Type: $content_type");



	// disposition



	$content_disposition = 'attachment';



	// IE



	if (strstr(mgm_server_var('HTTP_USER_AGENT'), "MSIE")) {



		// set filename



		$filename= preg_replace('/\./', '%2e', $filename, substr_count($filename,'.') - 1);		



	}



	// set



	header("Content-Disposition: $content_disposition;filename=\"$filename\"");	



	header("Accept-Ranges: bytes");



	// range



	$range = 0;



	// size



	$size = @filesize($filepath);	



	// supports range



	if(isset($_SERVER['HTTP_RANGE'])) {



		// get range



		list($a, $range) = explode('=', $_SERVER['HTTP_RANGE']);



		// set



		str_replace($range, '-', $range);



		// set size 



		$size2 = $size-1;



		// length



		$new_length = $size-$range;



		// set



		header("HTTP/1.1 206 Partial Content");



		header("Content-Length: $new_length");



		header("Content-Range: bytes $range$size2/$size");		



	} else {



		// set size



		$size2 = $size-1;



		// set header



		header("Content-Range: bytes 0-$size2/$size");



		header("Content-Length: $size");		



	}



	// error



	if ($size == 0 ) { 		



		die('Empty file! download aborted');



	}



	// kill quotes



	if(function_exists('set_magic_quotes_runtime')) @set_magic_quotes_runtime(0);



	// file pointer



	$fp = fopen($filepath, 'rb');



	// seek



	fseek($fp,$range);		



	// check				



	while(!feof($fp)) {



		// limit



		@set_time_limit(0);	



		@ini_set('memory_limit', 1073741824);	// 1024M



		// read



		echo fread($fp,1024*$max_speed);		



		// flush



		@ob_flush();		



		@flush();		



		// sleep



		@usleep(1000);	



		// flush	



		@ob_end_flush();	//keep this as there were some memory related issues(#545)						



	}	



	// close file



	fclose($fp);	



	// end	



	return((connection_status()==0) && !connection_aborted());



}



/**

 * old range download tested

 * 

 * @deprecated

 */

function mgm_stream_download_test($filepath){	

	// check connection

	if( connection_status()!= 0 ) return false;

	

	// extended server configurations:

	// @ini_set('max_execution_time' , 7200);

	// @ini_set('upload_max_filesize', 1048576000);// 1000M

	// @ini_set('post_max_size'      , 1048576000);// 1000M



	// start headers

	header("Cache-Control: public");

	header("Content-Transfer-Encoding: binary\n");

	header("Content-Type: $content_type");

	header("Content-Disposition: attachment; filename=\"$filename\"");	

	header("Accept-Ranges: bytes");

	// disable mb encoding

	mb_http_output('pass');

	// range

	$range = 0;	

	// supports range

	if(isset($_SERVER['HTTP_RANGE'])) {

		// get range

		list($a, $range) = explode('=', $_SERVER['HTTP_RANGE']); // bytes=17203025-		

		// set

		str_replace($range, '-', $range);

		// set size 

		$size2 = $size-1;

		// length

		$new_length = $size-$range;		

		// set headers

		header("HTTP/1.1 206 Partial Content");

		header("Content-Length: $new_length");

		header("Content-Range: bytes $range$size2/$size");		

		// log

		// mgm_log("size: $size | new_length: $new_length | Content-Range: bytes $range$size2/$size", __FUNCTION__ . '_size_range');	

	} else {

		// set size

		$size2 = $size-1;

		// set headers		

		header("Content-Length: $size");	

		header("Content-Range: bytes 0-$size2/$size");

		// log		

		// mgm_log("size: $size | Content-Range: bytes 0-$size2/$size", __FUNCTION__ . '_size_norange');		

	}

	

	// kill quotes

	if(function_exists('set_magic_quotes_runtime')) @set_magic_quotes_runtime(0);

	// file pointer

	if($fp = @fopen($filepath, 'rb')){

		// seek

		@fseek($fp,$range);		

		// check				

		while(!feof($fp)) {

			// limit

			@set_time_limit(0);	

			// @ini_set('memory_limit', 1073741824);	// 1024M

			// read

			echo @fread($fp, 1024*$max_speed);		

			// flush

			@ob_flush();		

			@flush();		

			// sleep

			@usleep(1000);	

			// flush	

			@ob_end_flush();	//keep this as there were some memory related issues(#545)						

		}	

		// close file

		@fclose($fp);	

	}

	

	// end	

	return((connection_status()==0) && !connection_aborted());

}



/**

 * Magic Members stream download with restart functionlity for s3

 *

 * @package MagicMembers

 * @since 2.5

 * @desc force download with restart functionlity

 * @param string filepath

 * @return none

 */ 

function mgm_stream_download_s3($filepath){

	global $mgm_mimes;

	// log

	// mgm_log($_SERVER, __FUNCTION__);

	// check connection

	if( connection_status()!= 0 ) return false;

	// extended server configurations:

	@ini_set('max_execution_time' , 7200);

	@ini_set('upload_max_filesize', 1048576000);// 1000M

	@ini_set('post_max_size'      , 1048576000);// 1000M		

	// set speed

	$max_speed = 2000;

	$do_stream = false;

	$filename = basename($filepath);	

	$extension = strtolower(end(explode('.',$filename)));

	// default mime if we can't find it

	if ( !isset($mgm_mimes[$extension]) ){

		$content_type = 'application/octet-stream';

	}else{

		$content_type = (is_array($mgm_mimes[$extension])) ? $mgm_mimes[$extension][0] : $mgm_mimes[$extension];

	}	

	// disposition

	$content_disposition = 'attachment';

	// IE

	if (strstr(mgm_server_var('HTTP_USER_AGENT'), "MSIE")) {

		// set filename

		$filename= preg_replace('/\./', '%2e', $filename, substr_count($filename,'.') - 1);		

	}	

	// range

	$range = 0;

	// size

	$size = @mgm_get_s3file_size($filepath);	

	// error

	if ($size == 0 ) { 		

		die('Empty file! download aborted');

	}

    //issue #1375

    session_write_close();	

	// start headers

	header("Cache-Control: public");

	header("Content-Transfer-Encoding: binary\n");

	header("Content-Type: $content_type");

	header("Content-Disposition: $content_disposition;filename=\"$filename\"");	

	header("Accept-Ranges: bytes");

	// supports range and sent, subsequest request

	if(isset($_SERVER['HTTP_RANGE'])) {

		// get range comes as bytes=1-10

		list($a, $range) = explode('=', $_SERVER['HTTP_RANGE']);

		// set

		str_replace($range, '-', $range);

		// set size 

		$size2 = $size-1;

		// length

		$new_length = $size-$range;

		// set

		header("HTTP/1.1 206 Partial Content");

		header("Content-Length: $new_length");

		header("Content-Range: bytes $range$size2/$size");		

	} else {

		// set size

		$size2 = $size-1;// one byte less

		// set header

		header("Content-Range: bytes 0-$size2/$size");

		header("Content-Length: $size");		

	}

	// log

	// mgm_log('size2: '.$size2. ' range: '.$range, __FUNCTION__);

	// kill quotes

	if(function_exists('set_magic_quotes_runtime')) @set_magic_quotes_runtime(0);	

	// limit

	@set_time_limit(0);	

	//@ini_set('memory_limit', 1073741824);	// 1024M

	@ini_set('memory_limit', '1024M');	// 1024M 

	// get file

	echo mgm_get_s3file($filepath, array('range'=>$range));// download by parts

	// flush

	@ob_flush();		

	@flush();		

	// sleep

	usleep(1000);	

	// flush	

	@ob_end_flush();	//keep this as there were some memory related issues(#545)		

	// end	

	return((connection_status()==0) && !connection_aborted());

}	



/**

 * Magic Members stream file

 *

 * @package MagicMembers

 * @since 2.5

 * @desc stream filename 

 * @param string filepath

 * @return none

 */ 

function mgm_stream_file($filename, $protect='downloads'){

	global $mgm_mimes;

	// dirs

	$protected_dirs = array('downloads'=>MGM_FILES_DOWNLOAD_DIR);	

	// dir

	$protected_dir = $protected_dirs[$protect];

	$filepath      = $protected_dir . $filename;	

	// check access

	$has_access    = is_user_logged_in(); // @ToDo add user wise protection

	// check

	if(file_exists($filepath) && is_readable($filepath) && $has_access){

		// content

		if($content = file_get_contents($filepath)){	

			// the file extension

			$extension = end(explode('.', $filename));			

			// default mime if we can't find it	

			if ( !isset($mgm_mimes[$extension]) ){	

				$content_type = 'application/octet-stream';	

			}else{	

				$content_type = (is_array($mgm_mimes[$extension])) ? $mgm_mimes[$extension][0] : $mgm_mimes[$extension];	

			}	

			// size

			$filesize = @filesize($filepath);

			// header	

			header("Content-Type: " . $content_type);				

			header("Content-Length: " . $filesize);	

			header("Content-Disposition: inline; filename=" . $filename);	

			// print		

			print $content;

		}else{		

			print sprintf('%s could not be read', $filename); 

		}		

		// exit

		exit;		

	}else{

		// header

		status_header(403); echo 'Forbidden'; exit;

	}		

}



/**

 * read file

 */

function mgm_readfile($file){

	// We don't need to write to the file, so just open for reading.	

	if ( $handle = @fopen($file, 'rb') ) {

	   while (!feof($handle)) {

		   $buffer .= fgets($handle, 4096);		   

	   }

	   fclose($handle);

	}



	// return

	return $buffer;

}



/**

 * check is a s3 file

 */

function mgm_is_s3_file($fileurl){

	//return ( preg_match('/^https:\/\/s3\.amazonaws\.com/',$fileurl) || preg_match('/^s3file_/', basename($fileurl)) );

	return ( preg_match('/^https:\/\/s3\.amazonaws\.com/',$fileurl) || 

			 preg_match('/^s3file_/', basename($fileurl)) ||

			 preg_match('/https:\/\/s3(.*)\.amazonaws\.com/', $fileurl) //Eg: https://s3-eu-west-1.amazonaws.com/ 			 

		   );		 

}



/**

 * get abs path

 */

function mgm_get_abs_file($fileurl) {

	// check s3 file	

	if(mgm_is_s3_file($fileurl)){

		return $fileurl = mgm_download_s3file($fileurl);

	}	

	// return 

	return str_replace(trailingslashit(get_option('siteurl')), str_replace('\\', '/', ABSPATH), $fileurl);

}



/**

 * get download data

 */

function mgm_get_download_data($code=false) {

	global $wpdb;

	// init

	$row = new stdClass();

	// set

	$row->id = $row->title = $row->filename = $row->post_date 

	         = $row->members_only = $row->user_id = $row->download_limit 

			 = $row->restrict_acces_ip = $row->is_s3_torrent = false;	

	// check

	if ($code) {

		// sql

		$sql = $wpdb->prepare("SELECT * FROM `" . TBL_MGM_DOWNLOAD . "`	WHERE code = '%s'", $code);

		// get 		

		$row = $wpdb->get_row($sql);

	}		

	// return 

	return $row;

}



/**

 * get s3 file info

 */

function mgm_get_s3file_info($fileurl){

	// system 

	$system_obj = mgm_get_class('system');

	// set keys

	$aws_key = $system_obj->get_setting('aws_key');

	$aws_secret_key = $system_obj->get_setting('aws_secret_key');	

	// Include the SDK

	require_once MGM_LIBRARY_DIR . 'third_party/awssdk/sdk.class.php';	

	// s3 object

	$s3 = new AmazonS3($aws_key, $aws_secret_key);

	// disable ssl verification if no ssl

	if(!extension_loaded('openssl')){

		$s3->disable_ssl_verification();

	}

	// get urlpath

	$urlpath = parse_url($fileurl, PHP_URL_PATH);

	// get parts

	$url_parts = explode('/', $urlpath);

	// get bucket from url

	do{

		$bucket = array_shift($url_parts);

	}while(empty($bucket));	

	// filename, including path

	$filename = implode('/',$url_parts);	

	// object

	$s3_info = new stdClass;

	// set vars

	$s3_info->s3       = $s3;

	$s3_info->bucket   = $bucket;

	$s3_info->filename = $filename;

	// return

	return $s3_info; 

}



/**

 * force download s3 file to local file

 *

 * @package MagicMembers

 * @since 2.5

 * @param string filepath

 * @return string local filepath

 */ 

function mgm_download_s3file($fileurl){

	// s3 info

	$s3_info = mgm_get_s3file_info($fileurl);

	

	// local file

	$localfile = MGM_FILES_DOWNLOAD_DIR . 's3file_' . time() . '_' . basename($s3_info->filename);

	

 	// download

	$response = $s3_info->s3->get_object($s3_info->bucket, $s3_info->filename, array('fileDownload' => $localfile));

	

	// Success?

	if($response->isOK()){

		return $localfile;

	}

	// error

	return false;

}



/**

 * get size of s3 file

 *

 * @package MagicMembers

 * @since 2.5

 * @param string filepath

 * @return string local filepath

 */ 

function mgm_get_s3file_size($fileurl){

	// s3 info

	$s3_info = mgm_get_s3file_info($fileurl);

	

	// response

	$response = $s3_info->s3->get_object_filesize($s3_info->bucket, $s3_info->filename);

	

 	// check

	if($response){

		return $response;

	}

	// error

	return false;

}



/**

 * get s3 token url

 *

 * @param string $fileurl

 * @param string $expires

 * @return string $token_url

 * @since 2.7.0

 */

 

function mgm_get_s3token_url($fileurl, $expires='1 HOUR'){

	// s3 info

	$s3_info = mgm_get_s3file_info($fileurl);

	// return

	return $token_url = $s3_info->s3->get_object_url($s3_info->bucket, $s3_info->filename, $expires);

}



/**

 * get s3 torent url

 *

 * @param string $fileurl

 * @param string $expires

 * @return string $torrent_url

 * @since 2.7.0

 */

 function mgm_get_s3torent_url($fileurl, $expires='1 HOUR'){

 	// s3 info

	$s3_info = mgm_get_s3file_info($fileurl);	

	// return

	return $torrent_url = $s3_info->s3->get_torrent_url($s3_info->bucket, $s3_info->filename, $expires);	

}



/**

 * get s3 object

 *

 */

 function mgm_get_s3file($fileurl, $opt=NULL){

 	// s3 info

	$s3_info = mgm_get_s3file_info($fileurl);

	

	// response

	$response = $s3_info->s3->get_object($s3_info->bucket, $s3_info->filename, $opt);		

	// check

	if( $response->isOK() )

		return $response->body;

		

	//  error

	return __('Error reading file.','mgm');	

 }

	

/**

 * check user access to post, uses rss_token form GET to restrict rss feed

 *

 * @package MagicMembers

 * @since 2.5

 * @param int post id

 * @param boolen purchasable

 * @return boolen access 

 */ 

function mgm_user_has_access($post_id = false, $allow_on_purchasable = false) {

	global  $user_data, $wpdb;	

	// current user

	$current_user = wp_get_current_user();

	// get user by username

	if (isset($_GET['username']) && isset($_GET['password'])) {// ? who did this? and why

		$user = wp_authenticate(strip_tags($_GET['username']), strip_tags($_GET['password']));	

	} else if (is_feed() && isset($_GET['token']) && mgm_use_rss_token()) {// added feed check while updating iss#676

	// get user by rss token, only for feed	

		$user = mgm_get_user_by_token(strip_tags($_GET['token']));	

	} else {

	// else get current use if logged in

		$user = $current_user;

	}

	

	// default return

	$return = false;

	

	// post id

	if (!$post_id) $post_id = get_the_id();	

		    

	// if post			

	if ($post_id) {		

		// get post data

        $post = get_post($post_id);   

		

		// check if purchasable    

		$purchasable = mgm_post_is_purchasable($post_id);		

		// check publish status

		$is_published = ($post->post_status == 'publish');

		// allow if set

		if ($allow_on_purchasable && $purchasable) {// if purchasable

			$return = true;

		} else if (isset($user->caps['administrator'])) {// if admin

			$return = true;

		} else if (!$is_published) {// not published

			// Issue #1043. Allow access to the user with editor permissions

			$return = mgm_has_preview_permissions($user->ID, (isset($post->post_type) ? $post->post_type : null ));

		} else { // check other access

			// get mgm post data				

			$post_obj = mgm_get_post($post_id);	

			// allowed types

			$allowed_membership_types = $post_obj->get_access_membership_types();

			// user membership types, including other membeship levels

			$user_membership_types = array();

			// logged in user

			if ($user->ID > 0){		

				// current user type

				// $membership_type    = mgm_get_user_membership_type($user->ID, 'code'); // status is implied through the type.				

				$user_membership_types = mgm_get_subscribed_membershiptypes($user->ID);

			} // end user check

			

			// not defined, use guest

			if (empty($user_membership_types)) {

				$user_membership_types = array('guest');

			}

							

			// check accessible membership types for current post first

			if (array_diff($allowed_membership_types, $user_membership_types) != $allowed_membership_types) { // if any match found

				// set access

				$return = true;

				// check hide content

				if ($user->ID > 0){	

					// get member

					$member = mgm_get_member($user->ID);					

					

					// return on pack join - issue #1227

					if(in_array($member->membership_type,$allowed_membership_types))

						$return = mgm_check_post_packjoin($member, $post);

					else 

						$return = false;

					// no access

					if(!$return) {

					 	// check other memberships if any:

					 	if(isset($member->other_membership_types) && is_array($member->other_membership_types) 

						   && count($member->other_membership_types) > 0) {

							// loop

							foreach ($member->other_membership_types as $key => $other_membership_types) {

								// other membership types							

								$other_membership_types = mgm_convert_array_to_memberobj($other_membership_types, $user->ID);

								// check status

								if(in_array($other_membership_types->status, array(MGM_STATUS_ACTIVE, MGM_STATUS_AWAITING_CANCEL))) {

									// check pack again - issue #1227

									$return = mgm_check_post_packjoin($other_membership_types, $post);

									//stop if any of the packs returned true

									if($return === TRUE) break;

								}								

							}

					 	}

					 }

				}	 

			}			

			

			// on access, also check duration and type

			if ($return == true && $user->ID > 0) {				

				// check membership wise min duration				

				$access_delay = $post_obj->access_delay;	

				// check  - issue #1227

				if(	in_array( $member->membership_type, $user_membership_types ) && 

					in_array($member->status, array(MGM_STATUS_ACTIVE, MGM_STATUS_AWAITING_CANCEL))  && 

					in_array($member->membership_type,$allowed_membership_types)) {



					$return = mgm_check_post_access_delay($member, $user, $access_delay);

				}else{ 

					$return = false;

				}

				

				// if no access

				if(!$return) {					

					//check other memberships if any:

				 	if(isset($member->other_membership_types) && is_array($member->other_membership_types) 

						&& count($member->other_membership_types) > 0) {

						// loop

						foreach($member->other_membership_types as $key => $other_membership_types) {

							// convert

							$other_membership_types = mgm_convert_array_to_memberobj($other_membership_types, $user->ID);

							// check - issue #1227

							if(	in_array( $other_membership_types->membership_type, $user_membership_types ) && 

								in_array($other_membership_types->status, array(MGM_STATUS_ACTIVE, MGM_STATUS_AWAITING_CANCEL)) &&

								in_array($other_membership_types->membership_type,$allowed_membership_types)) {

								// return

								$return = mgm_check_post_access_delay($other_membership_types, $user, $access_delay);

								//stop if any of the packs returned true									

								if($return === TRUE) break;

							}							

						}

				 	}

				}

			}

			

			// if not accessible yet, check purchasable for logged in user/user by token ONLY!

			if (!$return) {				

				// on purchasable, check user has purchased and access expired

				if($purchasable){

					// logged in user

					if($user->ID > 0){

					// true/false

						$return = mgm_user_has_purchased_post($post_id, $user->ID);

					}else{

					// guest token

						if(isset($_GET['guest_token'])){

							$return = mgm_user_has_purchased_post($post_id, NULL, strip_tags($_GET['guest_token']));

						}						

					}

				}						

			}				

		}

	}

	

	// user id

	$user_id = (isset($user->ID)) ? $user->ID : 0;



	// apply filter and return

    return apply_filters('mgm_user_has_access_additional', $return, $post_id, $user_id, $allow_on_purchasable);

}



/**

 * unsubscribe default callback

 */

function mgm_member_unsubscribe(){

	// user_id from post

	extract($_POST);	

	// system	

	$system_obj = mgm_get_class('system');	

	$packs_obj  = mgm_get_class('subscription_packs');	

	$dge        = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));

	

	// find user

	$user       = get_userdata($user_id);	

	$member = mgm_get_member($user_id);

	// multiple membesrhip level update:		

	if(isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']){

		$member = mgm_get_member_another_purchase($user_id, $_POST['membership_type']);	

	}	

		

	// get pack

	if($member->pack_id){

		$subs_pack = $packs_obj->get_pack($member->pack_id);

	}else{

		$subs_pack = $packs_obj->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);

	}

	

	// types

	$duration_exprs = $packs_obj->get_duration_exprs();

					

	// default expire date				

	$expire_date = $member->expire_date;

	if($member->duration_type == 'l')

		$expire_date = date('Y-m-d');

						

	// if trial on 

	if ($subs_pack['trial_on'] && isset($duration_exprs[$subs_pack['trial_duration_type']])) {			

		// if cancel data is before trial end, set cancel on trial expire_date

		$trial_expire_date = strtotime("+{$subs_pack['trial_duration']} {$duration_exprs[$subs_pack['trial_duration_type']]}", $member->join_date);

		

		// if lower

		if(time() < $trial_expire_date){

			$expire_date = date('Y-m-d',$trial_expire_date);

		}

	}	

	

	// old status

	$old_status = $member->status;			

	// if today 

	if($expire_date == date('Y-m-d')){

		// set new status

		$member->status = $new_status = MGM_STATUS_CANCELLED;

		// status string	

		$member->status_str = __('Subscription Cancelled','mgm');					

		$member->expire_date = date('Y-m-d');			

	}else{

		// date format

		$date_format = mgm_get_date_format('date_format');		

		// set new status

		$member->status = $new_status = MGM_STATUS_AWAITING_CANCEL;	

		// status string	

		$member->status_str = sprintf(__('Subscription awaiting cancellation on %s','mgm'), date($date_format, strtotime($expire_date)));			

		// reset on

		$member->status_reset_on = $expire_date;

		$member->status_reset_as = MGM_STATUS_CANCELLED;		

	}		

	

	// multiple memberhip level update:	

	if($post_membership_type = mgm_post_var('membership_type') && $member->membership_type != $post_membership_type){

		mgm_save_another_membership_fields($member, $user_id);

	}else{ 		

		$member->save();	

	}	

	

	// status change event

	do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'member_unsubscribe', $member->pack_id);	

		

	// send email notification to client

	$blogname = get_option('blogname');

	// email	

	$subject = sprintf(__('[%s] Subscription Cancelled','mgm'),$blogname);				

	$message = __('This is an automatic notification from %1$s to %2$s (%3$s). This is a notification to inform you that your subscription has been cancelled. For more information please contact %4$s','mgm');

	$message = sprintf($message, $blogname, $user->display_name, $user->user_email, $system_obj->setting['admin_email']);

	

	// send email notification to user

	mgm_mail($user->user_email, $subject, $message);		



	// notify admin, only if gateway emails on

	if (!$dge) {

		$subject = "[$blogname] {$user->user_email} - {$new_status}";

		$message = "	User display name: {$user->display_name}\n\n<br />

				User email: {$user->user_email}\n\n<br />

				User ID: {$user->ID}\n\n<br />

				Membership Type: {$membership_type}\n\n<br />

				New status: {$new_status}\n\n<br />

				Status message: {$member->status_str}\n\n<br />					

				Payment Mode: Cancelled\n\n<br />

				POST Data was: \n\n<br /><br /><pre>" . print_r($_POST, true) . '</pre>';

		mgm_mail($system_obj->setting['admin_email'], $subject, $message);

	}

	

	// after cancellation hook

	do_action('mgm_membership_subscription_cancelled', array('user_id' =>$user_id));	

	

	// message

	$lformat = mgm_get_date_format('date_format_long');

	$message = sprintf(__("You have successfully Unsubscribed. Your account has been marked for cancellation on %s", "mgm"), ($expire_date == date('Y-m-d') ? 'Today' : date($lformat, strtotime($expire_date)) ));

	

	// redirect 	

	//mgm_redirect('wp-admin/profile.php?page=mgm/profile&unsubscribed=true&unsubscribe_errors='.urlencode($message));

	mgm_redirect(mgm_get_custom_url('membership_details', false, array('unsubscribe_errors'=>urlencode($message))));	

}



/**

 * get user by rss token

 *

 * @param string token

 * @return mixed wp user or false

 * @since 1.0

 */

function mgm_get_user_by_token($token) {

	$meta_exists = true;

	// get users by meta :

	$a_users =  mgm_get_users_with_meta('_mgm_user_rss_token', $token);

	// fetch all users - paginated way

	if (empty($a_users)) {

		$a_users = mgm_get_all_userids();

		$meta_exists = false;

	}

	// default

	$user_id = false;

	// loop

	foreach($a_users as $user){

		// $user will be ID if returned from paginated array, otherwise object

		$uid = is_numeric($user) ? $user : $user->ID; 

		// member object

		$member = mgm_get_member($uid);

		// match

		if($member->rss_token == $token){

			// set

			$user_id = $uid;

			// update usermeta for next time

			if ( ! $meta_exists ) {

				update_user_option($user_id, '_mgm_user_rss_token', $member->rss_token, true);

			}

			// exit

			break;

		}

		// unset

		unset($member);

	}

	// set user

	if( $user_id ) return new WP_User($user_id);

	// error

	return false;

}

/**

 * show custom fields

 *

 * @param int user id

 * @param boolean submit 

 * @param boolean return typpe

 * @return mixed html

 * @since 1.0

 */

function mgm_show_custom_fields($user_ID=false, $submit_row=false, $return=false){

	// return

	return mgm_edit_custom_fields($user_ID, $submit_row, $return);

}



/**

 * edit custom fields

 */

function mgm_edit_custom_fields($user_ID=false, $submit_row=false, $return=false) {

	// get user

	if (!$user_ID) $user_ID = mgm_get_user_id();

	// get form object

	if (is_object($user_ID)) $user_ID = $user_ID->ID;

	//check logged in user is super admin:

	$is_admin = (is_super_admin()) ? true : false;

	// system

	$system_obj = mgm_get_class('system');

		

	// get custom_fields

	$profile_fields = mgm_get_config('default_profile_fields', array());

	// get active custom fields on profile page

	//$cf_profile_page = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_profile'=>true)));



	//issue #844 - get active custom fields for profile page

	$cf_profile_pg = mgm_get_class('member_custom_fields');

	$cf_profile_page = array();	

	foreach (array_unique($cf_profile_pg->sort_orders) as $id) :

		foreach($cf_profile_pg->custom_fields as $field):

			// issue #954: show the field only if it is enabled for profile page

			if ($field['id'] == $id && ( $field['display']['on_profile'] || $is_admin)):

				$cf_profile_page[]= $field;

			endif;

		endforeach;

	endforeach;



	$member = mgm_get_member($user_ID);

	//this is a fix for issue#: 589, see the notes for details:

	//This is to read saved coupons as array in order to fix the fatal error on some servers.	

	//This will change the object on each users profile view.

	//Also this will avoid using patch for batch update,	

	$arr_coupon = array('upgrade', 'extend');

	$oldcoupon_found = 0;

	foreach ($arr_coupon as $cpn_type) {

		if(isset($member->{$cpn_type}['coupon']) && is_object($member->{$cpn_type}['coupon'])) {

			$member->{$cpn_type}['coupon'] = (array) $member->{$cpn_type}['coupon'];

			$oldcoupon_found++ ;

		}

	}

	if($oldcoupon_found) {		

		$member->save();

	}

	

	// user

	$user = get_userdata($user_ID);	

	// init

	$html = '';	

	// capture

	$fields = array();	

	//default and readonly fields:

	$default_readonly = array();

	$arr_images = array();	

	//issue #844 	

	$skip_fileds =array('subscription_introduction','coupon','privacy_policy','payment_gateways','terms_conditions',

						'subscription_options','autoresponder','captcha','show_public_profile');

	// loop fields	

	foreach($cf_profile_page as $field){

		// issue#: 255			

		if (in_array($field['name'], array_keys($profile_fields)) ) {			

			//if custom field = defualt field, is read only

			if($field['attributes']['readonly'] && !$is_admin) {				

				$default_readonly[] = $profile_fields[ $field['name'] ]['id'];

				//email and url id is different than custom fields:

				if(in_array($field['name'],array('email','url')))

					$default_readonly[] = $field['name']; 			 	

			}

			continue;

		}

		

		//issue #844 

		if(in_array($field['name'],$skip_fileds)){

			continue;

		}



		// init value

		$value = '';

		//disable readonly for admin user(issue#: 515)

		$ro = (($field['attributes']['readonly'] == true && !$is_admin ) ? 'readonly="readonly"':false);

		// value 

		if (isset($member->custom_fields->$field['name'])) {

			$value = $member->custom_fields->$field['name'];

		}				

		// date	

		if ($field['name'] == 'birthdate') {

			if ($value) {

				//convert saved date to input field format

				$value = mgm_get_datepicker_format('date', $value);

			} else {

				$value = '';				

			}	

			$element = '<input type="text" name="mgm_profile_field['. $field['name'] .']" value="'. $value .'" '. $ro .' class="text '.(($ro)?'':'mgm_date').' mgm_custom_profile_birthdate"/>';

		} else if ($field['name'] == 'country' ) {					

			$countries = mgm_field_values(TBL_MGM_COUNTRY, 'code', 'name');			

			if($ro) {					

				$countries = !empty($value) ? array($value => $countries[ $value ]) : array(" " => "&nbsp;");

			}			

			$options   = mgm_make_combo_options($countries, $value, MGM_KEY_VALUE);						

			$element   ='<select name="mgm_profile_field['. $field['name'] .']" > ' . $options . ' </select>';		

		} 

		else {

			if ($field['type'] == 'text') {

				$element = '<input type="text" name="mgm_profile_field['. $field['name'] .']" value="'. $value .'" '. $ro .' class="text mgm_custom_profile_password"/>';

			} else if ($field['type'] == 'password') {

				continue;

			} else if ($field['type'] == 'textarea') {

				$element = '<textarea name="mgm_profile_field['. $field['name'] .']" cols="40" rows="5" '. $ro .'>'. $value .'</textarea>';

			} else if ($field['type'] == 'checkbox') {				

				$options = preg_split('/[;,]/', $field['options']); 

				//$values  = preg_split('/[;,\s]/', $value);

				$values = @unserialize($value);

				// pass " " as value to prevent the default value getting selected, if no option is selected

				$values = empty($values) ? " " : $values;

				//Issue # 694							

				$element= mgm_make_checkbox_group('mgm_profile_field['. $field['name'] .'][]', $options, $values, MGM_VALUE_ONLY, '', 'div');

			}else if ($field['type'] == 'checkboxg') {				

				$options = preg_split('/[;,]/', $field['options']); 

				if(!is_array($value)){

					$values = @unserialize($value);

				}else {

					$values =$value;

				}

				$values = empty($values) ? " " : $values;						

				$element= mgm_make_checkbox_group('mgm_profile_field['. $field['name'] .'][]', $options, $values, MGM_VALUE_ONLY, '', 'div');

			}else if ($field['type'] == 'radio') {

				$options = preg_split('/[;,]/', $field['options']); 									

				$element = mgm_make_radio_group('mgm_profile_field['. $field['name'] .']', $options, $value, MGM_VALUE_ONLY);

			} else if ($field['type'] == 'select') {

				$element  = '<select name="mgm_profile_field['. $field['name'] .']" '. $ro .'>' ;	

				$options  = preg_split('/[;,]/', $field['options']); 

				if($ro) {	

					$options = (!empty($value)) ? array($value => $value) :array(" " => "&nbsp;"); 

				}				

				$element .= mgm_make_combo_options($options, $value, MGM_VALUE_ONLY);								

				$element .= '</select>';			

			} else if ($field['type'] == 'selectm') {

				$element  = '<select name="mgm_profile_field['. $field['name'] .'][]" '. $ro .' multiple>' ;	

				$options  = preg_split('/[;,]/', $field['options']); 

				if($ro) {	

					$options = (!empty($value)) ? array($value => $value) :array(" " => "&nbsp;"); 

				}				

				$element .= mgm_make_combo_options($options, $value, MGM_VALUE_ONLY);								

				$element .= '</select>';							

			}else if ($field['type'] == 'html') {			

				$element  = '';

				$element .= '<div class="mgm_custom_subs_introduction">'.html_entity_decode(mgm_stripslashes_deep($field['value'])).'</div>';

			} else if ($field['type'] == 'image') {

				$form_fields = new mgm_form_fields();

				$element = $form_fields->get_field_element($field, 'mgm_profile_field',$value);

				if(!in_array($field['name'], $arr_images ))	

					$arr_images[] = $field['name'];

			//issue #1258

			} else if ($field['type'] == 'label') {				

				$form_fields = new mgm_form_fields();

				$element = $form_fields->get_field_element($field, 'mgm_profile_field',$value);

			}		

		}

			

		// set array

		if ($element) {

			$fields[] = array('name'=>$field['name'],'label'=>$field['label'], 'field'=>$element);

		}

	}

		

	// if fields

	if (count($fields)) {

		$html .= '<div class="table width100">';

		foreach ($fields as $i=>$row) {

			$html .= '<div class="row"> 

						<div class="cell th_div width125px mgm_text_align_left">' . mgm_stripslashes_deep($row['label']) . '</div>

						<div class="cell th_div mgm_text_align_left">' . $row['field'] . '</div>

					 </div>';

		}

		// button

		if ($submit_row) {

			$html .= '<div class="row">

						<div class="cell th_div mgm_text_align_right">							

							<input class="button" type="submit" name="submit" value="' . __('Update your profile', 'mgm') . '"/>

							<input type="hidden" name="update_mgm_custom_fields_submit" value="1" />

						</div>

					  </div>';

		}



		$html .= '</div>';

		$html .= mgm_attach_scripts(true,array());

		$yearRange = mgm_get_calendar_year_range();

		//include scripts for image upload:

		if(!empty($arr_images)) {		

			$html .= mgm_upload_script_js('your-profile', $arr_images);

		}

		$html .= '<script language="javascript">jQuery(document).ready(function(){try{mgm_date_picker(".mgm_date",false,{yearRange:"'.$yearRange.'", dateFormat: "'.mgm_get_datepicker_format().'"});}catch(x){}});</script>';

	}

		

	if(!empty($default_readonly)) {

		$html .= '<script language="javascript">';

		$html .= 'jQuery(document).ready(function(){try{';

		$html .= 'jQuery.each('. json_encode($default_readonly) .', function(){jQuery("#"+this).attr("readonly", true)})';	

		$html .= '}catch(x){}})';

		$html .= '</script>';

	}	

	// return	

	if ($return) {

		return $html;

	} else {

		echo $html;

	}

}



/**

 * save custom user fields, should only execute 

 *  when user data is saved from admin user edit screen

 *

 * @param int user id

 * @return bool success

 * @uses "profile_update" hook

 */

function mgm_save_custom_fields($user_id=NULL) {

	// get user id

	if(!$user_id) $user_id = @(int)$_POST['user_id'];

	

	// check profile update in action and skip, profile update ( mgm_user_profile_update() )

	// calls mgm_save_custom_fields via "profile_update" hook, this will help skip

	if(defined('MGM_DOING_USERS_PROFILE_UPDATE') && MGM_DOING_USERS_PROFILE_UPDATE == TRUE) {

	// return

		return $user_id;

	}

	

	// get member & user

	$user = get_userdata($user_id);	

	//check logged in user is super admin:

	$is_admin = (is_super_admin()) ? true : false;

	// member 

	$member = mgm_get_member($user_id);

	

	// default return

	$return = false;

	

	// submit 

	if (isset($_POST['submit'])) {		

		

		// password update

		if ($pass = $_POST['pass1']) {

			$member->user_password = mgm_encrypt_password($pass, $user_id);			

		}	

		

		// get default fields

		$profile_fields = mgm_get_config('default_profile_fields', array());

		// get active profile fileds

		// issue #954

		//$cf_on_profilepage = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_profile'=>true)));

		$cf_profile_pg = mgm_get_class('member_custom_fields');

		$cf_on_profilepage = array();	

		foreach (array_unique($cf_profile_pg->sort_orders) as $id) :

			foreach($cf_profile_pg->custom_fields as $field):

				// issue #954: show the field only if it is enabled for profile page

				if ($field['id'] == $id && ( $field['display']['on_profile'] || $is_admin)):					

					$cf_on_profilepage[]= $field;

				endif;

			endforeach;

		endforeach;

				

		// loop fields

		foreach($cf_on_profilepage as $field){

			// skip html

			if(in_array($field['type'], array('html','label')) || $field['name'] == 'password_conf') continue;//issue#: 206

			// custom

			if(isset($_POST['mgm_profile_field'][$field['name']])){				

				// value as it was posted

				$value = $_POST['mgm_profile_field'][$field['name']];

				// convert to date for birth date

				if($field['name'] == 'birthdate') {

					$value = mgm_format_inputdate_to_mysql($value); 

				}elseif ($field['name'] == 'password') {

				// encode for password

					$value = mgm_encrypt_password($value, $user_id);

				}elseif($field['type'] == 'checkbox' && is_array($value)){

				// join for checkbox with multi value

					//$value = implode(' ', $value);

					//issue #1070

					$value = serialize($value);

				}

			}else if(isset($_POST[$profile_fields[$field['name']]['name']])){	

			// wordpress			

				// value as it was posted

				$value = $_POST[$profile_fields[$field['name']]['name']];

				// convert to date for birth date

				if($field['name'] == 'birthdate') {					

					$value = mgm_format_inputdate_to_mysql($value); 

				}elseif ($field['name'] == 'password') {

				// encode for password

					$value = mgm_encrypt_password($value, $user_id);

				}elseif($field['type'] == 'checkbox' && is_array($value)){

				// join for checkbox with multi value

					//$value = implode(' ', $value);

					//issue #1070

					$value = serialize($value);

				}

			}else{

			// default

				// value as it was posted

				$value = $_POST[$field['name']];

				// convert to date for birth date

				if($field['name'] == 'birthdate') {					

					$value = mgm_format_inputdate_to_mysql($value); 					

				}elseif ($field['name'] == 'password' && !empty($_POST['pass1'])) {

				// encode for password

					$value = mgm_encrypt_password($_POST['pass1'], $user_id);

				}elseif($field['type'] == 'checkbox' && is_array($value)){

				// join for checkbox with multi value

					//$value = implode(' ', $value);

					//issue #1070

					$value = serialize($value);

				}

			}

			// set

			$member->custom_fields->$field['name'] = $value;									

		}

		// update

		$member->save();

		// return as true	

		$return = true;

		//important: the below function is to reinsert the user multiple roles.

		//This is required as the default profile page deletes the unselected roles from user 

		mgm_reset_roles();

	}		

	// mgm_array_dump($user);die;

	// return

	return $return;

}



/**

 * reset user roles 

 */

function mgm_reset_roles() {	

	if ( current_user_can('edit_users') && !IS_PROFILE_PAGE ) {		

		$user_id = 0;

		if(isset($_POST['user_id']) && is_numeric($_POST['user_id']))

			$user_id = $_POST['user_id'];

		elseif (isset($_GET['user_id']) && is_numeric($_GET['user_id']))

			$user_id = strip_tags($_GET['user_id']);

			

		if($user_id > 1 && isset($_POST['role'])) {	

			$user = new WP_User($user_id);							

			if(!empty($user->roles)){

				$member = mgm_get_member($user_id);

				$pack_ids = mgm_get_members_packids($member);

				

				if(!empty($pack_ids)) {

					$role_updated =  0;										

					$obj_role = new mgm_roles();				

					foreach ($pack_ids as $pid) {

						$pack = mgm_get_class('subscription_packs')->get_pack($pid);									

						if(isset($pack['role']) && $pack['role'] != $_POST['role'] ) {							

							$obj_role->add_user_role($user_id, $pack['role']);							

						}

					}

					

					//add selected role:									

					$obj_role->add_user_role($user_id, $_POST['role'], true, false);					

					

				}							

			}			

		}

	}

}



/**

 * custom fields standalone

 *

 * @deprecated

 */

function mgm_edit_custom_field_standalone() {

	$html = '';



	$html .= '	<div class="mgm_custom_fields_standalone">';



	if (isset($_POST['update_mgm_custom_fields_submit'])) {

		if (mgm_save_custom_fields()) {

			$html .= '<div class="mgm_feedback updated fade" id="message">' . __('Your profile has been updated successfully', 'mgm') . '</div>';

		}

	}



	$html .= '		<form method="POST">';



	$html .= mgm_edit_custom_fields(false, true, true);



	$html .= "		</form>

				</div>";



	return $html;

}



/**

 * get custom fields array

 *

 */

function mgm_get_custom_field_array($user_ID) {

	$fld_obj = get_option('mgm_custom_fields');

	$entries = $fld_obj->entries;

	$order = $fld_obj->order;



	$skip_array = array(

	__('Terms and Conditions','mgm')

	, __('Subscription Introduction','mgm')

	, __('Subscription Options','mgm')

	);



	$userfields = get_user_meta($user_ID, 'mgm_custom_fields');



	if (strpos($order, ';') !== false) {

		$orders = explode(';', $order);

	} else {

		$orders = array($order);

	}



	foreach ($orders as $order) {

		foreach ($entries as $entry) {

			if ($order == $entry['id']) {

				if (in_array($entry['name'], $skip_array)) {

					continue;

				} else {

					$return[strtolower(str_replace(' ','_',$entry['name']))] = $userfields[$entry['id']];

				}

			}

		}

	}



	if (isset($return['birthdate']) && $return['birthdate'] != '') {

		$bday_array = explode('-', $return['birthdate']);

		$return['birthdate_unixtime'] = strtotime($bday_array[2] . '-' . $bday_array[0] . '-' . $bday_array[1]);

	}



	return is_array($return)?$return:array();

}



/**

 * no access redirect

 *

 * @param object $system

 * @return void or string

 */

function mgm_no_access_redirect($system_obj) {	

	// url

	$url = $system_obj->get_setting(sprintf('no_access_redirect_%s_users', (is_user_logged_in() ? 'loggedin' : 'loggedout')));

	

	// if url

	if (!empty($url)) {

		if (!headers_sent()) {

			wp_redirect( $url ); exit;

		} else {

			return sprintf('<script language="javascript">document.location="%s";</script>', $url);

		}

	}

	

	// return

	return '';

}



/**

 * checks user has purchased post

 *

 */

function mgm_user_has_purchased_post($post_id, $user_id=NULL, $guest_token=NULL, $check_expired=false) {

	global $wpdb;

	// current_user

	$current_user = wp_get_current_user();

	

	// return if precoessed earlier, unique per post, per session - issue #1421

/*	if( defined('MGM_USER_HAS_PURCHASED_POST_'.$post_id) ){

		// return constant

		return constant('MGM_USER_HAS_PURCHASED_POST_'.$post_id);

	}

*/	

	// default 

	$return = false;



	// skip admin

	if (isset($current_user->caps['administrator']) && $current_user->caps['administrator'] >= 1) {

		$return = true;

	} else {

		// get duration

		$duration = mgm_get_post($post_id)->get_access_duration();

		// sql

		if($user_id){

		// sql	

			$sql = $wpdb->prepare('SELECT `id`,`purchase_dt`,`is_expire`,`is_gift`,`view_count` FROM `' . TBL_MGM_POST_PURCHASES . '` WHERE

								   post_id = %d AND user_id = %d ORDER BY `purchase_dt` DESC LIMIT 1', $post_id, $user_id );

		}else{

			// sanitize

			$guest_token = sanitize_title_for_query($guest_token);

			// sql

			$sql = $wpdb->prepare('SELECT `id`,`purchase_dt`,`is_expire`,`is_gift`,`view_count` FROM `' . TBL_MGM_POST_PURCHASES . '` WHERE

								   post_id = %d AND guest_token = %s ORDER BY `purchase_dt` DESC LIMIT 1', $post_id, $guest_token );

		}		

		// get 

		$purchased = $wpdb->get_row($sql);

		

		//echo $wpdb->last_query;



		// date is set		

		if (isset($purchased) && $purchase_dt = $purchased->purchase_dt) {			

			// duration is indefinite or gift with no expire

			if ((int)$duration == 0 || ($purchased->is_gift == 'Y' && $purchased->is_expire == 'N')) {

				$return = true;

			// check limited duration access, duration in days	

			} else if (strtotime($purchase_dt) + ($duration*86400) > time()) {

				$return = true;

			}			

			// check expired

			if($check_expired){				

				$return = (strtotime($purchase_dt) + ($duration*86400) < time());			

			}

		}

		

		// check view limit

		if($return === true){

			// get post

			$post_obj = mgm_get_post($post_id);

			// access_view_limit

			$access_view_limit = $post_obj->get_access_view_limit();

			// check not 0

			if($access_view_limit > 0 ){

				// check 

				if(!is_null($purchased->view_count) && (int)$purchased->view_count >= $access_view_limit){

					$return = false;

				}

			}

		}

		

		// update view count

		if($return === true){

		// update view count			

			// update

			$wpdb->query("UPDATE `" . TBL_MGM_POST_PURCHASES . "` SET `view_count` = IF(ISNULL(`view_count`), 1, `view_count`+1) WHERE `id`='{$purchased->id}'");

		}

	}

	

	// set constant to process once, wp repeat plugin call bypass - issue #1421

	//define('MGM_USER_HAS_PURCHASED_POST_'.$post_id, $return);

	

	// return	

	return $return;

}



/**

 * check purchased post expired

 */

function mgm_is_user_purchased_post_expired($post_id, $user_id=NULL, $guest_token=NULL){

 	// return 

	return mgm_user_has_purchased_post($post_id, $user_id, $guest_token, true);

}

 

/**

 * get postpack post in csv string

 *

 */

function mgm_get_postpack_posts_csv($pack_id) {

    $array = array();

    if ($posts = mgm_get_postpack_posts($pack_id)) {

        foreach ($posts as $i=>$post) {

            $array[] = $post->post_id;

        }

    }

    // implode

    return implode(',', $array);

}



/**

 * get packpack data

 *

 */

function mgm_get_postpack($pack_id = false) {

    global $wpdb;

    

    $postpack = new stdClass();

    $postpack->id = $postpack->name = $postpack->cost = $postpack->description = $postpack->product = $postpack->modules = $postpack->create_dt  = false;

    // set

    if ($pack_id) {

        $sql = $wpdb->prepare('SELECT id, name, cost, description, product, modules, create_dt FROM `' . TBL_MGM_POST_PACK . '` WHERE id = %d', $pack_id );

        $postpack = $wpdb->get_row($sql);

    }

    // return

    return $postpack;

}



/**

 * get packpack posts

 *

 */

function mgm_get_postpack_posts($pack_id = false, $count = false) {

    global $wpdb;

    // when set

    if ($pack_id) {

        $sql    = $wpdb->prepare('SELECT id, pack_id, post_id, create_dt  FROM `' . TBL_MGM_POST_PACK_POST_ASSOC . '`  WHERE pack_id =%d', $pack_id);

        $return = $wpdb->get_results($sql);

		// count / objects

		return ($count) ? count($return) : $return;

    }else{

	// error	

		$return = new stdClass();

    	$return->id = $return->pack_id = $return->post_id = $return->create_dt = false;

		// count / object

		return ($count) ? 0 : $return;

	}

}



/**

 * validate coupon

 *

 * @param string $code

 * @param decimal $cost

 * @return array

 */

function mgm_validate_coupon($code, $cost=NULL){

	// get coupon	

	$code = trim($code);

	// check

	if(!empty($code)) {

		// if found

		if($coupon = mgm_get_coupon_data($code)){

			// init

			$new_coupon = $coupon;

			



			// what type of coupon is it %, scalar, sub_id

			$type = mgm_get_coupon_type($coupon['value']);	

			// double check we still have content

			if($type)  {

				// check on type

				switch($type){

					case 'percent':

						// string % with number, issue #135, accept period for fraction value

						$values = mgm_get_coupon_values('percent', $coupon['value']);

						// percent

						$percent = $values['value'] / 100;

						// new cost

						if($cost){

						// calc

							$cost = $cost * (1 - $percent); 

							// zero cost 

							if($cost < 0) $cost = 0;						

							// set

							$new_coupon['cost'] = $cost;

						}		

					break;

					case 'sub_pack':

						// sub_pack#Price_Duration-Unit_Duration-Type_Membership-Type_Billing-Cycle

						$values = mgm_get_coupon_values('sub_pack', $coupon['value']);

						// set

						$new_coupon['cost']            = $values['new_cost'];	

						$new_coupon['duration']        = $values['new_duration'];

						$new_coupon['duration_type']   = strtolower($values['new_duration_type']);

						$new_coupon['membership_type'] = strtolower(str_replace('-','_',$values['new_membership_type']));

						// billing cycle:

						if(isset($values['new_num_cycles']) && is_numeric($values['new_num_cycles'])){

							$new_coupon['num_cycles']  = $values['new_num_cycles'];

						}	

					break;				

					case 'sub_pack_trial':

						// subs_pack_trial#Trial-Duration-Unit_Trial-Duration-Type_Trial-Price_Trial-Occurrences

						// subs_pack_trial#Trial-Price_Trial-Duration-Unit_Trial-Duration-Type_Trial-Occurrences

						$values = mgm_get_coupon_values('sub_pack_trial', $coupon['value']);										

						// set

						$new_coupon['trial_on']            = 1;

						$new_coupon['trial_cost']          = $values['new_cost'];	

						$new_coupon['trial_duration']      = $values['new_duration'] * $values['new_num_cycles'];

						$new_coupon['trial_duration_type'] = strtolower($values['new_duration_type']);

						$new_coupon['trial_num_cycles']    = $values['new_num_cycles'];

					break;

					case 'scalar':

					case 'flat':

					default:

						// issue #135, accept period for fraction value

						$values = mgm_get_coupon_values('flat', $coupon['value']);

						// cost

						if($cost){

						// calc

							$cost = $cost - $values['value']; 

							// zero cost

							if($cost < 0) $cost = 0;						

							// set

							$new_coupon['cost'] = $cost;	

						}

					break;

				}

			}			

			// format cost

			if(isset($new_coupon['cost']) && is_numeric($new_coupon['cost'])){

				// has ',' separator

				if(strpos($new_coupon['cost'], ',') === false){

					$new_coupon['cost'] = number_format($new_coupon['cost'], 2, '.','');

				} else {

					$new_coupon['cost'] = number_format($new_coupon['cost'], 2, ',','');

				}

			}	

			// return array

			return $new_coupon;

		}

	}

	

	// error

	return false;

}



/**

 * get coupon data 

 *

 */

function mgm_get_coupon_data($coupon_name){

	global $wpdb;

	

	// sql

	$sql = $wpdb->prepare("SELECT id,name,value,use_limit,used_count,expire_dt,description,product,exclude  FROM `" . TBL_MGM_COUPON . "` WHERE name = %s", $coupon_name);	

	// get

	$coupon = $wpdb->get_row($sql);	

	//  check

	if($coupon){		

		// limit validate

		if(!is_null($coupon->use_limit)){

			if((int)$coupon->used_count >= (int)$coupon->use_limit){

				return false;

			}

		}

		// expire validate

		if(!is_null($coupon->expire_dt)){

			if(time() > strtotime($coupon->expire_dt)){

				return false;

			}

		}

		

		// unset unworthy data

		unset($coupon->expire_dt,$coupon->use_limit,$coupon->used_count);

		// set

		$coupon = (array)$coupon;		

		// decode product

		$coupon['product'] = json_decode($coupon['product'], true);

		// return

		return $coupon;

	}

	// return	

	return false;

}



/**

 * get coupon type 

 *

 */

function mgm_get_coupon_type($coupon_value) {

	// type of coupon

	if(strpos($coupon_value, '%') !== false){

		// Percentage

		$return = 'percent';

	}else if(preg_match('/^sub_pack#/i', $coupon_value)){

		// subscription pack

		$return = 'sub_pack';

	}else if(preg_match('/^sub_pack_trial#/i', $coupon_value)){

		// trial subscription pack

		$return = 'sub_pack_trial';

	}else {

		// flat

		$return = 'flat';

	}

	//return 

	return $return;

}



/**

 * parse and return coupon values

 *

 * @since 2.6

 */

function mgm_get_coupon_values($type=NULL, $value, $format=false){

	// get type from value

	if(!$type) $type = mgm_get_coupon_type($value);

	// init values

	$values = array();

	// check on type

	switch($type){

		case 'sub_pack':

			// split

			$value = preg_replace('/[^A-Za-z0-9_\.-]/', '', str_replace('sub_pack#','',$value));

			// check

			$args = explode('_', $value);

			// split

			if(count($args) > 0){

				// anticipated vars

				$vars = array('new_cost','new_duration', 'new_duration_type', 'new_membership_type', 'new_num_cycles');

				// incrementer

				$i=0;

				// loop

				foreach($vars as $var){

					// args set

					if(isset($args[$i])){

						$values[$var] = $args[$i];

					}

					// increment

					$i++;	

				}						

			}

		break;

		case 'sub_pack_trial':

			// value

			$value = preg_replace('/[^A-Za-z0-9_\.-]/', '', str_replace('sub_pack_trial#','',$value));

			// check

			$args = explode('_', $value);

			// split

			if(count($args) > 0){

				// anticipated vars

				$vars = array('new_cost','new_duration', 'new_duration_type', 'new_num_cycles');

				// incrementer

				$i=0;

				// loop

				foreach($vars as $var){

					// args set

					if(isset($args[$i])){

						$values[$var] = $args[$i];

					}

					// increment

					$i++;	

				}						

			}

			// value

		break;

		case 'percent':						

		case 'scalar':

		case 'flat':

		default:

			// regx

			$regx = (strpos($value, ',') === false) ? '/[^0-9.]/' : '/[^0-9,]/';

			// replace and set

			$values['value'] = preg_replace($regx, '', $value);				

		break;

	}	

	// format some

	if($format){

		// new_duration_type

		if(isset($values['new_duration_type'])){

			$values['new_duration_type'] = strtolower($values['new_duration_type']);

		}

		// new_membership_type

		if(isset($values['new_membership_type'])){

			$values['new_membership_type'] = strtolower(str_replace('-', '_', $values['new_membership_type']));

		}

	}

	

	// return 

	return $values;

}



/**

 * check is a spider/bot 

 *

 */

function mgm_is_a_bot() {

	$spiders = array('googlebot','google','msnbot','ia_archiver','lycos','jeeves','scooter','fast-webcrawler','slurp@inktomi',

	                 'turnitinbot','technorati','yahoo','findexa','findlinks','gaisbo','zyborg','surveybot','bloglines','blogsearch',

					 'pubsub','syndic8','userland','gigabot','become.com');



	$useragent = mgm_server_var('HTTP_USER_AGENT');



	if (empty($useragent)) {

		return false;

	}



	// Check For Bot

	foreach ($spiders as $spider) {

		if (stristr($useragent, $spider) !== false) {

			return true;

		} else {

			return false;

		}

	}

}



/**

 * check content protection enabled 

 *

 */

function mgm_protect_content($content_protection=NULL) {

	// if not passed

	if(!$content_protection) $content_protection = mgm_get_class('system')->get_setting('content_protection');

	// is protection on 

	return ( $content_protection != 'none' ) ? true : false;	

}



/**

 * http query

 *

 * use _http_build_query() from WP

 */

function mgm_http_build_query($data, $encode=true, $join='&'){	

	// query

	$_query = '';

	foreach($data as $key => $value) {

		if (is_array($value)) {

			foreach($value as $item) {

				if (strlen($_query) > 0) $_query .= $join;

				$_query .= ($encode==true) ?  ("$key=".urlencode($item)) : ("$key=$value");

			}

		} else {

			if (strlen($_query) > 0) $_query .= $join;						

			$_query .= ($encode==true) ? ("$key=".urlencode($value)) : ("$key=$value");

				

		}

	}

	// return

	return $_query;

}



/**

 * get currencies list

 *

 */

function mgm_get_currencies(){

	// currencies

	$currencies = array('AED' => sprintf(__('%s - Arab Emirates Dirham','mgm'), 'AED'),

		'AUD' => sprintf(__('%s - Australian Dollar','mgm'), 'AUD'),'BRL' => sprintf(__('%s - Brazilian Real','mgm'), 'BRL'),

		'CAD' => sprintf(__('%s - Canadian Dollar','mgm'), 'CAD'),'CHF' => sprintf(__('%s - Swiss Franc','mgm'), 'CHF'),

		'CZK' => sprintf(__('%s - Czech Koruna','mgm'), 'CZK'),	'DKK' => sprintf(__('%s - Danish Krone','mgm'), 'DKK'),

		'EUR' => sprintf(__('%s - Euro','mgm'), 'EUR'),	'GBP' => sprintf(__('%s - Pound Sterling','mgm'), 'GBP'),

		'HKD' => sprintf(__('%s - Hong Kong Dollar','mgm'), 'HKD'),	'HUF' => sprintf(__('%s - Hungarian Forint','mgm'), 'HUF'),

		'ILS' => sprintf(__('%s - Israeli New Sheqel','mgm'), 'ILS'),'INR' => sprintf(__('%s - Indian Rupee','mgm'), 'INR'),

		'IDR' => sprintf(__('%s - Indonesian Rupiah','mgm'), 'IDR'),'JPY' => sprintf(__('%s - Japanese Yen','mgm'), 'JPY'),

		'KRW' => sprintf(__('%s - South Korea Won','mgm'), 'KRW'),'LTL' => sprintf(__('%s - Lithuanian Litas','mgm'), 'LTL'),

		'MXN' => sprintf(__('%s - New Mexican Peso','mgm'), 'MXR'),'MYR' => sprintf(__('%s - Malaysian Ringgit','mgm'), 'MYR'),

		'NOK' => sprintf(__('%s - Norwegian Krone','mgm'), 'NOK'),'NZD' => sprintf(__('%s - New Zealand Dollar','mgm'), 'NZD'),

		'NGN' => sprintf(__('%s - Nigeria Naira','mgm'), 'NGN'),'PHP' => sprintf(__('%s - Philippine Peso','mgm'), 'PHP'),

		'PLN' => sprintf(__('%s - Polish Zloty','mgm'), 'PLN'),'SEK' => sprintf(__('%s - Swedish Krona','mgm'), 'SEK'),

		'SGD' => sprintf(__('%s - Singapore Dollar','mgm'), 'SGD'),	'THB' => sprintf(__('%s - Thai Baht','mgm'), 'THB'),

		'TRY' => sprintf(__('%s - Turkish Lira','mgm'), 'TRY'),	'TWD' => sprintf(__('%s - New Taiwanese Dollar','mgm'), 'TWD'),

		'USD' => sprintf(__('%s - U.S. Dollar','mgm'), 'USD'),'ZAR' => sprintf(__('%s - South African Rand','mgm'), 'ZAR'));			

	// return	

	return apply_filters('mgm_get_currency', $currencies);

}



/**

 * get html symbol for given currency (issue #800 )

 *

 * @param string $code

 * @return string $html 

 */

function mgm_get_currency_symbols($code=NULL){

	// symbols

	$symbols = array('AED' => 'د.إ','AUD' => '&#36;','BRL' => 'R$','CAD' => '&#36;','CHF' => '&#8355;','CZK' => 'Kč','DKK' => 'kr','EUR' => '&#128;',

		'GBP' => '&#163;','HKD' => '&#36;','HUF' => 'Ft','ILS' => '₪','INR' => '&#8377;','IDR' => 'Rp','JPY' => '&#165;','KRW' => '₩','LTL' => 'Lt',

		'MXN' => '&#36;','MYR' => 'RM',	'NOK' => 'kr',	'NZD' => '&#36;','NGN' => '&#x20a6;','PHP' => '&#8369;','PLN' => 'zł','SEK' => 'kr','SGD' => '&#36;',

		'THB' => '&#3647;','TRY' => '&#36;','TWD' => 'NT$','USD' => '&#36;','ZAR' => 'R');	



	

	// filter

	$symbols = apply_filters('mgm_get_currency_symbol', $symbols);

	

	// return					

	return (isset($symbols[$code])) ? $symbols[$code] : $code;

}



/**

 * get currencies iso 4217 codes list

 *

 */

function mgm_get_currency_iso4217($code=NULL){

	// currencies

	$currencies = array('AED' => 784,'AUD' => 036,'BRL' => 986,'CAD' => 124,'CHF' => 756,'CZK' => 203,'DKK' => 208, 'EUR' => 978, 

		'GBP' => 826,'HKD' => 344,'HUF' => 348,'ILS' => 376,'INR' => 356,'IDR' => 360,'JPY' => 392,'KRW' => 410,'LTL' => 440,

		'MXN' => 484,'MYR' => 458,'NOK' => 578,'NZD' => 554,'NGN' => 566,'PHP' => 608,'PLN' => 985,'SEK' => 752,'SGD' => 702,

		'THB' => 764,'TRY' => 949,'TWD' => 901,'USD' => 840,'ZAR' => 710);

	

	// filter

	$currencies = apply_filters('mgm_get_currency_iso4217', $currencies);

						

	// return

	return (isset($currencies[$code])) ? $currencies[$code] : $code;

}



/**

 * get locales list

 *

 */

function mgm_get_locales(){

	// locales

	$locales = array('AU' => __('Australia','mgm'),	'AT' => __('Austria','mgm'),'BE' => __('Belgium','mgm'),'BR' => __('Brazil','mgm'),

		'CA' => __('Canada','mgm'),	'CN' => __('China','mgm'),'FR' => __('France','mgm'),'DE' => __('Germany','mgm'),'IT' => __('Italy','mgm'),

		'NL' => __('Netherlands','mgm'),'PL' => __('Poland','mgm'),'ES' => __('Spain','mgm'),'CH' => __('Switzerland','mgm'),

		'GB' => __('United Kingdom','mgm'),'US' => __('United States','mgm'),'CZ' => __('Czech Republic','mgm'),'DK' => __('Denmark','mgm'),

		'FI' => __('Finland','mgm'),'GF' => __('French Guiana','mgm'),'GR' => __('Greece','mgm'),	'GP' => __('Guadeloupe','mgm'),	

		'HU' => __('Hungary','mgm'),'IN' => __('India','mgm'),'ID' => __('Indonesia','mgm'),'IE' => __('Ireland','mgm'),'IL' => __('Israel','mgm'),

		'LU' => __('Luxembourg','mgm'),'MY' => __('Malaysia','mgm'),	'MQ' => __('Martinique','mgm'),'NZ' => __('New Zealand','mgm'),

		'NO' => __('Norway','mgm'),'PT' => __('Portugal','mgm'),'RE' => __('Reunion','mgm'),'SK' => __('Slovakia','mgm'),'KR' => __('South Korea','mgm'),

		'SE' => __('Sweden','mgm'),'TW' => __('Taiwan','mgm'),'TH' => __('Thailand','mgm'),	'TR' => __('Turkey','mgm'),'CL' => __('Chile','mgm'),

		'EC' => __('Ecuador','mgm'),'JM' => __('Jamaica','mgm'),'UY' => __('Uruguay','mgm'),'BM' => __('Bermuda','mgm'),'BG' => __('Bulgaria','mgm'),

		'KY' => __('Cayman Islands','mgm'),'CR' => __('Costa Rica','mgm'),	'CY' => __('Cyprus','mgm'),'DO' => __('Dominican Republic','mgm'),

		'SV' => __('El Salvador','mgm'),'EE' => __('Estonia','mgm'),'GI' => __('Gibraltar','mgm'),'GT' => __('Guatemala','mgm'),'IS' => __('Iceland','mgm'),

		'KE' => __('Kenya','mgm'),'KW' => __('Kuwait','mgm'),'LV' => __('Latvia','mgm'),'LI' => __('Liechtenstein','mgm'),'LT' => __('Lithuania','mgm'),

		'MT' => __('Malta','mgm'),'PA' => __('Panama','mgm'),'PE' => __('Peru','mgm'),'QA' => __('Qatar','mgm'),'RO' => __('Romania','mgm'),

		'SM' => __('San Marino','mgm'),'SI' => __('Slovenia','mgm'),'ZA' => __('South Africa','mgm'),'AE' => __('United Arab Emirates','mgm'),

		'VE' => __('Venezuela','mgm'),'VN' => __('Vietnam','mgm'),'MX' => __('Mexico','mgm'),'ja_JP' => __('Japan','mgm'));

	

	// sort

	asort($locales);

	

	// return	

	return apply_filters('mgm_get_locale', $locales);				

}



/**

 * get languages list

 *

 */

function mgm_get_languages(){

	// languages

	$languages = array( 'EN' => __('English','mgm'), 'DE' => __('German','mgm'), 'FR' => __('French','mgm'), 'ES' => __('Spanish','mgm'), 

		'IT' => __('Italian','mgm'),'PL' => __('Polish','mgm'), 'GR' => __('Greek','mgm'), 'RO' => __('Romanian','mgm'), 'RU' => __('Russian','mgm'), 

		'TR' => __('Turkish','mgm'),	'CN' => __('Chinese','mgm'), 'CZ' => __('Czech','mgm'));

	

	// return 

	return apply_filters('mgm_get_language', $languages);				

}



/**

 * convert to currency

 *

 */

function mgm_convert_to_currency($num) {

	// has no fraction

    if (strpos($num, '.') == false) {

        $num = $num . '.00';

    } else {

        $num = sprintf("%01.2f", (float)$num);

    }

	// return

    return $num;

}



/**

 * convert to decimal

 *

 */

function mgm_convert_to_decimal($num) {

	// fraction

    if (strpos($num, '.') !== false) {

        $num = (float)$num * 100;

    }

	// return

    return $num;

}



/**

 * convert to cent

 *

 */

function mgm_convert_to_cents($num) {

	// fraction    

    return (float)$num * 100;    

}



/** 

 * get words from content

 *

 * @param string content to process

 * @param int word limit to allow

 * @param bool allow html or text

 * @param int start from

 * @return string allowed words

 */

function mgm_words_from_content($content, $word_limit, $allow_html=true, $start=0){	

	// html

	if($allow_html){

		return mgm_html_words_from_content($content, $word_limit, $start);

	}else{

		return mgm_text_words_from_content($content, $word_limit, $start);	

	}				

}



/** 

 * get text words from content

 *

 * @param string content to process

 * @param int word limit to allow

 * @param int start from

 * @return string allowed words

 */

function mgm_text_words_from_content($content, $word_limit, $start=0){

	// strip all html

	$content = strip_tags($content);

	// split by space

	$words = preg_split('/\s+/', $content);

	// init

	$_words = array();

	// capture

	foreach($words as $word){

		// remove space

		$word = trim($word);

		// skip no value

		if(empty($word)) continue;

		// skip html 

		if(preg_match('/<(.*)>(.*)<\/(.*)>/', $word)) continue;		

		// add

		$_words[] = $word; 		

	}

	// return 

	return implode(' ', array_slice( $_words, $start, $word_limit ) );

}



/** 

 * get html words from content

 *

 * @param string content to process

 * @param int word limit to allow

 * @param int start from

 * @return string allowed words

 */

function mgm_html_words_from_content($content, $word_limit, $start=0){

	// init	

	$_words = array();

	$word_count = 0;	

	// create tokens

	preg_match_all('/(<[^>]+>|[^<>\s]+)\s*/u', $content, $matches);

	// tokonize

	$words = $matches[0];

	// loop

	foreach ((array)$words as $word){ 

		// limit reached

		if ($word_count >= $word_limit){ 

			break;

		}

		

		// match

		if (substr($word,0,1) != '<'){ // not a tag

			// limit reached

			if ($word_count >= $word_limit && preg_match('/[\?\.\!]\s*$/uS', $word) == 1){ 

				// set

				$_words[] = trim($word);

				break;

			}

			// word count

			$word_count++;	

		}

		// rest as it is

	   $_words[] = $word;

	}

	// return 

	return trim(force_balance_tags(implode('',$_words)));

}



/**

 * close if any broken htmls tags exist

 *

 */

function mgm_close_open_tags($html, $ignore=array('img', 'hr', 'br')) {    

	if (preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU", $html, $opentags)) {    	

	    $opentags[1] = array_diff($opentags[1], $ignore);

	    $opentags[1] = array_values($opentags[1]);

	    preg_match_all("#</([a-z]+)>#iU", $html, $closetags);

	    $opened = count($opentags[1]);

	    if (count($closetags[1]) == $opened) return $html;

	    $opentags[1] = array_reverse($opentags[1]);

	    for ($i=0;$i<$opened;$i++) {

	        if (!in_array($opentags[1][$i], $closetags)) $html .= '</'.$opentags[1][$i].'>';

	        else unset($closetags[array_search($opentags[1][$i], $closetags)]);

	    }

	}

	return $html;

}



/**

 * deep stripslashes

 *

 */

function mgm_stripslashes_deep_once($data){	

	// clean till found '\'

	do{

		$data = stripslashes($data);

	}while(strpos($data, '\\') !==false);	

	// return

	return $data;

}



/**

 * deep stripslashes recursive

 *

 */

function mgm_stripslashes_deep($value) {

	if ( is_array($value) ) {

		$value = array_map('mgm_stripslashes_deep', $value);

	} elseif ( is_object($value) ) {

		$vars = get_object_vars( $value );

		foreach ($vars as $key=>$data) {

			$value->{$key} = mgm_stripslashes_deep( $data );

		}

	} else {		

		// clean till found '\'

		do{

			$value = stripslashes($value);

		}while(strpos($value, '\\') !== false);

	}

	// return	

	return $value;

}



/**

 * deep recursive array merge

 *

 */

function mgm_array_merge_recursive_unique($array0, $array1)	{

	$arrays = func_get_args();

	$remains = $arrays;

	// We walk through each arrays and put value in the results (without

	// considering previous value).

	$result = array();

	// loop available array

	foreach($arrays as $array) {

		// The first remaining array is $array. We are processing it. So

		// we remove it from remaing arrays.

		array_shift($remains);

		// We don't care non array param, like array_merge since PHP 5.0.

		if(is_array($array)) {

			// Loop values

			foreach($array as $key => $value) {

				if(is_array($value)) {

					// we gather all remaining arrays that have such key available

					$args = array();

					foreach($remains as $remain) {

						if(array_key_exists($key, $remain)) {

							array_push($args, $remain[$key]);

						}

					}

					if(count($args) > 2) {

						// put the recursion

						$result[$key] = call_user_func_array(__FUNCTION__, $args);

					} else {

						foreach($value as $vkey => $vval) {

							$result[$key][$vkey] = $vval;

						}

					}

				} else {

					// simply put the value

					$result[$key] = $value;

				}

			}

		}

	}

	return $result;

}



/**

 * get jquery ui for wp version

 * 

 * @param void

 * @return string

 * @since 1.8.32

 */

function mgm_get_jqueryui_on_wp_version(){

	// array

	$versions = array('3.6' => '1.10.3', '3.5' => '1.9.2', '3.0' => '1.8.16', '2.9' => '1.7.3');

	// loop

	foreach( $versions as $wp_ver => $jqui_ver){

		// greater or equal

		// if ( version_compare( get_bloginfo('version'), $wp_ver, '>=' ) ){

		if ( mgm_compare_wp_version( $wp_ver, '>=' ) ){

			// set

			$jqueryui_version = $jqui_ver;									

			break;

		}	

	}



	// default

	if( ! isset($jqueryui_version) ) $jqueryui_version = '1.7.2';



	// return

	return $jqueryui_version;

}



/**

 * get jquery ui version

 *

 * @param void

 * @return string

 * @since 1.5.0

 */

function mgm_get_jqueryui_version(){

	// check db

	if( ! $jqueryui_version = get_option('mgm_jqueryui_version') ){// not defined, use as coded	

		// calc on wp version

		$jqueryui_version = mgm_get_jqueryui_on_wp_version();

		// save 								

		update_option('mgm_jqueryui_version', $jqueryui_version); // and update	

	}else{

		// calc on wp version

		$jqueryui_version = mgm_get_jqueryui_on_wp_version();

		// check latest not used

		if( get_option('mgm_jqueryui_version') != $jqueryui_version ){

			// save 								

			update_option('mgm_jqueryui_version', $jqueryui_version); // and update	

		}

	}	

	

	// return

	return $jqueryui_version;

}



/**

 * trim text

 *

 */

function mgm_trim($string, $trim_chars = " \t\n\r\0\x0B"){

	// return

 	return str_replace(str_split($trim_chars), '', $string);

}



/**

 * get include file content

 *

 */

function mgm_get_include($template, $data=false){

	// data

	if(is_array($data)) extract($data);

	// buffer start

	@ob_start();

	// include	

	@include($template);

	// return

	return @ob_get_clean();		

}



/**

 * get include file content

 *

 */

function mgm_get_action_hook_output($action_hook){	

	// start

	@ob_start();

	// run

	do_action($action_hook);

	// return

	return @ob_get_clean();		

}

/**

 * purchase options

 * 

 * @param string $text

 * @param string $login_url

 * @param int $post_id 

 * @param int $postpack_id 

 * @return string $text

 */

function mgm_get_content_purchase_options($text, $login_url, $post_id=NULL, $postpack_id=NULL){

	// global $post;	

	//[purchase_options] tag

	if(preg_match('/[purchase_options]/',$text)){	

		// system

		$system_obj = mgm_get_class('system');

		// guest purchase

		$guest_purchase = bool_from_yn( mgm_get_setting('enable_guest_content_purchase') );		

		// guest purchase

		if ($guest_purchase) {

			// init

			$purchase_options = array();

			// links

			$purchase_options_links = mgm_get_setting('guest_content_purchase_options_links');

			// check

			if(!is_array($purchase_options_links)) $purchase_options_links = array();

			// post or postpack - issue #1396

			$post_or_postpack = ($postpack_id) ? array('postpack_id'=>$postpack_id,'postpack_post_id'=>$post_id) : array('post_id'=>$post_id) ;

			// register url

			$register_url = mgm_get_custom_url('register', false, array_merge(array('show_purchase_options'=>true), $post_or_postpack));

			// purchse_url

			$purchase_url = mgm_get_custom_url('purchase_content', false, array_merge(array('method'=>'guest_purchase'), $post_or_postpack));

			// check

			if (in_array('register_purchase', $purchase_options_links)){

				$purchase_options[] = sprintf('<a href="%s">%s</a>', $register_url, __('Register & Purchase','mgm'));

			}

			// check 					  

			if (in_array('purchase_only', $purchase_options_links)){

				$purchase_options[] = sprintf('<a href="%s">%s</a>', $purchase_url, __('Purchase Only','mgm'));

			}

			// check

			if (in_array('login_purchase', $purchase_options_links)){					  

				$purchase_options[] = sprintf('<a href="%s">%s</a>', $login_url, __('Login & Purchase','mgm'));

			}			

			// replace 

			$text = str_replace('[purchase_options]', implode('&nbsp;&nbsp;', $purchase_options), $text);

		} else{

		// default

			$text = str_replace('[purchase_options]', sprintf('<a href="%s">%s</a>', $login_url, __('Login & Purchase','mgm')), $text);

		}		

	}	

	// return 

	return $text;	

}



/**

 * private text tags replacement

 * 

 * @param string text

 * @return string text

 */

function mgm_private_text_tags($text){		

	global $post;		

	// login url

	$login_url = mgm_get_custom_url('login', false, array('redirect_to' => get_permalink($post->ID)));

	// [login] tag

	if(preg_match('/[login]/',$text)){		

		// login text

		$login_text = (is_object($post)) ? sprintf(' <a href="%s"><b>%s</b></a>', $login_url, __('Login','mgm')) : '';	

		// replace

		$text = str_replace('[login]', $login_text, $text);	

	}			

	// purchase options	

	return $text = mgm_get_content_purchase_options($text, $login_url, $post->ID);			

}



/**

 * get the default content from templte file

 *

 * @param string name/code

 * @param array data place holders

 * @param sting type

 * @retrun string template 

 */

function mgm_get_template_default($name, $data=array(), $type= 'messages', $update=false){

	// init

	$content = '';

	// template file

	$template_file = MGM_CORE_DIR . MGM_DS . 'html' . MGM_DS . $type . MGM_DS . $name . '.html';

	// get content

	if(file_exists($template_file)){

		// insert

		if($content = file_get_contents($template_file)){

			// strp first

			$content = mgm_stripslashes_deep($content);

			// and update database

			if($update){

				// check if activated, tables are not available before activation

				if(mgm_is_activated()){

					global $wpdb;

					$wpdb->insert(TBL_MGM_TEMPLATE, array('name'=>$name,'type'=>$type,'content'=>addslashes($content),'create_dt'=>date('Y-m-d H:i:s')));

				}	

			}	

		}

	}	

	// return 

	return $content;

}



/**

 * get template

 *

 */

function mgm_get_template($name, $data=array(), $type= 'messages'){

	global $wpdb;

	// check from db first

	if(mgm_is_activated()){

		$content = $wpdb->get_var($wpdb->prepare("SELECT `content` FROM `".TBL_MGM_TEMPLATE."` WHERE `name`=%s AND `type`=%s", $name, $type ));

	}

	

	// not in db

	if(!isset($content) || (isset($content) && empty($content))){	

		// check old content

		$content = mgm_get_old_template_content($name);			

		// stil empty, take from file

		if(empty($content)){

			// template file

			$template_file = MGM_CORE_DIR . MGM_DS . 'html' . MGM_DS . $type . MGM_DS . $name . '.html';

			// get content

			if(file_exists($template_file)){

				$content = @file_get_contents($template_file);

			}

		}

		// insert

		if($content){

			// strp first

			$content = mgm_stripslashes_deep($content);

			// and update database

			if(mgm_is_activated()){

				$wpdb->insert(TBL_MGM_TEMPLATE, array('name'=>$name,'type'=>$type,'content'=>addslashes($content),'create_dt'=>date('Y-m-d H:i:s')));

			}	

		}	

	}	

		

	// check template parser

	if($content){		

		//patch to update old users message:

		$content = mgm_replace_oldlinks_with_tag($content, $name);

		// check

		if(is_array($data)){

			foreach($data as $key=>$value){

				$content = str_replace('['.$key.']', $value, $content);

			}

		}	

		// return

		return mgm_stripslashes_deep($content);

	}		

	// return

	return '';

}



/**

 * patch for old messages

 *

 */

function mgm_replace_oldlinks_with_tag($content, $name) {

	//echo $content;

	//die;

	// check

	switch ($name) {

		case 'login_errmsg_null':

		case 'login_errmsg_expired':

		case 'login_errmsg_trial_expired':	

			$oldlink = add_query_arg(array('action' => 'upgrade', 'username'=>'[[USERNAME]]'), mgm_home_url('purchase_subscription'));

			$content = str_replace($oldlink, '[subscription_url]', $content);

			break;

		case 'payment_success_message':		//double link issue	

			if(!preg_match("/\[login_url\]/", $content)) {

				$pos_profile_string = strrpos($content, __('Your Profile','mgm').'</a>');

				$pos_href = strrpos(substr($content, 0, $pos_profile_string), 'href=' );

				$needle = mgm_get_custom_url('login');	

				$prev_link = substr($content, $pos_href, $pos_profile_string);									

				if(strstr($prev_link, $needle) || strstr($prev_link, '/wp-login.php')) {					

					$link = "href=\"{$needle}\">";

					$content = substr($content, 0, $pos_href) . $link . substr($content, $pos_profile_string, strlen($content) );

				}				

			}

		case 'payment_failed_message':	//double link issue			

			if(!preg_match("/\[register_url\]/", $content)) {

				$pos_register_string = strrpos($content, 'Register</a>');

				$pos_href = strrpos(substr($content, 0, $pos_register_string), 'href=');

				$needle = mgm_get_custom_url('register');

				$prev_link = substr($content, $pos_href, $pos_register_string);

				if(strstr($prev_link, $needle) || strstr($prev_link, '/subscribe/?method=register') || strstr($prev_link, '/wp-login.php?action=register') ) {					

					$link = "href=\"{$needle}\">";

					$content = substr($content, 0, $pos_href) . $link . substr($content, $pos_register_string, strlen($content) );

				}

			}

			break;				

	}

	// return

	return $content;

}



/**

 * update template

 *

 */

function mgm_update_template($name, $content, $type= 'messages'){

	global $wpdb;

	// strp first

	$content = mgm_stripslashes_deep($content);

	// init

	$success = false;

	// save to db

	if(mgm_is_activated()){

		$success = $wpdb->update(TBL_MGM_TEMPLATE, array('content'=>addslashes($content)), array('name'=>$name,'type'=>$type));	

	}

	// return 

	return ($success === FALSE) ? false : true; 

}



/**

 * get old template content

 *

 */

function mgm_get_old_template_content($template){

	// system

	$system_obj = mgm_get_class('system');

	// init

	$content = '';

	// payment were different

	if(preg_match('/^payment/',$template)){		

		// new name

		$template_new = str_replace('payment_','',$template);

		// fetch

		if(isset($system_obj->setting['payment'][$template_new])){

			// content

			$content = $system_obj->setting['payment'][$template_new];				

		}

	}else{

		// fetch

		if(isset($system_obj->setting[$template])){

			// content

			$content = $system_obj->setting[$template];				

		}

	}	

	// content

	return $content;

}



/**

 * print template content

 *

 */

function mgm_print_template_content($template,$type='messages'){

	// return

	return mgm_get_template($template, NULL, $type);

}



/**

 * get message template

 *

 */

function mgm_get_message_template($message){

	$data = array();

	// set urls

	$data['home_url']     = trailingslashit(get_option('siteurl'));

	$data['site_url']     = trailingslashit(site_url());	

	$data['register_url'] = trailingslashit(mgm_get_custom_url('register'));					

	// login or profile

	$data['login_url']    = trailingslashit(mgm_get_custom_url((is_user_logged_in() ? 'profile' : 'login')));

	// check

	if(is_array($data)){

		foreach($data as $key=>$value){

			$message = str_replace('['.$key.']', $value, $message);

		}

	}	

	// return

	return $message;

}



/**

 * concat string

 *

 */

function mgm_str_concat(){

	$args_size= func_num_args();

	if($args_size>0){

		$args = func_get_args();

		return implode(' ', array_map('trim',$args));

	}

	return '';

}



/**

 * sidebar register links

 *

 */

function mgm_sidebar_register_links($username=false, $return=false, $template='sidebar') {

	global $wpdb, $user, $duration_str, $mgm_sidebar_widget;

	// username

	if (!$username) {

		$username = strip_tags($_GET['username']);

	}

	

	// member data

	$member = mgm_get_member( $user->ID);

	$system_obj = mgm_get_class('system');

	$membership_type = strtolower($member->membership_type);

	$packs_obj = mgm_get_class('subscription_packs');

	$packs = $packs_obj->packs;

	

	if (!$packs) {

		$packs = array();

	}

	

	$html = '';

	$border = '';



	$active_modules = $system_obj->get_active_modules('payment');	

	$mod_count = count($active_modules);

	$modules_dir = MGM_MODULE_BASE_DIR ;

	$base = add_query_arg(array('ud'=>1, 'username'=>$username), mgm_home_url('purchase_subscription'));



	foreach ($packs as $pack) {

		

		if($pack['hidden']!=1){



			$dur_type = $duration_str[$pack['duration_type']];

			$dur_str = ($pack['duration'] == 1 ? rtrim($dur_type, 's'):$dur_type);

			$ac_type = strtolower($pack['membership_type']);

	

			if (in_array($ac_type, array($membership_type, 'trial', 'free'))) {

				continue;

			}

	

			$cost = $pack['cost'];

	

			$pack_str = $packs_obj->get_pack_desc($pack);		

	

			$html .= '<div class="mgm_sidebar_register_links_container">';

	

			if ($template != 'sidebar') {

				$html .= '<div class="mgm_sidebar_register_pack_str">' . $pack_str . '</div>';

	

				if ($pack['description'] != '') {

					$html .= '<div id="mgm_pack_string" class="mgm_sidebar_pack_overview" id="mgm_pack_overview">' . $pack['description'] . '</div>';

				}

			} else {

				$html .= '<div class="mgm_sidebar_pack_string  ' . ($mod_count > 1 ? 'mgm_custom_filed_table':'') . '" id="mgm_pack_string_sidebar">' . $pack_str . '</div>';

			}

	

			if ($mod_count) {

				if ($active_modules) {

					$tran_id = 0;

					foreach ($active_modules as $module) {

						if ($module == 'mgm_trial') {

							continue;

						}

						$mod_obj = mgm_get_module($module,'payment');	

						// create transaction

						if(!$tran_id) $tran_id = mgm_add_transaction($pack, array('user_id' => $user->ID));

						// button

						// issue#: 398

						// $button = mgm_get_module($module,'payment')->get_button_subscribe(array('pack'=>$pack));					

						$button = $mod_obj->get_button_subscribe(array('tran_id' => $tran_id, 'pack'=>$pack, 'widget' => true));					

						// button

						if ($button) {

							$html .= sprintf('<div class="mgm_sidebar_module_container"><div class="mgm_sidebar_module_button">%s</div></div>', $button);

						}

					}

				}

			} else {

				$html .= __('There are no gateways available at this time.','mgm');

			}

	

			$html .= '<div class="clear_padding_marginfix"></div></div>';

		}

	}



	// html

	if ($return) {

		return $html;

	} else {

		echo $html;

	}

}



/**

 * wrapper for get_userdata, copies data form custom fields

 *

 */

function mgm_get_userdata($user_id){

	// get user

	$user = get_userdata($user_id);

	// member

	$member = mgm_get_member($user_id);

	// check profile fields

	$profile_fields = mgm_get_config('default_profile_fields', array());

	// loop

	foreach($profile_fields as $name=>$field){

		// wordpress is not set

		if(empty($user->$field['name'])){

			// check mgm if set

			if(isset($member->custom_fields->$name) && !empty($member->custom_fields->$name)){

				// default

				$user->$field['name'] = $member->custom_fields->$name;

				// compat

				if(!preg_match('/user_/',$field['name'])){

					// set

					$compat_field = 'user_'.str_replace('_','',$field['name']);

					// set

					$user->$compat_field = $member->custom_fields->$name;

				}

			}

		}

	}

	// return	

	return $user;

}



/**

 * update default userdate, copy form mgm custom fields to wordpress fields

 *

 */

function mgm_update_default_userdata($user_id){

	// db

	global $wpdb;

	// user	

	$user = get_userdata($user_id);

	// set aside member object

	$member = mgm_get_member($user_id);

	// default

	$profile_fields = mgm_get_config('default_profile_fields',array());

	// loop

	foreach($profile_fields as $name=>$field){

		// do not update pasword/login here !!!

		if(in_array($name, array('username','email','password','password_conf'))) continue;

		// check if empty

		if(empty($user->$field['name'])){

			// check custom

			if(isset($member->custom_fields->$name) && !empty($member->custom_fields->$name)){

				// value

				$value = $member->custom_fields->$name;

				// check diff

				if($name == 'url' || $name == 'display_name'){

				// users table update

					$wpdb->query($wpdb->prepare("UPDATE `{$wpdb->users}` SET `{$field['name']}` = %s WHERE ID = %d", $value, $user_id ));										

				}else{

				// meta update	

					update_user_option($user_id,$field['name'],$value,true);	

				}

			}

		} 	

	}		

	// return 

	return $user_id ;	

}



/**

 * subscribe to autoresponder

 *

 * @param int $user_id

 * @param object $member

 * @return int $user_id

 */

function mgm_autoresponder_send_subscribe($user_id, $member = NULL){	

	// get user, if passed from module

	if (is_null($member)) $member = mgm_get_member($user_id);			

	// if subscribed	

	if(isset($member->subscribed) && bool_from_yn($member->subscribed)){

		// send with active				

		if(isset($member->autoresponder)){

			$return = mgm_get_module($member->autoresponder, 'autoresponder')->subscribe($user_id);					

		}

	}

	// return

	return $user_id;

}



/**

 * unsubscribe from autoresponder

 *

 * @param int $user_id

 * @return int $user_id

 */

function mgm_autoresponder_send_unsubscribe($user_id, $member=NULL){

	// issue #861 - verifying unsubscribe setting is enable/disable

	$autoresponder_unsubscribe = mgm_get_setting('autoresponder_unsubscribe');	

	// disabled	

	if( !bool_from_yn($autoresponder_unsubscribe) ){

		// return

		return $user_id;

	}	

	// get user, if passed from module

	if (is_null($member)) $member = mgm_get_member($user_id);		

	// if subscribed	

	if(isset($member->subscribed) && bool_from_yn($member->subscribed)){

		// check

		if(isset($member->autoresponder)){

			// send with active					

			$return = mgm_get_module($member->autoresponder,'autoresponder')->unsubscribe($user_id);	

			// unset

			unset($member->subscribed,$member->autoresponder);	

			// save

			$member->save();	

		}				

	}

	// return

	return $user_id;

}





/**

 * Handles registering a new user.

 *

 * @param string $user_login User's username for logging in

 * @param string $user_email User's email address to send password and add

 * @return int|WP_Error Either user's ID or error on failure.

 */

function mgm_register_new_user( $user_login, $user_email, $show_fields=NULL ) {

	// init errors

	$errors = new WP_Error();



	// sanitize

	$sanitized_user_login = apply_filters( 'mgm_registration_user_login_sanitized', sanitize_user( $user_login ));

	$user_email           = apply_filters( 'user_registration_email', $user_email );

	

	// action

	do_action( 'register_post', $sanitized_user_login, $user_email, $errors, $show_fields);

	// errors

	$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

	// return

	if ( $errors->get_error_code() ) return $errors;

	

	// generate pass

	$user_pass = wp_generate_password();

	// create

	$user_id = wp_create_user( $sanitized_user_login, $user_pass, $user_email );

	// error

	if ( ! $user_id ) {

		$errors->add( 'registerfail', sprintf( __( '<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !' ), get_option( 'admin_email' ) ) );

		return $errors;

	}

	// update

	update_user_option( $user_id, 'default_password_nag', true, true ); //Set up the Password change nag.

	// send mail

	wp_new_user_notification( $user_id, $user_pass );

	// return id

	return $user_id;

}



/**

 * set custom errors

 *

 * @param object $wp_error

 * @return string $errors

 */

function mgm_set_errors($wp_error, $return = false) {	

	// init

	if ( empty($wp_error) ) $wp_error = new WP_Error();

	

	// if(defined('MGM_DONE_SET_ERRORS')) return;

	// check

	if ( $wp_error->get_error_code() ) {

		$error_string = '';

		$errors = array('error'=>array(), 'message'=>array());	

		$codes  = array();	

		foreach ( $wp_error->get_error_codes() as $code ) {

			if(in_array($code, $codes)) continue;



			$severity = $wp_error->get_error_data($code);

			foreach ( $wp_error->get_error_messages($code) as $error ) {				

				

				// $error = mgm_replace_message_links($code, $error);

				$error = mgm_replace_email_username($code, $error);

				if ( 'message' == $severity )

					$errors['message'][$code] = $error . "<br />\n";

				else

					$errors['error'][$code] = $error . "<br />\n";

				

				break;

			}

			

			// set for repeat

			$codes[] = $code;

		}	

		

		// css 

		$css_group = mgm_get_css_group();



		// issue #867

		if($css_group != 'none') {

			$error_string .= "\n".'<link rel="stylesheet" href="'. MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.messages.css' .'" type="text/css" media="all" />';			

		}

		

		// build

		if ( !empty($errors['error']) ){

			$error_string .= '<div class="mgm_message_error alert neg-alert">' . apply_filters('login_errors', implode(' ', $errors['error'])) . "</div>\n";

		}else if ( !empty($errors['message']) ){

			$error_string .= '<div class="mgm_message_success">' . apply_filters('login_messages', implode(' ',$errors['message'])) . "</div>\n";		

		}

				

		// return

		if($return)

			return $error_string;

		else 	

			echo $error_string;		

	}

	//let return false if $return = true;	

}



/**

 * replace message links with custom link

 *

 */

function mgm_replace_message_links($code, $error_str) {		

	switch ($code) {

		case 'invalid_username':		

		case 'incorrect_password'://replace old lost password link with new custom link

			$prev_link 	= site_url('wp-login.php?action=lostpassword', 'login');

			$replace 	= mgm_get_custom_url('lostpassword');

			$error_str  = str_replace($prev_link, $replace, $error_str);

		break;

	}

	

	return $error_str;

}



/**

 */

 function mgm_replace_email_username($code, $error_str){

 	switch ($code) {

		case 'empty_username':		

		case 'invalid_username':

			if(bool_from_yn(mgm_get_setting('enable_email_as_username'))){

				$error_str = preg_replace('#' . __('username','mgm') . '#', __('Email','mgm'), $error_str);

			}	

		break;

		case 'incorrect_password':

			if(bool_from_yn(mgm_get_setting('enable_email_as_username'))){				

				$error_str = preg_replace('#username <strong>(.*)</strong> is#', 'email <strong>' . mgm_post_var('log') . '</strong> is', $error_str);

			}

		break;

	}

	

	return $error_str;

 }

/**

 * check wp form loaded

 *

 */

function mgm_check_wordpress_login(){

	// current url

	$current_url = mgm_current_url();	

	// checl

	if(preg_match('/wp-login\.php/',$current_url) || preg_match('/wp-signup\.php/',$current_url)){// considering multi-site 

		return true;

	} 

	// return

	return false;

}



/**

 * retrive/lost password

 *

 */

function mgm_retrieve_password() {

	global $wpdb, $current_site;



	$errors = new WP_Error();

	if ( empty( $_POST['user_login'] ) && empty( $_POST['user_email'] ) )

		$errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or e-mail address.','mgm'));

	if ( strpos($_POST['user_login'], '@') ) {

		$user_data = get_user_by('email',trim($_POST['user_login']));

		if ( empty($user_data) )

			$errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.','mgm'));

	} else {

		$login = trim($_POST['user_login']);

		$user_data = get_user_by('login', $login);

	}	

	do_action('lostpassword_post');

	if ( $errors->get_error_code() )

		return $errors;

	if ( !$user_data ) {

		$errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or e-mail.','mgm'));

		return $errors;

	}

	// redefining user_login ensures we return the right case in the email

	$user_login = $user_data->user_login;

	$user_email = $user_data->user_email;

	do_action('retreive_password', $user_login);  // Misspelled and deprecated

	do_action('retrieve_password', $user_login);

	$allow = apply_filters('allow_password_reset', true, $user_data->ID);

	if ( ! $allow )

		return new WP_Error('no_password_reset', __('Password reset is not allowed for this user','mgm'));

	else if ( is_wp_error($allow) )

		return $allow;

	$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));

	if ( empty($key) ) {

		// Generate something random for a key...

		$key = wp_generate_password(20, false);

		// action

		do_action('retrieve_password_key', $user_login, $key);

		//issue #1700

		if( mgm_compare_wp_version('3.7', '>=') ){

			// Now insert the key, hashed, into the DB.

			if ( empty( $wp_hasher ) ) {

				require_once ABSPATH . 'wp-includes/class-phpass.php';

				$wp_hasher = new PasswordHash( 8, true );

			}

			$key = $wp_hasher->HashPassword( $key );

		}	

		// Now insert the new md5 key into the db

		$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));

	}

	// title source is unknown

	if(!isset($title)) $title = __('Get Your Password','mgm');

	// apply filter

	$title = apply_filters('retrieve_password_title', $title);

	

	// message source is unknown

	if(!isset($message)) $message = __('Password Reset Requested','mgm');

	// apply filter

	$message = apply_filters('retrieve_password_message', $message, $key);	

	// send

	if ( isset($message) && !empty($message) && !mgm_mail($user_email, $title, $message) ) {

		// add 

		$errors->add('empty_username', __('The e-mail could not be sent.','mgm') . "<br />" . __('Possible reason: your host may have disabled the mail() function...','mgm') );

		// return

		return $errors;	

	}

	// return

	return true;

}



/**

 * validate module

 *

 * @param string $module

 * @param string $type (payment|autoresponder)

 * @return bool/object

 */

function mgm_is_valid_module($module, $type='payment', $return = 'bool'){

	// system

	$system_obj = mgm_get_class('system');

	// check in avilable modules

	$available_modules = $system_obj->get_active_modules($type);				

	// match

	if( in_array($module, $available_modules) ){

		// object

		if( $return == 'object' ){

			// get

			$module_obj = mgm_get_module($module, $type); 

			// check

			if( isset($module_obj->code) ){

				return $module_obj;

			}else{

				// deactivate, this will auto deactivate previously tracked and deleted module 

				$system_obj->deactivate_module($module, $type);

				// return 

				return false;

			}	

		}else if ( $return == 'bool' ){

		// return

			return true;

		}	

	}	

	// return

	return false; 	

}



/**

 * get captcha

 */

function mgm_get_captcha_field($name='mgm_login_field'){

	// captcha

	$captcha = '';

	// get firls

	$captcha_field = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_login'=>true),'name'=>'captcha'));	

	// check

	if(!empty($captcha_field)){

		// get field

		$field   = array_shift($captcha_field);		

		// generate

		$captcha = mgm_get_class('mgm_form_fields')->field_type_captcha($field,$name);	

	}

	// return

	return $captcha;

}



/**

 * process user login

 *

 * @param string $action

 * @param string $user_login

 * @param string $user_password

 * @return bool/array

 */

function mgm_process_user_login($action=NULL, $user_login=NULL, $user_password=NULL) {

	$secure_cookie = '';

	// action

	if(!$action) $action = (isset($_GET['action']) ? strip_tags($_GET['action']) : ''); 

	// login 

	if(!$user_login) $user_login = (isset($_POST['log']) ? $_POST['log'] : '');

	// user_password 

	if(!$user_password) $user_password = (isset($_POST['pwd']) ? $_POST['pwd'] : '');

	 

	// check

	if( isset($action) && !empty($action) ) {

		switch ($action) {

			//logout

			case 'logout':

				do_action('mgm_logout');

				break;	

				

			//check password reset:	

			case 'rp':

			case 'resetpass':						

				$errors = apply_filters('mgm_validate_reset_password', strip_tags($_GET['key']), strip_tags($_GET['login']));

				if ( is_wp_error($errors) ) {

					return $errors;	

				}

				do_action('mgm_reset_password', strip_tags($_GET['key']), $errors ); //please note: $errors will carry user object if no error				

				break;

		}

	}

	

	// intrim

	$interim_login = isset($_REQUEST['interim-login']);

	// If the user wants ssl but the session is not ssl, force a secure cookie.

	if ( !empty($user_login) && !force_ssl_admin() ) {

		$user_name = sanitize_user($user_login);

		if ( $user = get_user_by('login', $user_name) ) {

			if ( get_user_option('use_ssl', $user->ID) ) {

				$secure_cookie = true;

				force_ssl_admin(true);

			}

		}

	}	

	if ( isset( $_REQUEST['redirect_to'] ) ) {

		$redirect_to = $_REQUEST['redirect_to'];

		// Redirect to https if user wants ssl

		if ( $secure_cookie && false !== strpos($redirect_to, 'wp-admin') )

			$redirect_to = preg_replace('|^http://|', 'https://', $redirect_to);

	} else {

		$redirect_to = admin_url();

	}

	

	$reauth = empty($_REQUEST['reauth']) ? false : true;

	if ( !$secure_cookie && is_ssl() && force_ssl_login() && !force_ssl_admin() && ( 0 !== strpos($redirect_to, 'https') ) && ( 0 === strpos($redirect_to, 'http') ) )

		$secure_cookie = false;



	//Issue #782

	if (isset($_POST['recaptcha_response_field']) && isset($_POST['recaptcha_response_field'])) {		

		$errors = new WP_Error();

		if(empty($user_login)){

			$errors->add('empty_username', __("<strong>ERROR</strong>: The username field is empty."));		

		}

		if(empty($user_password)){

			$errors->add('empty_password', __("<strong>ERROR</strong>:  The password field is empty.."));		

		}

		if(empty($_POST['recaptcha_response_field'])){		

			$errors->add('empty_captcha', __('<strong>ERROR</strong>: You must enter the Captcha String.','mgm'));

		}else {		

			$recaptcha = mgm_get_class('recaptcha')->recaptcha_check_answer($_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field'] );				

			if(!isset($recaptcha->is_valid) || !$recaptcha->is_valid ) {					

				$errors->add('invalid_captcha', __('<strong>ERROR</strong>: '.(!empty($recaptcha->error) ? $recaptcha->error : 'The Captcha String isn\'t correct.') ,'mgm'));	

			}

		}

		if ( $errors->get_error_code() )

			return $errors;

	}

	

	// credentials

	$credentials = array('user_login' => $user_login, 'user_password'=>$user_password);

	if(!empty( $_POST['rememberme'] )) $credentials['remember'] = $_POST['rememberme'];

	

	// do the signin	

	$user = wp_signon($credentials, $secure_cookie);

	

	// redirect to

	$redirect_to = apply_filters('login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user);

	

	if ( !is_wp_error($user) && !$reauth ) {

		if ( $interim_login ) {

			$message = '<div class="message">' . __('You have logged in successfully.') . '</div>';

			login_header( '', $message ); ?>

			<script type="text/javascript">setTimeout( function(){window.close()}, 8000);</script>

			<div class="alignright"><input type="button" class="button-primary" value="<?php esc_attr_e('Close'); ?>" onclick="window.close()" /></div></body></html>

		<?php exit; }

		

		// If the user can't edit posts, send them to their profile.

		if ( !$user->has_cap('edit_posts') && ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) )

			$redirect_to = admin_url('profile.php');

		wp_safe_redirect($redirect_to);

		exit();

	}

	// error

	$errors = $user;

	// Clear errors if loggedout is set.

	if ( !empty($_GET['loggedout']) || $reauth )

		$errors = new WP_Error();

	

	// If cookies are disabled we can't log in even with a valid user+pass	

	if ( isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]) ) {

		$errors->add('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress."));		

	}



	// Some parts of this script use the main login form to display a message

	if ( isset($_GET['loggedout']) && TRUE == $_GET['loggedout'] )

		$errors->add('loggedout', __('You are now logged out.'), 'message');

	elseif	( isset($_GET['registration']) && 'disabled' == $_GET['registration'] )

		$errors->add('registerdisabled', __('User registration is currently not allowed.'));

	elseif	( isset($_GET['checkemail']) && 'confirm' == $_GET['checkemail'] )

		$errors->add('confirm', __('Check your e-mail for the confirmation link.'), 'message');

	elseif	( isset($_GET['checkemail']) && 'newpass' == $_GET['checkemail'] )

		$errors->add('newpass', __('Check your e-mail for your new password.'), 'message');

	elseif	( isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'] )

		$errors->add('registered', __('Registration complete. Please check your e-mail.'), 'message');

	elseif	( $interim_login )

		$errors->add('expired', __('Your session has expired. Please log-in again.'), 'message');



	// Clear any stale cookies.

	if ( $reauth )

		wp_clear_auth_cookie();



	if ( $errors->get_error_code() )

		return $errors;

	else 

		return true;		

}



/**

 * get custom pages

 *

 * @param void

 * @return array $pages

 */

function mgm_get_custom_pages(){	

	// define pages

	$pages = array(  

		array('post_title'=>'Register'            , 'post_content'=>'[user_register]'       , 'post_name'=>'register'),

		array('post_title'=>'Profile'             , 'post_content'=>'[user_profile]'        , 'post_name'=>'profile'),	

		array('post_title'=>'User Profile'        , 'post_content'=>'[user_public_profile]' , 'post_name'=>'userprofile'),	

		array('post_title'=>'Login'               , 'post_content'=>'[user_login]'          , 'post_name'=>'login'),	

		array('post_title'=>'Lost Password'       , 'post_content'=>'[user_lostpassword]'   , 'post_name'=>'lostpassword'),	

		array('post_title'=>'Transactions'        , 'post_content'=>'[transactions]'        , 'post_name'=>'transactions'),

		array('post_title'=>'Membership Details'  , 'post_content'=>'[membership_details]'  , 'post_name'=>'membership-details'),

		array('post_title'=>'Membership Contents' , 'post_content'=>'[membership_contents]' , 'post_name'=>'membership-contents')

	);

	// apply filter to add more pages

	return apply_filters('mgm_custom_pages', $pages);

}



/**

 * base utl

 */

function mgm_get_wp_base_url(){

	// site url

	$site_url = trailingslashit(get_option('siteurl'));		

	// home

	$home_url = trailingslashit(get_option('home'));// different if wp installed in sub directory	

	// baseurl

	$base_url = ($home_url != $site_url) ? $home_url : $site_url;

	// return

	return $base_url;

}



/**

 * create custom pages

 *

 * @param void

 * @return void

 */

function mgm_create_custom_pages($flush=false){	

	global $wpdb;

	// pages

	$pages = mgm_get_custom_pages();

	// check 

	if( !$mgm_custom_pages = get_option('mgm_custom_pages') ){

		$mgm_custom_pages = 0;

	}

	// first run, flush or new pages added

	if( count($pages) > (int)$mgm_custom_pages || $flush === TRUE ){

		global $wp_rewrite;

		// get object

		if(!is_object($wp_rewrite)) $wp_rewrite= new WP_Rewrite();		

		

		// permalink type

		$custom_permalink = (get_option('permalink_structure')) ? true : false;

		// base url

		$base_url = mgm_get_wp_base_url();

		// order

		$menu_order = 0;

		// loop

		foreach($pages as $page){

			// order

			$page['menu_order'] = $menu_order++;

			// create/update

			$page = mgm_create_custom_page($page, $custom_permalink, $base_url);						

		}

		// flush rewrite rules

		if($menu_order>1 && is_object($wp_rewrite)){

			$wp_rewrite->flush_rules();

		}	

		// system

		$system_obj = mgm_get_class('system');					

		// set in system urls

		foreach($pages as $page){

			// page url

			$page_url = $custom_permalink ? trailingslashit($base_url . $page['post_name']) : add_query_arg(array('page_id'=>(int)$page['ID']), $base_url);	

			// set

			$system_obj->setting[str_replace('-', '_', $page['post_name']) . '_url'] = $page_url;				

		}		

		// update object

		$system_obj->save();		

		// track

		update_option('mgm_custom_pages', count($pages));

	}

	

	// sync pages count

	if( (int)$mgm_custom_pages > count($pages) ){

		// track

		update_option('mgm_custom_pages', count($pages));

	}	

}



/**

 * create custom page

 *

 * @param array $page

 * @return array $page

 */

function mgm_create_custom_page($page, $custom_permalink=NULL, $base_url=NULL, $system=NULL){

	global $wpdb;

	

	// permalink type

	if(is_null($custom_permalink)) $custom_permalink = (get_option('permalink_structure')) ? true : false;	

	// home url

	if(is_null($base_url)) $base_url = mgm_get_wp_base_url();;		

	// default		

	$default = array('post_author'=>1, 'post_date'=>date("Y-m-d H:i:s"), 'post_date_gmt'=>gmdate("Y-m-d H:i:s"), 

					 'post_status'=>'publish', 'comment_status'=>'closed', 'ping_status'=>'closed', 'post_type'=>'page');		

	// check already exists, impove to add type also wp 3+	

	$current_page = get_page_by_title( $page['post_title'], ARRAY_A, (isset($page['post_type']) ? $page['post_type'] : 'page'));			

	// does not exist		

	if(isset($current_page['ID']) && (int)$current_page['ID'] > 0) {

		// reset

		$page = $current_page;			

	}else{								

		// insert post

		$page['ID'] = @wp_insert_post( array_merge($default, $page) );						

		// update guid				

		$page['guid'] = add_query_arg(array('page_id'=>(int)$page['ID']), $base_url);	

		// update

		$wpdb->update( $wpdb->posts, array( 'guid' => $page['guid'] ), array('ID'=>$page['ID']) );	

	}

	

	// system

	if(is_null($system)){

		global $wp_rewrite;

		// get object

		if(!is_object($wp_rewrite))	$wp_rewrite= new WP_Rewrite();	

		// flush

		if(is_object($wp_rewrite)) $wp_rewrite->flush_rules();		

		// system

		$system_obj = mgm_get_class('system');	

		// page url

		$page_url = $custom_permalink ? trailingslashit($base_url . $page['post_name']) : add_query_arg(array('page_id'=>(int)$page['ID']), $base_url);	

		// set

		$system_obj->setting[str_replace('-', '_', $page['post_name']) . '_url'] = $page_url;	

		// update object

		$system_obj->save();		

	}

		

	// return 

	return $page;

}



/**

 * check if custom page created and published

 *

 * @param string key/name of page

 * @param string $content

 * @return bool published 

 */

function mgm_is_custom_page_published($name, $content=NULL){

	global $wpdb;

	// init

	$published = false;

	$shortcodes = array();

	// on name

	switch($name){				

		case 'register':

		case 'lostpassword':

		case 'profile':

			$shortcodes[] = sprintf('[user_%s]', $name);

	    break;	

	    case 'login':	

		case 'login_register':

			$shortcodes[] = '[user_login]';

			$shortcodes[] = '[[login_register]]';

		break;

		case 'userprofile':	

			$shortcodes[] = '[user_public_profile]';

		break;

		case 'transactions':

		case 'membership_details':

		case 'membership_contents':

			$shortcodes[] = sprintf('[%s]', $name);	

		break;

	}	

	// check

	if( !empty($shortcodes) ){

		// content

		if($content) {

			$match = false;

			foreach ($shortcodes as $shortcode){

				if( preg_match('#'. $shortcode . '#', $content) ){

					$match = true; break;

				}

			}

			return $match;

		}	

		

		// query

		$query = array();

		foreach ($shortcodes as $shortcode){

			$query[] = " `post_content` LIKE '%{$shortcode}%'"; 

		}

		// join

		$query_str = sprintf(' AND (%s) ', implode(' OR ', $query) );

		// sql

		$sql = "SELECT `post_status` FROM `{$wpdb->posts}` WHERE 1 {$query_str}

				AND `post_type` = 'page' AND `post_status` = 'publish' LIMIT 1";

		// get 

		$post_status = $wpdb->get_var( $sql );



		// check

		if(isset($post_status) && $post_status == 'publish') {

		// return

			$published = true;

		}		

	}

	// return 

	return $published;

}



/**

 * get custom url, return default if not published

 *

 * @param string key/name of page

 * @param bool force default

 * @param array query string

 * @return string url 

 */

function mgm_get_custom_url($name, $load_default = false, $query_arg = array()) {

	// get system

	$system_obj = mgm_get_class('system');

	

	// is published page

	$is_published = mgm_is_custom_page_published( ($name == 'purchase_content') ? 'transactions' : $name );

	

	// on name

	switch($name) {

		case 'login':

			// custom					

			if(!$load_default && $is_published && ($login_url = mgm_get_setting('login_url'))) {

				$url = mgm_site_url($login_url);

			}else{

			// default

				$url = site_url('wp-login.php?action=login', 'login');					

			}				

		break;

		case 'register':

			// custom 

			if(!$load_default && $is_published && ($register_url = mgm_get_setting('register_url'))){ 

				$url = mgm_site_url($register_url);

			}elseif(!$load_default){

			// old 

				$url = add_query_arg(array('method'=>'register'), mgm_home_url('purchase_subscription'));	

			}else{

			// default

				$url = site_url('wp-login.php?action=register', 'login');

			}			

		break;	

		case 'lostpassword':

			// custom 

			if(!$load_default && $is_published && ($lostpassword_url = mgm_get_setting('lostpassword_url'))){ 

				$url = mgm_site_url($lostpassword_url);

			}else{

			// default

				$url = site_url('wp-login.php?action=lostpassword');

			}			

		break;

		case 'profile':

			// custom 

			if(!$load_default && $is_published && ($profile_url = mgm_get_setting('profile_url'))){ 

				$url = mgm_site_url($profile_url);

			}else{

			// default

				$url = admin_url('profile.php');

			}			

		break;

		case 'userprofile':

			// custom 

			if(!$load_default && $is_published && ($public_profile_url = mgm_get_setting('userprofile_url'))){ 

				$url = mgm_site_url($public_profile_url);

			}else{

			// default

				$url = admin_url('profile.php');

			}			

		break;

		case 'purchase_content': // reuse the same transactions url here: issue#: 756

			// $is_published = mgm_is_custom_page_published('transactions');

		case 'transactions':			

			// custom 					

			if($is_published && ($transactions_url = mgm_get_setting('transactions_url'))){ 

				$url = mgm_site_url($transactions_url, true);

			}else{

			// default

				$url = mgm_home_url( $name == 'purchase_content' ? 'purchase_content' : 'purchase_subscription' );	

			}

		break;	

		case 'membership_details':	

			// custom 

			if($is_published && !$load_default && !is_super_admin() && ($membership_details_url = mgm_get_setting('membership_details_url'))){ 

				$url = mgm_site_url($membership_details_url);

			}else{

			// default						

				$url = admin_url(sprintf('%s.php?page=mgm/profile',(is_super_admin() ? 'users' : 'profile')));				

			}				

		break;

		case 'membership_contents':	

			// custom 

			if($is_published && !$load_default && !is_super_admin() && ($membership_contents_url = mgm_get_setting('membership_contents_url'))){ 

				$url = mgm_site_url($membership_contents_url);

			}else{

			// default		

				$url = admin_url(sprintf('%s.php?page=mgm/membership/content',(is_super_admin() ? 'users' : 'profile')));

			}			

		break;		

		default:

		// default

			$url = mgm_home_url('purchase_subscription');

		break;	

	}

	

	// @todo: add_query_arg urlencodes url with page=mgm/ need fix in some servers

	// add query arg

	if(!empty($query_arg)) $url = add_query_arg( $query_arg, $url );		

	

	// return

	return $url;

}



/**

 * get country from code

 */

function mgm_country_from_code($code){

	global $wpdb;

	// get country

	$country = $wpdb->get_var($wpdb->prepare("SELECT `name` FROM ". TBL_MGM_COUNTRY ." WHERE `code` = %s", $code));	

	// return 

	return $country;

}



/**

 * get member custom fields

 */

function mgm_get_member_custom_fields($user_id){

	// mgm_member

	$member = mgm_get_member($user_id);

	// get

	$custom_fields = $member->custom_fields;	

	// return 

	return $custom_fields;

}



/**

 * set member custom fields

 */

function mgm_set_member_custom_fields($user_id, $custom_fields=array()){

	// mgm_member

	$member = mgm_get_member($user_id);	

	// set fields

	$member->set_custom_fields($custom_fields, true);// merge

	// save	

	$member->save();	

	// return 

	return true;

}



/**

 * create slug

 */

function mgm_create_slug($text, $len=50){

	// trim

	if($len) $text = substr(trim($text),0,$len);

	// return

	return strtolower(preg_replace('/\s+/', '_', $text));

}



/**

 * get selected subscription

 */

function mgm_get_selected_subscription($args=array()){

	// encoded

	$encoded = false;

	// get 

	if(empty($args) && !empty($_GET)) {

		$args    = $_GET;

		$encoded = true;// from GET always encoded

	}		

	// init

	$selected = array();

	// package

	if(isset($args['package']) && !empty($args['package'])){

		// decode		

		$package = ($encoded) ? base64_decode($args['package']) : $args['package'];											

		// check

		if(strpos($package, '#') !== FALSE){

			list($selected['name'], $selected['id']) = explode('#',$package);

		}else{

			$selected['name'] = $package;

		}

	}else if(isset($args['membership']) && !empty($args['membership'])){

	// membership

		$selected['name'] = ($encoded) ? base64_decode($args['membership']) : $args['membership'];			

	}

	// return 

	return (isset($selected['name'])) ? $selected : false;

}



/**

 * select subscription

 */

function mgm_select_subscription($pack, $selected_pack){

	// init

	$checked = '';	

	// default select

	if(isset($selected_pack['name'])){

		// match

		if($selected_pack['name'] == $pack['membership_type']){

			// set

			if(isset($selected_pack['id'])){

				// match

				if($selected_pack['id'] == $pack['id']){

					$checked = ' checked="checked"';

				}

			}else{

				$checked = ' checked="checked"';

			}

		}									

	}else{

		 if((int)$pack['default'] == 1) $checked = ' checked="checked"';	

	}		

	// return

	return $checked;

}



/**

 * checks if pack is allowed on extend subscription page

 */

function mgm_pack_extend_allowed($pack){

	// set allow_renewal

	if(!isset($pack['allow_renewal'])){

		$pack['allow_renewal'] = 1;

	}

	// active

	if(!isset($pack['active']['extend'])){

		$pack['active']['extend'] = 1;

	}

	// return if both are true

	return (bool)$pack['allow_renewal'] & (bool)$pack['active']['extend'] ;

}



/**

 * checks if pack is allowed on upgrade subscription page

 */

function mgm_pack_upgrade_allowed($pack){	

	// active

	if(!isset($pack['active']['upgrade'])){

		$pack['active']['upgrade'] = 1;

	}

	// return 

	return (bool)$pack['active']['upgrade'];

}



/**

 * checks if pack is allowed on register subscription page

 */

function mgm_pack_register_allowed($pack){	

	// active

	if(!isset($pack['active']['register'])){

		$pack['active']['register'] = 1;

	}

	// return 

	return (bool)$pack['active']['register'] ;

}



/**

 * notice

 */

function mgm_notice($message, $error = false){	

	echo sprintf('<div id="message" class="%s"><div><strong>%s</strong></div></div>',( $error ? 'error' : 'updated fade'),$message);    

}



/**

 * expire member: for force expire test

 */

function mgm_expire_member($user_id,$expire_date){

	// get member

	$member = mgm_get_member($user_id);

	// if today 

	if($expire_date == date('Y-m-d')){

		$new_status              = MGM_STATUS_CANCELLED;

		$new_status_str          = __('Subscription cancelled','mgm');

		$member->status      = $new_status;

		$member->status_str  = $new_status_str;					

		$member->expire_date = date('Y-m-d');			

	}else{

		$format = mgm_get_date_format('date_format');

		// status

		$new_status     = MGM_STATUS_AWAITING_CANCEL;	

		$new_status_str = sprintf(__('Subscription awaiting cancellation on %s','mgm'),date($format, strtotime($expire_date)));			

		// set reset date

		$member->status_reset_on = $expire_date;

		$member->status_reset_as = MGM_STATUS_CANCELLED;

	}			

	// update user

	// update_user_option($user_id, 'mgm_member', $member, true);	

	$member->save();

}





/**

 * check logged in user is having the supplied subscriptions 

 */

function mgm_user_is($type = array()) {

	// get current user

	$current_user = wp_get_current_user();

	//if user not logged in

	if(!isset($current_user->ID) || (isset($current_user->ID) && $current_user->ID == 0))

		return false;

	//for admin: issue#; 878

	if (is_super_admin())

		return true;	

	

	// get object	

	$member = mgm_get_member($current_user->ID);

	$arr_mt = mgm_get_subscribed_membershiptypes($current_user->ID, $member);

	$membership_type = strtolower($member->membership_type);	

	if(!in_array($membership_type, $arr_mt))

		$arr_mt[] = $membership_type;

	$return = false;

	// check

	if($type) {		

		if(is_string($type)){

			//if($membership_type == strtolower($type) )

			if(in_array(strtolower($type), $arr_mt) )

				$return = true; 

		}elseif(is_array($type)){ 			

			foreach ($type as $t)

				if(in_array(strtolower($t), $arr_mt)) {

					$return = true;

					break;					

				}					

		}

	}

	// return

	return $return;	

}



/**

 * encode pack

 */

function mgm_encode_package($pack){

	// subs	

	$subs_text = implode('|', array($pack['cost'],$pack['duration'],$pack['duration_type'],$pack['membership_type'],$pack['id']));	

	// return

	return base64_encode($subs_text);		

}



/**

 * decode pack

 */

function mgm_decode_package($package){

	// get

	@list($cost, $duration, $duration_type, $membership_type, $pack_id) = explode('|', base64_decode($package));

	// return

	return array('cost'=>$cost, 'duration'=>$duration, 'duration_type'=>$duration_type, 'membership_type'=>$membership_type, 'pack_id'=>$pack_id);

}



/**

 * return packs

 *

 * @param obj $packs_obj :can be null

 * @param obj $types_obj :can be null

 * @param array $exclude : exclude ids, can be null

 * @return array

 */

function mgm_get_subscription_packages($packs_obj = null, $types_obj = null, $exclude = array()){

	// object

	$packs_obj = mgm_get_class('subscription_packs');	

	$types_obj = mgm_get_class('membership_types');

	// packages

	$packages = array();	

	// loop		

	foreach ($packs_obj->get_packs('all') as $pack) {	

		//skip passed ids		

		if(in_array($pack['id'], $exclude)) continue;

		// enc

		$subs_opt_enc = mgm_encode_package($pack);

		// key

		$packages[] = array('id' => $pack['id'], 'key'=>$subs_opt_enc,'label'=>$packs_obj->get_pack_desc($pack),'membership'=>$types_obj->get_type_name($pack['membership_type']),'description'=>$pack['description']);		

	}	

	// return

	return $packages;

}



/**

 * manually check a script is already included

 */

function mgm_is_script_already_included($script, $is_url = false) {	

	global $wp_scripts,$mgm_scripts;

		

	if(is_array($wp_scripts->registered)) {		

		$i = 0;

		foreach ($wp_scripts->registered as $obj) {			

			$file = (!$is_url) ? basename($obj->src) : $obj->src;				

			if($script == $file || (is_array($mgm_scripts) && in_array($file,$mgm_scripts)) ) {		

				return true;

			}

		}		

	}	

	

	if(is_array($mgm_scripts) && in_array($script,$mgm_scripts)) {			

		return true;

	}

	

	return false;

}



/**

 * get calendar year range

 */

function mgm_get_calendar_year_range(){

	// system

	$system_obj = mgm_get_class('system');

	// ranges

	$range_lower = $system_obj->setting['date_range_lower'];

	$range_upper = $system_obj->setting['date_range_upper'];

	

	// defaults

	if(!$range_lower) $range_lower = 50;

	if(!$range_upper) $range_upper = 10;

	

	// return 

	return sprintf('-%d:+%d',$range_lower,$range_upper);	

}



/**

 * check mgm scripts can be loaded: using in mgm_init.php

 *

 * @param none

 * $return boolean load script flag

 */

function mgm_if_load_admin_scripts() {

	// page

	$page = isset($_GET['page']) ? strip_tags($_GET['page']) : '';

	// return 

	$return = false;	

	// check

	if(!empty($page) && preg_match('/mgm\/admin/', $page)){ //mgm admin ui 

		$return = true;

	}elseif(isset($_GET['post']) && (int)$_GET['post']>0 && (isset($_GET['action']) &&  $_GET['action'] == 'edit')){ //edit post page

		$return = true;

	}elseif('post-new.php' == basename($_SERVER['SCRIPT_NAME'])){ //add new post page

		$return = true;

	}elseif('edit-tags.php' == basename($_SERVER['SCRIPT_NAME'])){ //edit tags, category taxonomy

		$return = true;

	}/*elseif('users.php' == basename($_SERVER['SCRIPT_NAME'])){ //users

		$return = true;

	}*/			

	// return 

	return $return;		

}



/**

 * check custom field active

 */

function mgm_is_customfield_active($fields = array(), $cf_register_page = array()) {

	if(!empty($fields)) {

		if(empty($cf_register_page))

			$cf_register_page = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_register'=>true)));			

		foreach ($cf_register_page as $cf) {

			if(in_array($cf['name'], $fields)) {

				return true;

			}

		}

	}

	return false;

}



/**

 * create test cookie

 */

function mgm_check_cookie() {

	if ( !is_user_logged_in() ) {

		//Set a cookie now to see if they are supported by the browser.

		setcookie(TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN);

		if ( SITECOOKIEPATH != COOKIEPATH )

			setcookie(TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN);

	}

}



/**

 * create token

 */

function mgm_create_token($type = 'alphanum', $len=8){

	// unique

	if( $type != 'unique'){

		// type

		switch ($type)

		{

			case 'alpha'	:	$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

			break;

			case 'alphanum'	:	$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

			break;

			case 'numeric'	:	$pool = '0123456789';

			break;

			case 'nozero'	:	$pool = '123456789';

			break;

		}

		// init

		$str = '';

		// loop

		for ($i=0; $i < $len; $i++)

		{

			$str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);

		}

		// return

		return $str;

	}else{

		// return

		return substr(md5(uniqid(mt_rand())), 0, $len);

	}	

	// default

	return substr(mt_rand(), 0, $len);	

}



/**

 * autologin redirection

 */

function mgm_auto_login($id) {

	// current user

	$current_user = wp_get_current_user();

	// check user already logged in

	if(isset($current_user->ID) && $current_user->ID > 0 ) return false;

	

	// check trans id

	if(is_numeric($id)) {	

		// sleep 2 sec to wait for response

		sleep(2);			

		// transaction

		$transaction = mgm_get_transaction($id);						

		// consider only registration

		if($transaction['payment_type'] != 'subscription_purchase' || (!isset($transaction['data']['is_registration']) || (isset($transaction['data']['is_registration']) && !bool_from_yn($transaction['data']['is_registration']))))

			return false;

			

		// verify transaction	

		if(is_numeric($transaction['data']['user_id']) && $transaction['data']['user_id'] > 0 && mgm_verify_transaction($transaction) ) {			

			// get user	

			$user_id = $transaction['data']['user_id'];		

			// cookie

			$secure_cookie = false;

			// check cookie

			if( !force_ssl_admin() && get_user_option('use_ssl', $user_id )) {

				$secure_cookie = true;

				force_ssl_admin(true);

			}			

			// set user

			$user = wp_set_current_user($user_id);	

			// validate		

			if(is_object($user)) {

				// flag to skip wp_signon hook used by mgm, "wp_login" to redirect 

				define('MGM_DOING_REGISTER_AUTO_LOGIN', true);

				// get member

				$member = mgm_get_member($user_id);

				// issue#: 672, decode password

				$pwd = mgm_decrypt_password($member->user_password, $user_id);							

				// login and redirect:

				wp_signon(array('user_login' => $user->user_login, 'user_password' => $pwd ), $secure_cookie);

				// return as done, why?, we are doing login in background instead of previous redirect and login

				// so far double check via this method should be deprecated, but kept for stable code reference

				return true;				

			}	

		}

	}

	// exit

	return false;

}



/**

 * verify transaction

 */

function mgm_verify_transaction($transaction) {	

	//check IP

	if(!isset($transaction['data']['client_ip']) || (isset($transaction['data']['client_ip']) && $transaction['data']['client_ip'] != mgm_get_client_ip_address()) ) //treat as fraud if try from different IP

		return false;

	//check datetime:

	if(!isset($transaction['transaction_dt']) || (isset($transaction['transaction_dt']) && (strtotime(date('Y-m-d H:i:s')) - strtotime($transaction['transaction_dt'])) > (60*10) )) { //delay is restricted to 10 minutes

		return false;

	}

	if($transaction['status'] != MGM_STATUS_ACTIVE)

		return false;

				

	return true;	

}



/**

 * 

 * converts mgm_member object to array to be saved in multiple membership type array

 *

 * @package MagicMembers

 * @since 2.5

 * @param $member, object 

 * @return array

 */

function mgm_get_members_packids($member) {

	$pack_ids = array();

	if(is_numeric($member->pack_id)) {

		$pack_ids[] = $member->pack_id;

	}

	if(isset($member->other_membership_types) && is_array($member->other_membership_types) && !empty($member->other_membership_types) ) {

		foreach ($member->other_membership_types as $key => $val) {

			$val = mgm_convert_array_to_memberobj($val, $member->id);

			if(isset($val->pack_id) && is_numeric($val->pack_id) && $val->status == MGM_STATUS_ACTIVE)

				$pack_ids[] = $val->pack_id;

		}

	}

	

	return $pack_ids;

}



/** 

 * convert mgm_member object to array to be saved in multiple membership type array

 *

 * @package MagicMembers

 * @since 2.5

 * @param $member, object 

 * @return array

 */ 

function mgm_convert_memberobj_to_array($member) {

	$arr_mgm_member = array();

	

	if(!empty($member)) {		

		foreach ($member as $key => $value) {				

			if(is_array($value) || is_object($value)) {

				$arr_mgm_member[$key] = mgm_convert_memberobj_to_array($value); 

			}else {	

				$arr_mgm_member[$key] = $value;	

			}			

		}

	}	

	

	return $arr_mgm_member;

}



/** 

 *convert array to mgm_member object to be used as $member object

 * This is specifically for multiple membership type

 *

 * @package MagicMembers

 * @since 2.5

 * @param $member, array  

 * @return array

 */ 

function mgm_convert_array_to_memberobj($member_options, $user_id, $attach_id = true, $recursive = true) {

	// init

	$member = new stdClass();

	// array

	if(is_array($member_options) && !empty($member_options)) {	

		// loop	

		foreach ($member_options as $key => $value) {

			// check

			if($recursive && (is_array($value) || is_object($value))) {

				// #1312 coupons

				if(in_array($key, array('coupon','upgrade','extend'))){

					$member->{$key} = $value;

				}else{

					$member->{$key} = mgm_convert_array_to_memberobj($value, $user_id, false, true);

				}

				

			}else {

				$member->{$key} = $value;

			}

		}

	}	

	

	// attach id:

	if($attach_id) $member->id = $user_id;

	// fix expire date

	mgm_fix_member_expire_date($member);

	// return

	return $member;

}



/**

 * fix expire date

 */

function mgm_fix_member_expire_date(&$member){

	// check

	if(!empty($member->expire_date) && (int)$member->expire_date > 0){

		// get time part

		$expire_time = date('H:i:s', strtotime($member->expire_date));

		// check

		if($expire_time == '00:00:00'){

			// pack join

			if(!empty($member->join_date) && (int)$member->join_date > 0){					

				$member->expire_date = date('Y-m-d', strtotime($member->expire_date)) . ' ' . date('H:i:s', $member->join_date);

			}

		}

	}

}



/**

 * saves multiple mgm_member objects(inner mgm_objects)

 * Every primary mgm_member object will have an array other_membership_types[] to hold multiple mgm_member objects.

 *

 * @param $user_id: user id

 * @param $fields: array/object of mgm_member fields 

 * @param update_index: index of inner mgm_member object in other_membership_types array, if specifically passed other_membership_types arrayt will be updated directly

 */

function mgm_save_another_membership_fields($fields, $user_id, $update_index = null) {	

	$member = mgm_get_member($user_id);	

	

	//make sure each membership object is an array:

	$fields = mgm_convert_memberobj_to_array($fields);

	$arr_remove = array('ID','id', 'name', 'code', 'description', 'saving','custom_fields', 'other_membership_types', 'setting');

	foreach ($arr_remove as $remove) {

		if(isset($fields[$remove]))

			unset($fields[$remove]);

	}		

	//checks if it is a new entry in other_membership_types array	

	if(!isset($member->other_membership_types) || (isset($member->other_membership_types) && empty($member->other_membership_types))) {		

		$member->other_membership_types[] = $fields;		

	}else {		

		//looping through multiple membership object 	

		$saved = false;

		foreach ($member->other_membership_types as $key => $memtypes) {

			//this is to treat each member array(old objects) as member object:(considering backward compatibility)			

			$memtypes = mgm_convert_array_to_memberobj($memtypes, $user_id);			

			//if supplied mgm_object's membership type == existing member object's membership type

			//OR

			//supplied index == $key 

			if( ($memtypes->membership_type == $fields['membership_type']) || (is_numeric($update_index) && $update_index == $key ) ) {			

				//reset if already saved				

				$member->other_membership_types[$key] = $fields;

				$saved = true;

				break;

			}

		}	

		//if didn't find any match insert as new	

		if(!$saved) {			

			$member->other_membership_types[] = $fields; 			

		}

	}

	// save

	$member->save();

	

}



/**

 * get member object for another purchase

 * checks and returns inner mgm_member object if multiple membership types exist within the primary mgm_member object.

 * where $membership_type is the membership to be matched with

 */

function mgm_get_member_another_purchase($user_id, $membership_type = null, $pack_id = null) {

	// get member

	$member = mgm_get_member($user_id);	

	// remove fields

	$remove_fields = array('ID','id', 'name', 'code', 'description', 'saving','custom_fields', 'other_membership_types', 'setting');

	// check

	if (isset($member->other_membership_types) && is_array($member->other_membership_types) && count($member->other_membership_types) > 0){

		// loop

		foreach ($member->other_membership_types as $key => $other_membership_type) {

			// convert

			$member_oth = mgm_convert_array_to_memberobj($other_membership_type, $user_id);			

			// skip guest	

			if (strtolower($member_oth->membership_type) == 'guest' ) continue;

			// match

			$is_match = false;

			

			// if pack_id is passed

			if($pack_id){ 

				// check

				if(isset($member_oth->pack_id) && $member_oth->pack_id == $pack_id ) {

					$is_match = true;

				}

			}else if($membership_type){

			// if membership_type is passed

				// check

				if(isset($member_oth->membership_type) && $member_oth->membership_type == $membership_type){

					$is_match = true;

				}

			}		

			

			// match			

			if ( $is_match ) {				

				// loop

				foreach ($remove_fields as $remove_field) {

					if (isset($member_oth->$remove_field)) unset($member_oth->$remove_field);

				}

				// return an object of the type mgm_member:

				return $member_oth;// what is this object ? array?

			}

		}

	}

	// return	

	return null;

}



/**

 * get purchased membership types

 *

 * @param int user id

 * @param object member

 * @return array membership types  

 */

function mgm_get_subscribed_membershiptypes($user_id, $member = NULL) {

	// get member object

	if(!$member) $member = mgm_get_member($user_id);	

	

	// init

	$membership_types = array();

	// base, first membership type

	if(isset($member->membership_type) && !empty($member->membership_type)) {

		$membership_types[] = $member->membership_type;

	}

	// other membership types

	if(isset($member->other_membership_types) && is_array($member->other_membership_types) && !empty($member->other_membership_types) ) {

		// loop

		foreach ($member->other_membership_types as $key => $val) {

			// create object

			$member_other = mgm_convert_array_to_memberobj($val, $user_id);

			// get 

			if(isset($member_other->membership_type) && !empty($member_other->membership_type) && $member_other->status == MGM_STATUS_ACTIVE){

				$membership_types[] = $member_other->membership_type;

			}	

			// unset

			unset($member_other);

		}

	}

	// return 

	return $membership_types;

}



/**

 * get purchased membership types with name/label

 */

function mgm_get_subscribed_membershiptypes_with_label($user_id, $member = null ) {

	$arr_mtlabel = array();

	$arr_mt = mgm_get_subscribed_membershiptypes($user_id, $member);

	$membership_types_obj = mgm_get_class('membership_types')->membership_types;

	foreach ($membership_types_obj as $type => $label )				

		if(in_array($type, $arr_mt)) {

			$arr_mtlabel[$type] = $label;						

		}

	return $arr_mtlabel;				

}



/**

 * check post pack join

 */

function mgm_check_post_packjoin($member, $post) {

	$return = true;	

	if ($pack_join = $member->join_date) {

		// if hide old content is set in subscription type

		if ($member->hide_old_content) {

		   $post_date = strtotime($post->post_date);

		   // reset no access

		   $return = false;	

		   // join date, TODO, We have to make it take last_active_date or similar for DRIP posts					   

		   if ($pack_join < $post_date) {

			   $return = true;    

		   }

		}

	 }

	 return $return;

}



/**

 * post access delay check

 */

function mgm_check_post_access_delay($member, $user, $access_delay) {

	// default

	$return = true;

	// delay

	$mt_access_delay = (int)$access_delay[$member->membership_type];

	// echo 'mt_access_delay:'. $mt_access_delay;

	// pref	

	$post_delay_preference = mgm_get_class('system')->setting['post_delay_preference'];	

	//Drip feed calculations -issue#: 262

	if( $member->membership_type == 'free' || 

		$member->membership_type == 'trial' ||

		//consider admin created users as they will not be having $member->payment_info->module : issue#: 812

		(!in_array($member->membership_type, array('free', 'trial')) && !isset($member->payment_info->module))

		) {

		$reg     = $user->user_registered;

	}



	//post delay preference pack join date - issue#: 262

	if($post_delay_preference == 'pack_join_date') {



		//getting purchased transactions based on membership level

		$rows = mgm_purchased_transactions($member->membership_type);

		$trans =array();

		// looping the results

		$i=0;

		foreach ($rows as $row){

			$row->data = json_decode($row->data,true);

			extract($row->data);

			// $duration value gets automatically included in the below calculations even if empty($trans)

			unset($duration);

			//storing current user all transactions into an array

			if($user->ID == $user_id){

				$trans[$i]['data'] = $row->data;		

				$trans[$i]['date'] = $row->transaction_dt;		

				$i++;

			}

		}	

		

		$cnt = count($trans);

		$access_d = 0;

		// finding the last join date and previous durations of membership level.

		if (!empty($trans) && $cnt > 0) {

		

			for($i=0;$i<$cnt;$i++){

		

				$last_joindate = $trans[$i]['date'];

		

				extract($trans[$i]['data']); 	

		

				$access_d += $duration;

			}

		}else{

			if(!empty($member->join_date)){

				$last_joindate = date('Y-m-d',$member->join_date);

			}else {

				$last_joindate = $user->user_registered;

			}

		}

		

		// calculating the duration of delay based on his previous membership level duration.		

		$access_d = $access_d - $duration;

		$mt_access_delay = $mt_access_delay - $access_d;	

		$reg = $last_joindate;

	}else {

		//post delay preference register date - issue#: 262

		$reg = $user->user_registered;

	}	



	// delay

	if ($mt_access_delay > 0) {

		// reg

		$reg_at = mktime(0,0,0,substr($reg, 5, 2), substr($reg, 8, 2), substr($reg, 0, 4));

		// echo '<br>' . date('d/m/Y H:i:s', $reg) ;

		$access_at = $reg_at + (86400 * $mt_access_delay);

		// echo '<br>' . date('d/m/Y H:i:s', $user_at) ;

		// check after time

		if ($access_at >= time()) {

			$return = false;

		}

	}

	// return

	return $return;

}



/**

 * check any more subscriptions exist to purchase:

 */

function mgm_check_purchasable_level_exists($user_id, $member = null) {	

	$subscribed_types = mgm_get_subscribed_membershiptypes($user_id, $member);	

	$subscribed_types = array_unique(array_merge($subscribed_types, array('free','trial','guest')));

	$membership_types_obj = mgm_get_class('membership_types')->membership_types;	

	$membership_types_obj = array_unique(array_keys($membership_types_obj));

		

	return ((count($subscribed_types) > 3 && count(array_diff($membership_types_obj, $subscribed_types)) == 0) ? false : true);			

}



/**

 * remove role from user:

 */

function mgm_remove_userroles($user_id, $member, $no_status_check = false) {	

	if($member->status == MGM_STATUS_EXPIRED || $no_status_check) {

		if($member->pack_id){

			$free_role = 'subscriber';

			//find role role assigned to free membership

			$arr_packs = mgm_get_class('subscription_packs')->get_packs();

			foreach ($arr_packs as $p) {

				if($p['membership_type'] == 'free') {

					$free_role = $p['role'];

					break;

				}

			}

			//get role assigned to the pack 

			$pack = mgm_get_class('subscription_packs')->get_pack($member->pack_id);			

			$remove_role = $pack['role'];			

			if($remove_role == $free_role || $remove_role == "")

				return;

			//instanciate role class				

			$obj_role = new mgm_roles();				

			$obj_role->replace_user_role($user_id, $remove_role, $free_role );	

		}

	}

}



/**

 * readjust user object to keep the role - pack balance

 */

function mgm_remove_excess_user_roles($user_id, $add_if_absent = false) {	

	$member = mgm_get_member($user_id);

	$user = new WP_User($user_id);

	$pack_ids = mgm_get_members_packids($member);

	$pack_roles = array();

	$obj_role = new mgm_roles();

	foreach ($pack_ids as $pack_id) {

		$pack = mgm_get_class('subscription_packs')->get_pack($pack_id);

		if(!empty($pack['role']))

			$pack_roles[] = $pack['role'];

	}		

	

	//remove from user object: 

	if(isset($user->roles) && !empty($user->roles) && !empty($pack_roles)) {		

					

		$arr_all_roles = $obj_role->_get_default_roles();

		$arr_mgm_roles = $obj_role->_get_mgm_roles();

		if(!empty($arr_mgm_roles))

			$arr_all_roles = array_merge($arr_all_roles, $arr_mgm_roles);

		foreach ($user->roles as $role) {

			if(!in_array( $role, $pack_roles )) {		

				//make sure delete only default/mgm roles:		

				if(in_array($role, $arr_all_roles)) {

					$user->remove_role($role);													

				}		

			}

		}

	}	

	

	//add if role is absent:

	if(!empty($pack_roles) && $add_if_absent) {

		$user = new WP_User($user_id);

		foreach ($pack_roles as $prole) {

			if(!in_array( $prole, $user->roles )) {

				$obj_role->add_user_role($user_id, $prole, false, false);								

			}

		}

	}

}



/**

 * print roles

 */

function mgm_print_userroles($user_id) {	

	$obj_role = new mgm_roles();				

	$obj_role->print_role($user_id);		

}



/**

 * upload photo

 */

function mgm_photo_file_upload() {	

	$user = wp_get_current_user();

	// init

	$download_file = array();

	// init messages

	$status  = 'error';	

	$message = 'file upload failed';

	$field_name = 'photo';

	$field_type = ($user->ID > 0) ? 'profile' : 'register';	

	

	//issue #1428

	$field_keys = array_keys( $_FILES['mgm_'.$field_type.'_field']['name']);	

	$field_name = $field_keys[0];

	

	if (isset($_FILES['mgm_'.$field_type.'_field']['tmp_name'][$field_name])) {		

		// upload check

		if (is_uploaded_file($_FILES['mgm_'.$field_type.'_field']['tmp_name'][$field_name])) {

			// random filename

			srand(time());

			$uniquename = substr(microtime(),2,8).rand(1000, 9999);

			// paths

			$oldname = strtolower($_FILES['mgm_'.$field_type.'_field']['name'][$field_name]);

			$newname = preg_replace('/(.*)\.(.*)$/i', $uniquename.'.$2', $oldname);	

			$thumb_name = (str_replace('.','_thumb.',$newname));		

			$medium_name = (str_replace('.','_medium.',$newname));	

			$filepath = MGM_FILES_UPLOADED_IMAGE_DIR . $newname;

			$arr_type = explode('/', $_FILES['mgm_'.$field_type.'_field']['type'][$field_name]);			

			if (strtolower($arr_type[0]) == 'image' && in_array(strtolower($arr_type[1]), array('jpg','jpeg','pjpeg','png','x-png','gif'))) {						

				$setting = mgm_get_class('system')->get_setting();

				if (isset($setting['image_size_mb']) && !empty($setting['image_size_mb']))	

					$max_size = $setting['image_size_mb']; 		 

				else

					$max_size = '2'; 

				//check size:	

				if ($_FILES['mgm_'.$field_type.'_field']['size'][$field_name] > 0 && (round($_FILES['mgm_'.$field_type.'_field']['size'][$field_name]/(1024*1024),2)) <= $max_size ) {

					// upload

					if (move_uploaded_file($_FILES['mgm_'.$field_type.'_field']['tmp_name'][$field_name], $filepath)) {	

						// permission

						@chmod($filepath, 0755);		

						$obj_irs = mgm_get_class('image_resize');

						if ($obj_irs->resize_image($filepath,  MGM_FILES_UPLOADED_IMAGE_DIR . $thumb_name ) && 

							$obj_irs->resize_image($filepath,  MGM_FILES_UPLOADED_IMAGE_DIR . $medium_name,'medium' )

							) {

							@chmod(MGM_FILES_UPLOADED_IMAGE_DIR . $thumb_name, 0755);

							@chmod(MGM_FILES_UPLOADED_IMAGE_DIR . $medium_name, 0755);

							@unlink(MGM_FILES_UPLOADED_IMAGE_DIR . $newname);

							

							//delete previous image:

							$user = wp_get_current_user();					

							if ($field_type == 'profile') {

								$member = mgm_get_member($user->ID);						

								if (isset($member->custom_fields->$field_name) && !empty($member->custom_fields->$field_name)) {

									$prev_thumb 	= MGM_FILES_UPLOADED_IMAGE_DIR . basename($member->custom_fields->$field_name);

									$prev_medium 	= MGM_FILES_UPLOADED_IMAGE_DIR . basename(str_replace('_thumb','_medium',$member->custom_fields->$field_name));

									if (file_exists($prev_thumb))

										unlink($prev_thumb);

									if (file_exists($prev_medium))	

										unlink($prev_medium);

								}

							}

							// set download_file				

							$download_file  = array('hidden_field_name' => 'mgm_'.$field_type.'_field['.$field_name.']','file_name' => $medium_name, 'file_url' => MGM_FILES_UPLOADED_IMAGE_URL . $medium_name, 'width' => $setting['medium_image_width'], 'height' => $setting['medium_image_height']);					

							// status

							$status  ='success';	

							$message =__('File uploaded successfully, it will be attached when you save the data.','mgm');

						}else {

							$settings = mgm_get_class('system')->get_setting();

							$width = (isset($settings['medium_image_width']) && !empty($settings['medium_image_width'])) ? $settings['medium_image_width'] : get_option('medium_size_w'); 	

						 	$height = (isset($settings['medium_image_height']) && !empty($settings['medium_image_height'])) ? $settings['medium_image_height'] : get_option('medium_size_h');

							$message = sprintf(__('File upload failed. Please select an image with minimum size: %s.', 'mgm'), ($width . 'x' . $height));

						}

					}

				}else {

					$message = sprintf(__('Please select an image file with size less than %s.','mgm'), $max_size );

				}

			}else {

				$message =__('Please select an image file.','mgm');

			}

		}

	}

	// send ouput		

	@ob_end_clean();	

	// print

	echo json_encode(array('status'=>$status,'message'=>$message, 'upload_file'=>$download_file));

	// end out put		

	@ob_flush();

	exit();

}



/**

 * get package redirect url

 */

function mgm_get_user_package_redirect_url($user_id) {	

	$user = new WP_user($user_id);	

	$member = mgm_get_member($user_id);

	//get highlighted role:

	$role = $user->roles[0];

	$packids 	= mgm_get_members_packids($member);

	$obj_pack 	= mgm_get_class('subscription_packs');

	$obj_mem 	= mgm_get_class('membership_types');

	if(!empty($packids)) {

		foreach ($packids as $pid) {

			$pack = $obj_pack->get_pack($pid);			

			//get login redirect url of the highlighted role:

			if($role == $pack['role']) {				

				$login_redirect_url = $obj_mem->get_login_redirect($pack['membership_type']);	

				if(!empty($login_redirect_url))	

					return $login_redirect_url;		

				break;

			}

		}

	}

	// return

	return null;

}



/**

 * logout redirection url

 */

function mgm_logout_redirect_url() {

	global $current_user;

	

	if(isset($current_user->ID) && $current_user->ID > 0 ) {

		$member = mgm_get_member($current_user->ID);

		$role 		= $current_user->roles[0];

		$packids 	= mgm_get_members_packids($member);

		$obj_pack 	= mgm_get_class('subscription_packs');

		$obj_mem 	= mgm_get_class('membership_types');								

		//get from highlighted packs

		if(!empty($packids)) {

			foreach ($packids as $pid) {

				$pack = $obj_pack->get_pack($pid);			

				//get login redirect url of the highlighted role:				

				if($role == $pack['role']) {			

					$logout_redirect_url = $obj_mem->get_logout_redirect($pack['membership_type']);	

					break;

				}

			}

		}

		

		//get from settings

		if(empty($logout_redirect_url)) {

			$system_obj = mgm_get_class('system');	

			$logout_redirect_url = trim($system_obj->setting['logout_redirect_url']);

			//get site url			

			if(empty($logout_redirect_url)) {

				$logout_redirect_url = get_option('siteurl');

			}

		}

				

		return $logout_redirect_url;					

	}

	

	return false;

}



/**

 * find users with the given package

 */

function mgm_get_users_with_package($pack_id, $uids = array()) {

	global $wpdb;	    

	if(empty($uids)) {

		//from cache

		$uids = wp_cache_get('all_user_ids', 'users');	 

		if(!$uids) {	    

			//$uids = $wpdb->get_col('SELECT ID from ' . $wpdb->users. ' WHERE ID <> 1');

			$uids = mgm_get_all_userids();

			wp_cache_set('all_user_ids', $uids, 'users');

		}

	}



	$arr_pack_users = array();

	foreach ($uids as $uid) {

		$user = mgm_get_member($uid);

		if(isset($user->pack_id) && $user->pack_id == $pack_id ) {

			$arr_pack_users[] = $uid; 

		}

	}

	return $arr_pack_users;

}



/**

 * check a date is valid: can be enhanced

 */

function mgm_is_valid_date($date, $delimiter = '/') {

	$arr_date = explode($delimiter, $date, 3);

	if(count($arr_date) == 3 && is_numeric($arr_date[0]) && is_numeric($arr_date[1]) && is_numeric($arr_date[2]) )

		return true;

		

	return false;	

}



/**

 * get all user ids 

 */

function mgm_get_all_userids($fields = array('ID'), $func = 'get_col') {

	global $wpdb;

	$result = array();

	$limit = 1000;

	$start = 0;

	$count  = $wpdb->get_var('SELECT count(*) from ' . $wpdb->users . ' WHERE ID <> 1');

	if($count) {

		for( $i = $start; $i < $count; $i = $i + $limit ) {

			$result = array_merge($result, mgm_patch_partial_users($i, $limit, $fields, $func));

			//a small delay of 0.01 second 

			usleep(10000);			

		}

	}		

	return $result;

}



/**

 * patch users

 */

function mgm_patch_partial_users($start, $limit, $fields, $func) {

	global $wpdb;

	$qry = 'SELECT '. implode(',', $fields) .' from ' . $wpdb->users . ' WHERE ID <> 1 ORDER BY ID LIMIT '. $start.','.$limit;	

	$result  = $wpdb->$func($qry);	

	return (array) $result;

}



/**

 * wrapper for user option, due to some object serilization bug, system goes to shutdown

 * use flat option datafetch

 */

function mgm_get_user_option($option, $user_id){

	global $wpdb;

	// get var

	$meta_value = $wpdb->get_var("SELECT `meta_value` FROM `{$wpdb->usermeta}` WHERE meta_key = '{$option}' AND user_id ='{$user_id}' LIMIT 1");

	// check

	if($meta_value){

		return $meta_value = maybe_unserialize($meta_value);

	}	

	// error

	return false;	

}



/**

 * encode variable

 *

 * @param int/var $id

 * @return string

 */

function mgm_encode_id($id) {

	// trim

	$id = trim($id);

	// return

	return base64_encode(base64_encode($id));

}



/**

 * decode variable

 *

 * @param int/var $id

 * @return string

 */

function mgm_decode_id($id) {

	// trim

	$id = trim($id);

	// return

	return base64_decode(base64_decode($id));

}



/**

 * delete file

 */

function mgm_delete_file($filepath){

	// check

	if(is_file($filepath)){

		// success

		return unlink($filepath);

	}

	// error

	return false;

}



/**

 * check api access allowed

 */

function mgm_api_access_allowed(){

	// check if disabled

	if(mgm_get_class('system')->setting['rest_server_enabled'] == 'N') return false;

	

	// if all

	if (defined('MGM_API_ALLOW_IP') && MGM_API_ALLOW_IP == 'all') return true;

	

	// in list

	$allowed_ips = explode(',', MGM_API_ALLOW_HOST);

		

	// check

	if(is_array($allowed_ips)){

		// check

		return (in_array($_SERVER['HTTP_HOST'], $allowed_ips));

	}

	

	// false

	return false;

}



/**

 * ellipsize

 */

function mgm_ellipsize($str,$len=50){

	// return if less

	if(strlen($str) < $len) return $str;

	

	// sub

	return $str = substr($str,0,$len). '...';

}



/**

 * return setting: MGM_DATE_FORMAT_INPUT to mysql format

 *

 * @param string $date

 * @return string: mysql date(Y-m-d)

 */

function mgm_format_inputdate_to_mysql($date, $format = null) {

	$delimiters = array(',', '\/', '-', ' ', ';');

	$delimiter = $date_delimiter = $format_delimiter = '/';

	$settings = mgm_get_class('system')->get_setting();

	if(is_null($format))

		$format = MGM_DATE_FORMAT_INPUT;

	

	foreach ($delimiters as $d) {

		//find delimiter in date

		if(preg_match("/$d/", $date)){

			$date_delimiter = stripslashes($d);			

		}

		//find delimiter in format

		if(preg_match("/$d/", $format)){

			$format_delimiter = stripslashes($d);		

		}

	}

	

	$date_splitted = explode($date_delimiter, $date);	

	$format_splitted = explode($format_delimiter, $format);

	

	foreach ($format_splitted as $key => $fs) {

		$fs = trim($fs);

		switch (strtolower($fs)) {

			case 'y':

			case 'yy':

			case 'yyyy':

				$year = isset($date_splitted[$key]) ? $date_splitted[$key]: '';

				break;

			case 'm':

			case 'mm':

				$month = isset($date_splitted[$key]) ? $date_splitted[$key]: '';

				break;	

			case 'd':

			case 'dd':

				$day = isset($date_splitted[$key]) ? $date_splitted[$key]: '';

				break;	

		}

	}	



	//return mysql std date format Y-m-d

	if(isset($year) && isset($month) && isset($day)) {

		$year 	= substr($year, 0, 4);

		$month 	= substr($month, 0, 2);			

		$day 	= substr($day, 0, 2);

		

		return $year.'-'.$month.'-'.$day;

	}

		

	return false; 	

}



/**

 * Convert MGM_DATE_FORMAT_INPUT to date picker format/date value to input field format(MGM_DATE_FORMAT_INPUT)

 * MGM_DATE_FORMAT_INPUT will always be fixed as we accept only numeric date value from input fields

 *

 */

function mgm_get_datepicker_format($type = 'format', $date = null) {

	// system

	$system_obj = mgm_get_class('system');

	//Issue # 680

	$short_format = (!empty($system_obj->setting['date_format_short'])) ? $system_obj->setting['date_format_short'] : MGM_DATE_FORMAT_SHORT;

	$input_format = $short_format;

	if($type == 'format') {		

		//formats supported by jQuery datepicker:

		$delimiters = array(',', '\/', '-', ' ', ';');

		$delimiter = '/';		

		foreach ($delimiters as $d) {

			if(preg_match("/$d/", $input_format)){

				$delimiter = stripslashes($d);

			}

		}

				

		$format_splitted = explode($delimiter, $input_format);		

		foreach ($format_splitted as $key => $fs) {

			$fs = trim($fs);

			switch ($fs) {

				//year

				case 'y':

					$arr_format[] = $fs;

					break;	

				case 'Y':

					$arr_format[] = 'yy';								

					break;

				//month	

				case 'F':

					$arr_format[] = 'MM';

					break;

				case 'm':

					$arr_format[] = 'mm';	

					break;	

				case 'M':

					$arr_format[] = 'M';	

					break;

				case 'n':

					$arr_format[] = 'm';	

					break;	

				//day						

				case 'd':

					$arr_format[] = 'dd';

					break;	

				case 'D':

					$arr_format[] = 'D';

					break;	

				case 'j':

					$arr_format[] = 'd';

					break;	

				case 'l':

					$arr_format[] = 'DD';

					break;			

			}

		}

		if(count($arr_format) < 3)

			$arr_format = array(0 > 'm', 1 => 'd', 2 => 'Y');

			

		return implode($delimiter, $arr_format);

			

	}elseif ($type == 'date' && !is_null($date)) {				

		if (mgm_is_mysql_dateformat($date)) {			

			$conv_date = date( $input_format, strtotime( $date ) ); 			

		}else {//backward compatibility - convert all the previously saved dates to mysql format			

			$date = mgm_format_inputdate_to_mysql($date);			

			$conv_date = date( $input_format, strtotime( $date ) );			

		}

		

		return $conv_date;

	}

}



/**

 * check the given date is in mysql format

 */

function mgm_is_mysql_dateformat($date) {

	$date = trim(str_replace("00:00:00", '', $date));

	$arr_date = explode('-', $date);	

	if(isset($arr_date[2]) && strlen($arr_date[0]) == 4 && strlen($arr_date[1]) == 2 && strlen($arr_date[2]) == 2) {		

		return true;

	}else 

		return false;	

}



/**

 * get date format from settings

 *

 * @param string $format_type the CONSTANT or lowercase var name

 */

function mgm_get_date_format($format_type) {

	// obj

	$system_obj = mgm_get_class('system');

	// init

	$format = '';

	// key

	$key = str_replace('mgm_','',strtolower($format_type));	

	// old key

	$old_key = str_replace('_format','farmat',$key);

	// date_format

	if(isset($system_obj->setting[$key]) && !empty($system_obj->setting[$key])){

		$format = $system_obj->setting[$key];

	}elseif(isset($system_obj->setting[$old_key]) && !empty($system_obj->setting[$old_key])){

	// date_farmat mis spelled old key

		$format = $system_obj->setting[$old_key];

	}else{

		// convert to upper

		$key_const = 'MGM_' . strtoupper($key);// these allows to call the function with both MGM_DATE_FORMAT and date_format

		// check

		if(defined($key_const)){

			$format = constant($key_const);

		}			

	}

	// return	

	return $format; 

}



/**

 * get errors

 */

function mgm_subscription_purchase_errors(){

	// error

	$error_field = mgm_request_var('error_field', '', true); 

	//issue #1250

	$error_field_value = mgm_request_var('error_field_value', '', true); 

	// check

	if(!empty($error_field)) {

		// obj

		$errors = new WP_Error();

		// type

		switch (mgm_request_var('error_type', '', true)) {

			case 'empty':

				$error_string = 'You must provide a ';

				//issue #1250				

				if($error_field == 'Coupon') {			

					$errors->add( $error_field, __( '<strong>ERROR</strong>: '.$error_string, 'mgm' ).$error_field );

				}				

				break;

			case 'invalid':

				$error_string = 'Invalid ';

				//issue #1250				

				if($error_field =='Coupon' && !mgm_request_var('membership_type', '', true)) {			

					$errors->add($error_field, sprintf(__('<strong>ERROR</strong>: Coupon Code "%s" is not valid, use a valid coupon only.','mgm'), $error_field_value));

				}

				if($error_field =='Coupon' && $membership_type=mgm_request_var('membership_type', '', true)) {			

					$errors->add($error_field, sprintf(__('<strong>ERROR</strong>: Coupon Code "%s" is only available with Membership Type "%s".','mgm'), $error_field_value, $membership_type));

				}				

				break;	

		}	

		// add - issue #1250

		if($error_field !='Coupon') {

			//issue #703			

			$errors->add( $error_field, __( '<strong>ERROR</strong>: '.$error_string, 'mgm' ).$error_field );

		}

		// return

		return mgm_set_errors($errors, true);					

	}

	// nothing

	return '';

}



/**

 * Returns timezone formatted current server date/timestamp

 *

 * @param string $format (date format) "Y-m-d" or "Y-m-d H:i:s"

 * @return array

 */

function mgm_get_current_datetime($format = 'Y-m-d', $format_timestamp = true) {

	// init

	$return = array();

	// get mysql time

	$timestamp = strtotime(current_time('mysql', 1));		

	// format:

	$return['date'] = date($format, $timestamp);

	// get formatted timestamp

	$return['timestamp']= $format_timestamp ? strtotime($return['date']) : $timestamp;	

	// return

	return $return; 

}



/**

 * do redirect

 */

function mgm_redirect($location, $status = 302, $type='header', $return=false){	

	//if default value is not overridden,read from settings:

	if($type == 'header' && $redirection_method = mgm_get_setting('redirection_method')) {

		$type = $redirection_method;

	}

	// meta redirect

	switch($type){		

		case 'javascript':			

			// only if no headers

			$header = sprintf('<script language="javascript">window.location = "%s";</script>', $location); 

			// return

			if($return) return $header;

			// print

			print $header; exit;			

		break;

		case 'meta':			

			// only if no headers

			if(!headers_sent()){

			// print

			    $header = sprintf('<meta http-equiv="refresh" content="1;url=%s" />', $location);

				// return

				if($return) return $header;

				// print

				print $header; exit;	

			}

		break;

	}	

	// default always
	// 
	//echo 	$location; exit;

	wp_redirect($location, $status); exit;

}



/**

 * delete all transient cache

 *

 * @param none

 * @return none

 */

 function mgm_delete_transients(){

 	// keys

	$keys = array('mgm_current_version','mgm_current_messages','mgm_subscription_status');

	// loop

	foreach($keys as $key){

	// delete

		delete_transient($key);

	}

 }

 

 /**

  * Encrypt a string with the supplied private key

  *

  * @param unknown_type $string

  * @param unknown_type $private_key

  * @return unknown

  */

 function mgm_encrypt($string, $private_key = '1q2w3e4r5t6y7u8i9o0p') {

 	//can be read from settings 		

 	$obk_secrypt = new mgm_secrypt();

	// return

 	return $obk_secrypt->Encrypt($string, $private_key);

 }

 

  /**

  * decrypt a string with the supplied private key(2way encryption)

  *

  * @param string $string

  * @param string $private_key

  * @return string

  */

 function mgm_decrypt($string, $private_key = '1q2w3e4r5t6y7u8i9o0p') {

 	// can be read from settings 		

 	$obk_secrypt = new mgm_secrypt();

	// return

 	return $obk_secrypt->Decrypt($string, $private_key);

 }

 

 /**

  * Encrypt password using private key 

  *

  * @param string $password

  * @param int $user_id

  * @return string

  */

 function mgm_encrypt_password($password, $user_id, $rss_token=NULL) {  

 	// member	 	 	

	if(!$rss_token){

		$member = mgm_get_member($user_id);

		$rss_token = $member->rss_token;

	}

	// return 

 	return mgm_encrypt($password,  $rss_token . '_' . $user_id);

 }

 

 /**

  * Decrypt password using private key

  *

  * @param string $en_password(can either be plaintext or encrypted string)

  * @param int $user_id

  * @return string

  */

 function mgm_decrypt_password($enc_password, $user_id, $rss_token=NULL) { 			

	// member	 	 	

	if(!$rss_token){

		$member = mgm_get_member($user_id);

		$rss_token = $member->rss_token;

	}

	// log

	// mgm_log('encrypted: ' . $enc_password, __FUNCTION__);

 	// decrypt password

 	if($dec_password = mgm_decrypt($enc_password, $rss_token . '_' . $user_id)) {

	// return

		// log

		// mgm_log('dycrypted: ' . $dec_password, __FUNCTION__);

		return $dec_password;

	}	

 	// return default 

 	return $enc_password;  	  		

 }

 

 /**

  * generate guest purchase purchase options

  *

  * @param object post 

  * @patam string message, pre_button|pre_register

  * @return string html

  */

 function mgm_get_post_purchase_options($post, $message='pre_button'){

 	// post ot post id

	if(!is_object($post) && is_numeric($post)){

		$post = & get_post($post);

	}	

		

 	// get post purchase options

	$post_obj = mgm_get_post($post->ID);

	

	// membership_types

	$membership_types_obj = mgm_get_class('mgm_membership_types');	

	// system

	$system_obj = mgm_get_class('system');



	//Issue #794

	$currency = $system_obj->get_setting('currency');	

	// symbol

	if(($currency_symbol = mgm_get_currency_symbols($currency)) != $currency){

		$purchase_cost =  $currency_symbol . mgm_convert_to_currency($post_obj->purchase_cost);	

	}else{

		$purchase_cost =  mgm_convert_to_currency($post_obj->purchase_cost) . ' ' . $currency;	

	}

	

	// types

	$membership_types = array();

	// acc

	$accessible_membership_types = $post_obj->get_access_membership_types();

	// loop

	foreach($accessible_membership_types as $membership_type){

		// url

		$membership_register_url = mgm_get_custom_url('register', false, array('membership'=>base64_encode($membership_type),'post_id'=>$post->ID));

		// name

		$membership_name = $membership_types_obj->get_type_name($membership_type);

		// set

		$membership_types[] = sprintf('<li><a href="%s" target="_blank">%s</a></li>', $membership_register_url, $membership_name);

	}

	

	// template

	$template = mgm_stripslashes_deep($system_obj->get_template('text_guest_purchase_' . $message, array(), true));// the template is twice used				

	

	// replace tags

	$html = str_replace('[post_title]', $post->post_title, $template);

	//Issue #794

	$html = str_replace('[purchase_cost]', $purchase_cost, $html);

	// membership_types_options

	$membership_types_options = (count($membership_types)>0) ? sprintf('<ul>%s</ul>', implode('',$membership_types)) : __('None available<br>', 'mgm');

	// set

	$html = str_replace('[membership_types]', $membership_types_options, $html);	

	// return 

	return $html;

 }

 

 /**

  * generate guest purchase post form 

  *

  * @param int post id

  * @return string html

  */

 function mgm_guest_purchase_post_form($post_id){

 	global $wpdb; 	

	

	// post

	$post = & get_post($post_id);

	

	// validate

	if(!$post->ID){

		return __('Bad data','mgm');

	}	

	

	// apply filter html

	$html = apply_filters('mgm_guest_purchase_post_form_pre_button_html', mgm_get_post_purchase_options($post));

	

	// return button

	return $html .= mgm_get_post_purchase_button($post->ID, false, true);	

 }

 

 /**

  * generate guest purchase post form - issue #1396

  *

  * @param int post id

  * @return string html

  */

 function mgm_guest_purchase_postpack_form($postpack_id,$postpack_post_id){

 	global $wpdb; 

	

	// get postpack

	$postpack = mgm_get_postpack($postpack_id);	

	

	// validate

	if(!$postpack->id){

		return __('Bad data','mgm');

	}

	

	// return - issue #1396

	return mgm_get_postpack_template($postpack_id, true,$postpack_post_id);

 }	

 

 /**

  * generate guest purchase form 

  */

  function mgm_guest_purchase_form(){  	

	// post

	if(isset($_GET['post_id'])){

	// post

		return mgm_guest_purchase_post_form((int)strip_tags($_GET['post_id']));

	}elseif(isset($_GET['postpack_id'])){

	// postpack - issue #1396

		return mgm_guest_purchase_postpack_form((int)strip_tags($_GET['postpack_id']),(int)strip_tags($_GET['postpack_post_id']));

	}	

	// nothing

	return __('No Post or PostPack', 'mgm');

  }

  

  /**

   * get status css class 

   *

   * @param sting status

   * @return string classname

   */

  function mgm_get_status_css_class($status){

  	// statuses

	$statuses = array(

		MGM_STATUS_NULL            => 's-inactive',

	 	MGM_STATUS_ACTIVE          => 's-active',

	 	MGM_STATUS_EXPIRED         => 's-expired',

	  	MGM_STATUS_PENDING         => 's-pending',

	  	MGM_STATUS_TRIAL_EXPIRED   => 's-expired-trial',

	  	MGM_STATUS_CANCELLED       => 's-canceled',

	  	MGM_STATUS_ERROR           => 's-error',

	  	MGM_STATUS_AWAITING_CANCEL => 's-canceled-awaiting'

	);

	

	// return

	return isset($statuses[$status]) ? $statuses[$status] : 's-inactive';				  

  }

  

/**

 * get subscription status

 *

 * @param booean $value

 * @return array $statuses

 */

function mgm_get_subscription_statuses($value=false){

	// init

	$statuses = array();

	// loop

	foreach(get_defined_constants() as $constant=>$value){

		// match

		if(preg_match('/^MGM_STATUS_/', $constant)){

			$statuses[] = ($value) ? constant($constant) : $constant;

		}

	}

	// return

	return $statuses;

}

  

/**

 * quick fetch defined schedule

 * 

 * @param none

 * @return array

 */

 function mgm_get_defined_schedules(){

  	// schedular

	$sch = mgm_get_class('schedular');

	// return 

	return $sch->get_defined_schedules();

  }



/**

 * check if a plugin active

 *

 * @param string plugin base

 * @return bool 

 */

 function mgm_is_plugin_active($plugin){ 

 	// lib

 	if(!function_exists('is_plugin_active')){

 		@require_once( ABSPATH . '/wp-admin/includes/plugin.php');

	}

	// plugin	

 	$plugin = untrailingslashit($plugin);// remove ending /

	

	// check

	return (function_exists('is_plugin_active')) ? is_plugin_active($plugin) : false;

 }

 

 /** 

  * calc current rebill cycle expiry date

  *

  * @param string cycle expiry date 

  * @param string date add expression

  * @return string date

  */

 function mgm_get_current_rebill_cycle_expiry_date($expire_date, $date_add='+7 DAY'){  	

	// time

	$current_datetime = mgm_get_current_datetime('Y-m-d H:i:s');

	// today

	$timestamp = $current_datetime['timestamp'];

	// loop

	while( strtotime($expire_date) < $timestamp ){	

		// cycle expire date

		$cycle_expire_date = date('Y-m-d H:i:s', strtotime($date_add, strtotime($expire_date)));

		// check

		if(strtotime($cycle_expire_date) < $timestamp) $expire_date = $cycle_expire_date; else break;				

	}

	// double check, if CRON runs in between the dates, except normal 1 day after expiry process, extend one unit in future

	if(strtotime($expire_date) < $timestamp){

		$expire_date = date('Y-m-d H:i:s', strtotime($date_add, strtotime($expire_date)));

	}	

	// return 

	return $expire_date;

 }

 

 /**

  * check Buddypress form is submitted or not

  * @return boolean

  */

 function mgm_is_bp_submitted() {

 	return isset( $_POST['signup_submit'] );

 }

 

/**

 * get singular form of string

 *

 * @param string 

 * @return string singular

 */	 

 function mgm_singular($str){

	$str = strtolower(trim($str));

	$end = substr($str, -3);



	if ($end == 'ies')

	{

		$str = substr($str, 0, strlen($str)-3).'y';

	}

	elseif ($end == 'ses')

	{

		$str = substr($str, 0, strlen($str)-2);

	}

	else

	{

		$end = substr($str, -1);

	

		if ($end == 's')

		{

			$str = substr($str, 0, strlen($str)-1);

		}

	}	

 	return $str;

 }



/**

 * get plural form of string

 *

 * @param string 

 * @param boolean to force 

 * @return string plural

 */	   

 function mgm_plural($str, $force = FALSE)

	{

		$str = strtolower(trim($str));

		$end = substr($str, -1);



		if ($end == 'y')

		{

			// Y preceded by vowel => regular plural

			$vowels = array('a', 'e', 'i', 'o', 'u');

			$str = in_array(substr($str, -2, 1), $vowels) ? $str.'s' : substr($str, 0, -1).'ies';

		}

		elseif ($end == 's')

		{

			if ($force == TRUE)

			{

				$str .= 'es';

			}

		}

		else

		{

			$str .= 's';

		}



		return $str;

	}

	

/**

 * get user role by user id

 *

 * @param int user id

 * @return string role pack

 */	 	

 function mgm_get_user_role ($user_id) {	

	// user	 

	$user = new WP_User( $user_id );

	// return

	return (is_array($user->roles) && !empty($user->roles)) ? array_shift($user->roles) : __('n/a','mgm');

 }



/**

 * get pack by pack id

 *

 * @param int pack id

 * @return array pack

 */	

function mgm_get_pack($pack_id){

	return mgm_get_class('subscription_packs')->get_pack($pack_id);

}	



/**

 * get pack duration type expr

 *

 * @param char duration type

 * @return string expr

 */	

function mgm_get_pack_duration_expr($duration_type='d'){

	return mgm_get_class('subscription_packs')->get_pack_duration_expr($duration_type);

}



/**

 * update payment state check

 *

 * @param int user id

 * @type string type ( cron, login, manual, notify, api )

 * @todo check only modules that supports rebill status check via api

 */	

function mgm_update_payment_check_state($user_id, $type='cron'){

	// get member

	$member = mgm_get_member($user_id);

	// update checked

	$member->last_payment_check_type = $type;

	$member->last_payment_check_date = date('Y-m-d');			

	// save

	$member->save();

}



/**

 * get css group

 *

 * @param string section

 * @return string group

 */	

function mgm_get_css_group($section='site'){

	// load

	if(!$css_group = mgm_get_class('system')->setting['css_settings']){

		$css_group = 'default';

	} 

	// return 

	return $css_group;	

}



/**

 * get pack date cycle

 *

 * @param int pack id

 * @param object member

 * @return string date expression

 */	

function mgm_get_pack_cycle_date($pack_id, $member){	

	// packs

	$s_packs = mgm_get_class('subscription_packs');

	// durations

	$duration_exprs = $s_packs->get_duration_exprs();		

	// check

	if((int)$pack_id > 0 ){				

		// get member subscribed  pack

		$pack = $s_packs->get_pack($pack_id);	

		if(!empty($pack)){	

			if(isset($pack['duration']) && isset($pack['duration_type']) && isset($duration_exprs[$pack['duration_type']]) && (int)$pack['duration'] > 0){

				// return

				if(in_array($pack['duration_type'], array_keys($duration_exprs))) {// take only date exprs

					return sprintf('+ %d %s', (int)$pack['duration'], $duration_exprs[$pack['duration_type']]);	

				}

			}

		}

		if(empty($pack)){			

			$pack = $s_packs->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);

			if(isset($pack['id'])){

				if(isset($pack['duration']) && isset($pack['duration_type']) && isset($duration_exprs[$pack['duration_type']]) && (int)$pack['duration'] > 0){

					if(in_array($pack['duration_type'], array_keys($duration_exprs))) {// take only date exprs	

						return sprintf('+ %d %s', (int)$pack['duration'], $duration_exprs[$pack['duration_type']]);		

					}	

				}

			}						

		}

	}	

	// check saved object	- issue #1134					

	if(isset($member->duration) && isset($member->duration_type) && isset($duration_exprs[$member->duration_type]) && (int)$member->duration > 0){

		// return

		if(in_array($member->duration_type, array_keys($duration_exprs))) {// take only date exprs

			return sprintf('+ %d %s', (int)$member->duration, $duration_exprs[$member->duration_type]);	

		}

	}		

	

	// error					

	return false;						

}



/**

 * compare wp version

 */

function mgm_compare_wp_version($version = '3.1', $operator = '<'){

	global $wp_version;

	// return

	return (version_compare($wp_version, $version, $operator));

}



/**

 * compare mm version

 */

function mgm_compare_version($version = '1.8.31', $operator = '<'){

	// version

	$mgm_version = get_option( 'mgm_version' );

	// return

	return (version_compare($mgm_version, $version, $operator));

}



/**

 * Save member object after Multiple Membership Upgrade

 * replace upgrade mgm_member with old mgm_member

 * @param array $data

 * @param int $trans_id

 */

function mgm_multiple_upgrade_save_memberobject($data, $trans_id) {

	

	if (empty($data['multiple_upgrade_prev_packid']))

		return;

		

	if (isset($data['user_id'])) {

		$member = mgm_get_member($data['user_id']);

		if (isset($member->other_membership_types) && is_array($member->other_membership_types) && count($member->other_membership_types) > 0){

			// loop

			$prev_index = null;

			$new_index = null;

			foreach ($member->other_membership_types as $index => $member) {

				$member = mgm_convert_array_to_memberobj($member, $data['user_id']);			

				//skip default values:

				//if(strtolower($memtypes->membership_type) == 'guest' || $memtypes->status == MGM_STATUS_NULL )	continue;			

				if (strtolower($member->membership_type) == 'guest' ) continue;

				// find previous member object index to be replaced

				if ($data['multiple_upgrade_prev_packid'] == $member->pack_id)

					$prev_index = $index;

				// find newsly inserted member object index	

				// consider only for success state

				if ($member->status == MGM_STATUS_ACTIVE && $data['pack_id'] == $member->pack_id)

					$new_index = $index;	

			}			

			if (is_numeric($prev_index) && is_numeric($new_index)) {

				// swap old with new

				$member->other_membership_types[$prev_index] = $member->other_membership_types[$new_index];

				// remove old member object as it is an upgrade

				unset($member->other_membership_types[$new_index]); 

				// save all

				$member->save();

				

				// update transacton - reset multiple_upgrade_prev_packid

				// to not consider next time

				// get

				$trans = mgm_get_transaction($trans_id);	

				// reset			

				$trans['data']['multiple_upgrade_prev_packid'] = '';	

				// update					

				mgm_update_transaction(array('data'=>json_encode($trans['data'])), $trans_id); 

			}

		}

	}

}



/**

 * get activation status

 */

function mgm_is_activated(){

	return mgm_get_class('auth')->verify();

}



/**

 * Check whether current page is an mgm custom url

 * @return boolean : true if custom page loaded

 */

function mgm_is_custompage_loaded() {

	$custompage_loaded = false;

	$current_url = mgm_current_url();

	$current_path = trim(parse_url($current_url, PHP_URL_PATH));

	$current_path = trailingslashit($current_path);

	$array_customurl = array('login', 'register', 'lostpassword', 'profile', 'transactions', 'membership_details', 'membership_contents', 'purchase_content');

	if (!empty($current_path))

		foreach ($array_customurl as $custrl) {

			$custrl = mgm_get_custom_url($custrl);

			$custrl = trailingslashit($custrl);

			$uri = trim(parse_url($custrl, PHP_URL_PATH));

			if( $uri == $current_path ) {

				$custompage_loaded = true;

				break;

			}					

		}

	

	return $custompage_loaded;

}







/**

 * Replace email short tags with contnet 

 *

 * @param string $message and $user_id 

 * @return string $message

 */

function mgm_replace_email_tags($message,$user_id=NULL) {

	global $wpdb;

	// has user

	if(!$user_id){

		// cusrrent user

		$current_user = wp_get_current_user();

		// set 

		$user_id = $current_user->ID;

	}else {

		// get user

		$current_user = new WP_User($user_id);	

	}

	// mgm member

	$member = mgm_get_member($user_id);

	$system_obj = mgm_get_class('system');	

	// user

	if ($user_id > 0) {	

		// display name

		if(isset($current_user->first_name) && !empty($current_user->first_name)){

			$name = $current_user->first_name;

		}elseif(isset($member->custom_fields->first_name) && !empty($member->custom_fields->first_name)){

			$name = $member->custom_fields->first_name;

		}else{					

		 	$name = $current_user->display_name;

		}

		//first name

		if(isset($member->custom_fields->first_name) && !empty($member->custom_fields->first_name)){

			$first_name = $member->custom_fields->first_name;

		}

		//last name

		if(isset($member->custom_fields->last_name) && !empty($member->custom_fields->last_name)){

			$last_name = $member->custom_fields->last_name;

		}

		//user name

		$username = $current_user->user_login;

		//password

		$password = mgm_decrypt_password($member->user_password, $user_id);

		//client email

		$email = $current_user->user_email;

		//reason email

		$reason = $member->status_str;

		//expire_date	

		$expire_date = $member->expire_date;

		//just fetch the key from db as it is already updated

		$key = $current_user->user_activation_key;

		//passwordlink

		$passwordlink = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($username), 'login');

		//amount email -#issue 1069

		$amount = $member->amount;

		//membership_type email

		$membership_type = $member->membership_type;

	}

	//admin_email

	$admin_email = $system_obj->get_setting('admin_email');

	// blog name

	$blogname = get_option('blogname');

	// siteurl name

	$siteurl = network_site_url();

	//login_url

	$login_url = wp_login_url();

	//loginurl

	$loginurl = mgm_get_custom_url('login');

	

	// issue #1177

	$currency_sign = mgm_get_currency_symbols($system_obj->setting['currency']);			

	

	//email short tags array

	$tags = array('name','username','password','login_url','admin_email','email','blogname',

				  'siteurl','loginurl','reason','expire_date','passwordlink','first_name','last_name',

				  'amount','membership_type','currency_sign');

	// loop

	foreach($tags as $tag){

		// check

		if(!isset(${$tag})) ${$tag} = '';

		// set

		$message = str_replace('['.$tag.']', ${$tag}, $message);

	}

	// return

	return $message;

}



/**

 * return json decoded data of json encoded string provided

 * false if not

 *

 * @since 2.6 

 * @param string $json

 * @return mixed $value

 */

function mgm_is_json_encoded($input){

	// check string

	if(!is_string($input)) return $input;

	// decoded	

	$decoded_output = json_decode($input, true);

	if (function_exists('json_last_error')) {

		// check last error	

		return (json_last_error() == JSON_ERROR_NONE) ? $decoded_output : $input;

	}

	else {

		return $decoded_output;

	}

}



/**

 * return setting with validation

 *

 * @since 2.6 

 * @param string $name

 * @return mixed $value

 */

function mgm_get_setting($name, $default=false){

	// return

	return mgm_get_class('system')->get_setting($name, $default);

}



/**

 * return site url on ssl verification

 *

 * @since 2.6

 * @param string $url

 * @param bool $transaction_page

 * @return string $url

 */

function mgm_site_url($url, $transaction_page=false){

	// payment

	$is_transaction_page = ( $transaction_page &&  bool_from_yn(mgm_get_setting('use_ssl_paymentpage')) ) ? true : false;

	// ssl

	if(is_ssl() || $is_transaction_page ){

	// replace

		$url = preg_replace('|^http:|', 'https:', $url); // preg_replace('/^http:/', 'https:', $url);		

	}

	// return 

	return $url;

}



/**

 * verify array shift and return null if not valid

 *

 * @since 2.6

 * @param array $array

 * @return mixed first element or NULL

 */

function mgm_array_shift($array){

	// return

	return is_array($array) ? array_shift($array) : NULL;

}



/**

 * Check autoresponder_notified flag for member objet, thereby ensure that AR subscription will be done only once 

 * @param unknown_type $member

 * @param unknown_type $trans_id

 */

function mgm_subscribe_to_autoresponder(& $member, $trans_id = null) {	

	// if an active user and ready to be subscribed to AR

	if (isset($member->subscribed) && bool_from_yn($member->subscribed) && $member->status == MGM_STATUS_ACTIVE) {

		// for backward compatibility

		// To skip skip previous transactions/IPN posts		

		if (!is_null($trans_id)) {

			// mgm_log($trans_id .' :checking trans date', __FUNCTION__);

			$transaction = mgm_get_transaction($trans_id);

			// check transaction date+1 day to skip old transaction IPN posts

			if (strtotime('+1 day', strtotime($transaction['transaction_dt'])) < strtotime('now')) {				

				return false;

			}

		}

		

		// check acknowledge flag. If not marked as Y, allow to subscribe

		if (isset($member->autoresponder_notified) && !bool_from_yn($member->autoresponder_notified)) {

			// set flag as notified

			$member->autoresponder_notified = 'Y';	

			// return		

			return true;

		//issue #1276

		}else if($member->autoresponder_notified == 'Y') {

			return true;

		}

	}	

	// return

	return false;

}



/**

 * strip short code

 */

function mgm_strip_shortcode($content, $length=200){

	// shortcode regx

	$pattern = get_shortcode_regex();

	// stip shortcodes

	$content  = substr(strip_tags(preg_replace('/'.$pattern.'/s', '', $content)), 0, $length);				

	$content .= (strlen($content) > $length ? '...' : '');	

	return $content;

}



/**

 * get current url

 */

function mgm_get_current_url(){

	global $post;

	// check is a post

	if(isset($post->ID) && (int)$post->ID > 0){

		return get_permalink($post->ID);

	}

	// uri, strip all tags #1233

	$uri = strip_tags(urldecode($_SERVER['REQUEST_URI']));

	// return

	return esc_url('http' . ($_SERVER['SERVER_PORT'] == 443 ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $uri);

}



/**

 * Checks if the logged in user is in $user_ids array

 * @param array $user_ids

 * @return boolean 

 */

function mgm_user_id_is($user_ids) {	

	$current_user = wp_get_current_user();	

	if (count($user_ids) && is_numeric($current_user->ID) && in_array($current_user->ID, $user_ids) ) {		

		return true;

	}

	

	return false;

}

/**

 * Checks if the logged in user pack is in $pack_ids array

 * @param array $pack_ids

 * @return boolean 

 */

function mgm_user_pack_is($pack_ids) {		

	$current_user = wp_get_current_user();		

	$user_pack_ids = array();

	//check 

	if (count($pack_ids) && is_numeric($current_user->ID)) {		

		// get

		$member = mgm_get_member($current_user->ID);

		$user_pack_ids [] = $member->pack_id;				

		// check if any multiple levels exist:

		if(isset($member->other_membership_types) && is_array($member->other_membership_types) && count($member->other_membership_types) > 0) {

			// loop

			foreach ($member->other_membership_types as $memtypes) {

				if(is_numeric($memtypes['pack_id']))

					$user_pack_ids[] = $memtypes['pack_id'];

			}

		}			

		//check if any pack id matches

		if(array_intersect($pack_ids,$user_pack_ids)){

			return true;

		}

	}	

	return false;

}

/**

 * convert quotes to entities

 */

function mgm_quotes_to_entities($str){

	// return

	return str_replace(array("\'","\"","'",'"'), array("&#39;","&quot;","&#39;","&quot;"), $str);

}



/**

 * strip quotes

 */

function mgm_strip_quotes($str){

	return str_replace(array('"', "'"), '', $str);

}



/**

 * unset if set

 */

function mgm_unset_if(){

	$args = func_get_args();

	if(count($args) > 0){

		foreach($args as $arg){

			if(isset($arg)){

				unset($arg);

			}

		}

	}

}



/**

 * try auto login if bypassed

 */

function mgm_try_auto_login(){

	// check

	if(isset($_GET['auto_login']) && isset($_GET['trans_ref']) && isset($_GET['redirect_to'])){

		// read transaction id

		if($id = mgm_decode_id(strip_tags($_GET['trans_ref']))){

			// process login

			if( mgm_auto_login($id) ){ 

				// no headers

				if(!headers_sent()){ 				

					@header(sprintf('Refresh: %d;url=%s', 5, strip_tags($_GET['redirect_to'])));

				}else{

					return sprintf('<script language="javascript">window.setTimeout(function(){window.location.href="%s";}, %d)</script>', strip_tags($_GET['redirect_to']), 5 * 5);	

				}

				// exit;	

				exit;

			}	

		}

	}

}



/** 

 * get mgm table names

 */

function mgm_get_tables(){

	// constants	

	$constants = get_defined_constants();		

	$tables = array();

	// loop

	foreach($constants as $constant=>$value){

		// match

		if(preg_match('/^TBL\_MGM\_/',$constant)){

			$tables[] = $value;

		}

	}

	// return

	return $tables;

}



/** 

 * get mgm table names

 */

function mgm_get_wp_tables(){

	global $wpdb;

	// init

	$tables = array();

	// loop

	if($all_tables = $wpdb->get_results(  "SHOW TABLES FROM " . $wpdb->dbname, ARRAY_A )){

		// loop

		foreach($all_tables as $table){

			$tables[] = current($table);

		}

	}

	// return

	return $tables;

}	

/**

 * find and update table field collation

 */

function mgm_reset_tables_collate($wpdb_tables=NULL){

	global $wpdb;

	// charset

	$charset = ( ! empty($wpdb->charset) ) ? $wpdb->charset : 'utf8';

	// collate

	$collate = ( ! empty($wpdb->collate) ) ? $wpdb->collate : 'utf8_general_ci';		

	// sql

	$sql_regx = 'ALTER TABLE `%s` CHANGE `%s` `%s` %s CHARACTER SET %s COLLATE %s %s;';

	// db tables

	if(!$wpdb_tables) $wpdb_tables = mgm_get_wp_tables();

	// loop

	foreach (mgm_get_tables() as $table){	

		// check if exists

		if(!in_array($table, $wpdb_tables)) continue;		 			

		// query

		$fields = $wpdb->get_results(sprintf('SHOW FULL COLUMNS FROM `%s`',$table), ARRAY_A); 		

		// loop

		foreach($fields as $field){

			// check			

			if(isset($field['Collation'])){	

				// check

				if(!empty($field['Collation']) && $field['Collation'] != $collate){													

					// null

					$null = ($field['Null'] == 'NO') ? 'NOT NULL' : 'NULL DEFAULT NULL';	  	

					// sql 						

					$sql = sprintf($sql_regx, $table, $field['Field'], $field['Field'], $field['Type'], $charset, $collate, $null);					

					// update 	

					$wpdb->query($sql);						 

				}

			}

		}			

	} 	

	// return

	return true;

}



/**

 * get charset collate

 */

function mgm_get_charset_collate(){

	global $wpdb;



	$charset_collate = '';



	if ( ! empty($wpdb->charset) )

		$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";

	if ( ! empty($wpdb->collate) )

		$charset_collate .= " COLLATE {$wpdb->collate}";

	

	// return	

	return $charset_collate;	

}



/**

 * Check editor capabilities against loggedin user capabilities

 * @param $user_id

 */

function mgm_has_preview_permissions($user_id, $post_type) {	

	if ($user_id > 0) {

		$obj_role = new mgm_roles();

		// Assume the user has the below capabilities

		$cap = null;

		// Capabilities corresponding to edit post/edit page

		if ($post_type == 'post')

			$cap = 'edit_posts';

		elseif ($post_type == 'page')

			$cap = 'edit_pages';	

		if ($roles = $obj_role->get_user_role($user_id)) {

			// Check user role ha the capability to edit post/page

			$caps = $obj_role->get_all_capabilities($roles);

			if (!empty($caps) && in_array($cap, $caps)) {

				return true;			

			}

		}

	}

	return false;

}



/**

 * implode associative array

 *

 * @param string $glue

 * @param array $pieces

 * @return string $string

 */

function mgm_implode_a($glue, $keys, $values){

	// a

	$a = array();

	// loop

	for($i = 0; $i < count($keys) ;  $i++ ){

		$a[] = ($keys[$i]. '=' . $values[$i]); 

	}

	// return 

	return implode($glue, $a);

}



/**

 * Check transaction page content is to be loaded if buddypress and mgm shares registration page.

 */

function mgm_is_bp_registration() {

	// If Buddypress is enabled

	if (mgm_is_plugin_active('buddypress/bp-loader.php')) {

		// If setting is enabled - is registration urls same for BP and MGM

		if (bool_from_yn(mgm_get_setting('share_registration_url_with_bp'))) {

			// if current url is registration url and transaction page is to be loaded

			if (false !== strpos(mgm_current_url(), untrailingslashit(mgm_get_setting('register_url'))) &&

				isset($_GET['method']) && preg_match('/^payment/',$_GET['method'])

			) {

				return true;

			} 

		}

	}

	return false;

}



/**

 */

 function mgm_security_error($action=NULL){

 	$title = __( 'MagicMembers Failure Notice', 'mgm' );

	$html  = __( 'Security breach, your attempt to access a secure form has been denied!', 'mgm');

	wp_die( $html, $title, array('response' => 403) );

 }



/**

 * Retrieve variable in the WP_Query class.

 *

 * @see WP_Query::get()

 * @uses $wp_query

 *

 * @param string $var The variable key to retrieve.

 * @return mixed

 */

 function mgm_get_query_var($var){

 	global $wp_query;	

	if(!is_object($wp_query)){

		$wp_query = new WP_Query();

	}	

 	if (!is_null($wp_query)) {	

 		return $wp_query->get($var);		

 	} 	

	return '';

 }

 

/**

 * Retrieve post content in the WP_Query class.

 *

 * @see WP_Query::get()

 * @uses $wp_query

 *

 * @param void

 * @return string

 */

 function mgm_get_query_post_content(){

 	global $wp_query;	

	if(!is_object($wp_query)){

		$wp_query = new WP_Query();

	}	

 	if (is_object($wp_query->post)) {	

 		return $wp_query->post->post_content;		

 	} 	

	return '';

 }



/**

 * get payment page query vars

 * mgm_query_vars

 */ 

 function mgm_get_payment_page_query_vars(){

 	// set

	return array('purchase_subscription','purchase_content','transactions','purchase','payments'); // subscribe deprecated

	// array('payments','purchase_subscription','purchase','transactions')

 }



/**

 * Fetch widget related data from db

 * @param string $type

 *   membership_count/status_count

 * @return array()

 */

 function mgm_get_dashboard_widget_data($type) {

 	// Fetch mgm_widget_data from db

 	// $data = get_option('mgm_widget_data');

 	// use transient to save cron load

 	if(!$widget_data = get_transient('mgm_dashboard_widget_data')){	

 		// generate

 		$widget_data = mgm_set_dashboard_widget_data();	

 		// set cache		

		set_transient('mgm_dashboard_widget_data', $widget_data, mgm_time2second('1 HOUR'));

 	}	



 	// return asked $type of data

 	if (isset($widget_data[$type]))

 		return $widget_data[$type];

 

 	return array();

 }



/**

 * generate dashboard widget data

 * 

 * @param void

 * @return array

 * @since 2.7

 */

 function mgm_set_dashboard_widget_data(){

 	// obj

	$mtypes_obj = mgm_get_class('membership_types');	

	// get membership counts

	$membership_count = mgm_get_membershiptype_users_count();

	// init

	$memberships_c = array();

	// loop through and update $arr_membership with count

	foreach ($mtypes_obj->membership_types as $type_code => $type_name) {

		// store

		$memberships_c[] = array('count' => $membership_count[$type_code], 

								 'name'  => mgm_stripslashes_deep($type_name),

								 'code'  => $type_code);

	}	

	// Membership count ends

	

	// Status count starts

	// get status counts

	$statuses = mgm_get_subscription_statuses(true);

	$status_count = mgm_get_subscription_status_users_count($statuses);

	$statuses_c = array();

	// loop

	foreach ($statuses as $status) {

		// store

		$statuses_c[] = array('count' => (isset($status_count[$status]) ? $status_count[$status] : 0), 'name' => $status, 'css_class' => mgm_get_status_css_class($status));

	}	

		

	// Current time

	$time = mgm_get_current_datetime('Y-m-d H:i:s');

	// Form data

	return $widget_data = array('membership_count' => $memberships_c, 'status_count' => $statuses_c, 'updated_time' => $time['date']);	

 }



/**

 * Check the mgm_member is in active state

 * 

 * @param int $user_id

 * @return boolean

 */ 

 function mgm_is_member_active($user_id) {

 	if ($user_id) {

	 	$member = mgm_get_member($user_id);

	 	// If member status is Active/Awaiting cancel

	 	return (in_array($member->status, array(MGM_STATUS_ACTIVE, MGM_STATUS_AWAITING_CANCEL)));

 	}

 	return false;

 }

 

/**

 * time2second

 */

function mgm_time2second($time){

	// expire in term

	if(preg_match('/\d+ (HOUR|DAY|WEEK|MONTH|YEAR)/', $time)){

		// list

		list($unit, $period) = explode(' ',$time);

		// periods

		$periods = array('HOUR'=> 1, 'DAY'=> 24, 'WEEK'=> 168, 'MONTH'=> 720, 'YEAR'=> 8760);

		// set 

		$time = (60*60) * $unit * $periods[$period];

	}

	// return

	return $time;

}



/**

 * time2day

 */

function mgm_time2day($time){

	// expire in term

	if(preg_match('/\d+ (DAY|WEEK|MONTH|YEAR)/', $time)){

		// list

		list($unit, $period) = explode(' ',$time);

		// periods

		$periods = array('DAY'=> 1, 'WEEK'=> 7, 'MONTH'=> 30, 'YEAR'=> 365);

		// set 

		$time = $unit * $periods[$period];

	}

	// return

	return $time;

}

/**

 * Format date string and translate to local language

 * 

 * @param string $date

 * @param string $format

 * @return string

 */

function mgm_translate_datestring($date, $format) {

	// If English, return normal date string

	if (WPLANG == '' || WPLANG == 'en_EN') {

		return date($format, strtotime($date));

	}else {

		// Set locale as per wp-config

		setlocale(LC_TIME, WPLANG);

	}

	// Replacement for strftime format

	$arr_format = array(

		'd' => '%d',

		'D' => '%a',

		'j' => '%d',

		'l' => '%A',

		'N' => '%u',

		'w' => '%w',

		'W' => '%W',	

		'F' => '%B',	

		'm' => '%m',

		'M' => '%b',

		'y' => '%y',

		'Y' => '%Y',

		'a' => '%P',

		'A' => '%p',

		'g' => '%I',

		'G' => '%k',

		'h' => '%I',

		'H' => '%H',

		'i' => '%M',

		's' => '%S'		

	);

	$new_format = '';

	// Loop through format string and replace character

	for($i = 0, $len = strlen($format);$i < $len; $i++) {

		$new_format .= isset($arr_format[$format[$i]]) ? $arr_format[$format[$i]] : $format[$i];

	}

	// Convert to local timestring

	return utf8_encode(strftime($new_format, strtotime($date)));

}

/**

 * get loading icon

 *

 * @since 2.6

 */

function mgm_get_loading_icon($message='Loading', $display='none'){

	// return

	return sprintf('<div id="waiting" class="m-waiting" style="display: %s;">

					<span class="spinner"></span> <span class="spinner-text">%s</span></div>', 

					$display, sprintf(esc_html__( '%s...' ), $message));		

}



/**

 * getting purchsed post packs for current user

 */

function mgm_get_purchased_postpacks ($user_id =''){



	global $wpdb;



	$purchased_postpacks = array();



	if(empty($user_id)) {

		$current_user = wp_get_current_user();	

		$user_id = $current_user->ID;

	}

	//condition	

	$condition = "`data` LIKE '%postpack_id%' AND `status_text` = 'Last payment was successful'";

	// sql

	$sql = "SELECT `data`,`transaction_dt`  FROM `".TBL_MGM_TRANSACTION ."` WHERE {$condition}";	

	// row

	$rows  = $wpdb->get_results($sql);



	$data ='';

	

	foreach ($rows as $row) {		

		

		$data = json_decode($row->data);

		

		if($data->postpack_id){

			

			if($data->user_id){

				if($data->user_id == $user_id || is_super_admin()){

					$purchased_postpacks[$data->postpack_id] = $row->transaction_dt;

				}

			}



		}		

		unset($data);

		unset($row);

	}



	$result = array_unique($purchased_postpacks);	

	

	return $result;	

}



/**

 * getting all super admin ids

 */

function mgm_get_super_adminids(){

	

/*	$all_userids = mgm_get_all_userids();

	

	$super_admin_ids = array();

	

	//default one

	$super_admin_ids[] = 1;

	

	foreach ($all_userids as $userid) {

		if(is_super_admin($userid)) {

			$super_admin_ids[]=$userid;

		}

	}

	*/

	//issue #1337

	global $wpdb;				



	$sql = "SELECT um.user_id AS ID, u.user_login FROM  ".$wpdb->users." u, ".$wpdb->usermeta." um ";

	$sql .= "WHERE u.ID = um.user_id AND um.meta_key = '".$wpdb->prefix."capabilities' ";

	$sql .= "AND um.meta_value LIKE '%administrator%' ORDER BY um.user_id";

	

	$results = $wpdb->get_results($sql);

	

	$super_admin_ids = array();

	

	foreach ($results as $result) {

		$super_admin_ids[]=$result->ID;

	}	

	

	return array_unique($super_admin_ids);

}



/**

 * create userlogin name for email as username feature

 *

 * @param string @email

 * @return string $username

 * @since 2.7 

 */

function mgm_generate_user_login($email){

    // split

    list($name, $domain) = explode('@', $email);

    // name

    $user_login = substr( ($name . substr(microtime(),2,8)), 0, 60);

    // return

    return apply_filters('mgm_generate_user_login', $user_login);

}



/**

 * create get users on metadata, check conversion status integrated

 *

 * @param string @meta_key

 * @param string @meta_value

 * @param string @meta_compare 

 * @return array $users

 * @since 2.7 

 */ 

function mgm_get_users_with_meta($meta_key, $meta_value=NULL, $meta_compare='=', $relation='AND'){

 	global $wpdb;

 	// admin escape

 	// $admin_escape = array(array('key'=>'user_level','value'=>10,'compare'=>'!=','type'=>'UNSIGNED'));

 	// args as array

 	if( is_array( $meta_key ) ){

 		$args = array('meta_query' => array_merge(array('relation' => $relation), $meta_key));

 		// foreach($meta_key as $mkey){

 		//	$key_check = 'mgm_usermeta_sync_' . $mkey['key']; break;// just take first key

 		// }

 	}else{

 	// key value pair	

 		$args = array('meta_key' => $meta_key, 'meta_value' => $meta_value, 'meta_compare' => $meta_compare);

 		// $key_check = 'mgm_usermeta_sync_' . $meta_key;

 	}

 	// log

 	// mgm_log($args, __FUNCTION__);

	// meta users	

	$meta_users = new WP_User_Query( array_merge( $args, array( 'fields' => array('ID','display_name','user_email') ) ) );		

	

	//  check the key has not been synced, can not use as result may be null/0

	// 	if( !get_option( $key_check ) ){

	// 		// get all user ids

	// 		$users_count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->users}`");

	// 		// get admin users		

	// 		$admin_users = new WP_User_Query( array( 'role' => 'Administrator', 'fields' => 'ID' ) );

	// 		// track

	// 		if(($admin_users->total_users + $meta_users->total_users) == $users_count){

	// 			update_option($key_check, 'Y');

	// 		}else{

	// 			// return as not yet ready

	// 			return false;

	// 		}

	// 	}

	// log

	// mgm_log($meta_users, __FUNCTION__);	

	// check, @todo, may need some thing more here

	if($meta_users){		

		$users = array();

		// check

		if ( !empty( $meta_users->results ) ) {

			foreach ( $meta_users->results as $user ) {	


				if (is_super_admin($user->ID)) continue; // skip admins	

				if ($oldid == $user->ID) continue; // skip dupes MTP

				$oldid = $user->ID;	

				$users[] = $user;

				$oldid = $user->ID;	

			}			

		}	

		return $users;

	}

	// default

	return false;	

}



/**

 * get restapi key

 *

 * @param void

 * @return string $key

 * @since 1.8.25

 */

function mgm_get_restapi_key(){

	global $wpdb;

	return $apikey = $wpdb->get_var("SELECT `api_key` FROM `".TBL_MGM_REST_API_KEY."` ORDER BY `create_dt` ASC LIMIT 0,1");

}



/**

 * generate restapi url

 *

 * @param string $resource

 * @param string $format

 * @return string $url

 * @since 1.8.25

 */

function mgm_restapi_url($resource='members', $format='xml'){

	return add_query_arg(array(MGM_API_KEY_VAR=>mgm_get_restapi_key()), site_url( trailingslashit(MGM_API_URI_PATH) . $resource . '.' . $format) ); //

} 



/**

 * chcek a member has specified coupon

 *

 * @param object $member

 * @param int $coupon_id

 * @return bool $has_coupon

 * @since 1.8.25

 */

function mgm_member_has_coupon($member, $coupon_id){

	// mgm_pr($member);

	$has_coupon = false;



	// log

	// mgm_log($member, 'upgrade_coupon');



	// register coupons

	if( isset($member->coupon) ){

		// make array

		$member->coupon = (array) $member->coupon;

		// check

		if(isset($member->coupon['id']) && $coupon_id == $member->coupon['id']){			

			$has_coupon = true;		

		}

	}	

	

	// upgrade

	if(isset($member->upgrade['coupon'])){	

		// make array

		$member->upgrade['coupon'] = (array) $member->upgrade['coupon'];

		

		// check

		if(isset($member->upgrade['coupon']['id']) && $coupon_id == $member->upgrade['coupon']['id']){			

			$has_coupon = true;			

		}

	}

	

	// extend	

	if(isset($member->extend['coupon'])){

		// make array

		$member->extend['coupon'] = (array) $member->extend['coupon'];

		// check

		if(isset($member->extend['coupon']['id']) && $coupon_id == $member->extend['coupon']['id']){			

			$has_coupon = true;			

		}

	}

	

	// return

	return $has_coupon;

}



/**

 * Getting post category member protection access types

 *

 * @param int $post_id

 * @return array $membership_types

 */

function mgm_get_post_category_access_membership_types($post_id){

	

	$post_categories = wp_get_post_categories( $post_id);		

			

	$cats = array();	

	

	$membership_types = array();

	

	//post category member types

	$access_membership_types = mgm_get_class('post_category')->get_access_membership_types();



	foreach($post_categories as $c){

		

		$cat = get_category( $c );		

		// check

		if(isset($cat->term_id) && $cat->term_id>0){

			// check

			if(isset($access_membership_types[$cat->term_id])){

				$membership_types = $access_membership_types[$cat->term_id];

			}

		}			

	}

	

	return $membership_types;



}



/**

 * Get current user email

 */

function mgm_get_current_user_email() {

	$user = wp_get_current_user();

	return ( isset( $user->user_email ) ? $user->user_email : '' );

}



/**

 * get custom fields

 */

function mgm_get_custom_fields ($user_ID=false,$display=NULL, $name='mgm_upgrade_field'){

	// display

	if(!$display) $display = array('on_upgrade'=>true);

	// get system

	$system_obj = mgm_get_class('system');	

	// init

	$html = '';

	// wordpress register

	$wordpres_form = mgm_check_wordpress_login();

	// 	get row row template

	$form_row_template = $system_obj->get_template('register_form_row_template');	

	// get template row filter, mgm_register_form_row_template for custom

	$form_row_template = apply_filters('mgm_register_form_row_template', $form_row_template);	

	// get mgm_form_fields generator

	$form_fields = new mgm_form_fields(array('wordpres_form'=>$wordpres_form,'form_row_template'=>$form_row_template));

	//member

	$member = mgm_get_member($user_ID);	

	// user fields on specific page, coupon specially

	$cf_profile_page = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>$display));	

	// loop fields	

	foreach($cf_profile_page as $field){		

		$value = '';

		//custom filed value

		if(isset($member->custom_fields->$field['name'])) {

			$value = $member->custom_fields->$field['name'];

		}

		// init

		$form_html = $form_row_template;		

		// replace wrapper

		$form_html = str_replace('[user_field_wrapper]', $field['name'].'_box', $form_html);

		// replace label

		$form_html = str_replace('[user_field_label]', ($field['attributes']['hide_label']?'':mgm_stripslashes_deep($field['label'])), $form_html);

		// replace element

		$form_html = str_replace('[user_field_element]', $form_fields->get_field_element($field, $name,$value), $form_html);

		// append

		$html .= $form_html;		

	}	

	//return

	return $html;

}



/**

 * get custom fields status Active/Inactive

 * @since 1.8.32

 */

function mgm_custom_filed_status($name =''){

	// custom fields		

    $cf_obj  = mgm_get_class('member_custom_fields');

	// active

	$status = false;

	// list by order first 

	foreach (array_unique($cf_obj->sort_orders) as $id) {

		// loop

		foreach($cf_obj->custom_fields as $field){

			// check

			if($field['id'] == $id){

				if($field['name'] == $name){

					// active

					$status = true;

				}

			}

		}

	}

	return $status;	

}



/**

 * set admin ajax action

 * @since 1.8.32

 */

function mgm_admin_ajax_url($path=''){

	return 'admin-ajax.php?action=mgm_admin_ajax_action' . $path;

}

/**



 * get post data by post name



 * @since 1.8.34



 */

function mgm_get_post_data_by_name($post_name = false){

	global $wpdb;

	// sql

	$sql = "SELECT * FROM `{$wpdb->posts }` WHERE `post_status` = 'publish' AND `post_type` IN ('page','post')  AND `post_name`='{$post_name}'";

	// return

	return $posts = $wpdb->get_results($sql);

}

/**



 * get pack currency by using pack id



 * @since 1.8.36



 */

function mgm_get_pack_currency($pack_id = false){

	//pack

	$pack = mgm_get_class('subscription_packs')->get_pack($pack_id);

	//check

	if(isset($pack['currency']) && !empty($pack['currency'])){

		//pack currency

		return $pack['currency'];

	}

	//return	

	return false;

}

//check content post access delay via private tags argument postdelay

function mgm_content_post_access_delay($args=array()){		

	global  $user_data, $wpdb;

	// current user

	$current_user = wp_get_current_user();

	// get user by username

	if (isset($_GET['username']) && isset($_GET['password'])) {// ? who did this? and why

		$user = wp_authenticate(strip_tags($_GET['username']), strip_tags($_GET['password']));	

	} else if (is_feed() && isset($_GET['token']) && mgm_use_rss_token()) {// added feed check while updating iss#676

		// get user by rss token, only for feed	

		$user = mgm_get_user_by_token(strip_tags($_GET['token']));	

	} else {

		// else get current use if logged in

		$user = $current_user;

	}

	//init

	$access_delay = array();

	//check

	if(isset($args['postdelay']) && !empty($args['postdelay'])){		

		$postdelay_membership_levels = explode(',',$args['postdelay']);

		foreach ($postdelay_membership_levels as $postdelay_membership_level){

			if($postdelay_membership_level) {

				$postdelay_membership_level = explode(':',$postdelay_membership_level);

				$access_delay[$postdelay_membership_level[0]] = $postdelay_membership_level[1];

			}

		}

	}

	//check

	if(!empty($access_delay) && !empty($user)){		

		// get member

		$member = mgm_get_member($user->ID);	

		//check		

		$allowed = mgm_check_post_access_delay($member, $user, $access_delay);

		if(!$allowed){

			return true;

		}

	}	

	// return

	return false;

}

// post access delay via private tags argument postdelay content replacement message

function mgm_replace_postdealy_content($content=NULL){	

	// system

	$system_obj = mgm_get_class('system'); 	

	// not logged in

	$content = mgm_private_text_tags(mgm_stripslashes_deep($system_obj->get_template('private_text_postdelay_no_access', array(), true)));	

	//return

	return sprintf('<div class="mgm_private_postdealy_no_access">%s</div>', mgm_replace_message_tags($content));	

}

// end file /core/libs/functions/mgm_misc_functions.php

