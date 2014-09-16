<?php
	// TEST CASE 6: DELETE MEMBERSHIP TYPE ---------------------------------------------------------	
	// head
	echo '<h1>TEST CASE 6: DELETE MEMBERSHIP TYPE</h1><hr>';
	// set delete data
	$delete =  array('code'=>'silver');
	// post
	$response = $client->delete('membership_types/delete', $delete);
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