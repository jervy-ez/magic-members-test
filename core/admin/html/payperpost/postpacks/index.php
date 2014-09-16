<!--postpacks-->
<div id="postpacks">
	<?php mgm_box_top('Post Packs');?>		
	<div id="postpack_list"></div>	
	<?php mgm_box_bottom();?>	
	
	<p>&nbsp;</p>
	
	<?php mgm_box_top('Post Pack Manage');?>
	<div id="postpack_manage"></div>
	<?php mgm_box_bottom();?>	
</div>	
<div id="postpack_posts"></div>	
<script language="javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){   
		// load list
		mgm_postpack_list=function(){
			jQuery('#postpack_list').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=postpack_list'); 
		}
		// load add
		mgm_postpack_add=function(){
			jQuery('#postpack_manage').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=postpack_add'); 
		}
		// load edit
		mgm_postpack_edit=function(id) {			
			jQuery('#postpack_manage').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=postpack_edit',{id:id}); 
		}
		// load posts within pack
		mgm_postpack_posts=function(id) {
			// id
			if(id){
				// hide
				jQuery('#postpacks').slideUp();
				// load			
				jQuery('#postpack_posts').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=postpack_posts',{pack_id:id}); 
			}else{
			// back to packs list
				// clear
				jQuery('#postpack_posts').html('');
				// show
				jQuery('#postpacks').slideDown();
			}
		}
		// delete	
		mgm_postpack_delete=function(id) {
			if (confirm("<?php _e('Are you sure you want to delete this pack? All posts within it will be removed and any shortcode references to it on the site will return false.', 'mgm')?>")) {
				jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=postpack_delete', 
							 type: 'POST', 
							 dataType: 'json', 
							 data :{id: id}, 
							 cache: false,
							 beforeSend: function(){
							 	// show message
								mgm_show_message('#postpack_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'});								 	
								// focus
								jQuery.scrollTo('#postpack_list',400);
							 },
							 success:function(data){
								// remove message										   														
								jQuery('#postpack_list #message').remove();									
																				
								// success	
								if(data.status=='success'){																							
									// create message
									jQuery('#postpack_list').prepend('<div id="message"></div>');
									// show
									jQuery('#postpack_list #message').addClass(data.status).html(data.message);	
									// delete row
									jQuery('#postpack_row_'+id).remove();											
								}else{															
									// create message
									jQuery('#postpack_list').prepend('<div id="message"></div>');
									// show
									jQuery('#postpack_list #message').addClass(data.status).html(data.message);
								}	
							 }
							});
			}
		}		
		// list 
		mgm_postpack_list();
		// add 	
		mgm_postpack_add();				
	});		
	//-->
</script>