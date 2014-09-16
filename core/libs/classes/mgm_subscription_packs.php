<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members subscription packages class
 * extends object to save options to database
 *
 * @package MagicMembers
 * @since 2.5
 */ 
class mgm_subscription_packs extends mgm_object{
	// packs
	var $packs;
	// duration str, not to save
	var $duration_str = array();
	// duration str plural, not to save
	var $duration_str_plu = array();	
	// next id
	var $next_id = 4;
	
	// construct
	public function __construct($packs=false){
		// php4
		$this->mgm_subscription_packs($packs);
	}
	
	// construct php4
	public function mgm_subscription_packs($packs=false){
		// parent
		parent::__construct(); 
		// defaults
		$this->_set_defaults($packs);
		// read vars from db
		$this->read();// read and sync	
		// delete empty type
		$this->delete_empty_type();		
	}
	
	/** 
	 * add pack
	 *
	 * @param string $membership_type
	 * @param array $pack
	 * @return array $pack 	 
	 */
	public function add($membership_type, $pack = array()){		
		// define empty
		$pack = $this->get_pack_array($membership_type, $pack);
		// set		
		$this->set_pack($pack);		
		// update next id
		$this->next_id++;		
		// save to database
		$this->save();
		// return last value
		return array((count($this->packs)) => (end($this->packs)));// possible id clash ? or just for list?
	}
	
	/** 
	 * add packs
	 *
	 * @param array $packs
	 * @return array $pack 	
	 */
	public function add_multiple($packs = array()){
				
	}
	
	/** 
	 * update pack
	 *
	 * @param int $id
	 * @param array $pack
	 * @return bool $success 
	 */
	public function update($id, $pack=array()){
		// check
		if($this->packs){ 
			// init
			$_packs = array();
			// loop
			foreach ($this->packs as $_pack) {
				// match
				if($_pack['id'] == $id){
					$_packs[] = array_merge($_pack, $pack);
				}else{
					$_packs[] = $_pack;
				}
			}
			// set 
			$this->add_multiple($_packs);
			// save
			$this->save();
			// return 
			return true;
		}
		// error
		return false;
	}
	
	/** 
	 * delete pack
	 *
	 * @param int $id
	 * @return bool $success 
	 */
	public function delete($id){
		// check
		if($this->packs){ 
			// init
			$_packs = array();
			// loop
			foreach ($this->packs as $_pack) {
				// match
				if($_pack['id'] != $id){					
					$_packs[] = $_pack;
				}
			}
			// set 
			$this->add_multiple($_packs);
			// save
			$this->save();
			// return 
			return true;
		}
		// error
		return false;
	}
	
	/** 
	 * delete all packs
	 *
	 * @param none
	 * @return bool $success 
	 */
	public function delete_all(){
		// check
		if($this->packs){ 
			// unset
			$this->packs = array();
			// id
			$this->next_id = 1;
			// save
			$this->save();
			// return 
			return true;
		}
		// error
		return false;
	}
	
	/** 
	 * delete all packs without any membership type
	 *
	 * @param none
	 * @return bool $success 
	 */
	public function delete_empty_type(){
		// check
		if($this->packs){ 
			// check for bad packs
			foreach ($this->packs as $_pack) {
				// delete pack if no type associated
				if(empty($_pack['membership_type'])){
					$this->delete($_pack['id']); 
				}
			}			
			// return 
			return true;
		}
		// error
		return false;
	}
	
	/** 
	 * get pack
	 *
	 * @param int $id
	 * @param string $page
	 * @return array $pack 
	 */
	public function get($id, $page=NULL){
		// default
		$pack = false;// does it check bool?
		// loop
		foreach ($this->packs as $_pack) {
			// check	
			if(!is_null($page) && $_pack['active'][$page] === 0 ) continue;
					
			// match		
			if ($_pack['id'] == $id ) {
				$pack = mgm_stripslashes_deep($_pack); break;
			}
		}
		// return
		return $pack;
	}
	
