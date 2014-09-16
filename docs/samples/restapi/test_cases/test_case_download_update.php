<?php
	// TEST CASE: UPDATE DOWNLOAD---------------------------------------------------------
	// head
	echo '<h1>TEST CASE: UPDATE DOWNLOAD</h1><hr>';
	// set post data
	$post =  array('id'           => 1,  
				   'title'        => 'Test Download 7 Updated',				   
	               //'file_name'  => '@'.dirname(__FILE__).'/files/sample.txt',
	               //'file_url'   => 'https://s3.amazonaws.com/magic_products/wordpress_guide.pdf',	
				   'members_only' => 'N', 			   
				   'user_id'      => 2,
				   //'code'       => uniqid(),
				   'expire_dt'    => '2012-07-31',
				   'posts[]'      => array(0)
				  );
	// post
	$response = $client->post('downloads/update', $post);
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