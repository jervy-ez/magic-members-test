<?php
	// TEST CASE: CREATE COUPON---------------------------------------------------------
	// head
	echo '<h1>TEST CASE: CREATE COUPON</h1><hr>';
	// set post data
	$post =  array('name'        => 'Thurday Dhamaka2',		
	               'value'       => '40.50',
	               'description' => 'Special Thursday Coupon1',					   
				   'expire_dt'   => '2012-07-31');
	// post
	$response = $client->post('coupons/create', $post);
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