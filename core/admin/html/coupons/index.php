<!--coupons-->
<div id="coupons">
	<?php mgm_box_top('Coupons');?>		
	<div id="coupon_list"></div>	
	<?php mgm_box_bottom();?>
		
	<p>&nbsp;</p>
	
	<?php mgm_box_top('Manage Coupons');?>
	<div id="coupon_manage"></div>
	<?php mgm_box_bottom();?>
	<div id="coupon_migrate"></div>	
</div>
<div id="coupon_users"></div>	
<script language="javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){   
		// list
		mgm_coupon_list=function(){
			// load
			jQuery('#coupon_list').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons&method=lists', function(){
				// focus
				jQuery.scrollTo('#coupons',400);
			}); 			
		}
		// list filter 
		mgm_coupon_list_filter = function(m) {
			var _m = m || false;
			// post
			jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons&method=lists', 
				type: 'POST', cache:false, data : jQuery("#coupon_list #search-table :input").serialize(),
				beforeSend: function(){	
					// show message
					mgm_show_message('#coupon_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
				 },
				 success:function(data){																																		
					// append 
					jQuery('#coupon_list').html(data);					
					// show message
					if(_m){
						mgm_show_message('#coupon_list', {status:'success', message: jQuery('#coupon_list #last_search_message').html() }, true);
					}else{
						mgm_hide_message('#coupon_list');
					}								 
				 }
			});
		}
		// add
		mgm_coupon_add=function(){
			// load
			jQuery('#coupon_manage').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons&method=add', function(){
				// focus
				jQuery.scrollTo('#coupon_manage',400);
			}); 			
		}	
		// edit
		mgm_coupon_edit=function(id) {
			// load add
			jQuery('#coupon_manage').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons&method=edit', {id:id}, function(){
				// focus
				jQuery.scrollTo('#coupon_manage',400);
			}); 
		}
		// users
		mgm_coupon_users=function(id) {
			// id
			if(id){
				// hide coupons
				jQuery('#coupons').slideUp('slow', function(){
					// load	users		
					jQuery('#coupon_users').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons&method=users', {id:id}, function(){
						jQuery('#coupon_users').slideDown('slow');
					}); 
				});				
			}else{
				// clear and hide users
				jQuery('#coupon_users').html('').slideUp('slow', function(){
					// show coupons
					jQuery('#coupons').slideDown('slow');
				});				
			}
		}
		// delete	
		mgm_coupon_delete=function(id) {
			if (confirm("<?php _e('Are you sure you want to delete this coupon?', 'mgm')?>")) {
				jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons&method=delete', 
				 type: 'POST', dataType: 'json', cache: false, data :{id: id}, 
				 beforeSend: function(){	
					// show message
					mgm_show_message('#coupon_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);									
				 },
				 success:function(data){
					// show message
					mgm_show_message('#coupon_list', data);																						
					// success	
					if(data.status=='success'){																																
						// delete row
						jQuery('#coupon_row_'+id).remove();											
					}
				 }});
			}
		}	
		
		// bulk actions
		mgm_coupon_bulk_actions = function(){
			// selected action
			var _action = jQuery('#coupon_list #bulk_actions').val();
			// no action
			if(_action == '') return;			
			
			// selected users
			size = jQuery("#coupon_list #coupon_rows :input[type='checkbox']:checked").size();
						
			// error
			if(size == 0){
				var _text = jQuery('#coupon_list #bulk_actions option:selected').text().toLowerCase();
				alert('<?php _e('Please select some coupons to ','mgm')?>' + _text + '! '); return;
			}
			
			// delete warning
			if(_action == 'delete'){
				if(!confirm('<?php _e('You are about to delete [count] coupon(s)! are you sure?','mgm')?>'.replace('[count]', size))) return;
			}
			// bulk update
			jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons&method=bulk_update', 
				dataType: 'json', type: 'POST', cache:false, 
				data : jQuery("#coupon_list #coupon_rows :input[type='checkbox']:checked").serialize() + '&_action='+_action,
				beforeSend: function(){	
					// show message
					mgm_show_message('#coupon_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
				},
				success:function(data){							
					// show message
					mgm_show_message('#coupon_list', data);		
					// show list after delete/status update
					window.setTimeout('mgm_coupon_list()', 5000);						 
				}
			});
		}
		
		// add
		mgm_coupon_migrate=function(){
			// load
			jQuery('#coupon_migrate').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons&method=manage_coupons', function(){
				// focus
				jQuery.scrollTo('#coupon_migrate',400);
			}); 			
		}		
		// list 
		mgm_coupon_list();
		// add 	
		mgm_coupon_add();
		
		mgm_coupon_migrate();	
	});		
	//-->
</script>
