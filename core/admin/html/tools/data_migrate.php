<!--data migrate: old MGM to new MGM migrate, keep back up, create new objects, table data. exit if new version.-->
<?php mgm_box_top('Migrate Old Magic Members Data');?>

	<form name="frmdatamgr" id="frmdatamgr" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools&method=data_migrate" method="post">
	<div class="table form-table">
  		<div class="row">
    		<div class="cell">
    			<b><?php _e('Please Select data options to migrate :','mgm')?></b>
    		</div>
    	</div>
  		<div class="row">
    		<div class="cell">
				<?php 
				// sections
/*				$sections = array('general_settings'=>__('General Settings','mgm'),'post_settings'=>__('Post Settings','mgm'),
							      'user_settings'=>__('User Settings','mgm'),	'coupons'=>__('Coupons','mgm'),	
								  'downloads'=>__('Downloads','mgm'),	'download_posts'=>__('Download Posts','mgm'),
								  'download_attributes'=>__('Download Attributes','mgm'),'download_attribute_types'=>__('Download Attribute Types','mgm'),
								  'post_purchase_records'=>__('Post Purchase Records','mgm'),	'post_packs'=>__('Post Packs','mgm'),	
								  'post_pack_posts'=>__('Post Pack Posts','mgm'));*/
				$sections = array('general_settings'=>__('General Settings','mgm'),'messages_settings'=>__('Messages Settings','mgm'),
							      'emails_settings'=>__('Emails Settings','mgm'),'content_protection_settings'=>__('Content Protection Settings','mgm'));
				 // show			
				 echo mgm_make_checkbox_group('export_sections[]', $sections, array_keys($sections), MGM_KEY_VALUE);
				 ?>
    		
    		</div>
    	</div>
  		<div class="row">
    		<div class="cell mgm_migrate_type">
				<input type="radio" name="migrate_type" value="export" checked="checked"/> <span><?php _e('Export','mgm'); ?></span> 
				<input type="radio" name="migrate_type" value="import"  /> <span><?php _e('Import','mgm'); ?></span> 
				<p>
					<div id="import_file_box" class="displaynone">
						<input type="file" name="import_file" id="import_file"/>
					</div>
				</p>
    		
    		</div>
    	</div>
  		<div class="row">
    		<div class="cell height10px">
				<p>					
					<?php if(get_option('mgm_version_migration')):?>
					<div class="warning"><?php _e('WARNING! migration already took place on','mgm')?> <?php echo get_option('mgm_version_migration')?>. <?php _e('This will overwrite any intermidiate updates','mgm')?>.</div><br />
					<?php endif;?>
					<input type="button" class="button" onclick="data_migrate()" value="<?php _e('MIGRATE','mgm') ?>" />
				</p>
    		</div>
    	</div>
  		<div class="row">
    		<div class="cell">
    		</div>
    	</div>
    </div>
    <input type="hidden" name="migrate_execute" value="true" />		
	</form>
	<iframe id="ifrm_export" src="#" allowtransparency="true" width="0" height="0" frameborder="0"></iframe>
<?php mgm_box_bottom();?>

<?php mgm_box_top('Import User Data');?>
	<form name="frmimportusers" id="frmimportusers" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools&method=data_migrate" method="post">
	<div class="table form-table">
  		<div class="row">
    		<div class="cell">
    			<b><?php _e('Please Select File:','mgm')?></b>
    		</div>
    		<div class="cell">
    			</select>&nbsp;<span id="userimport_container"><input type="file" name="import_users" id="import_users"/></span>
    		</div>
    	</div>
  		<div class="row">
    		<div class="cell">
				<div class="information">
					<?php _e('Supported File Types: <b>CSV/XLS</b> <br/>Minimum Fileds required: [<b>user_login, user_email, membership_type, pack_id</b>]. <br/><b>Custom field Names</b> can also be included.<br/>Maximum number of records that can be imported at once: <b>' . $data['import_limit'].'</b>','mgm')?>
				</div>	
    		</div>
    	</div>
  		<div class="row">
    		<div class="cell" style="float:left">
				<p>				
					<input type="button" class="button" onclick="import_users_submit()" value="<?php _e('Import Users','mgm') ?>" />
				</p>
    		</div>
		</div>	
	</div>
	<input type="hidden" name="migrate_type" value="import_users" />
	<input type="hidden" name="migrate_execute" value="true" />
	</form>
	<iframe id="ifrm_importusers" src="#" allowtransparency="true" width="0" height="0" frameborder="0"></iframe>
