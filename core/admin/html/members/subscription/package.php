<fieldset class="packgroup" id="mgm_pack_<?php echo $data['pack']['id'] ?>">
	<legend class="packdet_open"><?php echo $package_label= sprintf(__('Package #%d','mgm'),$data['pack']['id'])?></legend>	
	<!--<div><?php printf(__('Click on the %s to view the Package Details'), $package_label)?></div>-->
	<div class="packdet_hidden" id="mgm_pack_<?php echo $data['pack']['id'] ?>_details">
		<input type="hidden" name="packs[<?php echo ($data['pack_ctr']-1) ?>][id]" value="<?php echo $data['pack']['id']?>"/>
		
		<div class="table">
			<!-- basic settings -->
			<div class="row">
				<div class="cell">	
					<div class="subscription-heading"><?php _e('Basic Settings','mgm') ?></div>
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">
						<p class="fontweightbold"><?php _e('Membership Type','mgm')?>:</p>
						<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][membership_type]" id="packs_membership_type_<?php echo ($data['pack_ctr']-1) ?>" class="width250px">
							<option value="<?php echo $data['pack']['membership_type'] ?>"><?php echo mgm_stripslashes_deep(mgm_get_class('membership_types')->get_type_name($data['pack']['membership_type'])) ?></option>
						</select>
					</div>
				</div>
			</div>			
			
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Duration','mgm')?>:</p>
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
			</div>
			
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Cost','mgm')?>:</p>
						<input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][cost]" id="packs_cost_<?php echo ($data['pack_ctr']-1) ?>" value="<?php echo $data['pack']['cost'] ?>" size="10" maxlength="15"/>
						<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][currency]" id="<?php echo ($data['pack_ctr']-1) ?>_currency" class="width200px">
							<?php echo mgm_make_combo_options(mgm_get_currencies(),$data['pack']['currency'], MGM_KEY_VALUE)?>
						</select>						
						<?php //echo $data['pack']['currency'];?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Billing Cycle','mgm')?>:</p>
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
			</div>
			
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Role','mgm')?>:</p>
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
			</div>
			
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Default (Pre-Select)','mgm')?>:</p>
						<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][default]" class="width60px">
							<option value="1" <?php echo ($data['pack']['default'] ? 'selected="selected"':'') ?>><?php _e('Yes','mgm')?></option>
							<option value="0" <?php echo (!$data['pack']['default'] ? 'selected="selected"':'') ?>><?php _e('No','mgm')?></option>
						</select>
						<div class="tips width95"><?php _e('If selected Yes, register page will pre-select this pack.','mgm')?></div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Default (Assign)','mgm')?>:</p>
						<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][default_assign]" class="width60px">
							<option value="1" <?php echo ($data['pack']['default_assign'] ? 'selected="selected"':'') ?>><?php _e('Yes','mgm')?></option>
							<option value="0" <?php echo (!$data['pack']['default_assign'] ? 'selected="selected"':'') ?>><?php _e('No','mgm')?></option>
						</select>
						<div class="tips width95"><?php _e('If selected Yes, admin created users will be assigned this pack by default.','mgm')?></div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Description','mgm')?>:</p>
						<textarea cols="65" rows="5" name="packs[<?php echo ($data['pack_ctr']-1) ?>][description]"><?php echo esc_html(stripslashes($data['pack']['description'])) ?></textarea>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Hide Private Content Prior to Join','mgm')?>:</p>
						<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][hide_old_content]" class="width60px">
							<option value="1" <?php echo ((int)$data['pack']['hide_old_content'] ? 'selected="selected"':'') ?>><?php _e('Yes','mgm')?></option>
							<option value="0" <?php echo (!(int)$data['pack']['hide_old_content'] ? 'selected="selected"':'') ?>><?php _e('No','mgm')?></option>
						</select>  
						<div class="tips width95"><?php _e('If selected Yes, members can access only the content which are published after their registration date.','mgm')?></div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('When expired/cancelled, move members to','mgm')?>:</p>
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
			</div>
			
			<div class="row">

				<div class="cell">

					<div class="marginleft10px">	

						<p class="fontweightbold"><?php _e('Multiple Logins Allowed IP Limit','mgm')?>:</p>

						<input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][multiple_logins_limit]" id="packs_multiple_logins_limit_<?php echo ($data['pack_ctr']-1) ?>" value="<?php echo $data['pack']['multiple_logins_limit'] ?>" size="10" maxlength="15"/>

						<div class="tips width95"><?php _e('The number of login IPs allowed for this pack.','mgm')?></div>
					</div>

				</div>

			</div>
			
			<!-- display settings -->
			<div class="row">
				<div class="cell">	
					<div class="subscription-heading"><?php _e('Display Settings','mgm') ?></div>
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Active On','mgm')?>:</p>
						<?php foreach ($data['obj_sp']->get_active_options() as $option => $val):
							$checked = ($data['pack']['active'][$option]) ? ' checked="checked" ' : '';?>
							<input type="checkbox" name="packs[<?php echo ($data['pack_ctr']-1) ?>][active][<?php echo $option ?>]" value="1" <?php echo $checked; ?>>&nbsp;<?php echo sprintf(__('%s page','mgm'), ucwords($option)) ?>&nbsp;&nbsp
						<?php endforeach;?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Hide On','mgm')?>:</p>
						<input type="checkbox" name="packs[<?php echo ($data['pack_ctr']-1) ?>][hidden]" value="1" <?php echo ($data['pack']['hidden']) ? ' checked="checked" ' : '';?>/>
						<?php _e('General Register','mgm');?> 

						<input type="checkbox" name="packs[<?php echo ($data['pack_ctr']-1) ?>][hidden_single]" value="1" <?php echo ($data['pack']['hidden_single']) ? ' checked="checked" ' : '';?>/>
						<?php _e('Single Package Register','mgm');?> 
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Sort Order','mgm')?>:</p>
						<input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][sort]" value="<?php echo esc_html($data['pack']['sort']) ?>" size="10" maxlength="10"/>
						<div class="tips width95"><?php _e('Used to sort Packs on Register/Upgrade/Downgrade Pages.','mgm')?></div>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Preference','mgm')?>:</p>
						<input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][preference]" value="<?php echo esc_html($data['pack']['preference']) ?>" size="10" maxlength="10"/>
						<div class="tips width95"><?php _e('Packs with Higher Preference will be treated as "Upgrade" and vice versa.','mgm')?></div>
					</div>
				</div>
			</div>
			
			<?php if(!in_array($data['pack']['membership_type'], array('trial','free'))): if ($data['supports_trial'] === true):?>
			
			<!-- trial settings -->			
			<div class="row trail_pack_<?php echo ($data['pack_ctr']-1);?> <?php echo ($data['pack']['num_cycles'] == 1 ? 'displaynone':'')?>">
				<div class="cell">	
					<div class="subscription-heading"><?php _e('Trial Settings','mgm') ?></div>
				</div>
			</div>
			
			<div class="row trail_pack_<?php echo ($data['pack_ctr']-1);?> <?php echo ($data['pack']['num_cycles'] == 1 ? 'displaynone':'')?>">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Use Trial','mgm')?>:</p>
						<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][trial_on]" onchange="mgm_toggle_trial(this)" class="width60px">
							<option value="1" <?php echo ((int)$data['pack']['trial_on'] ? 'selected="selected"':'') ?>><?php _e('Yes','mgm')?></option>
							<option value="0" <?php echo (!(int)$data['pack']['trial_on'] ? 'selected="selected"':'') ?>><?php _e('No','mgm')?></option>
						</select>
					</div>
				</div>
			</div>
			
			<div class="row pack_trial_<?php echo ($data['pack_ctr']-1) . ' ' . ((!(int)$data['pack']['trial_on'] || $data['pack']['num_cycles'] == 1) ? 'displaynone' : ''); ?>">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Trial Duration','mgm')?>:</p>
						<input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][trial_duration]" value="<?php echo (int)$data['pack']['trial_duration'] ?>" size="5" maxlength="10"/>
						<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][trial_duration_type]" class="width100px">
							<?php foreach ($data['obj_sp']->get_duration_types('date_expr') as $value=>$text):
								$selected = ($value == $data['pack']['trial_duration_type'] ? 'selected="selected"':'');
								echo '<option value="'. $value .'" '. $selected .'>'. $text .'</option>';
							endforeach?>
						</select>
					</div>
				</div>
			</div>
			
			<div class="row pack_trial_<?php echo ($data['pack_ctr']-1) . ' ' . ((!(int)$data['pack']['trial_on'] || $data['pack']['num_cycles'] == 1)? 'displaynone' : ''); ?>">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Trial Cost','mgm')?>:</p>
						<input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][trial_cost]" value="<?php echo esc_html($data['pack']['trial_cost']) ?>" size="10" maxlength="15"/>
					</div>
				</div>
			</div>
			
			<div class="row pack_trial_<?php echo ($data['pack_ctr']-1) . ' ' . ((!(int)$data['pack']['trial_on'] || $data['pack']['num_cycles'] == 1) ? 'displaynone' : '');?>">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Trial Occurrences','mgm')?>:</p>
						<input type="text" name="packs[<?php echo ($data['pack_ctr']-1) ?>][trial_num_cycles]" value="<?php echo ($data['pack']['trial_num_cycles'])?>" size="10" maxlength="10"/>				
						<div class="tips width95"><?php _e('Please use "Trial Occurrences" to configure number of times "Subscription Package Duration" to be treated as "Trial Period". Authorize.Net Payment Gateway requires you to set up "Trial Period" same as "Subscription Package Duration" .', 'mgm')?></div>
					</div>
				</div>
			</div>
			
			<?php endif; endif; // end trial settings?>		
			
			<!-- payment settings -->
			<div class="row ">
				<div class="cell ">	
					<div class="subscription-heading"><?php _e('Payment Settings','mgm') ?></div>
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Allow Renewal','mgm')?>:</p>
						<select name="packs[<?php echo ($data['pack_ctr']-1) ?>][allow_renewal]" class="width60px">
							<option value="1" <?php echo ((int)$data['pack']['allow_renewal'] ? 'selected="selected"':'') ?>><?php _e('Yes','mgm')?></option>
							<option value="0" <?php echo (!(int)$data['pack']['allow_renewal'] ? 'selected="selected"':'') ?>><?php _e('No','mgm')?></option>
						</select>
					</div>
				</div>
			</div>
			
			<?php if(!in_array($data['pack']['membership_type'], array('free'))): ?>		
			<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"><?php _e('Allow Modules','mgm')?>:</p>
						<?php if($data['payment_modules']): $modue_i = 0; foreach($data['payment_modules'] as $payment_module) : if(!in_array($payment_module, array('mgm_trial'))):?>
						<input type="checkbox" name="packs[<?php echo ($data['pack_ctr']-1) ?>][modules][<?php echo $modue_i; ?>]" value="<?php echo $payment_module?>" <?php echo (in_array($payment_module,(array)$data['pack']['modules']))?'checked':''?> /> <?php echo mgm_get_module($payment_module)->name?>
						<?php $modue_i++; endif; endforeach; else:?>				
						<b class="mgm_color_red"><?php _e('No payment module is active.','mgm')?></b>		
						<?php endif;?>
					</div>
				</div>
			</div>
			
			<div id="<?php echo ('module_settings_' . ($data['pack_ctr']-1))?>">
			<?php 
				// subscription purchase/product settings
				if($data['payment_modules']): foreach($data['payment_modules'] as $payment_module) : 
					$module = mgm_get_module($payment_module); 
					if($module->has_product_map()):
						echo $module->settings_subscription_package($data);
					endif;
				endforeach; endif;?>
			</div>
			<?php endif; // end payment settings?>	
			
			<!-- package url tags -->
			<div class="row">
				<div class="cell">	
					<div class="subscription-heading"><?php _e('Package Register URLs/Tag','mgm') ?></div>
				</div>
			</div>
			
			<?php 
				// package					
				$package     = $data['pack']['membership_type'] . '#' . $data['pack']['id'];	
				$package_enc = base64_encode($package);				
			?>
			<div class="mhighlight">
				<div class="row">
					<div class="cell">
						<div>	
							<p class="fontweightbold"><?php _e('Custom URL','mgm')?>:</p>
							<span class="packreg-customurl"><?php echo mgm_get_custom_url('register',false,array('package'=>$package_enc));?></span>
							<!--<a class="copy-packreg-customurl">copy</a>-->
						</div>
					</div>
				</div>				
				
				<div class="row">
					<div class="cell">
						<div>	
							<p class="fontweightbold"><?php _e('Wordpress URL','mgm')?>:</p>
							<span class="packreg-wpurl"><?php echo mgm_get_custom_url('register',true,array('package'=>$package_enc));?></span>
							<!--<a class="copy-packreg">copy</a>-->
						</div>
					</div>
				</div>				
				
				<div class="row">
					<div class="cell">
						<div>	
							<p class="fontweightbold"><?php _e('Tag','mgm')?>:</p>
							<span class="packreg-tag"><?php echo sprintf('[user_register package=%s]',$package);?></span>
							<!--<a class="copy-packreg">copy</a>-->
						</div>
					</div>
				</div>
			</div>
			
			<!--<div class="row">
				<div class="cell">
					<div class="marginleft10px">	
						<p class="fontweightbold"></p>
						
					</div>
				</div>
			</div>-->			
			
			<div class="row ">
				<div class="cell">	
					<p>					
						<a class="button" href="javascript:mgm_delete_pack('<?php echo $data['pack_ctr']?>','<?php echo $data['pack']['id']?>')"><?php printf(__('Delete Package #%d','mgm'), $data['pack']['id']) ?></a>
						<!--<a class="button" href="javascript:mgm_save_pack('<?php echo $data['pack_ctr']?>','<?php echo $data['pack']['id']?>')"><?php printf(__('Save Package #%d','mgm'), $data['pack']['id']) ?></a>-->
					</p>				
				</div>
			</div>			
		</div>
	</div>	
