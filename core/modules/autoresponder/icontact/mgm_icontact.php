<?php
/**
 * Magic Members icontact autoresponder module
 *
 * @package MagicMembers
 * @since 1.0
 */
class mgm_icontact extends mgm_autoresponder{

	// construct
	function __construct(){
		// php4 construct
		$this->mgm_icontact();
	}
	
	// construct
	function mgm_icontact(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set name
		$this->name = 'iContact';
		// desc
		$this->description = __('iContact Desc.','mgm');		
		// set path
		parent::set_tmpl_path();	
		// read settings
		$this->read();	
		// endpoints, not saved
		$this->set_endpoint('subscribe','https://app.icontact.com/icp/signup.php'); // subscribe
		$this->set_endpoint('unsubscribe','https://app.icontact.com/icp/unsubscribe.php'); // unsubscribe
	}
	
	// settings api hook
	function settings(){
		global $wpdb;
		// data
		$data = array();		
		// set 
		$data['module']           = $this;
		// fields
		$data['custom_fields']    = $this->_get_custom_fields();
		// membership types
		$data['membership_types'] = $this->_get_membership_types();
		// load template view
		return $this->load->template('settings', array('data'=>$data), true);
	}	
	
	// settings_box
	function settings_box(){
		global $wpdb;
		// data
		$data = array();	
		// set 
		$data['module'] = $this;
		// load template view
		return $this->load->template('settings_box', array('data'=>$data), true);		
	}
	
	// update
	function settings_update(){
		// form type 
		switch($_POST['setting_form']){
			case 'main':
			// form main
				// set fields
				$this->setting['clientid']      = $_POST['setting']['clientid'];
				$this->setting['formid']        = $_POST['setting']['formid'];
				$this->setting['listid']        = $_POST['setting']['listid'];
				$this->setting['specialid']     = $_POST['setting']['specialid'];
				$this->setting['doubleopt']     = $_POST['setting']['doubleopt'];
				// fieldmap
				$this->setting['fieldmap']      = $this->_make_assoc($_POST['setting']['fieldmap']);
				// membershipmap
				$this->setting['membershipmap'] = $this->_make_assoc($_POST['setting']['membershipmap']);
				// update enable/disable
				$this->enabled                  = $_POST['enabled']; 
				// enable/disable method
				$activate_method = bool_from_yn($this->enabled) ? 'activate_module' : 'deactivate_module';				
				// update
				$ret = call_user_func_array(array(mgm_get_class('system'),$activate_method),array($this->code,$this->type));					
				// save object options
				$this->save();
				// message	
				return json_encode(array('status'=>'success','message'=>sprintf(__('%s settings updated','mgm'), $this->name)));
			break;			
			case 'box':
			default:
			// from box
				// set fields
				$this->setting['clientid']  = $_POST['setting']['icontact']['clientid'];
				$this->setting['formid']    = $_POST['setting']['icontact']['formid'];
				$this->setting['listid']    = $_POST['setting']['icontact']['listid'];
				$this->setting['specialid'] = $_POST['setting']['icontact']['specialid'];				
				$this->setting['doubleopt'] = $_POST['setting']['icontact']['doubleopt'];				
				// update object options
				$this->save();
				// message	
				return json_encode(array('status'=>'success','message'=>sprintf(__('%s settings updated','mgm'), $this->name)));
			break;			
		}		
	}
	
	// set postfields
	function set_postfields($user_id){
		// validate
		if(!isset($this->setting['formid']) && !isset($this->setting['clientid']) && !isset($this->setting['listid']) 
		    && !isset($this->setting['specialid']) && !isset($this->setting['doubleopt'])){
			// return
			return false;
		}
		
		// userdata	
		$userdata = $this->_get_userdata($user_id);	
		
		// specialidfld
		$specialidfld = sprintf('specialid:%s',$this->setting['listid']);
		
		// set		
		$this->postfields = array(
			'formid'       => $this->setting['formid'],
			'clientid'     => $this->setting['clientid'],
			'listid'       => $this->setting['listid'],
			$specialidfld  => $this->setting['specialid'],
			'doubleopt'    => $this->setting['doubleopt'],
			'reallistid'   => '1',
			'fields_email' => $userdata['email'],			
			'submit'       => 'Submit'
		);	
		
		// set extra postfields, not for unsubscribe
		if($this->method != 'unsubscribe') $this->_set_extra_postfields($userdata, 'listid', 'fields_%s');
		
		// multiple fields, listid,specialid
		if(preg_match('/,/', $this->postfields['listid'])){
			list($this->postfields['listid'], $this->setting['specialid']) = explode(',', $this->postfields['listid']);
		}
		
		// update specialidfld if listid update for membership type
		if(trim($this->setting['listid']) != trim($this->postfields['listid'])){
			// differnce in base listid and membership type listid			
			// unset old special id
			unset($this->postfields[$specialidfld]);
			
			// specialidfld
			// $specialidfld = sprintf('specialid:%s',$this->setting['listid']);/ bug, keeping same listid		
			$specialidfld = sprintf('specialid:%s', trim($this->postfields['listid']));
			
			// postfields
			$this->postfields[$specialidfld] = trim($this->setting['specialid']);
		}
		
		// return 
		return true;
	}
	
	// validate
	function validate(){
		// errors
		$errors = array();
		// check		
		if(empty($_POST['setting']['icontact']['clientid'])){
			$errors[] = __('Client id is required','mgm'); 
		}
		// check
		if(empty($_POST['setting']['icontact']['formid'])){
			$errors[] = __('Form id is required','mgm'); 
		}		
		// check		
		if(empty($_POST['setting']['icontact']['listid'])){
			$errors[] = __('List id is required','mgm'); 
		}
		// check		
		if(empty($_POST['setting']['icontact']['specialid'])){
			$errors[] = __('Special id is required','mgm'); 
		}
		// return
		return count($errors) == 0 ? false : $errors;
	}
	
	// user unsubscribe from the AR list
	function unsubscribe($user_id){	
		// set method
		$this->set_method('unsubscribe');
		// set params
		if($this->set_postfields($user_id)){			
			// transport
			return $this->_transport($user_id);
		}
		// return 
		return false;
	}
}
// end of file core/modules/autoresponder/mgm_icontact.php