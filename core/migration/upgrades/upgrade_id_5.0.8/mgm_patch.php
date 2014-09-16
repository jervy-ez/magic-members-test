<?php
/** 
 * Patch for updating pack_desc_template content
 */  
$name = 'pack_desc_template';
$type = 'templates';
$content = $wpdb->get_var("SELECT `content` FROM `".TBL_MGM_TEMPLATE."` WHERE `name`='{$name}' AND `type`='{$type}'");
// mgm_log($content, 'old_pack_desc_template');
if(!empty($content)) {
	$content = str_replace(array('days', 'USD', 'This pack', 'trial-offer:'), array('[trial_duration_period]', '[currency]', '. This pack', 'trial-offer: '), $content);
	mgm_update_template($name, $content, $type);
}