	/** 
	 * get all packs
	 *	
	 * @param string $page
	 * @param bool $sort
	 * @param array $package
	 * @return array $packs
	 */
	public function get_all($page='all', $sort=true, $package=array()){
		// init
		$packs = $pack_orders = array();		
		// loop and order		
		foreach ($this->packs as $_pack) {			
			// check pack is active			
			if($page != 'all') { 		
				// not active		
				/*
				if(!$_pack['active'][$page]) {
					// check if hidden 
					if($page == 'register'){
						// skip when hidden		
						if((bool)$_pack['hidden'] == TRUE){
							if(!isset($package['id'])) continue;
						}
					}else{ // else as usual skip
						continue;
					}	
				}
				*/
				// Issue #1248 
				if(isset($_pack['active'][$page]) && !$_pack['active'][$page]) {
					//Skip if not active
					continue;	
				}else {
					//Active
					// check if hidden if register page 
					if($page == 'register'){
						// skip when hidden	- issue #1398		
						if(isset($_pack['hidden']) && (bool)$_pack['hidden'] == TRUE && empty($package)){
							if(!isset($package['id'])) continue;
						}
					}
				}
			}
			// set order for later sort
			$pack_orders[] = $_pack['sort'];
		}	
		
		// sort packs
		if(count($pack_orders)>0){
			// sort
			sort($pack_orders);			
			// sorted
			$pack_sorted = array();
			// loop by order
			foreach($pack_orders as $pack_order){
				// loop packs
				foreach ($this->packs as $_pack) {
					// order match
					if($_pack['sort'] == $pack_order){
						// duplicate check
						if(!in_array($_pack['id'], $pack_sorted)){// #184 duplicate bug
							// set pack
							$packs[] = mgm_stripslashes_deep($_pack);							
							// mark as sorted
							$pack_sorted[] = $_pack['id'];
						}
					}
				}
			}
		}		
		// return 
		return $packs;
	}
	
	/** 
	 * get pack on page action options
	 *	
	 * @param none
	 * @return array $options
	 */
	function get_active_options() {
		// return
		return array('register' => 1, 'upgrade' => 1, 'extend' => 1 );
	}

	/** 
	 * get pack on page action options
	 *	
	 * @param none
	 * @return array $options
	 */
	function get_hide_options() {
		// return
		return array('general_register' => 1, 'single_register' => 1 );
	}
	
