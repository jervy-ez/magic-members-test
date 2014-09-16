<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members downloads handler utility class
 *
 * @package MagicMembers
 * @since 2.6.0
 */
class mgm_downloads{
	// construct
	public function __construct(){
		// php4 
		$this->mgm_downloads();
	}
	
	// php4 construct
	public function mgm_downloads(){
		// do
	}	
	
	// add
	public function add($data, $posts=NULL){
		global $wpdb;		
		// next id
		$next_id = ($this->get_count() + 1);				
		// setup default data
		$default = array('title'=>'New Download - ' . $next_id, 'filename'=>'', 'real_filename'=>'', 
			             'members_only'=>'N', 'post_date'=>date('Y-m-d H:i:s'), 'code'=>uniqid());
		// merge
		$data = array_merge($default, $data);
		// expire date format
		if(isset($data['expire_dt']) && !empty($data['expire_dt'])) {
			$data['expire_dt'] = date('Y-m-d', strtotime($data['expire_dt']));
		}	
		// user id
		if(!isset($data['user_id'])){
			// current user
			if($current_user = wp_get_current_user()){
				$data['user_id'] = $current_user->ID;
			}else{
				$data['user_id'] = 0;// nobody
			}	
		}	
		// insert
		if($affected = $wpdb->insert(TBL_MGM_DOWNLOAD, $data)){
			// get id
			if( $id = $wpdb->insert_id){
				// posts			
				if (isset($posts) && is_array($posts)) {
					// loop
					foreach ($posts as $post_id) {
						// insert
						$wpdb->insert(TBL_MGM_DOWNLOAD_POST_ASSOC, array('download_id'=>$id, 'post_id'=>$post_id));
					}
				}
				// return download
				return $download = $this->get($id);
			}			
		}
		// return
		return false;
	}
	
	// update
	public function update($id, $data, $posts=NULL){
		global $wpdb;
		
		// setup default data
		$default = array('post_date'=>date('Y-m-d H:i:s'), 'code'=>uniqid());
		// merge
		$data = array_merge($default, $data);
		// expire date format
		if(isset($data['expire_dt']) && !empty($data['expire_dt'])) {
			$data['expire_dt'] = date('Y-m-d', strtotime($data['expire_dt']));
		}	
		// user id
		if(!isset($data['user_id'])){
			// current user
			if($current_user = wp_get_current_user()){
				$data['user_id'] = $current_user->ID;
			}else{
				$data['user_id'] = 0;// nobody
			}	
		}	
		// update
		if($affected = $wpdb->update(TBL_MGM_DOWNLOAD, $data, array('id' => $id))){
			// posts			
			if (isset($posts) && is_array($posts)) {
				// clear old				
				$wpdb->query($wpdb->prepare('DELETE FROM `' . TBL_MGM_DOWNLOAD_POST_ASSOC . '` WHERE `download_id` = %d', $id));
				// loop
				foreach ($posts as $post_id) {
					// check
					if($post_id > 0){// for delete via api
					// insert
						$wpdb->insert(TBL_MGM_DOWNLOAD_POST_ASSOC, array('download_id'=>$id, 'post_id'=>$post_id));
					}
				}
			}
			// return download
			return $download = $this->get($id);						
		}
		// return
		return false;
	}
	
	// delete
	public function delete($id){
		global $wpdb;
		
		// get filename
		if($filename = $wpdb->get_var($wpdb->prepare("SELECT `filename` FROM `" . TBL_MGM_DOWNLOAD . "` WHERE id = %d", $id))){
			// check s3
			if(!mgm_is_s3_file($filename)){		
				// delete file if locally stored
				mgm_delete_file(MGM_FILES_DOWNLOAD_DIR . basename($filename));
			}
		}
		// delete		
		return $wpdb->query($wpdb->prepare('DELETE FROM `' . TBL_MGM_DOWNLOAD . '`	WHERE id = %d', $id));
	}
	
	// delete all
	public function delete_all(){
		global $wpdb;
		
		// get filenames	
		if($downloads = $wpdb->get_results("SELECT `filename` FROM `" . TBL_MGM_DOWNLOAD . "` WHERE 1")){
			// loop
			foreach($downloads as $download){
				// check s3
				if(!mgm_is_s3_file($download->filename)){		
					// delete file if locally stored
					mgm_delete_file(MGM_FILES_DOWNLOAD_DIR . basename($download->filename));
				}
			}			
		}
		// delete		
		return $wpdb->query('DELETE FROM `' . TBL_MGM_DOWNLOAD . '`	WHERE 1');
	}
	
	// get 
	public function get($id){
		global $wpdb;
		
		// get 	
		if($download = $wpdb->get_row("SELECT * FROM `" . TBL_MGM_DOWNLOAD . "` WHERE id= '{$id}'")){	
			// date 
			$download->post_date = date('Y-m-d', strtotime($download->post_date));		
			// date 
			$download->expire_dt = is_null($download->expire_dt)? 'N/A' : date('Y-m-d', strtotime($download->expire_dt));	
			// posts
			$download->posts = mgm_get_download_posts($id);		
			// return 
			return $download;	
		}
		// error
		return false;
	}
	
	// get all
	public function get_all(){
		global $wpdb;
		
		// init
		$downloads = array();
		// get all	
		if($_downloads = $wpdb->get_results("SELECT * FROM `" . TBL_MGM_DOWNLOAD . "` WHERE 1")){		
			// loop
			foreach($_downloads as $download){
				// date 
				$download->post_date = date('Y-m-d', strtotime($download->post_date));		
				// date 
				$download->expire_dt = is_null($download->expire_dt)? 'N/A' : date('Y-m-d', strtotime($download->expire_dt));
				// posts
				$download->posts = mgm_get_download_posts($download->id);	
				// set
				$downloads[] = $download;
			}				
		}	
		// return 
		return $downloads;	
	}
		
	// private
	public function get_count(){
		global $wpdb;
		
		// return
		return $wpdb->get_var('SELECT COUNT(*) AS _C FROM ' . TBL_MGM_DOWNLOAD);
	}
}
// end of file core/libs/utilities/mgm_downloads.php