<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin functions
 *
 * @package MagicMembers
 * @since 2.5
 */
// infobar
function mgm_render_infobar() {
	// get auth
	$auth = mgm_get_class('auth');
	// service_domain
	$service_domain = $auth->get_product_info('product_service_domain');
	// style	
	$style = 'class="mgm_render_infobar"';	
	// print
	echo '<div id="mgm-info" class="mgm_render_infobar_div">
			<strong>
				<a href="' . $service_domain . '" ' . $style . ' target="_blank">'.__('Powered by Magic Members','mgm').'</a> |
				<a href="' . $service_domain . 'support" ' . $style . ' target="_blank">'.__('Support','mgm').'</a> |
				<a href="' . $service_domain . $auth->get_product_info('product_url') .'" ' . $style . ' target="_blank">V. ' . $auth->get_product_info('product_version') . ' - '. $auth->get_product_info('product_name') .'</a>
			</strong>
		</div>';
}
// get tip
function mgm_get_tip($key){
	global 	$mgm_tips_info;
	return (isset($mgm_tips_info[$key]))?$mgm_tips_info[$key]:"[tip '$key' not available]";
}	
// get video
function mgm_get_video($key){	
	global 	$mgm_videos_info;
	return (isset($mgm_videos_info[$key]))?$mgm_videos_info[$key]:"[video '$key' not available]";
} 
// box top
function mgm_box_top($title, $helpkey='', $return=false, $attributes=false){
	// defaults
	$attributes_default=array('width'=>845, 'style'=>'margin:10px 0;');
	// attributes
	if(is_array($attributes)){
		$options = array_merge($attributes_default,$attributes);
	}else{
		$options = $attributes_default;
	}
	// local
	extract($options);	
	
	// help key
	if(empty($helpkey)){
		$helpkey = strtolower(preg_replace('/\s+/','',$title));
	}
	// html
	$html= '<div class="mgm-panel-box" style="'.$style.($width ? ';width: ' . ($width) . 'px;':'') .'">			
				<div class="box-title" style="' .($width ? 'width: ' . ($width-5) . 'px;':'') . '">			
					<h3>'.__($title, 'mgm').'</h3>				
					<div class="box-triggers">
						<img src="'.MGM_ASSETS_URL.'images/panel/help-image.png" alt="description" class="box-description" />							
						<img src="'.MGM_ASSETS_URL.'images/panel/television.png" alt="video" class="box-video" />
					</div>						
					<div class="box-description-content">				
						<div>'.mgm_get_tip($helpkey).'</div>				
					</div> <!-- end box-description-content div -->						
					<div class="box-video-content">				
						<div>'.mgm_get_video($helpkey).'</div>				
					</div> <!-- end box-video-content div -->				
				</div> <!-- end div box-title -->				
				<div class="box-content">';	
			  
	// return output
	if ($return) {
		return $html;
	} else {
		echo $html;
	}
}
// box bottom
function mgm_box_bottom($return=false){
	$html = '</div>
		   </div>';
	if ($return) {
		return $html;
	} else {
		echo $html;
	}
}
// box top
function mgm_set_tip($helpkey){	
	// html
	$html= '			
			<div class="box-triggers-left">
				<img src="'.MGM_ASSETS_URL.'images/panel/help-image.png" alt="description" class="box-description" />							
			</div>						
			<div class="box-description-content">				
				<div>'.mgm_get_tip($helpkey).'</div>				
			</div> <!-- end box-description-content div -->						
			';	
			  
	// return output	
	echo $html;	
}

/**
 * get post types
 *
 * @param bool $join for sql
 * @param array $exclude exclude 
 * @return mixed array or string
 */ 
function mgm_get_post_types($join=true, $exclude=array()){
	// get post types
	$post_types = get_post_types( array('public' => true), 'names' );
	// default
	if(!$post_types) $post_types = array('post','page');	
	// init
	$_post_types = array();
	// internal
	$exclude = array_merge(array('attachment','revision','nav_menu_item'), $exclude);
	// filter out un needed
	foreach($post_types as $post_type){
		// check
		if(in_array($post_type, $exclude)) continue;
		// set
		$_post_types[] = $post_type;
	}
	// return
	return ($join) ? mgm_map_for_in($_post_types) : $_post_types;
}

/**
 * get taxonomies
 *
 * @param bool $join for sql
 * @param array $exclude exclude 
 * @return mixed array or string
 */ 
function mgm_get_taxonomies($join=true, $exclude=array()){
	// get taxonomies
	$taxonomies = get_taxonomies( array('public' => true), 'names' );
	// default
	if(!$taxonomies) $taxonomies = array('category');	
	// init
	$_taxonomies = array();	
	// internal
	$exclude = array_merge(array('post_tag','post_format'), $exclude);
	// filter out un needed
	foreach($taxonomies as $taxonomy){
		// check
		if(in_array($taxonomy, $exclude)) continue;
		// set
		$_taxonomies[] = $taxonomy;
	}
	// return
	return ($join) ? mgm_map_for_in($_taxonomies) : $_taxonomies;
}
// get_purchasable_posts
function mgm_get_purchasable_posts($exclude=false){
	global $wpdb;
	// exclude
	$exclude_sql='';
	if(is_array($exclude) && count($exclude)>0){
		$exclude_sql = "AND A.ID NOT IN (".implode(',', $exclude).")";
	}
	// get types
	$post_types_in = mgm_get_post_types(true);
	// update to include both _mgm_post_options and _mgm_post for old and new option name
	// sql
	$sql = "SELECT DISTINCT(A.ID) AS id, A.post_title AS post_title FROM " . $wpdb->posts . " A	JOIN " . $wpdb->postmeta . " B 
	        ON (A.ID = B.post_id AND B.meta_key LIKE '_mgm_post%' ) WHERE A.post_status = 'publish' 
			AND A.post_type IN ({$post_types_in}) {$exclude_sql} ORDER BY A.post_title";										
	// fetch
	$rows = $wpdb->get_results($sql);	
	// init
	$purchasable_posts = array();
	// captured
	if($rows){	
		// loop	
		foreach($rows as $row){
			// get post object
			$post_obj = mgm_get_post($row->id);
			// in array
			if($post_obj->purchasable =='Y'){
				$purchasable_posts[$row->id] = $row->post_title;
			}
			// unset
			unset($post_obj);
		}
	} 	
	// return
	return $purchasable_posts;
}
// check version
function mgm_check_version() {
	echo mgm_get_class('auth')->check_version();
}
// get message
function mgm_get_messages() {	
    echo mgm_get_class('auth')->get_messages();
}
// get subscription status
function mgm_get_subscription_status(){ 
	echo mgm_get_class('auth')->get_subscription_status();;
}
// check api 2.0 status
function mgm_check_auto_upgrader_api() {
	echo mgm_get_class('auth')->check_auto_upgrader_api();
}
// site rss news
function mgm_site_rss_news(){
	// check cache
    if( !$items = get_transient('mgm_site_rss_news_items') ){	
    	// fetch and save in cache		
		$items = mgm_get_rss(MGM_GET_NEWS_URL, 'mgm_site_rss_news_items', 5);		
	}
     
    // check    
	if (is_array($items)) {
		foreach ($items as $item){
			$content = $item['description'];
			if (strlen($content > '500')) {
				$content = substr($content, 0, '500') . '...';
			}
			echo "<div class='mgm_site_rss_news'>
				  	<div class='mgm_site_rss_title'>
						<a href='".$item['link']."'>".$item['title']."</a>
					</div>
					<div class='mgm_site_rss_content'>".$content."</div>
				 </div>";
		}
	} else { 
		echo '<ul><li>'.$items.'</li></ul>';
	}
}
// rss blog
function mgm_site_rss_blog(){
	// check cache
    if( !$items = get_transient('mgm_site_rss_blog_items') ){	
    	// fetch and save in cache		
		$items = mgm_get_rss(MGM_GET_BLOG_URL, 'mgm_site_rss_blog_items', 5);		
	}
	
	echo '<ul>';
	if(is_array($items)){		
		foreach ( $items as $item ){
			echo "<li><a href='".$item['link']."'>".$item['title']."</a></li>";
		}
	} else { 
		echo '<li>'.$items.'</li>';
	}	
	echo '</ul>';
}
// rss parsing
function mgm_get_rss($url, $rss_cache_keyname, $maxitems){
	// lib
	@require_once (ABSPATH . WPINC . '/rss.php');
	// fetch
	if( $rss = fetch_rss($url) ) {
		// set
		$items = array_slice($rss->items, 0, $maxitems);
		// set cache		
		set_transient($rss_cache_keyname, $items, 3600);// 1 hour
	} else {
		$items = 'Error fetching the feed';
	}
	// return
	return $items;
}

