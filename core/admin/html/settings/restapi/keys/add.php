<form name="frmrestapikeys" id="frmrestapikeys" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi_key_add" class="marginpading0px">
		<div class="table widefatDiv">
	  		<div class="row headrow">
	    		<div class="cell theadDivCell">
		    		<b><?php _e('Add Key','mgm')?></b>
				</div>
			</div>
	  		<div class="row brBottom">
	    		<div class="cell width120px">
		    		<span class="required"><?php _e('API Key','mgm')?></span>: 
				</div>
	    		<div class="cell textalignleft">
		    		<input type="text" name="api_key" size="40" maxlength="40" value="<?php echo mgm_create_token()?>"/>					
				</div>
			</div>
	  		<div class="row brBottom">
	    		<div class="cell width120px">
		    		<span class="required"><?php _e('Level','mgm')?></span>: 
				</div>
	    		<div class="cell textalignleft">
		    		<select name="level" class="width50px">
						<?php echo mgm_make_combo_options($data['levels'], 1, MGM_VALUE_ONLY)?>
					</select>					

				</div>
			</div>
	  		<div class="row brBottom">
	    		<div class="cell">
					<div class="floatleft">			
						<input class="button" type="submit" name="save_key" value="<?php _e('Save', 'mgm') ?>" />	
						<input class="button" type="button" name="cancel_save_key" value="<?php _e('Cancel', 'mgm') ?>" onclick="mgm_load_api_keys()"/>		
					</div>		
				</div>
			</div>
		</div>	
	
</form>
<script language="javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){   
		// add : form validation
		jQuery("#frmrestapikeys").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmrestapikeys").ajaxSubmit({type: "POST",
				  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi_key_add',
				  dataType: 'json',				
				  iframe: false,							 
				  beforeSubmit: function(){	
					// show message
					mgm_show_message('#restapi_access_keys', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);												
				  },
				  success: function(data){	
				  		// message																				
						mgm_show_message('#restapi_access_keys', data);
						// success	
						if(data.status=='success'){
							// load new list														
							mgm_load_api_keys();										
						}														
				  }}); // end   		
				return false;											
			},
			rules: {			
				api_key: "required",						
				level: "required"
			},
			messages: {			
				api_key: "<?php _e('Please enter key','mgm')?>",
				name: "<?php _e('Please enter name','mgm')?>"
			},
			errorClass: 'invalid',
			errorPlacement:function(error, element) {	
				error.insertAfter(element);					
			}
		});			
	});	
	//-->	
</script>