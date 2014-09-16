<?php
/** 
 * Schema update
 */ 	
 // transaction options, custom fields unique column key
 $sql = 'ALTER TABLE `' . TBL_MGM_TRANSACTION_OPTION . '` ADD UNIQUE (`transaction_id` ,`option_name`)';
 $wpdb->query($sql); 
 
 // copy log
 $sql = 'INSERT INTO `'.TBL_MGM_TRANSACTION_OPTION.'` (`transaction_id`,`option_name`,`option_value`) 
 		 SELECT id,IF(module IS NULL, "paypal_return_data", CONCAT(module,"_return_data")),return_data 
		 FROM `'.TBL_MGM_TRANSACTION.'` WHERE return_data IS NOT NULL';
 $wpdb->query($sql);		 
  
 // drop old 
 $sql = 'ALTER TABLE `' . TBL_MGM_TRANSACTION . '` DROP `return_data`';
 $wpdb->query($sql); 
 
 // end of file
