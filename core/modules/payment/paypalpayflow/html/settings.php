<!--paypal main settings-->
<?php header('Content-Type: text/html; charset=UTF-8');?>
<div id="module_settings_<?php echo $data['module']->code?>">
	<?php mgm_box_top($data['module']->name. ' Settings');?>
		<form name="frmmod_<?php echo $data['module']->code?>" id="frmmod_<?php echo $data['module']->code?>" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payments&method=module_settings&module=<?php echo $data['module']->code?>">
		<div class="table">
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Paypal Payflow Partner','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[partner]" id="setting_partner" value="<?php echo esc_html($data['module']->setting['partner']); ?>" size="30"/>
					<p><div class="tips"><?php _e('Paypal Payflow Partner.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Paypal Payflow Vendor','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[vendor]" id="setting_vendor" value="<?php echo esc_html($data['module']->setting['vendor']); ?>" size="50"/>
					<p><div class="tips"><?php _e('Paypal Payflow Vendor.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Paypal Payflow API Username','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[username]" id="setting_username" value="<?php echo esc_html($data['module']->setting['username']); ?>" size="50"/>
					<p><div class="tips"><?php _e('Paypal Payflow API Username created in Paypal Manager.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Paypal Payflow API Password','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[password]" id="setting_password" value="<?php echo esc_html($data['module']->setting['password']); ?>" size="50"/>
					<p><div class="tips"><?php _e('Paypal Payflow API Password created in Paypal Manager.','mgm'); ?></div></p>
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Paypal Payflow Layout','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="setting[layout]" id="setting_layout" class="width200px">
						<?php echo mgm_make_combo_options(array('layout_a'=>'Layout A','layout_b'=>'Layout B','layout_c'=>'Layout C'), $data['module']->setting['layout'], MGM_KEY_VALUE)?>
					</select>							
					<p><div class="tips"><?php _e('Layout to use.','mgm'); ?></div></p>
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<p><b><?php _e('The Currency used for the payments','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="setting[currency]" id="setting_currency" class="width200px">
						<?php echo mgm_make_combo_options(mgm_get_currencies(), $data['module']->setting['currency'], MGM_KEY_VALUE)?>
					</select>							
					<p><div class="tips"><?php _e('Currency to use, update primary currency in General Settings page.','mgm'); ?></div></p>
				</div>
			</div>
			<!-- Issue #974-->			
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Maximum Number of Failed Payments','mgm'); ?>:</b></p>				
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[max_failed_payments]" id="setting_max_failed_payments" value="<?php echo $data['module']->setting['max_failed_payments']; ?>" size="10"/>
					<p><div class="tips"><?php _e('Maximum number of failed payments allowed before PayPal automatically suspends the profile.','mgm'); ?></div></p>
				</div>
			</div>	
			<?php /*?>		
			<div class="row">
				<div class="cell">
					<p><b><?php _e('PayPal local site to use','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="setting[locale]" id="setting_locale" class="width120px">
						<?php echo mgm_make_combo_options(mgm_get_locales(), $data['module']->setting['locale'], MGM_KEY_VALUE)?>
					</select>							
					<p><div class="tips"><?php _e('Paypal locale to use.','mgm'); ?></div></p>
				</div>
			</div>
			<?php */?>
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
			<?php /*?><div class="row">
				<div class="cell">
					<p><b><?php _e('Supported Credit Card Types','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo mgm_make_checkbox_group('card_types[]', $data['module']->card_types, $data['module']->setting['supported_card_types'], 2, '', 'div' );?>
					<p><div class="tips"><?php _e('Enable/Disable the credit card types to be shown in Credit Card Form.','mgm'); ?></div></p>
				</div>
			</div><?php */?>
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
					<?php if (! empty($data['module']->logo)) :?>
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
						<?php echo mgm_make_combo_options(array('test'=>'TEST','live'=>'LIVE'), $data['module']->status, MGM_KEY_VALUE)?>
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
					<p>
						<div class="tips">
						<?php _e('Custom Thankyou URL for redirecting user to payment success/failed page. '.
								 'This URL is meant to be updated inside your site, you can create a Wordpress post/page and paste the page url here.<br>' .
								 '<u><b>Tag</b></u>: <br><b>[transactions]</b> : Shows Transaction Details<br>','mgm'); ?>
						</div>
					</p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Return URL','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo $data['module']->setting['return_url']?>												
					<p><div class="tips"><?php _e('Return URL for capturing silent post data sent from PayPal. READONLY, only for information.','mgm'); ?></div></p>
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
					<p><div class="tips"><?php _e('Cancel URL for cancelling from PayPal. READONLY, only for information.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
				  <?php 
						if (call_user_func('mgm_' . 'check_paypal_ipn_port_support', 'null')):
							$support = __('Supported..!','mgm');
							$img_url  = $url = MGM_ASSETS_URL . 'images/icons/tick_pass.png';
						else:
							$support = __('Not Supported..!','mgm');
							$img_url  = $url = MGM_ASSETS_URL . 'images/icons/cross_error.png';
						endif;
						?>		
					 <span class="mgm_line_height26px"><b><?php _e('PayPal IPN uses standard HTTP POST so it should be on port 80','mgm'); ?> : </b> </span> <img src="<?php echo $img_url; ?>" width="24" height="24" alt="<?php echo $support; ?>" align="top" />					 			
					<div class="tips"><?php _e('Make sure the server port is set to 80 to avoid paypal transaction failures.','mgm'); ?></div>
				</div>
			</div>	
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Silent Post URL','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo $data['module']->setting['notify_url']?>												
					<p><div class="tips"><?php _e('Silent Post URL for capturing silent post data sent from PayPal. READONLY, only for information.','mgm'); ?></div></p>
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
					<?php echo (bool_from_yn($data['module']->supports_trial)) ? 'Yes' : 'No'?>												
					<p><div class="tips"><?php _e('Supports trial setup. READONLY, only for information.','mgm'); ?></div></p>
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
				'setting[partner]' : 'required',
				'setting[vendor]' : 'required',
				'setting[username]': 'required',
				'setting[password]': 'required'				
			},
			messages: {		
				'setting[partner]': "<?php _e('Please enter Payflow Partner.','mgm')?>",
				'setting[vendor]': "<?php _e('Please enter Payflow Vendor.','mgm')?>",
				'setting[username]': "<?php _e('Please enter Payflow Username.','mgm')?>",
				'setting[password]': "<?php _e('Please enter Payflow Password.','mgm')?>"
			},
			errorClass: 'invalid'
		});			
		// attach uploader
		mgm_file_uploader('#module_settings_<?php echo $data['module']->code?>', mgm_upload_logo);	
	});	
	//-->	
</script>

	
