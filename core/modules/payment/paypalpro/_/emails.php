// curl post
	// @deprecated
	/*
	function _curl_post($url, $post, $http_header=array(), $set_response = true){		
		if($set_response)
			$this->response = array();
		// create curl post		
		$ch = curl_init();		
		curl_setopt($ch, CURLOPT_URL, $url); 		
		// when set
		if(is_array($http_header)){	
			curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);	
		}			
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
		curl_setopt($ch, CURLOPT_NOPROGRESS, 1); 
		curl_setopt($ch, CURLOPT_VERBOSE, 1); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,0); 
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 180); 
		curl_setopt($ch, CURLOPT_USERAGENT, 'Magic Members Membership Software'); 
		curl_setopt($ch, CURLOPT_REFERER, get_option('siteurl')); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// buffer
		$buffer = curl_exec($ch);
		curl_close($ch);		
		// parse response
		if($set_response) parse_str($buffer, $this->response);		
		// return
		return $buffer;	
	}*/