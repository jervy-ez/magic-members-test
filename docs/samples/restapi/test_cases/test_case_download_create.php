<?php
	// TEST CASE: CREATE DOWNLOAD---------------------------------------------------------
	// head
	echo '<h1>TEST CASE: CREATE DOWNLOAD</h1><hr>';
	// set post data
	$post =  array('title'        => 'Test Download 7',				   
	               //'file_name'    => '@'.dirname(__FILE__).'/files/sample.txt',
	               'file_url'     => 'https://s3.amazonaws.com/magic_products/wordpress_guide.pdf',	
				   'members_only' => 'Y', 			   
				   'user_id'      => 1,
				   //'code'         => uniqid(),
				   'expire_dt'    => '2012-06-31');
	// post
	$response = $client->post('downloads/create', $post);
	// simplexml
	$xml = @simplexml_load_string($response);
	// endpoint
	echo sprintf('Endpoint: %s<br>', $client->get_endpoint());
	// dump
	if($xml){			
		echo sprintf('<pre>%s</pre>', print_r($xml, 1));
	}else{
		echo 'Response: <hr>' . $response;
	}	
?>