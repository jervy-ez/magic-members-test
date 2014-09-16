<div class="table widefatDiv width100">
	<div class="row headrow">
		<div class="cell theadDivCell">
			<b>
				<?php switch($data['role_type']): 
						case 'mgm': _e('Magic Members Roles','mgm'); break;  
						case 'others': _e('Other Roles','mgm');break; 
						case 'default':  _e('System Roles','mgm');break; 
				endswitch; ?>
			</b>
		</div>
	</div>
	<div class="row">
		<div class="cell form_div_font">		
			<div class='mgm'>
				<form name="frmrolelist<?php echo $data['role_type']; ?>" 
					id="frmrolelist<?php echo $data['role_type']; ?>" 
					method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=roles_capabilities_edit" class="marginpading0px">
				<div id="roles_list_div_<?php echo $data['role_type']; ?>">
				<?php if(!empty($data['roles'])): foreach ($data['roles'] as $key => $role):?>
					<h3><a href="#"><b><?php echo mgm_stripslashes_deep($role['name']); ?></b></a></h3>												
					<div>
						<div class="table">
							<div class="row">
								<div class="cell">
									<p><b><?php _e('Role','mgm')?>:</b>
									<input type="text" name="rolename[<?php echo $role['role'] ?>]" size="80" maxlength="100" value="<?php echo mgm_stripslashes_deep($role['name']); ?>" <?php if($role['is_systemrole']) echo " disabled='disabled' "; ?>/> 
									<?php if($role['is_systemrole']): ?><span class="mgm_system_defined"><?php _e('System defined.','mgm') ?></span><?php endif; ?>
									<?php if($role['is_systemrole']): ?>
									<input type="hidden" name="rolename[<?php echo $role['role'] ?>]" value="<?php echo mgm_stripslashes_deep($role['name']); ?>" />
									<?php endif; ?>
								</div>											
							</div>
							<div class="row brBottom">
								<div class="cell">

									<p><b><?php _e('Capabilities','mgm')?>:</b></p>
									<?php if(!empty($role['capabilities'])): ?>
									<div class="capabilities">								
								
										<div class="table form_div_font widefatDiv">
										
											<?php foreach ($role['capabilities'] as $i => $cap): $mod = ($i%3); 
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
													}															
												?>
												<div class="cell width30" <?php if(!isset($role['capabilities'][$i+1]) && $mod != 2 ) { echo 'colspan="'. (3 - $mod).'" '; } ?> ><input value="<?php echo $cap['capability']; ?>" <?php if($cap['belongsto']) echo " checked='checked '"; ?> type="checkbox" name="chk_capability[<?php echo $role['role'] ?>][]" id="chk_cap_<?php echo $cap['capability']; ?>"> <span <?php echo $cap_style ?>><?php if($cap['belongsto']) echo "<strong>"; echo $cap['name']; if($cap['belongsto']) echo "</strong>"; ?></span>&nbsp;<?php echo $cap_desc; ?></div>

											<?php if($mod == 2) echo "</div>"; endforeach; ?>
											<?php if($mod != 2) echo "</div>";?>
											
										</div>
									</div><br />
									<?php else: ?>
									<strong><?php _e('No Capabilities Found','mgm'); ?></strong>
									<?php endif; ?>
									<label 	id="labelchk_<?php echo $role['role'] ?>" 
											class="displaynone" 
											for="chk_capability[<?php echo $role['role'] ?>]"></label>
								</div>
							</div>																
								<div class="row">
									<div class="cell">
				
									<div class="mgm_save_roles_div">			
										<input class="button" type="submit" 
											id="<?php echo $role['role'] ?>" 
											name="save_roles" value="<?php _e('Save', 'mgm') ?>" />		
									</div>	
									<?php if(!$role['is_systemrole']): ?>
									<div class="mgm_cancel_roles_div">																									<input 	class="button" 
										type="button" 
										id="delete_roles" 
										onclick="delete_role_<?php echo $data['role_type']; ?>('<?php echo $role['role']; ?>')" 
										name="cancel_roles" 
										value="<?php _e('Delete', 'mgm') ?>" />																				
									</div>
									<?php endif; ?>
									<div class="mgm_movie_roles_div">	
										<?php _e('Move this role\'s users to','mgm'); ?>:&nbsp;
										<select name="reassign_<?php echo $role['role']; ?>" 
												id="reassign_<?php echo $role['role']; ?>" class="width25;">
										<?php
										$arr_options = $data['roles'];
										if($data['role_type'] == 'others') 
											$arr_options = array_merge($data['default_roles'], $arr_options); 
										foreach($arr_options as $rn => $arr_role):
											if( !in_array($arr_role['role'], array($role['role'], $data['admin_role']))):?>
												<option value="<?php echo mgm_stripslashes_deep($arr_role['role']); ?>">
												<?php echo mgm_stripslashes_deep($arr_role['name']); ?></option>
										<?php endif; endforeach;?>
										</select>
										<input class="button" type="button" 
										id="move_users" 
										onclick="move_users_<?php echo $data['role_type']; ?>('<?php echo $role['role']; ?>')" 
										name="move_users" 
										value="<?php _e('Move Users', 'mgm') ?>" />												
									</div>	
												
								</div>											
							</div>
						</div>

					</div>													
					<?php endforeach; else:?>
						<strong><?php _e('No Roles Found','mgm'); ?></strong>
					<?php endif; ?>
				</div>
				<input type="hidden" name="role_type" value="<?php echo $data['role_type']; ?>">				
				<input type="hidden" id="selected_role_<?php echo $data['role_type']; ?>" name="selected_role" value="">	
				</form>
			</div>
		</div>
	</div>