/**
 * membership statistics by type/level count on dashboard
 *
 * @param bool $return
 * @return mixed html|array
 */
function mgm_member_level_statistics($return=false) {
	// Fetch array of membership count
	$statistics = mgm_get_dashboard_widget_data('membership_count');
	// return 
	if($return) return $statistics;

	$html = '<div class="table mgm_member_level_statistics">
  				<div class="row ">
    				<div class="width70 cell mgm_member_level_membership_types">'.__('Membership Status','mgm').'</div>
    				<div class="width30 cell mgm_member_level_users textalignright">'.__('Users','mgm').'</div>
    			</div>';
	// check
	foreach($statistics as $statistic){
		$html .= '<div class="row ">
				    <div class="width70 cell mgm_member_level_values">' . $statistic['name'] . '</div>
				    <div class="width30 cell mgm_member_level_values textalignright">' . $statistic['count'] . '</div>
			      </div>';
	}

	// end
	$html .= '</div>';
	
	// return 
	echo $html;
}

/**
 * membership statistics by status count on dashboard
 *
 * @param bool $return
 * @return mixed html|array
 */
function mgm_member_status_statistics($return=false) {
	// Fetch array of status count
	$statistics = mgm_get_dashboard_widget_data('status_count');
	// return 
	if($return) return $statistics;

	$html = '<div class="table mgm_member_level_statistics">
  				<div class="row">
    				<div class="width70 cell mgm_member_level_membership_types">'.__('Membership Status','mgm').'</div>
    				<div class="width30 cell mgm_member_level_users textalignright">'.__('Users','mgm').'</div>
    			</div>';
		
	// create table
	// check
	foreach($statistics as $statistic){

		$html .= '<div class="row">
				    <div class="width70 cell mgm_member_level_values">' . $statistic['name'] . '</div>
				    <div class="width30 cell mgm_member_level_values textalignright">' . $statistic['count'] . '</div>
			      </div>';
	}
	
	// end
	$html .= '</div>';

	
	// return 
	echo $html;
}

/**
 * Shows total revenue for Subscription packages
 *
 * @param boolean $return
 * @return array/html string: if $return is true, an array of Subscription packages and total revenue will be returned
 * 								Eg:  [Gold[Package #4]] => 21.00
 *
 *  oldname mgm_membership_statistics
 * dev/incomplete
 */
function mgm_member_package_statistics($return = false) {
	global $wpdb;
	
	$arr_total = array(); 	
	// get membership_types
	$mt_obj	= mgm_get_class('membership_types');	
	$sp_obj = mgm_get_class('subscription_packs');	
	
	// packs
	$packages = $sp_obj->packs;
	
	// get counts
	$counts = mgm_get_subscription_package_users_count($packages);
	
	// init
	$statistics = array();
	// loop packs
	foreach ($packages as $package) {		
		// name
		$name = sprintf('%s [#%d]', $package['description'], $package['id']);
		// store
		$statistics[] = array('count'=>$counts[$package['id']], 'name'=>mgm_stripslashes_deep($name), 'id'=>$package['id']);		
	}			
	
	// return 
	if($return) return $statistics;
			
	/*// check
	foreach ($mt_obj->membership_types as $type_code=>$type_name) {
		// packs
		foreach ($sp_obj->packs as $pack) {
			//check pack:
			if($pack['membership_type'] == $type_code) {
				// members
				// the below function will return user ids satisfying (membership_type AND pack_id )
				$members = mgm_get_members_with('membership_type', $type_code, array('pack_id' => $pack['id']));
				
				$count = isset($members) ? count($members) : 0;				
				$total = number_format($count * $pack['cost'],2,'.', null );
				
				$label = mgm_stripslashes_deep($type_name) . '[' .sprintf(__('Package #%d','mgm'),$pack['id']).']';
				if(!$return) {
					echo '<tr>
							<td style="border-bottom: 1px solid #EFEFEF;" align="left">' . $label . '</td>
							<td style="border-bottom: 1px solid #EFEFEF;" align="left" valign="top">' . $total . '</td>
						  </tr>';
				}else {
					//update array
					$arr_total[$label] = $total;					
				}
			}
		}
	}
	
	// create table
	if(!$return) {
		echo '<table style="width:100%;">';
		echo '  <tr>
					<td style="border-bottom: 1px solid #EFEFEF; font-weight:bold;">'.__('Subscription Package','mgm').'</td>
					<td style="border-bottom: 1px solid #EFEFEF; font-weight:bold; width:20%;">'.__('Total','mgm').'</td>
				</tr>';
	}	
	
	if(!$return) {
		echo '</table>';
	}else {
		//return array
		return $arr_total;
	}*/
}
// purchased posts on dashboard
function mgm_render_posts_purchased($limit=false) {
	global $wpdb;

	$prefix = $wpdb->prefix;
	$sql = "SELECT B.post_title AS title, COUNT(B.id) AS count
			FROM `" . TBL_MGM_POST_PURCHASES."` A
			JOIN " . $wpdb->posts . " B ON (B.id = A.post_id)
			GROUP BY A.post_id	ORDER BY A.post_id DESC";

	$results = $wpdb->get_results($sql,'ARRAY_A');

	echo '<div class="table mgm_member_level_statistics">
		  	<div class="row">
				<div class="width70 cell mgm_member_level_membership_types">'.__('Post Title', 'mgm').'</div>
				<div class="width30 cell mgm_render_posts_purchased">'.__('Purchased', 'mgm').'</div>
			</div>';
	// check		
	if (isset($results[0]) && count($results[0])) {// @todi why index being checked
		$loop = 1;
		foreach ($results as $result) {

			echo '<div class="row">
					<div class="width70 cell mgm_member_level_values">' . $result['title'] . '</div>
					<div class="width30 cell mgm_render_posts_purchased_values">' . $result['count'] . '</div>';
			echo '</div>';

			$loop++;

			if ($limit && $loop == $limit) {
				break;
			}
		}
	} else {
		echo '<div class="row">
					<div class="width30 cell">'.__('No posts have been sold yet', 'mgm').'</div>
			  </div>';
	}

	echo '</div>';
	
	// show all link
	// check PPP - Post Packs tab is enabled for the user
	$show_link = mgm_is_mgm_menu_enabled('secondary', 'mgm_ppp', 'mgm_post_packs');	
	if ($limit!==false && $show_link){
		echo '<div class="mgm_render_posts_purchased_show_link">
				<a href="javascript:mgm_set_tab_url(3,1)">'.__('View All', 'mgm').' &#0187;</a>
			  </div>';
	}
}

/**
 * find members with selected critera
 *
 * @param string $field
 * @param mixed array|string $value
 * @param array $params
 * @param string $return ( results|count)
 * @return mixed array|int  
 */

