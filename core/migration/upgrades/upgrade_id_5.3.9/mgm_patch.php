<?php
/** 
 * Patch for updating template content payment_success_title (issue#: 1024)
 */  	

	//Updating the payment_success_title template.
	$name = 'payment_success_title';
	$type = 'messages';
	$template_file = MGM_CORE_DIR . MGM_DS . 'html' . MGM_DS . $type . MGM_DS . $name . '.html';
	
	// get content
	if(file_exists($template_file)){
		
		$content = file_get_contents($template_file);

		$wpdb->update(TBL_MGM_TEMPLATE, array('content' => addslashes($content)), array('name'=>$name, 'type' => $type));
	}
 