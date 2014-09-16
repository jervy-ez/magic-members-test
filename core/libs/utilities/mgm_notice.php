<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members notice handler utility class 
 *  
 * @package MagicMembers
 * @since 2.5.1
 */
class mgm_notice{		
	// daily schedule not setup
	function daily_schedule_not_setup(){					
		// message
		$message = 'MagicMembers schedular is not properly setup, this is required to run periodical updates and membership expiration. 
					Please deactivate and reactivate the plugin using plugin management screen, this will reinstall the schedular.';
		// show
		mgm_notice(__($message,'mgm'), true);				
	}
	
	// file storate not writable 
	function file_stotage_not_writable(){
		// folders
		$folders = array(sprintf('<li>%s</li>', WP_CONTENT_DIR.'/uploads'),
		                 sprintf('<li>%s</li>', WP_CONTENT_DIR.'/uploads/mgm'),
						 sprintf('<li>%s</li>', WP_CONTENT_DIR.'/uploads/mgm/downloads'),
						 sprintf('<li>%s</li>', WP_CONTENT_DIR.'/uploads/mgm/exports'),
						 sprintf('<li>%s</li>', WP_CONTENT_DIR.'/uploads/mgm/modules'),
						 sprintf('<li>%s</li>', WP_CONTENT_DIR.'/uploads/mgm/images'),
						 sprintf('<li>%s</li>', WP_CONTENT_DIR.'/uploads/mgm/logs'));
        // str
		$folder_structure = sprintf('<ul>%s</ul>', implode(' ',$folders));		
						
		// message
		$message = sprintf('MagicMembers files storage folder is not writable, please make sure "%s" is writable.<br> 
		                    You can also manually create the file structure:<br> %s',WP_CONTENT_DIR, $folder_structure);
		// show
		mgm_notice(__($message,'mgm'), true);
	}
	
	// default permalink  error
	function default_permalink_error(){					
		// message
		$message = sprintf('MagicMembers requires custom permalink structure before it can be used. Please use the 
		           <a href="%s">link</a> to update your permalink structure.', admin_url('options-permalink.php'));
		// show
		mgm_notice(__($message,'mgm'), true);				
	}
	
}
// core/libs/utilities/mgm_notice.php