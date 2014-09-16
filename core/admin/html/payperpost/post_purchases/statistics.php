<!--purchases statistics-->
<div class="table widefatDiv">
	<div class="row headrow">
		<div class="cell theadDivCell width50">
			<b><?php _e('Post Title','mgm')?></b>
		</div>
		<div class="cell theadDivCell width50 ">
			<b><?php _e('Purchased','mgm')?></b>
		</div>
	</div>
	<?php if($data['posts']):	foreach ($data['posts'] as $post) :?>
	<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?> brBottom">					
		<div class="cell width50">
    		<?php echo $post->title?>
		</div>
		<div class="cell width50">
    		<?php echo $post->count?>
		</div>
	</div>
	<?php endforeach; else:?> 
	<div class="row">
		<div class="cell textaligncenter">
    		<?php _e('No posts have been purchased yet','mgm')?>
		</div>
	<?php endif;?>		
</div><br />