function mgm_get_members_with($field, $value, $params = array(), $return='results'){
	
	global $wpdb;	
	
	$start = 0;
	$limit = 1000;
	//user meta fields
	$fields= array('user_id','meta_value');	
	// sql
	$sql = "SELECT count(*) FROM `{$wpdb->usermeta}` WHERE `meta_key` = 'mgm_member_options' AND `user_id` <> 1";	
	$count  = $wpdb->get_var($sql);		
	// init 
	$members = array();
	$members_status = array();
	//count	
	if($count) {
		
		for( $i = $start; $i < $count; $i = $i + $limit ) {
			
			$users = mgm_patch_partial_user_member_options($i, $limit, $fields);
						
			foreach ($users as $user) {
				$user_id = $user->user_id;
				$member = unserialize($user->meta_value);
				// convert
				$member = mgm_convert_array_to_memberobj($member, $user_id);
				// matrch field
				switch($field){
					case 'pack_id':						
						//cast 
						$pack_id = (int)$member->{$field};
						$value = (int)$value;	
						// match
						if($pack_id == $value){
							$valid = true;
							//mgm_log('user_id : ' .$user_id . ' pack_id : ' .$pack_id . ' value : ' .$value,__FUNCTION__);
						}else {
							$valid = false;
						}
						// check other
						if(!$valid && isset($member->other_membership_types) && is_array($member->other_membership_types) && count($member->other_membership_types) > 0) {
							// loop
							foreach ($member->other_membership_types as $key => $memtypes) {
								// convet
								if(is_array($memtypes)) $memtypes = mgm_convert_array_to_memberobj($memtypes,$user_id);
								// skip default values:
								if(strtolower($memtypes->membership_type) == 'guest' || $memtypes->status == MGM_STATUS_NULL ) continue;
								//cast
								$pack_id = (int)$memtypes->{$field};
								// match
								if($pack_id == $value){
									// reset if already saved
									$valid = true;
									break;
									//mgm_log('user_id : ' .$user_id . ' pack_id : ' .$pack_id . ' value : ' .$value,__FUNCTION__);
								}else {
									$valid = false;
								}
							}
						}
					break;					
					case 'last_pay_date':
					case 'expire_date':
						// match
						if($member->{$field} == date('Y-m-d', strtotime($value))){
							$valid = true;
						}
						// check other
						if(!$valid && isset($member->other_membership_types) && is_array($member->other_membership_types) && count($member->other_membership_types) > 0) {
							// loop
							foreach ($member->other_membership_types as $key => $memtypes) {
								// convet
								if(is_array($memtypes)) $memtypes = mgm_convert_array_to_memberobj($memtypes,$user_id);
								// skip default values:
								if(strtolower($memtypes->membership_type) == 'guest' || $memtypes->status == MGM_STATUS_NULL ) continue;
								// match
								if($memtypes->{$field} == date('Y-m-d', strtotime($value)) ) {
									// reset if already saved
									$valid = true;
									break;
								}
							}
						}
					break;
					case 'first_name':
					case 'last_name':			
						// check		
						if(!empty($value)) {
							// from custom field
							if(!empty($member->custom_fields->$field)) {
								$name = $member->custom_fields->$field;							
							}else  {
							// from user meta
								$userdata = get_userdata($user_id);
								// name
								$name = $userdata->$field;
								// unset
								unset($userdata);
							}
							// clean
							$value = mgm_stripslashes_deep($value);						
							// if there is a match
							if (!empty($name) && strpos(strtolower($name),strtolower($value)) !== false) {
							    $valid = true;
							}else {
								$valid = false;
							}
						}
					break;
					case 'payment_module':
					case 'payment_info':			
						// check		
						if(!empty($value)) {						
							// clean
							$value = mgm_stripslashes_deep($value);
							// if there is a match
							if(!empty($member->payment_info->module) && trim(strtolower($value)) == trim(strtolower($member->payment_info->module))) {
								$valid = true;
							}else {
								$valid = false;
							}
						}
					break;
					default: 
						// For status field
						if ($field == 'status' && is_array($value) && !empty($value)) {
							// init
							$valid_item = false;
							// Assuming this will always be for getting count for given fields values
							foreach ($value as $fd_value) {
								// check
								$valid_item = mgm_compare_member_field($member, $field, $fd_value, $params);
								if (!isset($members_status[$fd_value]))
									$members_status[$fd_value] = 0;
								if ($valid_item) {
									$valid = true;
									// Increment	
									$members_status[$fd_value]++; 
								}
							}
						}else {
						// All the other fields	
							$valid = mgm_compare_member_field($member, $field, $value, $params);
						}
					break;		
				}
				// store
				if($valid){
					$members[] = $user_id;
				}
				// unset object
				unset($member);
			}
			//a small delay of 0.01 second 			
			usleep(10000);			
		}
	}		
	// return
	return ($return == 'count') ? count($members) : (!empty($members_status) ? $members_status :$members);
}

/**
 * patch member options for users
 */
function mgm_patch_partial_user_member_options($start, $limit, $fields) {
	global $wpdb;
	$qry = "SELECT ". implode(",", $fields) ." FROM `{$wpdb->usermeta}` WHERE `meta_key` = 'mgm_member_options' AND `user_id` <> 1 ORDER BY `user_id` LIMIT ". $start.",".$limit;	
	//mgm_log($qry);
	$result  = $wpdb->get_results($qry);	
	return $result;
}

/**
 * find members with selected critera
 *
 * @param string $field
 * @param mixed array|string $value
 * @param array $params
 * @param string $return ( results|count)
 * @return mixed array|int  
 */
//Method depricated due to performace, issue #1483
/*
function mgm_get_members_with($field, $value, $params = array(), $return='results'){
	global $wpdb;	
	// read from cache:
	$users = wp_cache_get('all_user_ids', 'users');		
	// if empty read from db:
	if(empty($users)) {
		// read again
		$users = mgm_get_all_userids(array('ID'), 'get_results');
		// update cache with user ids:
		wp_cache_set('all_user_ids', $users, 'users');		
	}	
	// init 
	$members = array();
	$members_status = array();
	// check
	if($users){
		// loop
		foreach($users as $user){
			// vlid
			$valid = false;
			// member object
			$member= mgm_get_member($user->ID);			
			// matrch field
			switch($field){
				case 'last_pay_date':
				case 'expire_date':
					// match
					if($member->{$field} == date('Y-m-d', strtotime($value))){
						$valid = true;
					}
					// check other
					if(!$valid && isset($member->other_membership_types) && is_array($member->other_membership_types) && count($member->other_membership_types) > 0) {
						// loop
						foreach ($member->other_membership_types as $key => $memtypes) {
							// convet
							$memtypes = mgm_convert_array_to_memberobj($memtypes, $user->ID);
							// skip default values:
							if(strtolower($memtypes->membership_type) == 'guest' || $memtypes->status == MGM_STATUS_NULL ) continue;
							// match
							if($memtypes->{$field} == date('Y-m-d', strtotime($value)) ) {
								// reset if already saved
								$valid = true;
								break;
							}
						}
					}
				break;
				case 'first_name':
				case 'last_name':			
					// check		
					if(!empty($value)) {
						// from custom field
						if(!empty($member->custom_fields->$field)) {
							$name = $member->custom_fields->$field;							
						}else  {
						// from user meta
							$userdata = get_userdata($user->ID);
							// name
							$name = $userdata->$field;
							// unset
							unset($userdata);
						}
						// clean
						$value = mgm_stripslashes_deep($value);						
						// if there is a match
						if(!empty($name) && preg_match("/{$value}/i", $name)) {
							$valid = true;
						}
					}
				break;
				case 'payment_module':
				case 'payment_info':			
					// check		
					if(!empty($value)) {						
						// clean
						$value = mgm_stripslashes_deep($value);						
						// if there is a match
						if(!empty($member->payment_info->module) && $value == $member->payment_info->module) {
							$valid = true;
						}
					}
				break;
				default: 
					// For status field
					if ($field == 'status' && is_array($value) && !empty($value)) {
						// init
						$valid_item = false;
						// Assuming this will always be for getting count for given fields values
						foreach ($value as $fd_value) {
							// check
							$valid_item = mgm_compare_member_field($member, $field, $fd_value, $params);
							if (!isset($members_status[$fd_value]))
								$members_status[$fd_value] = 0;
							if ($valid_item) {
								$valid = true;
								// Increment	
								$members_status[$fd_value]++; 
							}
						}
					}else {
					// All the other fields	
						$valid = mgm_compare_member_field($member, $field, $value, $params);
					}
				break;		
			}
			
			// store
			if($valid){
				$members[] = $user->ID;
			}
			// unset object
			unset($member);
		}
	}
	
	// return
	return ($return == 'count') ? count($members) : (!empty($members_status) ? $members_status :$members);
}*/
/**
 * Compare member object field with given field for a value
 *
 * @param object $member
 * @param string $field
 * @param string $value
 * @param array $params
 * @return boolean  
 */
