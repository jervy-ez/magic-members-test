<?php
/** 
 * Schema update $id:5.0.3
 */ 	 
 
 // charset and collate
 $charset_collate = mgm_get_charset_collate();
 
 // rest api keys : mgm_rest_api_keys
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_REST_API_KEY . "` (
		`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		`api_key` varchar(40) NOT NULL,
		`level` smallint(5) UNSIGNED NOT NULL,		
		`create_dt` DATETIME NOT NULL,
		PRIMARY KEY (`id`)
		) {$charset_collate} COMMENT = 'rest api keys'";
 $wpdb->query($sql);
 
 // rest api levels : mgm_rest_api_levels
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_REST_API_LEVEL . "` (
		`id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
		`level` smallint(5) UNSIGNED NOT NULL,
		`name` varchar(100) NOT NULL,
		`permissions` text NOT NULL,
		`limits` int(11) UNSIGNED NOT NULL,
		PRIMARY KEY (`id`)
		) {$charset_collate} COMMENT = 'rest api access levels'";
 $wpdb->query($sql);
 
 // rest api logs : mgm_rest_api_logs
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_REST_API_LOG . "` (
		`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		`api_key` varchar(40) NOT NULL,
		`uri` varchar(255) NOT NULL,
		`method` varchar(6) NOT NULL,
		`params` text NOT NULL,		
		`ip_address` varchar(15) NOT NULL,		
		`is_authorized` enum('Y','N') NOT NULL,
		`create_dt` DATETIME NOT NULL,
		PRIMARY KEY (`id`)
		) {$charset_collate} COMMENT = 'rest api logs'";
 $wpdb->query($sql);
 
 // level
 $sql="INSERT IGNORE INTO `" . TBL_MGM_REST_API_LEVEL. "` (`id`, `level`, `name`, `permissions`, `limits`) VALUES 
	   (1, 1, 'full access', '[]', 1000);";
 $wpdb->query($sql); 
 
 // keys
 $sql="INSERT IGNORE INTO `" . TBL_MGM_REST_API_KEY. "` (`id`, `api_key`, `level`, `create_dt`) VALUES 
	   (1, '".mgm_create_token()."', '1', NOW());";
 $wpdb->query($sql);  
 
 // end of file 
