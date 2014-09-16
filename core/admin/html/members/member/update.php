	<div class="mgm-line-divider"></div>			
	<p style="padding-left:5px">
		<b><?php _e('Please select some members from the list above to apply the update(s)','mgm')?>:</b>
	</p>	

	<div class="table">
		<div class="row">
			<div class="cell width150px">
				<input type="checkbox" class="checkbox" name="update_opt[]" value="status" /> <b><?php _e('Status','mgm') ?></b>
			</div>
		</div>
		<div class="row">	
			<div class="cell">
				<div id="upd_elements_status">
					<select name="upd_status" id="upd_status" disabled="disabled" class="width150px">
						<option value="-"><?php _e('Select','mgm') ?></option>
						<?php foreach(mgm_get_subscription_statuses(true) as $subs_status):?>
						<option value="<?php echo $subs_status ?>"><?php echo esc_html($subs_status) ?></option>
						<?php endforeach;?>
						<?php /*
						<option value="<?php echo MGM_STATUS_NULL ?>"><?php echo esc_html(MGM_STATUS_NULL) ?></option>
						<option value="<?php echo MGM_STATUS_ACTIVE ?>"><?php echo esc_html(MGM_STATUS_ACTIVE) ?></option>
						<option value="<?php echo MGM_STATUS_EXPIRED ?>"><?php echo esc_html(MGM_STATUS_EXPIRED) ?></option>
						<option value="<?php echo MGM_STATUS_PENDING ?>"><?php echo esc_html(MGM_STATUS_PENDING) ?></option>
						<option value="<?php echo MGM_STATUS_ERROR ?>"><?php echo esc_html(MGM_STATUS_ERROR) ?></option>*/?>
					</select>
					<input type="checkbox" name="override_rebill_status_check" id="override_rebill_status_check" value="Y" disabled="disabled" class="checkbox"/> 
					<b><?php _e('Override Rebill Status Query','mgm') ?></b>&nbsp;&nbsp;
				</div>
			</div>
		</div>
		<div class="row">
			<div class="cell width150px">
				<input type="checkbox" class="checkbox" name="update_opt[]" value="membership_type" /> <b><?php _e('Membership Type','mgm') ?></b>
			</div>
		</div>
		<div class="row">		
			<div class="cell">
				<div id="upd_elements_membership_type">
					<select name="upd_membership_type" id="upd_membership_type" disabled="disabled" class="width200px">
						<option value="-"><?php _e('Select','mgm') ?></option>	
						<?php foreach (mgm_get_class('membership_types')->membership_types as $type_code=>$type_name):
							// check 
							if($type_code == 'guest') continue;							
							// prin
							printf( '<option value="%s">%s</option>', $type_code, __(mgm_stripslashes_deep($type_name), 'mgm'));
						endforeach;?>
					</select>	
				</div>	
			</div>
		</div>
		<div class="row">
			<div class="cell width150px">
				<input type="checkbox" class="checkbox" name="update_opt[]" value="expire_date" /> <b><?php _e('Expiration Date','mgm') ?></b>
			</div>
		</div>
		<div class="row">		
			<div class="cell">
				<div id="upd_elements_expire_date">
					<input type="text" name="upd_expire_date" id="upd_expire_date" size="12" disabled="disabled"/>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="cell width150px">
				<input type="checkbox" class="checkbox" name="update_opt[]" value="hide_old_content" /> <b><?php _e('Hide Private Content Prior to Join','mgm') ?></b>
			</div>
		</div>
		<div class="row">		
			<div class="cell">
				<div id="upd_elements_hide_old_content">
					<select name="upd_hide_old_content" id="upd_hide_old_content" disabled="disabled" class="width100px">
						<option value="1" ><?php _e('Yes','mgm')?></option>
						<option value="0" selected="selected"><?php _e('No','mgm')?></option>
					</select>	
				</div>
			</div>
		</div>
		<div class="row">
			<div class="cell width150px">
				<input type="checkbox" class="checkbox" name="update_opt[]" value="subscription" /> <b><?php _e('Subscription Pack','mgm') ?></b>
			</div>
		</div>
		<div class="row">		
			<div class="cell ">
				<div id="upd_elements_subscription">
					<select name="upd_subscription_pack" id="upd_subscription_pack" disabled="disabled" class="width250px">
						<option value="-"><?php _e('Select','mgm') ?></option>
						<?php foreach($packages = mgm_get_subscription_packages() as $pack):
							echo '<option value="'.$pack['key'].'">'.$pack['label'].'</option>';
						endforeach;?>
					</select>
					<div style="padding-top:5px">
						<?php if($data['enable_multiple_level_purchase']): ?>
						<input type="checkbox" name="insert_new_level" id="insert_new_level" value="new" disabled="disabled" class="checkbox"/> 
						<b><?php _e('Apply as additional subscription','mgm') ?></b>&nbsp;&nbsp;
						<?php endif; ?>
						<input type="checkbox" name="no_expiration_date_update" id="no_expiration_date_update" value="Y" disabled="disabled" class="checkbox"/> 
						<b><?php _e('Do not update expiration date','mgm') ?></b>&nbsp;&nbsp;					
						
						<input type="checkbox" name="subscribe_to_autoresponder" id="subscribe_to_autoresponder" value="Y" disabled="disabled" class="checkbox"/> 
						<b><?php _e('Subscribe to Autoresponder','mgm') ?></b>
					</div>
					<div style="padding-top:5px">
						<?php if($data['enable_multiple_level_purchase']): ?>
						<input type="checkbox" name="highlight_role" id="highlight_role" value="highlight" disabled="disabled" class="checkbox"/> 
						<b>	<?php if($data['enable_multiple_level_purchase']): _e('Update user\'s role','mgm'); endif; ?></b>
						<?php else: ?>
						<input type="hidden" name="highlight_role" id="highlight_role" value="highlight" disabled="disabled"/> 
						<?php endif; ?>
					</div>
				</div>	
			</div>
		</div>
		<div class="row">
			<div class="cell width150px">
				<input type="checkbox" class="checkbox" name="update_opt[]" value="payment_module_info" /> <b><?php _e('Payment Module Info','mgm') ?></b>
			</div>
		</div>
		<div class="row">		
			<div class="cell">
				<div id="upd_elements_payment_module_info">
					<?php 
					// init
					$module_data = array();
					// module tracking fields
					if($data['payment_modules']): foreach($data['payment_modules'] as $payment_module) : 
						// get modu;e
						$module = mgm_get_module($payment_module); 
						// check virtual
						if( ! $module->is_virtual_payment() || $module->code =='mgm_manualpay'):
						// set data
							$module_data[] = array('code'=>$module->code, 'name'=>$module->name, 'tracking_fields' => $module->get_tracking_fields_html());
						endif;
					endforeach; endif; ?>
					<select name="payment_module" id="payment_module" disabled="disabled" class="width150px" onchange="mgm_select_mod_fields()">
						<option value="-"><?php _e('Select','mgm') ?></option>		
						<?php foreach($module_data as $module):?>				
						<option value="<?php echo $module['code']?>"><?php echo $module['name'] ?></option>	
						<?php endforeach;?>					
					</select>
					<?php foreach($module_data as $module):?>
					<div id="<?php echo $module['code']?>_fields" class="displaynone">
						<?php echo $module['tracking_fields'] ?>
					</div>
					<?php endforeach;?>	
				</div>
			</div>
		</div>
	</div>						
	<div id="err_update_select"></div>		
	<p class="submit">
		<input class="button" type="submit" name="update_member_info" value="<?php _e('Update','mgm') ?>" /> 
	</p>

	<script type="text/javascript" language="javascript">
		<!--
		// onready
		jQuery(document).ready(function(){					
			// submit
			jQuery("#mgmmembersfrm").validate({
				submitHandler: function(form) {   
					jQuery("#mgmmembersfrm").ajaxSubmit({
					  type: "POST",
					  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=member_update',
					  dataType: 'json',				
					  iframe: false,							 
					  beforeSubmit: function(){	
						// show message
						mgm_show_message('#member_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);	
					  },
					  success: function(data){	
						// message																				
						mgm_show_message('#member_list', data);	
						// reload, keep paginate state
						window.setTimeout(function(){mgm_member_list(true);}, 5000);																							
					  }});// end 
					return false;															
				},
				rules: {			
					'members[]': {
					required: {
					depends: function(element) {
							var ps_mem= true;
							if(jQuery(".otherMems")){
								jQuery(".otherMems").each(function() {
									if (jQuery(this).is(':checked')) {
										ps_mem = false;
									}
								});
							}else{
								return ps_mem;
							}
							return ps_mem;
	
						},minlength: 1	
					}
				},
					'update_opt[]':	{required:true, minlength: 1}		
				},
				messages: {
					'members[]': {required:"<?php _e('Please select one member to update ','mgm')?>",minlength:"<?php _e('Please select one member to update','mgm')?>"},
					'update_opt[]':	{required:"<?php _e('Please select one action to perform','mgm')?>",minlength:"<?php _e('Please select one action to perform','mgm')?>"}				
				},
				errorClass: 'invalid',
				errorPlacement:function(error, element) {				
					if(element.is("#mgmmembersfrm :checkbox[name='members[]']") || element.is(":checkbox[name='update_opt[]']"))
						error.appendTo('#err_update_select');										
					else		
						error.insertAfter(element);					
				}
			});	
			// bind update
			jQuery("#mgmmembersfrm :checkbox[name='update_opt[]']").bind('click', function(){
				// switch state			
				jQuery('#upd_elements_'+jQuery(this).val()).find(':input').attr('disabled', !jQuery(this).attr('checked'));
				// date 
				if(jQuery(this).val() == 'expire_date'){
					mgm_date_picker("#mgmmembersfrm :input[name='upd_expire_date']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});			
				}		
			});
			// mgm_select_mod_fields
			mgm_select_mod_fields=function(){
				// selcted
				var payment_module = jQuery('#upd_elements_payment_module_info #payment_module').val();
				// clear
				jQuery('#upd_elements_payment_module_info').find("div[id$='_fields'] :input").val('');
				// hide
				jQuery('#upd_elements_payment_module_info').find("div[id$='_fields']").fadeOut();
				// check
				if(payment_module){
					jQuery('#'+payment_module+'_fields').fadeIn();
				}
			}		
		});		
		//-->		
	</script>	