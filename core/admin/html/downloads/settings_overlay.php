	<div id="download_settings_overlay_<?php echo $download->id ?>" class="apple_overlay">	
		<div class="table widefatDiv">			
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('Download link','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php printf('[%s#%d]', $download_hook, $download->id)?>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('Image Download link','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php printf('[%s#%d#image]', $download_hook, $download->id)?>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('Button Download link','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php printf('[%s#%d#button]', $download_hook, $download->id)?>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('Download link with filesize','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php printf('[%s#%d#size]', $download_hook, $download->id)?>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell fontweightbold textalignright width80">
					<?php _e('Download url only','mgm')?>
				</div>
	
				<div class="cell fontweightbold width20">
					<?php printf('[%s#%d#url]', $download_hook, $download->id)?>
				</div>
			</div>
		</div>
	</div>