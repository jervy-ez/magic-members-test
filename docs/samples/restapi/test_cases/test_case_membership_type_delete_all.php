<?php
	// TEST CASE: DELETE ALL MEMBERSHIP TYPE ---------------------------------------------------------	
	// head
	echo '<h1>TEST CASE: DELETE ALL MEMBERSHIP TYPE</h1><hr>';
	// post
	$response = $client->delete('membership_types/delete_all', $delete);
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