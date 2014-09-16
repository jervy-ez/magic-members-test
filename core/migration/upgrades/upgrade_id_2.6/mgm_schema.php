<?php
/** 
 * Schema update
 */ 	
 // add gift tracking
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PURCHASES . '` ADD `is_gift` enum("N","Y") NOT NULL AFTER `post_id`';
 $wpdb->query($sql); 
 
 // add purchase_dt
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PURCHASES . '` ADD `purchase_dt` datetime NULL AFTER `unixtime`';
 $wpdb->query($sql); 
 
 // add is_expire
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PURCHASES . '` ADD `is_expire` enum("Y","N") NOT NULL';
 $wpdb->query($sql); 
 
 // update purchase_dt date
 $sql = 'UPDATE `' . TBL_MGM_POST_PURCHASES . '` SET `purchase_dt` = FROM_UNIXTIME(`unixtime`) WHERE `purchase_dt` IS NULL';
 $wpdb->query($sql); 
 
 // drop old if updated successfully
 $count = $wpdb->get_var('SELECT COUNT(*) AS _CNT FROM `' . TBL_MGM_POST_PURCHASES . '` WHERE `purchase_dt` IS NULL');
 if($count==0){
	$sql = 'ALTER TABLE `' . TBL_MGM_POST_PURCHASES . '` DROP `unixtime`';
 	$wpdb->query($sql); 
 }
 
 // create unique
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PURCHASES . '` ADD UNIQUE (`user_id` ,`post_id`)';
 $wpdb->query($sql); 

 // ----------------------------------------------------------------------------------------
 
 // add create_dt
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PACK . '` ADD `create_dt` datetime NULL AFTER `unixtime`';
 $wpdb->query($sql); 
 
 // update date
 $sql = 'UPDATE `' . TBL_MGM_POST_PACK . '` SET `create_dt` = FROM_UNIXTIME(`unixtime`) WHERE `create_dt` IS NULL';
 $wpdb->query($sql); 
 
 // drop old if updated successfully
 $count = $wpdb->get_var('SELECT COUNT(*) AS _CNT FROM `' . TBL_MGM_POST_PACK . '` WHERE `create_dt` IS NULL');
 if($count==0){
	$sql = 'ALTER TABLE `' . TBL_MGM_POST_PACK . '` DROP `unixtime`';
 	$wpdb->query($sql); 
 }
 
 // ----------------------------------------------------------------------------------------
 
 // add create_dt
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PACK_POST_ASSOC . '` ADD `create_dt` datetime NULL AFTER `unixtime`';
 $wpdb->query($sql); 
 
 // update date
 $sql = 'UPDATE `' . TBL_MGM_POST_PACK_POST_ASSOC . '` SET `create_dt` = FROM_UNIXTIME(`unixtime`) WHERE `create_dt` IS NULL';
 $wpdb->query($sql); 
 
 // drop old if updated successfully
 $count = $wpdb->get_var('SELECT COUNT(*) AS _CNT FROM `' . TBL_MGM_POST_PACK_POST_ASSOC . '` WHERE `create_dt` IS NULL');
 if($count==0){
	$sql = 'ALTER TABLE `' . TBL_MGM_POST_PACK_POST_ASSOC . '` DROP `unixtime`';
 	$wpdb->query($sql); 
 }
 // end of file
