<!--getresponse main settings-->
<div id="module_settings_<?php echo $data['module']->code?>">
	<form name="frmmod_<?php echo $data['module']->code?>" id="frmmod_<?php echo $data['module']->code?>" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/autoresponders&method=module_settings&module=<?php echo $data['module']->code?>">
		<h3><b><?php _e('Enable/Disable Settings','mgm')?></b></h3>
		<div class="table widefatDiv">
			<div class="row headrow">
				<div class="cell theadDivCell width50 textalignleft ">
		    		<b><?php _e('Setting','mgm')?></b>
				</div>
				<div class="cell theadDivCell width50 textalignleft brCellBorderLeft">
		    		<b><?php _e('Value','mgm')?></b>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell width50 textalignleft ">
					<p><b><?php _e('Enable','mgm'); ?>?</b></p>
				</div>
				<div class="cell textalignleft width50 brCellBorderLeft">
					<select name="enabled" class="width100px">
						<?php echo mgm_make_combo_options(array('Y'=>__('Yes','mgm'),'N'=>__('No','mgm')), $data['module']->is_enabled('string'), MGM_KEY_VALUE)?>
					</select>						
				</div>
			</div>	
		</div>		

		<p><div class="tips width95"><?php _e('Enable/Disable the GetResponse module.','mgm'); ?></div></p>
		<p>&nbsp;</p>
				
		<h3><b><?php _e('Primary Settings','mgm')?></b></h3>
		<div class="table widefatDiv">
			<div class="row headrow">
				<div class="cell theadDivCell width50 textalignleft">
		    		<b><?php _e('GetResponse Field','mgm')?></b>
				</div>
				<div class="cell theadDivCell width50 textalignleft brCellBorderLeft">
		    		<b><?php _e('Value','mgm')?></b>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell width50 textalignleft">
					<b><?php _e('API Key','mgm'); ?></b>
				</div>
				<div class="cell textalignleft width50 brCellBorderLeft">
					<input type="text" name="setting[ref]" value="<?php echo $data['module']->setting['ref']; ?>"  size="40"/>
				</div>
			</div>
			<div class="row brBottom alternate">
				<div class="cell width50 textalignleft">
					<b><?php _e('Campaign Name ( default ) ','mgm'); ?></b>
				</div>
				<div class="cell textalignleft width50 brCellBorderLeft">
					<input type="text" name="setting[category1]" value="<?php echo $data['module']->setting['category1']; ?>" size="40" />
				</div>
			</div>
		</div>		

		<p>&nbsp;</p>		
		
		<h3><b><?php _e('Field Mappings','mgm')?></b></h3>
		
		<div class="table widefatDiv">
			<div class="row headrow">
				<div class="cell theadDivCell width50 textalignleft">
		    		<b><?php _e('GetResponse Field','mgm')?></b>
				</div>
				<div class="cell theadDivCell width50 textalignleft brCellBorderLeft">
		    		<b><?php _e('MagicMembers Field','mgm')?></b>
				</div>
			</div>
			
			<div class="tbodyDiv" id="fieldmap_layers_<?php echo $data['module']->code?>">
				
				<div class="row brBottom <?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
					<div class="cell width50 textalignleft ">
						<b><?php _e('email','mgm')?></b>
					</div>
					<div class="cell textalignleft width50 brCellBorderLeft">
						<b><?php _e('E-mail ( default )','mgm')?></b>
					</div>
				</div>	

				<?php if(count($data['module']->setting['fieldmap'])>0): $layer=1; foreach($data['module']->setting['fieldmap'] as $modulefld=>$mgmfld):?>
				<div class="row brBottom <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="layer<?php echo $layer?>">
					<div class="cell width50 textalignleft ">
						<input type="text" name="setting[fieldmap][]" value="<?php echo $modulefld?>" size="40" />
					</div>
					<div class="cell textalignleft width50 brCellBorderLeft">
						<select name="setting[fieldmap][]" class="width200px">
							<?php if(is_array($data['custom_fields']) && count($data['custom_fields'])>0): foreach($data['custom_fields'] as $field_name=>$field_label):?>
							<option value="<?php echo $field_name?>" <?php echo ($field_name==$mgmfld) ? 'selected' : ''?>><?php _e($field_label,'mgm')?></option>
							<?php endforeach; endif;?>
						</select>	
						<?php if($layer == count($data['module']->setting['fieldmap'])):?>	
						<a class='layer-trig' href="javascript:mgm_create_row('#fieldmap_layers_<?php echo $data['module']->code?>')"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-plus.png"/></a>												
						<?php else:?>
						<a class='layer-trig' href="javascript:mgm_remove_row('#fieldmap_layers_<?php echo $data['module']->code?>', <?php echo $layer?>)"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-cross.png"/></a>
						<?php endif;?>
					</div>
				</div>	
				<?php $layer++; endforeach; else:?>
				<div class="row brBottom <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="layer">
					<div class="cell width50 textalignleft ">
						<input type="text" name="setting[fieldmap][]" value="" size="40" />	
					</div>
					<div class="cell textalignleft width50 brCellBorderLeft">
						<select name="setting[fieldmap][]" class="width200px">
							<?php if(is_array($data['custom_fields']) && count($data['custom_fields'])>0): foreach($data['custom_fields'] as $field_name=>$field_label):?>
							<option value="<?php echo $field_name?>"><?php _e($field_label,'mgm')?></option>
							<?php endforeach; endif;?>
						</select>		
						<a class='layer-trig' href="javascript:mgm_create_row('#fieldmap_layers_<?php echo $data['module']->code?>')"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-plus.png"/></a>						
					</div>
				</div>	
				<?php endif;?>
			</div>
		</div>		

		<p>&nbsp;</p>
		
		<h3><b><?php _e('Membership Mappings','mgm')?></b></h3>
		<div class="table widefatDiv">
			<div class="row headrow">
				<div class="cell theadDivCell width50 textalignleft">
		    		<b><?php _e('GetResponse List/Group','mgm')?></b>
				</div>
				<div class="cell theadDivCell width50 textalignleft brCellBorderLeft">
		    		<b><?php _e('MagicMembers Membership Type','mgm')?></b>
				</div>
			</div>
			
			<div class="tbodyDiv" id="membershipmap_layers_<?php echo $data['module']->code?>">
				<?php if(count($data['module']->setting['membershipmap'])>0): $layer=1; foreach($data['module']->setting['membershipmap'] as $listid=>$ms_type):?>	
				
				<div class="row brBottom <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="layer<?php echo $layer?>">
					<div class="cell width50 textalignleft ">
						<input type="text" name="setting[membershipmap][]" value="<?php echo $listid?>" size="40" />
					</div>
					<div class="cell textalignleft width50 brCellBorderLeft">
						<select name="setting[membershipmap][]" class="width200px">
							<?php if(is_array($data['membership_types']) && count($data['membership_types'])>0): foreach($data['membership_types'] as $ms_code=>$ms_name):?>
							<option value="<?php echo $ms_code?>" <?php echo ($ms_code==$ms_type) ? 'selected' : ''?>><?php _e($ms_name,'mgm')?></option>
							<?php endforeach; endif;?>
						</select>		
						<?php if($layer == count($data['module']->setting['membershipmap'])):?>	
						<a class='layer-trig' href="javascript:mgm_create_row('#membershipmap_layers_<?php echo $data['module']->code?>')"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-plus.png"/></a>												
						<?php else:?>
						<a class='layer-trig' href="javascript:mgm_remove_row('#membershipmap_layers_<?php echo $data['module']->code?>', <?php echo $layer?>)"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-cross.png"/></a>
						<?php endif;?>
					</div>
				</div>
				<?php $layer++; endforeach; else:?>	
				<div class="row brBottom <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="layer">
					<div class="cell width50 textalignleft ">
						<input type="text" name="setting[membershipmap][]" value="" size="40" />
					</div>
					<div class="cell textalignleft width50 brCellBorderLeft">
						<select name="setting[membershipmap][]" class="width200px">							
							<?php if(is_array($data['membership_types']) && count($data['membership_types'])>0): foreach($data['membership_types'] as $ms_code=>$ms_name):?>
							<option value="<?php echo $ms_code?>"><?php _e($ms_name,'mgm')?></option>
							<?php endforeach; endif;?>
						</select>		
						<a class='layer-trig' href="javascript:mgm_create_row('#membershipmap_layers_<?php echo $data['module']->code?>')"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-plus.png"/></a>
					</div>
				</div>				
				<?php endif;?>	
			</div>
		</div>		

		<p>&nbsp;</p>			
		<p class="submit">						
			<input class="button" type="submit" name="btn_save" value="<?php _e('Update Settings', 'mgm') ?>" />
		</p>
		<input type="hidden" name="update" value="true" />
		<input type="hidden" name="setting_form" value="main" />
	</form>
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
					// update current sysmbol
					mgm_active_module_symbol('<?php echo $data['module']->code?>');												
				  }}); // end   		
				  return false;											
			},
			rules: {			
				'setting[category1]': 'required',
				'setting[ref]': 'required'
			},
			messages: {		
				'setting[category1]': '<?php _e('Please enter getresponse category.','mgm')?>',
				'setting[ref]': '<?php _e('Please enter getresponse API Key.','mgm')?>'
			},
			errorClass: 'invalid'
		});							
	});	
	//-->	
</script>