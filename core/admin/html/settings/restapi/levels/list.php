<form name="frmrestapilevels" id="frmrestapilevels" method="post" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi_levels">
<div class="table widefatDiv">
	<div class="row headrow">
		<div class="cell theadDivCell width100px">
    		<b><?php _e('Level','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width100px">
    		<b><?php _e('Name','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width100px">
    		<b><?php _e('Permissions','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width100px">
    		<b><?php _e('Limits','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width100px">
    		<b><?php _e('Action','mgm') ?></b>
		</div>
	</div>
	<?php if(count($data['levels'])>0): foreach($data['levels'] as $level):?>
	<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="row-<?php echo $level->id?>">
		<div class="cell width100px">
    		<?php echo $level->level ?>		   
		</div>
		<div class="cell width100px">
    		<?php echo $level->name ?>		   
		</div>
		<div class="cell width100px">
    		<?php if($permissions = implode(',',json_decode($level->permissions,true))): echo $permissions; else: _e('full','mgm'); endif; ?>		   
		</div>
		<div class="cell width100px">
    		<?php echo is_null($level->limits) ? __('Unlimited','mgm') : $level->limits?>		   
		</div>
		<div class="cell width100px">
			<input class="button" name="edit_level_btn" type="button" value="<?php _e('Edit', 'mgm') ?>" onclick="mgm_api_level_edit('<?php echo $level->id ?>');"/>
			<input class="button" name="delete_level_btn" type="button" value="<?php _e('Delete', 'mgm') ?>" onclick="mgm_api_level_delete('<?php echo $level->id ?>');"/>
		</div>
	</div>
	<?php endforeach; else:?>
	<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
		<div class="cell textaligncenter">
			<?php _e('No levels created','mgm')?>	
		</div>
	</div>
	<?php endif;?>

</div>	
</form>