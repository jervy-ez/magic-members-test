<div class="marginbottom10px">
	<div class="table widefatDiv width100">
		<div class="row headrow">
			<div class="cell width30 theadDivCell textalignleft">
	    		<b><?php _e('Post Title','mgm')?></b>
			</div>
			<div class="cell width30 theadDivCell textalignleft">
	    		<b><?php _e('Date Added','mgm')?></b>
			</div>
			<div class="cell width30 theadDivCell textalignleft">
	    		<b><?php _e('Action','mgm')?></b>
			</div>
		</div>
		<?php if($data['postpack_posts']):
				foreach ($data['postpack_posts'] as $i=>$postpack) :
		        $post = get_post($postpack->post_id);?>
	    
		<div class="row brBottom <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="ppp_row_<?php echo $postpack->id ?>">
	        <div class="cell width30  textalignleft"><?php echo $post->post_title ?></div>
	        <div class="cell width30  textalignleft"><?php echo date('d/m/Y H:i', strtotime($postpack->create_dt)) ?></div>
	        <div class="cell width30  textalignleft">
	        	<input onclick="mgm_postpack_post_delete(<?php echo $postpack->id ?>);" class="button" type="button" value="<?php _e('Delete', 'mgm') ?>" /></div>
	    </div>
		<?php
			endforeach;			
		else:?>	
		<div class="row">
			<div class="cell textaligncenter"><?php _e('There are currently no posts associated to this pack.','mgm')?></div>
		</div>
		<?php 
		endif;?>
	</div>
</div>