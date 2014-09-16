<!--payments-->
<div id="wrap-admin-payments" class="content-div">
	<ul class="tabs">		
		<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payments&method=payment_modules"><span class="pngfix"><?php _e('Payment Modules','mgm')?></span></a></li>			
		<?php foreach($data['payment_modules'] as $payment_module):	
				if($module = mgm_is_valid_module($payment_module, 'payment', 'object') ): if($module->is_enabled()): ?>	
		<li><a href="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payments&method=module_settings&module=<?php echo $module->code?>"><span class="pngfix"><?php echo sprintf(__('%s', 'mgm'), $module->name)?></span></a></li>
		<?php endif; endif; endforeach;?>					
	</ul>										
</div>
