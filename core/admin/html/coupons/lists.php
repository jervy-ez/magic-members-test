	<!--coupon list-->
	<form id="mgmcouponfrm" name="mgmcouponfrm" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/coupons" method="post">	
		<div class="mgm-search-box">	
			<?php include('search_box.php')?>			
		</div>
		<div class="clearfix"></div>
		<div class="table widefatDiv width830px">
			<div class="row headrow">
				<div class="cell theadDivCell width10px maxwidth10px">
					<input type="checkbox" name="check_all" value="coupons[]" title="<?php _e('Select all','mgm'); ?>" />     		
				</div>
				<div class="cell theadDivCell width75px maxwidth75px">
					<b><?php _e('Code','mgm')?></b>
				</div>
				<div class="cell theadDivCell width125px maxwidth125px">
					<b><?php _e('Value','mgm')?></b>
				</div>
				<div class="cell theadDivCell width125px maxwidth125px">
					<b><?php _e('Desc.','mgm')?></b>
				</div>
				<div class="cell theadDivCell width75px maxwidth75px">
					<b><?php _e('Use Limit','mgm')?></b>
				</div>
				<div class="cell theadDivCell width75px maxwidth75px">
					<b><?php _e('Expire Dt.','mgm')?></b>
				</div>		
				<div class="cell theadDivCell width75px maxwidth75px">
					<b><?php _e('Create Dt.','mgm')?></b>
				</div>		
				<div class="cell theadDivCell width100px maxwidth100px">
					<b><?php _e('Action','mgm')?></b>
				</div>		
			</div>	
			<div class="tbodyDiv" id="coupon_rows">	
				<?php 
					// date format
					$date_format_short = mgm_get_date_format('date_format_short');
					// check
					if(count($data['coupons']) > 0): foreach ($data['coupons'] as $i=>$coupon):
						//exp date 
						$expiry_date = __('Never','mgm');
						// check
						if(strtotime($coupon->expire_dt) > 0 ):
							 $expiry_date = date($date_format_short, strtotime($coupon->expire_dt));				
						endif;
						//created date
						$create_date = date($date_format_short, strtotime($coupon->create_dt));?>
				<div class="row brBottom <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="coupon_row_<?php echo $coupon->id ?>">
					<div class="cell width10px maxwidth10px paddingleftimp10px">
						<input type="checkbox" name="coupons[]" id="coupon_<?php echo $coupon->id ?>" value="<?php echo $coupon->id ?>" />		
					</div>
					<div class="cell width75px maxwidth75px paddingleftimp10px">
						<?php echo mgm_ellipsize($coupon->name,20) ?>
					</div>
					<div class="cell width125px maxwidth125px paddingleftimp10px">
						<?php echo is_numeric($coupon->value) ? mgm_format_currency($coupon->value) : $coupon->value?>
					</div>
					<div class="cell width125px maxwidth125px paddingleftimp10px">
						<?php echo mgm_ellipsize($coupon->description) ?>
					</div>
					<div class="cell width75px maxwidth75px paddingleftimp10px">
						<?php echo is_null($coupon->use_limit) ? __('Unlimited','mgm') : sprintf(__('%d of %d used', 'mgm'), $coupon->used_count,$coupon->use_limit) ?>
					</div>
					<div class="cell width75px maxwidth75px paddingleftimp10px">
						<?php echo $expiry_date; ?>
					</div>
					<div class="cell width75px maxwidth75px paddingleftimp10px">
						<?php echo $create_date; ?>
					</div>
					<div class="cell width100px maxwidth100px paddingleftimp10px">
						<a href="javascript:mgm_coupon_edit('<?php echo $coupon->id ?>')" title="<?php _e('Edit', 'mgm') ?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/edit.png" /></a>	
						<a href="javascript:mgm_coupon_delete('<?php echo $coupon->id ?>')" title="<?php _e('Delete', 'mgm') ?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-cross.png" /></a>
						<a href="javascript:mgm_coupon_users('<?php echo $coupon->id ?>')" title="<?php _e('Users', 'mgm') ?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/group.png" /></a>					
					</div>
				</div>	
				<?php endforeach; else:?>
				<div class="row">
					<div class="cell mgm-center-txt">
						<?php _e('You haven\'t created any coupons yet.','mgm')?>
					</div>
				</div>	
				<?php endif;?>		
			</div>
		</div>
		<div class="clearfix"></div>
		<?php if(count($data['coupons']) > 0):?>
		<div class="mgm_bulk_actions_div">
			<select name="bulk_actions" id="bulk_actions" class="width150px">
				<option value=""><?php _e('Bulk Actions','mgm')?></option>
				<?php echo mgm_make_combo_options(array('delete'=>__('Delete','mgm')), $data['bulk_actions'], MGM_KEY_VALUE);?>
			</select>
			<input class="button" type="button" name="apply_btn" value="<?php _e('Apply', 'mgm')?>" onclick="mgm_coupon_bulk_actions()"/>
		</div>	
		<div class="mgm_page_links_div">
			<?php if(isset($data['page_links'])):?><div class="pager-wrap"><?php echo $data['page_links']?></div><?php endif; ?>
		</div>	
		<div class="clearfix"></div>
	</form>	
	<script language="javascript">
		jQuery(document).ready(function(){
			// bind
			jQuery('#coupons').mgm_bind_check_all();
			// set pager anchor 2 post
			mgm_set_pager_anchor2post('#coupon_list', '#search-table');
			// set pager dropdown 2 post
			mgm_set_pager_select2post('#coupon_list', '#search-table', '<?php echo $data['page_url']?>');				
					
		});
	</script>	
	<?php endif;?>