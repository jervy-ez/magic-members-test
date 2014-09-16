<?php
/** 
 * Objects merge/update
 * Add worldpay gateway_successpage and gateway_failedpage settings
 */ 
 // read  
$worldpay = mgm_get_module('worldpay','payment');

if(isset($worldpay->setting['gateway_successpage'])) {
	$worldpay->setting['gateway_successpage'] 	= '<html>
<head>
<meta http-equiv="refresh" content="<WPDISPLAY ITEM=MC_redirectin>;url=<WPDISPLAY ITEM=MC_success>" />
<title>Thank you for your payment</title>
</head>
<WPDISPLAY FILE=header.html DEFAULT="<body bgcolor=#ffffff>">
<h1><WPDISPLAY ITEM=MC_sitename></h1>
<WPDISPLAY ITEM=name>, thank you for your payment of
<WPDISPLAY ITEM=amountString> for
<WPDISPLAY ITEM=desc>
<div>Click <a href="<WPDISPLAY ITEM=MC_success>"><strong>here</strong></a> to return to the site if you are not redirected within <WPDISPLAY ITEM=MC_redirectin> seconds</div>
<WPDISPLAY ITEM=banner>
<WPDISPLAY FILE=footer.html DEFAULT="</body>">
</html>';	
	
	$worldpay->save();
}
 // end file