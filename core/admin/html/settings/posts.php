<!--setup-->
<?php header('Content-Type: text/html; charset=UTF-8');?>
<div id="post_settings_message"></div>
<?php mgm_box_top('Manage Post/Page/Custom Post Type(s) Access & Protection Settings');?>
	<form name="frmpostsaccess" id="frmpostsaccess" method="post" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=post_settings_delete">		
	<div class="table widefatDiv">
		<div class="row headrow">
			<div class="cell theadDivCell width45">
				<b><?php _e('Post/Page/Custom Post Type','mgm')?></b>
			</div>
			<div class="cell theadDivCell width25">
				<b><?php _e('Memberships','mgm')?></b>
			</div>
			<div class="cell theadDivCell width20">
				<?php _e('Action','mgm')?></b>
			</div>
		</div>
		<div class="tbodyDiv" id="posts_access_list">
				<?php include('posts/posts_access.php');?>		
		</div>
	</div>

	</form>
<?php mgm_box_bottom();?>

<?php mgm_box_top('Manage Direct URL Access & Protection Settings');?>
	<form name="frmdirecturlsaccess" id="frmdirecturlsaccess" method="post" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=post_settings_delete">

	<div class="table widefatDiv">
		<div class="row headrow">
			<div class="cell theadDivCell width45">
				<b><?php _e('URL','mgm')?></b>
			</div>
			<div class="cell theadDivCell width35">
				<b><?php _e('Memberships','mgm')?></b>
			</div>
			<div class="cell theadDivCell width20">
				<?php _e('Action','mgm')?></b>
			</div>
		</div>
		<div class="tbodyDiv" id="direct_urls_access_list">
				<?php include('posts/direct_urls_access.php');?>		
		</div>
	</div>

	</form>
<?php mgm_box_bottom();?>

