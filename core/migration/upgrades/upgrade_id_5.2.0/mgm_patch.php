<?php
/** 
 * Patch for updating template content text_guest_purchase_pre_button and text_guest_purchase_pre_register(issue#: 794)
 */  

	//Updating the text_guest_purchase_pre_button template.
	$name = 'text_guest_purchase_pre_button';
	$type = 'messages';
	$template_file = MGM_CORE_DIR . MGM_DS . 'html' . MGM_DS . $type . MGM_DS . $name . '.html';
	
	// get content
	if(file_exists($template_file)){
		$content = file_get_contents($template_file);
		$wpdb->update(TBL_MGM_TEMPLATE, array('content' => addslashes($content)), array('name'=>$name, 'type' => $type));
	}
	
	
	//Updating the text_guest_purchase_pre_register template.
	$name = 'text_guest_purchase_pre_register';
	$type = 'messages';
	$template_file = MGM_CORE_DIR . MGM_DS . 'html' . MGM_DS . $type . MGM_DS . $name . '.html';
	
	// get content
	if(file_exists($template_file)){
		$content = file_get_contents($template_file);
		$wpdb->update(TBL_MGM_TEMPLATE, array('content' => addslashes($content)), array('name'=>$name, 'type' => $type));
	}