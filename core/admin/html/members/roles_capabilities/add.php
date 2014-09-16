
<div class="table widefatDiv width100">
	<div class="row headrow">
		<div class="cell theadDivCell">
			<b><?php _e('Create New Role','mgm')?></b>
		</div>
	</div>
	<div class="row">
		<div class="cell">
		
			<div class='mgm'>
				<form name="frmroleadd" id="frmroleadd" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=roles_capabilities_add" class="marginpading0px">
					
				<div class="table widefatDiv width100">
					<div class="row brBottom">
						<div class="cell">
							<p><b><?php _e('Role','mgm')?>:</b>
								<input 	type="text" name="rolename" size="80" maxlength="100" 
										value="<?php if(isset($data['rolename'])) echo $data['rolename']; ?>"/>
							</p>
						</div>
					</div>
					<div class="row brBottom">
						<div class="cell">
							<p><b><?php _e('Capabilities','mgm')?>:</b></p>
							<div class="capabilities" class="width100">

								<div class="table widefatDiv width100">
									<?php foreach ($data['capabilities'] as $i => $cap): $mod = ($i%3); 
										if($mod == 0) echo "<div class='row brBottom'>";
										$cap_style = '';
										$cap_desc = '';
										if (strtolower(substr($cap['capability'], 0,3)) == 'mgm') {
											$cap_style = "style=\"color:blue;";
											$cap_desc = "<span style=\"font-size:8pt\">";
											// use different font size according to hierarchy
											switch ($data['mgm_cap_hierarchy'][$cap['capability']]) {
												// Main plugin link 
												case 'root':
													//$cap_style .= ";font-size:12pt;";
													$cap_desc .= ' [Plugin link]';
													break;
												//Primary menu - vertical	
												case 'primary':
													$cap_style .= ";font-size:9pt;";
													$cap_desc .= ' [Main menu]';
													break;
												//Seconadary menu - Horizontal		
												case 'secondary':
													$cap_style .= ";font-size:9pt;";
													$cap_desc .= ' [Sub menu]';
													break;
												// Admin wdgets		
												case 'admin widget':
													$cap_style .= ";font-size:9pt;";
													$cap_desc .= ' [Admin widget]';	
													break;	
											}
											$cap_style .= "\"";
											$cap_desc .= "</span>";
										}												?>
								
										<div class="cell width30" <?php if(!isset($data['capabilities'][$i+1]) && $mod != 2 ) { echo 'colspan="'. (3 - $mod).'" '; } ?>>
											<input value="<?php echo $cap['capability']; ?>" 
														 <?php if(isset($data['posted_capabilities']) && 
														 		in_array($cap['capability'], $data['posted_capabilities'])) echo " checked='checked '"; ?> 
														 		type="checkbox" 
														 		name="chk_capability[]" 
														 		id="chk_cap_<?php echo $cap['capability']; ?>"> 
												<span <?php echo $cap_style; ?>> <?php echo $cap['name']; ?></span>&nbsp;<?php echo $cap_desc; ?>
										</div>
										<?php if($mod == 2) echo "</div>";endforeach ?>
										<?php if($mod != 2) echo "</div>";?>
									</div>
								</div>										

							</div><br />
							<label id="labelchk" class="displaynone;" for="chk_capability[]"></label>
						
						</div>
					</div>
					<div class="row">
						<div class="cell">
							<div class="floatleft">			
								<input 	class="button" type="submit" 
										name="add_roles" value="<?php _e('Add', 'mgm') ?>" />		
							</div>	
							<div class="floatright">			
								<input 	class="button" type="button" id="cancel_roles" 
										name="cancel_roles" value="<?php _e('Cancel', 'mgm') ?>" />		
							</div>	
						</div>
					</div>
				</div>
				</form>
			</div>

		</div>
	</div>
</div>
<script language="javascript">
	<!--	
	// onready	
	jQuery(document).ready(function(){  
		jQuery('#cancel_roles').bind('click', load_roles_capabilities_add);		 
		// edit : form validation		
		jQuery('#frmroleadd').validate({														
			submitHandler: function(form) {				
				jQuery(form).ajaxSubmit({type: "POST",
				  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=roles_capabilities_add',
				  dataType: 'json',		
				  iframe: false,									 
				  beforeSubmit: function(){	
					// clear
					clear_message_divs();
					// show message
					mgm_show_message('#roles_capabilities_add_message', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);					
				  },
				  success: function(data){																			
					// success	
					if(data.status=='success'){																										
						// reset form
						load_roles_capabilities_add();	
						//reload role list
						load_roles_capabilities_mgm();			
					}
					// show message		
					mgm_show_message('#roles_capabilities_add_message', data);																
				  }}); // end   		
				return false;											
			},
			rules: {			
				rolename: "required",
				'chk_capability[]': {required: true, minlength: 1}					
			},
			messages: {			
				rolename: "<?php _e('Please enter Role','mgm')?>",
				'chk_capability[]': {required: '<?php _e('Please select a Capability.','mgm')?>'}							
			},
			errorClass: 'invalid',
			errorPlacement:function(error, element) {	
				if(element.attr('name') == 'rolename')
					error.insertAfter(element);					
				else {
					error.insertAfter(jQuery('#labelchk'));	
				}
			}
		}
	);			
	});	
	//-->	
</script>