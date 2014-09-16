<?php
/** 
 * Schema update
 */ 	
  // download limit : mgm_download_limit_assoc
 $sql =  "ALTER TABLE `".TBL_MGM_DOWNLOAD_LIMIT_ASSOC."` CHANGE `ip_address` `ip_address` VARCHAR( 60 ) NULL DEFAULT NULL ";
 $wpdb->query($sql);

 // end of file
