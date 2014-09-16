<!--dependency-->
<?php mgm_box_top('MagicMembers Dependency');?>
<div class="table widefatDiv">
	<div class="row headrow">
		<div class="cell theadDivCell width80">
			<b><?php _e('Title','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width20 textaligncenterimp">
			<b><?php _e('Support','mgm') ?></b>
		</div>
	</div>
	<?php foreach ($data['checks'] as $key =>$check):	?>
	<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?> brBottom">
		<div class="cell width80">
			<?php echo $check['label']; ?>
		</div>
		<div class="cell width20 textaligncenterimp">
			   <?php 
				if (call_user_func('mgm_' . $check['callback'], $key)):
					$support = __('Supported..!','mgm');
					$img_url  = $url = MGM_ASSETS_URL . 'images/icons/tick_pass.png';
				else:
					$support = __('Not Supported..!','mgm');
					$img_url  = $url = MGM_ASSETS_URL . 'images/icons/cross_error.png';
				endif;?>
		  	 <img src="<?php echo $img_url; ?>" width="24" height="24" alt="<?php echo $support; ?>" />		   		
		</div>
	</div>
	<?php endforeach ?>
</div>

<?php mgm_box_bottom();?>
