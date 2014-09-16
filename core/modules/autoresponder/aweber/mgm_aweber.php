<?php
/**
 * Magic Members aweber autoresponder module
 *
 * @package MagicMembers
 * @since 1.0
 */
class mgm_aweber extends mgm_autoresponder{

	// construct
	function __construct(){
		// php4 construct
		$this->mgm_aweber();
	}
	
	// construct
	function mgm_aweber(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set name
		$this->name = 'Aweber';
		// desc
		$this->description = __('Aweber Desc','mgm');		
		// set path
		parent::set_tmpl_path();	
		// read settings
		$this->read();	
		// endpoints, not saved
		$this->set_endpoint('subscribe','http://www.aweber.com/scripts/addlead.pl');//subscribe		
		$this->set_endpoint('unsubscribe','http://www.aweber.com/scripts/removelead.pl');//unsubscribe, dummy			
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
	
	// settings box api hook
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
				// primary
				$this->setting['form_id']       = $_POST['setting']['form_id'];
				$this->setting['unit']          = $_POST['setting']['unit'];
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
				$this->setting['form_id'] = $_POST['setting']['aweber']['form_id'];
				$this->setting['unit']    = $_POST['setting']['aweber']['unit'];
				// save object options		
				$this->save();
				// message	
				return json_encode(array('status'=>'success','message'=>sprintf(__('%s settings updated','mgm'), $this->name)));
			break;			
		}		
	}
	
	// set postfields
	function set_postfields($user_id){	
		// validate
		if(!isset($this->setting['form_id']) && !isset($this->setting['unit'])){
			return false;
		}
			
		// userdata	
		$userdata = $this->_get_userdata($user_id);	
		
		// set
		$this->postfields = array(
			'meta_web_form_id'     => $this->setting['form_id'],
			'meta_split_id'        => '',
			'unit'                 => $this->setting['unit'],
			'redirect'             => 'http://www.aweber.com/form/thankyou_vo.html',
			'meta_redirect_onlist' => '',
			'meta_adtracking'      => '',
			'meta_message'         => '1',
			'meta_required'        => 'from',
			'meta_forward_vars'    => '0',
			'from'                 => $userdata['email'],
			//'name'                 => $userdata['full_name'],
			'submit'               => 'Submit'
		);	
		
		// set extra postfields, not for unsubscribe
		if($this->method != 'unsubscribe') $this->_set_extra_postfields($userdata, 'unit');
		
		// return 
		return true;
	}		
	
	// validate
	function validate(){
		// errors
		$errors = array();
		// check
		if(empty($_POST['setting']['aweber']['form_id'])){
			$errors[] = __('Web Form Id is required','mgm'); 
		}
		// check		
		if(empty($_POST['setting']['aweber']['unit'])){
			$errors[] = __('Unit/List Name is required','mgm'); 
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
// end of file core/modules/autoresponder/mgm_aweber.php