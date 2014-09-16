	<div class="table" id="search-table">
		<div class="row">
			<div class="cell width440px">
				<?php _e('Page No','mgm')?>&nbsp;&nbsp;&nbsp;&nbsp;: 
				<select name="page_no_s" id="page_no_s" class="width50px">					
					<?php echo mgm_make_combo_options(($data['page_count']>1? range(1, $data['page_count']): array(1)), $data['page_no'], MGM_VALUE_ONLY);?>
				</select>
			</div>
			<div class="cell">
				<?php _e('Records Per Page','mgm');?>: 
				<select name="page_limit" id="page_limit" class="width50px">
					<?php echo mgm_make_combo_options(array(20,40,50,100), $data['page_limit'], MGM_VALUE_ONLY);?>
				</select>
			</div>		
		</div>
		<div class="row">
			<div class="cell width440px">
				<?php _e('Search By','mgm')?>&nbsp;: 
				<select name='search_field_name' class='width135px'>					
					<?php echo mgm_make_combo_options($data['search_fields'], $data['search_field_name'], MGM_KEY_VALUE);?>
				</select>				
				<span id="fld_wrapper">
					<input type="text" name="search_field_value" value="<?php echo $data['search_field_value']?>" size="20">
				</span>
			</div>
			<div class="cell  ">
				<?php _e('Sort By','mgm')?>: 
				<select name='sort_field' class='width135px'>					
					<?php echo mgm_make_combo_options($data['sort_fields'], $data['sort_field'], MGM_KEY_VALUE);?>
				</select>		
				<select name='sort_type'>					
					<?php echo mgm_make_combo_options(array('asc'=>'ASC', 'desc'=>'DESC'), $data['sort_type'], MGM_VALUE_ONLY);?>
				</select>								
				<input class="button" type="button" name="list_filter_btn" value="<?php _e('Show','mgm') ?>" onclick="mgm_coupon_list_filter(true)" />
				<a href="javascript:mgm_coupon_list()" title="<?php _e('Refresh List','mgm')?>"><img src="<?php echo MGM_ASSETS_URL ?>images/icons/arrow_refresh.png" /></a>
			</div>
		</div>
	</div>
	<span id="last_search_message" class="displaynone"><?php echo $data['message']?></span>
	<script language="javascript">
		jQuery(document).ready(function(){
			// chnage search field
			var onchange_count = 0;
			var search_val     = '<?php echo (isset($data['search_field_value'])) ? $data['search_field_value'] : ''?>';	
			// bind	search field change	
			jQuery("#coupon_list select[name='search_field_name']").bind('change',function() {	
				// remove old		
				jQuery("#coupon_list :input[name='search_field_value']").remove();		
				// reset val
				if(onchange_count > 0) search_val = '';	
				// on val
				switch(jQuery(this).val()){				
					case 'create_dt':
					case 'expire_dt':									
						jQuery('#coupon_list #fld_wrapper').html('<input type="text" name="search_field_value" value="'+search_val+'" size="10">');
						if(!jQuery("#coupon_list :input[name='search_field_value']").hasClass('hasDatepicker')){					
							mgm_date_picker("#coupon_list :input[name='search_field_value']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});
						}
					break;
					default:					
						jQuery('#coupon_list #fld_wrapper').html('<input type="text" name="search_field_value" value="'+search_val+'" size="20">');
					break;
				}
				onchange_count++;
			}).change();	
		});
	</script>