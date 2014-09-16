<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members membership types
 * extends object to save options to database
 *
 * @package MagicMembers
 * @since 2.5
 */ 
class mgm_membership_types extends mgm_object{
	// membership types
	var $membership_types  = array();
	var $login_redirects   = array();
	var $logout_redirects  = array();
	var $capability_orders = array(); // TODO order	
	
	// construct
	public function __construct($membership_types=false){
		// php4
		$this->mgm_membership_types($membership_types);
	}
	
	// construct
	public function mgm_membership_types($membership_types=false){
		// parent
		parent::__construct(); 
		// defaults
		$this->_set_defaults($membership_types);
		// read vars from db
		$this->read();// read and sync			
	}
	
	// add
	public function add($name, $login_redirect='', $logout_redirect=''){
		// name
		if(isset($name) && !empty($name)){
			// set name
			if($code = $this->set_name($name)){
				// set login_redirect
				if(isset($login_redirect)){
					$this->set_login_redirect($code, $login_redirect);
				}
				// set logout_redirect
				if(isset($logout_redirect)){
					$this->set_logout_redirect($code, $logout_redirect);
				}								
				// save
				$this->save();
				// return code on success
				return $code;
			}
		}	
		// return error
		return false;	
	}
	
	// update
	public function update($code, $name, $login_redirect='', $logout_redirect=''){
		// remove
		if(array_search($code, array_keys($this->membership_types)) !== false){
			// unset
			$this->membership_types[$code] = $this->_trim($name);
			// set login_redirect
			if(isset($login_redirect)){
				$this->set_login_redirect($code, $login_redirect);
			}
			// set logout_redirect
			if(isset($logout_redirect)){
				$this->set_logout_redirect($code, $logout_redirect);
			}								
			// save
			$this->save();
			// return success
			return true;
		}
		// error
		return false;
	}
	
	// delete
	public function delete($code){
		// remove
		if(array_search($code, array_keys($this->membership_types)) !== false){
			// unset
			unset($this->membership_types[$code],$this->login_redirects[$code],$this->logout_redirects[$code]);
			// save
			$this->save();
			// treat success
			return true;
		}
		// error
		return false;
	}	
	
	// delete all
	public function delete_all(){
		// remove all
		unset($this->membership_types,$this->login_redirects,$this->logout_redirects);
		// save
		$this->save();
		// treat success
		return true;		
	}
	
	// get
	public function get($code){
		// init
		$membership_type = array();		
		// check
		if($this->membership_types){
			// loop
			foreach($this->membership_types as $t_code => $t_name){
				// check
				if($code == $t_code){
					// redirects
					$login_redirect = $this->get_login_redirect($t_code);
					$logout_redirect = $this->get_logout_redirect($t_code);
					// set
					$membership_type = array('code' => $t_code, 'name' => $t_name, 'login_redirect'=>$login_redirect , 'logout_redirect'=>$logout_redirect);
					// break
					break;
				}
			}
		}
		// return 
		return $membership_type;
	}
	
	// get all
	public function get_all(){
		// init
		$membership_types = array();		
		// check
		if($this->membership_types){
		// loop
			foreach($this->membership_types as $t_code => $t_name){
				// redirects
				$login_redirect = $this->get_login_redirect($t_code);
				$logout_redirect = $this->get_logout_redirect($t_code);
				// set
				$membership_types[] = array('code' => $t_code, 'name' => $t_name, 'login_redirect'=>$login_redirect , 'logout_redirect'=>$logout_redirect);
			}
		}
		// return 
		return $membership_types;
	}
	
	// set name
	function set_name($name, $code=NULL){		
		// char limit
		$name = $this->_trim($name);
		// check duplicate value
		if(!in_array($name, array_values($this->membership_types))){
			// get code	
			if($code){
				$this->membership_types[$code] = $name;
			}else{
				// get new code
				$code = $this->get_type_code($name); 			
				// merge to old array
				$this->membership_types = array_merge($this->membership_types, array($code => $name)); 			
			}	
			// return code on success
			return $code; 	
		}
		// return error
		return false;			
	}
	
	// set one login redirect
	function set_login_redirect($type_code, $redirect=NULL){
		// set if provided
		if($redirect){ 
			$this->login_redirects[$type_code] = trim($redirect);	
		}else{
			$this->login_redirects[$type_code] = '';
		}	
	} 
	
	// set multiple login redirects
	function set_login_redirects($redirects){
		// set if provided
		if(is_array($redirects)){			
			$this->login_redirects = array_merge($this->login_redirects,$redirects);
		}	
	} 
	// set one login redirect
	function set_logout_redirect($type_code, $redirect=NULL){
		// set if provided
		if($redirect){ 
			$this->logout_redirects[$type_code] = trim($redirect);	
		}else{
			$this->logout_redirects[$type_code] = '';
		}	
	} 
	
