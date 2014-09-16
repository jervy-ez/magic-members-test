<?php
/** 
 * Schema update
 */ 	
 $sql = 'ALTER TABLE `' . TBL_MGM_TRANSACTION . '` 
 		 ADD `module` varchar(150) NULL AFTER `payment_type` ,
		 ADD `status` varchar(100) NULL AFTER `data`,
		 ADD `status_text` varchar(255) NULL AFTER `status`';
 $wpdb->query($sql);
 
 // end of file
