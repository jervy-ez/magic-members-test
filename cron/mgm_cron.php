<?php #!/usr/bin/php
/**
 * This file runs via Linux CRON and exeutes wordpress cron, this will ensure cron system runs without any user hist on site
 * 
 * for security,this file is restricted to run in shell only
 * use linux cron system to setup M H D M W php path/to/wordpress/wp-content/plugins/magicmembers/mgm_cron.php
 */
// doing cron
define('MGM_DOING_CRON', true);
// load wp environment
if ( !defined('ABSPATH') ) {
	/** Set up WordPress environment */
	require_once('../../../../wp-load.php');
}
// check security, only allowed in shell
if(isset($argv)){
	// allowed, ruun it now
	wp_cron();	
}
// end