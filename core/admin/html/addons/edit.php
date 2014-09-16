<form name="frmaddonedit" id="frmaddonedit" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=edit" class="marginpading0px">
	<div class="table widefatDiv">
		<div class="row headrow">
			<div class="cell theadDivCell">
				<b><?php _e('Edit Addon','mgm')?></b>
			</div>
		</div>
		<div class="row">
			<div class="cell">
				<span class="required"><b><?php _e('Name','mgm')?>:</b></span> 
			</div>
		</div>
		<div class="row">	
			<div class="cell">
				<input type="text" name="name" id="name" size="80" maxlength="150" value="<?php echo $data['addon']->name?>"/>
			</div>
		</div>
		<div class="row">
			<div class="cell">
				<span class="required"><b><?php _e('Description','mgm')?>:</b></span>
			</div>
		</div>	
		<div class="row">	
			<div class="cell">
				<textarea name="description" id="description" cols="80" rows="5"><?php echo $data['addon']->description?></textarea>
			</div>
		</div>				
		<div class="row">
			<div class="cell"><b><?php _e('Expire Date','mgm') ?>:</b></div>
		</div>	
		<div class="row">	
			<div class="cell">				
				<?php
				$expire_dt = '';
				// check
				if(strtotime($data['addon']->expire_dt) > 0 ) :										 
					 $expire_dt = mgm_get_datepicker_format('date', date('Y-m-d', strtotime($data['addon']->expire_dt)));
				endif;?>
				<input name="expire_dt" id="expire_dt" type="text" size="12" value="<?php echo $expire_dt; ?>" />
			</div>
		</div>	
		<div class="row">
			<div class="cell">
				<span class="required"><b><?php _e('Options','mgm')?>:</b></span>
			</div>
		</div>	
		<div class="row">
			<div class="cell">
				<?php include('option_prices.php')?>											
			</div>
		</div>		
		<div class="row">
			<div class="cell">
				<div class="floatleft">			
					<input class="button" type="submit" name="save_addon" value="<?php _e('Save', 'mgm') ?>" />
				</div>	
				<div class="floatright">
					<input class="button" type="button" name="btn_cancel" value="<?php _e('Cancel', 'mgm') ?>" onclick="mgm_addon_add()"/>
				</div>		
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?php echo $data['addon']->id?>" />	
</form>
<script language="javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){   
		// edit : form validation
		jQuery("#frmaddonedit").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmaddonedit").ajaxSubmit({type: "POST",
				  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=edit',
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
							// load new list	
							mgm_addon_list();											
						}																	
				  }}); // end   		
				return false;											
			},
			rules: {			
				name: "required",											
				description: "required"
			},
			messages: {			
				name: "<?php _e('Please enter code','mgm')?>",				
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
		mgm_date_picker("#frmaddonedit :input[name='expire_dt']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});
	});	
	//-->	
</script>