<?php mgm_box_top('Add/Edit Post/Page(s) Access & Protection Settings');?>
	<form name="frmsetupposts" id="frmsetupposts" method="post" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=posts">
	<div class="table">

		<div class="row">
			<?php if(count($data['posts']) > 0):?>
    		<div class="cell mgm_add_edit_post">
				<input type="checkbox" name="check_all" value="access_membership_types[]" title="<?php _e('Select all','mgm'); ?>" /> 				
				&nbsp;<b><?php _e('Please select one or more membership type','mgm'); ?>:</b>
			</div>
			<?php endif;?> 
			<?php if(count($data['posts']) == 0):?>
			<div class="cell mgm_add_edit_post">
				&nbsp;<b><?php _e('Please select one or more membership type','mgm'); ?>:</b>
			</div>
			<?php endif;?> 
		</div>
		<div class="row">
    		<div class="cell paddingtop10px">
				<?php if(count($data['membership_types']) > 0):
					echo mgm_make_checkbox_group('access_membership_types[]', $data['membership_types'], '', MGM_KEY_VALUE);
				else:
					_e('Sorry, no membership types available.', 'mgm');
				endif;?>
			</div>
		</div>

		<div class="row">
			<?php if(count($data['posts']) > 0):?>
    		<div class="cell mgm_add_edit_post" >
				 <input type="checkbox" name="purchasable" id="purchasable" value="Y"/> &nbsp;
				 <b><?php _e('Is Purchasable (if membership type has no access)','mgm'); ?>:</b>
			</div>
			<?php endif;?>
			<?php if(count($data['posts']) == 0):?>
  			<div class="cell mgm_add_edit_post" ><b><?php _e('Is Purchasable (if membership type has no access)','mgm'); ?>:</b></div>		
  			<?php endif;?>
		</div>
		<div class="row">
    		<div class="cell paddingtop10px width120px" >
				<b><?php _e('Purchase Cost','mgm'); ?>:</b>
			</div>
    		<div class="cell paddingtop10px">
				<input type="text" name="purchase_cost" id="purchase_cost" value="" disabled="disabled" class="width75px height22px"/> 
			</div>
		</div>
		<div class="row">&nbsp;</div>
		<div class="row mgm_add_edit_post">
			<?php if(count($data['posts']) > 0):?>
    		<div class="cell">
				<input type="checkbox" name="check_all" value="posts[]" <?php _e('Select all','mgm'); ?>/>
				&nbsp;<b><?php _e('Please select one or more POSTS to attach the selected membership types','mgm'); ?>:</b>
			</div>
			<?php endif;?>
			<?php if(count($data['posts']) == 0):?>
			<div class="cell mgm_add_edit_post">
				<b><?php _e('Please select one or more POSTS to attach the selected membership types','mgm'); ?>:</b>
			</div>
			<?php endif;?>
		</div>
		<div class="row">
			<?php if(count($data['posts']) == 0): ?>
			<div class="cell paddingtop10px">
				<?php _e('There are no posts in the database or all marked as private.', 'mgm'); ?>
			</div>
			<?php else:?>
			<?php $post_chunks = array_chunk($data['posts'], ceil(count($data['posts'])/2), true);?>					
			<div class="cell paddingtop10px width50">		
				<?php if(isset($post_chunks[0])): foreach($post_chunks[0] as $post_id => $post_title):?>
				<input type="checkbox" name="posts[]" value="<?php echo $post_id?>" />
				<?php echo mgm_ellipsize($post_title,50)?> <br />
				<?php endforeach; endif;?>
			</div>
			<div class="cell paddingtop10px width50">		
				<?php if(isset($post_chunks[1])): foreach($post_chunks[1] as $post_id => $post_title):?>
				<input type="checkbox" name="posts[]" value="<?php echo $post_id?>" />
				<?php echo mgm_ellipsize($post_title,50)?> <br />
				<?php endforeach; endif;?>
			</div>
			<?php endif;?>
		</div>
		<div class="row">&nbsp;</div>
		<div class="row mgm_add_edit_post">
			<?php if(count($data['pages']) > 0):?>
    		<div class="cell mgm_add_edit_post">
				<input type="checkbox" name="check_all" value="pages[]" title="<?php _e('Select all','mgm'); ?>" /> 
				&nbsp;<b><?php _e('Please select one or more PAGES to attach the selected membership types','mgm'); ?>:</b>
			</div>
			<?php endif;?>
			<?php if(count($data['pages']) == 0):?>
			<div class="cell mgm_add_edit_post">
				<b><?php _e('Please select one or more POSTS to attach the selected membership types','mgm'); ?>:</b>
			</div>
			<?php endif;?>
		</div>
		<div class="row">
			<?php if(count($data['pages']) == 0): ?>
			<div class="cell paddingtop10px">
				<?php _e('There are no pages in the database or all marked as private.', 'mgm'); ?>
			</div>
			<?php else:?>
			<?php $post_chunks = array_chunk($data['pages'], ceil(count($data['pages'])/2), true);?>
			<div class="cell paddingtop10px width50">		
				<?php if(isset($post_chunks[0])): foreach($post_chunks[0] as $post_id => $post_title):?>
				<input type="checkbox" name="pages[]" value="<?php echo $post_id?>" />
				<?php echo mgm_ellipsize($post_title,50)?> <br />
				<?php endforeach; endif;?>
			</div>
			<div class="cell paddingtop10px width50">		
				<?php if(isset($post_chunks[1])): foreach($post_chunks[1] as $post_id => $post_title):?>
				<input type="checkbox" name="pages[]" value="<?php echo $post_id?>" />
				<?php echo mgm_ellipsize($post_title,50)?> <br />
				<?php endforeach; endif;?>
			</div>
			<?php endif;?>
		</div>
		<div class="row">&nbsp;</div>
		<div class="row mgm_add_edit_post">
			<?php if(count($data['custom_post_types']) > 0):?>
    		<div class="cell mgm_add_edit_post">
				<input type="checkbox" name="check_all" value="custom_post_types[]" title="<?php _e('Select all','mgm'); ?>" /> 
				&nbsp;<b><?php _e('Please select one or more CUSTOM POST TYPES to attach the selected membership types','mgm'); ?>:</b>
			</div>
			<?php endif;?>
			<?php if(count($data['custom_post_types']) == 0):?>
			<div class="cell mgm_add_edit_post">
				<b><?php  _e('Please select one or more CUSTOM POST TYPES to attach the selected membership types','mgm'); ?>:</b>
			</div>
			<?php endif;?>
		</div>
		<div class="row">
			<?php if(count($data['custom_post_types']) == 0): ?>
			<div class="cell paddingtop10px">
				<?php _e('There are no custom post types in the database or all marked as private.', 'mgm'); ?>
			</div>
			<?php else:?>
			<?php $post_chunks = array_chunk($data['custom_post_types'], ceil(count($data['custom_post_types'])/2), true);?>
			<div class="cell paddingtop10px width50">		
				<?php if(isset($post_chunks[0])): foreach($post_chunks[0] as $post_id => $post_title):?>
				<input type="checkbox" name="custom_post_types[]" value="<?php echo $post_id?>" />
				<?php echo mgm_ellipsize($post_title,50)?> <br />
				<?php endforeach; endif;?>
			</div>
			<div class="cell paddingtop10px width50">		
				<?php if(isset($post_chunks[1])): foreach($post_chunks[1] as $post_id => $post_title):?>
				<input type="checkbox" name="custom_post_types[]" value="<?php echo $post_id?>" />
				<?php echo mgm_ellipsize($post_title,50)?> <br />
				<?php endforeach; endif;?>
			</div>
			<?php endif;?>
		</div>
		<div class="row">&nbsp;</div>
		<div class="row">
			<div class="cell mgm_add_edit_post">		
				<b><?php _e('Please add Direct URLs to attach the selected membership types','mgm'); ?>:</b>
			</div>
		</div>
		<div class="row">
			<div class="cell paddingtop10px width75px">		
				<b><?php _e('New URL','mgm');?>:</b> 
			</div>
			<div class="cell paddingtop10px">		
				<input type="text" name="direct_urls[0]" id="direct_urls_0" size="100" value="" />
			</div>
		</div>
		<div class="row">
			<div class="cell height10px">		
				<div class="tips"><?php _e('<b>Available Wildcards:</b> All Sub pages - [URL]<b>:any</b> OR [URL]<b>*</b>','mgm')?></div>
			</div>
		</div>
		<?php if(mgm_protect_content() == false):?>
		<div class="row">
			<div class="cell height10px">
				<div class="information"><?php echo sprintf(__('<a href="%s">Content Protection</a> is <b>%s</b>. Make sure its enabled to Protect Post/Page(s).','mgm'), 'javascript:mgm_set_tab_url(2,0)', (mgm_protect_content() ? 'enabled' :'disabled'))?></div>	
			</div>
		</div>			
		<?php endif;?>
		<div class="row">&nbsp;</div>
		<div class="row">
			<div class="cell height10px">
				<input type="checkbox" name="add_private_tags" 	value="Y" <?php if(mgm_get_class('system')->setting['add_private_tags'] == "Y"){ echo "checked='checked'";} ?>/>
				&nbsp;<b><?php _e('Add Private Tags','mgm'); ?></b>
			</div>
		</div>			
		<div class="row">
			<div class="cell">
				<div class="tips"><?php _e('Will wrap full page content with [private] [/private] tags','mgm')?></div>
			</div>
		</div>			
		<div class="row">
			<div class="cell">
				<p class="submit floatleft" >
					<?php if (count($data['membership_types']) && (count($data['posts']) || count($data['pages'])) ) :?>
					<input type="button" name="btn_setup_posts" value="<?php _e('Setup Posts','mgm') ?>" onclick="mgm_setup_posts()" />
					<?php endif;?>	
				</p>
			</div>
		</div>			
	</div>	
	<input type="hidden" name="post_setup_save" value="true" />	
	</form>
