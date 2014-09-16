<?php
/** 
 * Schema update
 */ 	
 // coupon unique column key
 $sql = 'ALTER TABLE `' . TBL_MGM_DOWNLOAD . '` ADD UNIQUE (`title` )';
 $wpdb->query($sql);  
 
 // end of file
