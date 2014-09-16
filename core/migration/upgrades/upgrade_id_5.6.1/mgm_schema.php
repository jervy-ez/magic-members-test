<?php
/** 
 * Schema update $id:5.6.1
 */ 	

global $wpdb;
	
// charset and collate
$charset_collate = mgm_get_charset_collate();

// login logs : multiple_login_records
$sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_MULTIPLE_LOGIN_RECORDS . "` (
	  	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	  	`user_id` bigint(20) DEFAULT NULL,
	  	`pack_id` int(11) DEFAULT NULL,
	  	`ip_address` varchar(30) NOT NULL,
	  	`login_at` datetime,
	  	`logout_at` datetime NULL DEFAULT NULL,	  	
	  	PRIMARY KEY (`id`)
	) {$charset_collate} COMMENT = 'multiple login records'";
$wpdb->query($sql); 

// end of file
