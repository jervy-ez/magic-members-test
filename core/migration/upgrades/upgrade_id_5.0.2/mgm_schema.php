<?php
/** 
 * Schema update $id:5.0.2
 */ 	 
 // add post_id
 $sql = "ALTER TABLE `" . TBL_MGM_POST_PROTECTED_URL . "` ADD `post_id` BIGINT(20) UNSIGNED NULL AFTER `url`";
 $wpdb->query($sql);  
 // end of file 