<?php mgm_box_bottom();?>
<script language="javascript">
<!--
	jQuery(document).ready(function(){		
		// data_migrate		
		data_migrate = function(){
			// migrate_type
			var migrate_type = jQuery(":radio[name='migrate_type']:checked").val();
			if(migrate_type == 'import'){
				if(!confirm('<?php _e('Are sure you want to migrate all old data to new Magic Members?','mgm') ?>')){
					return;
				}
			}
			// process
			jQuery('#frmdatamgr').ajaxSubmit({
				 dataType: 'json',		
				 iframe: false,									 
				 beforeSubmit: function(){	
					// show message
					mgm_show_message('#frmdatamgr', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);												
				 },
				 success: function(data){	
					// message																				
					mgm_show_message('#frmdatamgr', data);	
					// export
					if(data.download_url){
						jQuery('#ifrm_export').attr('src', data.download_url);							
					}				
				 }
			});			
		}
		
		// bind
		jQuery(":radio[name='migrate_type']").bind('click', function(){			
			if(jQuery(this).val() == 'import'){
				jQuery('#import_file_box').slideDown();
			}else{
				jQuery('#import_file_box').slideUp();
			}
		});
		
		// define
		mgm_upload_import_file=function(obj) {
			// langs	
			// check empty
			if(jQuery(obj).val().toString().is_empty()==false){	
				// check ext	
				if(!(/\.(xml)$/i).test(jQuery(obj).val().toString())){
					alert('<?php _e('Please upload only xml file.','mgm')?>');
					return;
				}				
					
				// before send, remove old message
				jQuery('#frmdatamgr #message').remove();		
				// create new message
				jQuery('#frmdatamgr').prepend('<div id="message" class="running"><span><?php _e('Processing','mgm')?>...</span></div>');
				// remove old hidden
				jQuery("#frmdatamgr :input[type='hidden'][name='import_file']").remove();						
				// upload 
				jQuery.ajaxFileUpload({
						url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools&method=import_file_upload', 
						secureuri:false,
						fileElementId:jQuery(obj).attr('id'),
						dataType: 'json',						
						success: function (data, status){	
							// uploaded	
							if(data.status=='success'){										
								// set hidden
								jQuery('#frmdatamgr').append('<input type="hidden" name="import_file" value="'+data.file.path+'">');								
								// remove old message
								jQuery('#frmdatamgr #message').remove();								
								// create message
								jQuery('#frmdatamgr').prepend('<div id="message"></div>');	
								// show
								jQuery('#frmdatamgr #message').addClass(data.status).html(data.message);	
								// remove upload/elements						
								// box								
								jQuery("#frmdatamgr :file[name='"+jQuery(obj).attr('name')+"']").remove();															
							}											
						},
						error: function (data, status, e){
							alert('<?php _e('Error occured in upload.','mgm')?>');
						}
					}
				)		
				// end
			}		
		}
		
		// attach uploader
		mgm_file_uploader('#frmdatamgr', mgm_upload_import_file);
		
		//IMPORT USERS:	
		import_users_submit = function(){			
			// process
			jQuery('#frmimportusers').ajaxSubmit({				
				 dataType: 'json',					 
				 //async: false,
				 iframe: false,		
				 timeout: 0, //15 minutes: 900000								 
				 beforeSubmit: function(){					 	
				 	if(!confirm('<?php _e('Depending on the number of records and server configuration, import will take some minutes for completion.','mgm');?>'))
				 		return false;
					// show message
					mgm_show_message('#frmimportusers', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);												
				 },
				 success: function(data){				 	
				 	check_import_status(data, 1);				 					 						
				 },
				 error: function (data, status, e){
				 	// show message
					mgm_show_message('#frmimportusers', {status:'error', message:'<?php _e('An error occured while importing','mgm')?>'});
					// reset upload:					
					jQuery("#userimport_container :file[name='import_users']").remove();
					jQuery('#userimport_container').append('<input type="file" name="import_users" id="import_users">');
					// attach uploader
					mgm_file_uploader('#frmimportusers', mgm_upload_importusers_file);					
				}				
			});			
		}
		//check upload status: This is needed as some servers send null response while import is being done.
		check_import_status = function(data, retry_count) {
			jQuery.ajax({url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools&method=data_migrate', type: 'POST',dataType: 'json', data: {migrate_execute: 'true', migrate_type: 'import_status', import_users: jQuery('#frmimportusers input[name="import_users"]').val(), retry : retry_count },
					success: function(resp) {
						if(resp == null || ( typeof(resp.status) != 'undefined' && resp.status == 'incomplete') ) {						
							// poll again
							check_import_status(data, retry_count+1);
						}else {
							// message								
							mgm_show_message('#frmimportusers', resp);												
							// export
							if(data != null && typeof(data.download_url) != 'undefined'){
								jQuery('#ifrm_importusers').attr('src', data.download_url);							
							}
							// reset upload:					
							jQuery("#userimport_container :file[name='import_users']").remove();
							jQuery('#userimport_container').append('<input type="file" name="import_users" id="import_users">');
							// attach uploader
							mgm_file_uploader('#frmimportusers', mgm_upload_importusers_file);
						}
					},
					error: function() {
						//reset upload:					
						jQuery("#userimport_container :file[name='import_users']").remove();
						jQuery('#userimport_container').append('<input type="file" name="import_users" id="import_users">');
						// attach uploader
						mgm_file_uploader('#frmimportusers', mgm_upload_importusers_file);
					}
				});
		}
		
		//import 
		mgm_upload_importusers_file=function(obj) {
			// langs	
			// check empty
			if(jQuery(obj).val().toString().is_empty()==false){	
				// check ext	
				if(!(/\.(csv|xls)$/i).test(jQuery(obj).val().toString())){					
					alert('<?php _e('Please upload only '.(implode(",", $data['filetypes'])).' files.','mgm')?>');
					return;
				}				
					
				// before send, remove old message
				jQuery('#frmimportusers #message').remove();		
				// create new message
				jQuery('#frmimportusers').prepend('<div id="message" class="running"><span><?php _e('Processing','mgm')?>...</span></div>');
				// remove old hidden
				jQuery("#frmimportusers :input[type='hidden'][name='import_users']").remove();						
				// upload 
				jQuery.ajaxFileUpload({
						url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/tools&method=importusers_file_upload', 
						secureuri:false,
						fileElementId:jQuery(obj).attr('id'),
						dataType: 'json',						
						success: function (data, status){	
							// uploaded	
							if(data.status=='success'){										
								// set hidden
								jQuery('#userimport_container').append('<input type="hidden" name="import_users" value="'+data.file.path+'">');								
								// remove old message
								jQuery('#frmimportusers #message').remove();								
								// create message
								jQuery('#frmimportusers').prepend('<div id="message"></div>');	
								// show
								jQuery('#frmimportusers #message').addClass(data.status).html(data.message);	
								// remove upload/elements
								jQuery("#userimport_container :file[name='"+jQuery(obj).attr('name')+"']").remove();															
							}										
						},
						error: function (data, status, e){
							alert('<?php _e('Error occured in upload.','mgm')?>');
						}
					}
				);		
				// end
			}		
		}
		
		// attach uploader
		mgm_file_uploader('#frmimportusers', mgm_upload_importusers_file);	
	});
//-->
</script>