function mgm_compare_member_field($member, $field, $value, $params) {
	$valid = false;
	// check - issue #1617
	if(strtolower(trim($member->{$field})) == strtolower(trim($value))){
	// valid
		$valid = true;
		
		// check extra parameters: Loop through $extra_params ['field' => 'value'] to find a match with AND operator
		if(!empty($params)) {
			// loop
			foreach ($params as $ext_field => $ext_value) {
				// valid
				if($valid) {
					// check
					if($member->{$ext_field} != $ext_value){
						// not valid
						$valid = false;
						// as the operator is AND, no longer need to loop
						break;
					}	
				}
			}
		}
	}
	// check other - issue #1483
	if(!$valid && isset($member->other_membership_types) && is_object($member->other_membership_types) && !empty($member->other_membership_types) ) {	
		// loop
		foreach ($member->other_membership_types as $key => $memtypes) {
			// convert
			if(is_array($memtypes)) $memtypes = mgm_convert_array_to_memberobj($memtypes, $member->id);
			
			// match -issue #1617
			if(strtolower(trim($memtypes->{$field})) == strtolower(trim($value))){
				//reset if already saved
				$valid = true;
				
				//check extra parameters: Loop through $extra_params ['field' => 'value'] to find a match with AND operator
				if(!empty($params)) {
					// loop
					foreach ($params as $ext_field => $ext_value) {
						// valid
						if($valid) {
							// check
							if($memtypes->{$ext_field} != $ext_value) {
								// invalid
								$valid = false;
								//as the operator is AND, no longer need to loop
								break;
							}
						}
					}
				}
				// exit as first condition is satisfied
				break;
			}
		}
	}
	return $valid;
}


/**
 * find members with two dates  critera
 *
 * @param string $field
 * @param string $value
 * @param array $params
 * @param string $return ( results|count)
 * @return mixed array|int  
 */
function mgm_get_members_between_two_dates($field, $value_one, $value_two){
	global $wpdb;	
	$start = 0;
	$limit = 1000;
	//user meta fields
	$fields= array('user_id','meta_value');	
	// sql
	$sql = "SELECT count(*) FROM `{$wpdb->usermeta}` WHERE `meta_key` = 'mgm_member_options' AND `user_id` <> 1";	
	$count  = $wpdb->get_var($sql);		
	// init 
	$members = array();	
	//count	
	if($count) {
		
		for( $i = $start; $i < $count; $i = $i + $limit ) {
			
			$users = mgm_patch_partial_user_member_options($i, $limit, $fields);
			
			foreach ($users as $user) {
				$user_id = $user->user_id;
				$member = unserialize($user->meta_value);
				// convert member object
				$member = mgm_convert_array_to_memberobj($member, $user_id);
				// vlid
				$valid = false;		
				// matrch field
				switch($field){
					case 'last_pay_date':
					case 'expire_date':
					case 'join_date':					
						
						// take only date part #1023 related
						if($field =='join_date') {
							$field_ts = strtotime(date('Y-m-d', $member->{$field}));
						} else {
							$field_ts = strtotime(date('Y-m-d', strtotime($member->{$field})));
						}			
						mgm_log("User id: ".$member->id."User Exp : ".$member->{$field}. " User Start : ".$value_one. " User End : ".$value_two);
						// check between
						if($field_ts >= strtotime($value_one) && $field_ts <= strtotime($value_two) ){
						// valid
							$valid = true;
						}
						// check other - issue #1483
						if(!$valid && isset($member->other_membership_types) && is_object($member->other_membership_types) && !empty($member->other_membership_types) ) {	
							// loop
							foreach ($member->other_membership_types as $key => $memtypes) {
								// convet
								if(is_array($memtypes)) $memtypes = mgm_convert_array_to_memberobj($memtypes, $user_id);
								// skip default values:
								if(strtolower($memtypes->membership_type) == 'guest' || $memtypes->status == MGM_STATUS_NULL ) continue;
								// take only date part #1023 related
								$field_ts = strtotime(date('Y-m-d', strtotime($memtypes->{$field})));
								// match
								if($field_ts >= strtotime($value_one) && $field_ts <= strtotime($value_two) ){
								// valid
									$valid = true; break;
								}
							}
						}
					break;
				}				
				// store
				if($valid) $members[] = $user_id;
				
				// unset object
				unset($member);				
			}
			//a small delay of 0.01 second 			
			usleep(10000);						
		}	
	}
	// return
	return ($return == 'count') ? count($members) : $members;
	
}

/**
 * find members with two dates  critera
 *
 * @param string $field
 * @param string $value
 * @param array $params
 * @param string $return ( results|count)
 * @return mixed array|int  
 * 
*/
 
/*//Method depricated due to performace, issue #1483
function mgm_get_members_between_two_dates($field, $value_one, $value_two){
	global $wpdb;	
	// read from cache:
	$users = wp_cache_get('all_user_ids', 'users');		
	// if empty read from db:
	if(empty($users)) {
		// read again
		$users = mgm_get_all_userids(array('ID'), 'get_results');
		// update cache with user ids:
		wp_cache_set('all_user_ids', $users, 'users');		
	}	
	// init 
	$members = array();	
	// check
	if($users){
		// loop
		foreach($users as $user){			
			// vlid
			$valid = false;
			// member object
			$member= mgm_get_member($user->ID);		
			// matrch field
			switch($field){
				case 'last_pay_date':
				case 'expire_date':
				case 'join_date':					
					
					// take only date part #1023 related
					if($field =='join_date') {
						$field_ts = strtotime(date('Y-m-d', $member->{$field}));
					} else {
						$field_ts = strtotime(date('Y-m-d', strtotime($member->{$field})));
					}
					
					// check between
					if($field_ts >= strtotime($value_one) && $field_ts <= strtotime($value_two) ){
					// valid
						$valid = true;
					}
					// check other
					if(!$valid && isset($member->other_membership_types) && is_array($member->other_membership_types) && count($member->other_membership_types) > 0) {
						// loop
						foreach ($member->other_membership_types as $key => $memtypes) {
							// convet
							$memtypes = mgm_convert_array_to_memberobj($memtypes, $user->ID);
							// skip default values:
							if(strtolower($memtypes->membership_type) == 'guest' || $memtypes->status == MGM_STATUS_NULL ) continue;
							// take only date part #1023 related
							$field_ts = strtotime(date('Y-m-d', strtotime($memtypes->{$field})));
							// match
							if($field_ts >= strtotime($value_one) && $field_ts <= strtotime($value_two) ){
							// valid
								$valid = true; break;
							}
						}
					}
				break;
			}
			
			// store
			if($valid) $members[] = $user->ID;			
			// unset object
			unset($member);
		}
	}	
	// return
	return ($return == 'count') ? count($members) : $members;
}*/

/**
 * check if a protected directory
 *
 * @param string $dir
 * @return bool 
 */
function mgm_is_protected_dir($dir){
	// protected dirs
	$protected_dirs = array(MGM_FILES_DOWNLOAD_DIR,MGM_FILES_LOG_DIR);
	// check
	return (bool)in_array($dir, $protected_dirs);
}

/**
 * create directory
 *
 * @param string $dir
 * @return bool 
 */
function mgm_create_dir($dir){	
	// create if not created already
	if(!is_dir($dir)){
		// create
		@mkdir( $dir );
		// mode
		@chmod( $dir , 0777 );					
	}		
	// no index.html
	$index_file = $dir . MGM_DS . 'index.html';
	// check existence
	if(!file_exists($index_file)){
		// content
		$index_content = "<html><head><title>403 Forbidden</title></head><body><div>Directory access is forbidden.</div></body></html>";
		// save
		@file_put_contents($index_file, $index_content);
	}
	// protected .htaccess
	$htaccess_file = $dir . MGM_DS . '.htaccess';			
	// check existence
	if(!file_exists($htaccess_file)){
		// only protected dir should have htaccess
		if(mgm_is_protected_dir($dir)){
			// crlf
			$crlf = "\n";
			// content
			if($dir == MGM_FILES_DOWNLOAD_DIR){
				// content
				$htaccess_content = ' Options +FollowSymLinks +ExecCGI' . $crlf .
									' <IfModule mod_rewrite.c>' . $crlf .
									'	 RewriteEngine On' . $crlf .
									'	 RewriteCond %{REQUEST_URI} !(index\.html)$' . $crlf .
									'	 RewriteRule (.*) '.add_query_arg(array('protect'=>'downloads','file'=>'$1'),home_url()).' [QSA]' . $crlf .
									' </IfModule>';
 
			}else{
				// content
				$htaccess_content = '# deny web access' . $crlf .
									' order deny,allow' . $crlf .
									' deny from all' . $crlf .
									' allow from none';
			}	
			// save
			@file_put_contents($htaccess_file, $htaccess_content);	
		}		
	}else{
	// remove old htaccss	
		if(!mgm_is_protected_dir($dir) && file_exists($htaccess_file)){	
			@unlink($htaccess_file);
		}
	}	
}

