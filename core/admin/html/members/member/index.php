<!--members-->
<div id="members">	
	<?php mgm_box_top('Members');?>
	<div id="member_manage"><?php echo mgm_get_loading_icon();?></div>		
	<?php mgm_box_bottom();?>	
	
	<p>&nbsp;</p>	

	<?php mgm_box_top('Export Members');?>
	<div id="member_export"></div>
	<?php mgm_box_bottom();?>
</div>
<script language="javascript">
	<!--		
	// onready
	jQuery(document).ready(function(){   
		// load manage
		mgm_member_manage=function(){
			// waiting - issue #1297
			if(document.getElementById('waiting')) 
				var waiting = jQuery('#member_manage #waiting').show();
			// hide
			mgm_toggle_update_export(false, false);
			// load
			jQuery('#member_manage').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=member_manage', function(){
				mgm_member_list();
			}); 
		}		
		// load list
		mgm_member_list=function(is_post){
			// post
			var is_post = is_post || false;						
			// data
			var data = is_post ? jQuery('#member_list #search-table :input').serializeArray(): {};
			// url
			var url = 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=member_list';
			// url
			if(is_post) url += '&page_no=' + jQuery('#member_list #search-table #page_no_s').val(); 
			// hide old message
			mgm_hide_message('#member_list');
			// load on lists
			jQuery('#member_list').load(url, data, function(){				
				// load
				mgm_toggle_update_export(true);
			}); 
		}
		// toggle
		mgm_toggle_update_export =function(ld,sh){
			// defaults
			ld = ld || false;// load form
			sh = sh || (jQuery('#member_list #member_rows').find("tr[id^='member_row_']").size() == 0);// show hide form
			// size
			if(sh){
				// hide
				jQuery('#member_export').closest('.mgm-panel-box').hide();
				jQuery('#member_update').hide();
			}else{
				// show
				jQuery('#member_export').closest('.mgm-panel-box').show();
				jQuery('#member_update').show();				
			}						
			// load
			if(ld){
				// update form
				mgm_member_update();
				// export form
				mgm_member_export();
			}
		}
		// load update
		mgm_member_update=function(){
			// load
			jQuery('#member_update').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=member_update', function(){
				// jQuery.scrollTo('#member_update',400);
			}); 
		}	
		// load export
		mgm_member_export=function(){
			// load
			jQuery('#member_export').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=member_export', function(){
				// jQuery.scrollTo('#member_export',400);
			}); 
		}	
		// bulk actions
		mgm_member_bulk_actions = function(){
			// selected action
			var _action = jQuery('#member_list #bulk_actions').val();
			// no action
			if(_action == '') return;			
			
			// selected users
			size = jQuery("#member_list #member_rows :input[type='checkbox']:checked").size();
						
			// error
			if(size == 0){
				var _text = jQuery('#member_list #bulk_actions option:selected').text().toLowerCase();
				    //_text = (_action == 'check_rebill_status' )? 'update ' + _text : _text;
				alert('<?php _e('Please select some users to ','mgm')?>' + _text + '! '); return;
			}
			
			// delete warning
			if(_action == 'delete'){
				if(!confirm('<?php _e('You are about to delete [count] users! are you sure? \n\n This will only delete users from database, you will have to unsubscribe them manually in payment gateway.','mgm')?>'.replace('[count]', size))) return;
			}
			// bulk update
			jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=member_bulk_update', 
				dataType: 'json', type: 'POST', cache:false, 
				data : jQuery("#member_list #member_rows :input[type='checkbox']:checked").serialize() + '&_action='+_action,
				beforeSend: function(){	
					// show message
					mgm_show_message('#member_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
				 },
				 success:function(data){							
					// show message
					mgm_show_message('#member_list', data);		
					// show list after delete/status update
					window.setTimeout('mgm_show_member_list()', 5000);						 
				 }
			});
		}
		// show list, keep the last query
		mgm_show_member_list=function(m) {
			var _m = m || false;
			// post
			jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/members&method=member_list', 
				type: 'POST', cache:false, data : jQuery("#search-table :input").serialize(),
				beforeSend: function(){	
					// show message
					mgm_show_message('#member_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
				 },
				 success:function(data){																																		
					// append 
					jQuery('#member_list').html(data);					
					// show message
					if(_m){
						mgm_show_message('#member_list', {status:'success', message: jQuery('#last_search_message').html() }, true);
					}else{
						mgm_hide_message('#member_list');
					}	
					// toggle
					mgm_toggle_update_export(false);						 
				 }
			});
		}
		// uncheck
		mgm_uncheck_other_memberships = function(chk, user_id) {					
			// set
			if(chk.checked) jQuery('#user_' + user_id).attr('checked', true);
			// loop
			jQuery('#membership_tree_'+user_id+' input[type="checkbox"]:checked').each(function() { 		       			        		        		        	
	        	if(chk.value != jQuery(this).val()) jQuery(this).removeAttr('checked');		        	
		    });	    
		}		
		// manage  
		mgm_member_manage();					
	});
	//-->
</script>