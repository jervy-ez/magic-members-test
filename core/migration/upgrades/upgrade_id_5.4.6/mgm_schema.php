<?php
/** 
 * Schema update $id:5.4.6
 */ 	
 
 // charset and collate
 $charset_collate = mgm_get_charset_collate(); 
 
 // addons : mgm_addons 
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_ADDON . "` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` varchar(150) NOT NULL,		
		`description` varchar(255) NULL,		
		`expire_dt` datetime NULL,
		`create_dt` datetime NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `name` (`name`)
	    ) {$charset_collate} COMMENT = 'addons'";
 $wpdb->query($sql);
 
  // addons : mgm_addons 
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_ADDON_OPTION . "` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`addon_id` int(11) UNSIGNED NOT NULL,	
		`option` varchar(150) NOT NULL,				
		`price` decimal(10,2) UNSIGNED NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `addon_option` (`addon_id`,`option`)
	    ) {$charset_collate} COMMENT = 'addon options'";
 $wpdb->query($sql);
 
 // end of file
