<!--subscription_options-->
<?php mgm_box_top('Subscription Packages/Options','subscriptionoptions')?>
	<form name="frmsubspkgs" id="frmsubspkgs" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=subscription_packages_update" method="post">
		<div id="subscription_packages_list">
			<?php echo mgm_get_loading_icon();?>
		</div>
	</form>	
<?php mgm_box_bottom()?>	

<?php mgm_box_top('Membership Types','magicmembershiptypes')?>
	<form name="frmmshiptypes" id="frmmshiptypes" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=membership_type_update" method="post">
	
		<div class="table widefatDiv width100">
			<div class="row headrow">		
				<div class="cell theadDivCell width40 textalignleft">	
					<b><?php _e('Membership Type','mgm') ?></b>
				</div>
				<div class="cell theadDivCell width60 textalignleft">	
					<b><?php _e('Options','mgm') ?></b>
				</div>
			</div>
			<div class="tbodyDiv" id="membership_types_list">
				<!--membership types list will be loaded here-->			
			</div>
		</div>
		
		<p>&nbsp;</p>
		
		<div class="table widefatDiv">
			<div class="row headrow">		
				<div class="cell theadDivCell width40 textalignleft">	
					<b><?php _e('New Membership Type','mgm') ?></b>
				</div>							
			</div>
			<div class="row">	
				<div class="cell textalignleft width40">	
					<b><?php _e('Membership Type','mgm');?>:</b>
				</div>
			</div>
			<div class="row">
				<div class="cell textalignleft width60">	
					<input type="text" name="new_membership_type" size="100" maxlength="250"/>
				</div>							
			</div>	
			<div class="row">		
				<div class="cell">									
					<div class="tips width95">
						<?php _e('250 Characters max','mgm');?>
					</div>											
				</div>
			</div>
			<div class="row">
				<div class="cell textalignleft width40">	
					<b><?php _e('Login Redirect','mgm');?>:</b>
				</div>
			</div>					
			<div class="row">
				<div class="cell textalignleft width60">	
					<input type="text" name="new_login_redirect_url" size="100" maxlength="1000"/>
				</div>
			</div>
			<div class="row">	
				<div class="cell textalignleft width40">	
					<b><?php _e('Logout Redirect','mgm');?>:</b>
				</div>
			</div>
			<div class="row">
				<div class="cell textalignleft width60">	
					<input type="text" name="new_logout_redirect_url" size="100" maxlength="1000"/>
				</div>
			</div>	
			<div class="row brBottom">		
				<div class="cell">						
					<div class="tips width95">
						<?php _e('Please provide a new Membership Type and click on update. Please do not use any special characters in Membership Type name.','mgm'); ?>
					</div>								
				</div>
			</div>
			<div class="row">		
				<div class="cell">	
					 <input class="button" type="button" name="membership_type_update" value="<?php _e('Update','mgm') ?>" onclick="mgm_update_membership_types()"/>				
				</div>
			</div>					
		</div>
	</form>	
	<script language="javascript">
		<!--
		var arr_pack_role = new Array();
		jQuery(document).ready(function(){	
								
			// update membership types
			mgm_update_membership_types=function(){	
				var del_cnt = jQuery("#frmmshiptypes :checkbox[name='remove_membership_type[]']:checked").size();
				if(del_cnt>0){
					if(!confirm('<?php _e('Are you sure, membership types selected for deletion will also remove all the packs under it?')?>')){
						return;
					}
				}
				// proceed						
				jQuery("#frmmshiptypes").ajaxSubmit({type: "POST",
				  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=membership_type_update',
				  dataType: 'json',			
				  iframe: false,								 
				  beforeSubmit: function(){	
					// show message
					mgm_show_message('#frmmshiptypes', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);																			
				  },
				  success: function(data){	
						// message																				
						mgm_show_message('#frmmshiptypes', data);													
						// success	
						if(data.status=='success'){
							// clear fields
							jQuery("#frmmshiptypes :input[name='new_membership_type']").val('');		
							// clear fields
							jQuery("#frmmshiptypes :input[name='new_login_redirect_url']").val('');																				
							// clear fields
							jQuery("#frmmshiptypes :input[name='new_logout_redirect_url']").val('');																				
							// pkgs lists	
							mgm_get_subscription_packages();
							// types lists
							mgm_get_membership_types();												
						}														
				  }}); // end   				
			}
			
			// get subscription packages
			mgm_get_subscription_packages=function(){
				// waiting - issue #1297
				if(document.getElementById('waiting')){ 
					var waiting = jQuery('#subscription_packages_list #waiting').show();
				}
				jQuery('#subscription_packages_list').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=subscription_packages_list', function(){
					// set up accordian
					jQuery("#subs_pkgs_panel").accordion({
						collapsible: true,
						active: false,
						<?php if( mgm_compare_wp_version('3.6', '>=') ):?>
						heightStyle: 'content'
						<?php else:?>
						autoHeight: true,
						fillSpace: false,
						clearStyle: true
						<?php endif;?>						
					});		
					// wp3.6+
					jQuery( "#subs_pkgs_panel" ).accordion( "refresh" );		
				});	
			}
			
			// mgm_get_membership_types
			mgm_get_membership_types=function(){
				// load types list
				jQuery('#membership_types_list').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=membership_types_list', function(){
					// enable delete/move type
					jQuery("#membership_types_list :checkbox[name='remove_membership_type[]']").bind('click', function(){				
						//jQuery("select[name='move_membership_type_to["+jQuery(this).val().toString().keyslug()+"]']").attr('disabled', !jQuery(this).attr('checked'));
						jQuery("select[name='move_membership_type_to["+jQuery(this).val()+"]']").attr('disabled', !jQuery(this).attr('checked'));
					});	
					// enable/disable login redirect					
					jQuery("#membership_types_list :checkbox[name='update_login_redirect_url[]']").bind('click', function(){				
						//jQuery(":input[name='login_redirect_url["+jQuery(this).val().toString().keyslug()+"]']").attr('disabled', !jQuery(this).attr('checked'));
						jQuery(":input[name='login_redirect_url["+jQuery(this).val()+"]']").attr('disabled', !jQuery(this).attr('checked'));
					});
					// enable/disable logout redirect					
					jQuery("#membership_types_list :checkbox[name='update_logout_redirect_url[]']").bind('click', function(){										
						jQuery(":input[name='logout_redirect_url["+jQuery(this).val()+"]']").attr('disabled', !jQuery(this).attr('checked'));
					});										
				});	
			}	
			
			// mgm_toggle_mt_advanced
			mgm_toggle_mt_advanced= function(id){
				// img
				var img = jQuery('#'+id+'-trig').find("img");
				// show
				if(img.attr('src').indexOf('plus.png') != -1){
					// chnage image
					img.attr('src', img.attr('src').replace('plus.png','minus.png'));
					// show
					jQuery('#'+id).fadeIn('slow');
				}else if(img.attr('src').indexOf('minus.png') != -1){
					// change image
					img.attr('src', img.attr('src').replace('minus.png','plus.png'));
					// hide
					jQuery('#'+id).fadeOut('slow');
				}				
			}
			//check duration_type:
			mgm_check_pack_duration = function(pack_ctr, type, duration, billing) {		
				if(type == 'l') {// life time
					jQuery(':input[name="packs['+pack_ctr+'][duration]"]').val('1').attr('readonly', true).hide();	
					jQuery("span[id^='packs_"+pack_ctr+"_num_cycles']").not("[id$='_num_cycles_1']").hide();	
					jQuery(':radio[name="packs['+pack_ctr+'][num_cycles]"]:eq(1)').attr('checked', true);
					jQuery('#packs_'+pack_ctr+'_duration_range_start_dt_zone').hide();		
					jQuery('#packs_'+pack_ctr+'_duration_range_start_dt_zone :input').val('').attr('disabled', true);
					//disabled trail for one time
					jQuery('.trail_pack_'+pack_ctr).fadeOut();			
					jQuery('.pack_trial_'+pack_ctr).fadeOut();
									
				}else if(type == 'dr') {// date range
					jQuery(':input[name="packs['+pack_ctr+'][duration]"]').val('1').attr('readonly', true).hide();	
					jQuery("span[id^='packs_"+pack_ctr+"_num_cycles']").not("[id$='_num_cycles_1']").hide();
					jQuery(':radio[name="packs['+pack_ctr+'][num_cycles]"]:eq(1)').attr('checked', true);						
					jQuery('#packs_'+pack_ctr+'_duration_range_start_dt_zone').show();	
					jQuery('#packs_'+pack_ctr+'_duration_range_start_dt_zone :input').attr('disabled', false);
					if(!jQuery('#packs_'+pack_ctr+'_duration_range_start_dt_zone .date').hasClass('hasDatepicker')){					
						mgm_date_picker('#packs_'+pack_ctr+'_duration_range_start_dt_zone .date','<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});
					}
					//disabled trail for one time
					jQuery('.trail_pack_'+pack_ctr).fadeOut();			
					jQuery('.pack_trial_'+pack_ctr).fadeOut();
															
				}else {// rest dates
					jQuery("span[id^='packs_"+pack_ctr+"_num_cycles']").show();	
					jQuery(':input[name="packs['+pack_ctr+'][duration]"]').val(duration).attr('readonly', false).show();
					jQuery('#packs_'+pack_ctr+'_duration_range_start_dt_zone').hide();
					jQuery('#packs_'+pack_ctr+'_duration_range_start_dt_zone :input').val('').attr('disabled', true);						
				}	
				// disable limited 	
				if(jQuery(':radio[name="packs['+pack_ctr+'][num_cycles]"]:checked').val() != '2'){
					jQuery(':input[name="packs['+pack_ctr+'][num_cycles_limited]"]').attr('disabled',true);
				}
			}	
			
			// get packages
			mgm_get_subscription_packages();	
			// get types	
			mgm_get_membership_types();
		});
		//-->
	</script>
<?php mgm_box_bottom()?>