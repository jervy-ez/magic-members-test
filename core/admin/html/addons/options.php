<?php mgm_box_top(sprintf('View Options for Addon: <b>%s</b>',$data['addon']->name));?>
	<form name="frmaddonoptedit" id="frmaddonoptedit" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=options" class="marginpading0px">
		<div class="table widefatDiv width100">
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
						<input class="button" type="submit" name="btn_save" value="<?php _e('Save Options', 'mgm') ?>" />
					</div>	
					<div class="floatright">	
						<input class="button" type="button" onclick="mgm_addon_options(false)" value="<?php _e('Back to Addons', 'mgm') ?>" />
					</div>		
				</div>
			</div>	
		</div>	
		<input type="hidden" name="id" value="<?php echo $data['addon']->id?>" />	
		<input type="hidden" name="save_addon_options" value="true" />
	</form>
	<script language="javascript">
		jQuery(document).ready(function() {
			jQuery('#frmaddonoptedit').bind('submit', function(e) {
				e.preventDefault(); // <-- important
				jQuery(this).ajaxSubmit({type: "POST",
				  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=options',
				  dataType: 'json',		
				  iframe: false,									 
				  beforeSubmit: function(){	
					// show message
					mgm_show_message('#addon_options', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);																	
				  },
				  success: function(data){	
						// message																				
						mgm_show_message('#addon_options', data);																										
						// success	
						if(data.status=='success'){																										
							// load new list	
							window.setTimeout(function(){mgm_addon_options(false)}, 1000);											
						}																	
				  }});
			});
			
			// call addon options row manage
			mgm_addon_option_row_manage('#addon_options');
		});
	</script>
<?php mgm_box_bottom();?>