	/** 
	 * get pack description
	 *	
	 * @param array $pack
	 * @return string $desc
	 */
	function get_pack_desc($pack){		
		// system
		$system_obj = mgm_get_class('system');
		// format
		$date_fmt = mgm_get_date_format('date_format_short');
		// tpl data
		$tpl_data = array();
		// tpl vars
		$tpl_vars = array('membership_type', 'cost', 'currency', 'duration', 'duration_period', 'num_cycles', 
						  'trial_cost', 'trial_duration', 'trial_duration_period', 'description','currency_sign','pack_start_date','pack_end_date');
		// get template		
		if( ! $pack_desc_template = $system_obj->get_template('pack_desc_template', array(), true) ){
			$pack_desc_template = sprintf('[membership_type] - [cost] [currency] %s [duration] [duration_period] [num_cycles].<br/> 
										   [if_trial_on] %s [trial_cost] [currency] %s [trial_duration] [trial_duration_period] [/if_trial_on]', 
			                              __('per', 'mgm'), __('This pack includes a special, limited trial-offer:', 'mgm'), __('for', 'mgm'));
		}
		
		// lifetime template:
		if( $pack['duration_type'] == 'l' ){			
			// template	-issue #988
			if( ! $pack_desc_template = $system_obj->get_template('pack_desc_lifetime_template', array(), true)){
				$pack_desc_template = sprintf('[membership_type] - [cost] [currency] %s', __('for Lifetime','mgm'));
			}
		}elseif( $pack['duration_type'] == 'dr' ){			
			$date_range = '';
			// check
			if (mgm_is_valid_date($pack['duration_range_start_dt'], '-') && mgm_is_valid_date($pack['duration_range_end_dt'], '-')) {
				$tpl_data['pack_start_date']	=  	date($date_fmt, strtotime($pack['duration_range_start_dt']));
				$tpl_data['pack_end_date'] 		= 	date($date_fmt, strtotime($pack['duration_range_end_dt']));
			}			
			if( ! $pack_desc_template = $system_obj->get_template('pack_desc_date_range_template', array(), true)){
				$pack_desc_template = sprintf('[membership_type] - [cost] [currency] starts from [pack_start_date]  to [pack_end_date]');
			}
		}
		
		// currency - issue #1602
		if(!isset($pack['currency']) || empty($pack['currency'])){					
			$tpl_data['currency'] = $system_obj->get_setting('currency');
		}else{
			$tpl_data['currency'] = $pack['currency'];
		}		

		// issue #1177
		$tpl_data['currency_sign'] = mgm_get_currency_symbols( $tpl_data['currency'] );		
		// type
		$tpl_data['membership_type'] = mgm_stripslashes_deep(mgm_get_class('membership_types')->get_type_name($pack['membership_type']));
		$tpl_data['duration_period'] = strtolower($this->get_pack_duration($pack));
		
		// transalation issue #950
		$tpl_data['text_for'] = __('for','mgm');
		
		$tpl_data['num_cycles'] = ($pack['num_cycles'] == 0) ? __(' - Ongoing', 'mgm') : (sprintf(' - %s %d %s', $text_for, (int)$pack['num_cycles'], (($pack['num_cycles'] == 1 )? __('time','mgm') : __('times','mgm')))); 				
		$tpl_data['trial_duration_period'] = strtolower($this->get_pack_duration($pack, true));		
		
		// merge rest, overwrite tpl_data
		if(is_array($pack))	$tpl_data = array_merge($pack, $tpl_data);
		// remove next lines as preg_replace will fail
		$pack_desc_template = str_replace(array("\r\n", "\n", "\r"), '', $pack_desc_template);     
		// copy template 
		$pack_desc = $pack_desc_template;
		// replace 0 cost
		if( isset($tpl_data['cost']) && $tpl_data['cost'] == 0.00){
			$pack_desc = str_replace('[cost] [currency]', __('free', 'mgm'), $pack_desc);
		}
		// replace 0 trial_cost
		if( isset($tpl_data['trial_cost']) && $tpl_data['trial_cost'] == 0.00){
			$pack_desc = str_replace('[trial_cost] [currency]', __('free', 'mgm'), $pack_desc);
		}
		// replace
		foreach($tpl_vars as $var){
			if( isset( $tpl_data[$var] ) ){				
				$pack_desc = str_replace('['.$var.']', $tpl_data[$var], $pack_desc);
			}
		}
		// num cycles
		if ($pack['num_cycles']) {
			$pack_desc = preg_replace("'\[/?\s?if_num_cycles\s?\]'i", '', $pack_desc);
		} else {			
			$pack_desc = preg_replace("'\[if_num_cycles\s?\](.*)\[/if_num_cycles\s?\]'i", '', $pack_desc);
		}		
		// trial on		
		if (isset($pack['trial_on']) && (int)$pack['trial_on'] == 1) {			
			$pack_desc = preg_replace("'\[/?\s?if_trial_on\s?\]'i", '', $pack_desc);
		} else {			
			$pack_desc = preg_replace("'\[if_trial_on\s?\](.*)\[/if_trial_on\s?\]'i", '', $pack_desc);			
		}		
		// send 
		return $pack_desc;
	}
	
	/** 
	 * get pack duration
	 *	
	 * @param array $pack
	 * @param bool $trial
	 * @return string $duration
	 */
	function get_pack_duration($pack, $trial=false){
		// trial
		if( $trial ){
			// check
			if(!isset($pack['trial_duration'])) $pack['trial_duration'] = 0;
			if(!isset($pack['trial_duration_type'])) $pack['trial_duration_type'] = 'd';			
			// set
			$duration = ( $pack['trial_duration'] > 1) ? ($this->duration_str_plu[strtolower($pack['trial_duration_type'])]) : $this->duration_str[strtolower($pack['trial_duration_type'])];
		}else{
			$duration = ( $pack['duration'] > 1 ) ? ($this->duration_str_plu[strtolower($pack['duration_type'])]) : $this->duration_str[strtolower($pack['duration_type'])];
		}
		// return lower
		return strtolower($duration);			
	}
	
