<?php
	// TEST CASE: DELETE DOWNLOAD---------------------------------------------------------
	// head
	echo '<h1>TEST CASE: DELETE DOWNLOAD</h1><hr>';
	// set delete data
	$delete =  array('id'=>2);
	// post
	$response = $client->delete('downloads/delete', $delete);
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