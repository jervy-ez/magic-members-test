<form name="frmpostpackpedit" id="frmpostpackpedit" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=postpack_edit">
	<div class="table widefatDiv">
		<div class="row headrow">
			<div class="cell theadDivCell">
				<b><?php _e('Edit Post Pack','mgm')?></b>
			</div>
		</div>
		<div class="row">
			<div class="cell width20">
				<span class="required-field">
					<b><?php _e('Name','mgm')?>:</b>
				</span> 
			</div>
		</div>	
		<div class="row">	
			<div class="cell textalignleft width80">
				<input type="text" name="name" class="width100" maxlength="150" value="<?php echo $data['postpack']->name?>"/>
			</div>
		</div>
		<div class="row">
			<div class="cell width20">
				<span class="required-field">
					<b><?php _e('Cost','mgm')?>:</b>
				</span>  
			</div>
		</div>	
		<div class="row">	
			<div class="cell textalignleft width80">
				<input type="text" name="cost" size="10" maxlength="20" value="<?php echo $data['postpack']->cost?>"/> <em><?php echo $data['currency']?></em>
			</div>
		</div>
		<div class="row">
			<div class="cell width20">
				<span class="required-field">
					<b><?php _e('Description','mgm')?>:</b>
				</span>  
			</div>
		</div>	
		<div class="row">	
			<div class="cell textalignleft width80">
				<textarea name="description"  class="width100" rows="5"><?php echo $data['postpack']->description?></textarea>
			</div>
		</div>
		<div class="row brBottom">
			<div class="cell">	
				<?php include('product_mapping.php');?>			
			</div>
		</div>	
		<div class="row">
			<div class="cell">
				<div class="floatleft">			
					<input class="button" type="submit" name="save_postpack" value="<?php _e('Save', 'mgm') ?>" />		
				</div>
				<div class="floatright">
					<input class="button" type="button" name="btn_cancel" value="<?php _e('Cancel', 'mgm') ?>" onclick="mgm_postpack_add()"/>
				</div>			
			</div>
		</div>
		
	</div>
	<input type="hidden" name="id" value="<?php echo $data['postpack']->id?>" />
</form>
<script language="javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){   
		// edit : form validation
		jQuery("#frmpostpackpedit").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmpostpackpedit").ajaxSubmit({type: "POST",
				  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=postpack_edit',
				  dataType: 'json',		
				  iframe: false,									 
				  beforeSubmit: function(){	
					// show message
					mgm_show_message('#postpack_manage', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);												
				  },
				  success: function(data){	
					// message																				
					mgm_show_message('#postpack_manage', data);														
					// success	
					if(data.status=='success'){																				
						// load new list	
						mgm_postpack_list();
					}														
				  }}); // end   		
				return false;											
			},
			rules: {			
				name: "required",						
				cost: {required:true, number: true},
				description: "required"	
			},
			messages: {			
				name: "<?php _e('Please enter name','mgm')?>",
				cost: {required:"<?php _e('Please enter cost','mgm')?>",number:"<?php _e('Please enter number only','mgm')?>"},
				description: "<?php _e('Please enter description','mgm')?>"
			},
			errorClass: 'invalid',
			errorPlacement:function(error, element) {				
				if(element.is(":input[name='cost']"))
					error.insertAfter(element.next());										
				else		
					error.insertAfter(element);					
			}
		});	

		jQuery(":checkbox[name^='modules[']").bind('click',function() {		
			var _m = jQuery(this).val().replace('mgm_', '');			
			if(jQuery(this).attr('checked')){				
				jQuery('#settings_postpurchase_package_' + _m).slideDown('slow');
			}else{				
				jQuery('#settings_postpurchase_package_' + _m).slideUp('slow');
			}
		});
	});	
	//-->	
</script>