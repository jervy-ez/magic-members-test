<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members api modules parent class
 * base class for admin modules
 *
 * @package MagicMembers
 * @since 2.5
 */
class mgm_api_controller extends mgm_object{
	// protected vars
	protected $request;
	protected $response;	
	protected $_supported_verbs = array('get', 'delete', 'post', 'put');
	
	// List all supported verbs, the first will be the default format
	protected $_supported_formats = array(
		'xml'     => 'application/xml',
		'json'    => 'application/json',
		//'jsonp' => 'application/javascript',
		'phps'    => 'application/vnd.php.serialized',
		'php'     => 'text/plain'
	);
	
	// construct
	public function __construct(){		
		// php4 construct
		$this->mgm_api_controller();
	}
	
	// php4 construct
	public function mgm_api_controller(){
		// parent
		parent::__construct();		
		// no saving
		$this->saving = false;				
		// update formats/verbs form setting
		$this->_filter_formtats();
		$this->_filter_verbs();
		// rest
		$this->_rest_request();
	}	
	
	// response
	public function response($data = array(), $http_code = null)
	{
		// If data is empty and not code provide, error and bail
		if (empty($data) && $http_code === null)
    	{
    		$http_code = 404;
    	}
		// Otherwise (if no data but 200 provided) or some data, carry on camping!
		else
		{
			// cool syntax
			is_numeric($http_code) || $http_code = 200;

			// If the format method exists, call and return the output in that format
			if (method_exists($this, '_format_' . $this->response->format))
			{
				// Set the correct format header
				header('Content-Type: '.$this->_supported_formats[$this->response->format]);
				
				// method
				$method = '_format_' . $this->response->format;
				// output
				$output = $this->$method($data);
			}

			// If the format method exists, call and return the output in that format
			elseif (method_exists('mgm_format', 'to_' . $this->response->format))
			{
				// Set the correct format header
				header('Content-Type: ' . $this->_supported_formats[$this->response->format]);
				
				// method
				$method = 'to_' . $this->response->format;				
				
				// output
				$output = mgm_format::$method($data, NULL, 'response');				
			}
			// Format not supported, output directly
			else
			{
				$output = $data;
			}
		}
		
		// header	
		header('HTTP/1.1: ' . $http_code);
		header('Status: ' . $http_code);
		header('Content-Length: ' . strlen($output));
		
		// log
		exit($output);
	}
	
	// re route to verb
	public function route_action($action='index', $params){
		// set action/params
		$this->request->action = (!empty($action))? $action : 'index'; 
		$this->request->params = $params;
		// access control, @ToDO other restriction later
		if(!$this->_is_authorized($message)){
			// response
			$response = array(array('status' => false, 'error' => $message), 403);
		}else{					
			// action verb, by default its takes <action>_<verb> form i.e. members::index_get()
			$action_verbs = array( ($this->request->action . '_' . $this->request->verb) ) ;
			// get is default
			if($this->request->verb == 'get'){
				$action_verbs[] = $this->request->action;
			}
			// action method 
			$action_method = false;
			// loop
			foreach($action_verbs as $action_verb){
				// match
				if(method_exists($this, $action_verb)){
					// set
					$action_method = $action_verb;	
					// exit
					break;
				}
			}				
			
			// load action
			if( $action_method ){
				// call
				$response = call_user_func_array(array(&$this, $action_method), $this->request->params);						
			}else{
				// report nicely
				if($alt_action_verb = $this->get_alt_action_verb($this->request->action)){
					$m = sprintf(__('Invalid Request: api action - %s accepts %s verb only', 'mgm'), $this->request->action, $alt_action_verb);
				}else{
					$m = sprintf(__('Invalid Request: no such api action - %s','mgm'), $this->request->action);
				}
			
				// handle action error					
				$response = array(array('status' => false, 'error' => $m), 404);
			}
		}
		// response
		$this->response(array_shift($response), array_shift($response));
	}
	
	// get_alt_action_verb
	public function get_alt_action_verb($action){
		// init 
		$alt_action_verb = false;
		// loop
		foreach($this->_supported_verbs as $verb){
			// check
			if(method_exists($this, ($action . '_' . $verb))){
				$alt_action_verb = $verb; break;
			}
		}
		// return
		return $alt_action_verb;
	}
	
