<!--profile-->
<?php /*if(isset($_GET['unsubscribed']) && $_GET['unsubscribed']=='true'):?>
<script language="Javascript">//var t = setTimeout ( "window.location='<?php echo wp_logout_url()?>'", 1000 ); </script>
<?php endif;*/?>
<div class="wrap" id="mgm-profile-page">
	<div id="icon-profile" class="icon32"><br /></div> 
	<h2><?php _e('Magic Members - Membership Information','mgm') ?></h2>
	<?php 
	// error notice
	if(isset($_GET['unsubscribe_errors'])):
		echo sprintf('<p><div class="error">%s</div></p>', urldecode(strip_tags($_GET['unsubscribe_errors'])));
	endif;?>
	<div id="poststuff">
		<div class="minhightwidhtauto">					
			<div class="postbox mgm_profile_subscription_info" >
				<h3><b><?php _e('Subscription Information','mgm') ?></b></h3>
				<div class="inside">
					<?php echo mgm_user_subscription_info();?>
				</div>
			</div>			
			<div class="postbox mgm_profile_membership_info">
				<h3><b><?php _e('Membership Information','mgm')?></b></h3>
				<div class="inside">
				<?php echo mgm_user_membership_info(); ?>	
				</div>
			</div>	
			<?php if($info = mgm_user_other_subscriptions_info()):?>
			<div class="postbox mgm_profile_other_subscriptions_info">		
				<h3><b><?php _e('Other Subscriptions Information','mgm')?></b></h3>		
				<div class="inside">
				<?php echo $info; ?>	
				</div>
			</div>		
			<?php endif;?>
		</div>
	</div>	
</div>

<div class="clearfix"></div>
<!--<a>TEST</a>
<div id="download_settings_overlay_<?php echo $download->id ?>" class="apple_overlay"> TESTSTST TSTS </div>	-->
