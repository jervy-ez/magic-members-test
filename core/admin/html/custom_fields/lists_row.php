<div class="row brBottom cursormove <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="<?php echo $active ? 'active_':'inactive_' ?>custom_field_row_<?php echo $id ?>">
	<div class="cell width10 cursorauto">
		<input type="checkbox" name="custom_fields[]" value="<?php echo $id ?>" <?php echo ($active===TRUE) ? 'checked' : '' ?>>
	</div>
	<div class="cell width35">
		<?php echo mgm_ellipsize(mgm_stripslashes_deep($field['label']), 50)?>
	</div>
	<div class="cell width35">
		<?php echo mgm_ellipsize(mgm_stripslashes_deep($field['name']), 50)?>
	</div>
	<div class="cell width10">
		<?php echo $field['type'] ?>
	</div>	
	<div class="cell width20">
		<!--<a href="javascript://" rel="#custom_field_settings_overlay_<?php echo $id ?>" title="<?php _e('Settings','mgm')?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/cog.png" /></a>		
		<?php include('settings_overlay.php');?>-->
		
		<?php if($field['system'] == true):?>
		<img src="<?php echo MGM_ASSETS_URL?>images/icons/exclamation.png" alt="<?php _e('System', 'mgm')?>" title="<?php _e('System', 'mgm')?>" />
		<?php endif;?>
		
		<?php		
		// other buttons
		switch($field['name']):					
			case 'subscription_options':?>
				<a href="javascript:mgm_custom_field_edit('<?php echo $id ?>')" title="<?php _e('Edit', 'mgm') ?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/edit.png" /></a>
				<a href="javascript:mgm_set_tab_url(1,1)" title="<?php _e('Setup Subscription Options','mgm')?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/wrench.png" /></a>				
			<?php	
			break;	
			case 'autoresponder':?>
				<a href="javascript:mgm_custom_field_edit('<?php echo $id ?>')" title="<?php _e('Edit', 'mgm') ?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/edit.png" /></a>
				<a href="javascript:mgm_set_tab_url(5,0)" title="<?php _e('Setup Autoresponders','mgm')?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/wrench.png" /></a>				
			<?php
			break;				
			default:?>
				<a href="javascript:mgm_custom_field_edit('<?php echo $id ?>')" title="<?php _e('Edit', 'mgm') ?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/edit.png" /></a>
		   		<?php if($field['system'] == false):?>					
					<a href="javascript:mgm_custom_field_delete('<?php echo $id ?>')" title="<?php _e('Delete', 'mgm') ?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-cross.png" /></a>					
		   		<?php endif;?>
			<?php	
		    break;
		endswitch;?>	
	</div>
</div>
