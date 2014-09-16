	<div class="table table_content">
		<p class="sub"><?php  _e('Membership','mgm') ?></p>
		<div class="div_table">
			<?php foreach($data['level_statistics'] as $statistic):?>
			<div class="row list">
				<div class="cell count mgm_aleft mgm_width_30px"><a href="javascript:void(0)"><?php echo $statistic['count']?></a></div>
				<div class="cell name mgm_aleft "><a href="javascript:void(0)"><?php echo mgm_ellipsize($statistic['name'],45) ?></a></div>
			</div>	
			<?php endforeach;?>	
		</div>
	</div>	
	<div class="table table_discussion">
		<p class="sub"><?php _e('Status','mgm') ?></p>
		<div class="div_table">
			<?php foreach($data['status_statistics'] as $statistic): ?>
			<div class="row list">
				<div class="cell count mgm_aleft mgm_width_30px"><a href="javascript:void(0)"><?php echo $statistic['count']?></a></div>
				<div class="cell name mgm_aleft"><span class="<?php echo $statistic['css_class']?>"><?php echo $statistic['name'] ?></span></div>
			</div>
			<?php endforeach;?>	
		</div>
	</div>			
	<div class="versions" style="padding-top:5px">
		<?php echo sprintf(__('You are using <b>Magic Members %s</b>', 'mgm'), mgm_get_class('auth')->get_product_info('product_version'));?>
	</div>