<?php
/** 
 * Patch for updating template content <p> tags to <div> tags.
 */  	

	//Updating the register form row template.
	$name = 'register_form_row_template';
	$type = 'templates';
	$template_file = MGM_CORE_DIR . MGM_DS . 'html' . MGM_DS . $type . MGM_DS . $name . '.html';
	
	// get content
	if(file_exists($template_file)){
		
		$content = file_get_contents($template_file);

		$wpdb->update(TBL_MGM_TEMPLATE, array('content' => addslashes($content)), array('name'=>$name, 'type' => $type));
	}

	//Updating the register form row autoresponder template.
	$name = 'register_form_row_autoresponder_template';
	$type = 'templates';
	$template_file = MGM_CORE_DIR . MGM_DS . 'html' . MGM_DS . $type . MGM_DS . $name . '.html';
	
	// get content
	if(file_exists($template_file)){
		
		$content = file_get_contents($template_file);

		$wpdb->update(TBL_MGM_TEMPLATE, array('content' => addslashes($content)), array('name'=>$name, 'type' => $type));
	}

	//Updating the profile form row template.
	$name = 'profile_form_row_template';
	$type = 'templates';
	$template_file = MGM_CORE_DIR . MGM_DS . 'html' . MGM_DS . $type . MGM_DS . $name . '.html';
	
	// get content
	if(file_exists($template_file)){
		
		$content = file_get_contents($template_file);

		$wpdb->update(TBL_MGM_TEMPLATE, array('content' => addslashes($content)), array('name'=>$name, 'type' => $type));
	}
