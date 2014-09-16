<?php
/** 
 * Schema update
 */ 	
 
 // charset and collate
 $charset_collate = mgm_get_charset_collate();
 
 // templates : wp_mgm_downloads
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_TEMPLATE . "` (
			`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`name` VARCHAR( 250 ) NOT NULL ,
			`type` ENUM( 'emails', 'messages', 'templates' ) NOT NULL ,
			`content` TEXT NOT NULL ,
			`create_dt` DATETIME NOT NULL 
		) {$charset_collate} COMMENT = 'template contents'";
 $wpdb->query($sql); 
 
 // end of file
