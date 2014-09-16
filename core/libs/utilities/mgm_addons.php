<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members addons handler utility class
 *
 * @package MagicMembers
 * @since 2.6.0
 */
class mgm_addons{
	// construct
	public function __construct(){
		// php4 
		$this->mgm_addons();
	}
	
	// php4 construct
	public function mgm_addons(){
		// do
	}	
	
	// add
	public function add($data){
		global $wpdb;
		// next id
		$next_id = ($this->get_count() + 1);	
		// name
		$name    = sprintf('New Coupon - %d', $next_id);
		// setup default data
		$default = array('name'=>$name, 'value'=>'50%', 'description'=>$name, 'create_dt'=>date('Y-m-d H:i:s'));
		// merge
		$data = array_merge($default, $data);		
		// expire date
		if(isset($data['expire_dt']) && !empty($data['expire_dt'])) {
			$data['expire_dt'] = date('Y-m-d', strtotime($data['expire_dt']));
		}
		// product
		if(isset($data['product']) && !empty($data['product'])) {
			$data['product'] = is_array($data['product'])? json_encode($data['product']) : $data['product']; 
		}				
		// insert		
		if($affected = $wpdb->insert(TBL_MGM_ADDON, $data)){
			// get id
			if( $id = $wpdb->insert_id){				
				// return addon
				return $addon = $this->get($id);
			}			
		}
		// return
		return false;
	}
	
	// update
	public function update($id, $data){
		global $wpdb;
		
		// expire date
		if(isset($data['expire_dt']) && !empty($data['expire_dt'])) {
			$data['expire_dt'] = date('Y-m-d', strtotime($data['expire_dt']));
		}
		// product
		if(isset($data['product']) && !empty($data['product'])) {
			$data['product'] = is_array($data['product'])? json_encode($data['product']) : $data['product']; 
		}				
		// update
		if($affected = $wpdb->update(TBL_MGM_ADDON, $data, array('id' => $id))){			
			// return addon
			return $addon = $this->get($id);						
		}
		// return
		return false;
	}
	
	// delete
	public function delete($id){
		global $wpdb;
		
		// delete		
		return $wpdb->query($wpdb->prepare('DELETE FROM `' . TBL_MGM_ADDON . '`	WHERE id = %d', $id));
	}
	
	// delete all
	public function delete_all(){
		global $wpdb;
						
		// delete		
		return $wpdb->query('DELETE FROM `' . TBL_MGM_ADDON . '`	WHERE 1');
	}
	
	// get 
	public function get($id){
		global $wpdb;
		
		// get	
		if($addon = $wpdb->get_row("SELECT * FROM `" . TBL_MGM_ADDON . "` WHERE id= '{$id}'")){					
			// create date 
			$addon->create_dt  = date('Y-m-d', strtotime($addon->create_dt));		
			// expire date 
			$addon->expire_dt = is_null($addon->expire_dt)? __('Never','mgm') : date('Y-m-d', strtotime($addon->expire_dt));
			// use limit  
			$addon->use_limit  = is_null($addon->use_limit)? __('Unlimited','mgm') : (int)$addon->use_limit ;
			// used 
			$addon->used_count = (int)$addon->used_count;	
			// product
			$addon->product = json_decode($addon->product);	
			// return 
			return $addon;	
		}
		// error
		return false;	
	}
	
	// get all
	public function get_all(){
		global $wpdb;	
			
		// init
		$addons = array();
		// get all	
		if($_addons = $wpdb->get_results("SELECT * FROM `" . TBL_MGM_ADDON . "` WHERE 1 AND (`expire_dt` > NOW() OR `expire_dt` IS NULL)")){
			// mgm_log( $wpdb->last_query, __FUNCTION__);

			// loop
			foreach($_addons as $addon){
				// create date 
				$addon->create_dt  = date('Y-m-d', strtotime($addon->create_dt));		
				// expire date 
				$addon->expire_dt = is_null($addon->expire_dt)? __('Never','mgm') : date('Y-m-d', strtotime($addon->expire_dt));				
				// set
				$addons[] = $addon;
			}	
		}			
		// return 
		return $addons;	
	}
		
	// get count
	public function get_count(){
		global $wpdb;
		
		// return
		return $wpdb->get_var('SELECT COUNT(*) AS _C FROM ' . TBL_MGM_ADDON);
	}
	
	// get options
	public function get_options($addon_id=0){
		global $wpdb;
		// init
		$options = array();
		
		// check
		if((int)$addon_id>0){
			// row
			$options_results = $wpdb->get_results($wpdb->prepare("SELECT `id`,`option`,`price` FROM `".TBL_MGM_ADDON_OPTION."` WHERE `addon_id`=%d", $id));		
			// reset data
			if($options_results){
				foreach($options_results as $option){
					$options[$option->id] = array('option'=>$option->option,'price'=>$option->price);
				}				
			}
		}
		// error
		return $options;
	}
	
	// get options combine
	public function get_options_combine($addon_ids=array()){
		global $wpdb;
		// init
		$options_list = array();
		// init
		$addon_ids_sql = '';
		// check
		if(!empty($addon_ids)){
			// addon_ids			
			$addon_ids_sql = ' AND `addon_id` IN (' . mgm_map_for_in($addon_ids). ')';
		}		
		// sql
		$sql = "SELECT A.id,`name`, `description`, B.`id` AS option_id,`option`,`price` 
				FROM `" . TBL_MGM_ADDON . "` A JOIN `".TBL_MGM_ADDON_OPTION."` B ON(A.id=B.addon_id) 
				WHERE 1 {$addon_ids_sql} ORDER BY `price` DESC";
		// row
		$options_results = $wpdb->get_results($sql);	
		// reset data
		if($options_results){
			foreach($options_results as $option){
				$options_list[] = array('id'=>$option->id,'name'=>$option->name,'description'=>$option->description,
										'option_id'=>$option->option_id,'option'=>$option->option,
										'price'=>$option->price);
			}				
		}
		
		// error
		return $options_list;
	}
	
	// get options only
	public function get_options_only($addon_option_ids=array()){
		global $wpdb;
		// init
		$options_list = array();
		// check
		if(!empty($addon_option_ids)){
			// addon_option_ids
			$addon_option_ids_in = mgm_map_for_in($addon_option_ids);
			// sql
			$sql = "SELECT `id`,`option`,`price` FROM `".TBL_MGM_ADDON_OPTION."` 
				    WHERE `id` IN ({$addon_option_ids_in}) ORDER BY `price` DESC";
			// row
			$options_results = $wpdb->get_results($sql);	
			// reset data
			if($options_results){
				foreach($options_results as $option){
					$options_list[$option->id] = array('option'=>$option->option,'price'=>$option->price);
				}				
			}
		}
		// error
		return $options_list;
	}
	
}
// end of file core/libs/utilities/mgm_addons.php