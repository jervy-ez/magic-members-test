<?php
/** 
 * Schema update
 */ 	

 // add modules
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PACK . '` ADD `modules` TEXT NULL AFTER `product`';
 $wpdb->query($sql); 
 
 // end of file