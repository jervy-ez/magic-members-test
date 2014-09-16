<!--epoch settings-->
<?php header('Content-Type: text/html; charset=UTF-8');?>
<div id="module_settings_<?php echo $data['module']->code?>">
	<?php mgm_box_top($data['module']->name. ' Settings');?>
		<form name="frmmod_<?php echo $data['module']->code?>" id="frmmod_<?php echo $data['module']->code?>" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payments&method=module_settings&module=<?php echo $data['module']->code?>">
		<div class="table">
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Epoch Reseller','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[reseller]" id="setting_reseller" value="<?php echo $data['module']->setting['reseller']; ?>" size="30"/>
					<p><div class="tips"><?php _e('Epoch Reseller Code. If you are not acting as a reseller for the product, set reseller to the lowercase letter "a".','mgm'); ?></div></p>					
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Epoch Company Code','mgm'); ?>:</b></p>					
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[co_code]" id="setting_co_code" value="<?php echo $data['module']->setting['co_code']; ?>" size="30"/>
					<p><div class="tips"><?php _e('Epoch Company Code.','mgm'); ?></div></p>					
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Send Username/Password to Epoch','mgm'); ?>:</b></p>					
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="setting[send_userpass]" id="setting_send_userpass" class="width100px">
						<?php echo mgm_make_combo_options(array('no'=>__('No', 'mgm'),'yes'=>__('Yes','mgm')), $data['module']->setting['send_userpass'], MGM_KEY_VALUE)?>
					</select>						
					<p><div class="tips"><?php _e('If you would like to send Magic Member created username/password to Epoch, please select "Yes".','mgm'); ?></div></p>					
				</div>
			</div>			
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Enable Epoch DataPlus?','mgm'); ?>:</b></p>					
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="setting[dataplus_enable]" id="setting_dataplus_enable" class="width100px">
						<?php echo mgm_make_combo_options(array('no'=>__('No', 'mgm'),'yes'=>__('Yes','mgm')), $data['module']->setting['dataplus_enable'], MGM_KEY_VALUE)?>
					</select>
					<p><div class="tips"><?php _e('Using DataPlus will enable rebill status check via Epoch posted transaction data.','mgm'); ?></div></p>	
													
					<div id="dataplus_options" <?php if($data['module']->setting['dataplus_enable'] == 'no'):?>class="displaynone"<?php endif;?> style="padding-top:5px">
						<em><?php _e('Data Transfer:','mgm')?></em><br />
						<select name="setting[dataplus_data_transfer]" id="setting_dataplus_data_transfer" class="width100px">
							<?php echo mgm_make_combo_options(array('database'=>__('Database','mgm'),'http_post'=>__('HTTP/HTTPS Post','mgm')/*,'ssh_sftp_upload'=>__('SSH/SFTP Upload','mgm')*/), $data['module']->setting['dataplus_data_transfer'], MGM_KEY_VALUE)?>
						</select>						
						<div id="dataplus_database_options" <?php if($data['module']->setting['dataplus_data_transfer'] != 'database'):?>class="displaynone"<?php endif;?> style="padding-top:5px">
							<em><?php _e('Database Server:','mgm')?></em><br />
							<input type="radio" name="setting[dataplus_database_server]" id="setting_dataplus_database_server_local" value="local" <?php if ($data['module']->setting['dataplus_database_server'] == 'local'): echo 'checked="checked"'; endif; ?>/> <?php _e('Local','mgm'); ?>
							<input type="radio" name="setting[dataplus_database_server]" id="setting_dataplus_database_server_remote" value="remote"  <?php if ($data['module']->setting['dataplus_database_server'] == 'remote'): echo 'checked="checked"'; endif; ?>/> <?php _e('Remote','mgm'); ?>					
							<p><div class="tips width90"><?php _e('Database server authentication should be supplied to Epoch to post transaction data at a regular interval.','mgm'); ?></div></p>
							<div id="dataplus_database_server_remote_options" <?php if($data['module']->setting['dataplus_database_server'] == 'local'):?>class="displaynone"<?php endif;?> style="padding-top:5px">
								<em><?php _e('Database User:','mgm')?></em><br />
								<input type="text" name="setting[dataplus_database_user]" id="setting_dataplus_database_user" value="<?php echo $data['module']->setting['dataplus_database_user']; ?>" size="20"/><br />
								<em><?php _e('Database Password:','mgm')?></em><br />
								<input type="password" name="setting[dataplus_database_password]" id="setting_dataplus_database_password" value="<?php echo $data['module']->setting['dataplus_database_password']; ?>" size="20"/><br />
								<em><?php _e('Database Name:','mgm')?></em><br />
								<input type="text" name="setting[dataplus_database_name]" id="setting_dataplus_database_name" value="<?php echo $data['module']->setting['dataplus_database_name']; ?>" size="50"/><br />
								<em><?php _e('Database Host:','mgm')?></em><br />
								<input type="text" name="setting[dataplus_database_host]" id="setting_dataplus_database_host" value="<?php echo $data['module']->setting['dataplus_database_host']; ?>" size="50"/>
								<p><div class="tips width90"><?php _e('Remote database server authentication. Only required when the database where your site is hosted does not allow direct database connection.','mgm'); ?></div></p>
							</div>														
						</div>						
						<div id="dataplus_http_post_options" <?php if($data['module']->setting['dataplus_data_transfer'] != 'http_post'):?>class="displaynone"<?php endif;?> style="padding-top:5px">
							<em><?php _e('Post URL:','mgm')?></em><br />
							<input type="text" name="setting[dataplus_http_post_url]" id="setting_dataplus_http_post_url" value="<?php echo $data['module']->setting['dataplus_http_post_url']; ?>" size="120"/><br />
							<p><div class="tips width90"><?php _e('Post URL to be supplied to Epoch to send transaction data at a regular interval.','mgm'); ?></div></p>
						</div>
					</div>										
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Epoch SearchAPI Auth User','mgm'); ?>:</b></p>					
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[searchapi_auth_user]" id="setting_searchapi_auth_user" value="<?php echo $data['module']->setting['searchapi_auth_user']; ?>" size="30"/>
					<p><div class="tips"><?php _e('Epoch SearchAPI Auth User, required for Subscription Cancel.','mgm'); ?></div></p>					
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Epoch SearchAPI Auth Password','mgm'); ?>:</b></p>					
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[searchapi_auth_pass]" id="setting_searchapi_auth_pass" value="<?php echo $data['module']->setting['searchapi_auth_pass']; ?>" size="30"/>
					<p><div class="tips"><?php _e('Epoch  SearchAPI Auth Password, required for Subscription Cancel.','mgm'); ?></div></p>					
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
					<p><b><?php _e('Return URL','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo $data['module']->setting['return_url']?>															
					<p><div class="tips"><?php _e('Return URL for capturing payment post data returned from gateway. READONLY, only for information.','mgm'); ?></div></p>
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
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Supports Rebill Status Checking','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo ( $data['module']->is_rebill_status_check_supported() ) ? __('Yes','mgm') : __('No', 'mgm')?>												
					<p><div class="tips"><?php _e('Supports rebill status check via API query. READONLY, only for information.','mgm'); ?></div></p>
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
				'setting[reseller]': "required",						
				/*'setting[co_code]': "required"*/
				'setting[searchapi_auth_user]': "required",
				'setting[searchapi_auth_pass]': "required"
			},
			messages: {			
				'setting[reseller]': "<?php _e('Please enter Epoch reseller','mgm')?>",
				/*'setting[co_code]': "<?php _e('Please enter Epoch company code','mgm')?>"*/
				'setting[searchapi_auth_user]': "<?php _e('Please enter Epoch SearchAPI auth user','mgm')?>",
				'setting[searchapi_auth_pass]': "<?php _e('Please enter Epoch SearchAPI auth password','mgm')?>"
			},
			errorClass: 'invalid'
		});	
		// attach uploader
		mgm_file_uploader('#module_settings_<?php echo $data['module']->code?>', mgm_upload_logo);
		
		// setting[dataplus_enable]
		jQuery("#frmmod_<?php echo $data['module']->code?> select[name='setting[dataplus_enable]']").change(function(){
			if(jQuery(this).val() == 'yes'){
				jQuery('#dataplus_options').fadeIn();
			}else{
				jQuery('#dataplus_options').fadeOut();
			}
		});
		
		// data transfer 
		jQuery("#frmmod_<?php echo $data['module']->code?> select[name='setting[dataplus_data_transfer]']").change(function(){
			// hide all
			jQuery.each(['database','http_post'], function(){
				jQuery('#dataplus_' + this + '_options').hide();
			});
			// show
			jQuery('#dataplus_' + jQuery(this).val() + '_options').fadeIn();			
		});
		
		// database server
		jQuery("#frmmod_<?php echo $data['module']->code?> :radio[name='setting[dataplus_database_server]']").click(function(){
			if(jQuery(this).val() == 'remote'){
				jQuery('#dataplus_database_server_remote_options').fadeIn();
			}else{
				jQuery('#dataplus_database_server_remote_options').fadeOut();
			}
		});				
	});	
	//-->	
</script>