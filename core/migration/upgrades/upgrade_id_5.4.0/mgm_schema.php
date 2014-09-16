<?php
/** 
 * Schema update $id:5.4.0
 */ 	
 
 // user id as null
 $sql = 'ALTER TABLE `' . TBL_MGM_TRANSACTION . '` ADD `user_id` BIGINT( 20 ) UNSIGNED NULL AFTER `id`' ; 
 $wpdb->query($sql);
 // end of file
