<!--restapi-->
<?php mgm_box_top('Server Settings');?>
	<form name="frmrestapisettings" id="frmrestapisettings" method="post" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi">
		
		<div class="table">
	  		<div class="row">
	    		<div class="cell "><p><b><?php _e('Enable REST Server','mgm'); ?>:</b></p>	</div>
			</div>
	  		<div class="row">
	    		<div class="cell ">	
					<input type="radio" name="rest_server_enabled" value="Y" <?php echo ($data['system_obj']->setting['rest_server_enabled']=='Y') ? 'checked="checked"': ''; ?>/> <?php _e('Yes','mgm')?>
					<input type="radio" name="rest_server_enabled" value="N" <?php echo ($data['system_obj']->setting['rest_server_enabled']=='N') ? 'checked="checked"': ''; ?> /> <?php _e('No','mgm')?>
					<?php $restapi_url = mgm_restapi_url();?>
					<p><div class="tips width90"><?php printf(__('Enable REST Server. ( Endpoint: <a href="%s" target="_blank">%s</a> )','mgm'), $restapi_url, $restapi_url ); ?></div></p>	    		
	    		</div>
			</div>
	  		<div class="row">
	    		<div class="cell ">	
	    			<p><b><?php _e('Allow REST Output Formats','mgm'); ?>:</b></p>
	    		</div>
			</div>
	  		<div class="row">
	    		<div class="cell ">	
					<input type="checkbox" name="rest_output_formats[]" value="xml" <?php echo (in_array('xml',$data['system_obj']->setting['rest_output_formats'])) ? 'checked="checked"': ''; ?>/> <?php _e('XML','mgm')?><br />
					<input type="checkbox" name="rest_output_formats[]" value="json" <?php echo (in_array('json',$data['system_obj']->setting['rest_output_formats'])) ? 'checked="checked"': ''; ?> /> <?php _e('JSON','mgm')?><br />
					<input type="checkbox" name="rest_output_formats[]" value="phps" <?php echo (in_array('phps',$data['system_obj']->setting['rest_output_formats'])) ? 'checked="checked"': ''; ?> /> <?php _e('SERIALIZED PHP STRING','mgm')?><br />
					<input type="checkbox" name="rest_output_formats[]" value="php" <?php echo (in_array('php',$data['system_obj']->setting['rest_output_formats'])) ? 'checked="checked"': ''; ?> /> <?php _e('PHP ARRAY','mgm')?><br />										
					<p><div class="tips width90"><?php _e('Allowed output formats.','mgm'); ?></div></p>
	    		</div>
			</div>
	  		<div class="row">
	    		<div class="cell ">	
	    			<p><b><?php _e('Allow REST Input Methods','mgm'); ?>:</b></p>
	    		</div>
			</div>
	  		<div class="row">
	    		<div class="cell ">	
					<input type="checkbox" name="rest_input_methods[]" value="get" <?php echo (in_array('get',$data['system_obj']->get_setting('rest_input_methods'))) ? 'checked="checked"': ''; ?>/> <?php _e('GET','mgm')?><br />
					<input type="checkbox" name="rest_input_methods[]" value="post" <?php echo (in_array('post',$data['system_obj']->get_setting('rest_input_methods'))) ? 'checked="checked"': ''; ?> /> <?php _e('POST','mgm')?><br />
					<input type="checkbox" name="rest_input_methods[]" value="put" <?php echo (in_array('put',$data['system_obj']->get_setting('rest_input_methods'))) ? 'checked="checked"': ''; ?> /> <?php _e('PUT','mgm')?><br />
					<input type="checkbox" name="rest_input_methods[]" value="delete" <?php echo (in_array('delete',$data['system_obj']->get_setting('rest_input_methods'))) ? 'checked="checked"': ''; ?> /> <?php _e('DELETE','mgm')?><br />										
					<p><div class="tips width90"><?php _e('Allowed input methods.','mgm'); ?></div></p>
	    		</div>
			</div>
	  		<div class="row">
	    		<div class="cell ">	
	    			<p><b><?php _e('Default Consumption Limit','mgm'); ?>:</b></p>
	    		</div>
			</div>
	  		<div class="row">
	    		<div class="cell ">	
					<input type="text" name="rest_consumption_limit" value="<?php echo $data['system_obj']->setting['rest_consumption_limit']?>" /> <?php _e('per hour','mgm')?>
					<p><div class="tips width90"><?php _e('Default request consumption limit.','mgm'); ?></div></p>
	    		</div>
			</div>
		</div>
		<p class="submit floatleft">
			<input class="button" type="submit" name="settings_update" id="settings_update" value="<?php _e('Save Settings','mgm') ?>" />
		</p>
		<div class="clearfix"></div>	
	</form>
<?php mgm_box_bottom();?>

