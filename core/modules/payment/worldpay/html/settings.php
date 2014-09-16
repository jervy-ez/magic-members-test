<!--worldpay settings-->
<?php header('Content-Type: text/html; charset=UTF-8');?>
<div id="module_settings_<?php echo $data['module']->code?>">
	<?php mgm_box_top($data['module']->name. ' Settings');?>
	<form name="frmmod_<?php echo $data['module']->code?>" id="frmmod_<?php echo $data['module']->code?>" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payments&method=module_settings&module=<?php echo $data['module']->code?>">
		<div class="table">
			<div class="row">
				<div class="cell">
					<p><b><?php _e('WorldPay Installation ID','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[inst_id]" id="setting_inst_id" value="<?php echo $data['module']->setting['inst_id']; ?>" size="50"/>
					<p><div class="tips"><?php _e('WorldPay Installation ID.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('WorldPay MD5 Signature','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[md5_sig]" id="setting_md5_sig" value="<?php echo $data['module']->setting['md5_sig']; ?>" size="50"/>
					<p><div class="tips"><?php _e('WorldPay MD5 Signature.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Shopper Response','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="radio" name="setting[shopper_response]" id="setting_shopper_response_y" value="Y" <?php echo (bool_from_yn($data['module']->setting['shopper_response'])) ? 'checked="true"' : ''; ?>/> <?php _e('Yes','mgm'); ?>
					<input type="radio" name="setting[shopper_response]" id="setting_shopper_response_n" value="N" <?php echo (bool_from_yn($data['module']->setting['shopper_response'])) ? 'checked="true"' : ''; ?>/> <?php _e('No','mgm'); ?>						
					<p><div class="tips"><?php _e('Select Yes/No depending on "Enable the Shopper Response" value on Merchant panel installation settings.','mgm'); ?></div></p>
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
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Language','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="setting[lang]" id="setting_lang" class="width120px">
						<?php echo mgm_make_combo_options($data['module']->_get_languages(), $data['module']->setting['lang'], MGM_KEY_VALUE)?>
					</select>							
					<p><div class="tips"><?php _e('The language to use.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('WorldPay API Endpoints','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell paddingleft10px">
					<input type="checkbox" value="custom" name="setting[end_points]" id="setting_end_points" <?php echo $data['module']->setting['end_points']=='custom'?'checked':''?>/> <?php _e('Customize WorldPay API Endpoints?')?><br />
					<div id="custom_end_points_region" class="<?php echo $data['module']->setting['end_points']=='custom'?'displayblock':'displaynone'?> paddingleft5px">
						<div class="table">
							<div class="row">
								<div class="cell width100px">
									<?php _e('Test','mgm')?>
								</div>
								<div class="cell">
									<input type="text" name="end_points[test]" id="end_points_test" value="<?php echo $data['module']->_get_endpoint('test'); ?>" size="78" <?php echo $data['module']->setting['end_points']=='custom'?'':'disabled="true"'?> />
								</div>
							</div>
							<div class="row">							
								<div class="cell width100px">
									<?php _e('Live','mgm')?> 
								</div>
								<div class="cell">
									<input type="text" name="end_points[live]" id="end_points_live" value="<?php echo $data['module']->_get_endpoint('live'); ?>" size="78" <?php echo $data['module']->setting['end_points']=='custom'?'':'disabled="true"'?> />
								</div>
							</div>
							<div class="row">							
								<div class="cell width100px">
									<?php _e('Live Transaction','mgm')?> 
								</div>
								<div class="cell">
									<input type="text" name="end_points[live_transaction]" id="end_points_live_transaction" value="<?php echo $data['module']->_get_endpoint('live_transaction'); ?>" size="78" <?php echo $data['module']->setting['end_points']=='custom'?'':'disabled="true"'?> />
								</div>
							</div>
						</div>				
					</div>
					<p><div class="tips"><?php _e('WorldPay custom api endpoints, nedded when WorldPay used as gateway to other payment processors.','mgm'); ?></div></p>
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
					<input type="text" name="setting[success_title]" id="setting_success_title" value="<?php echo $data['module']->setting['success_title']; ?>" size="78"/>
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
					<p><b><?php _e('Worldpay Gateway Transaction Success Page Contents','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<textarea name="gateway_successpage" id="setting_gateway_successpage" rows='4' cols='75' class="width750px height100px"><?php echo mgm_stripslashes_deep(esc_html($data['module']->setting['gateway_successpage'])); ?></textarea>						
					<div class="clearfix"></div>
					<p><div class="tips"><?php _e('Contents to be displayed on Wordlpay Gateway success page.<br/>The default thankyou page on Merchant gateway needs to be replaced by this html.<br/>Copy and paste the html into <strong>resultY.html</strong> on Merchant Gateway','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Worldpay Gateway Transaction Failed Page Contents','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<textarea name="gateway_failedpage" id="setting_gateway_errorpage" rows='4' cols='75' class="width750px height100px"><?php echo mgm_stripslashes_deep(esc_html($data['module']->setting['gateway_failedpage'])); ?></textarea>						
					<div class="clearfix"></div>
					<p><div class="tips"><?php _e('Contents to be displayed on Wordlpay Gateway failed page.<br/>The default thankyou page on Merchant gateway needs to be replaced by this html.<br/>Copy and paste the html into <strong>resultC.html</strong> on Merchant Gateway','mgm'); ?></div></p>

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
					<p><b><?php _e('Payment Response URL','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo $data['module']->setting['notify_url']?>												
					<p><div class="tips"><?php _e('Payment Response URL for capturing payment post data returned from gateway. READONLY, only for information.','mgm'); ?></div></p>

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
					<?php echo ( $data['module']->is_trial_supported() ) ? __('Yes','mgm') : __('No', 'mgm')?>	
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
			if(this.name != 'gateway_successpage' && this.name != 'gateway_failedpage' )
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
				'setting[inst_id]': "required",						
				'setting[md5_sig]': "required"
			},
			messages: {			
				'setting[inst_id]': "<?php _e('Please enter WorldPay installation id.','mgm')?>",
				'setting[md5_sig]': "<?php _e('Please enter WorldPay md5 signature.','mgm')?>"
			},
			errorClass: 'invalid'
		});	
		// attach uploader
		mgm_file_uploader('#module_settings_<?php echo $data['module']->code?>', mgm_upload_logo);
		// custom endpoints
		jQuery('#module_settings_<?php echo $data['module']->code?> #setting_end_points').bind('click', function(){
			// selected
			if( jQuery(this).attr('checked') ){
				// show
				jQuery('#module_settings_<?php echo $data['module']->code?> #custom_end_points_region').fadeIn();
				// enable
				jQuery('#module_settings_<?php echo $data['module']->code?> #custom_end_points_region :input').attr('disabled', false);
			}else{
			// de selected
				// hide
				jQuery('#module_settings_<?php echo $data['module']->code?> #custom_end_points_region').fadeOut();
				// disable
				jQuery('#module_settings_<?php echo $data['module']->code?> #custom_end_points_region :input').attr('disabled', true);
			}
		});	
	});	
	//-->	
</script>

	
