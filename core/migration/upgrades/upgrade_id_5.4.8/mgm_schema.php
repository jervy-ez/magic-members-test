<?php
/** 
 * Schema update $id:5.4.8
 */ 	
 
 
// rename
$renames   = array();
// rename tables first
$renames[] = "RENAME TABLE `".TBL_MGM_DOWNLOAD_ATTRIBUTE."`  TO `_x_".TBL_MGM_DOWNLOAD_ATTRIBUTE."`" ;// _x_wp_mgm_download_attributes
$renames[] = "RENAME TABLE `".TBL_MGM_DOWNLOAD_ATTRIBUTE_TYPE."`  TO `_x_".TBL_MGM_DOWNLOAD_ATTRIBUTE_TYPE."`" ;// _x_wp_mgm_download_attribute_types
// run alters
foreach($renames as $rename){
	$wpdb->query($rename);
}	
// alter
$sql = "ALTER TABLE `" . TBL_MGM_DOWNLOAD . "` ADD `is_s3_torrent` enum('N','Y') NOT NULL AFTER `restrict_acces_ip`";
$wpdb->query($sql);

// end of file
