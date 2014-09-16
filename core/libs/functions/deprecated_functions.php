<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Deprecated Functions
 *
 */

/*
function mgm_get_coupon_pack2($member, &$pack){
	// set coupon
	$member->coupon = (array) $member->coupon;
	// check
	if(isset($member->coupon['id'])){				
		// main 		
		if($pack && $member->coupon['cost']){
			// original
			$pack['original_cost'] = $pack['cost'];
			// payable
			$pack['cost'] = $member->coupon['cost'];
		}	
		
		if($pack && $member->coupon['duration'])
			$pack['duration'] = $member->coupon['duration'];
		if($pack && $member->coupon['duration_type'])
			$pack['duration_type'] = $member->coupon['duration_type'];
		if($pack && $member->coupon['membership_type'])
			$pack['membership_type'] = $member->coupon['membership_type'];
		//issue#: 478/ add billing cycles.	
		if($pack && isset($member->coupon['num_cycles']))
			$pack['num_cycles'] = $member->coupon['num_cycles'];	
		
		// trial	
		if($pack && $member->coupon['trial_on'])
			$pack['trial_on'] = $member->coupon['trial_on'];
		if($pack && $member->coupon['trial_cost'])
			$pack['trial_cost'] = $member->coupon['trial_cost'];
		if($pack && $member->coupon['trial_duration_type'])
			$pack['trial_duration_type'] = $member->coupon['trial_duration_type'];
		if($pack && $member->coupon['trial_duration'])
			$pack['trial_duration'] = $member->coupon['trial_duration'];	
		if($pack && $member->coupon['trial_num_cycles'])
			$pack['trial_num_cycles'] = $member->coupon['trial_num_cycles'];	
			
		// mark pack as coupon applied
		$pack['coupon_id'] = $member->coupon['id'];				
	}
}*/

// get pp packs
/*
function mgm_get_ppp_pack_posts($pack_id = false) {
    global $wpdb;
    
    $return = new stdClass();
    $return->id = $return->pack_id = $return->post_id = $return->unixtime = false;
    
    if ($pack_id) {
        $sql = 'SELECT id, pack_id, post_id, unixtime FROM `' . TBL_MGM_POST_PACK_POST_ASSOC . '` WHERE pack_id =  ' . $pack_id;
        $return = $wpdb->get_results($sql);
    }
    
    return $return;
}
*/

/*// check post hide?
function mgm_content_protection() {
	return (mgm_get_class('system')->setting['hide_posts'] == 'Y') ? true : false;
}*/

// deep array merge recursive
/*function mgm_array_merge_deep($value1,$value2) {
	// return merged, wrapper for attending any bug later on
	return  array_merge_recursive($value1,$value2);	
}*/


// deprecated : not used
/*function mgm_get_userdatabylogin($user_login='') {
	// login	
	if (isset($_GET[$user_login])) {
		$return = get_user_by('login', $_GET[$user_login]);
	} else if($login){
		$return = get_user_by('login', $user_login);
	} else {
		// current
		$current_user = wp_get_current_user();
		$return = get_userdata($current_user->ID);
	}
	// return
	return $return;
}*/

/**
 * get url
 *
 * @deprecated
 */
/*function mgm_get_url() {
	$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$http = ($_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	return $http . $url ;
}*/

/**
 * remote request
 * @deprecated
 */
function mgm_remote_request($url, $error_string=true, $method='curl') {	
	// init
    $string = '';
    // check curl   
	if (extension_loaded('curl') && $method == 'curl') {		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$string = curl_exec($ch);
		// check error
		if(($errno = curl_errno($ch)) != CURLE_OK ){
			// on
		    if ($error_string) {
				$error  = curl_error($ch);
				$string = ($errno == 7) ? sprintf('%s "%s"',$error,parse_url($url,PHP_URL_HOST)) : $error ;				
			}
		}
		curl_close($ch);
	// check url fopen	
	}else if (ini_get('allow_url_fopen') && $method == 'fopen') {
		if (!$string = @file_get_contents($url)) {
            if ($error_string) {
				$string = sprintf(__('Could not connect to "%s", request failed.','mgm'), parse_url($url,PHP_URL_HOST));
            }
		}	 	
	} else if ($error_string) {
	    $string = __('This feature will not function until either CURL or fopen to urls is turned on.','mgm');
	}
	
	// return
	return $string;
}

/**
 * remote post
 * @deprecated
 */
function mgm_remote_post_x($url, $post_fields=NULL, $auth='', $http_header=array()) {			
	// set headers	
	$headers   = array();	
	$headers[] = "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11";
	$headers[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html,application/json;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
	$headers[] = "Accept-Language: en-us,en;q=0.5";
	$headers[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
	$headers[] = "Keep-Alive: 300";
	$headers[] = "Connection: keep-alive";
	$headers[] = "Content-Type: application/x-www-form-urlencoded";
	$headers[] = "Content-Length: " . strlen($fields);
	
	// init
    $ch = curl_init();	
	// set other params
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT,'Magic Members Membership Software');//$_SERVER['HTTP_USER_AGENT']
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);		
	// post
	if($post_fields){
		curl_setopt($ch, CURLOPT_POST, true);			
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
	}
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);		
	curl_setopt($ch, CURLOPT_REFERER, get_option('siteurl'));	
	// auth
	if($auth){
		curl_setopt($ch, CURLOPT_USERPWD, $auth);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	}
	// get result
	$response = curl_exec($ch);					
	curl_close($ch);
	// return
	return $response;
}

// log
	// mgm_log($jqueryui_version, __FUNCTION__);

	/*// compare version if greater than 2.9
	if (version_compare(get_bloginfo('version'), '2.9', '>=') && version_compare(get_bloginfo('version'), '3.0', '<')){
		// ui 1.7.3 for jQuery 1.4+ options : 1.7.3 , 1.8.2
		if( ! $jqueryui_version = get_option('mgm_jqueryui_version') ){// not defined, use as coded
			$jqueryui_version = '1.7.3';		
			update_option('mgm_jqueryui_version', $jqueryui_version); // and update		 
		}
	// compare version if greater than 3.5 issue #1182
	}else if (version_compare(get_bloginfo('version'), '3.5', '>=')){
		// 1.9.2
		if( ! $jqueryui_version = get_option('mgm_jqueryui_version') ){// not defined, use as coded
			$jqueryui_version = '1.9.2';		
			update_option('mgm_jqueryui_version', $jqueryui_version); // and update		 
		}	
	// compare version if greater than 3.6 issue #1182
	}else if (version_compare(get_bloginfo('version'), '3.6', '>=')){
		// 1.10.3
		if( ! $jqueryui_version = get_option('mgm_jqueryui_version') ){// not defined, use as coded
			$jqueryui_version = '1.10.3';		
			update_option('mgm_jqueryui_version', $jqueryui_version); // and update		 
		}	
	// compare version if greater than 3.0 issue #1010
	}else if (version_compare(get_bloginfo('version'), '3.0', '>=')){
		// 1.8.16
		if( ! $jqueryui_version = get_option('mgm_jqueryui_version') ){// not defined, use as coded
			$jqueryui_version = '1.8.16';		
			update_option('mgm_jqueryui_version', $jqueryui_version); // and update		 
		}
	}else {
		// ui 1.7.2 for jQuery 1.3.2+
		$jqueryui_version = '1.7.2';			 
	}
	*/
	