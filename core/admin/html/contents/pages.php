<!--page page_access-->
<div id="content_page_access">
	<form name="frmpageaccss" id="frmpageaccss" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/contents&method=pages" method="post">	
		<?php mgm_box_top('Page Exclusion Settings');?>
		<div class="table">
	  		<div class="row">
	  			<?php if(count($data['pages']) > 0):?>
	    		<div class="cell mgm_page_check_all width20px">
					<input type="checkbox" name="check_all" value="excluded_pages[]" title="<?php _e('Select all','mgm'); ?>" /> 
					&nbsp; <b><?php _e('Please select one or more PAGES to hide from site/menu','mgm'); ?>:</b>
	    		</div>
	    		<?php endif;?>
	    		<?php  if(count($data['pages']) == 0):?>
	    		<div class="cell mgm_page_check_all">
					<b><?php _e('Please select one or more PAGES to hide from site/menu','mgm'); ?>:</b>
	    		</div>
	    		<?php endif;?>
			</div>
		</div>
		<div class="table">
	  		<div class="row">
	  			<div class="cell paddingtop10px">
					<?php if(count($data['pages']) == 0): _e('There are no pages in the database to select.', 'mgm'); else:?>	
					<?php $post_chunks = array_chunk($data['pages'], ceil(count($data['pages'])/2), true);?>				
					<div class="table">
				  		<div class="row">
				  			<div class="cell width50">
								<?php if(isset($post_chunks[0])): foreach($post_chunks[0] as $post_id => $post_title):?>
								<input type="checkbox" name="excluded_pages[]" value="<?php echo $post_id?>" <?php echo in_array($post_id,$data['pages_excluded'])? 'checked':''?>/>
								<?php echo mgm_ellipsize($post_title,50)?> <br />
								<?php endforeach; endif;?>
				  			</div>
				  			<div class="cell width50">
								<?php if(isset($post_chunks[1])): foreach($post_chunks[1] as $post_id => $post_title):?>
								<input type="checkbox" name="excluded_pages[]" value="<?php echo $post_id?>" <?php echo in_array($post_id,$data['pages_excluded'])? 'checked':''?>/>
								<?php echo mgm_ellipsize($post_title,50)?> <br />
								<?php endforeach; endif;?>				  			
				  			</div>
				  		</div>
				  	</div>
					<?php endif;?>
	  			</div>
			</div>
		</div>		
			
		<?php mgm_box_bottom()?>
		<p class="submit">
			<input class="button" type="submit" name="update" value="<?php _e('Save','mgm') ?>" />
		</p>
	</form>
</div>
<script language="javascript">
	<!--
	// onready
	jQuery(document).ready(function(){
		// add : form validation
		jQuery("#frmpageaccss").validate({
			submitHandler: function(form) {   
				jQuery("#frmpageaccss").ajaxSubmit({type: "POST",
				  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/contents&method=pages',
				  dataType: 'json',			
				  iframe: false,								 
				  beforeSubmit: function(){	
				  	// show message
					mgm_show_message('#content_page_access', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);						
				  },
				  success: function(data){	
			  		// message																				
					mgm_show_message('#content_page_access', data);																				
				  }});// end 
				  return false;															
			}
		});		
		// check bind
		jQuery("#frmpageaccss :checkbox[name='check_all']").bind('click',function(){
			// state
			var checked = (jQuery(this).attr('checked') == 'checked') ? true : false;
			// switch checked state
			jQuery("#frmpageaccss :checkbox[name='"+jQuery(this).val()+"']").attr('checked', checked);			
		});						  
	});	
	//-->
</script>