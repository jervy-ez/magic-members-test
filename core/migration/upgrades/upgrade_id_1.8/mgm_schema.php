<?php
/** 
 * Schema update
 */ 	 
 $sql =" ALTER TABLE `" . TBL_MGM_COUPON . "` ADD `used_count` INT( 11 ) UNSIGNED NULL AFTER `use_limit` ";
 $wpdb->query($sql);

 // charset and collate
 $charset_collate = mgm_get_charset_collate();
 
 // post pack post assoc
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_TRANSACTION . "` (
			`id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`payment_type` ENUM( 'post_purchase', 'subscription_purchase' ) NOT NULL ,
			`module` varchar(150) NULL,
			`data` TEXT NULL ,
			`status` varchar(100) NULL,
			`status_text` varchar(255) NULL,
			`transaction_dt` DATETIME NOT NULL
		) {$charset_collate} COMMENT = 'transaction data log'";
 $wpdb->query($sql);
 // end of file
