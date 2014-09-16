<?php if(count($data['direct_urls_access'])>0): foreach($data['direct_urls_access'] as $direct_urls_access):?>
<div class="brBottom row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="direct_urls_access_row_<?php echo $direct_urls_access->id ?>">
	<div class="cell width45"><?php echo $direct_urls_access->url?></div>
	<div class="cell width35"><?php echo implode(', ',json_decode($direct_urls_access->membership_types,true))?></div>
	<div class="cell width20 paddingtop10px"><input type="button" class="button" value="<?php _e('Delete','mgm')?>" onclick="mgm_delete_protected_url('<?php echo $direct_urls_access->id ?>','direct_urls_access')" /></div>
</div>
<?php endforeach; else:?>
<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" >
	<div class="cell textaligncenter"><?php _e('No access settings','mgm')?></div>
</div>
<?php endif;?>