<?php
/** 
 * Schema update
 */ 
 $charset_collate = mgm_get_charset_collate();	
 // db tables
 $wpdb_tables = mgm_get_wp_tables();
 // alter charset	
 foreach(mgm_get_tables() as $table){
 	// check if exists
	if(!in_array($table, $wpdb_tables)) continue;		
	// run
	$wpdb->query("ALTER TABLE `{$table}` {$charset_collate}");
 }
 // update collat
 mgm_reset_tables_collate($wpdb_tables);
 // end of file
