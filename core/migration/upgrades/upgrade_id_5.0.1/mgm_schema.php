<?php
/** 
 * Schema update
 */ 	
 
 // charset and collate
 $charset_collate = mgm_get_charset_collate();
 
 // protected urls
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_POST_PROTECTED_URL . "` (
		`id` BIGINT(20) UNSIGNED AUTO_INCREMENT,
		`url` VARCHAR(255) NOT NULL,
		`membership_types` TEXT NULL,
		PRIMARY KEY (`id`)
	) {$charset_collate} COMMENT = 'protected post urls'";
 $wpdb->query($sql); 
 // end of file
