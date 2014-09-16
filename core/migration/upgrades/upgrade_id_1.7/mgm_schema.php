<?php
/** 
 * Schema update
 */ 	
 $sql = 'ALTER TABLE `' . TBL_MGM_COUPON . '` 
 		 ADD `use_limit` INT( 11 ) UNSIGNED NULL AFTER `description` ,
		 ADD `used_count` INT( 11 ) UNSIGNED NULL ,
		 ADD `expire_dt` DATETIME NULL AFTER `use_limit`';
 $wpdb->query($sql);
 
 $sql = 'ALTER TABLE `' . TBL_MGM_COUPON . '` CHANGE `unixtime` `create_dt` DATETIME NOT NULL'; 
 $wpdb->query($sql); 
 
 // end of file
