<form name="frmrestapikeys" id="frmrestapikeys" method="post" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=restapi_keys">

<div class="table widefatDiv">
	<div class="row headrow">
		<div class="cell theadDivCell width100px">
    		<b><?php _e('Api Key','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width100px">
    		<b><?php _e('Level','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width100px">
    		<b><?php _e('Create Date','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width100px">
    		<b><?php _e('Action','mgm') ?></b>
		</div>
	</div>
	<?php $date_format = mgm_get_date_format('date_format');?>
	<?php if(count($data['keys'])>0): foreach($data['keys'] as $key):?>		
	<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="row-<?php echo $key->id?>">					
		<div class="cell width100px">
    		<?php echo $key->api_key?>		   
		</div>
		<div class="cell width100px">
    		<?php echo $key->level?>		   
		</div>
		<div class="cell width100px">
    		<?php echo date($date_format, strtotime($key->create_dt))?>		   
		</div>
		<div class="cell width100px">
			<input class="button" name="edit_key_btn" type="button" value="<?php _e('Edit', 'mgm') ?>" onclick="mgm_api_key_edit('<?php echo $key->id ?>');"/>
			<input class="button" name="delete_key_btn" type="button" value="<?php _e('Delete', 'mgm') ?>" onclick="mgm_api_key_delete('<?php echo $key->id ?>');"/>
		</div>
	</div>
	<?php endforeach; else:?>
	<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
		<div class="cell textaligncenter">
    		<?php _e('No keys created','mgm')?>					 					
		</div>
	<?php endif;?>	
</div>
</form>