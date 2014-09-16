<!--mailchimp settings box-->
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
		    		<b><?php _e('Unique List Id','mgm'); ?></b>
				</div>
	    		<div class="cell width2 textaligncenter">
		    		<b>:</b>
				</div>
	    		<div class="cell textalignleft">
		    		<input type="text" name="setting[mailchimp][unique_id]" value="<?php echo $data['module']->setting['unique_id']; ?>" size="40" />
				</div>
	  		<div class="row">
	    		<div class="cell width26 textalignleft">
		    		<b><?php _e('API Key','mgm'); ?></b>
				</div>
	    		<div class="cell width2 textaligncenter">
		    		<b>:</b>
				</div>
	    		<div class="cell textalignleft">
		    		<input type="text" name="setting[mailchimp][apikey]" value="<?php echo $data['module']->setting['apikey']; ?>"  size="40"/>
				</div>
			</div>
	  		<div class="row">
	    		<div class="cell width26 textalignleft">
		    		<b><?php _e('Opt-in','mgm'); ?></b>
				</div>
	    		<div class="cell width2 textaligncenter">
		    		<b>:</b>
				</div>
	    		<div class="cell textalignleft">
					<select name="setting[mailchimp][double_optin]" class="width100px">
						<?php echo mgm_make_combo_options(array('1'=>__('Double opt-in','mgm'),'0'=>__('Single opt-in','mgm')), $data['module']->setting['double_optin'], MGM_KEY_VALUE)?>
					</select>						
				</div>
			</div>
			
		</div>
	</div>		
</div>