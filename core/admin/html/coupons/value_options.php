	<div class="mbhighlight">
		<div class="row">	
			<div class="cell">
				<select name="value_is[options]" id="value_is_options" class="width300px">		
					<?php echo mgm_make_combo_options(array('flat'=>__('Flat','mgm'),'percent'=>__('Percentage','mgm'),'sub_pack'=>__('Subscription Package','mgm'),'sub_pack_bc'=>__('Subscription Package (with Billing Cycle)','mgm'),'sub_pack_trial'=>__('Trial Subscription Package','mgm')), $data['value_is']['options'], MGM_KEY_VALUE)?>		
				</select>
			</div>
		</div>
		
		<div id="flat_value_options_block" class="<?php echo ($data['value_is']['options']=='flat') ? 'displayblock' : 'displaynone'?>">
			<div class="table form-table">
				<div class="row">
					<div class="cell">
						<span class="required">
							<b><?php _e('Flat Cost','mgm')?>:</b>
						</span> 
					</div>
				</div>
				<div class="row">	
					<div class="cell">
						<input type="text" name="value_is[flat]" size="10" maxlength="50" value="<?php echo $data['value_is']['flat']?>"/>
					</div>
				</div>			
			</div>		
		</div>
		
		<div id="percent_value_options_block" class="<?php echo ($data['value_is']['options']=='percent') ? 'displayblock' : 'displaynone'?>">
			<div class="table form-table">
				<div class="row">
					<div class="cell">
						<span class="required">
							<b><?php _e('Percentage of Cost','mgm')?>:</b>
						</span> 
					</div>
				</div>
				<div class="row">	
					<div class="cell">
						<input type="text" name="value_is[percent]" size="10" maxlength="50" value="<?php echo $data['value_is']['percent']?>"/>%
					</div>
				</div>			
			</div>			
		</div>
		
		<div id="sub_pack_value_options_block" class="<?php echo (in_array($data['value_is']['options'], array('sub_pack','sub_pack_bc'))) ? 'displayblock' : 'displaynone'?>">	
			<div class="table form-table">
				<div class="row">
					<div class="cell">
						<span class="required">
							<b><?php _e('Cost','mgm')?>:</b>
						</span> 
					</div>
				</div>
				<div class="row">	
					<div class="cell">
						 <input type="text" name="value_is[sub_pack][cost]" size="10" maxlength="50" value="<?php echo $data['value_is']['sub_pack']['cost']?>"/>
					</div>
				</div>	
				<div class="row">
					<div class="cell">
						<span class="required">
							<b><?php _e('Duration','mgm')?>:</b>
						</span> 
					</div>
				</div>
				<div class="row">	
					<div class="cell">
						<input type="text" name="value_is[sub_pack][duration_unit]" size="10" maxlength="50" value="<?php echo $data['value_is']['sub_pack']['duration_unit']?>"/>
						<select name="value_is[sub_pack][duration_type]" class="width80px">
							<?php echo mgm_make_combo_options(array('d'=>'Day','m'=>'Month','y'=>'Year','l'=>'Lifetime'), $data['value_is']['sub_pack']['duration_type'], MGM_KEY_VALUE)?>
						</select>
					</div>
				</div>	
				<div class="row">
					<div class="cell">
						<span class="required">
							<b><?php _e('Membership Type','mgm')?>:</b>
						</span> 
					</div>
				</div>
				<div class="row">	
					<div class="cell">
						<select name="value_is[sub_pack][membership_type]" class="width150px">
							<?php echo mgm_make_combo_options(mgm_get_all_membership_type_combo(array('guest')), $data['value_is']['sub_pack']['membership_type'], MGM_KEY_VALUE)?>
						</select>
					</div>
				</div>				
				<div class="row row_billing_cycle <?php echo ($data['value_is']['options'] != 'sub_pack_bc') ? 'displaynone' : ''?>">
					<div class="cell">
						<span class="required">
							<b><?php _e('Billing Cycle','mgm')?>:</b>
						</span> 
					</div>
				</div>
				<div class="row row_billing_cycle <?php echo ($data['value_is']['options'] != 'sub_pack_bc') ? 'displaynone' : ''?>">	
					<div class="cell">
						<select name="value_is[sub_pack][num_cycles]" class="width80px" <?php echo ($data['value_is']['options'] != 'sub_pack_bc') ? 'disabled="disabled"' : ''?>>
							<?php echo mgm_make_combo_options(array(0 => __('Ongoing','mgm'), 1 => __('Onetime','mgm'), 2 => __('Limited','mgm')), (int)$data['value_is']['sub_pack']['num_cycles'], MGM_KEY_VALUE)?>
						</select>
						<input type="text" name="value_is[sub_pack][num_cycles_limited]" value="<?php echo $data['value_is']['sub_pack']['num_cycles_limited']?>" size="10" maxlength="10" class="<?php echo (int)($data['value_is']['sub_pack']['num_cycles'] == 2) ? 'displayblock' : 'displaynone'?>" />															
					</div>
				</div>			
			</div>	
		</div>
	
		<div id="sub_pack_trial_value_options_block" class="<?php echo ($data['value_is']['options']=='sub_pack_trial') ? 'displayblock' : 'displaynone'?>">
			<div class="table form-table">
				<div class="row">
					<div class="cell">
						<span class="required">
							<b><?php _e('Trial Cost','mgm')?>:</b>
						</span> 
					</div>
				</div>
				<div class="row">	
					<div class="cell">
						<input type="text" name="value_is[sub_pack_trial][cost]" size="10" maxlength="50" value="<?php echo $data['value_is']['sub_pack_trial']['cost']?>"/>
					</div>
				</div>	
				<div class="row">
					<div class="cell">
						<span class="required">
							<b><?php _e('Trial Duration','mgm')?>:</b>
						</span> 
					</div>
				</div>
				<div class="row">	
					<div class="cell">
						<input type="text" name="value_is[sub_pack_trial][duration_unit]" size="10" maxlength="50" value="<?php echo $data['value_is']['sub_pack_trial']['duration_unit']?>"/>
						<select name="value_is[sub_pack_trial][duration_type]" class="width80px">
							<?php echo mgm_make_combo_options(array('d'=>'Day','m'=>'Month','y'=>'Year','l'=>'Lifetime'), $data['value_is']['sub_pack_trial']['duration_type'], MGM_KEY_VALUE)?>
						</select>
					</div>
				</div>	
				<div class="row">
					<div class="cell">
						<span class="required">
							<b><?php _e('Trial Occurrences','mgm')?>:</b>
						</span> 
					</div>
				</div>
				<div class="row">	
					<div class="cell">
						<input type="text" name="value_is[sub_pack_trial][num_cycles]" value="<?php echo $data['value_is']['sub_pack_trial']['num_cycles']?>" size="10" maxlength="10" />
					</div>
				</div>
			</div>	
		</div>
	</div>	
	<script language="javascript">
		jQuery(document).ready(function(){
			// value_option change
			jQuery("#coupon_manage select[name='value_is[options]']").bind('change', function(){
				// hide all that end with 
				jQuery("div[id$='value_options_block']").hide();
				// disable all
				jQuery("div[id$='value_options_block'] :input").attr('disabled', true);
				// check
				option_selected = jQuery(this).val();
				option_selected_id = (option_selected == 'sub_pack_bc') ? 'sub_pack' : option_selected;
				
				// show selected
				jQuery("div[id='"+option_selected_id+"_value_options_block']").slideDown('slow');
				// enable selected
				jQuery("div[id='"+option_selected_id+"_value_options_block'] :input").attr('disabled', false);
				// show billing cycle
				if(option_selected == 'sub_pack_bc'){
					jQuery("div[id='"+option_selected_id+"_value_options_block'] div.row_billing_cycle").show();
					jQuery("div[id='"+option_selected_id+"_value_options_block'] div.row_billing_cycle select").attr('disabled', false);
				}else if(option_selected == 'sub_pack'){
				// hide billing cycle
					jQuery("div[id='"+option_selected_id+"_value_options_block'] div.row_billing_cycle").hide();
					jQuery("div[id='"+option_selected_id+"_value_options_block'] div.row_billing_cycle select").attr('disabled', true);
				}
			});
			// billing cycle change
			jQuery("#coupon_manage select[name='value_is[sub_pack][num_cycles]']").bind('change', function(){
				if(jQuery(this).val() == 2){				
					jQuery("#coupon_manage :input[name='value_is[sub_pack][num_cycles_limited]']").attr('disabled', false).show();
				}else{
					jQuery("#coupon_manage :input[name='value_is[sub_pack][num_cycles_limited]']").attr('disabled', true).hide();
				}
			});
			// duration type change
			jQuery("#coupon_manage select[name$='[duration_type]']").bind('change', function(){
				// name
				var name = jQuery(this).attr('name').replace('duration_type', 'duration_unit');			
				// lifetime
				if(jQuery(this).val() == 'l'){				
					jQuery("#coupon_manage :input[name='"+name+"']").val(1).attr('disabled', true);
				}else{
					jQuery("#coupon_manage :input[name='"+name+"']").attr('disabled', false);
				}
			});
		});	
	</script>