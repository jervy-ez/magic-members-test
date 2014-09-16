<script type="text/javascript">var arr_ul_ids = [];</script> 
<?php $date_format = mgm_get_date_format('date_format');?>
<?php mgm_box_top('Payment History Search');?>
<div class="table form-table widefat" id="search-table">
	<div class="row">
	    <div class="cell textaligncenterimp">
			<?php _e('Search By','mgm')?>: 
			<select name='search_field_name' class='width135px'>					
				<?php echo mgm_make_combo_options($data['search_fields'], $data['search_field_name'], MGM_KEY_VALUE);?>
			</select>				
			<span id="fld_wrapper"><input type="text" name="search_field_value" value="<?php echo $data['search_field_value']?>" size="20"></span>
			<input type="button" name="show_members_btn" class="button" value="<?php _e('Generate Report','mgm') ?>" onclick="mgm_show_payment_history_list(true)" />	    
		</div>
	</div>
	<div class="row">
	    <div class="cell width50 textalignleft">
			<?php _e('Page No','mgm')?>: 
			<select name="page_no_s" id="page_no_s" class="width50px">					
				<?php echo mgm_make_combo_options(($data['page_count']>1? range(1, $data['page_count']): array(1)), $data['page_no'], MGM_VALUE_ONLY);?>
			</select>
		</div>
	    <div class="cell width50 textalignright">
			<?php _e('Records Per Page','mgm');?>: 
			<select name="page_limit" id="page_limit" class="width50px">
				<?php echo mgm_make_combo_options(array(20,40,50,100), $data['page_limit'], MGM_VALUE_ONLY);?>
			</select>
			<input type="hidden" name="export_format" value="xls" />
	    
		</div>
	</div>
</div>

