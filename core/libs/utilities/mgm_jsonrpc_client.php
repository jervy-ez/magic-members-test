<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/*
					COPYRIGHT

Copyright 2007 Sergio Vaccaro <sergio@inservibile.org>

This file is part of JSON-RPC PHP.

JSON-RPC PHP is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

JSON-RPC PHP is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with JSON-RPC PHP; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * The object of this class are generic jsonRPC 1.0 clients
 * http://json-rpc.org/wiki/specification
 *
 * @author sergio <jsonrpcphp@inservibile.org>
 */
class mgm_jsonrpc_client {
	
	/**
	 * Debug state
	 *
	 * @var boolean
	 */
	private $debug;
	
	/**
	 * The server URL
	 *
	 * @var string
	 */
	private $url;
	/**
	 * The request id
	 *
	 * @var integer
	 */
	private $id;
	/**
	 * If true, notifications are performed instead of requests
	 *
	 * @var boolean
	 */
	private $notification = false;
	/**
	 * Proxy
	 *
	 * @var unknown_type
	 */
	private $proxy = null;
	/**
	 * Proxy user:pwd
	 *
	 * @var unknown_type
	 */
	private $proxy_login = null;
	/**
	 * Takes the connection parameters
	 *
	 * @param string $url
	 * @param boolean $debug
	 */
	public function __construct($url, $proxy = array(), $debug = false) {
		// server URL
		$this->url = $url;
		// proxy
		if(!empty($proxy['proxy'])) {
			$this->proxy 		= $proxy['proxy']; 
			
			if(!empty($proxy['proxy_login'])) {
				$this->proxy_login 	= $proxy['proxy_login']; 
			}
		}
		// debug state
		empty($debug) ? $this->debug = false : $this->debug = true;
		// message id
		$this->id = 1;
	}
	
	/**
	 * Sets the notification state of the object. In this state, notifications are performed, instead of requests.
	 *
	 * @param boolean $notification
	 */
	public function setRPCNotification($notification) {
		empty($notification) ?
							$this->notification = false
							:
							$this->notification = true;
	}
	
	/**
	 * Performs a jsonRCP request and gets the results as an array
	 *
	 * @param string $method
	 * @param array $params
	 * @return array
	 */
	public function __call($method,$params) {
		
		// check
		if (!is_scalar($method)) {
			if($this->debug)
				throw new Exception('Method name has no scalar value');
		}
		
		// check
		if (is_array($params)) {
			// no keys
			$params = array_values($params);
		} else {
			if($this->debug)
				throw new Exception('Params must be given as array');
		}
		
		// sets notification or request task
		if ($this->notification) {
			$currentId = NULL;
		} else {
			$currentId = $this->id;
		}
		
		// prepares the request
		$request = array(
						'method' => $method,
						'params' => $params,
						'id' => $currentId
						);
		$request = json_encode($request);
		$this->debug && $this->debug.='***** Request *****'."\n".$request."\n".'***** End Of request *****'."\n\n";
		
		if(ini_get('allow_url_fopen')) {			
			$arr_http = array (
								'method'  => 'POST',							
								'content' => $request,							
								'request_fulluri' => true,
								'header' => array('Content-type: application/json')	
								);
			//of any proxy is set					
			if(!empty($this->proxy)) {			
				$arr_http['proxy'] = $this->proxy;
				//proxy authentication:
				if(!empty($this->proxy_login)) {				
					array_push($arr_http['header'], 'Proxy-Authorization: Basic '. base64_encode($this->proxy_login));							
				}
			}
			// performs the HTTP POST
			$opts = array ('http' => $arr_http);		
			$context  = stream_context_create($opts);
			if ($fp = fopen($this->url, 'r', false, $context)) {
				$response = '';
				while($row = fgets($fp)) {
					$response.= trim($row)."\n";
				}				
				$this->debug && $this->debug.='***** Server response *****'."\n".$response.'***** End of server response *****'."\n";
				$response = json_decode($response,true);
			} else {
				if($this->debug)
					throw new Exception('Unable to connect to '.$this->url. ' through fopen');
			}
		}else {
			//use cURL
			$headers = array('Content-type: application/json', 'Content-Length: '. strlen($request));
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array());
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);		
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);// new			
			curl_setopt($ch, CURLOPT_REFERER, get_option('siteurl'));
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);	
			//of any proxy is set
			if(!empty($this->proxy)) {	
				curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
				if(!empty($this->proxy_login)) {
					curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxy_login);
				}
			}
			$response = curl_exec($ch);
			$this->debug && $this->debug.='***** CURL Server response *****'."\n".$response.'***** End of server response *****'."\n";
			if(curl_error()) {
				if($this->debug)
					throw new Exception('curl Error:' .curl_errno(). ':'.curl_error() );
			}else {			
				$response = json_decode($response, true);
			}			
			curl_close($ch);
		}
		
		// debug output
		if ($this->debug) {			
			echo nl2br($this->debug);			
		}
		
		// final checks and return
		if (!$this->notification) {
			// check
			if ($response['id'] != $currentId) {
				if($this->debug)
					throw new Exception('Incorrect response id (request id: '.$currentId.', response id: '.$response['id'].')');
			}
			if (!is_null($response['error'])) {
				if($this->debug)
					throw new Exception('Request error: '.$response['error']);
			}
			
			return $response['result'];
			
		} else {
			return true;
		}
	}
}
// core/libs/utilities/mgm_jsonrpc_client.php