<!--reset-->
<?php mgm_box_top('Reset Magic Members');?>
	<form name="frmresetmgm" id="frmresetmgm" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools&method=system_reset" method="post">
	<div class="table form-table">
  		<div class="row">
    		<div class="cell">	
    			<b><?php _e('Please Select a reset type :','mgm')?></b>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<select name="reset_type" id="reset_type">	
				<?php echo mgm_make_select_options($data['reset_opts'], 'settonly', MGM_KEY_VALUE)?>		
				</select>				
			</div>
		</div>
  		<div class="row">
    		<div class="cell">	
				<p>					
					<input type="button" class="button" onclick="system_reset()" value="<?php _e('RESET','mgm') ?>" />
				</p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">	
			</div>
		</div>
	</div>
	<input type="hidden" name="reset_execute" value="true" />
	</form>
<?php mgm_box_bottom();?>
<script language="javascript">
<!--
	jQuery(document).ready(function(){		
		// reset		
		system_reset = function(){
			var reset_type = jQuery("#frmresetmgm select[name='reset_type']").val();

			switch(reset_type){
				case 'settonly':
					var message = "<?php _e('This will erase all custom settings and revert to factory settings.','mgm') ?>";
				break;
				case 'settntable':
					var message = "<?php _e('This will erase all custom settings, post settings, table data (coupons etc.) and revert to factory settings.','mgm') ?>";
				break;
				case 'fullreset':
					var message = "<?php _e('This will erase all Magic Members data and deactivate the plugin. To deactivate without erasing data, please use Wordpress Plugin Management Interface.','mgm') ?>";
				break;
				case 'licensereset':
					var message = "<?php _e('This will erase your License Info as well Dashboard Cache','mgm') ?>";
				break;
				case 'dashcachereset':
					var message = "<?php _e('This will erase Dashboard Cache.','mgm') ?>";
				break;
				case 'sidebarwidgetreset':
					var message = "<?php _e('This will erase Sidebar Widget Settings.','mgm') ?>";
				break;
			}		
			// warn
			if(confirm('<?php _e('Are sure you want to reset Magic Members? ','mgm') ?>'+message)){
				jQuery('#frmresetmgm').ajaxSubmit({
					 dataType: 'json',		
					 iframe: false,									 
					 beforeSubmit: function(){	
					  	// show message
						mgm_show_message('#frmresetmgm', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);						
					 },
					 success: function(data){	
						// message																				
						mgm_show_message('#frmresetmgm', data);																					
												
						// success	
						if(data.status=='success'){																													
							// redirect
							if(data.redirect && data.redirect != ''){
								window.location.href = data.redirect;
							}										
						}													
					 }
				});
			}
		}
	});
//-->
</script>
