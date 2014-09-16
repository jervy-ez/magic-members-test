<?php
/** 
 * Schema update
 */ 	
 $sql = 'ALTER TABLE `' . TBL_MGM_DOWNLOAD . '` ADD `expire_dt` datetime NULL';
 $wpdb->query($sql);

 $sql = 'ALTER TABLE `' . TBL_MGM_DOWNLOAD . '` ADD `code` VARCHAR(50) NOT NULL AFTER `user_id`';
 $wpdb->query($sql);
 // end of file
