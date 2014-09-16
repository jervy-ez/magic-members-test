<form name="frmcoupedit" id="frmcoupedit" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons&method=edit" class="marginpading0px">
	<div class="table widefatDiv">
		<div class="row headrow">
			<div class="cell theadDivCell">
				<b><?php _e('Edit Coupon','mgm')?></b>
			</div>
		</div>
		<div class="row">
			<div class="cell">
				<span class="required">
					<b><?php _e('Code','mgm')?>:</b>
				</span> 
			</div>
		</div>
		<div class="row">	
			<div class="cell">
				<input type="text" name="name" size="80" maxlength="150" value="<?php echo $data['coupon']->name?>"/>
			</div>
		</div>
		<div class="row">
			<div class="cell">
				<span class="required">
					<b><?php _e('Value','mgm')?>:</b>
				</span> 
			</div>
		</div>
		<div class="row">	
			<div class="cell">
				<?php include('value_options.php')?>	
			</div>
		</div>
		<div class="row">
			<div class="cell">
				<span class="required">
					<b><?php _e('Description','mgm')?>:</b>
				</span> 
			</div>
		</div>
		<div class="row">	
			<div class="cell">
				<textarea name="description" cols="80" rows="5"><?php echo $data['coupon']->description?></textarea>
			</div>
		</div>
		<div class="row">
			<div class="cell">
				<span class="required">
					<b><?php _e('Usage Limit','mgm')?>:</b>
				</span> 
			</div>
		</div>
		<div class="row">	
			<div class="cell">
				<input type="text" name="use_limit" size="5" maxlength="10" value="<?php echo $data['coupon']->use_limit?>" <?php echo is_null($data['coupon']->use_limit) ? 'disabled': ''?>/>&nbsp;
				<input type="checkbox" name="use_unlimited" <?php echo is_null($data['coupon']->use_limit) ? 'checked': ''?>/> <?php _e('Unlimited','mgm')?>?
				<div id="e_use_limit"></div>
			</div>
		</div>
		<div class="row">
			<div class="cell">
				<span class="required">
					<b><?php _e('Expire Date','mgm') ?>:</b>
				</span> 
			</div>
		</div>
		<div class="row brBottom">	
			<div class="cell">
				<?php
				$expire_dt = '';
				if(strtotime($data['coupon']->expire_dt) > 0 ) :					
					 $expire_dt = mgm_get_datepicker_format('date', date('Y-m-d', strtotime($data['coupon']->expire_dt)));
				endif;?>
				<input name="expire_dt" id="expire_dt" type="text" size="12" value="<?php echo $expire_dt; ?>" />
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
					<input class="button" type="submit" name="save_coupon" value="<?php _e('Save', 'mgm') ?>" />		
				</div>
				<div class="floatright">
					<input class="button" type="button" name="btn_cancel" value="<?php _e('Cancel', 'mgm') ?>" onclick="mgm_coupon_add()"/>
				</div>	
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="<?php echo $data['coupon']->id?>" />
	<input type="hidden" name="used_count" value="<?php echo $data['coupon']->used_count?>" />	
</form>
<script language="javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){   
		// edit : form validation
		jQuery("#frmcoupedit").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmcoupedit").ajaxSubmit({type: "POST",
				  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons&method=edit',
				  dataType: 'json',		
				  iframe: false,									 
				  beforeSubmit: function(){	
					// show message
					mgm_show_message('#coupon_manage', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);												
				  },
				  success: function(data){	
						// message																				
						mgm_show_message('#coupon_manage', data);																										
						// success	
						if(data.status=='success'){																										
							// load new list	
							mgm_coupon_list();											
						}															
				  }}); // end   		
				return false;											
			},
			rules: {			
				name: "required",						
				value: "required",				
				description: "required",
				use_limit:{required: function(){ return jQuery("#frmcoupadd :input[name='use_unlimited']").attr('checked') },digits:true}		
			},
			messages: {			
				name: "<?php _e('Please enter code','mgm')?>",
				value: "<?php _e('Please enter value','mgm')?>",
				description: "<?php _e('Please enter description','mgm')?>",
				use_limit:{required: "<?php _e('Please enter user limit','mgm')?>",digits:"<?php _e('Please enter number for limit','mgm')?>"}
			},
			errorClass: 'invalid',
			errorPlacement:function(error, element) {										
				if(element.attr('name') == 'use_limit')
					error.appendTo('#e_use_limit');
				else
					error.insertAfter(element);					
			}
		});	
		
		// use limit
		jQuery("#frmcoupedit :input[name='use_unlimited']").bind('click', function(){
			if(jQuery(this).attr('checked')){
				jQuery("#frmcoupedit :input[name='use_limit']").val('').attr('disabled', true);
			}else{
				jQuery("#frmcoupedit :input[name='use_limit']").attr('disabled', false);
			}
		});
		// date picker
		mgm_date_picker("#frmcoupedit :input[name='expire_dt']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});
	});	
	//-->	
</script>