<!--addons-->
<div id="addons">
	<?php mgm_box_top('Addons');?>		
	<div id="addon_list"></div>	
	<?php mgm_box_bottom();?>
		
	<p>&nbsp;</p>
	
	<?php mgm_box_top('Manage Addons');?>
	<div id="addon_manage"></div>
	<?php mgm_box_bottom();?>	
</div>
<div id="addon_options"></div>	
<script language="javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){   
		// list
		mgm_addon_list=function(){
			// load
			jQuery('#addon_list').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=lists'); 			
		}
		// list filter 
		mgm_addon_list_filter = function(m) {
			var _m = m || false;
			// post
			jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=lists', 
				type: 'POST', cache:false, data : jQuery("#addon_list #search-table :input").serialize(),
				beforeSend: function(){	
					// show message
					mgm_show_message('#addon_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
				 },
				 success:function(data){																																		
					// append 
					jQuery('#addon_list').html(data);					
					// show message
					if(_m){
						mgm_show_message('#addon_list', {status:'success', message: jQuery('#addon_list #last_search_message').html()}, true);
					}else{
						mgm_hide_message('#addon_list');
					}								 
				 }
			});
		}
		// add
		mgm_addon_add=function(){
			// load
			jQuery('#addon_manage').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=add', function(){
				// focus
				jQuery.scrollTo('#addon_manage',400);
			}); 			
		}	
		// edit
		mgm_addon_edit=function(id) {
			// load add
			jQuery('#addon_manage').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=edit', {id:id}, function(){
				// focus
				jQuery.scrollTo('#addon_manage',400);
			}); 
		}
		// options
		mgm_addon_options=function(id) {
			// id
			if(id){
				// hide addons
				jQuery('#addons').slideUp('slow', function(){
					// load	options		
					jQuery('#addon_options').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=options',{id:id}, function(){
						jQuery('#addon_options').slideDown('slow');
					}); 
				});				
			}else{
				// clear and hide options
				jQuery('#addon_options').html('').slideUp('slow', function(){
					// show addons
					jQuery('#addons').slideDown('slow');
				});
			}
		}
		// delete	
		mgm_addon_delete=function(id) {
			if (confirm("<?php _e('Are you sure you want to delete this addon?', 'mgm')?>")) {
				jQuery.ajax({
				 url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=delete', 
				 type: 'POST', 
				 dataType: 'json', 
				 cache: false, 
				 data :{id: id}, 
				 beforeSend: function(){	
					// show message
					mgm_show_message('#addon_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);									
				 },
				 success:function(data){
					// show message
					mgm_show_message('#addon_list', data);																						
					// success	
					if(data.status=='success'){																																
						// delete row
						jQuery('#addon_row_'+id).remove();											
					}
				 }
				});
			}
		}			
		// bulk actions
		mgm_addon_bulk_actions = function(){
			// selected action
			var _action = jQuery('#addon_list #bulk_actions').val();
			// no action
			if(_action == '') return;			
			
			// selected users
			size = jQuery("#addon_list #addon_rows :input[type='checkbox']:checked").size();
						
			// error
			if(size == 0){
				var _text = jQuery('#addon_list #bulk_actions option:selected').text().toLowerCase();
				alert('<?php _e('Please select some addons to ','mgm')?>' + _text + '! '); return;
			}
			
			// delete warning
			if(_action == 'delete'){
				if(!confirm('<?php _e('You are about to delete [count] addon(s)! are you sure?','mgm')?>'.replace('[count]', size))) return;
			}
			// bulk update
			jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=bulk_update', 
				dataType: 'json', 
				type: 'POST', 
				cache:false, 
				data : jQuery("#addon_list #addon_rows :input[type='checkbox']:checked").serialize() + '&_action='+_action,
				beforeSend: function(){	
					// show message
					mgm_show_message('#addon_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
				},
				success:function(data){							
					// show message
					mgm_show_message('#addon_list', data);		
					// show list after delete/status update
					window.setTimeout('mgm_addon_list()', 5000);						 
				}
			});
		}
		// list 
		mgm_addon_list();
		// add 	
		mgm_addon_add();	
	});		
	//-->
</script>
