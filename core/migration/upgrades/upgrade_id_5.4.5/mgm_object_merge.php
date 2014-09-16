<?php
/** 
 * Option update
 * Add New data set: mgm_widget_data
 * The data to be used to populate membership/status count
 */ 
// Check mgm_widget_data exists
if (!get_option('mgm_widget_data')) {
	// Force cron callback to insert mgm_widget_data into db
	mgm_get_class('schedular')->hourly_update_widget_data();	
}