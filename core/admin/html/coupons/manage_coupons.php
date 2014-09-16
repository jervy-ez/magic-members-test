<?php mgm_box_top('Coupons Import/Export');?>
	<form name="frmmanagecoupons" id="frmmanagecoupons" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons&method=manage_coupons" method="post">
	<div class="table form-table">
   		<div class="row">
    		<div class="cell mgm_migrate_type">
				<input type="radio" name="coupon_action" value="export" checked="checked"/> <span><?php _e('Export','mgm'); ?></span> 
				<input type="radio" name="coupon_action" value="import"  /> <span><?php _e('Import','mgm'); ?></span> 
				
				<div class="table form-table displaynone widefatDiv" id="import_file_box">
					<div class="row">
						<div class="cell">
							<b id="select_lable"><?php _e('Please Select File:','mgm')?></b>
						</div>
						<div class="cell">
							&nbsp;<span id="couponimport_container"><input type="file" name="import_file" id="import_file"/></span>
						</div>
					</div>
			  		<div class="row">
			    		<div class="cell">
							<div class="information">
								<?php _e('Supported File Types: <b>CSV</b> <br/>Minimum Fileds required: [<b>name,value,description,use_limit,expire_dt</b>]. <br/>Maximum number of records that can be imported at once: <b>' . $data['import_limit'].'</b>','mgm')?>
							</div>	
			    		</div>
			    	</div>
			  		<div class="row">
			    		<div class="cell" style="float:left">
							<p>				
								<input type="button" class="button" onclick="manage_coupons_submit()" value="<?php _e('Import Coupons','mgm') ?>" />
							</p>
			    		</div>
					</div>					
				</div>
				<div class="table form-table widefatDiv" id="export_file_box">
				
			  		<div class="row">
			    		<div class="cell">&nbsp;</div>
					</div>				
					<div class="row brBottom">
						<div class="cell width100px">
							<b><?php _e('Export Options','mgm') ?></b>
						</div>
						<div class="cell">
							<input type="radio" class="checkbox" name="export_option"  value="used" checked="checked"/> <?php _e('Export Only Used Coupons','mgm') ?>
							<input type="radio" class="checkbox" name="export_option"  value="unused" /> <?php _e('Export Only Unused Coupons','mgm') ?>
							<input type="radio" class="checkbox" name="export_option"  value="all" /> <?php _e('Export All Coupons','mgm') ?>
						</div>
					</div>				
					<div class="row brBottom">
						<div class="cell width100px">
							<b><?php _e('Format','mgm') ?></b>
						</div>
						<div class="cell">
							<select name="export_format">
								<option value="xls"><?php _e('XLS','mgm') ?></option>
								<option value="csv"><?php _e('CSV','mgm') ?></option>									
							</select>
						</div>
					</div>				
			  		<div class="row">
			    		<div class="cell" style="float:left">
							<p>				
								<input type="button" class="button" onclick="manage_coupons_submit()" value="<?php _e('Export Coupons','mgm') ?>" />
							</p>
			    		</div>
					</div>					
				</div>
    		</div>
    	</div>   	
	</div>
	</form>
	<iframe id="ifrm_managecoupons" src="#" allowtransparency="true" width="0" height="0" frameborder="0"></iframe>
	<!-- issue #1384 -->
	<?php $url = MGM_ASSETS_URL.'js/editor/plugins/downloads/csv_download.php'; ?>
    <form id = "coupons_csv_download" name="coupons_csv_download" action="<?php echo $url; ?>">
    	<input type="hidden" name="csv_url" id="coupons_csv_url" value="" />
    </form>	
