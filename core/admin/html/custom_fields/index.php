<!--custom_fields-->
<div id="custom_fields">
	<?php mgm_box_top('Custom Fields');?>
		<div id="custom_field_list"></div>
	<?php mgm_box_bottom()?>
		<p>&nbsp;</p>
	<?php mgm_box_top('Manage Custom Field');?>	
		<div id="custom_field_manage"></div>
	<?php mgm_box_bottom()?>
</div>
<script language="javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){   
		// load list	
		mgm_custom_field_list=function(){
			jQuery('#custom_field_list').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/custom_fields&method=lists');	
		}	
		// load add
		mgm_custom_field_add=function(){								
			jQuery('#custom_field_manage').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/custom_fields&method=add', function(){
				// focus
				jQuery.scrollTo('#custom_field_manage',400);
			}); 
		}
		// edit
		mgm_custom_field_edit=function(id) {
			// load add
			jQuery('#custom_field_manage').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/custom_fields&method=edit',{id:id}, function(){
				// focus
				jQuery.scrollTo('#custom_field_manage',400);
			}); 
		}
		// delete	
		mgm_custom_field_delete=function(id) {						
			if (confirm("<?php _e('Are you sure you want to delete this custom field?', 'mgm')?>")) {
				jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/custom_fields&method=delete', 
					 type: 'POST', dataType: 'json', data :{id: id}, cache: false,
					 beforeSend: function(){	
						// show message
						mgm_show_message('#custom_field_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);									
					 },
					 success:function(data){		
						// message																				
						mgm_show_message('#custom_field_list', data);					
						// success	
						if(data.status=='success'){		
							// delete row
							jQuery("#custom_field_rows div[id$='custom_field_row_"+id+"']").remove();											
						}	
					 }});
			}
		}
		
		// list
		mgm_custom_field_list();
		// add form
		mgm_custom_field_add();			
	});		
	//-->
</script>
