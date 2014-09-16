<?php
/** 
 * Schema update for Epoch tables
 */ 	

 $sql = 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'mgm_epochtransstats';
 $wpdb->query($sql); 
 
 $sql = 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'mgm_membercancelstats';
 $wpdb->query($sql); 
 // end of file
