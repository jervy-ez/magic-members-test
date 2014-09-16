<?php
/** 
 * Schema update $id:5.0.5
 */ 	 
 
 // add level
 $sql="ALTER TABLE `" . TBL_MGM_REST_API_LEVEL . "` ADD `level` smallint(5) UNSIGNED NOT NULL AFTER `id`;";
 $wpdb->query($sql); 
 
 // change name  
 $sql = 'ALTER TABLE `' . TBL_MGM_REST_API_LEVEL . '` CHANGE `level_name` `name` varchar(100) NOT NULL'; 
 $wpdb->query($sql);
 
 // update level 
 $sql = 'UPDATE `' . TBL_MGM_REST_API_LEVEL . '` SET level=id WHERE level=0'; 
 $wpdb->query($sql); 
 // end of file 