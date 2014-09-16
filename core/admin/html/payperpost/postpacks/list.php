<div class="table widefatDiv">
	<div class="row headrow">
		<div class="cell theadDivCell width5 textalignleft">
    		<b><?php _e('ID','mgm')?></b>
		</div>
		<div class="cell theadDivCell width15 textalignleft">
    		<b><?php _e('Shortcode','mgm')?></b>
		</div>
		<div class="cell theadDivCell width15 textalignleft">
    		<b><?php _e('Name','mgm')?></b>
		</div>
		<div class="cell theadDivCell  width10 textalignleft">
    		<b><?php _e('Cost','mgm')?></b>
		</div>
		<div class="cell theadDivCell width15 textalignleft">
    		<b><?php _e('Description','mgm')?></b>
		</div>
		<div class="cell theadDivCell width15 textalignleft">
    		<b><?php _e('Date Created','mgm')?></b>
		</div>
		<div class="cell theadDivCell width25 textalignleft">
    		<b><?php _e('Action','mgm')?></b>
		</div>
	</div>
	<?php if($data['postpacks']): foreach ($data['postpacks'] as $i=>$postpack) :		
			// count
			$posts_count = mgm_get_postpack_posts($postpack->id, true); 
			$sformat = mgm_get_date_format('date_format_short');
	?>
	<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?> brBottom" id="postpack_row_<?php echo $postpack->id ?>">
		<div class="cell width5 textalignleft">
    		<?php echo $postpack->id ?>
		</div>
		<div class="cell width15">
    		[payperpost_pack#<?php echo $postpack->id ?>]
		</div>
		<div class="cell width15 textalignleft">
    		<?php echo $postpack->name ?>
		</div>
		<div class="cell width10 textalignleft">
    		<?php echo mgm_format_currency($postpack->cost) . ' ' . $data['currency']?>
		</div>
		<div class="cell width15 textalignleft">
    		<?php echo $postpack->description ?>
		</div>
		<div class="cell width15 textalignleft">
    		<?php echo date($sformat, strtotime($postpack->create_dt)) ?>
		</div>
		<div class="cell width25">
			<a href="javascript:mgm_postpack_edit('<?php echo $postpack->id ?>')" title="<?php _e('Edit', 'mgm') ?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/edit.png" /></a>	
			<a href="javascript:mgm_postpack_delete('<?php echo $postpack->id ?>')" title="<?php _e('Delete', 'mgm') ?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-cross.png" /></a>
			<a href="javascript:mgm_postpack_posts('<?php echo $postpack->id ?>')" title="<?php _e('Pack Posts', 'mgm') ?>"><img src="<?php echo MGM_ASSETS_URL?>images/icons/brick.png" /></a>					
			
			<?php /*?><input class="button" name="edit" type="button" value="<?php _e('Edit', 'mgm') ?>" onclick="mgm_postpack_edit('<?php echo $postpack->id ?>');"/>
			<input class="button" name="delete" type="button" value="<?php _e('Delete', 'mgm') ?>" onclick="mgm_postpack_delete('<?php echo $postpack->id ?>');"/>
			<input class="button" name="posts" type="button" value="<?php echo $posts_count . ' ' .__(($posts_count == 1 ? 'Post':'Posts'), 'mgm') ?>" onclick="mgm_postpack_posts('<?php echo $postpack->id ?>');"/>	 <?php */?>
		</div>
	</div>
	<?php endforeach;else:?>
	<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
		<div class="cell textaligncenter">
    		<?php _e('You haven\'t created any post pack yet.','mgm')?>
		</div>
	</div>
	<?php endif;?>
	
</div>

<br />