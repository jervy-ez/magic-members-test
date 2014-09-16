<!--post purchases-->
<div id="post_purchases">
	<?php mgm_box_top('Post Purchases/Gifts');?>
		<div id="post_purchase_manage"><?php echo mgm_get_loading_icon();?></div>
	<?php mgm_box_bottom();?>
	
	<?php mgm_box_top('Post Purchase Statistics');?>
		<div id="post_purchase_statistics"><?php echo mgm_get_loading_icon();?></div>
	<?php mgm_box_bottom();?>
	
	<?php mgm_box_top('Gift a Post/Page');?>
		<div id="post_purchase_gift"><?php echo mgm_get_loading_icon();?></div>
	<?php mgm_box_bottom();?>		
</div>

<script language="javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){   
		// load purchase statistics
		mgm_post_purchase_statistics=function(){
			// waiting
			waiting = jQuery('#post_purchases #post_purchase_statistics #waiting').show();
			// load
			jQuery('#post_purchase_statistics').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=post_purchase_statistics'); 
		}
		// load purchase manage
		mgm_post_purchase_manage=function(){
			// waiting 
			waiting = jQuery('#post_purchases #post_purchase_manage #waiting').show();
			// load
			jQuery('#post_purchase_manage').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=post_purchase_manage',function(){
				mgm_post_purchase_list();
			}); 
		}
		// load purchase list
		mgm_post_purchase_list=function(is_post){
			// post
			var is_post = is_post || false;						
			// data
			var data = is_post ? jQuery('#post_purchase_list #search-table :input').serializeArray(): {};
			// url
			var url = 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=post_purchase_lists';
			// url
			if(is_post) url += '&page_no=' + jQuery('#post_purchase_list #search-table #page_no_s').val(); 
			// hide old message
			mgm_hide_message('#post_purchase_list');
			// load on lists
			jQuery('#post_purchase_list').load(url, data); 
		}		
		// search list, keep the last query
		mgm_post_purchase_list_search=function(m) {
			var _m = m || false;
			// post
			jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=post_purchase_lists', 
				type: 'POST', cache:false, data : jQuery("#post_purchase_list #search-table :input").serialize(),
				beforeSend: function(){	
					// show message
					mgm_show_message('#post_purchase_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
				 },
				 success:function(data){																																		
					// append 
					jQuery('#post_purchase_list').html(data);					
					// show message
					if(_m){
						mgm_show_message('#post_purchase_list', {status:'success', message: jQuery('#post_purchase_list #last_search_message').html() }, true);
					}else{
						mgm_hide_message('#post_purchase_list');
					}								 
				 }
			});
		}			
		// load post purchase gift
		mgm_post_purchase_gift=function() {			
			// waiting
			waiting = jQuery('#post_purchases #post_purchase_gift #waiting').show();
			// load
			jQuery('#post_purchase_gift').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=post_purchase_gift'); 
		}
		// export
		mgm_post_purchase_export=function(m) {
			// waiting
			waiting = jQuery('#post_purchases #post_purchases_export_options #waiting').show();
			// var
			var _m = m || false;
			// export format
			jQuery("#post_purchase_list #search-table :input[name='export_format']").val(jQuery("#post_purchase_list select[id='select_export_format']").val());
			// post
			jQuery.ajax({
				url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=post_purchase_export', 
				type: 'POST', 
				cache:false, 
				data : jQuery("#post_purchase_list #search-table :input").serialize(),
				dataType:'json',
				beforeSend: function(){	
					// show message
					mgm_show_message('#post_purchase_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
				},
				success:function(data){																											
					// show message
					if(_m){						
						mgm_show_message('#post_purchase_list', data);	
						// set backup
						jQuery('#ifrm_post_purchase_export').attr('src', data.src);															
					}else{
						mgm_hide_message('#post_purchase_list');
					}		
					// hide waitng
					waiting.hide();						 
				}
			});
		}		
		
			
		// purchase statistics 
		mgm_post_purchase_statistics();
		// purchase manage	
		mgm_post_purchase_manage();	
		// purchase gift
		mgm_post_purchase_gift();			
	});		
	//-->
</script>		