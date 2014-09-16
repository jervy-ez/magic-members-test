<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members coupons handler utility class
 *
 * @package MagicMembers
 * @since 2.6.0
 */
class mgm_coupons{
	// construct
	public function __construct(){
		// php4 
		$this->mgm_coupons();
	}
	
	// php4 construct
	public function mgm_coupons(){
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
		if($affected = $wpdb->insert(TBL_MGM_COUPON, $data)){
			// get id
			if( $id = $wpdb->insert_id){				
				// return coupon
				return $coupon = $this->get($id);
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
		if($affected = $wpdb->update(TBL_MGM_COUPON, $data, array('id' => $id))){			
			// return coupon
			return $coupon = $this->get($id);						
		}
		// return
		return false;
	}
	
	// delete
	public function delete($id){
		global $wpdb;
		
		// delete		
		return $wpdb->query($wpdb->prepare('DELETE FROM `' . TBL_MGM_COUPON . '` WHERE id = %d', $id));
	}
	
	// delete all
	public function delete_all(){
		global $wpdb;
						
		// delete		
		return $wpdb->query('DELETE FROM `' . TBL_MGM_COUPON . '`	WHERE 1');
	}
	
	// get 
	public function get($id){
		global $wpdb;
		
		// get	
		if($coupon = $wpdb->get_row("SELECT * FROM `" . TBL_MGM_COUPON . "` WHERE id= '{$id}'")){					
			// create date 
			$coupon->create_dt  = date('Y-m-d', strtotime($coupon->create_dt));		
			// expire date 
			$coupon->expire_dt = is_null($coupon->expire_dt)? __('Never','mgm') : date('Y-m-d', strtotime($coupon->expire_dt));
			// use limit  
			$coupon->use_limit  = is_null($coupon->use_limit)? __('Unlimited','mgm') : (int)$coupon->use_limit ;
			// used 
			$coupon->used_count = (int)$coupon->used_count;	
			// product
			$coupon->product = json_decode($coupon->product);	
			// return 
			return $coupon;	
		}
		// error
		return false;	
	}
	
	// get all
	public function get_all(){
		global $wpdb;	
			
		// init
		$coupons = array();
		// get all	
		if($_coupons = $wpdb->get_results("SELECT * FROM `" . TBL_MGM_COUPON . "` WHERE 1")){
			// loop
			foreach($_coupons as $coupon){
				// create date 
				$coupon->create_dt  = date('Y-m-d', strtotime($coupon->create_dt ));		
				// expire date 
				$coupon->expire_dt = is_null($coupon->expire_dt)? __('Never','mgm') : date('Y-m-d', strtotime($coupon->expire_dt));
				// use limit  
				$coupon->use_limit  = is_null($coupon->use_limit )? __('Unlimited','mgm') : (int)$coupon->use_limit ;
				// used 
				$coupon->used_count = (int)$coupon->used_count;	
				// product
				$coupon->product = json_decode($coupon->product);	
				// set
				$coupons[] = $coupon;
			}	
		}			
		// return 
		return $coupons;	
	}
		
	// get count
	public function get_count(){
		global $wpdb;
		
		// return
		return $wpdb->get_var('SELECT COUNT(*) AS _C FROM ' . TBL_MGM_COUPON);
	}
	
	// get users
	public function get_users($id){
		global $wpdb;
		// init
		$users = array();
		
		// return
		return $users;
	}
	
}
// end of file core/libs/utilities/mgm_coupons.php