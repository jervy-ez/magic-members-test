<?php
/**
 * MagicMembers Rest Client
 *
 * @version 1.0
 */ 
 class mgm_rest_client{
 	// pravate vars
 	private $api_key;
	private $resource_baseurl = 'http://localhost/magicmediagroup/dev';
	
	// constructor
	public function __construct(){
		// global set up
	}
	
	/**
	 * GET simulation
	 *
	 * @param string resource uri
	 * @param array or null, data
	 * @param string format (xml|json|php|phps)
	 * @return string response
	 */
	public function get($resource, $data=NULL, $format='xml'){
		return $this->_request('GET', $resource, $data, $format);
	}
	
	/**
	 * POST simulation
	 *
	 * @param string resource uri
	 * @param array or null, data
	 * @param string format (xml|json|php|phps)
	 * @return string response
	 */
	public function post($resource, $data=NULL, $format='xml'){
		return $this->_request('POST', $resource, $data, $format);
	}
	
	/**
	 * PUT simulation
	 *
	 * @param string resource uri
	 * @param array or null, data
	 * @param string format (xml|json|php|phps)
	 * @return string response
	 */
	public function put($resource, $data=NULL, $format='xml'){
		return $this->_request('PUT', $resource, $data, $format);
	}
	
	/**
	 * DELETE simulation
	 *
	 * @param string resource uri
	 * @param array or null, data
	 * @param string format (xml|json|php|phps)
	 * @return string response
	 */
	public function delete($resource, $data=NULL, $format='xml'){
		return $this->_request('DELETE', $resource, $data, $format);
	}
	
	/**
	 * set api key
	 *
	 * @param string api key
	 */
	public function set_api_key($api_key){
		$this->api_key = $api_key;
	}	
	
	/**
	 * set resource base url
	 *
	 * @param string base url
	 */
	public function set_resource_baseurl($base_url){
		$this->resource_baseurl = $base_url;
	}
	
	/**
	 * get endpoint
	 *
	 * @param none
	 * @return string url
	 */
	public function get_endpoint(){
		return $this->endpoint;
	}
	
	/**
	 * request 
	 *
	 * @param string VERB ( GET|POST|PUT|DELETE)
	 * @param string resource uri
	 * @param array or null, data
	 * @param string format (xml|json|php|phps)
	 * @return string response
	 */
	private function _request($method='POST', $resource='', $data=NULL, $format='xml'){	
		// endpoint
		$this->endpoint =  $this->resource_baseurl . '/mgmapi/' . $resource . '.' . $format;	
		// cookie
		$cookie_file = tempnam('/tmp', 'mgmapi');
		
		// set headers	
		$headers   = array();	
		$headers[] = "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11";
		$headers[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html,application/json;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$headers[] = "Accept-Language: en-us,en;q=0.5";
		$headers[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
		$headers[] = "Keep-Alive: 300";
		$headers[] = "Connection: keep-alive";		
		$headers[] = "X-MGMAPI-KEY: " . $this->api_key;// API KEY
		
		// init
		$ch = curl_init();
		// set curl opt
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		// curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_REFERER, 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']);	
		// cookie 
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);		
		// method
		switch ($method) {
			case 'POST':
				// data
				$_data = $this->_get_data($data);
				// opt
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $_data);	
				// headers, auto detect _data array: maltipart, _data: string: form-urlencoded
				// $headers[] = "Content-Type: application/x-www-form-urlencoded";		
			break;
			case 'PUT':
				// data
				$_data = $this->_get_data($data);
				// opt
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($ch, CURLOPT_POSTFIELDS, $_data);
				// headers
				$headers[] = "Content-Type: text/plain";
				$headers[] = "Content-Length: " . strlen($_data);
			break;
			case 'DELETE':
				// data
				$_data = $this->_get_data($data);
				// opt
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				curl_setopt($ch, CURLOPT_POSTFIELDS, $_data);
				// headers
				$headers[] = "Content-Type: text/plain";
				$headers[] = "Content-Length: " . strlen($_data);
			break;
			case 'GET':
				// data
				$_data = $this->_get_data($data);
				// data
				if ($_data) $this->endpoint .= ('/' . (strpos($this->endpoint,'?') === FALSE ? '?' : '&') . $_data);							
				// headers
				$headers[] = "Content-Type: text/plain";
			break;
			default:
				die('Invalid Method: ' . $method);
			break;	
		}
		// headers
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		// url
		curl_setopt($ch, CURLOPT_URL, $this->endpoint);	
		// execute
		$response = curl_exec($ch);
		// check for error
		if(($errno = curl_errno($ch)) != CURLE_OK ){
			// get errors
			$error = curl_error($ch);
			$info  = curl_getinfo($ch);
			// reset response
			$response = sprintf('CURL Error[%d]: %s, <br>Info: <pre>%s</pre>, <br>Response: %s', $errno, $error, print_r($info,1), $response);
		}
		// close
		curl_close($ch);
		// return
		return $response;
	}	
	
	/**
	 * create query string from array
	 *
	 * @param array data
	 * @param bool encode flag
	 * @return string encoded data
	 */
	private function _http_build_query($data, $encode=true){	
		// query
		$_query = '';
		// loop
		foreach($data as $key => $value) {
			// array
			if (is_array($value)) {
				// loop
				foreach($value as $item) {
					// append
					if (strlen($_query) > 0) $_query .= "&";
					// append
					$_query .= ($encode==true) ?  ("$key=".urlencode($item)) : ("$key=$value");
				}
			} else {
				// append
				if (strlen($_query) > 0) $_query .= "&";		
				// append				
				$_query .= ($encode==true) ? ("$key=".urlencode($value)) : ("$key=$value");					
			}
		}
		// return
		return $_query;
	}
	
	/**
	 * data to post
	 *
	 * @param array data
	 * @return mixed (encoded data | array)
	 */
	private function _get_data($data=NULL){
		// data to send
		$_data = NULL;	
		// data
		if($data){
			// has upload
			if($this->_has_upload($data)){
				$_data = $data;// do not encode
			}else{
			// encode
				$_data = $this->_http_build_query($data);
			}
		}else{
			$_data = '';
		}
		
		// return 
		return $_data;
	}
	
	/**
	 * check if post data has upload
	 *
	 * @param array data
	 * @return bool
	 */
	private function _has_upload($data){
		// query
		$has_upload = false;
		// loop
		foreach($data as $key => $value) {
			// array
			if (is_array($value)) {
				// loop
				foreach($value as $item) {
					// check
					if(preg_match('/^@/', $item)){
						$has_upload = true; break;
					}
				}
			} else {
				// check
				if(preg_match('/^@/', $value)){
					$has_upload = true; break;
				}			
			}
		}
		// return
		return $has_upload;
	}
	
	/**
	 * log
	 *
	 * @param array data
	 * @param string prefix
	 * @return bool success
	 */
	private function _log($data, $prefix = 'error_log'){
		@file_put_contents($prefix.'__'.time().'.log', print_r($data,1));
	}	
 }
?>