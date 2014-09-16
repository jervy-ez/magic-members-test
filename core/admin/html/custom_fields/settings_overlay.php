	<div id="custom_field_settings_overlay_<?php echo $id ?>" class="apple_overlay">	
		<div class="table widefatDiv">
			<?php if($field['type'] != 'html'): ?>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('Required?','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php echo ($field['attributes']['required']==true) ? __('Yes','mgm') : __('No','mgm'); ?>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('ReadOnly?','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php echo ($field['attributes']['readonly']==true) ? __('Yes','mgm') : __('No','mgm');?>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('Hide Label?','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php echo ($field['attributes']['hide_label']==true) ? __('Yes','mgm') : __('No','mgm');?>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('Send to Autoresponder?','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php echo ($field['attributes']['to_autoresponder']==true) ? __('Yes','mgm') : __('No','mgm');?>
				</div>
			</div>
			<?php endif; ?>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('On Register Page?','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php echo ($field['display']['on_register']==true) ? __('Yes','mgm') : __('No','mgm');?>
				</div>
			</div>
	
			<?php if($field['type'] != 'html'): ?>	
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('On Profile Page?','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php echo ($field['display']['on_profile']==true) ? __('Yes','mgm') : __('No','mgm');?>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('On Payment Page?','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php echo ($field['display']['on_payment']==true) ? __('Yes','mgm') : __('No','mgm');?>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('On Public Profile Page?','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php echo ($field['display']['on_public_profile']==true) ? __('Yes','mgm') : __('No','mgm');?>
				</div>
			</div>
			<?php endif; ?>
			
			<?php if($field['name'] == 'coupon'): ?>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('On Upgrade Page?','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php echo ($field['display']['on_upgrade']==true) ? __('Yes','mgm') : __('No','mgm');?>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('On Extend Page?','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php echo ($field['display']['on_extend']==true) ? __('Yes','mgm') : __('No','mgm');?>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('On Post Purchase Page?','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php echo ($field['display']['on_postpurchase']==true) ? __('Yes','mgm') : __('No','mgm');?>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('On Multiple Membership Level Purchase Page?','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php echo ($field['display']['on_multiple_membership_level_purchase']==true) ? __('Yes','mgm') : __('No','mgm');?>
				</div>
			</div>
			<?php endif; ?>
			<?php if($field['name'] == 'autoresponder'): ?>	
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('On Upgrade Page?','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php echo ($field['display']['on_upgrade']==true) ? __('Yes','mgm') : __('No','mgm');?>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('On Multiple Membership Level Purchase Page?','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php echo ($field['display']['on_multiple_membership_level_purchase']==true) ? __('Yes','mgm') : __('No','mgm');?>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</div>