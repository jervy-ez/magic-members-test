<!--autoresponders-->
<form name="frmaresp" id="frmaresp" method="post" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=autoresponders">
	<?php mgm_box_top('Auto Responders');?>
	<?php foreach($data['module'] as $module_name):
			echo $module_name['html'];
		endforeach;?>
	<div class="clearfix"></div>
	<p class="submit">
		<input class="button" type="submit" name="update" value="<?php _e('Save','mgm'); ?>" />
	</p>
	<?php mgm_box_bottom();?>
</form>
<script language="javascript">
	<!--
	jQuery(document).ready(function(){
		// ie style
		jQuery('.module_settings_box').corner("5px");	
		// select
		jQuery("div[id^='module_settings_box_']").find(":input[@type=text]").focus(function(){
			var name   = jQuery(this).attr('name');
			var module = 'mgm_' + name.replace(/setting\[(.*)\]\[(.*)\]/, "$1");						
			jQuery(":radio[name=module][value='"+module+"']").attr('checked',true);
		});	 
		// add : form validation
		jQuery("#frmaresp").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmaresp").ajaxSubmit({type: "POST",										  
				  dataType: 'json',		
				  iframe: false,									 
				  beforeSubmit: function(){	
					// show message
					mgm_show_message('#frmaresp', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);					
				  },
				  success: function(data){	
				  	// message
					mgm_show_message('#frmaresp', data);																				
				  }}); // end   		
				return false;											
			},			
			errorClass: 'invalid'
		});		
	});
	//-->
</script>		 