/**
 * create file directories
 *
 * @param array $dirs
 * @return none 
 */
function mgm_create_files_dir($dirs){	
	// create	
	foreach($dirs as $dir){
		mgm_create_dir($dir);
	}
}

/**
 * create xls file from given data
 *
 * @param array $rowset
 * @param string $name
 * @return void
 */
function mgm_create_xls_file($rowset, $name='export'){	
	// writer
	$xls_writer = new mgm_xls_writer();
	// init vars	
	$row_ctr = 0;
	// columns
	$columns = $xls_arr = array();
	// bof
	$xls_writer->xls_bof();
	// log
	// mgm_log($rowset, __FUNCTION__);
	// loop dataset	
	foreach($rowset as $row){	
		// col
		$col = 0;	
		// create arry
		$data = mgm_object2array($row);			
		// header
		if(empty($columns)){
			// get colums
			$labels = array_keys($data);
			// loop
			foreach($labels as $label){
				// trim
				$label = trim($label);
				// set
				$xls_writer->xls_write_label($row_ctr, $col++, $label);
				// store
				$columns[] = $label;
			}			
			// increment ctr
			$row_ctr++;		
		}		
		// reset col
		$col  = 0;
		// loop cols
		foreach($columns as $column){				
			// value
			$value = (isset($data[$column]) && !empty($data[$column])) ? remove_accents($data[$column]) : 'n/a';
			// array #783
			if(is_array($value)) $value = implode(' ', $value);			
			// limit string length: issue#: 492
			if(strlen($value) > 1024) $value = substr($value, 0, 1020) . '...';
			// trim special chars
			$value = str_replace(array(",", "\r\n", "\n", "\r"), ' ', $value);				
			// set
			$xls_writer->xls_write_label($row_ctr, $col++, trim($value));
		}		
		// increment ctr	
		$row_ctr++;				
	}
	// end
	$xls_writer->xls_eof();
	// xls string	
	$xls_string = $xls_writer->xls_output();	
	// filename		
	$filename = sprintf('%s_%s.xls', $name, date('mdYHis'));
	// create
	if($fp = fopen(MGM_FILES_EXPORT_DIR . $filename, 'w+')){
		// write
		fwrite($fp, $xls_string);
		// close
		fclose($fp);
		// return
		return $filename;
	}
	// return 
	return false;
}

/**
 * create csv file from given data
 */
 function mgm_create_csv_file($rowset, $name='export'){	
 	// file name
 	$filename = sprintf('%s_%s.csv', $name, date('mdYHis'));
 	
	// create
 	if($fp = fopen(MGM_FILES_EXPORT_DIR . $filename, 'w')){
		// columns
		$columns = array();
		// loop data
		foreach ($rowset as $row) {
			// values
			$values = array();
			// create arry
			$data = mgm_object2array($row);		
			// header
			if(empty($columns)){
				// get colums
				$labels = array_keys($data);
				// loop
				foreach($labels as $label){
					// trim
					$label = trim($label);					
					// store
					$columns[] = $label;
				}			
				// put
				fputcsv($fp, $columns, ",");						
			}
				
			// reset col
			$col = 0;
			// loop cols
			foreach($columns as $column){		
				// value
				$value = (isset($data[$column]) && !empty($data[$column])) ? $data[$column] : 'n/a';
				// array #783
				if(is_array($value)) $value = implode(' ', $value);			
				// limit string length: issue#: 492
				if(strlen($value) > 1024) $value = substr($value, 0, 1020) . '...';	
				// trim special char
				$value = str_replace(array(",", "\r\n", "\n", "\r", "\t"), ' ', $value);		
				// values
				$values[] = $value;				
			}	
			// put
			fputcsv($fp, $values, ",");
		}
		// close
		fclose($fp);	
		// mod
		@chmod(MGM_FILES_EXPORT_DIR . $filename, 0777);
		// return
		return $filename;
	}	
	// return 
	return false;
 }
 
/**
 * get jquery ui versions
 */
function mgm_get_jquery_ui_versions(){
	// read
	$_versions = glob(MGM_ASSETS_DIR . implode(MGM_DS, array('js','jquery','jquery.ui')) . MGM_DS . 'jquery-ui-*.js');	
	// init
	$versions = array('1.7.2','1.7.3','1.8.2');
	// check
	if($_versions){
		// loop
		foreach($_versions as $_version){
			// trim
			$versions[] = str_replace(array('jquery-ui-','.min.js'), '', basename($_version));
		}
	}	
	// return 
	return array_unique($versions);	
}
// fetch membership type  - user count
function mgm_get_membershiptype_users_count() {
	global $wpdb;	
	$arr_memtype_count = array();
	// get membership_types
	$membership_types = mgm_get_class('membership_types');	
	foreach ($membership_types->membership_types as $type_code=>$type_name) {
		$arr_memtype_count[$type_code] = 0;
	}
	
	$users = wp_cache_get('all_user_ids', 'users');		
	//if empty read from db:
	if(empty($users)) {
		$users = mgm_get_all_userids(array('ID'), 'get_results');
		//update cache with user ids:
		wp_cache_set('all_user_ids', $users, 'users');		
	}
	$members = array();	
	// check
	if($users){
		// loop
		foreach($users as $user) {
			// vlid
			$valid = false;
			// member object
			$member = mgm_get_member($user->ID);	
			
			if(array_key_exists($member->membership_type, $arr_memtype_count)) {				
				$arr_memtype_count[$member->membership_type]++;
				$valid = true;
			}
			// check other membership types
			if(!$valid)
				if(isset($member->other_membership_types) && is_array($member->other_membership_types) && count($member->other_membership_types) > 0) {
					// loop
					foreach ($member->other_membership_types as $key => $memtypes) {	
						if(array_key_exists($member->membership_type, $arr_memtype_count)) {				
							$arr_memtype_count[$member->membership_type]++;
							$valid = true;
						}
					}
			}
			unset($member);		
		}
		
		unset($users);
	}
	
	unset($membership_types);
	
	return $arr_memtype_count;
}

/*
Getting the membership type that have access to a post and 
then get the active users of a membership type issue #696
*/

function mgm_get_membershiptype_access_post($post_id = "") {

	$users = array();	
	
	$post_obj = mgm_get_post($post_id);
	
	$access_types = $post_obj->get_access_membership_types();

	$users = wp_cache_get('all_user_ids', 'users');		
	//if empty read from db:
	if(empty($users)) {
		$users = mgm_get_all_userids(array('ID'), 'get_results');
		//update cache with user ids:
		wp_cache_set('all_user_ids', $users, 'users');		
	}
	$members = array();	

	// check
	if($users){
		// loop
		foreach($users as $user) {
			// member object
			$member = mgm_get_member($user->ID);	
			if (in_array($member->membership_type, $access_types)) {
 				if($member->status == MGM_STATUS_ACTIVE){
					$members[]=$member;
				}
			}
		}
	}

	return $members;

}
/**
 *  Render vertical menu depending on user roles
 *  @return html
 */
