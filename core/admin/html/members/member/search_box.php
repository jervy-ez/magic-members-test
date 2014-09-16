	<div class="table" id="search-table">
		<div class="row">
			<div class="cell width55">
				<?php _e('Page No','mgm')?>: 
				<select name="page_no_s" id="page_no_s" class="width50px">					
					<?php echo mgm_make_combo_options(($data['page_count']>1? range(1, $data['page_count']): array(1)), $data['page_no'], MGM_VALUE_ONLY);?>
				</select>
			</div>
			<div class="cell width45">
				<?php _e('Records Per Page','mgm');?>: 
				<select name="page_limit" id="page_limit" class="width50px">
					<?php echo mgm_make_combo_options(array(20,40,50,100), $data['page_limit'], MGM_VALUE_ONLY);?>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="cell width55">
				<?php _e('Search By','mgm')?>: 
				<select name='search_field_name' class='width135px'>					
					<?php echo mgm_make_combo_options($data['search_fields'], $data['search_field_name'], MGM_KEY_VALUE);?>
				</select>				
				<span id="fld_wrapper">
					<input type="text" name="search_field_value" value="<?php echo $data['search_field_value']?>">
				</span>					
				<span id="fld_wrapper_two">
					<input type="text" name="search_field_value_two" value="<?php echo $data['search_field_value_two']?>">
				</span>
			</div>
			<div class="cell width45">
				<?php _e('Sort By','mgm')?>: 
				<select name='sort_field_name' class='width135px'>					
					<?php echo mgm_make_combo_options($data['sort_fields'], $data['sort_field'], MGM_KEY_VALUE);?>
				</select>		
				<select name='sort_type'>					
					<?php echo mgm_make_combo_options(array('desc'=>'DESC','asc'=>'ASC'), $data['sort_type'], MGM_VALUE_ONLY);?>
				</select>								
				<input type="button" name="show_members_btn" class="button" value="<?php _e('Show','mgm') ?>" onclick="mgm_show_member_list(true)" />
				<a href="javascript:mgm_member_list()" title="<?php _e('Refresh List','mgm')?>"><img src="<?php echo MGM_ASSETS_URL ?>images/icons/arrow_refresh.png" /></a>
			</div>
		</div>
	</div>
	
	<script language="javascript">
		jQuery(document).ready(function(){
			// chnage search field
			var onchange_count = 0;
			var search_val     = '<?php echo (isset($data['search_field_value'])) ? $data['search_field_value'] : ''?>';	
			var search_val2    = '<?php echo (isset($data['search_field_value_two'])) ? $data['search_field_value_two'] : ''?>';
			// bind	search field change		
			jQuery("select[name='search_field_name']").bind('change',function() {	
				// remove old		
				jQuery(":input[name='search_field_value']").remove();		
				// reset val
				if(onchange_count > 0) search_val = search_val2 = '';	
				// on val
				switch(jQuery(this).val()){
					case 'membership_type':
						jQuery('#fld_wrapper_two').html('');
						var s=document.createElement('select');
							s.name='search_field_value';						
						<?php foreach(mgm_get_class('membership_types')->membership_types as $membership_type_value=>$membership_type_text):?>
							s.options[s.options.length]=new Option('<?php echo $membership_type_text?>','<?php echo $membership_type_value?>',false,<?php echo ($data['search_field_value']==$membership_type_value?'true':'false')?>);
						<?php endforeach?>
						jQuery('#fld_wrapper').html(s);
					break;
					case 'status':
						jQuery('#fld_wrapper_two').html('');
						var s=document.createElement('select');
							s.name='search_field_value';
						<?php 
						$statuses = mgm_get_subscription_statuses(true);
						foreach($statuses as $status):?>
							s.options[s.options.length] = new Option('<?php echo $status?>','<?php echo $status?>',false,<?php echo ($data['search_field_value']==$status?'true':'false')?>);
						<?php endforeach?>
						jQuery('#fld_wrapper').html(s);
					break;
					case 'reg_date':
					case 'last_payment':
					case 'expire_date':				
					case 'join_date':				
						// issue#: 219
						jQuery('#fld_wrapper').html('<input type="text" name="search_field_value" value="'+search_val+'" size="8">');
						if(!jQuery("#mgmmembersfrm :input[name='search_field_value']").hasClass('hasDatepicker')){					
							mgm_date_picker("#mgmmembersfrm :input[name='search_field_value']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});
						}
	
						// issue#: 219
						jQuery('#fld_wrapper_two').html('<input type="text" name="search_field_value_two" value="'+search_val2+'" size="8">');
						if(!jQuery("#mgmmembersfrm :input[name='search_field_value_two']").hasClass('hasDatepicker')){					
							mgm_date_picker("#mgmmembersfrm :input[name='search_field_value_two']",'<?php echo MGM_ASSETS_URL?>', {yearRange:"<?php echo mgm_get_calendar_year_range(); ?>", dateFormat: "<?php echo mgm_get_datepicker_format()?>"});
						}	
					break;
					case 'payment_module':
					<?php
 					// init
					$payment_modules = array();
					// module tracking fields
					if($data['payment_modules']): foreach($data['payment_modules'] as $payment_module) : 
						// get modu;e
						$module = mgm_get_module($payment_module); 
						// check virtual
						//if( !$module->is_virtual_payment() ):
						// set data
							$payment_modules[$module->code] = $module->name;
						//endif;
					endforeach; endif; ?>
						jQuery('#fld_wrapper_two').html('');
						var s=document.createElement('select');
							s.name='search_field_value';
						<?php						
						foreach($payment_modules as $payment_module_code=>$payment_module_name):?>
							s.options[s.options.length] = new Option('<?php echo $payment_module_name?>','<?php echo $payment_module_code?>',false,<?php echo ($data['search_field_value']==$payment_module_name?'true':'false')?>);
						<?php endforeach?>
						jQuery('#fld_wrapper').html(s);
					break;
					default:					
					jQuery('#fld_wrapper_two').html('');
						// issue#: 219
						jQuery('#fld_wrapper').html('<input type="text" name="search_field_value" value="'+search_val+'" size="20">');
					break;
				}
				onchange_count++;
			}).change();	
		});
	</script>