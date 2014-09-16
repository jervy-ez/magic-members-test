<?php
/** 
 * Apply patch for removing styles FROM saved private messages 
 */ 	
$pattern = "/style=\\\(.*)\\\\\">/";
$arr_messages = array('private_text', 'private_text_no_access', 'private_text_purchasable', 'private_text_purchasable_login');
foreach ($arr_messages as $msg) {
	$content = $wpdb->get_var("SELECT content FROM ". TBL_MGM_TEMPLATE ." WHERE name='". $msg ."' AND type='messages'");
	$content = preg_replace($pattern, "style=\\\"\\\">", $content);
	$wpdb->update(TBL_MGM_TEMPLATE, array('content' => $content), array('name'=>$msg, 'type' => 'messages'));
}
 // end of file
