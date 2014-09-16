<!--addon purchases-->
<div id="addon_purchases">
	<?php mgm_box_top('Addon Purchases');?>
		<div id="addon_purchase_manage"><?php echo mgm_get_loading_icon();?></div>
	<?php mgm_box_bottom();?>		
</div>

<script language="javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){   		
		// load purchase manage
		mgm_addon_purchase_manage=function(){
			// waiting 
			waiting = jQuery('#addon_purchases #addon_purchase_manage #waiting').show();
			// load
			jQuery('#addon_purchase_manage').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=purchase_manage',function(){
				mgm_addon_purchase_list();
			}); 
		}		
		// load purchase list
		mgm_addon_purchase_list=function(is_post){
			// post
			var is_post = is_post || false;						
			// data
			var data = is_post ? jQuery('#addon_purchase_list #search-table :input').serializeArray(): {};
			// url
			var url = 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=purchase_lists';
			// url
			if(is_post) url += '&page_no=' + jQuery('#addon_purchase_list #search-table #page_no_s').val(); 
			// hide old message
			mgm_hide_message('#addon_purchase_list');
			// load on lists
			jQuery('#addon_purchase_list').load(url, data); 
		}
		// search list, keep the last query
		mgm_addon_purchase_list_search=function(m) {
			var _m = m || false;
			// post
			jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=purchase_lists', 
				type: 'POST', cache:false, data : jQuery("#addon_purchase_list #search-table :input").serialize(),
				beforeSend: function(){	
					// show message
					mgm_show_message('#addon_purchase_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
				 },
				 success:function(data){																																		
					// append 
					jQuery('#addon_purchase_list').html(data);					
					// show message
					if(_m){
						mgm_show_message('#addon_purchase_list', {status:'success', message: jQuery('#addon_purchase_list #last_search_message').html() }, true);
					}else{
						mgm_hide_message('#addon_purchase_list');
					}								 
				 }
			});
		}
		// export
		mgm_addon_purchase_export=function(m) {
			// export
			// waiting
			waiting = jQuery('#addon_purchases #addon_purchases_export_options #waiting').show();
			// var
			var _m = m || false;
			// export format
			jQuery("#addon_purchase_list #search-table :input[name='export_format']").val(jQuery("#addon_purchase_list select[id='select_export_format']").val());
			// post
			jQuery.ajax({
				url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=purchase_export', 
				type: 'POST', 
				cache:false, 
				data : jQuery("#addon_purchase_list #search-table :input").serialize(),
				dataType:'json',
				beforeSend: function(){	
					// show message
					mgm_show_message('#addon_purchase_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
				},
				success:function(data){																											
					// show message
					if(_m){
						mgm_show_message('#addon_purchase_list', data);	
						// set backup
						jQuery('#ifrm_addon_purchase_export').attr('src', data.src);															
					}else{
						mgm_hide_message('#addon_purchase_list');
					}		
					// hide
					waiting.hide();						 
				}
			});
		}				
		
		// purchase manage	
		mgm_addon_purchase_manage();			
	});		
	//-->
</script>		