	<!--logs-->
	<?php mgm_box_top('Transaction Logs');?>
		<div class="table widefatDiv form-table">
			<div class="row headrow">
				<div class="cell theadDivCell width75px">
					<b><?php _e('ID#','mgm') ?></b>
				</div>
				<div class="cell theadDivCell width120px">
					<b><?php _e('Type','mgm') ?></b>
				</div>
				<div class="cell theadDivCell width100px">
					<b><?php _e('Module','mgm') ?></b>
				</div>
				<div class="cell theadDivCell width100px">
					<b><?php _e('Status','mgm') ?></b>
				</div>
				<div class="cell theadDivCell width100px">
					<b><?php _e('Status Text','mgm') ?></b>
				</div>
				<div class="cell theadDivCell width100px">
					<b><?php _e('Transaction Date','mgm') ?></b>
				</div>
			</div>
			<?php if(count($data['transactions_logs'])>0): foreach($data['transactions_logs'] as $tran_log):?>
	  		<div class="brBottom row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
	    		<div class="cell width75px">
		    		<?php echo $tran_log->id ?>		   
				</div>
	    		<div class="cell width120px">
		    		<?php echo ucwords(str_replace('_',' ',$tran_log->payment_type)) ?>		   
				</div>
	    		<div class="cell width100px">
		    		<?php echo ucwords($tran_log->module) ?>		   
				</div>
	    		<div class="cell width100px">
		    		<?php echo ($tran_log->status)? $tran_log->status : __('N/A', 'mgm') ?>
				</div>
	    		<div class="cell width100px">
		    		<?php echo ($tran_log->status_text)? $tran_log->status_text : __('N/A', 'mgm') ?>		   
				</div>
	    		<div class="cell width100px">
		    		<?php echo date(MGM_DATE_FORMAT_SHORT, strtotime($tran_log->transaction_dt))?>		   
				</div>
			</div>
			<?php endforeach; else:?>
			<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
				<div class="cell textaligncenter">
					<?php _e('No transaction log','mgm')?>					 					
				</div>
			</div>
			<?php endif;?>	
		</div>	

	<?php mgm_box_bottom();?>
	
	<?php mgm_box_top('Rest API Access Logs');?>
	
		<div class="table widefatDiv form-table">
			<div class="row headrow">
				<div class="cell theadDivCell width75px">
					<b><?php _e('API Key#','mgm') ?></b>
				</div>
				<div class="cell theadDivCell width120px">
					<b><?php _e('URI','mgm') ?></b>
				</div>
				<div class="cell theadDivCell width100px">
					<b><?php _e('Method','mgm') ?></b>
				</div>
				<div class="cell theadDivCell width100px">
					<b><?php _e('IP','mgm') ?></b>
				</div>
				<div class="cell theadDivCell width100px ">
					<b><?php _e('Authorized?','mgm') ?></b>
				</div>
				<div class="cell theadDivCell width100px">
					<b><?php _e('Date','mgm') ?></b>
				</div>
			</div>
			
			<?php if(count($data['api_logs'])>0): foreach($data['api_logs'] as $api_log):?>
	  		<div class="brBottom row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
	    		<div class="cell width75px">
		    		<?php echo $api_log->api_key ?>		   
				</div>
	    		<div class="cell width120px">
		    		<?php echo $api_log->uri ?>		   
				</div>
	    		<div class="cell width100px">
		    		<?php echo strtoupper($api_log->method) ?>		   
				</div>
	    		<div class="cell width100px">
		    		<?php echo $api_log->ip_address ?>		   
				</div>
	    		<div class="cell width100px">
			   		<?php echo ($api_log->is_authorized=='Y')? sprintf('<span class="mgm_proxy_installed">%s</span>',__('Yes','mgm')) 
                                         : sprintf('<span class="mgm_proxy_not_installed">%s</span>',__('No', 'mgm'))  ?>		   

				</div>
	    		<div class="cell width100px">
		    		<?php echo date(MGM_DATE_FORMAT_LONG_TIME, strtotime($api_log->create_dt))?>		   
				</div>
			</div>
			<?php endforeach; else:?>
			<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
				<div class="cell textaligncenter">
					<?php _e('No api log','mgm')?>					 					
				</div>
			</div>
			<?php endif;?>		
		</div>	

	<?php mgm_box_bottom();?>
	
	<script language="javascript">
	<!--
	jQuery(document).ready(function(){
			
	});
	//-->	
	</script>