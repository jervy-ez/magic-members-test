<fieldset class="autoresponderlist" id="mgm_autoresponder_<?php echo $data['pack_ctr'] ?>"><legend><?php echo sprintf(__('Package #%d','mgm'),$data['pack_ctr'] )?></legend>	
	<input type="hidden" name="packs[<?php echo ($data['pack_ctr']-1) ?>][id]" value="<?php echo $data['pack']['id']?>"/>
	
	<div class="table">
		<div class="row ">
			<div class="cell ">	
				<div class="subscription-heading"><?php _e('Basic Settings','mgm') ?></div>
			</div>
		</div>
		<div class="row ">
			<div class="cell width125px textalignleft">	
				<?php _e('Membership Type','mgm')?>
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][membership_type]" style="width: 150px;">
					<option value="<?php echo $data['pack']['membership_type'] ?>"><?php echo mgm_get_class('membership_types')->get_type_name($data['pack']['membership_type']) ?></option>
				</select>
			</div>
		</div>

		<div class="row ">
			<div class="cell width125px">	
				<?php _e('Duration','mgm')?>
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<input type="text" size="5" name="packs[<?php echo ($data['pack_ctr']-1) ?>][duration]" value="<?php echo esc_html($data['pack']['duration']) ?>" maxlength="10"/>
				<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][duration_type]" style="width:100px">
				<?php foreach (mgm_get_class('subscription_packs')->duration_str as $value=>$text):
						  $selected = ($value == $data['pack']['duration_type'] ? 'selected="selected"':'');
						  echo '<option value="'. $value .'" '. $selected .'>'. $text .'</option>';
					  endforeach;?>
				</select>
			</div>
		</div>
		<div class="row ">
			<div class="cell width125px">	
				<?php _e('Cost','mgm')?>
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<input type="text" size="10" name="packs[<?php echo ($data['pack_ctr']-1) ?>][cost]" value="<?php echo esc_html($data['pack']['cost']) ?>" maxlength="15"/>
			</div>
		</div>

		<div class="row ">
			<div class="cell width125px">	
				<?php _e('Billing ','mgm')?>
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][num_cycles]" style="width:80px">		
				<?php foreach (range(0, 99) as $i) :
						$name = (!$i ? __('Ongoing', 'mgm') : $i);
						echo '<option value="' . $i . '" ' . ($data['pack']['num_cycles'] == $i ? 'selected="selected"':'') . '>' . $name . '</option>';
				endforeach;?>
				</select>
			</div>
		</div>

		<div class="row ">
			<div class="cell width125px">	
				<?php _e('Role','mgm')?>
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][role]" style="width: 120px;">
				<?php						
				foreach ($data['roles'] as $role=>$name) {
					$selected = '';
					if ($data['pack']['role'] == $role) {
						$selected = 'selected="selected"';
					}
					echo '<option value="' . $role . '" ' . $selected . '>' . $name . '</option>';
				}			
				?>
				</select>	
			</div>
		</div>
		<div class="row ">
			<div class="cell width125px">	
				<?php _e('Default','mgm')?>
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][default]" style="width:60px">
					<option value="1" <?php echo ($data['pack']['default'] ? 'selected="selected"':'') ?>><?php _e('Yes','mgm')?></option>
					<option value="0" <?php echo (!$data['pack']['default'] ? 'selected="selected"':'') ?>><?php _e('No','mgm')?></option>
				</select>
			</div>
		</div>
		<div class="row ">
			<div class="cell width125px">	
				<?php _e('Description','mgm')?>
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<textarea cols="50" rows="5" name="packs[<?php echo ($data['pack_ctr']-1) ?>][description]"><?php echo esc_html(stripslashes($data['pack']['description'])) ?></textarea>
			</div>
		</div>
		
		<div class="row ">
			<div class="cell width125px">	
				<?php _e('Hide Private Content Prior to Join','mgm')?>
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][hide_old_content]" style="width:60px">
					<option value="1" <?php echo ((int)$data['pack']['hide_old_content'] ? 'selected="selected"':'') ?>><?php _e('Yes','mgm')?></option>
					<option value="0" <?php echo (!(int)$data['pack']['hide_old_content'] ? 'selected="selected"':'') ?>><?php _e('No','mgm')?></option>
				</select>  
				<div class="tips"><?php _e('If selected Yes, members can access only the content which are published after their registration date.','mgm')?></div>
			</div>
		</div>
		<div class="row ">
			<div class="cell ">	
				<div class="subscription-heading"><?php _e('Display Settings','mgm') ?></div>
			</div>
		</div>
		
		<div class="row ">
			<div class="cell width125px">	
				<?php _e('Active','mgm')?>
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][active]" style="width:60px">
					<option value="1" <?php echo ($data['pack']['active'] ? 'selected="selected"':'') ?>><?php _e('Yes','mgm')?></option>
					<option value="0" <?php echo (!$data['pack']['active'] ? 'selected="selected"':'') ?>><?php _e('No','mgm')?></option>
				</select>
			</div>
		</div>
		
		<div class="row ">
			<div class="cell width125px">	
				<?php _e('Sort Order','mgm')?>
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<input type="text" size="10" name="packs[<?php echo ($data['pack_ctr']-1) ?>][sort]" value="<?php echo esc_html($data['pack']['sort']) ?>" maxlength="10"/>
			</div>
		</div>
		<?php if(!in_array($data['pack']['membership_type'], array('trial','free'))):
				 if ($data['supports_trial'] === true):?>
		<div class="row ">
			<div class="cell ">	
				<div class="subscription-heading"><?php _e('Trial Settings','mgm') ?></div>
			</div>
		</div>
		<div class="row ">
			<div class="cell width125px">	
				<?php _e('Use Trial','mgm')?>
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][trial_on]" onchange="mgm_toggle_trial(this)" style="width:60px">
					<option value="1" <?php echo ((int)$data['pack']['trial_on'] ? 'selected="selected"':'') ?>><?php _e('Yes','mgm')?></option>
					<option value="0" <?php echo (!(int)$data['pack']['trial_on'] ? 'selected="selected"':'') ?>><?php _e('No','mgm')?></option>
				</select>
			
			</div>
		</div>
		
		<div class="row  pack_trial_<?php echo ($data['pack_ctr']-1) . ' ' . (!(int)$data['pack']['trial_on'] ? 'displaynone' : ''); ?>">		
			<div class="cell width125px">	
				<?php _e('Trial Duration','mgm')?>			
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<input size="5" type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][trial_duration]" value="<?php echo (int)$data['pack']['trial_duration'] ?>" maxlength="10"/>
				<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][trial_duration_type]" style="width:100px">
				<?php
				foreach (mgm_get_class('subscription_packs')->duration_str as $value=>$text) {
					$selected = ($value == $data['pack']['trial_duration_type'] ? 'selected="selected"':'');
					echo '<option value="'. $value .'" '. $selected .'>'. $text .'</option>';
				}?>	
				</select>
			</div>
		</div>
		
		<div class="row pack_trial_<?php echo ($data['pack_ctr']-1) . ' ' . (!(int)$data['pack']['trial_on'] ? 'displaynone' : ''); ?>">
			<div class="cell width125px">	
				<?php _e('Trial Cost','mgm')?>			
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<input size="5" type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][trial_cost]" value="<?php echo esc_html($data['pack']['trial_cost']) ?>" maxlength="10"/>
			</div>
		</div>
				
		<div class="row pack_trial_<?php echo ($data['pack_ctr']-1) . ' ' . (!(int)$data['pack']['trial_on'] ? 'displaynone' : '');?>">
			<div class="cell width125px">	
				<?php _e('Trial Occurrences','mgm')?>
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][trial_num_cycles]" style="width:80px">		
				<?php 
				foreach (range(1, 99) as $i) :
					echo '<option value="' . $i . '" ' . ($data['pack']['trial_num_cycles'] == $i ? 'selected="selected"':'') . '>' . $i . '</option>';
				endforeach;?>
				</select>
				<div class="tips" style="width:520px"><?php _e('Please use "Trial Occurrences" to configure number of times "Subscription Package Duration" to be treated as "Trial Period". Authorize.Net Payment Gateway requires you to set up "Trial Period" same as "Subscription Package Duration" .', 'mgm')?></div>
			
			</div>
		</div>		
		<?php 
			endif; // end trial settings
		
			// post purchase settings
			if($data['payment_modules']):		
				foreach($data['payment_modules'] as $payment_module) :
					echo mgm_get_module($payment_module)->settings_subscription_package($data);
				endforeach;		
			endif;?>		
		
		<div class="row ">
			<div class="cell ">	
				<div class="subscription-heading"><?php _e('Module Settings','mgm') ?></div>
			</div>
		</div>
		<div class="row ">
			<div class="cell width125px">	
				<?php _e('Use Module','mgm')?>
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<?php if($data['payment_modules']):		
						foreach($data['payment_modules'] as $payment_module) :
							if(!in_array($payment_module, array('mgm_trial','mgm_free'))):
						?>
						<input type="checkbox" name="packs[<?php echo ($data['pack_ctr']-1) ?>][modules][]" value="<?php echo $payment_module?>" <?php echo (in_array($payment_module,(array)$data['pack']['modules']))?'checked':''?> /> <?php echo mgm_get_module($payment_module)->name?>
				<?php	
							endif;
						endforeach;		
				endif;?>
			</div>
		</div>
		<?php elseif($data['pack']['membership_type'] == 'trial'):?>
		<div class="row ">
			<div class="cell ">	
				<div class="subscription-heading"><?php _e('Module Settings','mgm') ?></div>
			</div>
		</div>
		<div class="row ">
			<div class="cell width125px">	
				<?php _e('Use Module','mgm')?>
			</div>
			<div class="cell width5px">:</div>
			<div class="cell textalignleft">	
				<?php if($data['payment_modules']):		
						foreach($data['payment_modules'] as $payment_module) :
							if(!in_array($payment_module, array('mgm_free'))):
						?>
						<input type="checkbox" name="packs[<?php echo ($data['pack_ctr']-1) ?>][modules][]" value="<?php echo $payment_module?>" <?php echo (in_array($payment_module,(array)$data['pack']['modules']))?'checked':''?> /> <?php echo mgm_get_module($payment_module)->name?>
				<?php	
							endif;
						endforeach;
				endif;?>
			</div>
		</div>
		<?php endif; // end type free/trial check?>	
		<div class="row ">
			<div class="cell ">	
				<div class="subscription-heading"><?php _e('Package Register URLs/Tag','mgm') ?></div>
			</div>
		</div>
		<?php 
			// package					
			$package     = $data['pack']['membership_type'].'#'.$data['pack']['id'];	
			$package_enc = base64_encode($package);							
		?>
		<div class="row ">
			<div class="cell ">	
				<div class="padding10px">
					<div class="table widefatDiv">
						<div class="row brBottom">
							<div class="cell width125px">	
								<?php _e('Custom URL','mgm')?>
							</div>
							<div class="cell width5px">:</div>
							<div class="cell textalignleft">
								<?php echo add_query_arg(array('method'=>'register','package'=>$package_enc), mgm_home_url('purchase_subscription'));?>	
							</div>
						</div>
						<div class="row brBottom">
							<div class="cell width125px">	
								<?php _e('Wordpress URL','mgm')?>
							</div>
							<div class="cell width5px">:</div>
							<div class="cell textalignleft">
								<?php echo add_query_arg(array('package'=>$package_enc), site_url('wp-login.php?action=register', 'login'));?>
							</div>
						</div>
						<div class="row brBottom">
							<div class="cell width125px">	
								<?php _e('Tag','mgm')?>
							</div>
							<div class="cell width5px">:</div>
							<div class="cell textalignleft">
								<?php echo sprintf('[user_register package=%s]',$package);?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row ">
			<div class="cell ">	
				<p>					
					<a class="button" href="javascript:delete_pack('<?php echo $data['pack']['membership_type'] ?>','<?php echo $data['pack_ctr']?>')"><?php _e('Delete Package','mgm') ?></a>
				</p>
			</div>
		</div>
		
	</div>	
	
</fieldset>
