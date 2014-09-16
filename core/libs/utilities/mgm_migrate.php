<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members migrate utility class 
 *
 * @package MagicMembers
 * @since 2.5
 */ 
class mgm_migrate {
	// filename
	private $filename = '';
	
	// constructor
	function __construct($filename = false) {
		// php4
		$this->mgm_migrate($filename);
	}
	
	// php4 construct
	function mgm_migrate($filename = false){
		// version
		$this->version = mgm_get_class('auth')->get_product_info('product_version');
		// check ext
		if ($filename) {
			$this->filename = $this->check_extension($filename);
		} else {
		// create
			$this->filename = 'export-'.$this->version.'-'.time().'.xml';
		}
	}
	
	// set file 
	function set_filename($filename) {
		// set
		$this->filename = $this->check_extension($filename);
	}

	// check ext
	function check_extension($filename) {
		// check
		if (!preg_match('/\.xml$/i',$filename)) {
			$filename .= '.xml';
		}
		// return
		return $filename;
	}
	
	// create
	function create($filepath=false,$data = array()){
		global $wpdb;
		// create object
		$xml = simplexml_load_string(sprintf('<?xml version="1.0" encoding="utf-8"?><magicmembers version="%s" mapping="any"></magicmembers>',$this->version)); 
		
		// check
		if($xml){
			foreach ($data as $export_section){
				
				switch ($export_section) {
					case 'general_settings':
						//system object
						$system_obj = mgm_get_class('system');
						foreach ($system_obj->setting as $name =>$value){
							//content protection settings
							$cp_settings_arr = array(	'content_protection','public_content_words','content_protection_allow_html',
														'content_hide_by_membership','enable_excerpt_protection','using_the_excerpt_in_theme',
														'no_access_redirect_loggedin_users','no_access_redirect_loggedout_users','redirect_on_homepage','use_rss_token');
							//skip content protection settings							
							if(in_array($name,$cp_settings_arr)) {continue;}
														
							$general_settings = $xml->addChild('general_settings');
							$general_settings->addAttribute('name',$name);
							if(is_array($value)) {
								$general_settings->addAttribute('value',serialize($value));
							}else {
								$general_settings->addAttribute('value',$value);
							}														
						}
						if($value = get_option('mgm_extend_dir')){
							$general_settings = $xml->addChild('general_settings');
							$general_settings->addAttribute('name','extend_dir');
							$general_settings->addAttribute('value',$value);						
						}
						if($value = get_option('mgm_affiliate_id')){
							$general_settings = $xml->addChild('general_settings');
							$general_settings->addAttribute('name','affiliate_id');
							$general_settings->addAttribute('value',$value);						
						}
					break;
					case 'messages_settings':
						$messages_sql = "SELECT `name` , `content` FROM `".TBL_MGM_TEMPLATE."` WHERE `type` IN ('messages','templates')";
						$messages = $wpdb->get_results($messages_sql);
						foreach ($messages as $message) {
							$message_settings = $xml->addChild('messages_settings');
							$message_settings->addAttribute('name',$message->name);
							$message_settings->addAttribute('value',$message->content);
						}													
					break;
					case 'emails_settings':
						$emails_sql = "SELECT `name` , `content` FROM `".TBL_MGM_TEMPLATE."` WHERE `type` IN ('emails')";
						$emails = $wpdb->get_results($emails_sql);
						foreach ($emails as $email) {
							$email_settings = $xml->addChild('emails_settings');
							$email_settings->addAttribute('name',$email->name);
							$email_settings->addAttribute('value',$email->content);
						}						
					break;
					case 'content_protection_settings':
						//system object
						$system_obj = mgm_get_class('system');
						foreach ($system_obj->setting as $name =>$value){
							$cp_settings_arr = array(	'content_protection','public_content_words','content_protection_allow_html',
														'content_hide_by_membership','enable_excerpt_protection','using_the_excerpt_in_theme',
														'no_access_redirect_loggedin_users','no_access_redirect_loggedout_users','redirect_on_homepage','use_rss_token');
							
							if(in_array($name,$cp_settings_arr)){
								$content_protection_settings = $xml->addChild('content_protection_settings');
								$content_protection_settings->addAttribute('name',$name);
								if(is_array($value)) {
									$content_protection_settings->addAttribute('value',serialize($value));
								}else {
									$content_protection_settings->addAttribute('value',$value);
								}	
							}													
						}						
					break;
				}
			}			
			// create file
			if($filepath){
				return $xml->asXML($filepath);	
			}else{
				// string
				return $xml->asXML();
			}
		}else{
			// log
			// mgm_log('Error creating XML');
			return false;
		}	
	}
	
	// check html content
	function has_html($content){
		// match
		if(preg_match('/<(.*)>(.*)<\/(.*)>/', $content)){
			return true;
		}
		// negative
		return false;
	}
}
// core/libs/utilities/mgm_migrate.php