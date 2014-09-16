	<script type="text/javascript">var arr_ul_ids = [];</script>		
	<?php //if(count($data['users']) > 0):?>
	<div class="mgm-search-box">	
		<?php include('search_box.php')?>
	</div>
	<?php //endif;?>
	<span id="last_search_message" class="displaynone"><?php echo $data['message']?></span>
	<table id="tree_container" width="100%" cellpadding="1" cellspacing="0" border="0" class="widefat form-table">
		<thead>
			<tr>
				<th scope="col" width="2" align="center"><input type="checkbox" name="check_all" value="members[]" title="<?php _e('Select all','mgm'); ?>" /></th>
				<th scope="col" width="14%"><b><?php _e('User','mgm') ?> [<?php _e('ID','mgm') ?>]</b></th>
				<th scope="col" width="15%"><b><?php _e('Membership','mgm') ?></b></th>
				<th scope="col" width="15%"><b><?php _e('Package','mgm') ?></b></th>
				<th scope="col" width="15%"><b><?php _e('Register','mgm') ?></b></th>
				<th scope="col" width="15%"><b><?php _e('Expiry','mgm') ?></b></th>
				<th scope="col" width="25%"><b><?php _e('Status','mgm') ?></b></th>
			</tr>
		</thead>
		<tbody id="member_rows">
			<?php include('lists_row.php')?>
		</tbody>
	</table>
	<div class="clearfix"></div>
	<?php if(count($data['users']) > 0):?>
	<div class="mgm_bulk_actions_div">
		<select name="bulk_actions" id="bulk_actions" class="width150px">
			<option value=""><?php _e('Bulk Actions','mgm')?></option>
			<?php echo mgm_make_combo_options(array('check_rebill_status'=>__('Check Rebill Status','mgm'), 'delete'=>__('Delete','mgm')), $data['bulk_actions'], MGM_KEY_VALUE);?>
		</select>
		<input type="button" name="apply_btn" class="button" value="<?php _e('Apply', 'mgm')?>" onclick="mgm_member_bulk_actions()"/>
	</div>	
	<div class="mgm_page_links_div">
		<?php if($data['page_links']):?><div class="pager-wrap"><?php echo $data['page_links']?></div><?php endif; ?>
	</div>	
	<div class="clearfix"></div>			
	
	<script language="javascript">
		<!--
		jQuery(document).ready(function(){
			// set pager anchor 2 post
			mgm_set_pager_anchor2post('#member_list', '#search-table');
			// set pager dropdown 2 post
			mgm_set_pager_select2post('#member_list', '#search-table', '<?php echo $data['page_url']?>');		
			// bind check all
			jQuery('#member_list').mgm_bind_check_all();			
			// render other membership tree:		
			if(arr_ul_ids.length > 0) for(var i = 0; i < arr_ul_ids.length; i++) jQuery('ul#'+arr_ul_ids[i]).collapsibleCheckboxTree();								
		});
		//-->	
	</script>
	<?php endif;?>