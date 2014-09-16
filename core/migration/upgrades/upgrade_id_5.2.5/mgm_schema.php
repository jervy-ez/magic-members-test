<?php
/** 
 * Schema update
 */ 	

 // add filesize
 $sql = 'ALTER TABLE `' . TBL_MGM_DOWNLOAD . '` ADD `filesize` VARCHAR(10) NULL AFTER `real_filename`';
 $wpdb->query($sql); 
 
 // end of file
