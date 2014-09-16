<?php
/** 
 * Options
 */ 
 
 // affilite
 update_option('mgm_affiliate_id', MGM_AFFILIATE_ID);
 
 // update upgrade id, track for upgrade 
 $upgrade_id = $mgm_upgrade_id = '1.0';
 // get list of upgrades
 $upgrades = glob(str_replace('install','upgrades',dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'upgrade_id_*', GLOB_ONLYDIR);
 // we have some in the list
 if(count($upgrades)>0){
	// loop
	foreach($upgrades as $upgrade){		
		// get id form folder
		$upgrade_id = str_replace('upgrade_id_', '', pathinfo($upgrade, PATHINFO_BASENAME));
		// check
		if( version_compare($upgrade_id, $mgm_upgrade_id, '>') ){// fix for minor version checking	
			$mgm_upgrade_id = $upgrade_id;
		}	
	}
 }		
 // update upgrade id		
 update_option('mgm_upgrade_id', $mgm_upgrade_id);// 5.5.2 is last

 // end of file