	<?php if(count($data['addon_purchases']) > 0):?>
	<div class="mgm-search-box">	
		<?php include('search_box.php')?>
	</div>
	<?php endif;?>
	
	<!--purchases-->
	<div class="table widefatDiv">
		<div class="row headrow">
			<div class="cell theadDivCell width100px">
				<b><?php _e('Username','mgm') ?></b>
			</div>			
			<div class="cell theadDivCell width100px">
				<b><?php _e('Addon','mgm') ?></b>
			</div>			
			<div class="cell theadDivCell width100px">
				<b><?php _e('Purchase Date','mgm') ?></b>
			</div>
			<div class="cell theadDivCell width100px">
				<b><?php _e('Action','mgm') ?></b>
			</div>
		</div>
		<?php 	
		// format
		$date_format = mgm_get_date_format('date_format');
		// loop
		if($data['addon_purchases']): foreach ($data['addon_purchases'] as $purchase) :	?>
		<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?> brBottom" id="addon_purchase_row_<?php echo $purchase->id ?>">					
			<div class="cell width100px">
				<?php echo $purchase->user_login?>		   
			</div>			
			<div class="cell width100px">
				<?php echo $purchase->addon_option?>
			</div>
			<div class="cell width100px">
				<?php echo date($date_format, strtotime($purchase->purchase_dt)) ?>
			</div>
			<div class="cell width100px">
				<input class="button" name="delete" type="button" value="<?php _e('Delete', 'mgm') ?>" onclick="mgm_addon_purchase_delete('<?php echo $purchase->id ?>');"/>
			</div>			
		</div>	
		<?php endforeach; else: ?> 
		<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
			<div class="cell textaligncenter">
				<?php _e('No addons have been purchased yet','mgm')?>
			</div>
		</div>		
		<?php endif;?>	
	</div>
	<div class="clearfix"></div>
	<?php if(count($data['addon_purchases']) > 0):?>
	<div id="addon_purchases_export_options" class="cell" style="float:left; margin-top:10px;">
		<input type="button" name="export_addon_purchases_btn" class="button" value="<?php _e('Export','mgm') ?>" onclick="mgm_addon_purchase_export(true)" />
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
	<iframe id="ifrm_addon_purchase_export" src="" allowtransparency="true" width="0" height="0" frameborder="0"></iframe>
	<div class="clearfix"></div>	
	<span id="last_search_message" class="displaynone"><?php echo $data['message']?></span>
	<script language="javascript">
	<!--	
	jQuery(document).ready(function(){
		// set pager anchor 2 post
		mgm_set_pager_anchor2post('#addon_purchase_list', '#search-table');
		// set pager dropdown 2 post
		mgm_set_pager_select2post('#addon_purchase_list', '#search-table', '<?php echo $data['page_url']?>');				
		// delete
		mgm_addon_purchase_delete=function(id) {
			if (confirm("<?php _e('Are you sure you want to delete this purchase record?', 'mgm') ?>")) {
				jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/addons&method=purchase_delete', 
				 type: 'POST', 
				 dataType: 'json', 
				 data :{id: id}, 
				 cache: false,
				 beforeSend: function(){	
					// show message
					mgm_show_message('#addon_purchase_list', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);
				 },
				 success:function(data){
					// message																				
					mgm_show_message('#addon_purchase_list', data);																		
					// success	
					if(data.status=='success'){
						// reload
						mgm_addon_purchase_list();										
					}
				 }});
			}
		}					
	});		
	//-->
	</script>
	<?php endif;?>