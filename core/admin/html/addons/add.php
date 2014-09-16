<form name="frmaddonadd" id="frmaddonadd" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=add" class="marginpading0px">
	<div class="table widefatDiv">
		<div class="row headrow">
			<div class="cell theadDivCell">
				<b><?php _e('Add Addon','mgm')?></b>
			</div>
		</div>
		<div class="row">
			<div class="cell">
				<span class="required"><b><?php _e('Name','mgm')?>:</b></span> 
			</div>
		</div>
		<div class="row">	
			<div class="cell">
				<input type="text" name="name" id="name" size="80" maxlength="150" value=""/>
			</div>
		</div>
		<div class="row">
			<div class="cell">
				<span class="required"><b><?php _e('Description','mgm')?>:</b></span>
			</div>
		</div>	
		<div class="row">	
			<div class="cell">
				<textarea name="description" id="description" cols="80" rows="5"></textarea>
			</div>
		</div>				
		<div class="row">
			<div class="cell"><b><?php _e('Expire Date','mgm') ?>:</b></div>
		</div>	
		<div class="row">	
			<div class="cell">
				<input name="expire_dt" id="expire_dt" type="text" size="12" value="" />
			</div>
		</div>	
		<div class="row">
			<div class="cell">
				<span class="required"><b><?php _e('Options','mgm')?>:</b></span>
			</div>
		</div>	
		<div class="row brBottom">
			<div class="cell">
				<?php include('option_prices.php')?>									
			</div>
		</div>			
		<div class="row">
			<div class="cell">
				<div class="floatleft">			
					<input class="button" type="submit" name="save_addon" value="<?php _e('Save', 'mgm') ?>" />
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
		jQuery("#frmaddonadd").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmaddonadd").ajaxSubmit({type: "POST",
				  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=add',
				  dataType: 'json',				
				  iframe: false,							 
				  beforeSubmit: function(){	
					// show message
					mgm_show_message('#addon_manage', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);												
				  },
				  success: function(data){	
				  		// message																				
						mgm_show_message('#addon_manage', data);
						// success	
						if(data.status=='success'){
							// clear fields
							jQuery("#frmaddonadd :input").not(":input[type='hidden'][type='submit'][type='checkbox']").val('');	
							// load new list														
							mgm_addon_list();										
						}
						// reload form
						mgm_addon_add();														
				  }}); // end   		
				return false;											
			},
			rules: {			
				name: "required",														
				description: "required"
			},
			messages: {			
				name: "<?php _e('Please enter name','mgm')?>",				
				description: "<?php _e('Please enter description','mgm')?>"	
			},
			errorClass: 'invalid',
			errorPlacement:function(error, element) {	
				error.insertAfter(element);					
			}
		});		
		// call addon options row manage
		mgm_addon_option_row_manage('#addon_manage');
		// date picker
		mgm_date_picker("#frmaddonadd :input[name='expire_dt']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});
	});	
	//-->	
</script>