	/** 
	 * get pack duration date expression
	 *	
	 * @param char $duration_type
	 * @return string $duration_expr
	 */
	function get_pack_duration_expr($duration_type){
	// duration
		return (isset($this->duration_str[$duration_type])) ? $this->duration_str[$duration_type] : __('Undefined', 'mgm');
	}	
	
	/** 
	 * get duration types
	 *	
	 * @param char $duration_type
	 * @return string $duration_expr
	 */	
	function get_duration_types($return='all'){
		// date expr
		if($return == 'date_expr'){
			return array_slice($this->duration_str,0,4);// d,w,m,y
		}
		
		// non date expr
		if($return == 'non_date_expr'){
			return array_slice($this->duration_str,4,2);//l,dr
		}
		
		// all
		return $this->duration_str;
	}
	
	/** 
	 * get duration date exprs
	 *	
	 * @param char $duration_type
	 * @return string $duration_expr
	 */	
	function get_duration_exprs($duration_type=NULL){
	// return
		if(!is_null($duration_type)) 
			return isset($this->duration_expr[$duration_type]) ? $this->duration_expr[$duration_type] : false;
			
		// all
		return $this->duration_expr;
	}
	
	/** 
	 * get pack duration date cycle expression
	 *	
	 * @param init $pack_id
	 * @return string $date_cycle_expr
	 */
	function get_pack_date_cycle($pack_id){
		// durations
		$durations = $this->get_duration_exprs();
		// get member subscribed  pack
		if($pack = $this->get_pack($pack_id)){
			// check
			if(isset($pack['duration']) && isset($pack['duration_type']) && isset($durations[$pack['duration_type']]) && (int)$pack['duration'] > 0){
			// return
				return sprintf('+ %d %s', (int)$pack['duration'], $durations[$pack['duration_type']]);	
			}
		}
		// error
		return false;			
	}		
	
	/** 
	 * set pack
	 *
	 * @param array $pack
	 * @return none
	 */
	function set($pack){
		// set
		if($pack) array_push($this->packs, $pack);	
	}
	
	/** 
	 * set packs
	 *
	 * @param array $packs
	 * @param bool $merge
	 * @return none
	 */
	function set_all($packs, $merge=false){
		// set		
		if($packs) $this->packs = ($merge) ? array_merge($this->packs, $packs) : $packs;
	}
	
	/** 
	 * validate pack
	 *
	 * @param double $cost
	 * @param string $duration_type
	 * @param string $membership_type
	 * @param int $pack_id
	 * @return array $pack 
	 */
	function validate($cost, $duration, $duration_type, $membership_type, $pack_id=NULL){
		// init
		$_pack = false;
		// loop
		foreach ($this->packs as $pack) {			
			// with pack id
			if($pack_id){			
				if ($pack['id'] == $pack_id && $pack['cost'] == $cost && $pack['duration'] == $duration && $pack['duration_type'] == $duration_type && $pack['membership_type'] == $membership_type) {
					$_pack = $pack;
					break;
				}	
			}else{
			// without pack id
				if ($pack['cost'] == $cost && $pack['duration'] == $duration && $pack['duration_type'] == $duration_type && $pack['membership_type'] == $membership_type) {
					$_pack = $pack;
					break;
				}	
			}
		}
		// return
		return $_pack;	 
	}
		
	// deprecated methods ----------------------------------------------	
	
	/**
	 * set pack
	 *
	 * @deprecated 
	 * @see set() 
	 */
	function set_pack($pack) {				
		// see set()
		return $this->set($pack);		
	}
	
	/**
	 * set packs
	 *
	 * @deprecated 
	 * @see set_all() 
	 */
	function set_packs($packs, $merge=false) {
		// see set_all()
		return $this->set_all($packs, $merge);	
	}
	
	/**
	 * add new pack
	 *
	 * @deprecated 
	 * @see add() 
	 */	
	function add_pack($membership_type, $pack=array()){
		// see add()
		return $this->add($membership_type, $pack);
	}
		
