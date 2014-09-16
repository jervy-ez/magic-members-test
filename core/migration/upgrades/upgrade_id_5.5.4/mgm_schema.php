<?php
/** 
 * Schema update
 */ 	

 // add guest_coupon
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PURCHASES . '` ADD `guest_coupon` varchar(20) NULL AFTER `guest_token`';
 $wpdb->query($sql); 
 
 // end of file