<?php mgm_box_bottom();?>
<script language="javascript">
	<!--
	jQuery(document).ready(function(){			
		// post_setup
		mgm_setup_posts= function(undo){
			// undo
			var undo = undo || 'N';
			// set
			jQuery("#frmsetupposts :input[name='undo_post_setup']").val(undo);
			// add : form validation
			jQuery("#frmsetupposts").validate({
				submitHandler: function(form) {					    					
					jQuery("#frmsetupposts").ajaxSubmit({type: "POST",										  
					  dataType: 'json',		
					  iframe: false,									 
					  beforeSubmit: function(){	
						// show message
						mgm_show_message('#frmsetupposts', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);													
					  },
					  success: function(data){	
					  	// show message
						mgm_show_message('#frmsetupposts', data);
						// reset 
						jQuery('#direct_urls_0').val('');
						jQuery("#frmsetupposts :input[type='checkbox']").attr('checked', false);
						// reload
						mgm_load_posts_access_list();	
						mgm_load_direct_urls_access_list();												
					  }}); // end   		
					return false;											
				},			
				errorClass: 'invalid'
			});	
			// trigger
			jQuery('#frmsetupposts').submit();
		}
		// load posts_access_list
		mgm_load_posts_access_list=function(){
			// html
			_html = '<div class="row"><div class="cell textaligncenter"><img src="<?php echo MGM_ASSETS_URL?>images/ajax/fb-loader.gif"> <?php _e('Refreshing...','mgm')?></div></div>';
			// load
			jQuery('#posts_access_list').html(_html).load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=post_posts_access_list');
		}
		// load direct_urls_access_list
		mgm_load_direct_urls_access_list=function(){
			// html
			_html = '<div class="row"><div class="cell textaligncenter"><img src="<?php echo MGM_ASSETS_URL?>images/ajax/fb-loader.gif"> <?php _e('Refreshing...','mgm')?></div></div>';
			// load
			jQuery('#direct_urls_access_list').html(_html).load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=post_direct_urls_access');
		}
		// delete
		mgm_delete_protected_url=function(id, type){
			if (confirm("<?php _e('Are you sure you want to delete selected access setting?', 'mgm')?>")) {
				jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=post_settings_delete', type: 'POST', dataType: 'json', cache: false, data :{id: id}, 
				 beforeSend: function(){	
					// show message
					mgm_show_message('#post_settings_message', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);									
				 },
				 success:function(data){
					// show message
					mgm_show_message('#post_settings_message', data);																						
					// success	
					if(data.status=='success'){																																			
						// delete row
						jQuery('#'+type+'_row_'+id).remove();											
					}
				 }
				});
			}
		}
		// check bind
		jQuery("#frmsetupposts :checkbox[name='check_all']").bind('click',function(){
			var checked = (jQuery(this).attr('checked') == 'checked') ? true : false;
			// switch checked state
			jQuery("#frmsetupposts :checkbox[name='"+jQuery(this).val()+"']").attr('checked', checked);			
		});	
		
		jQuery('#purchasable').bind('click', function(){
			  	
			jQuery('#purchase_cost').attr('disabled', !jQuery(this).attr('checked'))
		})
	});
	//-->
</script>