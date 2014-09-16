	<!--addon list-->
	<form id="mgmaddonfrm" name="mgmaddonfrm" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons" method="post">	
		<div class="mgm-search-box">
			<?php include('search_box.php')?>			
		</div>
		<div class="clearfix"></div>
		<div class="table widefatDiv width830px">
			<div class="row headrow">
				<div class="cell theadDivCell width10px maxwidth10px">
					<input type="checkbox" name="check_all" value="addons[]" title="<?php _e('Select all','mgm'); ?>" />     		
				</div>
				<div class="cell theadDivCell width175px">
					<b><?php _e('Name','mgm')?></b>
				</div>		
				<div class="cell theadDivCell width175px">
					<b><?php _e('Expire Dt.','mgm')?></b>
				</div>		
				<div class="cell theadDivCell width175px">
					<b><?php _e('Create Dt.','mgm')?></b>
				</div>		
				<div class="cell theadDivCell width100px">
					<b><?php _e('Action','mgm')?></b>
				</div>		
			</div>	
			<div class="tbodyDiv" id="addon_rows">	
				<?php 
					// date format
					$date_format_short = mgm_get_date_format('date_format_short');
					// check
					if(count($data['addons']) > 0): foreach ($data['addons'] as $i=>$addon):
						//exp date 
						$expiry_date = __('Never','mgm');
						// check
						if(strtotime($addon->expire_dt) > 0):		
							 $expiry_date = date($date_format_short, strtotime($addon->expire_dt));				
						endif;
						// created date
						$create_date = date($date_format_short, strtotime($addon->create_dt));			
				?>
				<div class="row brBottom  <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="addon_row_<?php echo $addon->id ?>">
					<div class="cell width10px maxwidth10px paddingleftimp10px">
						<input type="checkbox" name="addons[]" id="addon_<?php echo $addon->id ?>" value="<?php echo $addon->id ?>" />		
					</div>
					<div class="cell width175px maxwidth175px paddingleftimp10px">
						<?php echo mgm_ellipsize($addon->name,20) ?>
					</div>		
					<div class="cell width175px maxwidth175px paddingleftimp10px">
						<?php echo $expiry_date; ?>
					</div>
					<div class="cell width175px maxwidth175px paddingleftimp10px">
						<?php echo $create_date; ?>
					</div>
					<div class="cell width100px maxwidth100px paddingleftimp10px">					
						<?php /*?><a href="javascript://" rel="#addon_settings_overlay_<?php echo $addon->id ?>" title="<?php _e('Settings','mgm')?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/cog.png" /></a>		
						<?php include('settings_overlay.php');?><?php */?>		
						<a href="javascript:mgm_addon_edit('<?php echo $addon->id ?>')" title="<?php _e('Edit', 'mgm') ?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/edit.png" /></a>	
						<a href="javascript:mgm_addon_delete('<?php echo $addon->id ?>')" title="<?php _e('Delete', 'mgm') ?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-cross.png" /></a>
						<a href="javascript:mgm_addon_options('<?php echo $addon->id ?>')" title="<?php _e('Options', 'mgm') ?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/wrench.png" /></a>					 			
					</div>
				</div>	
				<?php endforeach; else:?>
				<div class="row">
					<div class="cell mgm-center-txt">
						<?php _e('You haven\'t created any addons yet.','mgm')?>
					</div>
				</div>	
				<?php endif;?>		
			</div>
		</div>
		<div class="clearfix"></div>
		<?php if(count($data['addons']) > 0):?>
		<div class="mgm_bulk_actions_div">
			<select name="bulk_actions" id="bulk_actions" class="width150px">
				<option value=""><?php _e('Bulk Actions','mgm')?></option>
				<?php echo mgm_make_combo_options(array('delete'=>__('Delete','mgm')), $data['bulk_actions'], MGM_KEY_VALUE);?>
			</select>
			<input class="button" type="button" name="apply_btn" value="<?php _e('Apply', 'mgm')?>" onclick="mgm_addon_bulk_actions()"/>
		</div>	
		<div class="mgm_page_links_div">
			<?php if(isset($data['page_links'])):?><div class="pager-wrap"><?php echo $data['page_links']?></div><?php endif; ?>
		</div>	
		<div class="clearfix"></div>
	</form>
	<script language="javascript">
		jQuery(document).ready(function(){
			// bind check
			jQuery('#addons').mgm_bind_check_all();
			// set pager anchor 2 post
			mgm_set_pager_anchor2post('#addon_list', '#search-table');
			// set pager dropdown 2 post
			mgm_set_pager_select2post('#addon_list', '#search-table', '<?php echo $data['page_url']?>');				
			
			
			// tip
			// jQuery("#addon_rows a[rel]").overlay({effect: 'apple'});
		});
	</script>	
	<?php endif;?>