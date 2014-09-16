<!--upgrade : time to time upgrader, internal system, modules, code, database etc.-->
<!--core_setup-->
<?php mgm_box_top('Upgrade Magic Members');?>
	<form name="frmmgmupgrade" id="frmmgmupgrade" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools&method=upgrade" method="post">
	<div class="table form-table width100">
  		<div class="row">
    		<div class="cell">
				<?php // _e('No Upgrade available','mgm') ?>
				<div id="update_data">
					<?php 
					// load remote data
					$upgrade_url = MGM_SERVICE_SITE.'upgrade_screen'.MGM_INFORMATION;//.'&new_version='.$_REQUEST['new_version'];				
					echo mgm_remote_get($upgrade_url, NULL, NULL, 'Could not connect');
					?>
				</div>	
			</div>
		</div>
		<!--<div class="row">
    		<div class="cell">
				<p>					
					<input type="button" class="button" onclick="core_setup()" value="<?php //_e('UPGRADE','mgm') ?>" disabled="disabled" />
				</p>
			</div>
		</div>-->	
	</div> 
	<input type="hidden" name="upgrade_execute" value="true" />
	</form>
<?php mgm_box_bottom();?>
<script language="javascript">
<!--
	jQuery(document).ready(function(){		
		// core_setup		
		core_setup = function(){
			//if(confirm('<?php _e('Are sure you want to update core version of Magic Members?','mgm') ?>')){
				jQuery('#frmmgmupgrade').ajaxSubmit({
					 dataType: 'json',		
					 iframe: false,									 
					 beforeSubmit: function(){	
					  	// show message
						mgm_show_message('#frmmgmupgrade', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);						
					 },
					 success: function(data){	
						// message																				
						mgm_show_message('#frmmgmupgrade', data);			
					 }
				});
			//}
		}
	});
//-->
</script>

