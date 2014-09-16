<?php header('Content-Type: text/html; charset=UTF-8');?>
<form name="frmuserfldedit" id="frmuserfldedit" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/custom_fields&method=edit" class="marginpading0px">	
	<div class="table widefatDiv">
		<div class="row headrow">
			<div class="cell theadDivCell">
				<?php _e('Edit Custom Field','mgm')?>
			</div>
		</div>
		<div class="row">
			<div class="cell width120px">
				<span class="required-field">
					<b><?php _e('Label','mgm')?>:</b>
				</span>
			</div>
		</div>
		<div class="row">	
			<div class="cell textalignleft ">
				<input type="text" name="label" id="label" value="<?php echo stripslashes($data['custom_field']['label'])?>" size="50" maxlength="150"/>
			</div>
		</div>
		<div class="row">
			<div class="cell width120px">
				<span class="required-field">
					<b><?php _e('Name','mgm')?>:</b>
				</span>
			</div>
		</div>
		<div class="row">		
			<div class="cell textalignleft ">
				<input type="text" name="name" id="name" value="<?php echo stripslashes($data['custom_field']['name'])?>"size="50" maxlength="150" readonly="readonly" />
				<div class="tips"><?php _e('default lowercase label value, spaces replaced by underscore','mgm')?>.</div>
			</div>
		</div>	
		<div class="row">
			<div class="cell width120px">
				<span class="required-field">
					<b><?php _e('Input Type','mgm')?>:</b>
				</span>
			</div>
		</div>
		<div class="row">		
			<div class="cell textalignleft ">
				<?php 
				//issue #1234	
				if($data['custom_field']['name'] == 'subscription_options'):
					$data['input_types'] = array('select' => 'Select (Drop down box)','radio' => 'Radio');
				endif;?>
				<select name="type" id="type">
					<?php echo mgm_make_combo_options($data['input_types'], $data['custom_field']['type'], MGM_KEY_VALUE)?>
				</select>
			</div>
		</div>	
		
		<?php  
		// issue #1239		
		if($data['custom_field']['name'] != 'display_name'): 
		?>
		<div class="row displaynone">
			<div class="cell width120px">
				<span class="required-field">
					<b><?php _e('Value','mgm')?>:</b>
				</span>					
			</div>
		</div>
		<div class="row" id="values_row">		
			<div class="cell textalignleft ">
				<div id="value_element"></div>					
				<div class="tips margintop5px">						
					<?php 
						// counry
						$countrynote = '';
						// check
						if($data['custom_field']['name'] == 'country'):
							 $countrynote = sprintf('For country, use 2 character <a href="%s" target="_blank">ISO Country code</a>.', 'http://www.iso.org/iso/english_country_names_and_code_elements');
						endif;?>
					<?php printf(__('The default value for the field. %s','mgm'),$countrynote)?>.
				</div>
				<!-- issue #1281 -->
				<input type="hidden" name="old_value" id="old_value" value="<?php echo htmlentities(mgm_stripslashes_deep($data['custom_field']['value']), ENT_QUOTES, "UTF-8") ?>"/>
			</div>
		</div>	
		<div class="row displaynone">
			<div class="cell width120px">
				<span class="required-field">
					<b><?php _e('Options','mgm')?>:</b>
				</span>
			</div>
		</div>
		<div class="row" id="options_row">		
			<div class="cell textalignleft ">
				 <textarea name="options" id="options" class="height200px width650px"><?php echo stripslashes($data['custom_field']['options'])?></textarea>
				 <div class="tips width600px">
					<?php _e('Options for multiple value fields. Applicable to field type Select, Checkbox and Radio.<br />'.
							 'Comma or semicolon separated values, eg: value1;value2;value3 OR value1,value2,value3.<br />'.
							 'Leave blank for Country, will be populated from database','mgm')?>.
				 </div>
			</div>
		</div>
		<?php endif; ?>
		<div class="row">
			<div class="cell width120px">
				<span class="required-field">
					<b><?php _e('Settings','mgm')?>:</b>
				</span>
			</div>
		</div>
		<div class="row brBottom">		
			<div class="cell textalignleft ">
				<ul>
					<li><input type="checkbox" class="checkbox" name="required" value="1" <?php mgm_check_if_match(1, $data['custom_field']['attributes']['required'])?>/> <?php _e('Required!','mgm') ?></li>
					<li><input type="checkbox" class="checkbox" name="readonly" value="1" <?php mgm_check_if_match(1, $data['custom_field']['attributes']['readonly'])?>/> <?php _e('Readonly','mgm') ?></li>
					<li><input type="checkbox" class="checkbox" name="hide_label" value="1" <?php mgm_check_if_match(1, $data['custom_field']['attributes']['hide_label'])?>/> <?php _e('Hide Label','mgm') ?></li>
					<li><input type="checkbox" class="checkbox" name="to_autoresponder" value="1" <?php mgm_check_if_match(1, $data['custom_field']['attributes']['to_autoresponder'])?>/> <?php _e('Send to Autoresponder','mgm') ?></li>						
					<li>
						<input type="checkbox" class="checkbox" name="capture_only" value="1" <?php mgm_check_if_match(1, $data['custom_field']['attributes']['capture_only'])?> /> <?php _e('Capture Only','mgm') ?>
						<span class="<?php echo ($data['custom_field']['attributes']['capture_only'] == 1) ? 'displayinline' : 'displaynone'?>" id="capture_field_alias_wrap">
							<u><?php _e('Field Alias','mgm')?></u>:<input type="text" size="20" value="<?php echo stripslashes($data['custom_field']['attributes']['capture_field_alias'])?>" id="capture_field_alias" name="capture_field_alias" />
						</span>
					</li>	
					<!-- issue #973 start -->
					<li>
						<input type="checkbox" class="checkbox" name="password_min_length" value="1" <?php mgm_check_if_match(1, $data['custom_field']['attributes']['password_min_length'])?> /> <?php _e('Minimum length validation','mgm') ?>
						<span class="<?php echo ($data['custom_field']['attributes']['password_min_length'] == 1) ? 'displayinline' : 'displaynone'?>" id="password_min_length_field_alias_wrap">
							<u><?php _e('Minimum value','mgm')?></u>:<input type="text" size="20" value="<?php echo stripslashes($data['custom_field']['attributes']['password_min_length_field_alias'])?>" id="password_min_length_field_alias" name="password_min_length_field_alias" />
						</span>
					</li>				
					<li>
						<input type="checkbox" class="checkbox" name="password_max_length" value="1" <?php mgm_check_if_match(1, $data['custom_field']['attributes']['password_max_length'])?> /> <?php _e('Maximum length validation','mgm') ?>
						<span class="<?php echo ($data['custom_field']['attributes']['password_max_length'] == 1) ? 'displayinline' : 'displaynone'?>" id="password_max_length_field_alias_wrap">
							<u><?php _e('Maximum value','mgm')?></u>:<input type="text" size="20" value="<?php echo stripslashes($data['custom_field']['attributes']['password_max_length_field_alias'])?>" id="password_max_length_field_alias" name="password_max_length_field_alias" />
						</span>
					</li>				
					<!-- issue #973 end -->
					<!-- issue #1573 start -->
					<li>
						<input type="checkbox" class="checkbox" name="profile_by_membership_types" value="1" <?php mgm_check_if_match(1, $data['custom_field']['attributes']['profile_by_membership_types'])?> /> <?php _e('Show On Profile By Membership Type','mgm') ?>
						<span class="<?php echo ($data['custom_field']['attributes']['profile_by_membership_types'] == 1) ? 'displayinline' : 'displaynone'?>" id="profile_membership_types_field_alias_wrap">						
							<u><?php _e('Membership types','mgm')?></u>:<input type="text" size="40" value="<?php echo stripslashes($data['custom_field']['attributes']['profile_membership_types_field_alias'])?>" name="profile_membership_types_field_alias" id="profile_membership_types_field_alias" />
						</span>
					</li>
					<li>
						<input type="checkbox" class="checkbox" name="register_by_membership_types" value="1" <?php mgm_check_if_match(1, $data['custom_field']['attributes']['register_by_membership_types'])?> /> <?php _e('Show On Register By Membership Type','mgm') ?>
						<span class="<?php echo ($data['custom_field']['attributes']['register_by_membership_types'] == 1) ? 'displayinline' : 'displaynone'?>" id="register_membership_types_field_alias_wrap">						
							<u><?php _e('Membership types','mgm')?></u>:<input type="text" size="40" value="<?php echo stripslashes($data['custom_field']['attributes']['register_membership_types_field_alias'])?>" name="register_membership_types_field_alias" id="register_membership_types_field_alias"/>
						</span>
					</li>					
					<!-- issue #1573 end -->					
					<li><input type="checkbox" class="checkbox" name="on_register" value="1" <?php mgm_check_if_match(1, $data['custom_field']['display']['on_register'])?>/> <?php _e('Show On Register Page','mgm') ?></li>
					<li><input type="checkbox" class="checkbox" name="on_login" value="1" <?php mgm_check_if_match(1, $data['custom_field']['display']['on_login'])?>/> <?php _e('Show On Login Page','mgm') ?></li>
					
					<?php //if(in_array($data['custom_field']['name'], array('autoresponder','coupon','payment_gateways'))):?>
					<li><input type="checkbox" class="checkbox" name="on_upgrade" value="1" <?php mgm_check_if_match(1, $data['custom_field']['display']['on_upgrade'])?>/> <?php _e('Show On Upgrade Page','mgm') ?></li>
					<li><input type="checkbox" class="checkbox" name="on_multiple_membership_level_purchase" value="1" <?php mgm_check_if_match(1, $data['custom_field']['display']['on_multiple_membership_level_purchase'])?>/> <?php _e('Show On Multiple Membership Level Purchase Page','mgm') ?></li>				
					<?php //endif;?>		
					
					<?php if(in_array($data['custom_field']['name'], array('coupon','payment_gateways'))):?>
					<li><input type="checkbox" class="checkbox" name="on_extend" value="1" <?php mgm_check_if_match(1, $data['custom_field']['display']['on_extend'])?>/> <?php _e('Show On Extend Page','mgm') ?></li>
					<li><input type="checkbox" class="checkbox" name="on_postpurchase" value="1" <?php mgm_check_if_match(1, $data['custom_field']['display']['on_postpurchase'])?>/> <?php _e('Show On Post Purchase Page','mgm') ?></li>
					<?php endif;?>
					
					<li><input type="checkbox" class="checkbox" name="on_login_widget" value="1" <?php mgm_check_if_match(1, $data['custom_field']['display']['on_login_widget'])?>/> <?php _e('Show On Login Widget','mgm') ?></li>
					<li><input type="checkbox" class="checkbox" name="on_profile" value="1" <?php mgm_check_if_match(1, $data['custom_field']['display']['on_profile'])?>/> <?php _e('Show On Profile Page','mgm') ?></li>
					<li><input type="checkbox" class="checkbox" name="on_payment" value="1" <?php mgm_check_if_match(1, $data['custom_field']['display']['on_payment'])?>/> <?php _e('Show On Payment Page','mgm') ?></li>
					<li><input type="checkbox" class="checkbox" name="on_public_profile" value="1" <?php mgm_check_if_match(1, $data['custom_field']['display']['on_public_profile'])?>/> <?php _e('Show On Public Profile Page','mgm') ?></li>										
					<?php if(in_array($data['custom_field']['name'], array('autoresponder'))):?>
					<li><input type="checkbox" class="checkbox" name="auto_checked" value="1" <?php mgm_check_if_match(1, $data['custom_field']['attributes']['auto_checked'])?>/> <?php _e('Auto Checked','mgm') ?></li>
					<?php endif;?>
					<?php if(in_array($data['custom_field']['name'], array('email'))):?>
					<li><input type="checkbox" class="checkbox" name="email_confirm" value="1" <?php mgm_check_if_match(1, $data['custom_field']['attributes']['email_confirm'])?>/> <?php _e('Enable confirm e-mail field','mgm') ?></li>
					<?php endif;?>		

					<?php if(in_array($data['custom_field']['name'], array('birthdate'))):?>
					<li>
						<input type="checkbox" class="checkbox" name="verify_age" value="1" <?php mgm_check_if_match(1, $data['custom_field']['attributes']['verify_age'])?>/> <?php _e('Verify age','mgm') ?>
						<span class="<?php echo ($data['custom_field']['attributes']['verify_age'] == 1) ? 'displayinline' : 'displaynone'?>" id="verify_age_wrap">
							<u><?php _e('More than','mgm')?></u>:
							<input type="text" size="5" maxlength="10" value="<?php echo stripslashes($data['custom_field']['attributes']['verify_age_unit'])?>" id="verify_age_unit" name="verify_age_unit" />
							<?php $options = array('YEAR'=>__('YEAR','mgm'), 'MONTH'=>__('MONTH','mgm'), 'WEEK'=>__('WEEK','mgm'),'DAY'=>__('DAY','mgm'));?>
							<select id="verify_age_period" name="verify_age_period">
								<?php echo mgm_make_select_options($options, $data['custom_field']['attributes']['verify_age_period'], MGM_KEY_VALUE)?>
							</select>	
						</span>	
					</li>
					
					<?php endif;?>				
				</ul>					
				<div class="tips"><?php _e('Display/Usage settings for the fields','mgm')?>.</div>
			</div>
		</div>	
		<div class="row">
			<div class="cell">
				<div class="floatleft">
					<input class="button" type="submit" name="save_fields" id="save_fields" value="<?php _e('Save', 'mgm') ?>" />				
				</div>
				<div class="floatright">
					<input class="button" type="button" name="btn_cancel" value="<?php _e('Cancel', 'mgm') ?>" onclick="mgm_custom_field_add()"/>
				</div>				
			</div>
		</div>
	</div>			
	<input type="hidden" name="id" value="<?php echo $data['custom_field']['id']?>" />
	<input type="hidden" name="system" value="<?php echo $data['custom_field']['system']?>" />	
