<?php
/** 
 * Schema update
 */ 	
 $sql = 'ALTER TABLE `' . TBL_MGM_DOWNLOAD . '` ADD `expire_dt` datetime NULL';
 $wpdb->query($sql);
 // end of file
