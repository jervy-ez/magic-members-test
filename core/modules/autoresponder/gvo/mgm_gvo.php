<?php
/**
 * Magic Members gvo autoresponder module
 *
 * @package MagicMembers
 * @since 1.0
 */
class mgm_gvo extends mgm_autoresponder{
	
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_gvo();
	}
	
	// construct
	function mgm_gvo(){
		// parent
		parent::mgm_autoresponder();
		// set code
		$this->code = __CLASS__; 
		// set name
		$this->name = 'GVO';
		// desc
		$this->description = __('GVO Desc','mgm');		
		// set path
		parent::set_tmpl_path();	
		// read settings
		$this->read();	
		// endpoints, not saved
		$this->set_endpoint('subscribe','http://www.gogvo.com/subscribe.php');// subscribe
		$this->set_endpoint('unsubscribe','http://www.gogvo.com/unsubscribe.php');// unsubscribe
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
				$this->setting['campaign']      = $_POST['setting']['campaign'];
				$this->setting['formid']        = $_POST['setting']['formid'];
				$this->setting['affiliatename'] = $_POST['setting']['affiliatename'];
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
				$this->setting['campaign']      = $_POST['setting']['gvo']['campaign'];
				$this->setting['formid']        = $_POST['setting']['gvo']['formid'];
				$this->setting['affiliatename'] = $_POST['setting']['gvo']['affiliatename'];
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
		if(!isset($this->setting['campaign']) && !isset($this->setting['formid']) && !isset($this->setting['affiliatename'])){
			return false;
		}
		
		// userdata	
		$userdata = $this->_get_userdata($user_id);	
		
		// set		
		$this->postfields = array(
			//'Campaign'      => $this->setting['campaign'],
			'CampaignCode'      => $this->setting['campaign'],
			'FormId'        => $this->setting['formid'],
			'AffiliateName' => $this->setting['affiliatename'],								
			'Email'         => $userdata['email'],
			//'FullName'      => $userdata['full_name'],
			'submit'        => 'Submit'
		);	
		
		// set extra postfields, not for unsubscribe
		if($this->method != 'unsubscribe') $this->_set_extra_postfields($userdata, 'Campaign');
		
		// return 
		return true;
	}
	
	// validate
	function validate(){
		// errors
		$errors = array();
		// check
		if(empty($_POST['setting']['gvo']['campaign'])){
			$errors[] = __('Campaign is required','mgm'); 
		}
		// check		
		if(empty($_POST['setting']['gvo']['formid'])){
			$errors[] = __('Form Id is required','mgm'); 
		}
		// check		
		if(empty($_POST['setting']['gvo']['affiliatename'])){
			$errors[] = __('Affiliate Name is required','mgm'); 
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
// end of file core/modules/autoresponder/mgm_gvo.php