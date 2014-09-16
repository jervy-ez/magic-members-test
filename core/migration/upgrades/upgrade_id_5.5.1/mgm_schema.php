<?php
/** 
 * Schema update $id:5.5.1
 */ 	
 
	$sql = "SHOW columns from `".TBL_MGM_DOWNLOAD."` where field='is_s3_torrent'";
	
	$query = $wpdb->query($sql);
	
	if(!$query) {
		// alter
		$sql = "ALTER TABLE `" . TBL_MGM_DOWNLOAD . "` ADD `is_s3_torrent` enum('N','Y') NOT NULL AFTER `restrict_acces_ip`";
		$wpdb->query($sql);
	}
// end of file