<?php mgm_box_top('API Access Levels');?>
	<div id="restapi_access_levels"></div>
	<div>
		<p class="submit floatleft">
			<input type="button" name="add_level_btn" id="add_level_btn" value="<?php _e('Add Level','mgm') ?>" onclick="mgm_api_level_add()" />
		</p>
	</div>
	<div class="clearfix"></div>
<?php mgm_box_bottom();?>

<?php mgm_box_top('API Access Keys');?>
	<div id="restapi_access_keys"></div>
	<div>
		<p class="submit floatleft">
			<input type="button" name="add_key_btn" id="add_key_btn" value="<?php _e('Add Key','mgm') ?>" onclick="mgm_api_key_add()"/>
		</p>
	</div>
	<div class="clearfix"></div>
<?php mgm_box_bottom();?>
		
<script language="javascript">
	<!--
	jQuery(document).ready(function(){		
		// add : form validation
		jQuery("#frmrestapisettings").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmrestapisettings").ajaxSubmit({type: "POST",										  
				  dataType: 'json',			
				  iframe: false,								 
				  beforeSubmit: function(){	
				  	// show message
					mgm_show_message('#frmrestapisettings', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);
				  },
				  success: function(data){	
					// show message
				  	mgm_show_message('#frmrestapisettings', data);														
				  }}); // end   		
				return false;											
			},
			rules:{
				rest_server_enabled:{
					required:true
				},
				'rest_output_formats[]':{
					required:true,
					minlength: 1
				},
				'rest_input_methods[]':{
					required:true,
					minlength: 1
				},
				rest_consumption_limit:{
					required:true,
					digits: true
				}
			},	
			messages: {	
				rest_server_enabled: "<?php _e('Please select server status','mgm')?>",
				'rest_output_formats[]': "<?php _e('Please select one output format','mgm')?>",
				'rest_input_methods[]': "<?php _e('Please select one input method','mgm')?>",
				rest_consumption_limit: "<?php _e('Please enter valid limit, digits only','mgm')?>"
			},		
			errorClass: 'invalid',
			errorPlacement:function(error, element) {	
				if(element.is(":input[name='rest_output_formats[]']"))
					error.insertAfter(jQuery(":input[name='rest_output_formats[]']:last").next());
				else if(element.is(":input[name='rest_input_methods[]']"))
					error.insertAfter(jQuery(":input[name='rest_input_methods[]']:last").next());
				else if(element.is(":input[name='rest_consumption_limit']"))
					error.insertAfter(element.next());						
				else									
					error.insertAfter(element);
			}
		});		
		
		// load levels
		mgm_load_api_levels=function(){
			// load
			jQuery('#restapi_access_levels').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi_levels',
			function(){ jQuery('#add_level_btn').show(); });
		}
		// level edit
		mgm_api_level_edit=function(id){
			// load
			jQuery('#restapi_access_levels').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi_level_edit', {id: id}, 
			function(){ jQuery('#add_level_btn').hide(); });
		}
		// level delete
		mgm_api_level_delete=function(id){
			// load
			if(confirm("<?php _e('You are about to delete access level, are you sure?')?>")){
				jQuery.ajax({
					url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi_level_delete',
					type: 'POST',
					dataType: 'json',
					data: {id: id},
					beforeSend: function(){	
						// show message
						mgm_show_message('#restapi_access_levels', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);
					},
					success: function(data){	
						// show message
						mgm_show_message('#restapi_access_levels', data);	
						// delete
						jQuery('#restapi_access_levels #row-'+id).remove();													
					}
				})
			}			
		}
		// level add
		mgm_api_level_add=function(id){
			// load
			jQuery('#restapi_access_levels').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi_level_add',
			function(){ jQuery('#add_level_btn').hide(); });
		}
				
		// load keys
		mgm_load_api_keys=function(){
			jQuery('#restapi_access_keys').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi_keys',
			function(){ jQuery('#add_key_btn').show(); });
		}			
		// key edit
		mgm_api_key_edit=function(id){
			jQuery('#restapi_access_keys').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi_key_edit', {id: id},
			function(){ jQuery('#add_key_btn').hide(); });
		}	
		// key delete
		mgm_api_key_delete=function(id){
			// load
			if(confirm("<?php _e('You are about to delete access key, are you sure?')?>")){
				jQuery.ajax({
					url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi_key_delete',
					type: 'POST',
					dataType: 'json',
					data: {id: id},
					beforeSend: function(){	
						// show message
						mgm_show_message('#restapi_access_keys', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);
					},
					success: function(data){	
						// show message
						mgm_show_message('#restapi_access_keys', data);	
						// delete
						jQuery('#restapi_access_keys #row-'+id).remove();													
					}
				})
			}			
		}	
		// key add
		mgm_api_key_add=function(id){
			jQuery('#restapi_access_keys').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi_key_add',
			function(){ jQuery('#add_key_btn').hide(); });
		}
		// load 
		mgm_load_api_levels();
		mgm_load_api_keys();
	});
	//-->
</script>			
	