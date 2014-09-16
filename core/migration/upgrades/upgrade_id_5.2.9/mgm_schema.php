<?php
/** 
 * Schema update
 */ 	
 
 $sql = 'ALTER TABLE `' . TBL_MGM_DOWNLOAD . '` ADD `restrict_acces_ip` enum("N","Y") NOT NULL AFTER `code`';
 $wpdb->query($sql);
 
 
 $sql = 'ALTER TABLE `' . TBL_MGM_DOWNLOAD . '` ADD `download_limit` INT( 11 ) UNSIGNED NULL AFTER `user_id`';
 $wpdb->query($sql);
 
 // end of file
