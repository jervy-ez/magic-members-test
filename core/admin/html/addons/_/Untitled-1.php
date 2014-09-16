		<div class="row brBottom">
			<div class="cell width120px">
				<span class="required-field"><?php _e('Code','mgm')?></span>: 
			</div>
			<div class="cell textalignleft">
				<input type="text" name="name" size="100" maxlength="80" value="<?php echo $data['addon']->name?>"/>
			</div>
		</div>
		<div class="row brBottom">
			<div class="cell width120px">
				<span class="required-field"><?php _e('Value','mgm')?></span>: </td>
			</div>
			<div class="cell textalignleft">
				<?php include('value_options.php')?>						
				<!--<div class="tips width95"><?php //include('tips.php')?></div>-->
			</div>
		</div>
		<div class="row brBottom">
			<div class="cell width120px">
				<span class="required-field"><?php _e('Description','mgm')?></span>:
			</div>
			<div class="cell textalignleft">
				<textarea name="description" cols="80" rows="5"><?php echo $data['addon']->description?></textarea>
			</div>
		</div>
		<div class="row brBottom">
			<div class="cell width120px">
				<?php _e('Usage Limit','mgm')?>: 
			</div>
			<div class="cell textalignleft">
				<input type="text" name="use_limit" size="5" maxlength="10" value="<?php echo $data['addon']->use_limit?>" <?php echo is_null($data['addon']->use_limit) ? 'disabled': ''?>/>&nbsp;
				<input type="checkbox" name="use_unlimited" <?php echo is_null($data['addon']->use_limit) ? 'checked': ''?>/> <?php _e('Unlimited','mgm')?>?
				<div id="e_use_limit"></div>
				
			</div>
		</div>
		<div class="row brBottom">
			<div class="cell width120px">
				<?php _e('Expire Date','mgm') ?>		    		
			</div>
			<div class="cell textalignleft">
				<?php
				$expire_dt = '';
				if(strtotime($data['addon']->expire_dt) > 0 ) :
					 /*date(MGM_DATE_FORMAT_INPUT, strtotime($data['addon']->expire_dt) )*/
					 $date = date('Y-m-d', strtotime($data['addon']->expire_dt));
					 $expire_dt = mgm_get_datepicker_format('date', $date);
				endif;?>
				<input name="expire_dt" id="expire_dt" type="text" size="12" value="<?php echo $expire_dt; ?>" />
				
			</div>
		</div>
		<?php include('product_mapping.php');?>	
		
		<div class="row brBottom">
			<div class="cell">
				<div class="floatleft">			
					<input class="button" type="submit" name="save_addon" value="<?php _e('Save', 'mgm') ?>" />		
				</div>
				<div class="floatright">
					<input class="button" type="button" name="btn_cancel" value="<?php _e('Cancel', 'mgm') ?>" onclick="mgm_addon_add()"/>
				</div>	

			</div>
		</div>