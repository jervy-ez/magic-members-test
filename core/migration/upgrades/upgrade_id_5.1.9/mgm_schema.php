<?php
/** 
 * Schema update
 */ 	

 // add view_count
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PURCHASES . '` ADD `view_count` int(11) unsigned NULL AFTER `guest_token`';
 $wpdb->query($sql); 
 
 // end of file
