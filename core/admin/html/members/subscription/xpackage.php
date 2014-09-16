<!-- old -->
	<div class="row">
		<div class="cell width125px textalignleft">	
			<?php _e('Membership Type','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][membership_type]" id="packs_membership_type_<?php echo ($data['pack_ctr']-1) ?>" class="width250px">
				<option value="<?php echo $data['pack']['membership_type'] ?>"><?php echo mgm_stripslashes_deep(mgm_get_class('membership_types')->get_type_name($data['pack']['membership_type'])) ?></option>
			</select>
		</div>
	</div>
	<div class="row ">
		<div class="cell width125px">	
			<?php _e('Duration','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][duration]" value="<?php echo esc_html($data['pack']['duration']) ?>" size="5" maxlength="10"/>
			<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][duration_type]" class="width120px" >
			<?php foreach ($data['obj_sp']->get_duration_types() as $value=>$text):
					  $selected = ($value == $data['pack']['duration_type'] ? 'selected="selected"' : '');
					  echo '<option value="'. $value .'" '. $selected .'>'. $text .'</option>';
				  endforeach;?>
			</select>
			<span id="packs_<?php echo ($data['pack_ctr']-1) ?>_duration_range_start_dt_zone" class="displaynone">
				<?php $date_fmt = mgm_get_date_format('date_format_short');?>
				<?php _e('Start Date', 'mgm')?>: <input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][duration_range_start_dt]" value="<?php echo esc_html(date($date_fmt,strtotime($data['pack']['duration_range_start_dt']))) ?>" size="10" maxlength="12" class="date"/>
				<?php _e('End Date', 'mgm')?>: <input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][duration_range_end_dt]" value="<?php echo esc_html(date($date_fmt,strtotime($data['pack']['duration_range_end_dt']))) ?>" size="10" maxlength="12" class="date"/>
			</span>
		</div>
	</div>

	<div class="row ">
		<div class="cell width125px">	
			<?php _e('Cost','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][cost]" id="packs_cost_<?php echo ($data['pack_ctr']-1) ?>" value="<?php echo $data['pack']['cost'] ?>" size="10" maxlength="15"/>
			<?php echo $data['pack']['currency'];?>
		</div>
	</div>

	<div class="row ">
		<div class="cell width125px">	
			<?php _e('Billing Cycle','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<span id="packs_<?php echo ($data['pack_ctr']-1) ?>_num_cycles_0">
				<input type="radio" name="packs[<?php echo ($data['pack_ctr']-1) ?>][num_cycles]" value="0" <?php echo ($data['pack']['num_cycles'] == 0 ? 'checked="checked"':'')?>/> <?php _e('Ongoing','mgm')?>
			</span>
			<span id="packs_<?php echo ($data['pack_ctr']-1) ?>_num_cycles_1">	
				<input type="radio" name="packs[<?php echo ($data['pack_ctr']-1) ?>][num_cycles]" value="1" <?php echo ($data['pack']['num_cycles'] == 1 ? 'checked="checked"':'')?>/> <?php _e('Onetime','mgm')?>
			</span>
			<span id="packs_<?php echo ($data['pack_ctr']-1) ?>_num_cycles_2">	
				<input type="radio" name="packs[<?php echo ($data['pack_ctr']-1) ?>][num_cycles]" value="2" <?php echo ($data['pack']['num_cycles'] > 1 ? 'checked="checked"':'')?>/> <?php _e('Limited','mgm')?>
				<input type="text"  name="packs[<?php echo ($data['pack_ctr']-1) ?>][num_cycles_limited]" value="<?php echo ($data['pack']['num_cycles'] > 1 ? $data['pack']['num_cycles'] : 99)?>" size="10" maxlength="10" <?php echo ($data['pack']['num_cycles'] > 1 ? '' : 'disabled="disabled"')?> />
			</span>	
		</div>
	</div>

	<div class="row ">
		<div class="cell width125px">	
			<?php _e('Role','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][role]" class="width250px">
			<?php foreach ($data['roles'] as $role=>$name): 
				$selected = '';
				if ($data['pack']['role'] == $role):
					$selected = 'selected="selected"';
				endif;
				echo '<option value="' . $role . '" ' . $selected . '>' . $name . '</option>';
			endforeach;?>
			</select>	
		</div>
	</div>
	<div class="row ">
		<div class="cell width125px">	
			<?php _e('Default (Pre-Select)','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][default]" class="width60px">
				<option value="1" <?php echo ($data['pack']['default'] ? 'selected="selected"':'') ?>><?php _e('Yes','mgm')?></option>
				<option value="0" <?php echo (!$data['pack']['default'] ? 'selected="selected"':'') ?>><?php _e('No','mgm')?></option>
			</select>
			<div class="tips width95"><?php _e('If selected Yes, register page will pre-select this pack.','mgm')?></div>
		</div>
	</div>
	<div class="row ">
		<div class="cell width125px">	
			<?php _e('Default (Assign)','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][default_assign]" class="width60px">
				<option value="1" <?php echo ($data['pack']['default_assign'] ? 'selected="selected"':'') ?>><?php _e('Yes','mgm')?></option>
				<option value="0" <?php echo (!$data['pack']['default_assign'] ? 'selected="selected"':'') ?>><?php _e('No','mgm')?></option>
			</select>
			<div class="tips width95"><?php _e('If selected Yes, admin created users will be assigned this pack by default.','mgm')?></div>
		</div>
	</div>
	<div class="row ">
		<div class="cell width125px">	
			<?php _e('Description','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<textarea cols="65" rows="5" name="packs[<?php echo ($data['pack_ctr']-1) ?>][description]"><?php echo esc_html(stripslashes($data['pack']['description'])) ?></textarea>		
		</div>
	</div>
	<div class="row ">
		<div class="cell width125px">	
			<?php _e('Hide Private Content Prior to Join','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][hide_old_content]" class="width60px">
				<option value="1" <?php echo ((int)$data['pack']['hide_old_content'] ? 'selected="selected"':'') ?>><?php _e('Yes','mgm')?></option>
				<option value="0" <?php echo (!(int)$data['pack']['hide_old_content'] ? 'selected="selected"':'') ?>><?php _e('No','mgm')?></option>
			</select>  
			<div class="tips width95"><?php _e('If selected Yes, members can access only the content which are published after their registration date.','mgm')?></div>
		</div>
	</div>
	<div class="row ">
		<div class="cell width125px">	
			<?php _e('When expired/cancelled, move members to','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][move_members_pack]" class="width250px">
				<option value=""><?php _e('None','mgm')?></option>
				<?php foreach($data['packages'] as $pack):
					$selected = (isset($data['pack']['move_members_pack']) && $data['pack']['move_members_pack'] == $pack['id']) ?  'selected="selected"' : '';
					echo '<option value="'.$pack['id'].'" '.$selected.' >'.$pack['label'].'</option>';
				endforeach;?>	
			</select>  
			<div class="tips width95"><?php _e('If selected, member\'s will be assigned with the selected pack when expired/cancelled.','mgm')?></div>
		</div>
	</div>
	
	<div class="row ">
		<div class="cell ">	
			<div class="subscription-heading"><?php _e('Display Settings','mgm') ?></div>
		</div>
	</div>
	
	<div class="row ">
		<div class="cell width125px">	
			<?php _e('Active On','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<?php foreach ($data['obj_sp']->get_active_options() as $option => $val):
				$checked = ($data['pack']['active'][$option]) ? ' checked="checked" ' : '';?>
				<input type="checkbox" name="packs[<?php echo ($data['pack_ctr']-1) ?>][active][<?php echo $option ?>]" value="1" <?php echo $checked; ?>>&nbsp;<?php echo sprintf(__('%s page','mgm'), ucwords($option)) ?>&nbsp;&nbsp
			<?php endforeach;?>
		</div>
	</div>
	<div class="row ">
		<div class="cell width125px">	
			<?php _e('Hidden?','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<input type="checkbox" name="packs[<?php echo ($data['pack_ctr']-1) ?>][hidden]" value="1" <?php echo ($data['pack']['hidden']) ? ' checked="checked" ' : '';?>/>
			<?php _e('Hide on General Registration','mgm');?> 
		</div>
	</div>
	<div class="row ">
		<div class="cell width125px">	
			<?php _e('Sort Order','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][sort]" value="<?php echo esc_html($data['pack']['sort']) ?>" size="10" maxlength="10"/>
			<div class="tips width95"><?php _e('Used to sort Packs on Register/Upgrade/Downgrade Pages.','mgm')?></div>
		</div>
	</div>
	<div class="row ">
		<div class="cell width125px">	
			<?php _e('Preference','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][preference]" value="<?php echo esc_html($data['pack']['preference']) ?>" size="10" maxlength="10"/>
			<div class="tips width95"><?php _e('Packs with Higher Preference will be treated as "Upgrade" and vice versa.','mgm')?></div>
		</div>
	</div>

	<?php if(!in_array($data['pack']['membership_type'], array('trial','free'))): if ($data['supports_trial'] === true):?>
	
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
			<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][trial_on]" onchange="mgm_toggle_trial(this)" class="width60px">
				<option value="1" <?php echo ((int)$data['pack']['trial_on'] ? 'selected="selected"':'') ?>><?php _e('Yes','mgm')?></option>
				<option value="0" <?php echo (!(int)$data['pack']['trial_on'] ? 'selected="selected"':'') ?>><?php _e('No','mgm')?></option>
			</select>
		
		</div>
	</div>
	
	<div class="row pack_trial_<?php echo ($data['pack_ctr']-1) . ' ' . (!(int)$data['pack']['trial_on'] ? 'displaynone' : ''); ?>">
		<div class="cell width125px">	
			<?php _e('Trial Duration','mgm')?>			
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][trial_duration]" value="<?php echo (int)$data['pack']['trial_duration'] ?>" size="5" maxlength="10"/>
			<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][trial_duration_type]" class="width100px">
			<?php foreach ($data['obj_sp']->get_duration_types('date_expr') as $value=>$text):
				$selected = ($value == $data['pack']['trial_duration_type'] ? 'selected="selected"':'');
				echo '<option value="'. $value .'" '. $selected .'>'. $text .'</option>';
			endforeach?>
			</select>
		</div>
	</div>
	
	<div class="row pack_trial_<?php echo ($data['pack_ctr']-1) . ' ' . (!(int)$data['pack']['trial_on'] ? 'displaynone' : ''); ?>">
		<div class="cell width125px">	
			<?php _e('Trial Cost','mgm')?>			
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][trial_cost]" value="<?php echo esc_html($data['pack']['trial_cost']) ?>" size="10" maxlength="15"/>
		</div>
	</div>
	
	<div class="row pack_trial_<?php echo ($data['pack_ctr']-1) . ' ' . (!(int)$data['pack']['trial_on'] ? 'displaynone' : '');?>">
		<div class="cell width125px">	
			<?php _e('Trial Occurrences','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][trial_num_cycles]" value="<?php echo ($data['pack']['trial_num_cycles'])?>" size="10" maxlength="10"/>				
			<div class="tips width95"><?php _e('Please use "Trial Occurrences" to configure number of times "Subscription Package Duration" to be treated as "Trial Period". Authorize.Net Payment Gateway requires you to set up "Trial Period" same as "Subscription Package Duration" .', 'mgm')?></div>
		
		</div>
	</div>
	<?php endif; endif; // end trial settings?>		

	<div class="row ">
		<div class="cell ">	
			<div class="subscription-heading"><?php _e('Payment Settings','mgm') ?></div>
		</div>
	</div>
	
	<div class="row ">
		<div class="cell width125px">	
			<?php _e('Allow Renewal','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">	
			<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][allow_renewal]" class="width60px">
				<option value="1" <?php echo ((int)$data['pack']['allow_renewal'] ? 'selected="selected"':'') ?>><?php _e('Yes','mgm')?></option>
				<option value="0" <?php echo (!(int)$data['pack']['allow_renewal'] ? 'selected="selected"':'') ?>><?php _e('No','mgm')?></option>
			</select>
		</div>
	</div>

	<?php if(!in_array($data['pack']['membership_type'], array('free'))): ?>				
	<div class="row ">
		<div class="cell width125px">	
			<?php _e('Use Modules','mgm')?>
		</div>
		<div class="cell width5px">:</div>
		<div class="cell textalignleft">
			<?php
			if($data['payment_modules']): $modue_i = 0; foreach($data['payment_modules'] as $payment_module) : if(!in_array($payment_module, array('mgm_trial'))):?>
			<input type="checkbox" name="packs[<?php echo ($data['pack_ctr']-1) ?>][modules][<?php echo $modue_i;$modue_i++; ?>]" value="<?php echo $payment_module?>" <?php echo (in_array($payment_module,(array)$data['pack']['modules']))?'checked':''?> /> <?php echo mgm_get_module($payment_module)->name?>
			<?php endif; endforeach; else:?>				
			<b class="mgm_color_red"><?php _e('No payment module is active.','mgm')?></b>		
			<?php endif;?>
		</div>
	</div>
	
	<?php 
	// subscription purchase/product settings
	if($data['payment_modules']): foreach($data['payment_modules'] as $payment_module) : 
		$module = mgm_get_module($payment_module); 
		if($module->has_product_map()):
			echo $module->settings_subscription_package($data);
		endif;
	endforeach; endif;?>
	
	<?php endif; // end payment settings?>		  
	
	
	<div class="row ">
		<div class="cell">	
			<div class="subscription-heading"><?php _e('Package Register URLs/Tag','mgm') ?></div>
		</div>
	</div>

	<?php 
		// package					
		$package     = $data['pack']['membership_type'].'#'.$data['pack']['id'];	
		$package_enc = base64_encode($package);				
	?>
	
	<div class="row ">
		<div class="cell">	
		
			<div class="padding10px">		
			
				<div class="table widefatDiv">
					<div class="row brBottom">
						<div class="cell textalignleft width100px">	
							<?php _e('Custom URL','mgm')?>
						</div>
						<div class="cell width5px">:</div>
						<div class="cell textalignleft">	
							<?php echo mgm_get_custom_url('register',false,array('package'=>$package_enc));?>
						</div>
					</div>
					<div class="row brBottom">
						<div class="cell textalignleft width100px">	
							<?php _e('Wordpress URL','mgm')?>
						</div>
						<div class="cell width5px">:</div>
						<div class="cell textalignleft">	
							<?php echo mgm_get_custom_url('register',true,array('package'=>$package_enc));?>
						</div>
					</div>
					<div class="row brBottom">
						<div class="cell textalignleft width100px">	
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
	
	
	/*// toggle
	jQuery('#mgm_pack_<?php echo $data['pack']['id'] ?>').closest('legend').click(function(){
		if(jQuery('#mgm_pack_<?php echo $data['pack']['id'] ?>_details').hasClass('packdet_hidden')){
			jQuery('#mgm_pack_<?php echo $data['pack']['id'] ?>_details').show('slow', function(){
				jQuery('#mgm_pack_<?php echo $data['pack']['id'] ?>_details').removeClass('packdet_hidden');
			});
		}else{
			jQuery('#mgm_pack_<?php echo $data['pack']['id'] ?>_details').hide('slow', function(){
				jQuery('#mgm_pack_<?php echo $data['pack']['id'] ?>_details').addClass('packdet_hidden');
			});
		}
	});
	jQuery('#mgm_pack_<?php echo $data['pack']['id'] ?>_details').hide();*/