function mgm_render_primary_menus() {
	// member menu
	$user_id = get_current_user_id();
	$obj_roles = new mgm_roles();
	$capabilities = $obj_roles->get_loggedinuser_custom_capabilities($user_id);	
	$system_obj = mgm_get_class('system');
	// common setting
	$dml = $system_obj->get_setting('enable_role_based_menu_loading');
	$is_admin = is_super_admin();
	$html = '';	
	$panels=array();
	$default_page_load = true;
	
	// authenticate
	if (mgm_get_class('auth')->verify()) {
		// Dashboard
		if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_home', $capabilities))){			
			if($default_page_load) {
				$default_page_load = false;								
				$html .= '<li aria-controls="admin_dashboard"><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin&method=dashboard_index" title="' . __('Dashboard', 'mgm') . '"><img src="' . MGM_ASSETS_URL .'images/icons/status_online.png" class="pngfix" alt="" />' . __('Dashboard','mgm') .'</a></li>';
			}else{
				$html .= '<li aria-controls="admin_dashboard"><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin&method=dashboard_index" title="' . __('Dashboard', 'mgm') . '"><img src="' . MGM_ASSETS_URL .'images/icons/status_online.png" class="pngfix" alt="" />' . __('Dashboard','mgm') .'</a></li>';
			}
			// set panel
			$panels[] = 'admin_dashboard';
		}
		// Members : capability: mgm_members
		if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_members', $capabilities))){
			if($default_page_load) {
				$default_page_load = false;	
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members" title="' . __('Members', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/user.png" class="pngfix" alt="" />' . __('Members','mgm'). '</a></li>';
			}else {
				$html .= '<li aria-controls="admin_members"><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members" title="' . __('Members', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/user.png" class="pngfix" alt="" />' . __('Members','mgm'). '</a></li>';			
			}
			// set panel
			$panels[] = 'admin_members';
		}	
		// mgm_content_control
		if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_content_control', $capabilities))){
			
			if($default_page_load) {
				$default_page_load = false;					
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/contents" title="' . __('Contents', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/page_white_key.png" class="pngfix" alt="" />' . __('Content Control','mgm') . '</a></li>';
			}else {
				$html .= '<li aria-controls="admin_contents"><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/contents" title="' . __('Contents', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/page_white_key.png" class="pngfix" alt="" />' . __('Content Control','mgm') . '</a></li>';			
			}
			// set panel
			$panels[] = 'admin_contents';
		}
			
		// mgm_ppp
		if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_ppp', $capabilities))){
			
			if($default_page_load) {
				$default_page_load = false;
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost" title="' . __('Pay Per Post', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/page_white_lightning.png" class="pngfix" alt="" />' . __('Pay Per Post','mgm') . '</a></li>';
			}else {				
				$html .= '<li aria-controls="admin_payperpost"><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost" title="' . __('Pay Per Post', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/page_white_lightning.png" class="pngfix" alt="" />' . __('Pay Per Post','mgm') . '</a></li>';
			}
			// set panel
			$panels[] = 'admin_payperpost';
		}							
		// mgm_payment_settings
		if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_payment_settings', $capabilities))){
			
			if($default_page_load) {
				$default_page_load = false;
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payments" title="' . __('Payments Settings', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/money.png" class="pngfix" alt="" />' . __('Payment Settings','mgm').'</a></li>';
			}else {				
				$html .= '<li aria-controls="admin_payments"><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payments" title="' . __('Payments Settings', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/money.png" class="pngfix" alt="" />' . __('Payment Settings','mgm').'</a></li>';				
			}
			// set panel
			$panels[] = 'admin_payments';
		}			
		// mgm_autoresponders
		if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_autoresponders', $capabilities))){							
			
			if($default_page_load) {
				$default_page_load = false;
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/autoresponders" title="' . __('Autoresponders Settings', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/email_go.png" class="pngfix" alt="" />' . __('Autoresponders','mgm') . '</a></li>';
			}else {				
				$html .= '<li aria-controls="admin_autoresponders"><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/autoresponders" title="' . __('Autoresponders Settings', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/email_go.png" class="pngfix" alt="" />' . __('Autoresponders','mgm') . '</a></li>';					
			}
			// set panel
			$panels[] = 'admin_autoresponders';
		}			
		// mgm_reports
		if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_reports', $capabilities))){		
			
			if($default_page_load) {
				$default_page_load = false;				
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports" title="' . __('Reports', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/chart_bar.png" class="pngfix" alt="" />' . __('Reports','mgm') . '</a></li>';
			}else {				
				$html .= '<li aria-controls="admin_reports"><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports" title="' . __('Reports', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/chart_bar.png" class="pngfix" alt="" />' . __('Reports','mgm') . '</a></li>';				
			}			
			// set panel
			$panels[] = 'admin_reports';
		}			
		// mgm_misc_settings
		if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_misc_settings', $capabilities))){
			
			if($default_page_load) {
				$default_page_load = false;				
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings" title="' . __('Misc. Settings', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/cog.png" class="pngfix" alt="" />' . __('Misc. Settings','mgm') . '</a></li>';
			}else {				
				$html .= '<li aria-controls="admin_settings"><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings" title="' . __('Misc. Settings', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/cog.png" class="pngfix" alt="" />' . __('Misc. Settings','mgm') . '</a></li>';				
			}
			// set panel
			$panels[] = 'admin_settings';
		}			
		// mgm_tools
		if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_tools', $capabilities))){			
			if($default_page_load) {
				$default_page_load = false;				
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools" title="' . __('Tools', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/wrench.png" class="pngfix" alt="" />' . __('Tools','mgm') . '</a></li>';
			}else {				
				$html .= '<li aria-controls="admin_tools"><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools" title="' . __('Tools', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/wrench.png" class="pngfix" alt="" />' . __('Tools','mgm') . '</a></li>';				
			}
			// set panel
			$panels[] = 'admin_tools';
		}		
		// mgm_support_docs	
		$html .= '<li aria-controls="admin_support_docs"><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/support_docs" title="' . __('Support Docs', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/report.png" class="pngfix" alt="" />' . __('Support Docs','mgm'). '</a></li>';
		// set panel
		$panels[] = 'admin_support_docs';
	}else {
		$html .= '<li aria-controls="admin_activation"><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin&method=activation_index" title="' . __('Activation', 'mgm') . '"><img src="' . MGM_ASSETS_URL . 'images/icons/status_online.png" class="pngfix" alt="" />' . __('Magic Members','mgm') . '</a></li>';
		// set panel
		$panels[] = 'admin_activation';
	}
	// print html
	echo $html;
	
	// return
	return $panels;
}
/**
 * Render secondary menu (horizontal tabs) depending on user roles
 * @param unknown_type $primary_menu
 * @return html
 */
