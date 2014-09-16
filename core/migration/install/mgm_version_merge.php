<?php
// schema alter
global $wpdb;
// rename
$renames   = array();
// rename tables first
$renames[] = "RENAME TABLE `".$wpdb->prefix."mgm_coupon`  TO `".TBL_MGM_COUPON."`" ; // mgm_coupons
$renames[] = "RENAME TABLE `".$wpdb->prefix."mgm_download`  TO `".TBL_MGM_DOWNLOAD."`" ;// mgm_downloads
$renames[] = "RENAME TABLE `".$wpdb->prefix."mgm_download_attribute`  TO `".TBL_MGM_DOWNLOAD_ATTRIBUTE."`" ;// mgm_download_attributes
$renames[] = "RENAME TABLE `".$wpdb->prefix."mgm_download_attribute_type`  TO `".TBL_MGM_DOWNLOAD_ATTRIBUTE_TYPE."`" ;// mgm_download_attribute_types
$renames[] = "RENAME TABLE `".$wpdb->prefix."mgm_post_pack`  TO `".TBL_MGM_POST_PACK."`" ;// mgm_post_packs

// run alters
foreach($renames as $rename){
	$wpdb->query($rename);
}	
// alters
$alters   = array();
// wp_mgm_countries
$alters[] = "ALTER TABLE `".TBL_MGM_COUNTRY."` 
			 CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
			 CHANGE `name` `name` VARCHAR( 100 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
			 CHANGE `code` `code` VARCHAR( 3 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL"; 
// wp_mgm_coupon			 
$alters[] = "ALTER TABLE `".TBL_MGM_COUPON."` 
			 CHANGE `name` `name` VARCHAR( 150 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
			 CHANGE `value` `value` VARCHAR( 150 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
			 CHANGE `description` `description` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL "; 			 

// wp_mgm_download
$alters[] = "ALTER TABLE `".TBL_MGM_DOWNLOAD."`
			 CHANGE `postDate` `post_date` DATETIME NOT NULL ,
			 CHANGE `members` `members_only` ENUM( 'N', 'Y' ) NOT NULL ,
			 CHANGE `title` `title` VARCHAR( 150 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
			 CHANGE `filename` `filename` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
			 CHANGE `user` `user_id` BIGINT( 20 ) UNSIGNED NOT NULL"; 

// wp_mgm_download_attribute			 
$alters[] = "ALTER TABLE `".TBL_MGM_DOWNLOAD_ATTRIBUTE."`
             CHANGE `download_id` `download_id` INT( 11 ) UNSIGNED NOT NULL ,
			 CHANGE `attribute_id` `attribute_id` INT( 11 ) UNSIGNED NOT NULL"; 	
			 
// wp_mgm_download_attribute_type
$alters[] = "ALTER TABLE `".TBL_MGM_DOWNLOAD_ATTRIBUTE_TYPE."` 
			 CHANGE `name` `name` VARCHAR( 150 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
			 CHANGE `description` `description` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL";
			
// wp_mgm_download_post_assoc
$alters[] = "ALTER TABLE `".TBL_MGM_DOWNLOAD_POST_ASSOC."` CHANGE `post_id` `post_id` BIGINT( 20 ) UNSIGNED NOT NULL"; 	

// wp_mgm_posts_purchased
$alters[] = "ALTER TABLE `".TBL_MGM_POST_PURCHASES."` 
             CHANGE `user_id` `user_id` BIGINT( 20 ) NOT NULL ,
			 CHANGE `post_id` `post_id` BIGINT( 20 ) NOT NULL"; 	
			 
// wp_mgm_post_pack
$alters[] = "ALTER TABLE `".TBL_MGM_POST_PACK."` 
             CHANGE `name` `name` VARCHAR( 150 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ,
			 CHANGE `cost` `cost` DECIMAL( 10, 2 )  UNSIGNED NOT NULL ,
			 CHANGE `description` `description` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL ,
			 CHANGE `unixtime` `unixtime` INT( 11 ) UNSIGNED NOT NULL"; 		
			 
// wp_mgm_post_pack_post_assoc
$alters[] = "ALTER TABLE `".TBL_MGM_POST_PACK_POST_ASSOC."` 
			 CHANGE `pack_id` `pack_id` INT( 11 ) UNSIGNED NOT NULL ,
			 CHANGE `post_id` `post_id` BIGINT( 20 ) UNSIGNED NOT NULL ,
			 CHANGE `unixtime` `unixtime` INT( 11 ) UNSIGNED NOT NULL";
			 
// run alters
foreach($alters as $alter){
	$wpdb->query($alter);
}	

// rename options 
// option meta
$wpdb->query("UPDATE " . $wpdb->options . " SET option_name = CONCAT('v1_', option_name) WHERE `option_name` LIKE 'mgm_%' AND option_name NOT IN('mgm_version','mgm_upgrade_id','mgm_auth')");
// user meta			
$wpdb->query("UPDATE " . $wpdb->usermeta . " SET meta_key = CONCAT('v1_', meta_key) WHERE `meta_key` LIKE 'mgm_%' ");
// post meta				
$wpdb->query("UPDATE " . $wpdb->postmeta . " SET meta_key = CONCAT('_v1', meta_key) WHERE `meta_key` LIKE '_mgm_%' ");		 

// end of file