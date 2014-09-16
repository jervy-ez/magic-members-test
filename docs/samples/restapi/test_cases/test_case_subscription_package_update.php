<?php
	// TEST CASE 10: UPDATE SUBSCRIPTION PACKAGE---------------------------------------------------------
	// head
	echo '<h1>TEST CASE 10: UPDATE SUBSCRIPTION PACKAGE</h1><hr>';
	// set post data
	$post =  array('id'=>'11', 'pack[trial_on]'=>1, 'pack[trial_cost]'=>'1.00', 'pack[cost]'=>'4.50', 'pack[duration]'=>2, 'pack[duration_type]'=>'m');
	// post
	$response = $client->post('subscription_packages/update', $post);
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