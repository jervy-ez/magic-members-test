<div class="table widefatDiv width100">
	<div class="row headrow">
		<div class="cell theadDivCell">
			<b><?php _e('Subscription Packages','mgm')?></b>
		</div>
	</div>
	<div class="row">
		<div class="cell form_div_font">				
			<div class='mgm'>
				<div id="subs_pkgs_panel">
					<?php 			
					// loop membership types
					foreach($data['membership_types'] as $type_code=>$type) : if($type_code == 'guest') continue;						
						// for inactive free/trial hide the tab
						if(in_array($type_code, array('free','trial'))): if(!in_array('mgm_'.$type_code,$data['payment_modules'])) continue; endif;	?>		
					<?php if( mgm_compare_wp_version('3.6', '>=') ):?>
					<h3><b><?php echo mgm_stripslashes_deep($type)?></b></h3>
					<?php else:?>
					<h3><a href="#"><b><?php echo mgm_stripslashes_deep($type)?></b></a></h3>
					<?php endif;?>
					<div>											
						<!-- new package-->
						<div id="pkgs_<?php echo $type_code?>">
							<?php echo mgm_stripslashes_deep($data['membership'][$type_code])?>
						</div>						
						<div>	
							<a class="button" href="javascript:mgm_add_pack('<?php echo $type_code; ?>')"><?php _e('Add New Package','mgm') ?></a>
							<?php printf(__('in <b>%s</b>','mgm'), mgm_stripslashes_deep(mgm_ellipsize($type)));?>								
						</div>						
						<div class="clearfix"></div>						
					</div>
					<?php endforeach?>		
				</div>			
			</div>
		</div>
	</div>

	<div class="row">
		<div class="cell">
			<div class="floatleft">
				<input type="button" class="button" onclick="mgm_update_packs()" value="<?php _e('Update All Packages','mgm') ?>" />
			</div>	
		</div>
	</div>
</div>			
<script language="javascript">
<!--
jQuery(document).ready(function(){		
	// add pack
	mgm_add_pack = function(type_code){		
		jQuery.ajax({ url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=subscription_package',
			 type: 'POST',
			 cache: false,
			 dataType: 'html',
			 data: {type: type_code},
			 beforeSend: function(){	
				// show message
				mgm_show_message('#frmsubspkgs', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);																			
			 },
			 success: function(data){	
			 	// message																				
				mgm_show_message('#frmsubspkgs', {status:'success', message:'<?php _e('Successfully created new subscription package','mgm')?>.'});
				// appened						 	
				jQuery('#pkgs_' + type_code).append(data);
				// scroll				
				jQuery.scrollTo('#pkgs_' + type_code + ' fieldset:last', 400);
			 }
		});
	}	
	// update pack
	mgm_update_packs = function(){	
		// data
		var data = jQuery('#frmsubspkgs').mgm_serialize_form({action: 'mgm_admin_ajax_action', page: 'mgm/admin/members', method: 'subscription_packages_update'});
		// submit		
		jQuery.ajax({ url: ajaxurl,
			 type: 'POST',
			 cache: false,
			 dataType: 'json',
			 data: data,
			 beforeSend: function(){	
				// check
				if(mgm_validate_pack_update() == false){
					return false;// stop process
				}
				// show message
				mgm_show_message('#frmsubspkgs', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);																			
			 },
			 success: function(data){	
			 	// message																				
				mgm_show_message('#frmsubspkgs', data);		
			 }
		});
	  // end 
	}	
	// validate
	mgm_validate_pack_update=function(){
		//free module check:
		var freemodule = <?php echo $data['free_module_enabled']?>;
		if(!freemodule) {
			var confirm_zero_cost = false;		   		
			jQuery("#frmsubspkgs select").each(function() {
				if(null != (this.id).match(/packs_membership_type_/) ) {		   				
					var arr_id = (this.id).split('packs_membership_type_');
					if(arr_id[1] != '') {
						var type = this.value;
						var cost = jQuery('#packs_cost_'+arr_id[1]).val();
						if(parseInt(cost) <= 0 && (type != 'free'&& type != 'trial') ) {								
							confirm_zero_cost = true;								
						}
					}
				}		   		
			});	 
			//check different role selected:
			var confirm_role = false;
			if(arr_pack_role.length > 0) {
				for(var r in arr_pack_role) {
					var current_role = jQuery('select[name="packs['+r+'][role]"]').val();			   				
					if(arr_pack_role[r] != current_role) {
						confirm_role = true;
					}
				}
				
				//if zero is entered for a paid subscription
				if(confirm_role) {
					if(!confirm('<?php _e('Are you sure, you have selected a different role for subscription pack. This will cause a mass update to the associated user data?','mgm')?>')) {
						return false;	   				
					}
				}
			}
			
			//if zero is entered for a paid subscription
			if(confirm_zero_cost) {
				if(!confirm('<?php _e('Are you sure, you have selected a zero cost for subscription and Free Payment module needs to be enabled for this to be processed?','mgm') ?>')) {
					return false;	   				
				}
			}
	   }
	   //duplicate check for move member's pack on expiry/cancellation
	   <?php if(bool_from_yn($data['enable_multiple_level_purchase'])): ?>
	   var arr_movepacks = [];
	   var error_movepack = false;
	   jQuery("#frmsubspkgs select[name*='move_members_pack']").each( function() {
			if(this.value != '') {
				if(typeof(arr_movepacks[this.value]) == 'undefined')
					arr_movepacks[this.value] = true;
				else{
					error_movepack = true;
					return;
				}
			}					
		});
		
		if(error_movepack) {
			if (!confirm('<?php _e('You have selected same pack for "When expired/cancelled, move members to:" field for multiple packs. Are you sure you want to save?','mgm') ?>'))
				return false;	
		}
	   <?php endif; ?>   		
	   
	   // ok
	   return true;		
	}
	// delete pack
	mgm_delete_pack	= function(index, id){
		// warn
		if(confirm('<?php _e('Are sure you want to delete selected package?','mgm') ?>')){			
			jQuery.ajax({ url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=subscription_package_delete',
				 type: 'POST',
				 cache: false,
				 dataType: 'json',
				 data: {index: (index-1), id: id},
				 beforeSend: function(){	
					// show message
					mgm_show_message('#frmsubspkgs', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);																			
				 },
				 success: function(data){	
				 	// message																				
					mgm_show_message('#frmsubspkgs', data);
				 	// success				 	
					if(data.status=='success'){
						// scroll
						jQuery.scrollTo('#subscription_packages_list #mgm_pack_' + id, 400);
						// delete row
						jQuery('#subscription_packages_list #mgm_pack_' + id).fadeOut('slow').remove();						
					}
				 }
			});
		}
	}		
});
//-->
</script>	