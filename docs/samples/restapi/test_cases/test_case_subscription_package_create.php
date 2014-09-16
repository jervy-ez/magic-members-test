<?php
	// TEST CASE 9: CREATE SUBSCRIPTION PACKAGE---------------------------------------------------------
	// head
	echo '<h1>TEST CASE 9: CREATE SUBSCRIPTION PACKAGE</h1><hr>';
	// set post data
	$post =  array('membership_type'=>'premium', 'pack[cost]'=>'3.50', 'pack[duration]'=>1, 'pack[duration_type]'=>'m');
	// post
	$response = $client->post('subscription_packages/create', $post);
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