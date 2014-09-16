	<div class="table widefatDiv" id="option_prices">	
		<div class="row headrow">
			<div class="cell theadDivCell width145px">
				<b><?php _e('Option','mgm')?></b>
			</div>
			<div class="cell theadDivCell width125px">
				<b><?php _e('Price','mgm')?></b>
			</div>
			<div class="cell theadDivCell width125px">
			</div>
		</div>
		<?php foreach($data['addon_options'] as $i=>$addon_option):?>
		<div class="row option_price_row">
			<div class="cell textalignleft width125px">
				<input type="text" name="addon_options[<?php echo $i?>][option]" id="addon_options_<?php echo $i?>_option" size="50" maxlength="150" value="<?php echo $addon_option['option']?>"/>
			</div>			
			<div class="cell textalignleft width125px">
				<input type="text" name="addon_options[<?php echo $i?>][price]" id="addon_options_<?php echo $i?>_price" size="15" maxlength="50" value="<?php echo $addon_option['price']?>"/>
			</div>
			<div class="cell textalignleft">
				<a href="javascript://" class="choice_add" title="<?php _e('Add Option','mgm')?>"><img src="<?php echo MGM_ASSETS_URL ?>images/icons/16-em-plus.png" /></a>
				<a href="javascript://" class="choice_delete" title="<?php _e('Delete Option','mgm')?>"><img src="<?php echo MGM_ASSETS_URL ?>images/icons/16-em-cross.png" /></a>							
			</div>
		</div>
		<?php endforeach;?>
	</div>	
	<script language="javascript">
		jQuery(document).ready(function(){
			// index of rows
			var _row_index = <?php echo count($data['addon_options'])?>;
			// bind select choices add/delete
			mgm_addon_option_row_manage = function(selector){				
				// loop
				jQuery(selector).find("#option_prices a[class^='choice_']").each(function(){				
					if(jQuery(this).attr('class') == 'choice_add'){						
						jQuery(this).click(function(){	
							mgm_addon_option_row_add(this, selector);						
						});
					}else if(jQuery(this).attr('class') == 'choice_delete'){	
						jQuery(this).click(function(){	
							mgm_addon_option_row_delete(this, selector);							
						});						
					}
				});
				
				// disable delete for first row
				jQuery(selector).find("#option_prices .option_price_row:first a[class='choice_delete']").addClass('displaynone');				
			}
			// add
			mgm_addon_option_row_add=function(el, selector){
				// current row
				_current_row = jQuery(el).closest('div.option_price_row');
				// row
				_new_row = _current_row.clone(true);			
				// size
				_size = jQuery(selector).find('#option_prices').children('.option_price_row').size();	
				// check
				if(_size > 0){
					// new row
					jQuery(_new_row).find('a.choice_delete').removeClass('displaynone');
					// current row
					if(_current_row.find('a.choice_delete').hasClass('displaynone')){
						_current_row.find('a.choice_delete').removeClass('displaynone');
					}
				}
				// clean inputs				
				jQuery(_new_row).find(":input[type='text']").each(function(){
					// clear old val
					jQuery(this).val('');
					// reset id
					jQuery(this).attr('id', jQuery(this).attr('id').replace(/_(\d+)_/, ('_'+_row_index+'_')));
					// reset name
					jQuery(this).attr('name', jQuery(this).attr('name').replace(/\[(\d+)\]/, ('['+_row_index+']')));
				});	
				// append		
				jQuery(el).closest('#option_prices').append(_new_row);
				// increement row counter
				_row_index++;
			}			
			// delete
			mgm_addon_option_row_delete=function(el, selector){										
				// remove
				jQuery(el).closest('.option_price_row').remove();
				// size
				_size = jQuery(selector).find('#option_prices').children('.option_price_row').size();
				// check
				if(_size == 1){
					jQuery(selector).find('#option_prices').children('.option_price_row:first').find('a.choice_delete').addClass('displaynone');
				}
			}				
		});	
	</script>