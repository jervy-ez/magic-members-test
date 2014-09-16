<?php
	// TEST CASE: UPDATE COUPON---------------------------------------------------------
	// head
	echo '<h1>TEST CASE: UPDATE COUPON</h1><hr>';
	// set post data
	$post =  array('id'          => 1,  
				   'name'        => 'Thurday Dhamaka1 UPdated',		
	               'value'       => '51'
				  );
	// post
	$response = $client->post('coupons/update', $post);
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