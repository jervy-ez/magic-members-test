<!--send_gift-->
<form name="frmpostgift" id="frmpostgift" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=post_purchase_gift">
	<div class="table widefatDiv">
		<div class="row">
			<div class="cell width120px">
				<b><?php _e('Pick a User', 'mgm') ?>:</b>
			</div>
		</div>
		<div class="row">	
			<div class="cell textalignleft">
				<select name="user_id" class="width70">
					<?php echo mgm_make_combo_options($data['users'], 1, MGM_KEY_VALUE);?>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="cell width120px">
				<b><?php _e('Select a post/page', 'mgm') ?>:</b>
			</div>
		</div>
		<div class="row brBottom">	
			<div class="cell textalignleft">
				<select name="post_id" class="width70">
					<?php echo mgm_make_combo_options($data['posts'], 1, MGM_KEY_VALUE);?>
				</select><br />
				<input type="checkbox" name="is_expire" value="N" />&nbsp;<?php _e('Override PPP expiration date', 'mgm') ?>
			</div>
		</div>		
		<div class="row brBottom">
			<div class="cell textalignleft">
				<input class="button" type="submit" name="submit" value="<?php _e('Send Gift', 'mgm') ?>" <?php echo (count($data['posts'])==0 ? 'disabled="disabled"':"")?>/>
			</div>
		</div>
	</div><br />
	<input type="hidden" name="send_gift" value="true" />
</form>
<script language="javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){   
		// add : form validation
		jQuery("#frmpostgift").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmpostgift").ajaxSubmit({type: "POST",
				  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=post_purchase_gift',
				  dataType: 'json',		
				  iframe: false,									 
				  beforeSubmit: function(){	
				  	// show message
					mgm_show_message('#post_purchase_gift', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);						
				  },
				  success: function(data){	
					// message																				
					mgm_show_message('#post_purchase_gift', data);	
					// reload
					mgm_post_purchase_list();																																	
				  }}); // end   		
				return false;											
			},
			rules: {			
				user_id: "required",										
				post_id: "required"	
			},
			messages: {			
				user_id: "<?php _e('Please select user','mgm')?>",
				post_id: "<?php _e('Please select post','mgm')?>"
			},
			errorClass: 'invalid',
			errorPlacement:function(error, element) {				
				error.insertAfter(element);					
			}
		});	
	});	
	//-->	
</script>