<?php mgm_box_bottom();?>

	<script language="javascript">

	jQuery(document).ready(function(){
			
		// mgm_download_file_upload
		mgm_upload_import_file=function(obj){
			// check empty
			if(jQuery(obj).val().toString().is_empty()==false){	
				
				// check ext	
				if(!(/\.(csv)$/i).test(jQuery(obj).val().toString())){
					alert('<?php _e('Please upload only csv file.','mgm')?>');
					return;
				}				
				// before send, remove old message
				jQuery('#frmmanagecoupons #message').remove();		
				// create new message
				jQuery('#frmmanagecoupons').prepend('<div id="message" class="running"><span><?php _e('Processing','mgm')?>...</span></div>');
				// remove old hidden
				jQuery("#frmmanagecoupons :input[type='hidden'][name='import_file']").remove();						
			
				jQuery.ajaxFileUpload({
						url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons&method=import_file_upload', 
						secureuri:false,
						fileElementId:jQuery(obj).attr('id'),
						dataType: 'json',						
						success: function (data, status){	
							// uploaded	
							if(data.status=='success'){										
								// set hidden
								jQuery('#frmmanagecoupons').append('<input type="hidden" name="import_file" value="'+data.file.path+'">');								
								// remove old message
								jQuery('#frmmanagecoupons #message').remove();								
								jQuery('#frmmanagecoupons #select_lable').remove();								
								// create message
								jQuery('#frmmanagecoupons').prepend('<div id="message"></div>');	
								// show
								jQuery('#frmmanagecoupons #message').addClass(data.status).html(data.message);
								// remove upload/elements						
								// box								
								jQuery("#frmmanagecoupons :file[name='"+jQuery(obj).attr('name')+"']").remove();									
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
		mgm_file_uploader('#frmmanagecoupons', mgm_upload_import_file);
		//IMPORT COUPONS:	
		manage_coupons_submit = function(){			
			// process
			jQuery('#frmmanagecoupons').ajaxSubmit({				
				 dataType: 'json',					 
				 //async: false,
				 iframe: false,		
				 timeout: 0, //15 minutes: 900000								 
				 beforeSubmit: function(){					 	
				 	
					 if(jQuery("input:radio[name='coupon_action']").val() == 'import') {
						 	if(!confirm('<?php _e('Depending on the number of records and server configuration, import will take some minutes for completion.','mgm');?>'))
						 		return false;
					 }
					// show message
					mgm_show_message('#frmmanagecoupons', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);												
				 },
				 success: function(data){				 	
					// uploaded	
					if(data.status=='success'){															
						// show message
						mgm_show_message('#frmmanagecoupons', {status:data.status, message:data.message}, true);	

						if(data.action=='import'){
							// reset upload:					
							jQuery("#couponimport_container :file[name='import_file']").remove();
							jQuery('#couponimport_container').append('<input type="file" name="import_file" id="import_file">');
							// attach uploader
							mgm_file_uploader('#frmmanagecoupons', mgm_upload_import_file);
							// list 
							mgm_coupon_list();
						}
						
						if(data.action=='export'){
							//issue #1384
							var ext = data.src.split('.').pop().toLowerCase();							
							if(ext == 'csv') {
								jQuery('#coupons_csv_url').val(data.src);
								document.coupons_csv_download.submit();
							}else{
								jQuery('#ifrm_managecoupons').attr('src', data.src);
							}		
						}
					}
					
					// uploaded	
					if(data.status=='error'){															
						// show message
						mgm_show_message('#frmmanagecoupons', {status:data.status, message:data.message}, true);	
						// reset upload:					
						jQuery("#couponimport_container :file[name='import_file']").remove();
						jQuery('#couponimport_container').append('<input type="file" name="import_file" id="import_file">');
						// attach uploader
						mgm_file_uploader('#frmmanagecoupons', mgm_upload_import_file);
					}					

				 },
				 error: function (data, status, e){
				 	// show message
					mgm_show_message('#frmmanagecoupons', {status:'error', message:'<?php _e('An error occured while importing','mgm')?>'});
					// reset upload:					
					jQuery("#couponimport_container :file[name='import_file']").remove();
					jQuery('#couponimport_container').append('<input type="file" name="import_file" id="import_file">');
					// attach uploader
					mgm_file_uploader('#frmmanagecoupons', mgm_upload_import_file);					
				}				
			});			
		}

		// bind
		jQuery(":radio[name='coupon_action']").bind('click', function(){			
			if(jQuery(this).val() == 'import'){
				mgm_hide_message('#frmmanagecoupons');
				jQuery('#import_file_box').slideDown();
				jQuery('#export_file_box').slideUp();
				
			}else{
				mgm_hide_message('#frmmanagecoupons');
				jQuery('#import_file_box').slideUp();
				jQuery('#export_file_box').slideDown();

			}
		});		
			
/*		jQuery.extend({
			handleError: function( s, xhr, status, e ) {
			    // If a local callback was specified, fire it
			    if ( s.error )
			        s.error( xhr, status, e );
			    // If we have some XML response text (e.g. from an AJAX call) then log it in the console
			    else if(xhr.responseText)
			        console.log(xhr.responseText);
			}
		});	*/		
			
	});
		