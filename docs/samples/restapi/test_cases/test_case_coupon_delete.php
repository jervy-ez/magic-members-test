<?php
	// TEST CASE: DELETE COUPON---------------------------------------------------------
	// head
	echo '<h1>TEST CASE: DELETE COUPON</h1><hr>';
	// set delete data
	$delete =  array('id'=>1);
	// post
	$response = $client->delete('coupons/delete', $delete);
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