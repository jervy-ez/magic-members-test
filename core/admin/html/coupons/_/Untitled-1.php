<div class="row brBottom">
			<div class="cell width120px">
				<span class="required-field"><?php _e('Code','mgm')?></span>: 
			</div>
			<div class="cell textalignleft">
				<input type="text" name="name" size="100" maxlength="80" value="<?php echo $data['coupon']->name?>"/>
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
				<textarea name="description" cols="80" rows="5"><?php echo $data['coupon']->description?></textarea>
			</div>
		</div>
		<div class="row brBottom">
			<div class="cell width120px">
				<?php _e('Usage Limit','mgm')?>: 
			</div>
			<div class="cell textalignleft">
				<input type="text" name="use_limit" size="5" maxlength="10" value="<?php echo $data['coupon']->use_limit?>" <?php echo is_null($data['coupon']->use_limit) ? 'disabled': ''?>/>&nbsp;
				<input type="checkbox" name="use_unlimited" <?php echo is_null($data['coupon']->use_limit) ? 'checked': ''?>/> <?php _e('Unlimited','mgm')?>?
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
				if(strtotime($data['coupon']->expire_dt) > 0 ) :
					 /*date(MGM_DATE_FORMAT_INPUT, strtotime($data['coupon']->expire_dt) )*/
					 $date = date('Y-m-d', strtotime($data['coupon']->expire_dt));
					 $expire_dt = mgm_get_datepicker_format('date', $date);
				endif;?>
				<input name="expire_dt" id="expire_dt" type="text" size="12" value="<?php echo $expire_dt; ?>" />
				
			</div>
		</div>
		<?php include('product_mapping.php');?>		