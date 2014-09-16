<!--downloads-->
<div id="downloads">	
	<?php mgm_box_top('Downloads');?>
	<div id="download_list"></div>	
	<?php mgm_box_bottom();?>	
	
	<p>&nbsp;</p>
	
	<?php mgm_box_top('Manage Downloads');?>		
	<div id="download_manage"></div>
	<?php mgm_box_bottom()?>	
</div>
<script language="javascript">
	// onready
	jQuery(document).ready(function(){   
		// list
		mgm_download_list = function(){
			// hide old message
			mgm_hide_message('#downloads');
			// load
			jQuery('#download_list').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/downloads&method=lists'); 
		}
		// list filter
		mgm_download_list_filter = function(m) {
			var _m = m || false;
			// post
			jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/downloads&method=lists', 
				type: 'POST', cache:false, data : jQuery("#download_list #search-table :input").serialize(),
				beforeSend: function(){	
					// show message
					mgm_show_message('#download_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
				},
				success:function(data){																																		
					// append 
					jQuery('#download_list').html(data);					
					// show message
					if(_m){
						mgm_show_message('#downloads', {status:'success', message: jQuery('#download_list #last_search_message').html() }, true);
					}else{
						mgm_hide_message('#downloads');
					}								 
				}
			});
		}
		// add
		mgm_download_add = function(){			
			jQuery('#download_manage').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/downloads&method=add', function(){
				// focus
				jQuery.scrollTo('#download_manage',400);
			});
		}
		// edit
		mgm_download_edit = function(id){
			jQuery('#download_manage').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/downloads&method=edit',{id:id}, function(){
				// focus
				jQuery.scrollTo('#download_manage',400);
			});
		}
		// delete
		mgm_download_delete = function(id){			
			if(confirm('<?php _e('Are you sure you want to delete this download?','mgm')?>')){
				jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/downloads&method=delete', 
					type:'POST', 
					dataType: 'json',
					data:{id:id},
					 beforeSend: function(){	
						// show message
						mgm_show_message('#download_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'});								
						// focus scroll
						jQuery.scrollTo('#download_list',400);
					},
					success:function(data){								
						// success	
						if(data.status=='success'){																				
							// message																				
							mgm_show_message('#download_list', data);
							// remove row
							jQuery('#download_list #row-'+id).remove();
							// add
							mgm_download_add();									
							// none
							if(jQuery("#download_list #download_list tr[id^='row-']").size() == 0 ){
								jQuery('#download_list #download_list').append('<tr><td colspan="7"><?php _e('No downloads','mgm')?></td></tr>');
							}											
						}else{															
							// message																				
							mgm_show_message('#download_list', data);
						}	
					}
				});
			}
		}
		// bulk actions
		mgm_download_bulk_actions = function(){
			// selected action
			var _action = jQuery('#download_list #bulk_actions').val();
			// no action
			if(_action == '') return;			
			
			// selected users
			size = jQuery("#download_list #download_rows :input[type='checkbox']:checked").size();
						
			// error
			if(size == 0){
				var _text = jQuery('#download_list #bulk_actions option:selected').text().toLowerCase();
				alert('<?php _e('Please select some downloads to ','mgm')?>' + _text + '! '); return;
			}
			
			// delete warning
			if(_action == 'delete'){
				if(!confirm('<?php _e('You are about to delete [count] download(s)! are you sure?','mgm')?>'.replace('[count]', size))) return;
			}
			// bulk update
			jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/downloads&method=bulk_update', 
				dataType: 'json', type: 'POST', cache:false, 
				data : jQuery("#download_list #download_rows :input[type='checkbox']:checked").serialize() + '&_action='+_action,
				beforeSend: function(){	
					// show message
					mgm_show_message('#download_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
				},
				success:function(data){							
					// show message
					mgm_show_message('#download_list', data);		
					// show list after delete/status update
					window.setTimeout('mgm_download_list()', 5000);						 
				}
			});
		}
		// list
		mgm_download_list();
		// add
		mgm_download_add();			
	});
</script>