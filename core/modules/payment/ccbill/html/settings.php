<!--ccbill main settings-->
<?php header('Content-Type: text/html; charset=UTF-8');?>
<div id="module_settings_<?php echo $data['module']->code?>">
	<?php mgm_box_top($data['module']->name. ' Settings');?>
		<form name="frmmod_<?php echo $data['module']->code?>" id="frmmod_<?php echo $data['module']->code?>" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payments&method=module_settings&module=<?php echo $data['module']->code?>">
		<div class="table">
			<div class="row">
				<div class="cell">
					<p><b><?php _e('CCBill Client Account No','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[client_acccnum]" id="setting_client_acccnum" value="<?php echo esc_html($data['module']->setting['client_acccnum']); ?>" size="50"/>
					<p><div class="tips"><?php _e('The Client Account No Provided by CCBill.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('CCBill Client Sub Account No','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[client_subacc]" id="setting_client_subacc" value="<?php echo esc_html($data['module']->setting['client_subacc']); ?>" size="50"/>
					<p><div class="tips"><?php _e('The Client Sub Account created on CCBill.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('CCBill Form Name','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[formname]" id="setting_formname" value="<?php echo esc_html($data['module']->setting['formname']); ?>" size="50"/>
					<p><div class="tips"><?php _e('Form Name created on CCBill.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Upgrade API','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="setting[upgrade_api]" id="setting_upgrade_api" class="width100px">
						<?php echo mgm_make_combo_options(array('signup'=>__('Signup','mgm'),'upgrade'=>__('Upgrade','mgm')), $data['module']->setting['upgrade_api'], MGM_KEY_VALUE)?>
					</select>						
					<p><div class="tips"><?php _e('Selects between Signup API or Upgrade API for subscription upgrade procedure.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row uek_field">
				<div class="cell">
					<p><b><?php _e('CCBill Upgrade Security Key','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row uek_field">
				<div class="cell">
					<input type="text" name="setting[upgrade_enc_key]" id="setting_upgrade_enc_key" value="<?php echo esc_html($data['module']->setting['upgrade_enc_key']); ?>" size="50"/>
					<p><div class="tips"><?php _e('Upgrade Security key obtained from CCBill Support.','mgm'); ?></div></p>		
				</div>
			</div>			
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Send Username/Password to CCBill','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="setting[send_userpass]" id="setting_send_userpass" class="width100px">
						<?php echo mgm_make_combo_options(array('no'=>__('No','mgm'),'yes'=>__('Yes','mgm')), $data['module']->setting['send_userpass'], MGM_KEY_VALUE)?>
					</select>						
					<p><div class="tips"><?php _e('If you would like to send Magic Member created username/password to CCBill, please select "Yes".','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Dynamic Pricing','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="setting[dynamic_pricing]" id="setting_dynamic_pricing" class="width100px">
						<?php echo mgm_make_combo_options(array('disabled'=>'Disabled', 'enabled'=>'Enabled'), $data['module']->setting['dynamic_pricing'], MGM_KEY_VALUE)?>
					</select>						
					<p><div class="tips"><?php _e('If Dynamic Pricing feature is enabled for the Sub Account, please select "Enabled".','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row dp_field">
				<div class="cell">
					<p><b><?php _e('Dynamic Pricing MD5 Hash Salt','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row dp_field">
				<div class="cell">
					<input type="text" name="setting[md5_hashsalt]" id="setting_md5_hashsalt" value="<?php echo esc_html($data['module']->setting['md5_hashsalt']); ?>" size="50"/>
					<p><div class="tips"><?php _e('Dynamic Pricing MD5 Hash Salt obtained from CCBill Support. Only needed when Dynamic Pricing is "Enabled"','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('DataLink SMS Username','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[datalink_username]" id="setting_datalink_username" value="<?php echo esc_html($data['module']->setting['datalink_username']); ?>" size="50"/>
					<p><div class="tips"><?php _e('DataLink SMS Username created on CCBill. Used for DataLink SMS Rebill Status Query.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('DataLink SMS Password','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[datalink_password]" id="setting_datalink_password" value="<?php echo esc_html($data['module']->setting['datalink_password']); ?>" size="50"/>
					<p><div class="tips"><?php _e('DataLink SMS Password created on CCBill. Used for DataLink SMS Rebill Status Query.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Currency','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="setting[currency]" id="setting_currency" class="width200px">
						<?php echo mgm_make_combo_options(mgm_get_currencies(), $data['module']->setting['currency'], MGM_KEY_VALUE)?>
					</select>						
					<p><div class="tips"><?php _e('Currency used for the payment if Dynamic Pricing is enabled.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Rebill Status Query','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="setting[rebill_status_query]" id="setting_rebill_status_query" class="width100px">
						<?php echo mgm_make_combo_options(array('enabled'=>__('Enabled','mgm'),'disabled'=>__('Disabled','mgm')), $data['module']->setting['rebill_status_query'], MGM_KEY_VALUE)?>
					</select>						
					<p><div class="tips"><?php _e('Enable/Disable Rebill Status Query via CCBill API.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('CCBill API Endpoints','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell paddingleft10px">
					<input type="checkbox" value="custom" name="setting[end_points]" id="setting_end_points" <?php echo $data['module']->setting['end_points']=='custom'?'checked':''?>/> <?php _e('Customize CCBill API Endpoints?')?><br />
					<div id="custom_end_points_region" class="<?php echo $data['module']->setting['end_points']=='custom'?'displayblock':'displaynone'?> paddingleft5px">
		
						<div class="table">
							
							<div class="row">
								<div class="cell"><?php _e('Live','mgm')?> </div>
								<div class="cell"><input type="text" name="end_points[live]" id="end_points_live" value="<?php echo $data['module']->_get_endpoint('live'); ?>" size="100" <?php echo $data['module']->setting['end_points']=='custom'?'':'disabled="true"'?> /></div>
							</div>
							<div class="row">
								<div class="cell"><?php _e('Datalink (SMS)','mgm')?> </div>
								<div class="cell"><input type="text" name="end_points[datalink_sms]" id="end_points_datalink_sms" value="<?php echo $data['module']->_get_endpoint('datalink_sms'); ?>" size="100" <?php echo $data['module']->setting['end_points']=='custom'?'':'disabled="true"'?> /></div>
							</div>
							<div class="row">
								<div class="cell"><?php _e('Datalink (Extract)','mgm')?> </div>
								<div class="cell"><input type="text" name="end_points[datalink_extract]" id="end_points_datalink_extract" value="<?php echo $data['module']->_get_endpoint('datalink_extract'); ?>" size="100" <?php echo $data['module']->setting['end_points']=='custom'?'':'disabled="true"'?> /></div>
							</div>
							<div class="row">
								<div class="cell"><?php _e('Upgrade','mgm')?> </div>
								<div class="cell"><input type="text" name="end_points[upgrade]" id="end_points_upgrade" value="<?php echo $data['module']->_get_endpoint('upgrade'); ?>" size="100" <?php echo $data['module']->setting['end_points']=='custom'?'':'disabled="true"'?> /></div>
							</div>
						</div>		
						
					</div>
					<p><div class="tips"><?php _e('CCBill custom api endpoints, nedded when CCBill used as gateway to other payment processors.','mgm'); ?></div></p>
		
				</div>
			</div>
			<?php if(in_array('buypost', $data['module']->supported_buttons)):?>			
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Default Post Purchase Price','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[purchase_price]" id="setting_purchase_price" value="<?php echo $data['module']->setting['purchase_price']; ?>" size="10"/>
					<p><div class="tips"><?php _e('Post purchase price. Only available in modules which supports buypost.','mgm'); ?></div></p>		
				</div>
			</div>
			<?php endif;?>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Callback Success Title','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[success_title]" id="setting_success_title" value="<?php echo $data['module']->setting['success_title']; ?>" size="100"/>
					<p><div class="tips"><?php _e('Payment success page title.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Callback Success Message','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<textarea name="setting[success_message]" id="setting_success_message_<?php echo $data['module']->code?>" rows='4' cols='75' class="width750px height100px"><?php echo mgm_stripslashes_deep(esc_html($data['module']->setting['success_message'])); ?></textarea>						
					<div class="clearfix"></div>
					<p><div class="tips"><?php _e('Payment success page message.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Callback Failed Title','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[failed_title]" id="setting_failed_title" value="<?php echo $data['module']->setting['failed_title']; ?>" size="100"/>
					<p><div class="tips"><?php _e('Payment failed page title.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Callback Failed Message','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<textarea name="setting[failed_message]" id="setting_failed_message_<?php echo $data['module']->code?>" rows='4' cols='75' class="width750px height100px"><?php echo mgm_stripslashes_deep(esc_html($data['module']->setting['failed_message'])); ?></textarea>						
					<div class="clearfix"></div>
					<p><div class="tips"><?php _e('Payment failed page message.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Button/Logo','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php if (! empty($data['module']->logo)):?>
						<img src="<?php echo $data['module']->logo ?>" id="logo_image_<?php echo $data['module']->code?>" alt="<?php echo sprintf(__('%s Logo', 'mgm'),$data['module']->name) ?>" border="0"/><br />
				    <?php endif;?> 
					<input type="file" name="logo_<?php echo $data['module']->code?>" id="logo_<?php echo $data['module']->code?>" size="50"/>						
					<p><div class="tips"><?php _e('Button/logo image.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Description','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<textarea name="description" id="setting_description_<?php echo $data['module']->code?>" rows='4' cols='75' class="width750px height100px"><?php echo mgm_stripslashes_deep(esc_html($data['module']->description)); ?></textarea>						
					<div class="clearfix"></div>
					<p><div class="tips"><?php _e('Description shown on payment page.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Test/Live Switch','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="status" class="width100px">
						<?php echo mgm_make_combo_options(array('test'=>__('TEST','mgm'),'live'=>__('LIVE','mgm')), $data['module']->status, MGM_KEY_VALUE)?>
					</select>						
					<p><div class="tips"><?php _e('Switch between TEST/LIVE mode to test your payments. Not all modules supports this feature.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Custom Thankyou URL','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[thankyou_url]" id="setting_thankyou_url" value="<?php echo $data['module']->setting['thankyou_url']; ?>" size="120"/>											
					<p><div class="tips"><?php _e('Custom Thankyou URL for redirecting user to payment success/failed page. This URL is meant to be updated inside your site, you can create a Wordpress post/page and paste the page url here.<br><u><b>Tag</b></u>: <br> <b>[transactions]</b> : Shows Transaction Details<br>','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Background Post URL','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo $data['module']->setting['notify_url']?>												
					<p><div class="tips"><?php _e('Background Post URL for capturing silent post data returned from gateway. This should be setup in CCBill Admin Background Post Information section ( Approval and Denial both). READONLY, only for information.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Approval URL','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo add_query_arg(array('status'=>'success'), $data['module']->setting['return_url'])?>												
					<p><div class="tips"><?php _e('Approval URL for all sub accounts. This should be setup in CCBill basic section Approval URL. READONLY, only for information.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Denial URL','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo add_query_arg(array('status'=>'error'), $data['module']->setting['return_url'])?>										
					<p><div class="tips"><?php _e('Denial URL for all sub accounts. This should be setup in CCBill basic section Denial URL. READONLY, only for information.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Cancel URL','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo $data['module']->setting['cancel_url']?>												
					<p><div class="tips"><?php _e('Cancel/Optout URL for all sub accounts. This should be setup in CCBill basic section Opt Out URL. READONLY, only for information.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Webhook/Status Notify URL','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo $data['module']->setting['status_notify_url']?>												
					<p><div class="tips"><?php _e('Webhook/Status Notify URL for all sub accounts. This should be setup in CCBill Webhook URL. READONLY, only for information.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Supported Buttons','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo implode(', ', $data['module']->supported_buttons)?>												
					<p><div class="tips"><?php _e('Supported buttons. READONLY, only for information.','mgm'); ?></div></p>		
				</div>
			</div>
			<?php if(in_array('subscription', $data['module']->supported_buttons)):?>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Supports Trial','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo ( $data['module']->is_trial_supported() ) ? __('Yes','mgm') : __('No','mgm')?>												
					<p><div class="tips"><?php _e('Supports trial setup. READONLY, only for information.','mgm'); ?></div></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Supports Rebill Status Checking','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo ( $data['module']->is_rebill_status_check_supported() ) ? __('Yes', 'mgm') : __('No', 'mgm')?>												
					<p><div class="tips"><?php _e('Supports rebill status check via API query. READONLY, only for information.','mgm'); ?></div></p>
				</div>
			</div>
			<?php endif;?>
			<?php if($data['module']->dependency_check() == true):?>			
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Dependency','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo implode(', <br>',$data['module']->dependency);?>												
					<p><div class="tips"><?php _e('Dependency Check. READONLY, only for information.','mgm'); ?></div></p>
				</div>
			</div>
			<?php endif;?>
		</div>	
		<p>					
			<input class="button" type="submit" name="btn_save" value="<?php _e('Update Settings', 'mgm') ?>" />
		</p>
		<input type="hidden" name="update" value="true" />
		<input type="hidden" name="setting_form" value="main" />
		</form>
	<?php mgm_box_bottom();?>
</div>
<script language="javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){   
		// editor
		jQuery("#frmmod_<?php echo $data['module']->code?> textarea[id]").each(function(){						
			new nicEditor({fullPanel : true, iconsPath: '<?php echo MGM_ASSETS_URL?>js/nicedit/nicEditorIcons.gif'}).panelInstance(jQuery(this).attr('id')); 			
		});
		// add : form validation
		jQuery("#frmmod_<?php echo $data['module']->code?>").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmmod_<?php echo $data['module']->code?>").ajaxSubmit({type: "POST",				  
				  dataType: 'json',			
				  iframe: false,					
				  beforeSerialize: function($form) { 					
					// only on IE
					if(jQuery.browser.msie){
						jQuery($form).find("textarea[id]").each(function(){								
							jQuery(this).val(nicEditors.findEditor(jQuery(this).attr('id')).getContent()); 
						});										
					}
				  },			 
				  beforeSubmit: function(){	
				  	// show processing 
					mgm_show_message("#module_settings_<?php echo $data['module']->code?>", {status: "running", message: "<?php _e('Processing','mgm')?>..."}, true);						
				  },
				  success: function(data){							
					// show status  
					mgm_show_message("#module_settings_<?php echo $data['module']->code?>", data);													
				  }}); // end   		
				  return false;											
			},
			rules: {			
				'setting[client_acccnum]': "required",
				'setting[client_subacc]': "required",
				'setting[formname]': "required",
				'setting[upgrade_enc_key]': {required: function(){ return jQuery("select#setting_upgrade_api").val() == 'upgrade'}},
				'setting[md5_hashsalt]': {required: function(){ return jQuery("select#setting_dynamic_pricing").val() == 'enabled'}},
				'setting[datalink_username]': "required",
				'setting[datalink_password]': "required"
			},
			messages: {		
				'setting[client_acccnum]': "<?php _e('Please enter CCbill client account.','mgm')?>",
				'setting[client_subacc]': "<?php _e('Please enter CCbill client subaccount.','mgm')?>",
				'setting[formname]': "<?php _e('Please enter CCbill formname.','mgm')?>",
				'setting[upgrade_enc_key]': "<?php _e('Please enter CCbill upgrade security key.','mgm')?>",
				'setting[md5_hashsalt]': "<?php _e('Please enter CCbill md5 hashsalt for Dynamic Pricing.','mgm')?>",
				'setting[datalink_username]': "<?php _e('Please enter CCbill DataLink Username.','mgm')?>",
				'setting[datalink_password]': "<?php _e('Please enter CCbill DataLink Password.','mgm')?>"
			},
			errorClass: 'invalid'
		});	
		// dynamic price salt
		jQuery('#module_settings_<?php echo $data['module']->code?>	#setting_dynamic_pricing').bind('change', function(){
			if(jQuery(this).val() == 'enabled'){
				jQuery('#module_settings_<?php echo $data['module']->code?> .dp_field').fadeIn('slow');
			}else{
				jQuery('#module_settings_<?php echo $data['module']->code?> .dp_field').fadeOut('slow');
			}
		}).change();	
		// upgrade enc key
		jQuery('#module_settings_<?php echo $data['module']->code?>	#setting_upgrade_api').bind('change', function(){
			if(jQuery(this).val() == 'upgrade'){
				jQuery('#module_settings_<?php echo $data['module']->code?> .uek_field').fadeIn('slow');
			}else{
				jQuery('#module_settings_<?php echo $data['module']->code?> .uek_field').fadeOut('slow');
			}
		}).change();		
		// attach uploader
		mgm_file_uploader('#module_settings_<?php echo $data['module']->code?>', mgm_upload_logo);
		// attach endpoint toggle
		mgm_module_endpoints_toggle('<?php echo $data['module']->code?>');
	});	
	//-->	
</script>

	
