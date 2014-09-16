	<div id="addon_settings_overlay_<?php echo $addon->id ?>" class="apple_overlay">	
		<div class="table widefatDiv">			
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('Addon Shortcode','mgm')?>
				</div>	
				<div class="cell fontweightbold width20">
					<?php printf('[addon id="%d"]', $addon->id)?>
				</div>
			</div>			
		</div>
	</div>