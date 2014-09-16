<!--gvo settings box-->
<div id="module_settings_box_<?php echo $data['module']->code?>" class="module_settings_box box_max">	
	<div class="name">
		<input type="radio" name="module" value="<?php echo $data['module']->code?>" <?php echo ( $data['module']->is_enabled() ) ? 'checked' : ''; ?>/>
		<?php echo $data['module']->name?>
	</div>	
	<div class="clearfix"></div>
	<div class="description">
		<?php echo $data['module']->description?>
	</div>			
	<div class="fields">

		<div class="table">
	  		<div class="row">
	    		<div class="cell width26 textalignleft">
		    		<b><?php _e('Campaign','mgm'); ?></b>
				</div>
	    		<div class="cell width2 textaligncenter">
		    		<b>:</b>
				</div>
	    		<div class="cell textalignleft">
		    		<input type="text" name="setting[gvo][campaign]" value="<?php echo $data['module']->setting['campaign']; ?>" size="40" />
				</div>
			</div>
	  		<div class="row">
	    		<div class="cell width26 textalignleft">
		    		<b><?php _e('Form Id','mgm'); ?></b>
				</div>
	    		<div class="cell width2 textaligncenter">
		    		<b>:</b>
				</div>
	    		<div class="cell textalignleft">
		    		<input type="text" name="setting[gvo][formid]" value="<?php echo $data['module']->setting['formid']; ?>"  size="40"/>
				</div>
			</div>
	  		<div class="row">
	    		<div class="cell width26 textalignleft">
		    		<b><?php _e('Affiliate Name','mgm'); ?></b>
				</div>
	    		<div class="cell width2 textaligncenter">
		    		<b>:</b>
				</div>
	    		<div class="cell textalignleft">
		    		<input type="text" name="setting[gvo][affiliatename]" value="<?php echo $data['module']->setting['affiliatename']; ?>"  size="40"/>
				</div>
			</div>
		</div>				
	</div>		
</div>