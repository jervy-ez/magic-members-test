<?php
/** 
 * Schema update
 */ 	
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PACK . '` ADD `product` TEXT NULL AFTER `description`';
 $wpdb->query($sql);

 $sql = 'ALTER TABLE `' . TBL_MGM_DOWNLOAD . '` ADD `code` VARCHAR(50) NOT NULL AFTER `user_id`';
 $wpdb->query($sql);
 // end of file
