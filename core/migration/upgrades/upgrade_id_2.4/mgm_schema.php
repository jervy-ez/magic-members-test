<?php
/** 
 * Schema update
 */ 	
  // templates delete wrong keys
 $sql = 'DELETE FROM `' . TBL_MGM_TEMPLATE . '` WHERE `name` IN ("pack_desc_template", "ppp_pack_template") AND `type`="messages"';
 $wpdb->query($sql); 
 
 // end of file
