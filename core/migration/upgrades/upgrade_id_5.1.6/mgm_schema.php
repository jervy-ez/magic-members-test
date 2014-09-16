<?php
/** 
 * Schema update $id:5.1.6
 */ 	
 
 // guest_token
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PURCHASES . '` ADD `guest_token` VARCHAR(10) NULL AFTER `is_expire`' ;
 $wpdb->query($sql);

 // user id as null
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PURCHASES . '` CHANGE `user_id` `user_id` BIGINT( 20 ) UNSIGNED NULL' ; 
 $wpdb->query($sql);
 // end of file
