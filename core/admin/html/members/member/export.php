	<form name="mgmexportfrm" id="mgmexportfrm" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=member_export">		
		<div class="table widefatDiv">
			<div class="row brBottom">
				<div class="cell width150px">
					<b><?php _e('Membership Type','mgm') ?></b>
				</div>
				<div class="cell">
					<select name="bk_membership_type" onchange="this.form.bk_inactive.checked=(this.value!='all');" class="width200px">
						<option value="all"><?php _e('All','mgm') ?></option>
						<?php
						// init
						$strTypes = '';						
						// loop
						foreach(mgm_get_class('membership_types')->membership_types as $type_code=>$type_name):
							// skip guest
							if ($type_code == 'guest') continue;
							// append
							$strTypes .= sprintf('<option value="%s">%s</option>', $type_code, __(mgm_stripslashes_deep($type_name), 'mgm'));
						endforeach;
						// print
						echo $strTypes;?>
					</select>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell width150px">
					<b><?php _e('Status','mgm') ?></b>			
				</div>
				<div class="cell">
					<select name="bk_membership_status" id="bk_membership_status" onchange="this.form.bk_inactive.checked=(this.value!='all');" class="width200px">
						<option value="all"><?php _e('All','mgm') ?></option>
						<option value="<?php echo MGM_STATUS_NULL ?>"><?php echo esc_html(MGM_STATUS_NULL) ?></option>
						<option value="<?php echo MGM_STATUS_ACTIVE ?>"><?php echo esc_html(MGM_STATUS_ACTIVE) ?></option>
						<option value="<?php echo MGM_STATUS_EXPIRED ?>"><?php echo esc_html(MGM_STATUS_EXPIRED) ?></option>
						<option value="<?php echo MGM_STATUS_PENDING ?>"><?php echo esc_html(MGM_STATUS_PENDING) ?></option>
						<option value="<?php echo MGM_STATUS_ERROR ?>"><?php echo esc_html(MGM_STATUS_ERROR) ?></option>
						<option value="<?php echo MGM_STATUS_CANCELLED ?>"><?php echo esc_html(MGM_STATUS_CANCELLED) ?></option>						
					</select>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell width150px">
					<b><?php _e('Membership Expires','mgm') ?></b>			
				</div>
				<div class="cell">
					<input type="text" name="bk_msexp_dur_unit" size="3" maxlength="3" />
					<select name="bk_msexp_dur">
						<option value="day"><?php _e('Days','mgm') ?></option>
						<option value="week"><?php _e('Weeks','mgm') ?></option>
						<option value="month"><?php _e('Months','mgm') ?></option>
					</select>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell width150px">
					<b><?php _e('Date Range','mgm') ?></b>
				</div>
				<div class="cell">
					<?php _e('Start', 'mgm')?>: <input type="text" name="bk_date_start" size="10"/> <?php _e('End', 'mgm')?> <input type="text" name="bk_date_end" size="10"/>				
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell width150px">
					<b><?php _e('Others','mgm') ?></b>
				</div>
				<div class="cell">
					<input type="checkbox" class="checkbox" name="bk_inactive" id="bk_inactive" value="1" /> <?php _e('Exclude Expired Users','mgm') ?>
					<input type="checkbox" class="checkbox" name="bk_only_selected" id="bk_only_selected" value="1" /> <?php _e('Export Only Selected Users','mgm') ?>
					<input type="checkbox" class="checkbox" name="bk_users_to_import" id="bk_users_to_import" value="1" /> <?php _e('Export users to import','mgm') ?>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell width150px">
					<b><?php _e('Format','mgm') ?></b>
				</div>
				<div class="cell">
					<select name="bk_export_format">
						<option value="xls"><?php _e('XLS','mgm') ?></option>
						<option value="csv"><?php _e('CSV','mgm') ?></option>									
					</select>
				</div>
			</div>
		</div>		
		<div>		
			<p class="submit">
				<input class="button" type="submit" name="export_member_info" value="<?php _e('Export','mgm') ?>" />
			</p>
		</div>	
	</form>
	<iframe id="ifrm_backup" src="" allowtransparency="true" width="0" height="0" frameborder="0"></iframe>
	<!-- issue #1384 -->
	<?php $url = MGM_ASSETS_URL.'js/editor/plugins/downloads/csv_download.php'; ?>
    <form id="csv_download" name="csv_download" action="<?php echo $url; ?>">
    	<input type="hidden" name="csv_url" id="csv_url" value="" />
    </form>

    <script type="text/javascript" language="javascript">
		<?php
		$daterange = mgm_get_calendar_year_range();
		$dateformat = mgm_get_datepicker_format();
		?>
		<!--
		jQuery(document).ready(function(){		
			// dates		
			mgm_date_picker("#mgmexportfrm :input[name='bk_date_start']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo $daterange; ?>", dateFormat: "<?php echo $dateformat?>"});
			mgm_date_picker("#mgmexportfrm :input[name='bk_date_end']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo $daterange; ?>", dateFormat: "<?php echo $dateformat?>"});
			
			// submit
			jQuery("#mgmexportfrm").validate({
				submitHandler: function(form) {   
					jQuery("#mgmexportfrm").ajaxSubmit({type: "POST",
					  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=member_export',
					  dataType: 'json',			
					  iframe: false,	
					  beforeSerialize: function(form, options){
						bk_only_selected = (jQuery("#mgmexportfrm :input[name='bk_only_selected']").attr('checked') == 'checked');										
						if(bk_only_selected){
							jQuery("#mgmmembersfrm :input[name='members[]']:checked").each(function(){
								jQuery('#mgmexportfrm').append('<input type="hidden" name="bk_selected_members[]" value="'+jQuery(this).val()+'">');
							});
						}else{
							jQuery("#mgmexportfrm :input[name='bk_selected_members[]']").remove();
						}
					  },							 
					  beforeSubmit: function(form_data, form, options){					  	
						// show message
						mgm_show_message('#mgmexportfrm', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);		
					  },
					  success: function(data){	
						// message																				
						mgm_show_message('#mgmexportfrm', data);					
						// set backup
						if(data.status == 'success'){							
							//issue #1384
							var ext = data.src.split('.').pop().toLowerCase();							
							if(ext == 'csv') {
								jQuery('#csv_url').val(data.src);
								document.csv_download.submit();
							}else{
								jQuery('#ifrm_backup').attr('src', data.src);
							}
						}																							
					  }});// end 			  
				  return false;
				}
			});				
		});
		//-->
	</script>