<!--download edit-->
	<form name="frmdwnedit" id="frmdwnedit" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/downloads&method=edit" method="post" enctype="multipart/form-data">
		<div class="table widefatDiv">
			<div class="row headrow">
				<div class="cell theadDivCell">
					<b><?php _e('Edit Download','mgm')?></b>
				</div>
			</div>
			<div class="row">
				<div class="cell width120px">
					<span class="required">
						<b><?php _e('Title (required)','mgm') ?>:</b>
					</span>
				</div>
			</div>
			<div class="row">	
				<div class="cell textalignleft ">
					<input name="title" id="title" type="text" size="100" value="<?php echo $data['download']->title ?>" />
				</div>
			</div>
			<div class="row">
				<div class="cell width120px">
					<span class="required">
						<b><?php _e('Upload a file','mgm') ?>:</b>
					</span>
				</div>
			</div>
			<div class="row">	
				<div class="cell textalignleft ">
					<input name="download_file" id="download_file" type="file" size="70"/><br />	
					<div class="mbhighlight">	
						<div class="table form-table">
							<div class="row">
								<div class="cell">
									<b><?php _e('File Name','mgm') ?>:</b>
								</div>
							</div>	
							<div class="row">
								<div class="cell">
									<a href="<?php echo $data['download']->filename?>" target="_blank"><?php echo basename($data['download']->filename)?></a>
								</div>
							</div>	
							
							<div class="row">
								<div class="cell">
									<b><?php _e('Direct URL','mgm') ?>:</b>
								</div>
							</div>	
							<div class="row">
								<div class="cell">
									<input name="direct_url" id="direct_url" type="text" value="<?php echo $data['download']->filename ?>" size="120" maxlength="255" />
									<input type="hidden" name="old_download_file" id="old_download_file" value="<?php echo basename($data['download']->filename)?>" />		
								</div>
							</div>
							
							<div class="row">
								<div class="cell">
									<b><?php _e('Protected URL','mgm') ?>:</b>
								</div>
							</div>	
							<div class="row">
								<div class="cell">
									<?php echo mgm_download_url($data['download'],$data['download_slug'])?>	
								</div>
							</div>				
						</div>		
					</div>	
				</div>
			</div>
			<div class="row">
				<div class="cell width120px">
					<b><?php _e('Restrict Access?','mgm') ?>:</b>
				</div>
			</div>
			<div class="row">	
				<div class="cell textalignleft ">
					<input type="checkbox" name="members_only" <?php echo( bool_from_yn($data['download']->members_only) ? "checked='checked'":'') ?> /> <?php _e('Restrict via Post/Page Access','mgm') ?>
					<div class="tips width90">
						<?php _e('If checked, only users of the appropriate access level can access the file. User level is calculated by checking access to a certain post or posts.','mgm') ?>
					</div>
					<p id="members_only_posts" class="<?php echo( bool_from_yn($data['download']->members_only) ? "displayblock":'displaynone') ?>">
						<select name="link_to_post_id[]" multiple size="10" class="height250px width820px">
							<?php echo mgm_make_combo_options($data['posts'], $data['download_posts'], MGM_KEY_VALUE)?>
						</select>
					</p>
					<p>
						<input type="checkbox" name="restrict_acces_ip" id="restrict_acces_ip" <?php echo( bool_from_yn($data['download']->restrict_acces_ip) ? "checked='checked'":'') ?>/> <?php _e('Restrict Download to IP','mgm') ?>
						<div class="tips">
							<?php _e('If checked, download will be locked to particular IP address, if download limit is set, either of user or IP restriction must be set.','mgm') ?>
						</div>
					</p>
				</div>
			</div>
			<div class="row">
				<div class="cell width120px">
					<b><?php _e('Download Limit','mgm') ?>:</b>
				</div>
			</div>
			<div class="row">	
				<div class="cell textalignleft ">
					<input name="download_limit" id="download_limit" type="text" size="10" maxlength="10" value="<?php echo (($data['download']->download_limit)>0) ? $data['download']->download_limit : ''?>" />
					<div class="tips"><?php _e('Leave empty for unlimited downloads','mgm')?></div>
				</div>
			</div>
			<div class="row">
				<div class="cell width120px">
					<b><?php _e('Expire Date','mgm') ?>:</b>
				</div>
			</div>
			<div class="row">					
				<?php
				$expire_dt = '';
				if((int)$data['download']->expire_dt > 0 ):
					 $expire_dt = mgm_get_datepicker_format('date', date('Y-m-d', strtotime($data['download']->expire_dt)));
				endif;?>		
				<div class="cell textalignleft ">
					<input name="expire_dt" id="expire_dt" type="text" size="12" value="<?php echo $expire_dt ?>" />
					<div class="tips"><?php _e('Leave empty for never expire','mgm')?></div>
				</div>				
			</div>
			<div class="row">
				<div class="cell width120px">
					<b><?php _e('Amazon S3 Settings','mgm') ?>:</b> 
				</div>
			</div>
			<div class="row">		
				<div class="cell textalignleft ">
					<input type="checkbox" name="is_s3_torrent" id="is_s3_torrent" <?php echo( bool_from_yn($data['download']->is_s3_torrent) ? "checked='checked'":'') ?> /> <?php _e('Is Torrent','mgm') ?>
					<div class="tips"><?php printf(__('Allow Torrent for Amazon S3 larger files ( rquires public ACL ). Please read <a href="%s">here</a>.','mgm'), ' http://docs.amazonwebservices.com/AmazonS3/latest/dev/S3TorrentRetrieve.html')?></div>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell">
					<div class="tips">
						<b><?php _e('Available Tags','mgm')?></b><br />
						[<?php echo $data['download_hook'] . "#" . $data['download']->id ?>] : <?php _e('Download link','mgm')?><br />
						[<?php echo $data['download_hook'] . "#" . $data['download']->id ?>#image] : <?php _e('Image Download link','mgm')?><br />
						[<?php echo $data['download_hook'] . "#" . $data['download']->id ?>#button] : <?php _e('Button Download link','mgm')?><br />
						[<?php echo $data['download_hook'] . "#" . $data['download']->id ?>#size] : <?php _e('Download link with filesize','mgm')?><br />
						[<?php echo $data['download_hook'] . "#" . $data['download']->id ?>#url] : <?php _e('Download url only','mgm')?><br />
					</div>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<div class="floatleft">			
						<input class="button" type="submit" name="submit_download" value="<?php _e('Save','mgm') ?>" />			
					</div>
					<div class="floatright">
						<input class="button" type="button" onclick="mgm_download_add()" value="<?php _e('Cancel', 'mgm') ?>" />
					</div>	
				</div>
			</div>
		</div>			
		<input type="hidden" name="code" id="code" value="<?php echo $data['download']->code ?>" />
		<input type="hidden" name="id" id="id" value="<?php echo $data['download']->id ?>" />				
	</form>
	<script language="javascript">
		<!--
		jQuery(document).ready(function(){		
			 // edit : form validation
			 jQuery("#frmdwnedit").validate({
				submitHandler: function(form) {   
					jQuery("#frmdwnedit").ajaxSubmit({type: "POST",
					  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/downloads&method=edit',
					  dataType: 'json',		
					  iframe: false,									 
					  beforeSubmit: function(){	
						// show message
						mgm_show_message('#download_manage', {status:'running', message:'<?php _e('Processing','mgm')?>...'});								
						// focus scroll
						jQuery.scrollTo('#download_manage',400);
					  },
					  success: function(data){		
							mgm_show_message('#download_manage', data);		
							// success	
							if(data.status=='success'){																				
								// list
								mgm_download_list(data);
								//mgm_download_edit();														
							}else{															
								// message																				
								mgm_show_message('#download_manage', data);
							}														
					  }});// end 
					  return false;															
				},
				rules:{
					title :"required",
					download_file : {required: function(){ return jQuery('#old_download_file').val().toString().is_empty() ? ( jQuery('#direct_url').val().toString().is_empty() ) : false; } }
				},
				messages:{
					title :"<?php _e('Please enter title','mgm')?>",
					download_file :"<?php _e('Please upload the file or set direct url','mgm')?>"
				},
				errorClass: 'invalid'
			 });	
			 // mgm_download_file_upload
			 mgm_download_file_upload=function(obj){
				// check empty
				if(jQuery(obj).val().toString().is_empty()==false){	
					// check ext	
					if((/\.(exe|bin|php)$/i).test(jQuery(obj).val().toString())){
						alert('<?php _e('Please do not upload unsafe files','mgm')?>');
						return;
					}	
					
					// process upload 		
					// vars													
					var form_id = jQuery(jQuery(obj).get(0).form).attr('id');					
					// before send, remove old message
					jQuery('#'+form_id+' #message').remove();		
					// create new message
					jQuery('#'+form_id).prepend('<div id="message" class="running"><span><?php _e('Processing','mgm')?>...</span></div>');
					// remove old hidden
					jQuery("#"+form_id+" :input[type='hidden'][name='download_file_new']").remove();
					// upload 
					jQuery.ajaxFileUpload({
							url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/downloads&method=file_upload', 
							secureuri:false,
							fileElementId:jQuery(obj).attr('id'),
							dataType: 'json',						
							success: function (data, status){	
								// uploaded					
								if(data.status=='success'){				
									// change file
									jQuery("#"+form_id+" :file[name='"+jQuery(obj).attr('name')+"']").parent().html(data.download_file.file_url);
									// remove
									jQuery("#"+form_id+" :file[name='"+jQuery(obj).attr('name')+"']").remove();
									// set hidden
									jQuery('#'+form_id).append('<input type="hidden" name="download_file_new" value="'+data.file_info.file_url+'">');								
									jQuery('#'+form_id).append('<input type="hidden" name="download_file_new_realname" value="'+data.file_info.real_name+'">');	
									// remove old message
									jQuery('#'+form_id+' #message').remove();								
									// create message
									jQuery('#'+form_id).prepend('<div id="message"></div>');	
									// show
									jQuery('#'+form_id+' #message').addClass(data.status).html(data.message);									
								}											
							},
							error: function (data, status, e){
								alert('<?php _e('Error occured in upload','mgm')?>');
							}
						}
					)		
					// end
				}			 
			 }
			 // bind
			 jQuery("#frmdwnedit :checkbox[name='members_only']").bind('click', function(){
			 	if(jQuery(this).attr('checked')){
					jQuery('#frmdwnedit #members_only_posts').fadeIn();	
				}else{
					jQuery('#frmdwnedit #members_only_posts').fadeOut();	
				}
			 });
			 // bind uploader
			 mgm_file_uploader('#download_manage', mgm_download_file_upload);			 
			 // date picker
			 mgm_date_picker("#frmdwnedit :input[name='expire_dt']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});
		});	 
		//-->
	</script>