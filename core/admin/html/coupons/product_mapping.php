<?php
// product mapping
if($payment_modules = mgm_get_class('system')->get_active_modules('payment')): 
	foreach($payment_modules as $payment_module) :
		$module = mgm_get_module($payment_module); 
		if($module->has_product_map()):
			if(isset($data['coupon'])):
				echo $module->settings_coupon(json_decode($data['coupon']->product, true)); 
			else:
				echo $module->settings_coupon(); 
			endif;			
		endif;
	endforeach; 
endif;
?>