	// set multiple login redirects
	function set_logout_redirects($redirects){
		// set if provided
		if(is_array($redirects)){			
			$this->logout_redirects = array_merge($this->logout_redirects,$redirects);
		}	
	}
	
	// get one login redirect
	function get_login_redirect($type_code){
		// set if provided
		if(isset($this->login_redirects[$type_code])){ 
			return $this->login_redirects[$type_code];	
		}
		// return
		return '';			
	} 
	
	// get all login redirects
	function get_login_redirects(){
		// set if provided
		if(is_array($this->login_redirects)){ 
			return $this->login_redirects;	
		}
		// return
		return array();		
	}
	
	// get one login redirect
	function get_logout_redirect($type_code){
		// set if provided
		if(isset($this->logout_redirects[$type_code])){ 
			return $this->logout_redirects[$type_code];	
		}
		// return
		return '';			
	}  
	
	// get all login redirects
	function get_logout_redirects(){
		// set if provided
		if(is_array($this->logout_redirects)){ 
			return $this->logout_redirects;	
		}		
		// return
		return array();			
	}
	
	// check duplicate
	function is_duplicate($type_name, $type_code=NULL){
		// return
		if(!$type_code){
			return(in_array($type_name, array_values($this->membership_types)));
		}else{
			// get 
			if($code = array_search($type_name,$this->membership_types)){
				return ($code != $type_code) ? true : false;
			}
		}
		// default
		return false;
	}
	// deprecated-------------------------------------------------------------
	// set multiple
	function set_membership_types($membership_types) {
		// check
		if(is_array($membership_types)) $this->membership_types = $membership_types;
	}
	
	// add single
	function set_membership_type($type_name, $return='bool') {
		// check duplicate value
		if(!in_array($type_name, array_values($this->membership_types))){
			// get code			
			$membership_type = array($this->get_type_code($type_name) => $type_name);
			// merge to old array
			$this->membership_types = array_merge($this->membership_types, $membership_type); 
			// treat success
			if($return == 'type') return $membership_type;
			// else as dfault
			return true; 	
		}else{
			// error duplicate
			return false;	
		}	
	}
		
	/**
	 * delete type
	 * @deprecated use delete()
	 */
	function unset_membership_type($code) {
		// deprecae
		return $this->delete($code);
	}
		
	// get code
	function get_type_code($type){
		// return strtolower(preg_replace('/\s+/', '_', $type));
		return strtolower(preg_replace('/\W+/', '_', $type)); // which issue ?
	}
	
	// get type
	function get_type_name($type_code){
		// def
		$type_name = ucwords(str_replace('_', ' ', $type_code));
		// search and match
		foreach($this->membership_types as $code=>$name){
			// match
			if($code == $type_code){
				$type_name = $name;
			}		
		}	
		// ret
		return $type_name;
	}
	
	// get nicecode
	function get_type_nicecode($type){
		return strtolower(preg_replace('/[\_]\s+/', '-', $type));
	}
	
	// get all types
	function get_membership_types(){
		return $this->membership_types;
	}
	
	// deprecated end-------------------------------------------------------------
	
	private function _trim($val, $field='name'){
		return substr(trim($val), 0, 250);
	}
	
	// defaults
	function _set_defaults($membership_types=false){
		// code
		$this->code        = __CLASS__;
		// name
		$this->name        = 'Membership Types Lib';
		// description
		$this->description = 'Membership Types Lib';
		
		// set from argument
		if(!is_array($membership_types)){
			$membership_types = array('guest'=>'Guest', 'trial'=>'Trial', 'free'=>'Free', 'member'=>'Member');
		}				
		// set
		$this->set_membership_types($membership_types);	
	}
	
	// object data migration
	function apply_fix($old_obj){
		// to be copied vars
		$vars = array('membership_types','login_redirects','logout_redirects','capability_orders');
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
		$vars = array('membership_types','login_redirects','logout_redirects','capability_orders');
		// set
		foreach($vars as $var){
			// var
			$this->options[$var] = $this->{$var};
		}	
	}
	
	/**
	 * Overridden function:	
	   See the comment below:
	 *
	 * @param string $option_name
	 * @param array $current_value current value for class var(can be default)
	 * @param array $option_value: updated value
	 */
	function _option_merge_callback($option_name, $current_value, $option_value) {		
		// This is to make sure that the default membership_type array doesn;t contain the hardcoded option 'member' 
		// incase user deletes it and option array doesn't have it.
		// issue#: 521
		if($option_name == 'membership_types') {
			//This is to copy from options:
			$current_value = array();			
		}
		// update class var
		$this->{$option_name} = mgm_array_merge_recursive_unique($current_value,$option_value);
	}
}
// core/libs/classes/mgm_membership_types.php