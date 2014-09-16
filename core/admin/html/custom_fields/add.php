	<form name="frmuserfldadd" id="frmuserfldadd" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/custom_fields&method=add" class="marginpading0px">		
		<div class="table widefatDiv">
			<div class="row headrow">
				<div class="cell theadDivCell">
					<?php _e('Add Custom Field','mgm')?>
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
					<input type="text" name="label" id="label" size="50" maxlength="150"/>					
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
					<input type="text" name="name" id="name" size="50" maxlength="150" readonly="readonly" /><br />
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
					<select name="type" id="type">
						<?php echo mgm_make_combo_options($data['input_types'], 'text', MGM_KEY_VALUE)?>
					</select>
				</div>
			</div>
			<div class="row displaynone">
				<div class="cell width120px">
					<span class="required-field">
						<b><?php _e('Value','mgm')?>:</b>
					</span>
				</div>
			</div>
			<div class="row">		
				<div class="cell textalignleft ">
					<div id="value_element"></div>	
					<div class="tips margintop5px"><?php _e('The default value for the field.','mgm')?></div>
				</div>
			</div>
			<div class="row displaynone">
				<div class="cell width120px">
					<span class="required-field">
						<b><?php _e('Options','mgm')?>:</b>
					</span>
				</div>
			</div>
			<div class="row">		
				<div class="cell textalignleft ">
					 <textarea name="options" id="options" class="height200px width650px" disabled="disabled"></textarea>
					 <div class="tips width600px">
						<?php _e('Options for multiple value fields. Applicable to field type Select, Checkbox and Radio.<br />'.
								 'Comma or semicolon separated values, eg: value1;value2;value3 OR value1,value2,value3','mgm')?>.
					 </div>
				</div>
			</div>
			<div class="row">
				<div class="cell width120px">
					<span class="required-field">
						<b><?php _e('Settings','mgm')?>:</b>
					</span>
				</div>
			</div>
			<div class="row brBottom">		
				<div class="cell textalignleft">
					<ul>
						<li><input type="checkbox" class="checkbox" name="required" value="1" /> <?php _e('Required!','mgm') ?></li>
						<li><input type="checkbox" class="checkbox" name="readonly" value="1" /> <?php _e('Readonly','mgm') ?></li>
						<li><input type="checkbox" class="checkbox" name="hide_label" value="1" /> <?php _e('Hide Label','mgm') ?></li>
						<li><input type="checkbox" class="checkbox" name="to_autoresponder" value="1" /> <?php _e('Send to Autoresponder','mgm') ?></li>
						<li>
							<input type="checkbox" class="checkbox" name="capture_only" value="1" /> <?php _e('Capture Only','mgm') ?>
							<span class="displaynone" id="capture_field_alias_wrap">
								<u><?php _e('Field Alias','mgm')?></u>:<input type="text" size="20" name="capture_field_alias" />
							</span>
						</li>		
						<!-- issue #973 start -->
						<li>
							<input type="checkbox" class="checkbox" name="password_min_length" value="1" /> <?php _e('Minimum length validation','mgm') ?>
							<span class="displaynone" id="password_min_length_field_alias_wrap">
								<u><?php _e('Minimum value','mgm')?></u>:<input type="text" size="20" name="password_min_length_field_alias" />
							</span>
						</li>					
						<li>
							<input type="checkbox" class="checkbox" name="password_max_length" value="1" /> <?php _e('Maximum length validation','mgm') ?>
							<span class="displaynone" id="password_max_length_field_alias_wrap">
								<u><?php _e('Maximum value ','mgm')?></u>:<input type="text" size="20" name="password_max_length_field_alias" />
							</span>
						</li>					
						<!-- issue #973 end -->
						<!-- issue #1573 start -->
						<li>
							<input type="checkbox" class="checkbox" name="profile_by_membership_types" value="1" /> <?php _e('Show On Profile By Membership Type','mgm') ?>
							<span class="displaynone" id="profile_membership_types_field_alias_wrap">
								<u><?php _e('Membership types','mgm')?></u>:<input type="text" size="20" name="profile_membership_types_field_alias" />
							</span>
						</li>					
						<li>
							<input type="checkbox" class="checkbox" name="register_by_membership_types" value="1" /> <?php _e('Show On Register By Membership Type','mgm') ?>
							<span class="displaynone" id="register_membership_types_field_alias_wrap">
								<u><?php _e('Membership types','mgm')?></u>:<input type="text" size="20" name="register_membership_types_field_alias" />
							</span>
						</li>					
						<!-- issue #1573 end -->												
						<li><input type="checkbox" class="checkbox" name="on_register" value="1" /> <?php _e('Show On Register Page','mgm') ?></li>
						<li><input type="checkbox" class="checkbox" name="on_profile" value="1" /> <?php _e('Show On Profile Page','mgm') ?></li>
						<li><input type="checkbox" class="checkbox" name="on_login" value="1" /> <?php _e('Show On Login Page','mgm') ?></li>
						<li><input type="checkbox" class="checkbox" name="on_login_widget" value="1" /> <?php _e('Show On Login Widget','mgm') ?></li>
						<li><input type="checkbox" class="checkbox" name="on_payment" value="1" /> <?php _e('Show On Payment Page','mgm') ?></li>
						<li><input type="checkbox" class="checkbox" name="on_public_profile" value="1" /> <?php _e('Show On Public Profile Page','mgm') ?></li>				
						<li><input type="checkbox" class="checkbox" name="on_upgrade" value="1" /> <?php _e('Show On Upgrade Page','mgm') ?></li>
						<li><input type="checkbox" class="checkbox" name="on_multiple_membership_level_purchase" value="1" /> <?php _e('Show On Multiple Membership Level Purchase Page','mgm') ?></li>				
					</ul>					
					<div class="tips"><?php _e('Settings for the fields','mgm')?>.</div>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<div class="floatleft">
						<input class="button" type="submit" name="save_fields" id="save_fields" value="<?php _e('Save', 'mgm') ?>" />					
					</div>					
				</div>
			</div>
		</div>
	</form>
	<script language="javascript">
		<!--	
		// onready
		jQuery(document).ready(function(){   
			// enabld/disable options
			mgm_switch_options = function(options){
				// all options
				var all_options = ['required','readonly','hide_label','to_autoresponder','capture_only','on_register','on_payment',
								   'on_profile','on_public_profile','on_upgrade','on_extend','on_login','on_login_widget',
								   'password_max_length','password_min_length','on_multiple_membership_level_purchase',
								   'profile_by_membership_types','register_by_membership_types'];
				// hide all
				jQuery.each(all_options, function(){ jQuery("#frmuserfldadd :checkbox[name='"+this+"']").parent().hide();});		
				// show selected
				jQuery.each(options, function(){ jQuery("#frmuserfldadd :checkbox[name='"+this+"']").parent().show();});		
			}		
			// switch elements
			mgm_switch_elements = function(type){
				// by type
				switch(type){				
					case 'text':
					case 'textarea':
					case 'password':
					case 'image':
						jQuery('#frmuserfldadd #value_element').html('');
						jQuery('#frmuserfldadd #value_element').parent().parent().fadeOut();
						
						jQuery("#frmuserfldadd textarea[name='options']").attr('disabled',true);
						jQuery('#frmuserfldadd #options').parent().parent().fadeOut();
						
						if(type=='password'){
							_options = ['required','hide_label','on_register','on_profile','password_max_length','password_min_length',
							'profile_by_membership_types','register_by_membership_types'];
						}else{
							_options = ['required','readonly','hide_label','on_register','on_profile','on_public_profile','on_payment','on_upgrade',
							'on_multiple_membership_level_purchase','profile_by_membership_types','register_by_membership_types'];
							if(type!='image'){
								_options.push('to_autoresponder');
							}						
						}	
						_options.push('capture_only');
						mgm_switch_options(_options);
					break;
					case 'html':
						jQuery('#frmuserfldadd #value_element').html('<textarea name="value" id="value" class="height200px width650px"></textarea>');
						mgm_toggle_editor(true);
						jQuery('#frmuserfldadd #value_element').parent().parent().fadeIn();
						
						jQuery("#frmuserfldadd textarea[name='options']").attr('disabled',true);
						jQuery('#frmuserfldadd #options').parent().parent().fadeOut();
						
						mgm_switch_options(['hide_label','on_register','register_by_membership_types']);
					break;	
					case 'select':
					case 'selectm':
					case 'checkbox':						
					case 'radio':
						jQuery('#frmuserfldadd #value_element').html('<input type="text" name="value" id="value" value="" size="50"/>');	
						jQuery('#frmuserfldadd #value_element').parent().parent().fadeIn();
						
						jQuery("#frmuserfldadd textarea[name='options']").attr('disabled',false);
						jQuery('#frmuserfldadd #options').parent().parent().fadeIn();
						
						mgm_switch_options(['required','readonly','hide_label','to_autoresponder','capture_only','on_register','on_profile','on_public_profile','on_payment','on_upgrade','on_multiple_membership_level_purchase','profile_by_membership_types','register_by_membership_types']);
					break;					
					case 'hidden':
						jQuery('#frmuserfldadd #value_element').html('<input type="text" name="value" id="value" value="" size="50"/>');
						jQuery('#frmuserfldadd #value_element').parent().parent().fadeIn();
						
						jQuery("#frmuserfldadd textarea[name='options']").attr('disabled',true);
						jQuery('#frmuserfldadd #options').parent().parent().fadeOut();
						
						mgm_switch_options(['required','to_autoresponder','capture_only','on_register','on_profile','on_public_profile','on_payment','on_upgrade','on_multiple_membership_level_purchase','profile_by_membership_types','register_by_membership_types']);
					break;	
					case 'label':					
						jQuery('#frmuserfldadd #value_element').html('<input type="text" name="value" id="value" value="" size="50"/>');
						jQuery('#frmuserfldadd #value_element').parent().parent().fadeIn();
						
						jQuery("#frmuserfldadd textarea[name='options']").attr('disabled',true);
						jQuery('#frmuserfldadd #options').parent().parent().fadeOut();
						
						mgm_switch_options(['required','readonly','hide_label','on_register','on_profile','on_public_profile','on_payment','on_upgrade','on_multiple_membership_level_purchase','profile_by_membership_types','register_by_membership_types']);
					break;	
					/*
					case 'image':
						jQuery('#frmuserfldadd #value_element').html('<input type="file" name="value" id="value" value="" size="50"/>');
						jQuery('#frmuserfldadd #value_element').parent().parent().fadeIn();
						
						jQuery("#frmuserfldadd textarea[name='options']").attr('disabled',true);
						jQuery('#frmuserfldadd #options').parent().parent().fadeOut();
						
						mgm_switch_options(['required','readonly','on_payment','on_profile'], false);
					break;*/
					case 'captcha':
						jQuery('#frmuserfldadd #value_element').html('');
						jQuery('#frmuserfldadd #value_element').parent().parent().fadeOut();
						
						jQuery("#frmuserfldadd textarea[name='options']").attr('disabled',true);
						jQuery('#frmuserfldadd #options').parent().parent().fadeOut();
						
						mgm_switch_options(['on_register','hide_label','on_login','on_login_widget','register_by_membership_types']);
						
						jQuery("#frmuserfldadd input[name='required']").attr('checked',true);
						jQuery("#frmuserfldadd input[name='readonly']").attr('checked',false);
						jQuery("#frmuserfldadd input[name='on_profile']").attr('checked',false);
						jQuery("#frmuserfldadd input[name='on_payment']").attr('checked',false);
						jQuery("#frmuserfldadd input[name='on_public_profile']").attr('checked',false);
					break;
				}	
			}		
			// bind
			jQuery("#frmuserfldadd select[name='type']").bind('change', function(){		
				mgm_switch_elements(jQuery(this).val());					
			});
			// add : form validation
			jQuery("#frmuserfldadd").validate({
				submitHandler: function(form) {					    					
					jQuery("#frmuserfldadd").ajaxSubmit({type: "POST",
					  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/custom_fields&method=add',
					  dataType: 'json',			
					  iframe: false,								 
					  beforeSubmit: function(){	
						// save
						if(jQuery("#frmuserfldadd select[name='type']").val() == 'html'){
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
								// clear fields
								jQuery("#frmuserfldadd :input").not(":input[type='hidden']").not(":input[type='submit']").not(":input[type='checkbox']").val('');
								// checkboxes
								jQuery("#frmuserfldadd :input[type='checkbox']").attr('checked',false);		
								// list																			
								mgm_custom_field_list();				
							}														
					  }}); // end   		
					  return false;											
				},
				rules: {			
					label: "required",
					'name': "required",						
					type: "required",					
					'options': {required: function(){ return ( (jQuery.inArray(jQuery("#frmuserfldadd select[name='type']").val(), ['select','checkbox','radio']) !=-1) ); }},
					'value': {required: function(){ return ( (jQuery.inArray(jQuery("#frmuserfldadd select[name='type']").val(), ['select','radio','hidden','label','image']) !=-1) ); }}		
				},
				messages: {			
					label: "<?php _e('Please enter label','mgm')?>",
					'name': "<?php _e('Please enter name','mgm')?>",
					type: "<?php _e('Please select a type ','mgm')?>",
					'options': "<?php _e('Please enter options','mgm')?>",
					'value': "<?php _e('Please enter value/default','mgm')?>"
				},
				errorClass: 'invalid',
				errorPlacement:function(error, element) {	
					if(element.is("[name='name']"))
						error.insertAfter(element.next());	
					else	
						error.insertAfter(element);					
				}
			});	
			
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
			//issue#: 353
			mgm_save_editor = function() {			
				if(v_editor && typeof(v_editor) == 'object')				  	
					v_editor.nicInstances[0].saveContent();
			}
			// save: important: the below line is required(issue#: 353 ) 
			jQuery("#save_fields").bind('click', mgm_save_editor);
			
			// bind keyup
			jQuery('#label').bind('keyup', function(){
				jQuery('#name').val(jQuery('#label').val().toString().keyslug())
			});
			// bind blur
			jQuery('#label').bind('blur', function(){
				jQuery('#name').val(jQuery('#label').val().toString().keyslug())
			});		
			
			// bind
			jQuery("#frmuserfldadd :input[name='capture_only']").bind('click', function(){
				if(jQuery(this).attr('checked')){
					jQuery('#capture_field_alias_wrap').fadeIn();
				}else{
					jQuery('#capture_field_alias_wrap').fadeOut();
				}
			});
			
			// bind - issue #973
			jQuery("#frmuserfldadd :input[name='password_min_length']").bind('click', function(){
				if(jQuery(this).attr('checked')){
					jQuery('#password_min_length_field_alias_wrap').fadeIn();
				}else{
					jQuery('#password_min_length_field_alias_wrap').fadeOut();
				}
			});
			// bind - issue #973
			jQuery("#frmuserfldadd :input[name='password_max_length']").bind('click', function(){
				if(jQuery(this).attr('checked')){
					jQuery('#password_max_length_field_alias_wrap').fadeIn();
				}else{
					jQuery('#password_max_length_field_alias_wrap').fadeOut();
				}
			});
			// bind - issue #1573
			jQuery("#frmuserfldadd :input[name='profile_by_membership_types']").bind('click', function(){
				if(jQuery(this).attr('checked')){
					jQuery('#profile_membership_types_field_alias_wrap').fadeIn();
				}else{
					jQuery('#profile_membership_types_field_alias_wrap').fadeOut();
				}
			});
			// bind - issue #1573
			jQuery("#frmuserfldadd :input[name='register_by_membership_types']").bind('click', function(){
				if(jQuery(this).attr('checked')){
					jQuery('#register_membership_types_field_alias_wrap').fadeIn();
				}else{
					jQuery('#register_membership_types_field_alias_wrap').fadeOut();
				}
			});
			// hide all
			mgm_switch_elements('text');
		});	
		//-->	
	</script>