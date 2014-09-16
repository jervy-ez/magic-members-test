<?php if(count($data['posts_access'])>0): foreach($data['posts_access'] as $posts_access):?>

<div class="brBottom row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="posts_access_row_<?php echo $posts_access->id ?>">
	<div class="cell width45"><?php echo !is_null($posts_access->post_id) ? get_post($posts_access->post_id)->post_title: __('N/A','mgm')?> </div>
	<div class="cell width35"><?php echo implode(', ',json_decode($posts_access->membership_types,true))?> </div>
	<div class="cell paddingtop10px width20"><input type="button" class="button" value="<?php _e('Delete','mgm')?>" onclick="mgm_delete_protected_url('<?php echo $posts_access->id ?>','posts_access')" /> </div>
</div>


<?php endforeach; else:?>
<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" >
	<div class="cell textaligncenter"><?php _e('No access settings','mgm')?></div>
</div>
<?php endif;?>