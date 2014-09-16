<?php
	// TEST CASE: CREATE MEMBERSHIP TYPE ---------------------------------------------------------
	// head
	echo '<h1>TEST CASE 2: CREATE MEMBERSHIP TYPE</h1><hr>';
	// set post data
	$post =  array('name'=>'Bronze', 'login_redirect'=>'http://bronzein.com','logout_redirect'=>'http://bronzeout.com');
	// post
	$response = $client->post('membership_types/create', $post);
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