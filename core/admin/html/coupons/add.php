<form name="frmcoupadd" id="frmcoupadd" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons&method=add" class="marginpading0px">
	<div class="table widefatDiv">
		<div class="row headrow">
			<div class="cell theadDivCell">
				<b><?php _e('Add Coupon','mgm')?></b>
			</div>
		</div>
		<div class="row" >
			<div class="cell">&nbsp;</div>
		</div>		
		<div class="row">
			<div class="cell"> <b>&nbsp;<?php _e('Genarate coupon ','mgm')?> : </b>
				<select name="coupon_choice" id="coupon_choice" >		
					<?php echo mgm_make_combo_options(array('single'=>__('single','mgm'),'multiple'=>__('multiple','mgm')));?>		
				</select>
			</div>
		</div>		
		<div class="row">
			<div class="cell">
				<span class="required">
					<b id="coupon_choice_label"><?php _e('Coupon code : ','mgm')?></b>
				</span> 
			</div>
		</div>
		<div class="row">	
			<div class="cell">
				<input type="text" name="name" size="80" maxlength="150" value=""/>
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
				<textarea name="description" cols="80" rows="5"></textarea>
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
				<input type="text" name="use_limit" disabled="disabled" size="5" maxlength="10" value=""/>
				&nbsp;<input type="checkbox" checked="checked" name="use_unlimited" /> <?php _e('Unlimited','mgm')?>?
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
				<input name="expire_dt" id="expire_dt" type="text" size="12" value="" />
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
			</div>
		</div>
	</div>
</form>
<script language="javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){   
		// add : form validation
		jQuery("#frmcoupadd").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmcoupadd").ajaxSubmit({type: "POST",
				  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons&method=add',
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
						// clear fields
						jQuery("#frmcoupadd :input").not(":input[type='hidden'][type='submit'][type='checkbox']").val('');	
						// load new list														
						mgm_coupon_list();										
					}
					// reload form
					mgm_coupon_add();														
				  }}); // end   		
				return false;											
			},
			rules: {			
				name: "required",						
				value: "required",					
				description: "required",
				use_limit:{required: function(){ return jQuery("#frmcoupadd :input[name='use_unlimited']").attr('checked') }, digits:true}	
			},
			messages: {			
				name: "<?php _e('Please enter code','mgm')?>",
				value: "<?php _e('Please enter value','mgm')?>",
				description: "<?php _e('Please enter description','mgm')?>",
				use_limit:{required: "<?php _e('Please enter user limit','mgm')?>", digits: "<?php _e('Please enter number for limit','mgm')?>"}	
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
		jQuery("#frmcoupadd :input[name='use_unlimited']").bind('click', function(){
			if(jQuery(this).attr('checked')){
				jQuery("#frmcoupadd :input[name='use_limit']").val('').attr('disabled', true);
			}else{
				jQuery("#frmcoupadd :input[name='use_limit']").attr('disabled', false);
			}
		});		
		// date picker
		mgm_date_picker("#frmcoupadd :input[name='expire_dt']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});
				
		// genarate coupons on change - issue #1383
		jQuery('#coupon_choice').on('change', function (e) {			
			var optionSelected = jQuery("option:selected", this);			
			var valueSelected = this.value;							
			if(valueSelected =='single'){
				jQuery("#coupon_choice_label").html('<?php _e('Coupon code : ','mgm')?>');
			}						
			if(valueSelected =='multiple'){
				jQuery("#coupon_choice_label").html('<?php _e('Number of coupons : ','mgm')?>');
			}				
		});		
	});	
	//-->	
</script>