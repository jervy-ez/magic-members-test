<!--custom_field_list-->
	<div class="table widefatDiv">
		<div class="row headrow">
			<div class="cell theadDivCell width10 padding5px">
				<b><?php _e('Active?','mgm')?></b>
			</div>
			<div class="cell theadDivCell width35 padding5px">
				<b><?php _e('Label','mgm')?></b>
			</div>
			<div class="cell theadDivCell width35 padding5px">
				<?php _e('Name/ID','mgm')?></b>
			</div>
			<div class="cell theadDivCell width10 padding5px">
				<b><?php _e('Type','mgm')?></b>
			</div>			
			<div class="cell theadDivCell width20 padding5px">
				<b><?php _e('Action','mgm')?></b>
			</div>			
		</div>
		<?php if($data['cf_obj']->custom_fields):?>	
		<div class="tbodyDiv" id="custom_field_rows">
			<?php
				// list by order first 
				foreach (array_unique($data['cf_obj']->sort_orders) as $id):
					// loop
					foreach($data['cf_obj']->custom_fields as $field):
						// active
						$active = false;
						// check
						if($field['id'] == $id):
							// active
							$active = true;
							// reapeat
							include('lists_row.php');
						endif;
					endforeach;
				endforeach;
				// list rest of inactive fields  
				foreach($data['cf_obj']->custom_fields as $field):
					// check
					if(!in_array($field['id'], $data['cf_obj']->sort_orders)):
						// id
						$id = $field['id'];
						// active
						$active = false;
						// reapeat
						include('lists_row.php');
					endif;
				endforeach;
			else:?>
			<div class="row">
				<div class="cell theadDivCell">
					<?php _e('There are no custom fields yet.','mgm')?>
				</div>
			</div>
			<?php endif;?>
		</div>
	</div>
	
<script language="javascript">
	<!--
	jQuery(document).ready(function(){								
		// tip
		// jQuery("#custom_field_rows a[rel]").overlay({effect: 'apple'});
		
		// bind sortable
		jQuery("#custom_field_rows").sortable({
			update:function(event, ui){						
				// id not set, not active, skip
				if(!ui.item.attr('id'))	return;				
				// id flag not active, skip
				if(!(/^active_/.test(ui.item.attr('id')))) return;						
				// if(!ui.item.children('td').children('input').attr('checked')) return;		
				if(!ui.item.children('div:first').children(":input[name='custom_fields[]']").attr('checked')) return;					
				// post
				jQuery.ajax({
					url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/custom_fields&method=lists_sort',
					type:'POST',
					dataType:'json',
					data:{sort_order:jQuery('#custom_field_rows').sortable('serialize')},
					success:function(data){
						mgm_show_message('#custom_field_list', data);										
					}
				});// ajax end					
			}
		});// sortable end		
		
		// bind active/inactive
		jQuery("#custom_field_rows :checkbox[name='custom_fields[]']").bind('click',function(){		
			// vars
			var id	     = jQuery(this).val();	
			var active   = (jQuery(this).attr('checked'))?'Y':'N';
			var $element = jQuery(this);
			// send
			jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/custom_fields&method=status_change',
				type:'POST',
				dataType:'json',
				data:{id:id, active:active},
				success:function(data){
					// show message
					mgm_show_message('#custom_field_list', data);
					// success
					if(data.status == 'success'){						
						// set id for sort						
						$element.parent().parent().attr('id', ((active == 'Y') ? 'active' : 'inactive') + '_custom_field_row_' + id);						
					}				
				}
			});
		});
	});
	//-->
</script>	