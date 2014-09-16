	<p class="postpurhase-heading"><?php _e('Payment Settings','mgm')?>:</p>	
	<p class="fontweightbold"><?php _e('Allow Modules','mgm')?>:</p>
	<?php if( $payment_modules = mgm_get_class('system')->get_active_modules('payment') ): 
	$modue_i = 0; foreach($payment_modules as $payment_module) : if( ! in_array($payment_module, array('mgm_trial'))):?>
	<input type="checkbox" name="modules[<?php echo $modue_i; ?>]" value="<?php echo $payment_module?>" <?php echo ( isset($data['postpack']) && in_array($payment_module,$data['postpack']->modules))?'checked':''?>/> 
	<label><?php echo mgm_get_module($payment_module)->name?></label>
	<?php $modue_i++; endif; endforeach; else:?>				
	<b class="mgm_color_red"><?php _e('No payment module is active.','mgm')?></b>		
	<?php endif;?>
	<?php 
	// product id mapping 
	if($payment_modules /*= mgm_get_class('system')->get_active_modules('payment')*/ ): 
		foreach($payment_modules as $payment_module) :
			$module = mgm_get_module($payment_module); 
			if($module->has_product_map()):
				// edit
				$postpack = isset($data['postpack']) ? $data['postpack'] : null;
				// print
				echo $module->settings_postpack_purchase( $postpack );
			endif;
		endforeach; 
	endif;
	
	/*// product id mapping 
	if($payment_modules = mgm_get_class('system')->get_active_modules('payment')): 
		foreach($payment_modules as $payment_module) :
			$module = mgm_get_module($payment_module); 
			if($module->has_product_map()):
				
			endif;
		endforeach; 
	endif;

	if():
					//echo $module->settings_postpack_purchase(json_decode($data['postpack']->product, true));
				else:
					//echo $module->settings_postpack_purchase();
				endif;*/
	?>