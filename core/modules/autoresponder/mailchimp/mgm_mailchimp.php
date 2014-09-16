<?php
/**
 * Magic Members mailchimp autoresponder module
 *
 * @package MagicMembers
 * @since 1.0
 */
class mgm_mailchimp extends mgm_autoresponder{
	
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_mailchimp();
	}
	
	// construct
	function mgm_mailchimp(){
		// parent
		parent::mgm_autoresponder();
		// set code
		$this->code = __CLASS__; 
		// set name
		$this->name = 'MailChimp';
		// desc
		$this->description = __('MailChimp Desc.','mgm');		
		// set path
		parent::set_tmpl_path();	
		// read settings
		$this->read();	
		// endpoints, not saved
		$this->set_endpoint('subscribe','https://[domain].api.mailchimp.com/1.3/?output=php&method=listSubscribe');// subscribe
		$this->set_endpoint('unsubscribe','https://[domain].api.mailchimp.com/1.3/?output=php&method=listUnsubscribe');// unsubscribe
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
				$this->setting['unique_id']     = $_POST['setting']['unique_id'];
				$this->setting['apikey']        = $_POST['setting']['apikey'];
				
				//double or single opt-in
				$this->setting['double_optin']     = $_POST['setting']['double_optin'];
				
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
				return json_encode(array('status'=>'success','message'=>sprintf(__('%s settings updated','mgm'),$this->name)));
			break;			
			case 'box':
			default:
			// from box	
				// set fields
				$this->setting['unique_id'] = $_POST['setting']['mailchimp']['unique_id'];
				$this->setting['apikey']    = $_POST['setting']['mailchimp']['apikey'];		
				$this->setting['double_optin'] = $_POST['setting']['mailchimp']['double_optin'];		

				
				// save
				$this->save();							
				// message
				return json_encode(array('status'=>'success','message'=>sprintf(__('%s settings updated','mgm'),$this->name)));
			break;			
		}		
	}
		
	// set postfields
	function set_postfields($user_id){
		// validate		
		if(!isset($this->setting['unique_id']) && !isset($this->setting['apikey'])){			
			return false;
		}
		
		// userdata	
		$userdata = $this->_get_userdata($user_id);	
		
		// set		
		$this->postfields = array(
			  'id'                => trim($this->setting['unique_id']),			
			  'apikey'            => trim($this->setting['apikey']),								  
			  'email_address'     => $userdata['email'],
			  'double_optin'      => $this->setting['double_optin'],
			  'submit'            => 'Submit'	
		);			
		// set extra postfields, not for unsubscribe
		if($this->method != 'unsubscribe') $this->_set_extra_postfields($userdata, 'id', 'merge_vars[%s]');
		
		// return
		return true;
	}
	
	// validate
	function validate(){
		// errors
		$errors = array();
		// check
		if(empty($_POST['setting']['mailchimp']['unique_id'])){
			$errors[] = __('Unique List Id is required','mgm'); 
		}
		// check		
		if(empty($_POST['setting']['mailchimp']['apikey'])){
			$errors[] = __('API Key is required','mgm'); 
		}
		
		// return
		return count($errors) == 0 ? false : $errors;
	}
	
	// get endpoint
	function get_endpoint($method='subscribe'){
		// key
		$apikey = $this->setting['apikey'];
		// mailchipmp url must be configured
		list($key, $domain) = explode('-', $apikey, 2);
		// default
		if (!$domain) $domain = 'us1';
		// return		
		return parent::get_endpoint($method, array('domain'=>$domain));
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
// end of file core/modules/autoresponder/mgm_mailchimp.php