function mgm_render_secondary_menus($primary_menu) {
	$user_id = get_current_user_id();
	$obj_roles = new mgm_roles();
	$capabilities = $obj_roles->get_loggedinuser_custom_capabilities($user_id);	
	$system_obj = mgm_get_class('system');
	// common setting
	$dml = $system_obj->get_setting('enable_role_based_menu_loading');	
	$is_admin = is_super_admin();	
	// html
	$html = '<ul class="tabs">';
	switch ($primary_menu) {
		// Members sub-tabs
		case 'mgm_members':
		case 'members':
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_member_list', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=members"><span class="pngfix">' . __('Members','mgm') . '</span></a></li>';
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_subscription_options', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=subscription_options"><span class="pngfix">' . __('Subscription Options','mgm') . '</span></a></li>';
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_coupons', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons"><span class="pngfix">' . __('Coupons','mgm') . '</span></a></li>';
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_addons', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons"><span class="pngfix">' . __('Addons','mgm') . '</span></a></li>';									
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_roles_capabilities', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=roles_capabilities"><span class="pngfix">' . __('Roles & Capabilities','mgm') . '</span></a></li>';
			break;
		// Content Control Sub-tabs 	
		case 'mgm_content_control':
		case 'content_control':
		case 'mgm_contents':
		case 'contents':
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_protection', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/contents&method=protections"><span class="pngfix">' . __('Protections','mgm') . '</span></a></li>';
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_downloads', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/downloads"><span class="pngfix">' . __('Downloads','mgm') . '</span></a></li>';
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_pages', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/contents&method=pages"><span class="pngfix">' . __('Pages','mgm') . '</span></a></li>';	
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_custom_fields', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/custom_fields"><span class="pngfix">' . __('Custom Fields','mgm') . '</span></a></li>';	
			break;	
		// Pay Per Post Sub-tabs 	
		case 'mgm_ppp':	
		case 'mgm_payperpost':	
		case 'payperpost':	
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_post_packs', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=postpacks"><span class="pngfix">' . __('Post Packs','mgm') . '</span></a></li>';
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_post_purchases', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=post_purchases"><span class="pngfix">' . __('Post Purchases','mgm') . '</span></a></li>';
			
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_addon_purchases', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=purchases"><span class="pngfix">' . __('Addon Purchases','mgm') . '</span></a></li>';
			
			break;
		// Reports Sub-tabs 	
		case 'mgm_reports':	
		case 'reports':	
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_sales', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=sales"><span class="pngfix">' . __('Sales','mgm') . '</span></a></li>';
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_earnings', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=earnings_index"><span class="pngfix">' . __('Earnings','mgm') . '</span></a></li>';
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_projection', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=projection"><span class="pngfix">' . __('Projection','mgm') . '</span></a></li>';
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_payment_history', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=payment_history"><span class="pngfix">' . __('Payment History','mgm') . '</span></a></li>';
			break;
		// Reports Sub-tabs	
		case 'mgm_misc_settings':
		case 'misc_settings':
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_general', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=general" title="' . __('General Settings','mgm') . '"><span class="pngfix">' . __('General','mgm') . '</span></a></li>';
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_post_settings', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=posts" title="' . __('Post/Page(s) Access & Protection','mgm') . '"><span class="pngfix">' . __('Post/Page(s)','mgm') . '</span></a></li>';		
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_message_settings', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=messages" title="' . __('Messages Settings','mgm') . '"><span class="pngfix">' . __('Messages','mgm') . '</span></a></li>';
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_email_settings', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=emails" title="' . __('Email Templates Settings','mgm') . '"><span class="pngfix">' . __('Emails','mgm') . '</span></a></li>';
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_autoresponder_settings', $capabilities)))
				$html .= '<!--<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=autoresponders" title="' . __('Auto Responders Settings','mgm') . '"><span class="pngfix">' . __('Auto Responders','mgm') . '</span></a></li>-->';
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_rest_API_settings', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi" title="' . __('Rest Api Settings','mgm') . '"><span class="pngfix">' . __('Rest API','mgm') . '</span></a></li>';
			break;	
		// Tools Sub-tabs	
		case 'mgm_tools':
		case 'tools':
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_data_migrate', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools&method=data_migrate" title="' . __('Data Migrate','mgm') . '"><span class="pngfix">' . __('Data Migrate','mgm') . '</span></a></li>';
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_core_setup', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools&method=core_setup" title="' . __('Core Setup','mgm') . '"><span class="pngfix">' . __('Core Setup','mgm') . '</span></a></li>';					
			
			//if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_upgrade', $capabilities)))
				//$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools&method=upgrade" title="' . __('Upgrade','mgm') . '"><span class="pngfix">' . __('Upgrade','mgm') . '</span></a></li>';					
			
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_system_reset', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools&method=system_reset" title="' . __('Reset Magic Members','mgm') . '"><span class="pngfix">' . __('System Reset','mgm') . '</span></a></li>';
			
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_logs', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools&method=logs" title="' . __('Logs','mgm') . '"><span class="pngfix">' . __('Logs','mgm') . '</span></a></li>';
			
			if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_dependency', $capabilities)))
				$html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools&method=dependencies" title="' . __('Dependency','mgm') . '"><span class="pngfix">' . __('Dependency','mgm') . '</span></a></li>';					
			
			// if (($is_admin && !bool_from_yn($dml)) || (bool_from_yn($dml) && in_array('mgm_system_health', $capabilities)))
				// $html .= '<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools&method=system_health" title="' . __('System Health','mgm') . '"><span class="pngfix">' . __('System Health','mgm') . '</span></a></li>';						
		break;			
	}
	// end
	$html .= '</ul>';
	// return	
	return $html;
}

/**
 * Check secondary menu is enabled for the logged in user
 * @param string $primary_menu
 * @param string $secondary_menu
 * @return boolean
 */
function mgm_is_mgm_menu_enabled($type = 'primary', $primary_menu, $secondary_menu = null) {
	$user_id = get_current_user_id();
	$obj_roles = new mgm_roles();
	// for the firsttime load
	if (!get_option('rolebasedmenu_init')) {		
		// update administrator role with capabilities		
		$custom_caps = $obj_roles->get_custom_capabilities();
		foreach ($custom_caps as $cap)
			$obj_roles->update_capability_role('administrator', $cap, true);
		// set init value
		update_option('rolebasedmenu_init', 1);	
	}
	
	$capabilities = $obj_roles->get_loggedinuser_custom_capabilities($user_id);	
	$system_obj = mgm_get_class('system');
	// common setting
	$dml = $system_obj->setting['enable_role_based_menu_loading'];	
	// if dynamic menu loading is disabled/priamry and secondary menus are enabled
	return ((is_super_admin() && !bool_from_yn($dml)) || // if admin user and rome based menu loading setting is disabled
		(bool_from_yn($dml) && 
		($type == 'primary' && in_array($primary_menu, $capabilities)) || // check primary menu capability is loaded
		($type == 'secondary' && in_array($primary_menu, $capabilities) && in_array($secondary_menu, $capabilities)) )); // check secondary menu capability is loaded
}

/**
 * get count by status
 */
function mgm_get_subscription_status_users_count($statuses){
	// init
	$_count = array();
	// loop
	// This will return status array with count in single call
	$_count = mgm_get_members_with('status', $statuses);
	/*
	foreach($statuses as $status){
		// with
		$u = mgm_get_members_with('status', constant($status));
		// count
		$_count[$status] = count($u);
	}
	*/
	// return 
	return $_count;
}
/**
 * get count by pack
 */
function mgm_get_subscription_package_users_count($packages){
	// init
	$_count = array();
	// loop
	foreach($packages as $package){
		// with
		$u = mgm_get_members_with('membership_type', $package['membership_type'], array('pack_id' => $package['id']));
		// count
		$_count[$package['id']] = count($u);
	}	
	// return 
	return $_count;
}
/**
 * find members with selected custom filed critera
 * @param string $field
 * @return mixed array|int  
 */
/*

//depricated due to performance rewritten 
function mgm_get_members_with_customfiled($field, $value ){
	
	global $wpdb;	
	// read from cache:
	$users = wp_cache_get('all_user_ids', 'users');		
	// if empty read from db:
	if(empty($users)) {
		// read again
		$users = mgm_get_all_userids(array('ID'), 'get_results');
		// update cache with user ids:
		wp_cache_set('all_user_ids', $users, 'users');		
	}	
	// init 
	$members = array();
	// check
	if($users){
		// loop
		foreach($users as $user){
			// vlid
			$valid = false;
			//name
			$name = '';
			// member object
			$member= mgm_get_member($user->ID);	
			// check		
			if(!empty($value) && isset($member->status) && $member->status =='Active') {
				// from custom field
				if(isset($member->custom_fields->$field) && !empty($member->custom_fields->$field)) {
					$name = $member->custom_fields->$field;							
				}
				// clean
				$value = mgm_stripslashes_deep($value);						
				// if there is a match
				if (!empty($name) && strpos(strtolower($name),strtolower($value)) !== false) {
				    $valid = true;
				}else {
					$valid = false;
				}
			}
			// store
			if($valid){
				$members[] = $user->ID;
			}
			// unset object
			unset($member);
		}
	}		
	// return
	return $members;
	
}*/
//user list custom filed sort
function mgm_userlist_customfield_sort($field, $order_type, $sql_filter,$show_level_members ){

	global $wpdb;	
	// get members		
	$sql = "SELECT SQL_CALC_FOUND_ROWS `ID` FROM `{$wpdb->users}` WHERE ID != 1 {$sql_filter}";		
	//users
	$users = $wpdb->get_results($sql);	
	// init 
	$members = array();
	//sorted members
	$sorted_members = array();
	// check
	if($users){
		// loop
		foreach($users as $user){			
			// member object
			$member= mgm_get_member($user->ID);	
			//mgm_pr($member);
			// from custom field
			if(isset($member->custom_fields->$field) && !empty($member->custom_fields->$field)) {
				$members[$user->ID] = strtolower($member->custom_fields->$field);							
			}else if(isset($user->$field) && !empty($user->$field)) {
				$members[$user->ID] = strtolower($user->$field);							
			}else {
				$members[$user->ID] = '';
			}
			unset($member);
		}
	}

	//mgm_pr($field);
	//mgm_pr($members);

	//sort the array
	if($order_type == 'desc')
		arsort($members);
	if($order_type == 'asc')
		asort($members);
	
	//geting sorted membres id
	if(!empty($show_level_members)){
		foreach ($members as $key => $value) {
			if(in_array($key,$show_level_members))
				$sorted_members[]=$key;
		}
	}else {
		foreach ($members as $key => $value) {
			$sorted_members[]=$key;
		}		
	}
	// return	
	return $sorted_members;
}

