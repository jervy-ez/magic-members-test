<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members download helpers
 *
 * @package MagicMembers
 * @version 1.0
 * @since 2.6.0
 */
 
/**
 * Magic Members add download
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param array $data
 * @param array $posts
 * @return array $download
 */
 function mgm_add_download($data, $posts=NULL){
 	// object
	$d_obj = mgm_get_utility_class('downloads');
	// return 
	return $d_obj->add($data, $posts);
 }
 
 /**
 * Magic Members update download
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int $id
 * @param array $data
 * @param array $posts
 * @return array $download
 */
 function mgm_update_download($id, $data, $posts=NULL){
 	// object
	$d_obj = mgm_get_utility_class('downloads');
	// return 
	return $d_obj->update($id, $data, $posts);
 }
 
/**
 * Magic Members delete download
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int id
 * @return bool (success|failure)
 */
 function mgm_delete_download($id){
 	// object
	$d_obj = mgm_get_utility_class('downloads');
	// return 
	return $d_obj->delete($id);
 }
 
 /**
 * Magic Members delete all download
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param none
 * @return bool (success|failure)
 */
 function mgm_delete_all_download(){
 	// object
	$d_obj = mgm_get_utility_class('downloads');
	// return 
	return $d_obj->delete_all();
 }
 
 /**
 * Magic Members get download
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param int id
 * @return array $download
 */
 function mgm_get_download($id){
 	// object
	$d_obj = mgm_get_utility_class('downloads');
	// return 
	return $d_obj->get($id);
 }
 
/**
 * Magic Members get all download
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string none
 * @return array $downloads
 */
 function mgm_get_all_download(){
 	// object
	$d_obj = mgm_get_utility_class('downloads');
	// return 
	return $d_obj->get_all();
 }

/**
 * Magic Members save download file
 *
 * @package MagicMembers
 * @since 2.6.0
 * @param string $element 
 * @return array $fileinfo
 */ 
 function mgm_save_file_for_download($element='download_file'){
 	// upload check
	if (@is_uploaded_file($_FILES[$element]['tmp_name'])) {
		// real name
		$realname = $_FILES[$element]['name'];  
		// random filename
		$uniquename = substr(microtime(),2,8);
		// paths
		$oldname = strtolower($realname);
		$newname = preg_replace('/(.*)\.(.*)$/i', $uniquename.'.$2', $oldname);
		// keep file name
		// $realname = wp_unique_filename(MGM_FILES_DOWNLOAD_DIR, $realname);	
		// path		
		$filepath = MGM_FILES_DOWNLOAD_DIR . $newname;
		// extended server configurations:
		// should move to htaccess/php.ini
		@ini_set('max_execution_time', 	'3600');
		@ini_set('upload_max_filesize', 	'1000M');
		@ini_set('post_max_size', 		'1000M');			
		// upload
		if(@move_uploaded_file($_FILES[$element]['tmp_name'], $filepath)){	
			// permission
			@chmod($filepath, 0755);			
			// set download_file				
			return $file_info  = array('file_name' => $newname, 'file_url' => MGM_FILES_DOWNLOAD_URL . $newname, 'real_name' => $realname);				
		}
	}
	
	// return 
	return false;
 }
 // end file /core/libs/helpers/mgm_download_helper.php
