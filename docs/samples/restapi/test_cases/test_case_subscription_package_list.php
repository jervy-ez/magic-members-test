<?php
	// TEST CASE 8: LIST SUBSCRIPTION PACKAGES ---------------------------------------------------------
	// head
	echo '<h1>TEST CASE 8: LIST SUBSCRIPTION PACKAGES</h1><hr>';
	// post
	$response = $client->get('subscription_packages');
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