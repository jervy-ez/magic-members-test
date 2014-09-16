<!--membership_content-->
<div class="wrap">
	<div id="icon-edit-pages" class="icon32"><br /></div>
	<h2><?php _e('Members\' Contents','mgm') ?></h2>
	<div id="poststuff">	
		<?php $section = isset($_GET['section']) ? strip_tags($_GET['section']) : 'all';?>
		
		<?php if(in_array($section, array('all','accessible'))):?>
		<div class="postbox margin10px0px">
			<h3><?php echo sprintf(__('Your Membership Level "%s" Accessible Contents','mgm'), mgm_stripslashes_deep($data['membership_level'])); ?></h3>
			<div class="inside">
				<?php echo mgm_member_accessible_contents('admin');?>
			</div>
		</div>
		<?php endif;?>
		
		<?php if(in_array($section, array('all','purchased'))):?>
		<div class="postbox margin10px0px">
			<h3><?php _e('Purchased Contents','mgm') ?></h3>
			<div class="inside">
				<?php echo mgm_member_purchased_contents('admin');?>
			</div>
		</div>
		<?php endif;?>
		
		<?php if(in_array($section, array('all','purchasable'))):?>
		<div class="postbox margin10px0px">
			<h3><?php _e('Purchasable Contents','mgm') ?></h3>
			<div class="inside">
				<?php echo mgm_member_purchasable_contents('admin');?>
			</div>
		</div>
		<?php endif;?>
		
	</div>	
</div>