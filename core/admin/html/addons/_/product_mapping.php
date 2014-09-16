<?php
// product mapping
if($payment_modules = mgm_get_class('system')->get_active_modules('payment')): 
	foreach($payment_modules as $payment_module) :
		$module = mgm_get_module($payment_module); 
		if($module->has_product_map()):
			if(isset($data['addon'])):
				echo $module->settings_addon(json_decode($data['addon']->product, true)); 
			else:
				echo $module->settings_addon(); 
			endif;			
		endif;
	endforeach; 
endif;
?>