// getting member user ids based on user sql filter
function mgm_get_members_with_sql_filter($sql_filter =''){

	global $wpdb;	
	// init 
	$members = array();
	// get members		
	$sql = "SELECT `ID` FROM `{$wpdb->users}` WHERE ID != 1 {$sql_filter}";	
	// mgm_log($sql);
	// users
	$users = $wpdb->get_results($sql);	
	// check
	if($users){
		// loop
		foreach($users as $user){			
			// member object
			$members[]= $user->ID;	
		}	
	}
	
	//mgm_log(mgm_array_dump($members,true));
	// return
	return $members;

}

/**
 * find members with selected custom filed critera
 * @param string $field
 * @return mixed array|int  
 */
function mgm_get_members_with_customfiled($field, $value ){		
	global $wpdb;	
	$start = 0;
	$limit = 1000;	
	//user meta fields
	$fields= array('user_id','meta_value');	
	// sql
	$sql = "SELECT count(*) FROM `{$wpdb->usermeta}` WHERE `meta_key` = 'mgm_member_options' AND `user_id` <> 1";	
	$count  = $wpdb->get_var($sql);
	// init 
	$members = array();
	//count	
	if($count) {
		//for
		for( $i = $start; $i < $count; $i = $i + $limit ) {
			//users
			$users = mgm_patch_partial_user_member_options($i, $limit, $fields);
			// check
			if($users){
				// loop
				foreach($users as $user){
					// vlid
					$valid = false;
					//name
					$name = '';
					
					$user_id = $user->user_id;
					$member = unserialize($user->meta_value);
					// convert
					$member = mgm_convert_array_to_memberobj($member, $user_id);
		
					// check		
					if(!empty($value) && isset($member->status) && $member->status =='Active') {
						// from custom field
						if(isset($member->custom_fields->$field) && !empty($member->custom_fields->$field)) {
							$name = $member->custom_fields->$field;							
						}
						// clean
						$value = mgm_stripslashes_deep($value);						
						// if there is a match
						if (!empty($name) && strpos(strtolower($name),strtolower($value)) !== false) {
						    $valid = true;
						}else {
							$valid = false;
						}
					}
					// store
					if($valid){
						$members[] = $user_id;
					}
					// unset object
					unset($member);
					unset($user);
				}
			}			
		}
	}					
	// return
	return $members;	
}
/**
 * Fetch coupon used members with selected coupon id
 * @param  int $coupon_id
 * @param  array $return (users)
 * @return array 
 */
function mgm_coupon_used_users($coupon_id = NULL){
	
	global $wpdb;
	@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', '2048M' ) );
	$users = array();
	
	if(!is_null($coupon_id)){
		
		$start = 0;
		$limit = 1000;		
		
		$qry  = "SELECT count(DISTINCT {$wpdb->users}.ID) ";
		$qry .= "FROM {$wpdb->users} ";
		$qry .= "INNER JOIN {$wpdb->usermeta} ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id ) ";
		$qry .= "INNER JOIN {$wpdb->usermeta} AS mt1 ON ( {$wpdb->users}.ID = mt1.user_id ) ";
		$qry .= "INNER JOIN {$wpdb->usermeta} AS mt2 ON ( {$wpdb->users}.ID = mt2.user_id ) ";
		$qry .= "WHERE 1 = 1 AND (({$wpdb->usermeta}.meta_key =  '_mgm_user_register_coupon' AND CAST( {$wpdb->usermeta}.meta_value AS UNSIGNED ) =  '{$coupon_id}') ";
		$qry .= "OR (mt1.meta_key =  '_mgm_user_upgrade_coupon' AND CAST( mt1.meta_value AS UNSIGNED ) =  '{$coupon_id}') ";
		$qry .= "OR (mt2.meta_key =  '_mgm_user_extend_coupon' AND CAST( mt2.meta_value AS UNSIGNED ) =  '{$coupon_id}')) ";		
		
		$count  = $wpdb->get_var($qry);
		
		if($count) {			
			// read again
			for( $i = $start; $i < $count; $i = $i + $limit ) {						
				$users = array_merge($users, (array)mgm_patch_partial_coupon_used_users($i, $limit, $coupon_id));
				//a small delay of 0.01 second 
				usleep(10000);					
			}
		}
	}
	
	return $users;
}
/**
 * patch partial coupon used users
 */
function mgm_patch_partial_coupon_used_users($start, $limit,$coupon_id = NULL) {
	global $wpdb;

	$results = array();
	
	if(!is_null($coupon_id)){
		
		$qry  = "SELECT DISTINCT SQL_CALC_FOUND_ROWS {$wpdb->users}.ID, {$wpdb->users}.display_name, {$wpdb->users}.user_email, {$wpdb->users}.user_login, ";
		$qry .= "(SELECT t.meta_value FROM {$wpdb->usermeta} t WHERE t.user_id = {$wpdb->users}.ID AND t.meta_key =  'mgm_member_options') mgm_member_options ";
		$qry .= "FROM {$wpdb->users} ";
		$qry .= "INNER JOIN {$wpdb->usermeta} ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id ) ";
		$qry .= "INNER JOIN {$wpdb->usermeta} AS mt1 ON ( {$wpdb->users}.ID = mt1.user_id ) ";
		$qry .= "INNER JOIN {$wpdb->usermeta} AS mt2 ON ( {$wpdb->users}.ID = mt2.user_id ) ";
		$qry .= "WHERE 1 = 1 AND (({$wpdb->usermeta}.meta_key =  '_mgm_user_register_coupon' AND CAST( {$wpdb->usermeta}.meta_value AS UNSIGNED ) =  '{$coupon_id}') ";
		$qry .= "OR (mt1.meta_key =  '_mgm_user_upgrade_coupon' AND CAST( mt1.meta_value AS UNSIGNED ) =  '{$coupon_id}') ";
		$qry .= "OR (mt2.meta_key =  '_mgm_user_extend_coupon' AND CAST( mt2.meta_value AS UNSIGNED ) =  '{$coupon_id}')) ";
		$qry .= "ORDER BY user_login ASC LIMIT ". $start.",".$limit;
	
		$results =  $wpdb->get_results($qry);
		
		return $results;
	}
	return $results;
}
//member list sort for member object field.
function mgm_member_sort($field, $order_type, $sql_filter,$super_adminids = array()){
	global $wpdb;
	
	$super_admin_in = (count($super_adminids)==0) ? 0 : (implode(',', $super_adminids));	
	// get members		
	$sql = "SELECT SQL_CALC_FOUND_ROWS `ID` FROM `{$wpdb->users}` WHERE ID NOT IN ($super_admin_in) {$sql_filter}";		
	//users
	$users = $wpdb->get_results($sql);	
	// init 
	$members = array();
	//sorted members
	$sorted_members = array();
	// check
	if($users){
		// loop
		foreach($users as $user){			
			// member object
			$member= mgm_get_member($user->ID);	
			switch($field){
				case 'last_pay_date':
				case 'expire_date':						
					// from custom field
					if(isset($member->$field) && !empty($member->$field)) {
						$members[$user->ID] = date('Y-m-d', strtotime($member->$field));							
					}else {
						$members[$user->ID] = '';
					}
				break;				
			}
			unset($member);
		}
	}
	//sort the array
	if(strtolower($order_type) == 'desc')
		arsort($members);
	if(strtolower($order_type) == 'asc')
		asort($members);

	if(!empty($members)) {
		foreach ($members as $key => $value) {
			$sorted_members[]=$key;
		}
	}
	// return	
	return $sorted_members;	
}
//end file