<?php mgm_box_bottom();?>
<?php mgm_box_top('Payment History.');?>
<div class="table widefatDiv form-table">
	<div class="row headrow">
		<div class="cell theadDivCell width125px textalignleft maxwidth125px">
			<b><?php _e('User','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width125px textalignleft maxwidth125px">
			<b><?php _e('Type','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width125px textalignleft maxwidth125px">
			<b><?php _e('Module','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width125px textalignleft maxwidth125px">
			<b><?php _e('Amount','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width125px textalignleft maxwidth125px">
			<b><?php _e('Transaction Date','mgm') ?></b>
		</div>
	</div>
	
	<?php if(count($data['transactions'])>0): foreach($data['transactions'] as $tran_log):?>
					
	<div class="brBottom row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
		<div class="cell width125px textalignleft maxwidth125px maxwidth125px paddingleftimp10px">
	   		<?php 
	   			$json_decoded = json_decode($tran_log->data);
	   			//$user_obj = $data[$json_decoded->user_id];
	   			$user_obj = get_userdata($json_decoded->user_id);
	   			//echo $user_obj->user_login;
	   		?>
			<label for="user_<?php $user_obj->ID ?>">
				<strong><?php echo esc_html($user_obj->user_login) ?> </strong> [<?php echo $user_obj->ID ?>]
			</label>
			<div>
				<a href="mailto:<?php echo esc_html($user_obj->user_email) ?>">
					<?php echo esc_html($user_obj->user_email) ?>
				</a>
			</div>
			<div><?php echo esc_html($user_obj->first_name . ' ' . $user_obj->last_name) ?></div>

		</div>
		<div class="cell width125px textalignleft maxwidth125px paddingleftimp10px">
			<?php echo ucwords(str_replace('_',' ',$tran_log->payment_type)) ?>		   
		</div>
		<div class="cell width125px textalignleft maxwidth125px paddingleftimp10px">
	   		<?php echo ucwords($tran_log->module) ?>		   
		</div>
		<div class="cell width125px textalignleft maxwidth125px paddingleftimp10px">
	   		<?php echo ($json_decoded->trial_on) ? $json_decoded->trial_cost : $json_decoded->cost; ?>	
		</div>
		<div class="cell width125px textalignleft maxwidth125px paddingleftimp10px">
	   		<?php echo date(MGM_DATE_FORMAT_SHORT, strtotime($tran_log->transaction_dt))?>
		</div>
	</div>	
	<?php endforeach; else:?>
	<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
		<div class="cell textaligncenter">
		 <?php _e('No transaction found..!','mgm')?>					 					
		</div>
	</div>
	<?php endif;?>	
	
	<div class="row">
		<div class="cell textaligncenter">
		 	<input type="button" name="export_transactions_btn" class="button" value="<?php _e('Export','mgm') ?>" onclick="mgm_export_payment_history_list(true)" />
			<select id="select_export_format">
				<option value="xls"><?php _e('XLS','mgm') ?></option>
				<option value="csv"><?php _e('CSV','mgm') ?></option>						
			</select>
		</div>
	</div>
	
</div>

<div class="clearfix"></div>
<div style="float:right; height:30px; margin:10px 5px 0; padding:10px 5px 0">
	<?php if($data['page_links']):?><div class="pager-wrap"><?php echo $data['page_links']?></div><?php endif; ?>
</div>	
<div class="clearfix"></div>
<?php mgm_box_bottom();?>
<span id="last_search_message" style="display:none"><?php echo $data['message']?></span>

<script language="javascript">
	<!--
	jQuery(document).ready(function(){
		// set pager
		mgm_set_pager_anchor2post('#report_payment_history', '#search-table');
		// change page count
		jQuery('#report_payment_history #page_no_s').bind('change', function(){
			mgm_load_pager_page('#report_payment_history', '<?php echo $data['page_url']?>&page_no='+jQuery(this).val(), 'post', jQuery("#search-table :input").serializeArray());
		});
		// bind check all
		jQuery("#frmPaymentHistory :checkbox[name='check_all']").bind('click',function(){
			// checked
			var checked = (jQuery(this).attr('checked') == 'checked') ? true : false;
			// switch checked state
			jQuery("#frmPaymentHistory :checkbox[name='"+jQuery(this).val()+"']").attr('checked', checked);			
		});
		
		// chnage search field
		var onchange_count = 0;
		var search_val     = '<?php echo (isset($data['search_field_value'])) ? $data['search_field_value'] : ''?>';	
		// bind	search field change		
		jQuery("select[name='search_field_name']").bind('change',function() {	
			// remove old		
			jQuery(":input[name='search_field_value']").remove();		
			// reset val
			if(onchange_count > 0) search_val = '';	
			// on val
			switch(jQuery(this).val()){
				case 'membership_type':
					var s=document.createElement('select');
						s.name='search_field_value';						
					<?php foreach(mgm_get_class('membership_types')->membership_types as $membership_type_value=>$membership_type_text):?>
						s.options[s.options.length]=new Option('<?php echo $membership_type_text?>','<?php echo $membership_type_value?>',false,<?php echo ($data['search_field_value']==$membership_type_value?'true':'false')?>);
					<?php endforeach?>
					jQuery('#fld_wrapper').html(s);
				break;
				case 'payment_type':
					var s=document.createElement('select');
						s.name='search_field_value';
					<?php 
					$statuses = array('subscription_purchase'=>__('Subscription Purchase', 'mgm'),'post_purchase'=> __('Post Purchase', 'mgm'));
					foreach($statuses as $key=>$status):?>
						s.options[s.options.length] = new Option('<?php echo $status?>','<?php echo $key?>',false,<?php echo ($data['search_field_value']==$key?'true':'false')?>);
					<?php endforeach?>
					jQuery('#fld_wrapper').html(s);
				break;
				default:					
					// issue#: 219
					jQuery('#fld_wrapper').html('<input type="text" name="search_field_value" value="'+search_val+'" size="20">');
				break;
			}
			onchange_count++;
		}).change();	
		
		// render other membership tree:		
		if(arr_ul_ids.length > 0) for(var i = 0; i < arr_ul_ids.length; i++) jQuery('ul#'+arr_ul_ids[i]).collapsibleCheckboxTree();								
	});
	//-->	
</script>