</form>
<script language="javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){  
		// enable/disable options
		mgm_switch_options = function(options){
			// all options
			var _options = ['required','readonly','hide_label','to_autoresponder','on_register','capture_only','on_payment',
							'on_profile','on_public_profile','on_upgrade','on_extend','on_login','on_login_widget','on_upgrade',
							'on_multiple_membership_level_purchase','password_min_length','password_max_length',
							'profile_by_membership_types','register_by_membership_types'];
			// hide all
			jQuery.each(_options, function(){ jQuery("#frmuserfldedit :checkbox[name='"+this+"']").parent().hide();});		
			// show selected
			jQuery.each(options, function(){ jQuery("#frmuserfldedit :checkbox[name='"+this+"']").parent().show();});
		}	
		// switch elements
		mgm_switch_elements = function(type, name){
			// capture old value
			var old_value = jQuery('#frmuserfldedit #old_value').val();		
			// console.log(type + ' ' + name );	
			// by type
			switch(type){				
				case 'text':
				case 'textarea':
				case 'password':
				case 'image':
				case 'datepicker':
					// empty value
					jQuery('#frmuserfldedit #value_element').html('');
					jQuery('#frmuserfldedit #value_element').parent().parent().fadeOut();
					// disable options
					jQuery("#frmuserfldedit textarea[name='options']").attr('disabled',true);
					jQuery('#frmuserfldedit #options').parent().parent().fadeOut();
					
					// show fields
					if(type == 'password'){
						_options = ['required','hide_label','on_register','on_profile','password_min_length','password_max_length','profile_by_membership_types','register_by_membership_types'];
					}else{
						if( name == 'coupon'){
							_options = ['required','readonly','hide_label','on_register','on_upgrade','on_extend','on_postpurchase',
										'on_multiple_membership_level_purchase','register_by_membership_types'];
						}else{
							_options = ['required','readonly','hide_label','on_register','on_profile','on_public_profile','on_payment','on_upgrade','on_multiple_membership_level_purchase','profile_by_membership_types','register_by_membership_types'];
							// skip image
							if(type != 'image'){
								_options.push('to_autoresponder');
							}
						}							
					}
					// add
					_options.push('capture_only');
						
					// switch
					mgm_switch_options(_options);
				break;
				case 'html':
					jQuery('#frmuserfldedit #value_element').html('<textarea name="value" id="value" class="height200px width650px">'+old_value+'</textarea>');					
					jQuery('#frmuserfldedit #value_element').parent().parent().fadeIn();
					
					mgm_toggle_editor(true);
					
					jQuery("#frmuserfldedit textarea[name='options']").attr('disabled',true);
					jQuery('#frmuserfldedit #options').parent().parent().fadeOut();
					
					// switch
					mgm_switch_options(['hide_label','on_register','register_by_membership_types']);
				break;	
				case 'select':	
				case 'selectm':					
				case 'checkbox':	
				case 'checkboxg':						
				case 'radio':	

					jQuery('#frmuserfldedit #value_element').html('<input type="text" name="value" id="value" value="'+old_value+'" size="50"/>');	
					jQuery('#frmuserfldedit #value_element').parent().parent().fadeIn();
					
					// console.log(jQuery('#frmuserfldedit #name').val());
					switch( name ){
						case 'country':
						case 'autoresponder':
							// disable options
							jQuery("#frmuserfldedit textarea[name='options']").attr('disabled',true);
							// hide options row
							jQuery('#frmuserfldedit #options_row').hide();

							// for autoresponder
							if( name == 'autoresponder'){
								// hide row
								jQuery('#frmuserfldedit #values_row').hide();
								// selected options
								_options = ['required','readonly','hide_label','to_autoresponder','capture_only','on_register','on_profile',
											'on_public_profile','on_payment','on_upgrade','on_multiple_membership_level_purchase',
											'profile_by_membership_types','register_by_membership_types'];
							}				
						break;
						case 'subscription_options':
						case 'addon':
							// disable options
							jQuery("#frmuserfldedit textarea[name='options']").attr('disabled',true);
							// hide row
							jQuery('#frmuserfldedit #values_row').hide();
							jQuery('#frmuserfldedit #options_row').hide();
							// selected options
							_options = ['required','hide_label','on_register','register_by_membership_types'];
						break;
						default:
							// enable options
							jQuery("#frmuserfldedit textarea[name='options']").attr('disabled',false);
							// show row
							jQuery('#frmuserfldedit #options').parent().parent().fadeIn();		
							// selected options	
							_options = ['required','readonly','hide_label','to_autoresponder','capture_only','on_register','on_profile',
									'on_public_profile','on_payment','on_upgrade','on_multiple_membership_level_purchase',
									'profile_by_membership_types','register_by_membership_types'];
						break;
					}
					//console.log(_options);
					// switch
					mgm_switch_options(_options);
				break;					
				case 'hidden':
					jQuery('#frmuserfldedit #value_element').html('<input type="text" name="value" id="value" value="'+old_value+'" size="50"/>');
					jQuery('#frmuserfldedit #value_element').parent().parent().fadeIn();
					
					jQuery("#frmuserfldedit textarea[name='options']").attr('disabled',true);
					jQuery('#frmuserfldedit #options').parent().parent().fadeOut();
					
					// switch
					mgm_switch_options(['required','to_autoresponder','capture_only','on_register','on_profile','on_public_profile','on_payment','on_upgrade',
					'on_multiple_membership_level_purchase','profile_by_membership_types','register_by_membership_types']);
				break;	
				case 'label':						
					if( jQuery.inArray(name, [ 'subscription_options', 'payment_gateways']) != -1 ){				
						jQuery('#frmuserfldedit #value_element').html('');
						jQuery('#frmuserfldedit #value_element').parent().parent().fadeOut();
					}else{
						jQuery('#frmuserfldedit #value_element').html('<input type="text" name="value" id="value" value="'+old_value+'" size="50"/>');
						jQuery('#frmuserfldedit #value_element').parent().parent().fadeIn();
					}	
					
					jQuery("#frmuserfldedit textarea[name='options']").attr('disabled',true);
					jQuery('#frmuserfldedit #options').parent().parent().fadeOut();
					
					switch( name ){
						case 'subscription_options':
							_options = ['required','hide_label','on_register','register_by_membership_types'];
						break;
						case 'payment_gateways':
							_options = ['required','hide_label','on_register','on_upgrade','on_extend','on_postpurchase',
										'on_multiple_membership_level_purchase','register_by_membership_types'];
						break;
						default:
							_options = ['required','hide_label','on_register','readonly','on_profile','on_public_profile','on_payment','on_upgrade',
							'on_multiple_membership_level_purchase','profile_by_membership_types','register_by_membership_types'];
						break;
					}
					
					// switch
					mgm_switch_options(_options);	
				break;	
				/*
				case 'image':
					jQuery('#frmuserfldedit #value_element').html('<input type="file" name="value" id="value" value="'+old_value+'" size="100"/>');
					jQuery('#frmuserfldedit #value_element').parent().parent().fadeIn();
					
					jQuery("#frmuserfldedit textarea[name='options']").attr('disabled',true);
					jQuery('#frmuserfldedit #options').parent().parent().fadeOut();
					
					mgm_switch_options(['required','readonly','on_payment','on_profile'], false);
				break;
				*/
				case 'captcha':
					jQuery('#frmuserfldedit #value_element').html('');
					jQuery('#frmuserfldedit #value_element').parent().parent().fadeOut();
					
					jQuery("#frmuserfldedit textarea[name='options']").attr('disabled',true);
					jQuery('#frmuserfldedit #options').parent().parent().fadeOut();
					
					// switch
					mgm_switch_options(['hide_label','on_register','on_login','on_login_widget','register_by_membership_types']);
					
					/*
					jQuery("#frmuserfldedit input[name='required']").attr('checked',true);					
					jQuery("#frmuserfldedit input[name='readonly']").attr('checked',false);
					jQuery("#frmuserfldedit input[name='on_profile']").attr('checked',false);
					jQuery("#frmuserfldedit input[name='on_payment']").attr('checked',false);
					jQuery("#frmuserfldedit input[name='on_public_profile']").attr('checked',false);
					*/
					
				break;
			}
		}
		// toggle editor
		var v_editor = null;// instance
		// toggle
		mgm_toggle_editor=function(op) {
			// add
			if(op) {
				v_editor = new nicEditor({fullPanel : true, iconsPath: '<?php echo MGM_ASSETS_URL?>js/nicedit/nicEditorIcons.gif'}).panelInstance('value');
			} else {
				// check
				if(v_editor){
					v_editor.removeInstance('value');			
					v_editor = null;
				}
			}
		}
		// issue#: 353
		mgm_save_editor = function() {			
			if(v_editor && typeof(v_editor) == 'object'){				  	
				v_editor.nicInstances[0].saveContent();
			}	
		}
		// lock system field type change
		mgm_lock_system_field_type_change=function(name, system){		
			//issue #1234
			if(system == '1' && name !='subscription_options' ){
				jQuery("#frmuserfldedit select[name='type']").attr('disabled', true).addClass('readonly');
			}else{
				jQuery("#frmuserfldedit select[name='type']").removeAttr('disabled', false).removeClass('readonly');
			}
		}
		// bind
		jQuery("#frmuserfldedit :input[name='capture_only']").bind('click', function(){
			if(jQuery(this).attr('checked')){
				jQuery('#capture_field_alias_wrap').fadeIn();
				//jQuery('#capture_field_alias_wrap').css('display', 'block');
				jQuery('#capture_field_alias').val('');
			}else{
				jQuery('#capture_field_alias_wrap').fadeOut();
			}
		});

		// 
		jQuery("#frmuserfldedit :input[name='verify_age']").bind('click', function(){
			if(jQuery(this).attr('checked')){
				jQuery('#verify_age_wrap').find(':input').attr('disabled', false).andSelf().fadeIn();
			}else{
				jQuery('#verify_age_wrap').find(':input').attr('disabled', true).andSelf().fadeOut();
			}
		});


		// bind - issue #973
		jQuery("#frmuserfldedit :input[name='password_min_length']").bind('click', function(){
			if(jQuery(this).attr('checked')){
				jQuery('#password_min_length_field_alias_wrap').fadeIn();
				//jQuery('#capture_field_alias_wrap').css('display', 'block');
				jQuery('#password_min_length_field_alias').val('');
			}else{
				jQuery('#password_min_length_field_alias_wrap').fadeOut();
			}
		});		
		// bind - issue #973
		jQuery("#frmuserfldedit :input[name='password_max_length']").bind('click', function(){
			if(jQuery(this).attr('checked')){
				jQuery('#password_max_length_field_alias_wrap').fadeIn();
				//jQuery('#capture_field_alias_wrap').css('display', 'block');
				jQuery('#password_max_length_field_alias').val('');
			}else{
				jQuery('#password_max_length_field_alias_wrap').fadeOut();
			}
		});
		// bind - issue #1573
		jQuery("#frmuserfldedit :input[name='profile_by_membership_types']").bind('click', function(){
			if(jQuery(this).attr('checked')){
				jQuery('#profile_membership_types_field_alias_wrap').fadeIn();
				jQuery('#profile_membership_types_field_alias').val('');
			}else{
				jQuery('#profile_membership_types_field_alias_wrap').fadeOut();
			}
		});		
		// bind - issue #1573
		jQuery("#frmuserfldedit :input[name='register_by_membership_types']").bind('click', function(){
			if(jQuery(this).attr('checked')){
				jQuery('#register_membership_types_field_alias_wrap').fadeIn();
				jQuery('#register_membership_types_field_alias').val('');
			}else{
				jQuery('#register_membership_types_field_alias_wrap').fadeOut();
			}
		});	
		// bind
		jQuery("#frmuserfldedit select[name='type']").not('.readonly').bind('change', function(){			
			mgm_switch_elements(jQuery(this).val(), jQuery("#frmuserfldedit :input[name='name']").val());
		});
		// edit : form validation
		jQuery("#frmuserfldedit").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmuserfldedit").ajaxSubmit({type: "POST",
				  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/custom_fields&method=edit',
				  dataType: 'json',				
				  iframe: false,							 
				  beforeSubmit: function(){	
				  	// save
					if(jQuery("#frmuserfldedit select[name='type']").val() == 'html'){
						mgm_save_editor();
					}
				  	// show message
					mgm_show_message('#custom_field_manage', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);						
				  },
				  success: function(data){			
					// message																				
					mgm_show_message('#custom_field_manage', data);														
					// success	
					if(data.status=='success'){		
						// list																			
						mgm_custom_field_list();														
					}														
				  }}); // end   		
				  return false;											
			},
			rules: {			
				label: "required",			
				name: "required",				
				type: "required",					
				'options': {required: function(){ 
						return ( 
							(jQuery.inArray(jQuery("#frmuserfldedit select[name='type']").val(), ['select','checkbox','radio']) !=-1) 
							 && (jQuery('#frmuserfldedit #name').val() != 'country')
							 && (jQuery('#frmuserfldedit #name').val() != 'subscription_options')
						); 
					}
				 },
				'value': {required: function(){ 
						return ( 
							(jQuery.inArray(jQuery("#frmuserfldedit select[name='type']").val(), ['select','radio','hidden','label','image']) !=-1)
							&& (jQuery('#frmuserfldedit #name').val() != 'subscription_options') 
						); 
					}
				}			
			},
			messages: {			
				label: "<?php _e('Please enter label','mgm')?>",
				name: "<?php _e('Please enter name','mgm')?>",
				type: "<?php _e('Please select a type ','mgm')?>",
				options: "<?php _e('Please enter options','mgm')?>",
				value: "<?php _e('Please enter value/default','mgm')?>"
			},
			errorClass: 'invalid',
			errorPlacement:function(error, element) {	
				if(element.is("[name='name']"))
					error.insertAfter(element.next());	
				else
					error.insertAfter(element);					
			}
		});			
		// save: important: the below line is required(issue#: 353 ) 
		jQuery("#save_fields").bind('click', mgm_save_editor);
		// lock
		mgm_lock_system_field_type_change('<?php echo $data['custom_field']['name']?>', '<?php echo (int)$data['custom_field']['system']?>');
		// onload switch
		mgm_switch_elements('<?php echo $data['custom_field']['type']?>','<?php echo $data['custom_field']['name']?>');		
	});		
	//-->	
</script>