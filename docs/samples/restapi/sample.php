<?php
// form
include('form.php');

// set case to test
if($_POST){
	// include
	require('class/mgm_rest_client.php');
	// get client
	$client = new mgm_rest_client();
	// set api key
	$client->set_api_key($_POST['api_key']);
	// set resource base_url
	$client->set_resource_baseurl($_POST['resource_baseurl']);	
	// case 
	if($test_case = $_POST['test_case']){
		include('test_cases/test_case_'.$test_case.'.php');
	}
}	
?>