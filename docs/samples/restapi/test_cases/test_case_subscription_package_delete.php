<?php
	// TEST CASE: DELETE SUBSCRIPTION PACKAGE---------------------------------------------------------	
	// head
	echo '<h1>TEST CASE: DELETE SUBSCRIPTION PACKAGE</h1><hr>';
	// set delete data
	$delete =  array('id'=>5);
	// post
	$response = $client->delete('subscription_packages/delete', $delete);
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