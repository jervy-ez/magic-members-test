<div id="mgm-wrapper">
  <div id="mgm-panel-wrap">
		<div id="mgm-panel-wrapper">
			<div id="mgm-panel">
				<div id="mgm-panel-content-wrap">
					<div id="mgm-panel-content">	
						<!--logo-->					
						<a href="admin.php?page=mgm/admin"><img src="<?php echo MGM_ASSETS_URL ?>images/logo.png" alt="mgm-panel" class="pngfix" id="mgm-panel-logo" width="150" height="70"/></a>
						<!--end logo-->
						
						<!-- mgm-panel mainmenu -->
						<ul id="mgm-panel-mainmenu">
							<?php $panels = mgm_render_primary_menus();?>							
						</ul>
						<!-- end mgm-panel mainmenu -->
						
						<?php /*
						<!-- tabs contents -->
						<?php if(count($panels) > 0): foreach($panels as $panel): ?>
						<div id="<?php echo $panel?>"><?php echo ucwords( $panel );?> Content....</div>
						<?php endforeach; endif;?>									
						<!-- end tab contents -->
						*/?>

					</div> <!-- end mgm-panel-content div -->

				</div> <!-- end mgm-panel-content-wrap div -->

			</div> <!-- end mgm-panel div -->

		</div> <!-- end mgm-panel-wrapper div -->

		<div id="mgm-panel-bottom">   	
               
			<?php mgm_render_infobar()?>	
			
        </div><!-- end mgm-panel-bottom div -->

        <div style="clear: both;"></div>

	  </div> <!-- end panel-wrap div -->

	</div> <!-- end wrapper div -->

	<?php include('tab2.js.php');?>