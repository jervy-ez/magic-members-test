<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members api contents controller
 *
 * @package MagicMembers
 * @version 1.0
 * @since 2.6.0
 */
 class mgm_api_contents extends mgm_api_controller{
 	// construct
	public function __construct(){
		// php4
		$this->mgm_api_contents();
	}
	
	// php4
	public function mgm_api_contents(){
		// parent
		parent::__construct();
	}
		
	/** 
	 * get protected contents
	 *
	 * @verb GET
	 * @action all 	
	 * @url <site>/mgmapi/contents/protected.<format>  -- list all post types
	 * @url <site>/mgmapi/contents/protected/:(posts|pages|custom_post_type).<format> -- list only specified post type
	 * @url <site>/mgmapi/contents/protected/:(posts|pages|custom_post_type)/:id.<format> -- list specifiedposty type by post id
	 * @url <site>/mgmapi/contents/protected/taxonomies/:taxonomy.<format> -- list all specified taxonomy
	 * @since 1.0
	 *
	 * @param string $post_type
	 * @param mixed $id_taxonomy ( id or taxonomy)
	 * @return array $contents 	 
	 */
	public function protected_get($post_type='', $id_taxonomy=''){
		global $wpdb;
		
		// get vars
		$get_vars = $this->request->data['get'];
		
		// start
		$start = (isset($get_vars['start'])) ? (int)$get_vars['start'] : 0 ;
		// rows
		$rows = (isset($get_vars['rows'])) ? (int)$get_vars['rows'] : 100 ;	
		
		// status
		$status = 'success';
		$message = '';
		
		// check and validate post_type
		if($post_type == 'taxonomies'){						
			// registered taxonomies
			$taxonomies = mgm_get_taxonomies(false);			
			// all post types
			if(empty($taxonomy)){
				// get
				$contents = $this->_get_protected_taxonimies($taxonomies);
				// content type
				$content_type = 'all taxonomies';
			}else{
				// check
				if(!in_array($taxonomy, $taxonomies)){
				// error
					$status = 'error';
					$message = sprintf(__('Specified taxonomy - %s is invalid, try with a valid taxonomy only','mgm'), $taxonomy);
				}else{
					// get
					$contents = $this->_get_protected_taxonimies($taxonomy);
					// content type
					$content_type = $taxonomy;		
				}
			}	
			// name
			$content_type_name = 'taxonomies';						
		}else{
			// id
			if(!$id = (int)$id_taxonomy){
				$id = false;
			}			
			// registered post types
			$post_types = mgm_get_post_types(false);
			// all post types
			if(empty($post_type)){
				// get
				$contents = $this->_get_protected_contents($post_types, $id, $start, $rows);
				// content type
				$content_type = 'all post types';
			}else{
				// post/page
				if(in_array($post_type, array('posts','pages'))){
					$post_type = mgm_singular($post_type);
				}
				// validate
				if(!in_array($post_type, $post_types)){
				// error
					$status = 'error';
					$message = sprintf(__('Specified post type - %s is invalid, try with a valid post type only','mgm'), $post_type);
				}else{											
					// get
					$contents = $this->_get_protected_contents($post_type, $id, $start, $rows);
					// content type
					$content_type = $post_type;
				}		
			}	
			// name
			$content_type_name = 'contents';		
		}
		
		// unset
		unset($id_taxonomy);		
		 
		// data when contents found
		if(isset($contents)){
			// total rows
			$total_rows = count($contents);
			// base
			$data = array('total_rows'=>$total_rows);	
			// by id
			if(isset($id) && (int)$id > 0){
				// message
				$message = sprintf(__('Get protected - %s dy id#%d response','mgm'), $content_type, $id);				
				// data
				if($total_rows > 0) $data = $data + array(mgm_singular($content_type_name)=>array_shift($contents));
			}else{
			// all
				// message
				$message = sprintf(__('Get protected - %s response - %d %s found','mgm'), $content_type, $total_rows, $content_type);
				// data
				if($total_rows > 0) $data = $data + array($content_type_name=>$contents);
			}
		}
		
		// response
		$response = array('status' => $status, 
		                  'message' => $message
						 );
		
		// data
		if(isset($data)) $response = $response + array('data'=>$data);					 
		
		// return
		return array($response, 200);	
	}
	
	/** 
	 * get purchasable contents	
	 * 
	 * @verb GET
	 * @action all 	
	 * @url <site>/mgmapi/contents/purchasable.<format>  -- list all post types
	 * @url <site>/mgmapi/contents/purchasable/:(posts|pages|custom_post_type).<format> -- list only specified post type
	 * @url <site>/mgmapi/contents/purchasable/:(posts|pages|custom_post_type)/:id.<format> -- list specified post type by post id
	 *	
	 * @param string $post_type
	 * @param int $id
	 * @since 1.0
	 */
	public function purchasable_get($post_type='', $id=NULL){
		global $wpdb;
		
		// get vars
		$get_vars = $this->request->data['get'];
		
		// start
		$start = (isset($get_vars['start'])) ? (int)$get_vars['start'] : 0 ;
		// rows
		$rows = (isset($get_vars['rows'])) ? (int)$get_vars['rows'] : 100 ;	
		
		// status
		$status = 'success';
		$message = '';		
				
		// registered post types
		$post_types = mgm_get_post_types(false);
		// all post types
		if(empty($post_type)){
			// get
			$contents = $this->_get_purchasable_contents($post_types, $id, $start, $rows);
			// content type
			$content_type = 'all post types';
		}else{
			// post/page
			if(in_array($post_type, array('posts','pages'))){
				$post_type = mgm_singular($post_type);
			}
			// validate
			if(!in_array($post_type, $post_types)){
			// error
				$status = 'error';
				$message = sprintf(__('Specified post type - %s is invalid, try with a valid post type only','mgm'), $post_type);
			}else{											
				// get
				$contents = $this->_get_purchasable_contents($post_type, $id, $start, $rows);
				// content type
				$content_type = $post_type;
			}		
		}	
		// name
		$content_type_name = 'contents';						
		 
		// data when contents found
		if(isset($contents)){
			// total rows
			$total_rows = count($contents);
			// base
			$data = array('total_rows'=>$total_rows);	
			// by id
			if(isset($id) && (int)$id>0){
				// message
				$message = sprintf(__('Get purchasable - %s dy id#%d response','mgm'), $content_type, $id);				
				// data
				if($total_rows > 0) $data = $data + array(mgm_singular($content_type_name)=>array_shift($contents));
			}else{
			// all
				// message
				$message = sprintf(__('Get purchasable - %s response - %d %s found','mgm'), $content_type, $total_rows, $content_type);
				// data
				if($total_rows > 0) $data = $data + array($content_type_name=>$contents);
			}
		}
		
		// response
		$response = array('status' => $status, 
		                  'message' => $message
						 );
		
		// data
		if(isset($data)) $response = $response + array('data'=>$data);		
		
		// return
		return array($response, 200);	
	}	
	
	/** 
	 * get purchased contents	
	 * 
	 * @verb GET
	 * @action all 	
	 * @url <site>/mgmapi/contents/purchased.<format>  -- list all post types
	 * @url <site>/mgmapi/contents/purchased/:(posts|pages|custom_post_type).<format> -- list only specified post type
	 * @url <site>/mgmapi/contents/purchased/:(posts|pages|custom_post_type)/:id.<format> -- list specified post type by post id
	 *	
	 * @param string $post_type
	 * @param int $id
	 * @since 1.0
	 */
	public function purchased_get($post_type='', $id=NULL){
		global $wpdb;
		
		// get vars
		$get_vars = $this->request->data['get'];
		
		// start
		$start = (isset($get_vars['start'])) ? (int)$get_vars['start'] : 0 ;
		// rows
		$rows = (isset($get_vars['rows'])) ? (int)$get_vars['rows'] : 100 ;	
		
		// status
		$status = 'success';
		$message = '';		
				
		// registered post types
		$post_types = mgm_get_post_types(false);
		// all post types
		if(empty($post_type)){
			// get
			$contents = $this->_get_purchased_contents($post_types, $id, $start, $rows);
			// content type
			$content_type = 'all post types';
		}else{
			// post/page
			if(in_array($post_type, array('posts','pages'))){
				$post_type = mgm_singular($post_type);
			}
			// validate
			if(!in_array($post_type, $post_types)){
			// error
				$status = 'error';
				$message = sprintf(__('Specified post type - %s is invalid, try with a valid post type only','mgm'), $post_type);
			}else{											
				// get
				$contents = $this->_get_purchased_contents($post_type, $id, $start, $rows);
				// content type
				$content_type = $post_type;
			}		
		}	
		// name
		$content_type_name = 'contents';						
		 
		// data when contents found
		if(isset($contents)){			
			// total rows
			$total_rows = count($contents);
			// base
			$data = array('total_rows'=>$total_rows);	
			// by id
			if(isset($id) && (int)$id>0){
				// message
				$message = sprintf(__('Get purchased - %s dy id#%d response','mgm'), $content_type, $id);				
				// data
				if($total_rows > 0) $data = $data + array(mgm_singular($content_type_name)=>array_shift($contents));
			}else{
			// all
				// message
				$message = sprintf(__('Get purchased - %s response - %d %s found','mgm'), $content_type, $total_rows, $content_type);
				// data
				if($total_rows > 0) $data = $data + array($content_type_name=>$contents);
			}
		}
		
		// response
		$response = array('status' => $status, 
		                  'message' => $message
						 );
		
		// data
		if(isset($data)) $response = $response + array('data'=>$data);
		
		// return
		return array($response, 200);		
	}
	
	/** 
	 * get gifted contents
	 *
	 * @verb GET
	 * @action all 	
	 * @url <site>/mgmapi/contents/gifted.<format>  -- list all post types
	 * @url <site>/mgmapi/contents/gifted/:(posts|pages|custom_post_type).<format> -- list only specified post type
	 * @url <site>/mgmapi/contents/gifted/:(posts|pages|custom_post_type)/:id.<format> -- list specified post type by post id
	 *	
	 * @param string $post_type
	 * @param int $id
	 * @since 1.0
	 */
	public function gifted_get($post_type='', $id=NULL){
		global $wpdb;
		
		// get vars
		$get_vars = $this->request->data['get'];
		
		// start
		$start = (isset($get_vars['start'])) ? (int)$get_vars['start'] : 0 ;
		// rows
		$rows = (isset($get_vars['rows'])) ? (int)$get_vars['rows'] : 100 ;	
		
		// status
		$status = 'success';
		$message = '';		
				
		// registered post types
		$post_types = mgm_get_post_types(false);
		// all post types
		if(empty($post_type)){
			// get
			$contents = $this->_get_purchased_contents($post_types, $id, $start, $rows, true);
			// content type
			$content_type = 'all post types';
		}else{
			// post/page
			if(in_array($post_type, array('posts','pages'))){
				$post_type = mgm_singular($post_type);
			}
			// validate
			if(!in_array($post_type, $post_types)){
			// error
				$status = 'error';
				$message = sprintf(__('Specified post type - %s is invalid, try with a valid post type only','mgm'), $post_type);
			}else{											
				// get
				$contents = $this->_get_purchased_contents($post_type, $id, $start, $rows, true);
				// content type
				$content_type = $post_type;
			}		
		}	
		// name
		$content_type_name = 'contents';						
		 
		// data when contents found
		if(isset($contents)){
			// total rows
			$total_rows = count($contents);
			// base
			$data = array('total_rows'=>$total_rows);	
			// by id
			if(isset($id) && (int)$id>0){
				// message
				$message = sprintf(__('Get gifted - %s dy id#%d response','mgm'), $content_type, $id);				
				// data
				if($total_rows > 0) $data = $data + array(mgm_singular($content_type_name)=>array_shift($contents));
			}else{
			// all
				// message
				$message = sprintf(__('Get gifted - %s response - %d %s found','mgm'), $content_type, $total_rows, $content_type);
				// data
				if($total_rows > 0) $data = $data + array($content_type_name=>$contents);
			}
		}
		
		// response
		$response = array('status' => $status, 
		                  'message' => $message
						 );
		
		// data
		if(isset($data)) $response = $response + array('data'=>$data);
		
		// return
		return array($response, 200);
	}
	
	// private ------------------------------------------------------------------------
	
	// get protected contents
	private function _get_protected_contents($post_types, $id=false, $start=0, $rows=100){
		global $wpdb;		
		// array or string
		if(!is_array($post_types)) $post_types = array($post_types);
		// impode
		$post_types_in = mgm_map_for_in($post_types);
		// from
		$sql_from = " FROM " . $wpdb->posts . " A JOIN " . $wpdb->postmeta . " B ON (A.ID = B.post_id ) 
					  WHERE post_status = 'publish' AND B.meta_key LIKE '_mgm_post%' 
					  AND post_type IN ( {$post_types_in} )";		
		// id
		if( $id ) $sql_from .= " AND ID = '{$id}' ";			  
		// get posts	
		$results = $wpdb->get_results("SELECT DISTINCT(ID), post_type, post_title, post_date, post_content {$sql_from} ORDER BY post_date DESC LIMIT {$start},{$rows}");	
		// log
		// mgm_log($wpdb->last_query, __FUNCTION__);
		// init
		$posts = array();	
		// check
		if($results){
			// loop
			foreach($results as $post){
				// get object
				$post_obj = mgm_get_post($post->ID);	
				// check
				if(mgm_post_is_protected($post->ID, $post_obj)){	
					// stip short code
					$post->post_content = mgm_strip_shortcode($post->post_content);					
					// access type
					$access_types = $post_obj->get_access_membership_types();
					// access delay
					$access_delays = $post_obj->get_access_delay();
					// init 
					$access_settings = array();
					// loop
					foreach($access_types as $access_type){
						// delay
						$delay = isset($access_delays[$access_type]) ? (int)$access_delays[$access_type] : 0;
						// set
						$access_settings[] = array('membership_type' => array('code' => $access_type, 'name' => mgm_get_membership_type_name($access_type)), 'access_delay' => sprintf(__('%d day', 'mgm'), $delay) );
					}
					// access
					$post->access_settings = $access_settings;
					// set
					$posts[] = $post;
				}
			}
		}
		
		// mgm_log($posts, __FUNCTION__);
		// return
		return $posts;
	}
	
	// get protected taxonomies
	private function _get_protected_taxonimies($taxonomies){
		global $wpdb;	
		// array or string
		if(!is_array($taxonomies)) $taxonomies = array($taxonomies);
		// membership types
		$access_membership_types['category'] = mgm_get_class('post_category')->get_access_membership_types();
		// membership types
		$access_membership_types['taxonomy'] = mgm_get_class('post_taxonomy')->get_access_membership_types();
		// init
		$membership_types = array();
		// taxonomies
		$terms = get_terms($taxonomies, array('hide_empty'=>0));
		// taxonomies
		$taxonomies = array();
		// loop
		foreach($terms as $term){
			// $access_settings
			$access_settings = array();
			// check
			if(isset($access_membership_types[$term->taxonomy][$term->term_id])){
				// loop
				if($membership_types = $access_membership_types[$term->taxonomy][$term->term_id]){
					// loop
					foreach($membership_types as $membership_type){
						$access_settings[] = array('membership_type' => array('code' => $membership_type, 'name' => mgm_get_membership_type_name($membership_type)));
					}
				}
			}else{
				$access_settings = 'public';
			}
			// access_settings
			$term->access_settings = $access_settings;	
			// store
			$taxonomies[] = $term;		
		}
		// return
		return $taxonomies;
	}
	
	// get purchasable contents
	function _get_purchasable_contents($post_types, $id, $start, $rows){
		global $wpdb;		
		// array or string
		if(!is_array($post_types)) $post_types = array($post_types);
		// impode
		$post_types_in = mgm_map_for_in($post_types);
		// from
		$sql_from = " FROM " . $wpdb->posts . " A JOIN " . $wpdb->postmeta . " B ON (A.ID = B.post_id ) 
					  WHERE post_status = 'publish' AND B.meta_key LIKE '_mgm_post%' 
					  AND post_type IN ( {$post_types_in} )";		
		// get posts	
		$results = $wpdb->get_results("SELECT DISTINCT(ID), post_type, post_title, post_date, post_content {$sql_from} ORDER BY post_date DESC LIMIT {$start},{$rows}");	
		// init
		$posts = array();	
		// check
		if($results){
			// loop
			foreach($results as $post){
				// get object
				$post_obj = mgm_get_post($post->ID);	
				// check
				if(mgm_post_is_purchasable($post->ID, $post_obj)){			
					// stip short code
					$post->post_content = mgm_strip_shortcode($post->post_content);		
					// access type
					$access_types = $post_obj->get_access_membership_types();
					// access delay
					$access_delays = $post_obj->get_access_delay();
					// init 
					$access_settings = array();
					// loop
					foreach($access_types as $access_type){
						// delay
						$delay = isset($access_delays[$access_type]) ? (int)$access_delays[$access_type] : 0;
						// set
						$access_settings[] = array('membership_type' => array('code' => $access_type, 'name' => mgm_get_membership_type_name($access_type)), 'access_delay' => sprintf(__('%d day', 'mgm'), $delay) );
					}
					// access
					$post->access_settings = $access_settings;
					// set
					$posts[] = $post;
				}
			}
		}
		
		// return
		return $posts;
	}
	
	// get purchased contents
	function _get_purchased_contents($post_types, $id, $start, $rows, $gifted=false){
		global $wpdb;		
		// array or string
		if(!is_array($post_types)) $post_types = array($post_types);
		// impode
		$post_types_in = mgm_map_for_in($post_types);
		// gifted
		$gifted_sql = ($gifted) ? "AND is_gift = 'Y'" : "AND is_gift = 'N'";
		// from
		$sql_from = " FROM " . $wpdb->posts . " A JOIN " . TBL_MGM_POST_PURCHASES . " B ON(A.ID = B.post_id) 
					  WHERE post_status = 'publish' AND post_type IN ( {$post_types_in} ) {$gifted_sql}";		
		// sql
		$sql = "SELECT DISTINCT(A.ID), post_type, post_title, post_date, post_content, user_id,guest_token {$sql_from} 
		        ORDER BY post_date DESC LIMIT {$start},{$rows}";
		// get posts	
		$results = $wpdb->get_results($sql);	
		// init
		$posts = array();	
		// check
		if($results){
			// loop
			foreach($results as $post){
				// get object
				$post_obj = mgm_get_post($post->ID);	
				// check
				if(mgm_post_is_purchasable($post->ID, $post_obj)){		
					// stip short code
					$post->post_content = mgm_strip_shortcode($post->post_content);				
					// access type
					$access_types = $post_obj->get_access_membership_types();
					// access delay
					$access_delays = $post_obj->get_access_delay();
					// init 
					$access_settings = array();
					// loop
					foreach($access_types as $access_type){
						// delay
						$delay = isset($access_delays[$access_type]) ? (int)$access_delays[$access_type] : 0;
						// set
						$access_settings[] = array('membership_type' => array('code' => $access_type, 'name' => mgm_get_membership_type_name($access_type)), 'access_delay' => sprintf(__('%d day', 'mgm'), $delay) );
					}
					// access
					$post->access_settings = $access_settings;
					// user
					if((int)$post->user_id > 0){
						// user
						$user = get_userdata($post->user_id);
						$user_info = array('by'=>'user', 'id'=>$post->user_id, 'username'=>$user->user_login,'email'=>$user->user_email);
						// gifted
						if($gifted){
							$post->gift = array_slice($user_info, 1);
						}else{
							$post->purchase = $user_info;
						}
					}else{
						$post->purchase = array('by'=>'guest', 'token' => $post->guest_token);
					}					
					// unset
					unset($post->guest_token,$post->user_id);
					// set
					$posts[] = $post;
				}
			}
		}
		
		// return
		return $posts;
	}
}
// return name of class 
 return basename(__FILE__,'.php');
// end file /core/api/mgm_api_contents.php			