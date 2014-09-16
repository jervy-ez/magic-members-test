<!--icontact settings box-->
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
		    		<b><?php _e('Client Id','mgm'); ?></b>
				</div>
	    		<div class="cell width2 textaligncenter">
		    		<b>:</b>
				</div>
	    		<div class="cell textalignleft">
		    		<input type="text" name="setting[icontact][clientid]" value="<?php echo $data['module']->setting['clientid']; ?>"  size="40"/>
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
		    		<input type="text" name="setting[icontact][formid]" value="<?php echo $data['module']->setting['formid']; ?>" size="40" />
				</div>
			</div>
	  		<div class="row">
	    		<div class="cell width26 textalignleft">
		    		<b><?php _e('List Id','mgm'); ?></b>
				</div>
	    		<div class="cell width2 textaligncenter">
		    		<b>:</b>
				</div>
	    		<div class="cell textalignleft">
					<input type="text" name="setting[icontact][listid]" value="<?php echo $data['module']->setting['listid']; ?>"  size="40"/>
				</div>
			</div>
	  		<div class="row">
	    		<div class="cell width26 textalignleft">
		    		<b><?php _e('Special Id','mgm'); ?></b>
				</div>
	    		<div class="cell width2 textaligncenter">
		    		<b>:</b>
				</div>
	    		<div class="cell textalignleft">
					<input type="text" name="setting[icontact][specialid]" value="<?php echo $data['module']->setting['specialid']; ?>"  size="40"/>
				</div>
			</div>
	  		<div class="row">
	    		<div class="cell width26 textalignleft">
		    		<b><?php _e('Doubleopt Id','mgm'); ?></b>
				</div>
	    		<div class="cell width2 textaligncenter">
		    		<b>:</b>
				</div>
	    		<div class="cell textalignleft">
					<select name="setting[icontact][doubleopt]" class="width100px">
						<?php echo mgm_make_combo_options(array('1'=>__('Yes','mgm'),'0'=>__('No','mgm')), $data['module']->setting['doubleopt'], MGM_KEY_VALUE)?>
					</select>						
				</div>
			</div>
		</div>	
		
	</div>
</div>