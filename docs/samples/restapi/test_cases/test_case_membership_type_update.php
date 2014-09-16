<?php
	// TEST CASE: UPDATE MEMBERSHIP TYPE ---------------------------------------------------------
	// head
	echo '<h1>TEST CASE 3: UPDATE MEMBERSHIP TYPE</h1><hr>';
	// set post data
	$post =  array('code'=>'silver','name'=>'Silver2', 'login_redirect'=>'http://mysite.com/silverpagein','logout_redirect'=>'mysite.com/silverpageout');
	// post
	$response = $client->post('membership_types/update', $post);
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