</fieldset>
<script type="text/javascript">
	jQuery(document).ready(function(){	
		// default
		mgm_check_pack_duration('<?php echo $data['pack_ctr']-1;?>', '<?php echo $data['pack']['duration_type'] ?>', '<?php echo $data['pack']['duration']; ?>', '<?php echo $data['pack']['num_cycles']; ?>' );	
		
		// assign to onchange event:
		jQuery('select[name="packs[<?php echo $data['pack_ctr']-1;?>][duration_type]"]').change(function() {		
			mgm_check_pack_duration('<?php echo $data['pack_ctr']-1;?>', this.value, '<?php echo $data['pack']['duration']; ?>', '<?php echo $data['pack']['num_cycles']; ?>' );	
		});
		
		// set billing to 1 if lifetime selected:
		jQuery('select[name="packs[<?php echo $data['pack_ctr']-1;?>][num_cycles]"]').change(function() {		
			if(jQuery('select[name="packs[<?php echo $data['pack_ctr']-1;?>][duration_type]"]').val() == 'l') {// lifetime
				this.selectedIndex = 1;
			}
		});
		
		// bind billing change
		jQuery(':radio[name="packs[<?php echo $data['pack_ctr']-1;?>][num_cycles]"]').bind('click',function() {				
			if(jQuery(this).val() == '2'){
				jQuery(':input[name="packs[<?php echo $data['pack_ctr']-1;?>][num_cycles_limited]"]').attr('disabled',false);
			}else{
				jQuery(':input[name="packs[<?php echo $data['pack_ctr']-1;?>][num_cycles_limited]"]').attr('disabled',true);
			}
			//disabled trail for one time
			if(jQuery(this).val() == '1'){
				jQuery('.trail_pack_<?php echo ($data['pack_ctr']-1);?>').fadeOut();			
				jQuery('.pack_trial_<?php echo ($data['pack_ctr']-1);?>').fadeOut();			
			}else{
				jQuery('.trail_pack_<?php echo ($data['pack_ctr']-1);?>').fadeIn();	
				jQuery('.pack_trial_<?php echo ($data['pack_ctr']-1);?>').fadeIn();	
				
			}
		});
		
		// bind module allow
		jQuery(":checkbox[name^='packs[<?php echo ($data['pack_ctr']-1) ?>][modules]']").bind('click',function() {		
			var _m = jQuery(this).val().replace('mgm_', '');
			var _i = '<?php echo ($data['pack_ctr']-1) ?>';
			if(jQuery(this).attr('checked')){				
				jQuery('#module_settings_' + _i).find('#settings_subscription_package_' + _m).slideDown('slow');
			}else{				
				jQuery('#module_settings_' + _i).find('#settings_subscription_package_'+_m).slideUp('slow');
			}
		});
		// copy
		/*jQuery('#mgm_pack_<?php echo $data['pack']['id'] ?> a.copy-packreg-customurl').zclip({
			path: '<?php echo MGM_ASSETS_URL?>js/jquery/zclip/ZeroClipboard.swf',
			copy: jQuery('#mgm_pack_<?php echo $data['pack']['id'] ?> span.packreg-customurl').text()
		});*/
		
		jQuery('#mgm_pack_<?php echo $data['pack']['id'] ?>').find('legend').css({cursor : 'pointer'});

		jQuery('#mgm_pack_<?php echo $data['pack']['id'] ?>').find('legend').bind('click', function(){
			if( jQuery(this).hasClass('packdet_open') ){
				jQuery(this).removeClass('packdet_open');
				jQuery('#mgm_pack_<?php echo $data['pack']['id'] ?>_details').fadeOut('slow');
				//console.log('Close');
			}else{
				jQuery(this).addClass('packdet_open');
				jQuery('#mgm_pack_<?php echo $data['pack']['id'] ?>_details').fadeIn('slow');
				//console.log('Open');
			}
		});
	});
	
	//get pack roles:
	arr_pack_role[<?php echo ($data['pack_ctr']-1) ?>] = '<?php echo $data['pack']['role']; ?>';	
</script>