	/**
	 * pack by id
	 * @deprecated use get()
	 */	
	function get_pack($id, $page = null){
		// return
		return $this->get($id, $page);
	}
	
	/**
	 * get packs on page
	 *
	 * @param string display page, possible values register|upgrade|extend
	 * @param boolean sort
	 * @param array selected pack
	 * @return array sorted and mathed packs
	 * @deprecated use get_all()
	 */	
	function get_packs($page='all', $sort=true, $package=array()){		
		// use 
		return $this->get_all($page, $sort, $package);
	}
	
	/**
	 * validate pack
	 *
	 * @deprecated 
	 * @see validate() 
	 */
	function validate_pack($cost, $duration, $duration_type, $membership_type, $pack_id=NULL){
		// see validate()
		return $this->validate($cost, $duration, $duration_type, $membership_type, $pack_id);
	}	
	
	// base packs
	function base_packs() {
		// base packs
		// $base_pack1 = $this->get_pack_array('trial', array('id'=>1));
		// $base_pack2 = $this->get_pack_array('free', array('id'=>2));
		// $base_pack3 = $this->get_pack_array('member', array('id'=>3,'cost'=> '5.00'));
		
		// options
		return 
			array(
				array(
					'id'                  => 1,
					'trial_on'            => 0,
					'trial_cost'          => '0.00',
					'trial_duration'      => 0,
					'trial_duration_type' => 'd',
					'cost'                => '0.00',
					'currency'            => 'USD',
					'duration'            => 3,
					'duration_type'       => 'd',
					'country'             => '',
					'num_cycles'          => '',
					'role'                => 'subscriber',					
					'product'             => '',
					'membership_type'     => 'trial',
					'description'         => 'Trial Account',
					'hide_old_content'    => 0,
					'default'             => 0,
					'default_assign'      => 0, // used to assign pack to admin created user
					'active'              => $this->get_active_options(),
					'hidden'              => 0,
					'sort'                => 1,
					'modules'             => array('mgm_trial'),
					'allow_renewal'       => 0,
					'move_members_pack'	  => '',
					'preference'	      => 1
				),
				array(
					'id'                  => 2,
					'trial_on'            => 0,
					'trial_cost'          => '0.00',
					'trial_duration'      => 0,
					'trial_duration_type' => 'd',
					'cost'                => '0.00',
					'currency'            => 'USD',					
					'duration'            => 1,
					'duration_type'       => 'y',
					'country'             => '',
					'num_cycles'          => '',
					'role'                => 'subscriber',					
					'product'             => '',
					'membership_type'     => 'free',
					'description'         => 'Free Account',
					'hide_old_content'    => 0,
					'default'             => 0,
					'default_assign'      => 0, // used to assign pack to admin created user
					'active'              => $this->get_active_options(),
					'hidden'              => 0,
					'sort'                => 2,
					'modules'             => array('mgm_free'),
					'allow_renewal'       => 0,
					'move_members_pack'	  => '',
					'preference'	      => 2	
				),
				array(
					'id'                  => 3,
					'trial_on'            => 0,
					'trial_cost'          => '0.00',
					'trial_duration'      => 0,
					'trial_duration_type' => 'd',
					'cost'                => '5.00',
					'currency'            => 'USD',					
					'duration'            => 3,
					'duration_type'       => 'm',
					'country'             => '',
					'num_cycles'          => '',	
					'role'                => 'subscriber',	
					'product'             => '',
					'membership_type'     => 'member',
					'description'         => 'Paid Member Account',
					'hide_old_content'    => 0,
					'default'             => 1,
					'default_assign'      => 1, // used to assign pack to admin created user
					'active'              => $this->get_active_options(),
					'hidden'              => 0,
					'sort'                => 3,
					'modules'             => array('mgm_free','mgm_paypal'),
					'allow_renewal'       => 1,
					'move_members_pack'	  => '',
					'preference'	      => 3
				)
			);	
	}
	
