<form name="frmrestapilevels" id="frmrestapilevels" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi_level_edit" class="marginpading0px">

	<div class="table widefatDiv">
		<div class="row headrow">
			<div class="cell theadDivCell">
	    		<b><?php _e('Edit Level','mgm')?></b>
			</div>
		</div>
		<div class="row brBottom">
			<div class="cell width120px">
				<span class="required"><?php _e('Level','mgm')?></span>: 
			</div>
			<div class="cell textalignleft">
				<input type="text" name="level" size="5" maxlength="5" value="<?php echo $data['level']->level?>"/>
			</div>
		</div>
		<div class="row brBottom">
			<div class="cell width120px">
				<span class="required"><?php _e('Name','mgm')?></span>: 
			</div>
			<div class="cell textalignleft">
				<input type="text" name="name" size="100" maxlength="100" value="<?php echo $data['level']->name?>"/>
			</div>
		</div>
		<div class="row brBottom">
			<div class="cell width120px">
				<span class="required"><?php _e('Permissions','mgm')?></span>:
			</div>
			<div class="cell textalignleft">
				<input type="radio" name="permission_type" value="full" <?php echo ($data['permission_type']=='full') ? 'checked="checked"': ''?>> <?php _e('Full','mgm')?>
				<input type="radio" name="permission_type" value="limited" <?php echo ($data['permission_type']=='limited') ? 'checked="checked"': ''?>> <?php _e('Limited','mgm')?>
				<div id="permissions_limited" <?php echo ($data['permission_type']=='limited') ? '' : 'class="displaynone"'?>>
					<input type="checkbox" name="permissions[]" value="members_get" <?php echo in_array('members_get',$data['permissions']) ? 'checked="checked"' :''?>> <?php _e('Get Member By ID')?>
				</div>
			</div>
		</div>
		<div class="row brBottom">
			<div class="cell width120px"><?php _e('Limits','mgm')?>: </div>
			<div class="cell textalignleft">
				<input type="text" name="limits" <?php echo (is_null($data['level']->limits)) ? 'disabled="disabled"' : ''?>  size="5" maxlength="10" value="<?php echo $data['level']->limits?>"/>
				&nbsp;<input type="checkbox" name="limits_unlimited" <?php echo (is_null($data['level']->limits)) ? 'checked="checked"' : ''?>/> <?php _e('Unlimited','mgm')?>?
				<div id="e_limits"></div>
			</div>
		</div>
		<div class="row brBottom">
			<div class="cell textaligncenter">
					<div class="floatleft">			
						<input class="button" type="submit" name="save_level" value="<?php _e('Save', 'mgm') ?>" />	
						<input class="button" type="button" name="cancel_save_level" value="<?php _e('Cancel', 'mgm') ?>" onclick="mgm_load_api_levels()"/>		
					</div>		

			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?php echo $data['level']->id?>" />
</form>
<script language="javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){   
		// add : form validation
		jQuery("#frmrestapilevels").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmrestapilevels").ajaxSubmit({type: "POST",
				  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi_level_edit',
				  dataType: 'json',				
				  iframe: false,							 
				  beforeSubmit: function(){	
					// show message
					mgm_show_message('#restapi_access_levels', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);												
				  },
				  success: function(data){	
				  		// message																				
						mgm_show_message('#restapi_access_levels', data);
						// success	
						if(data.status=='success'){
							// load new list														
							mgm_load_api_levels();										
						}														
				  }}); // end   		
				return false;											
			},
			rules: {			
				level: {required: true, digits: true},
				name: "required",
				permission_type: "required",
				limits:{required: function(){ return jQuery("#frmrestapilevels :input[name='limits_unlimited']").attr('checked') }, digits:true}	
			},
			messages: {			
				level: {required: "<?php _e('Please enter level','mgm')?>", digits: "<?php _e('Please enter numeric value for level','mgm')?>"},
				name: "<?php _e('Please enter name','mgm')?>",
				permission_type: "<?php _e('Please select permissions','mgm')?>",
				limits:{required: "<?php _e('Please enter limits','mgm')?>", digits: "<?php _e('Please enter number for limits','mgm')?>"}	
			},
			errorClass: 'invalid',
			errorPlacement:function(error, element) {	
				if(element.attr('name') == 'limits')
					error.appendTo('#e_limits');
				else										
					error.insertAfter(element);					
			}
		});	
		// limits
		jQuery("#frmrestapilevels :input[name='limits_unlimited']").bind('click', function(){
			if(jQuery(this).attr('checked')){
				jQuery("#frmrestapilevels :input[name='limits']").val('').attr('disabled', true);
			}else{
				jQuery("#frmrestapilevels :input[name='limits']").attr('disabled', false);
			}
		});		
		
		// permissions
		jQuery("#frmrestapilevels :input[name='permission_type']").bind('click', function(){
			if(jQuery(this).val() == 'limited'){
				jQuery("#permissions_limited").show();
			}else{
				jQuery("#permissions_limited").hide();
			}
		});
	});	
	//-->	
</script>