<?php 
/**
 * UPDATE settings object with download_slug
 */
$system_cached = mgm_get_option('system');
if(!isset($system_cached->setting['download_slug']) || (isset($system_cached->setting['download_slug']) && empty($system_cached->setting['download_slug'])) ) {
	$system_cached->setting['download_slug'] = 'download';
	update_option('mgm_system', $system_cached);
}
//ends