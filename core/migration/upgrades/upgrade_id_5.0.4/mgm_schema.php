<?php
/** 
 * Schema update $id:5.0.4
 */ 	 
  // keys
 $sql="ALTER TABLE `" . TBL_MGM_DOWNLOAD . "` ADD `real_filename` VARCHAR (255) NULL AFTER `filename`;";
 $wpdb->query($sql);   
 // end of file 