	// set_uri_string
	public function set_uri_string($uri_string){
		// request uri 
		$this->request->uri_string = $uri_string;	
	}
	
	// -- PRIVATE -------------------------------------------------
	// build request
	private function _rest_request(){
		// request 
		$this->request = new stdClass;
		// request verb
		$this->request->verb = $this->_get_request_verb();		
		// request format
		$this->request->format = $this->_get_request_format();				
		// store data
		$this->request->data = $this->_get_request_data();		
		// response 
		$this->response = new stdClass;
		// response format
		$this->response->format = $this->_get_response_format();			
		// log
		// mgm_log($this, 'restapi_' . time() . '_' . $this->request->verb);									
	}
		
	// get request verb
	private function _get_request_verb(){
		// request
		$verb = strtolower($_SERVER['REQUEST_METHOD']);
		// check
		if (in_array($verb, $this->_supported_verbs)){
			// return 
			return $verb;
		}
		// default
		return 'get';		
	}
	
	// get request format
	private function _get_request_format(){
		// if set
		if (isset($_SERVER['CONTENT_TYPE'])){
			// Check all formats against the HTTP_ACCEPT header
			foreach ($this->_supported_formats as $format => $mime){
				// match
				if (strpos($match = $_SERVER['CONTENT_TYPE'], ';')){
					$match = current(explode(';', $match));
				}
				// match
				if ($match == $mime){
					// return 
					return $format;
				}
			}
		}
		// null
		return NULL;
	}
	
	// get request data
	private function _get_request_data(){
		// BODY from input stream
		$this->request->data['body'] = NULL;		
		// on verb
		switch ($this->request->verb)
		{
			case 'get':
				// GET variables
				parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $this->request->data['get']);
			break;
			case 'post':
				// POST variables
				$this->request->data['post'] = $_POST;
				// format
				if($this->request->format) $this->request->data['body'] = file_get_contents('php://input');
			break;
			case 'put':
				// HTTP body
				if($this->request->format && $this->request->format != 'php') 
				{
					$this->request->data['body'] = file_get_contents('php://input');
				}else
				{
				// No file type, parse args
					parse_str(file_get_contents('php://input'), $this->request->data['put']);
				}				
			break;
			case 'delete':
				// DELETE variables
				parse_str(file_get_contents('php://input'), $this->request->data['delete']);
			break;
		}

		// Now we know all about our request, let's try and parse the body if it exists
		if ($this->request->format && $this->request->data['body'])
		{
			$this->request->data['body'] = $this->_get_request_body();
		}
		
