<?php
	// TEST CASE 11: DELETE ALL SUBSCRIPTION PACKAGE---------------------------------------------------------	
	// head
	echo '<h1>TEST CASE: DELETE ALL SUBSCRIPTION PACKAGE</h1><hr>';
	// post
	$response = $client->delete('subscription_packages/delete_all');
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