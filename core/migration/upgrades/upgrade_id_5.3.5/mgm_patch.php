<?php
/** 
 * Patch for updating template content reminder_email_template_body (issue#: 991)
 */  	

	//Updating the reminder_email_template_body template.
	$name = 'reminder_email_template_body';
	$type = 'emails';
	$template_file = MGM_CORE_DIR . MGM_DS . 'html' . MGM_DS . $type . MGM_DS . $name . '.html';
	
	// get content
	if(file_exists($template_file)){
		
		$content = file_get_contents($template_file);

		$wpdb->update(TBL_MGM_TEMPLATE, array('content' => addslashes($content)), array('name'=>$name, 'type' => $type));
	}
 