		// merge
		$this->request->data['global'] = array_merge(isset($this->request->data['get']) ? (array)$this->request->data['get'] : array(),
		                                             isset($this->request->data['post']) ? (array)$this->request->data['post'] : array(),
													 isset($this->request->data['put']) ? (array)$this->request->data['put'] : array(),
													 isset($this->request->data['delete']) ? (array)$this->request->data['delete'] : array(),
													 isset($this->request->data['body']) ? (array)$this->request->data['body'] : array());
		// return self
		return $this->request->data;											 
	}
	
	// get response format
	private function _get_response_format($default='xml'){
		// get pattern
		$pattern = '/\.(' . implode('|', array_keys($this->_supported_formats)) . ')$/';	
		
		// uri id get var set
		if(!empty($_GET)){ 		
			list($_REQUEST_URI, $_QUERY_STRING) = explode('?', $_SERVER['REQUEST_URI']);
		}else{
			$_REQUEST_URI = $_SERVER['REQUEST_URI'];
		}	
		
		// file extension is used
		if (preg_match($pattern, $_REQUEST_URI, $matches))
		{
			return $matches[1];
		}elseif ($this->request->data['get'] && ! is_array(end($this->request->data['get'])) && preg_match($pattern, end($this->request->data['get']), $matches))
		{
			// Check if a file extension is used in get param
			// The key of the last argument
			$last_key = end(array_keys($this->request->data['get']));

			// Remove the extension from arguments too
			$this->request->data['get'][$last_key] = preg_replace($pattern, '', $this->request->data['get'][$last_key]);
			// return
			return $matches[1];
		}

		// format arg in get
		if (isset($this->request->data['get']['format']) && array_key_exists($this->request->data['get']['format'], $this->_supported_formats))
		{
			return $this->request->data['get']['format'];
		}
		
		// return default
		return $default;
	}
	
	// parse only known formats
	private function _get_request_body(){
		//  format
		switch($this->request->format){			
			case 'json':// json, .json
				return mgm_format::to_array(mgm_format::from_json($this->request->data['body']));
			break;			
			case 'phps': // php serialize, .phps
				return mgm_format::to_array(mgm_format::from_phps($this->request->data['body']));
			break;
			case 'php': // php array, .php				
				return mgm_format::to_array(mgm_format::from_php($this->request->data['body']));
			break;
			case 'xml':	// xml, .xml
			default:
				return mgm_format::to_array(mgm_format::from_xml($this->request->data['body']));
			break;
		}
	}
	
	// check authorized
	private function _is_authorized(&$message){
        global $wpdb;
		// init defaults
		$api_key = '';
		// set message
		$message = __('Invalid API Key.','mgm');
		// authorized
		$authorized = false;
		// rest
		$this->rest = new stdClass;
		// get key name
		$key_name = strtoupper(str_replace('-', '_', MGM_API_KEY_VAR));	
		// Work out the name of the SERVER entry based on config
		if(isset($_SERVER['HTTP_' . $key_name])){ 
		// HTTP_X_MGMAPI_KEY 
			$api_key = $_SERVER['HTTP_' . $key_name];		
		}elseif(isset($this->request->data['global'][MGM_API_KEY_VAR]))	{ 
		// post/get val X_MGMAPI_KEY
			$api_key = $this->request->data['global'][MGM_API_KEY_VAR];
		}else{
			// set message
			$message = __('Invalid Request, No API Key provided!','mgm');
		}			
		
		// check key 
		if ($api_key){
			// check db
			$row = $wpdb->get_row(sprintf("SELECT * FROM `%s` WHERE `api_key` = '%s'", TBL_MGM_REST_API_KEY, $api_key)); 							
			// set
			if(isset($row->id) && (int)$row->id > 0){
				// set
				$this->rest->access = $row;
				// return
				$authorized = true;
			}
		}		
		// log, @todo, if only active
		$this->_log_request($api_key, $authorized);
		// No key has been sent
		return $authorized;
	}
	
	// log
	private function _log_request($api_key, $authorized){
		global $wpdb;
		// sql data
		$sql_data = array();
		// set
		$sql_data['api_key']       = $api_key;
		$sql_data['uri']           = $this->request->uri_string;
		$sql_data['method']        = $this->request->verb;
		$sql_data['params']        = json_encode($this->request->data['global']);
		$sql_data['ip_address']    = mgm_get_client_ip_address();
		$sql_data['is_authorized'] = ($authorized === TRUE) ? 'Y' : 'N';
		$sql_data['create_dt']     = date('Y-m-d H:i:s');
		// insert
		$wpdb->insert(TBL_MGM_REST_API_LOG, $sql_data);	
	}
	
	// filter formtats
	private function _filter_formtats(){
		// get formats
		$rest_output_formats = mgm_get_class('system')->setting['rest_output_formats'];
		// check
		if(is_array($rest_output_formats)){
			// $_supported_formats
			$_supported_formats = array();
			// loop
			foreach($rest_output_formats as $format){
				$_supported_formats[$format] = $this->_supported_formats[$format];
			}
			// set
			$this->_supported_formats = $_supported_formats;
			// log
			// mgm_log($this->_supported_formats, __FUNCTION__);
		}
	}
	
	// filter verbs
	private function _filter_verbs(){
		// get verbs
		$rest_input_methods = mgm_get_class('system')->get_setting('rest_input_methods');
		// check
		if(is_array($rest_input_methods)){
			// only matching
			$this->_supported_verbs = array_intersect($rest_input_methods, $this->_supported_verbs);
		}
	}
}
// end of file core/libs/core/mgm_api_controller.php