</div>
<script language="javascript">
	<!--	
	// onready	
	jQuery(document).ready(function() {		
		jQuery('.button').each(
			function() {			
				jQuery(this).bind('click',function(){				
					jQuery('#selected_role_<?php echo $data['role_type']; ?>').val(this.name == 'save_roles' ? this.id : '');
				});
			}
		);
		
		// edit : form validation	
		jQuery.validator.addMethod('validateRole<?php echo $data['role_type']; ?>', function(value, element) {
			var selected_role = jQuery('#selected_role_<?php echo $data['role_type']; ?>').val();
			if('rolename['+selected_role+']' == element.name && jQuery.trim(value) == '') {
				return false;
			}			
			return true;
		},'<?php _e('Please enter Role','mgm');?>'
		);
		
		jQuery.validator.addMethod('validateCaps<?php echo $data['role_type']; ?>', function(value, element) {
			var selected_role = jQuery('#selected_role_<?php echo $data['role_type']; ?>').val();
			if('chk_capability['+selected_role+'][]' == element.name ) {
				cchecked = false;									
				jQuery(":input[name^='chk_capability["+selected_role+"]']:checked").each(function() {
					cchecked = true;}
				);
				return cchecked;
			}			
			return true;
		},'<?php _e('Please select a Capability','mgm');?>'
		);
		
		jQuery('#frmrolelist<?php echo $data['role_type']; ?>').validate({														
			submitHandler: function(form) {
				var selected_role = jQuery('#selected_role_<?php echo $data['role_type']; ?>').val();
				jQuery(form).ajaxSubmit({type: "POST",
				  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=roles_capabilities_edit',
				  dataType: 'json',		
				  iframe: false,				  									 
				  beforeSubmit: function(){	
				  	// clear
				  	clear_message_divs(); 	
					// show message
					mgm_show_message('#roles_capabilities_list_message_<?php echo $data['role_type']; ?>', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);	
				  },
				  success: function(data){
					// success	
					if(data.status == 'success'){																										
						// load new list							
						load_roles_capabilities_<?php echo $data['role_type']; ?>();											
					}		
					// show message
					mgm_show_message('#roles_capabilities_list_message_<?php echo $data['role_type']; ?>', data);	
				  }}); // end   		
				return false;											
			},
			rules: {
				<?php 
				if(!empty($data['roles'])): foreach ($data['roles'] as $key => $role): ?>	
				'rolename[<?php echo $role['role'] ?>]': {validateRole<?php echo $data['role_type']; ?>: true},					
				'chk_capability[<?php echo $role['role'] ?>][]': {validateCaps<?php echo $data['role_type']; ?>: true},					
				<?php endforeach; endif;?>
			},
			messages: {	},
			errorClass: 'invalid',
			errorPlacement:function(error, element) {										
				//error.insertAfter(element);	
				var selected_role = jQuery('#selected_role_<?php echo $data['role_type']; ?>').val();		
				if(element.attr('name') == 'rolename['+selected_role+']')
					error.insertAfter(element);					
				else {
					error.insertAfter(jQuery('#labelchk_'+selected_role));	
				}		
			}
		}
		);
	});	
	var delete_role_<?php echo $data['role_type']; ?> = function(role) {
		clear_message_divs();
		var reassign = jQuery('#reassign_'+role).val();			
		if(confirm('<?php _e('Are you sure, this role will be permanently deleted and its users will be moved to:','mgm');?>'+' "'+jQuery('#reassign_'+role+' :selected').text()+'"?')) {
			jQuery.ajax({	url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=roles_capabilities_delete',
							dataType: 'json',
							type:'POST',
							data: 'role='+escape(role)+'&new_role='+reassign+'&role_type=<?php echo $data['role_type']; ?>',
							beforeSubmit: function(){	
								// clear
								// clear_message_divs(); 	
								// show message
								mgm_show_message('#roles_capabilities_list_message_<?php echo $data['role_type']; ?>', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);	
							},
							success: function(data) {
								// success									
								if(data.status == 'success') {
									// load new list	
									load_roles_capabilities_<?php echo $data['role_type']; ?>();	
								}
								// show message
								mgm_show_message('#roles_capabilities_list_message_<?php echo $data['role_type']; ?>', data);	
							},
							failure: function() {
								
							}
						});
			}
		}
		
		var move_users_<?php echo $data['role_type']; ?> = function(role){
			clear_message_divs();
			var reassign = jQuery('#reassign_'+role).val();			
			if(confirm('<?php _e('Are you sure, this role\\\'s users will be permanently moved to:','mgm');?>'+' "'+jQuery('#reassign_'+role+' :selected').text()+'"?')) {
				jQuery.ajax({	url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=roles_capabilities_move_users',
								dataType: 'json',
								type:'POST',
								data: 'role='+escape(role)+'&new_role='+reassign+'&role_type=<?php echo $data['role_type']; ?>',
								beforeSubmit: function(){	
									// clear
									// clear_message_divs(); 	
									// show message
									mgm_show_message('#roles_capabilities_list_message_<?php echo $data['role_type']; ?>', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);	
								},
								success: function(data) {	
									// success								
									if(data.status == 'success') {
										// load new list	
										load_roles_capabilities_<?php echo $data['role_type']; ?>();	
									}
									// show message
									mgm_show_message('#roles_capabilities_list_message_<?php echo $data['role_type']; ?>', data);	
								},
								failure: function() {
									
								}
							});
				}
		};
	//-->	
</script>