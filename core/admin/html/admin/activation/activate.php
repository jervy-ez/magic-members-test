<!--activate-->
<?php mgm_box_top('Magic Members Activation');?>	
	<form name="frmactivate" id="frmactivate" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin&method=activation_activate">
		<div id="activate">
			<div class="tab-error fade" style="line-height:20px">
				<p><?php _e('Magic Members will not function until a valid license key has been entered. Please enter the email address used to purchase the plugin in the box below to activate your product. Please contact Magic Members if you need help with this.','mgm');?></p>
				<p><?php echo sprintf(__("If you don't have a key then please visit %s to purchase one.",'mgm'),"<a href='http://www.magicmembers.com'>http://www.magicmembers.com</a>");?></p>
			</div>
			<div style="padding:10px 10px 10px 20px">
				<b><?php _e('Registration Email','mgm')?>:</b> <input type="text" name="email" size="50"/> 
				<input class="button" type="submit" name="btn_activate" value="<?php _e('Activate','mgm')?>" />
				<label id="email-error"></label>
			</div>
		</div>
	</form>	
<?php mgm_box_bottom();?>
<script language="javascript">
	<!--
	// onready
	jQuery(document).ready(function(){   					
		// first field focus 	
		jQuery("#frmactivate :input:first").focus();							
		// add login form validation
		jQuery("#frmactivate").validate({					
			submitHandler: function(form) {					    					
				jQuery("#frmactivate").ajaxSubmit({type: "POST",										  
				  dataType: 'json',				
				  iframe: false,							 
				  beforeSubmit: function(){							
					mgm_show_message('#activate',{status:'running', message:'<?php _e('Validating','mgm')?>...'},true);							  	
				  },
				  success: function(data){	
					// show 						
					mgm_show_message('#activate', data);
					// cancel to list
					if(data.status=='success'){														
						window.location.href='admin.php?page=mgm/admin';													
					}													   	
				  }});    		
				return false;											
			},
			rules: {			
				email:{
					required:true,
					email:true
				}				
			},
			messages: {	
				email: "<?php _e('Please enter a valid email address','mgm')?>"									
			},
			errorClass: 'validation-error',
			errorPlacement: function(error, element){
				error.appendTo(jQuery("#email-error"));
			}
		});				
	});	
	//-->		
</script>