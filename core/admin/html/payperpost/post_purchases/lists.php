	<?php if(count($data['post_purchases']) > 0):?>
	<div class="mgm-search-box">	
		<?php include('search_box.php')?>
	</div>
	<?php endif;?>
	
	<!--purchases_gifts-->
	<div class="table widefatDiv">
		<div class="row headrow">
			<div class="cell theadDivCell width100px">
				<b><?php _e('Username/Guest','mgm') ?></b>
			</div>
			<div class="cell theadDivCell width100px">
				<b><?php _e('Post','mgm') ?></b>
			</div>
			<div class="cell theadDivCell width100px">
				<b><?php _e('Type','mgm') ?></b>
			</div>
			<div class="cell theadDivCell width100px">
				<b><?php _e('Expire Date','mgm') ?></b>
			</div>
			<div class="cell theadDivCell width100px">
				<b><?php _e('Purchase/Gift Date','mgm') ?></b>
			</div>
			<div class="cell theadDivCell width100px">
				<b><?php _e('Action','mgm') ?></b>
			</div>
		</div>
		<?php 	
		$date_format = mgm_get_date_format('date_format');
		$date_format_time = mgm_get_date_format('date_format_time');	
		// loop
		if($data['post_purchases']): foreach ($data['post_purchases'] as $purchase) :		
			// check is_expiry
			if($purchase->is_expire == 'N'):
				$expiry = __('Indefinite', 'mgm');	
			else:
				$expiry = mgm_get_post($purchase->post_id)->get_access_duration();
				$expiry = (!$expiry) ? __('Indefinite', 'mgm') : (date('d/m/Y',(86400*$expiry) + strtotime($purchase->purchase_dt)) . " (" . $expiry . __(' D','mgm').")");	
			endif;	
			
			// member name
			if(preg_match('/^guest-/',$purchase->user_login)):
				// guest token
				$guest_token = str_replace('guest-','',$purchase->user_login);
				// member
				$member = __('Guest','mgm') . sprintf(' (%s)', $guest_token);
				// post url
				$post_url = add_query_arg(array('guest_token' => $guest_token),get_permalink($purchase->post_id));
			else:	
				// member
				$member =  $purchase->user_login;
				// post url
				$post_url = get_permalink($purchase->post_id);
			endif;	
		?>
		<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?> brBottom" id="post_purchase_row_<?php echo $purchase->id ?>">					
			<div class="cell width100px">
				<?php echo $member ?>		   
			</div>
			<div class="cell width100px">
				<?php echo sprintf('<a href="%s" target="_blank">%s</a>', $post_url, $purchase->post_title); ?>
			</div>
			<div class="cell width100px">
				<?php echo ($purchase->is_gift == 'Y') ? __('Gift','mgm') : __('Purchase','mgm') ?>
			</div>
			<div class="cell width100px">
				<?php echo $expiry ?>
			</div>
			<div class="cell width100px">
				<?php echo date('d/m/Y', strtotime($purchase->purchase_dt)) ?>
			</div>
			<div class="cell width100px">
				<input class="button" name="delete" type="button" value="<?php _e('Delete', 'mgm') ?>" onclick="mgm_post_purchase_delete('<?php echo $purchase->id ?>');"/>
			</div>			
		</div>	
		<?php endforeach; else: ?> 
		<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
			<div class="cell textaligncenter">
				<?php _e('No posts have been sold/gifted yet','mgm')?>
			</div>
		</div>		
		<?php endif;?>	
	</div>
	<div class="clearfix"></div>
	<?php if(count($data['post_purchases']) > 0):?>
	<div id="post_purchases_export_options" class="cell" style="float:left; margin-top:10px;">
		<input type="button" name="export_post_purchases_btn" class="button" value="<?php _e('Export','mgm') ?>" onclick="mgm_post_purchase_export(true)" />
		<select id="select_export_format">
			<option value="xls"><?php _e('XLS','mgm') ?></option>
			<option value="csv"><?php _e('CSV','mgm') ?></option>						
		</select>
		<?php echo mgm_get_loading_icon();?>
	</div>
	<div class="mgm_page_links_div">
		<?php if($data['page_links']):?><div class="pager-wrap"><?php echo $data['page_links']?></div><?php endif; ?>
	</div>	
	<!--Purchanse export iframe -->
	<iframe id="ifrm_post_purchase_export" src="" allowtransparency="true" width="0" height="0" frameborder="0"></iframe>
	<div class="clearfix"></div>	
	<span id="last_search_message" class="displaynone"><?php echo $data['message']?></span>
	<script language="javascript">
	<!--	
	jQuery(document).ready(function(){
		// set pager anchor 2 post
		mgm_set_pager_anchor2post('#post_purchase_list', '#search-table');
		// set pager dropdown 2 post
		mgm_set_pager_select2post('#post_purchase_list', '#search-table', '<?php echo $data['page_url']?>');				
		// delete
		mgm_post_purchase_delete=function(id) {
			if (confirm("<?php _e('Are you sure you want to delete this purchase record?', 'mgm') ?>")) {
				jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payperpost&method=post_purchase_delete', 
				 type: 'POST', 
				 dataType: 'json', 
				 data :{id: id}, 
				 cache: false,
				 beforeSend: function(){	
					// show message
					mgm_show_message('#post_purchase_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);
				 },
				 success:function(data){
					// message																				
					mgm_show_message('#post_purchase_list', data);																		
					// success	
					if(data.status=='success'){
						// reload
						mgm_post_purchase_list();										
					}
				 }});
			}
		}					
	});		
	//-->
	</script>
	<?php endif;?>