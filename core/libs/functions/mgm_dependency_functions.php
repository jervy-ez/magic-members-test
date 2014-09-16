<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members dependency functions
 *
 * @package MagicMembers
 * @subpackage tools
 * @since 2.6
 */

/**
 * check curl support
 */
function mgm_check_curl_support($key=NULL){
	// return
	return extension_loaded('curl');
}
/**
 * check simplexml support
 */
function mgm_check_simplexml_support($key=NULL){
	// return
	return extension_loaded('simplexml');
}

/**
 * check hash support
 */
function mgm_check_hash_support($key=NULL){
	// return
	return extension_loaded('hash');
}

/**
 * check mbstring support
 */
function mgm_check_mbstring_support($key=NULL){
	// return
	return extension_loaded('mbstring');
}

/**
 * check mysq support
 */
function mgm_check_mysql_support($key=NULL){
	// return
	return ((version_compare(mysql_get_server_info(), '5.0.0')) >= 0) ? true : false;	
}

/**
 * check wordpress version support
 */
function mgm_check_wp_version_support($key=NULL){
	// return
	return mgm_compare_wp_version( '3.1.0', '>=' ) ? true : false;

	# ((version_compare($GLOBALS['wp_version'], '3.1.0')) >= 0) ? true : false;	
}

/**
 * check php version support
 */

function mgm_check_php_version_support($key=NULL){
	// return
	return ((version_compare(phpversion(), '5.2.0')) >= 0) ? true : false;	
}

/**
 * check http range support for heavy download
 */
function mgm_check_http_range_support($key=NULL){
	// sample
    //$sample_url = site_url('wp-content/plugins/magicmembers/core/assets/images/logo.png');
	// sample    
    $sample_url = MGM_ASSETS_URL.'images/logo.png';
	// headers
	$h = wp_remote_head($sample_url, array('timeout'=>30));	
	// return	
	return ( isset($h['headers']['accept-ranges']) && $h['headers']['accept-ranges'] == 'bytes') ? true : false;
}

/**
 * check tables loaded
 */
function mgm_check_dbtables_loaded($key=NULL){
	global $wpdb;		
	// wp tables
	$wp_tables  = mgm_get_wp_tables();
	// check
	$mgm_tables = mgm_get_tables();
	// deprecated tables not removed from tables define
	$mgm_deprecated_tables = array(TBL_MGM_DOWNLOAD_ATTRIBUTE,TBL_MGM_DOWNLOAD_ATTRIBUTE_TYPE);
	// return 
	$return = true;
	// prefix
	$t_prefix = $wpdb->prefix . MGM_TABLE_PREFIX;
	// check
	foreach($mgm_tables as $mgm_table){
		// only tables matching "wp_mgm_" at beginging
		if(preg_match('/^' . $t_prefix . '/i', $mgm_table)){
			// skip
			if(in_array($mgm_table, $mgm_deprecated_tables)) continue;
			// check
			if(!in_array($mgm_table, $wp_tables)){								
				$return = false; break;
			}
		}
	}
	
	// reload
	if(!$return){
		require_once(MGM_CORE_DIR . 'migration/install/mgm_schema.php');
	}	
	
	// return	
	return $return;
}
/**
 * check paypal ipn server port support
 */
function mgm_check_paypal_ipn_port_support($key=NULL){
	// return
	return (($_SERVER['SERVER_PORT'] == 80) ? true : false);	
}
// end file