	// get arra
	function get_pack_array($membership_type, $pack=array()){
		// des
		$description = sprintf('%s subscription', ucwords(str_replace('_', ' ', $membership_type)));
		// pack merge
		$pack = array_merge($pack, array('membership_type'=>$membership_type, 'description' => $description));
		// init
		$pack = array_merge(
						array(
							'id'                  => $this->next_id,
							'trial_on'            => 0,
							'trial_cost'          => '0.00',
							'trial_duration'      => 0,
							'trial_duration_type' => 'd',
							'trial_num_cycles'    => '0',
							'cost'                => '0.00',
							'currency'            => 'USD',							
							'duration'            => 3,
							'duration_type'       => 'd',
							'country'             => '',
							'num_cycles'          => '', // 0 = 'Ongoing', 1-99 for recurrence
							'role'                => 'subscriber',							
							'product'             => '',
							'membership_type'     => $membership_type,
							'description'         => ucwords(str_replace('_', ' ', $membership_type . ' subscription')),
							'hide_old_content'    => 0,
							'default'             => 0,
							'active'              => $this->get_active_options(),
							'hidden'              => 0,
							'sort'                => $this->next_id,
							'modules'             => array('mgm_free','mgm_paypal'),
							'allow_renewal'       => 1,
							'move_members_pack'	  => '',
							'preference'	      => $this->get_next_preference(),
							'multiple_logins_limit' => 1	
					), $pack);
		
		// return 
		return $pack;			
		
	}
	
	// get_next_preference
	function get_next_preference(){
		// next_preference
		$next_preference = 1;
		// loop
		foreach ($this->packs as $pack) {
			// greater
			if((int)$pack['preference'] > $next_preference){
				$next_preference = (int)$pack['preference'];
			}	
		}
		// increase
		return $next_preference+1;
	}
	
	// defaults
	function _set_defaults($packs=false){
		// code
		$this->code = __CLASS__;
		// name
		$this->name = 'Subscription Packs Lib';
		// description
		$this->description = 'Subscription Packs Lib';		
		// set from argument
		if(!is_array($packs)) $packs = $this->base_packs();					
		// set
		$this->set_all($packs);			
		// duration
		$this->duration_str = array('d' => __('Day', 'mgm'), 'w'=> __('Week','mgm'), 'm'=> __('Month', 'mgm'), 
									'y'=> __('Year', 'mgm'), 'l' => __('Lifetime', 'mgm'), 'dr' => __('Date Range', 'mgm'));
		// duration plural							
		$this->duration_str_plu = array('d' => __('Days', 'mgm'), 'w'=> __('Weeks','mgm'), 'm'=> __('Months', 'mgm'),
		                                'y'=> __('Years', 'mgm'), 'l' => __('Lifetime', 'mgm'), 'dr' => __('Date Range', 'mgm'));	
										
		// duration exprs for date calculation in php
		$this->duration_expr = array('d'=>'DAY', 'w'=>'WEEK', 'm'=>'MONTH', 'y'=>'YEAR');													
	}
	
	// fix
	function apply_fix($old_obj){
		// to be copied vars
		$vars = array('packs','next_id');
		// set
		foreach($vars as $var){
			// var
			$this->{$var} = (isset( $old_obj->{$var} ) ) ? $old_obj->{$var} : '';
		}				
		// save
		$this->save();	
	}
	
	// prepare save, define the object vars to be saved
	// internally called by object->save()
	function _prepare(){	
		// init array
		$this->options = array();	
		// to be saved vars
		$vars = array('packs','next_id');
		// set
		foreach($vars as $var){
			// var
			$this->options[$var] = $this->{$var};
		}	
	}
	/**
	 * Overridden function:	
	 *  See the comment below:
	 *
	 * @param string $option_name
	 * @param array $current_value current value for class var(can be default)
	 * @param array $option_value: updated value
	 */
	function _option_merge_callback($option_name, $current_value, $option_value) {		
		// This is to make sure that the default membership_type array doesn;t contain the hardcoded option 
		// Eg:'member' incase user deletes it and option array doesn't have it.
		// copy from option
		// issue#: 521
		if($option_name == 'packs') {			
			$current_value = array();
		}		
		// update class var
		$this->{$option_name} = mgm_array_merge_recursive_unique($current_value,$option_value);
	}
}
// core/libs/classes/mgm_subscription_packs.php