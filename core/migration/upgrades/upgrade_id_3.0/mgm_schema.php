<?php
/** 
 * Schema update
 */ 	
 // coupon unique column key
 $sql = 'ALTER TABLE `' . TBL_MGM_COUPON . '` ADD UNIQUE (`name` )';
 $wpdb->query($sql);  
 
 // end of file
