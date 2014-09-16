<?php
/** 
 * Schema update
 */ 	

 // add view_count
 $sql = 'ALTER TABLE `' . TBL_MGM_REST_API_LEVEL . '` CHANGE `limits` `limits` INT( 11 ) UNSIGNED NULL';
 $wpdb->query($sql); 
 
 // end of file
