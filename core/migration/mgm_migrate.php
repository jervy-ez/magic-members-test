<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
// first time install, option will not be present, 
// both version and upgdare id used to attend auth data bug that may arise due to level-1 architecture upgrade
if(!get_option('mgm_version') && !get_option('mgm_upgrade_id')){	
	// is version merge?
	if(get_option('mgm_license_key')){
		// install
		require_once('install/mgm_version_merge.php');		
	}
	// install
	require_once('install/mgm_first_run.php');
}else{   
	// upgrade
	// get last upgrade version 
	$mgm_upgrade_id = get_option('mgm_upgrade_id');	
	// get list of upgrades
	$upgrades = glob(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'upgrades' . DIRECTORY_SEPARATOR . 'upgrade_id_*', GLOB_ONLYDIR);
	// we have some in the list
	if(count($upgrades)>0){
		// loop
		foreach($upgrades as $upgrade){		
			// get id from folder
			$upgrade_id = str_replace('upgrade_id_', '', pathinfo($upgrade, PATHINFO_BASENAME));
			// when new folder, not executed before			
			if( version_compare($upgrade_id, $mgm_upgrade_id, '>') ){// fix for minor version checking		
				// init
				$upgraded = false;
				// run upgrade
				foreach(array('mgm_schema','mgm_options','mgm_object_merge','mgm_patch','mgm_batch_upgrade') as $upgrade_file){
					// file name
					$upgrade_file_path = $upgrade . DIRECTORY_SEPARATOR . $upgrade_file . '.php';
					// file exists
					if(file_exists($upgrade_file_path)){
						@set_time_limit(300);//300s
						@ini_set('memory_limit', 134217728);// 128M
						// include upgrade
						@include_once($upgrade_file_path);	
						// upgraded
						$upgraded = true;				
					}					
				}
				// upgraded
				if($upgraded && !isset($skip_upgrade_tracking[$upgrade_id])) update_option('mgm_upgrade_id', $upgrade_id);
			}
		}
	}
	
	// sync version
	$product_version = mgm_get_class('auth')->get_product_info('product_version');
	// higher
	if( version_compare($product_version, get_option('mgm_version'), '>') ){ 
		update_option('mgm_version', $product_version);
	}
}

// end of file