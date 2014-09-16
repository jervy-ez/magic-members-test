<?php
/**
 * Magic Members getresponse autoresponder module
 *
 * @package MagicMembers
 * @since 1.0
 */
class mgm_getresponse extends mgm_autoresponder {
	
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_getresponse();
	}
	
	// construct
	function mgm_getresponse(){
		// parent
		parent::mgm_autoresponder();
		// set code
		$this->code = __CLASS__; 
		// set name
		$this->name = 'GetResponse';
		// desc
		$this->description = __('GetResponse Desc','mgm');		
		// set path
		parent::set_tmpl_path();	
		// read settings
		$this->read();	
		// endpoints, not saved
		$this->set_endpoint('subscribe','http://api2.getresponse.com');// subscribe
		$this->set_endpoint('unsubscribe','http://api2.getresponse.com');// unsubscribe
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
				$this->setting['category1']     = $_POST['setting']['category1'];
				$this->setting['ref']           = $_POST['setting']['ref'];
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
				$this->setting['category1'] = $_POST['setting']['getresponse']['category1'];
				$this->setting['ref']       = $_POST['setting']['getresponse']['ref'];
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
		if(!isset($this->setting['category1']) && !isset($this->setting['ref'])){
			return false;
		}
		
		// userdata	
		$userdata = $this->_get_userdata($user_id);	
		
		// set		
		$this->postfields = array(
			'campaign_name' => $this->setting['category1'],			
			'campaign_ref'  => $this->setting['ref'],			
			'email'         => $userdata['email']
		);	
		
		// set extra postfields, not for unsubscribe
		if($this->method != 'unsubscribe') $this->_set_extra_postfields($userdata, 'campaign_name');
		
		// return 
		return true;
	}
	
	// validate
	function validate(){
		// errors
		$errors = array();
		// check
		if(empty($_POST['setting']['getresponse']['category1'])){
			$errors[] = __('Campaign name is required','mgm'); 
		}
		// check		
		if(empty($_POST['setting']['getresponse']['ref'])){
			$errors[] = __('API Key is required','mgm'); 
		}		
		// return
		return count($errors) == 0 ? false : $errors;
	}
	
	// Sending user confirmation mail to campaign admin
	function send_confirmation() {
		
		$todate = new DateTime();
		$todate = $todate->format('Y-m-d');

		$url = $this->get_endpoint('live');
		$fields = array();
		
		// set		
		$this->postfields = array('campaign_name' => $this->setting['category1'], 'campaign_ref'  => $this->setting['ref']);	
		
		$fields = $this->postfields;
		$this->get_proxy($url);	
		// obj				
		$jsonrpc = new mgm_jsonrpc_client($url);
		// campaigns
		$campaigns = $jsonrpc->get_campaigns($fields['campaign_ref'], array ('name' => array ( 'EQUALS' => $fields['campaign_name'] ) ) ); 
        // validate
		if (is_array($campaigns) && count($campaigns)>0) {
			// campaign_id
			$campaign_id = array_shift(array_keys($campaigns));
			
			foreach($campaigns as $campaign) {
				    //Contacts from campaigns
					$contacts = $jsonrpc->get_contacts($fields['campaign_ref'], 
					array ('campaigns' => array ($campaign_id),
						   'created_on' => array ( 'AT' => $todate ) ) );	
				if (!empty($contacts)) {
					
					$confirm_users = '';
					foreach ($contacts as $contact) {
						
						if (!empty($contact['name']) && !empty($contact['email'])) {
							$confirm_users .= $contact['name']." [ ".$contact['email']."], ";
						} else {
							
							if (!empty($contact['name'])) { 
								$confirm_users .= $contact['name'].",";
							}
							
							if (!empty($contact['email'])) { 
								$confirm_users .= $contact['email'].",";
							}
						}
					}
					// contacts
					$confirm_users = rtrim($confirm_users, ', ');
					$campaign_name = $campaign['name'];
					$campaign_admin_email = $campaign['from_email'];
					$subject = "User(s) confirmed in campaign - {$campaign_name} - Automated mail";
					//Mail message
					$message = "Hi Admin,\n\n<br/><br />";
					$message .= "Campaign Name : {$campaign_name}\n\n<br /><br />";
					if (strpos($confirm_users, ',') === false && !empty($confirm_users)) { 
						$message .= " Below user has been confirmed today. \n\n<br /><br /> ";
						$message .= " User : {$confirm_users}\n\n<br /";
					}
					else {
						if (!empty($confirm_users)) {
							$message .= " Below users has been confirmed today: \n\n<br /><br /> ";
							$message .= " Users : {$confirm_users}\n\n<br /";
						}
					}
					//Sending mail
					mgm_mail($campaign_admin_email, $subject, $message);
				}
			}
			
		}

	}
	
	// proxy submit
	function get_proxy($url){		
		// fields		
		$fields = $this->postfields;			
		// init result
		$result = array();				
		// obj				
		$jsonrpc = new mgm_jsonrpc_client($url);				
		// campaigns
		$campaigns = $jsonrpc->get_campaigns($fields['campaign_ref'], array ('name' => array ( 'EQUALS' => $fields['campaign_name'] ) ) ); 					
		// validate
		if(is_array($campaigns) && count($campaigns)>0){
			// campaign_id
			$campaign_id = array_shift(array_keys($campaigns));
			// check
			if($campaign_id) {
				// campaign fields
				$campaign_fields = array (
					'campaign'  => $campaign_id,
					'action'    => 'standard',	
					'email'     => $fields['email'],
					'cycle_day' => 0,
					'ip'        => mgm_get_client_ip_address()            
				);
				// append extra
				foreach($this->postfields as $field=>$value){
					// set, skip already set and base ones
					if(!isset($campaign_fields[$field]) && !in_array($field, array('campaign_name','campaign_ref'))){
						$campaign_fields['customs'][] = array('name' => strtolower($field), 'content' => $value);
					}
				}
				// add to campaign
				return $result = $jsonrpc->add_contact($fields['campaign_ref'], $campaign_fields);							
			}		
		}
					
		// return as executed 
		return true;
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
// end of file core/modules/autoresponder/mgm_getresponse.php