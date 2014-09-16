<?php
/** 
 * Schema update
 */ 	
 
 // charset and collate
 $charset_collate = mgm_get_charset_collate();
 
 // download limit : mgm_download_limit_assoc
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_DOWNLOAD_LIMIT_ASSOC . "` (
			`id` int(11) UNSIGNED NOT NULL auto_increment,
			`download_id` int(11) UNSIGNED NOT NULL,
			`user_id` bigint(20) unsigned NULL,
			`ip_address` int(11) UNSIGNED NULL,
			`count` INT NOT NULL,
			PRIMARY KEY (`id`)
		) {$charset_collate} COMMENT = 'download limit associations'";
 
 $wpdb->query($sql);

 // end of file
