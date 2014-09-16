<?php
/** 
 * Schema update $id:5.5.2
 */ 	
  	global $wpdb;
		
	// rename table
	if(!in_array(TBL_MGM_POST_PURCHASES, mgm_get_wp_tables())){
		$wpdb->query("RENAME TABLE `". $wpdb->prefix . MGM_TABLE_PREFIX . "posts_purchased` TO `".TBL_MGM_POST_PURCHASES."`") ;// posts_purchased => post_purchases
	}
	
	// check new col
	if(!$query = $wpdb->query("SHOW columns from `".TBL_MGM_POST_PURCHASES."` where field='transaction_id'")) {
		// sql
		$sql = "ALTER TABLE `" . TBL_MGM_POST_PURCHASES . "` ADD `transaction_id` bigint(20) unsigned NULL AFTER `view_count`";
		// ex
		$wpdb->query($sql);
	}
	
	// charset and collate
 	$charset_collate = mgm_get_charset_collate();
 
	// addon purchases : mgm_addon_purchases
	$sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_ADDON_PURCHASES . "` (
			`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) unsigned NULL,
			`addon_option_id` int(11) unsigned NOT NULL,		
			`purchase_dt` datetime NULL,
			`transaction_id` bigint(20) unsigned NULL,
			PRIMARY KEY (`id`)
			) {$charset_collate} COMMENT = 'addon purchases log'";
	$wpdb->query($sql); 

// end of file
