<?php
/** 
 * Schema update $id:5.1.5
 */ 	
 
 // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_COUNTRY . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql);

 // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_COUPON . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql); 
 
 // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_DOWNLOAD . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql);
 
 // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_DOWNLOAD_ATTRIBUTE . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql);
 
 // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_DOWNLOAD_ATTRIBUTE_TYPE . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql);
 
 // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_DOWNLOAD_POST_ASSOC . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql);
 
 // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PURCHASES . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql);
 
 // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PACK . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql);
 
 // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PACK_POST_ASSOC . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql);
 
  // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_POST_PROTECTED_URL . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql);
 
  // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_REST_API_KEY . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql);
 
  // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_REST_API_LEVEL . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql);
 
  // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_REST_API_LOG . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql);
 
  // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_TEMPLATE . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql);
 
  // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_TRANSACTION . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql);
 
  // collat
 $sql = 'ALTER TABLE `' . TBL_MGM_TRANSACTION_OPTION . '` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci' ;
 $wpdb->query($sql);
  
 // ----------------------------------------------------------------------------------------------------------- 
 // coupon product
 $sql = 'ALTER TABLE `' . TBL_MGM_COUPON . '` ADD `product` TEXT NULL AFTER `used_count`';
 $wpdb->query($sql); 
 
 // alter charset
 $sql = "ALTER TABLE `" . TBL_MGM_COUPON . "` 
 	CHANGE `name` `name` VARCHAR( 150 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
 	CHANGE `value` `value` VARCHAR( 150 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
	CHANGE `description` `description` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL";
 $wpdb->query($sql);	
 
 // rename tables, if exists 
 $sql = 'RENAME TABLE `EpochTransStats` TO `'.TBL_MGM_EPOCH_TRANS_STATUS.'` ';
 $wpdb->query($sql); 
 
 $sql = 'RENAME TABLE `MemberCancelStats` TO `'.TBL_MGM_EPOCH_CANCEL_STATUS.'` ';
 $wpdb->query($sql); 
 
 // end of file
