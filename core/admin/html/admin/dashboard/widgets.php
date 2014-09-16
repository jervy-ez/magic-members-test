<!--widgets-->
<?php mgm_box_top('Magic Members Dashboard');?>
	<div id="admin_dashboard">
		<div class="cols">
			<div class="col">
				<?php mgm_box_top('Subscription Information', 'subscriptioninformation', false, array('width'=>400));?>
				<div id="dashboard_widget_plugin_subscription_status"><img src="<?php echo MGM_ASSETS_URL?>images/ajax/fb-loader.gif" align="absmiddle" /> <?php _e('Loading...','mgm')?></div>	
				<?php mgm_box_bottom();?>
			</div>
			<div class="col">	
				<?php mgm_box_top('Version Information', 'versioninformation', false, array('width'=>410));?>
				<div id="dashboard_widget_plugin_version_info"><img src="<?php echo MGM_ASSETS_URL?>images/ajax/fb-loader.gif" align="absmiddle" /> <?php _e('Loading...','mgm')?></div>	
				<?php mgm_box_bottom();?>	
			</div>
		</div>	
		<div class="clearfix"></div>
		<div class="cols">
			<div class="col">			
				<?php mgm_box_top('Magic Members News', '', false, array('width'=>400));?>
				<div id="dashboard_widget_site_news"><img src="<?php echo MGM_ASSETS_URL?>images/ajax/fb-loader.gif" align="absmiddle" /> <?php _e('Loading...','mgm')?></div>	
				<?php mgm_box_bottom();?>	
			</div>
			<div class="col">	
				<?php mgm_box_top('Magic Members Blog', '', false, array('width'=>410));?>
				<div id="dashboard_widget_site_blog"><img src="<?php echo MGM_ASSETS_URL?>images/ajax/fb-loader.gif" align="absmiddle" /> <?php _e('Loading...','mgm')?></div>	
				<?php mgm_box_bottom();?>
			</div>	
		</div>
		<div class="clearfix"></div>
		<div class="cols">
			<div class="colw">
				<?php mgm_box_top('Recent Messages', 'recentmessages', false, array('width'=>825));?>
				<div id="dashboard_widget_plugin_messages"><img src="<?php echo MGM_ASSETS_URL?>images/ajax/fb-loader.gif" align="absmiddle" /> <?php _e('Loading...','mgm')?></div>	
				<?php mgm_box_bottom();?>	
			</div>	
		</div>	
		<div class="cols">
			<div class="col">				
				<?php mgm_box_top('Purchased Posts (last 5)', 'purchasedpostslast5', false, array('width'=>400));?>				
				<div id="dashboard_widget_posts_purchased"><img src="<?php echo MGM_ASSETS_URL?>images/ajax/fb-loader.gif" align="absmiddle" /> <?php _e('Loading...','mgm')?></div>
				<?php mgm_box_bottom();?>	
			</div>	
			<div class="col">				
				<?php mgm_box_top('Member Statistics', '', false, array('width'=>410));?>				
				<div id="dashboard_widget_member_statistics"><img src="<?php echo MGM_ASSETS_URL?>images/ajax/fb-loader.gif" align="absmiddle" /> <?php _e('Loading...','mgm')?></div>
				<?php mgm_box_bottom();?>			
			</div>
		</div>		
	</div>
	<div class="clearfix"></div>
<?php mgm_box_bottom();?>
<script language="javascript">
	<!--
	// onready
	jQuery(document).ready(function(){   							
		// get subscription status 
		mgm_get_plugin_subscription_status=function(){
			jQuery("#dashboard_widget_plugin_subscription_status").load('<?php echo mgm_admin_ajax_url('&page=mgm/admin&method=dashboard_widget_plugin_subscription_status')?>');
		}
		// get version 
		mgm_get_plugin_version_info=function(){
			jQuery('#dashboard_widget_plugin_version_info').load('<?php echo mgm_admin_ajax_url('&page=mgm/admin&method=dashboard_widget_plugin_check_version')?>');
		}
		// get messages 
		mgm_get_plugin_messages=function(){
			jQuery('#dashboard_widget_plugin_messages').load('<?php echo mgm_admin_ajax_url('&page=mgm/admin&method=dashboard_widget_plugin_messages')?>');
		}
		// get site_news
		mgm_get_site_news=function(){
			jQuery("#dashboard_widget_site_news").load('<?php echo mgm_admin_ajax_url('&page=mgm/admin&method=dashboard_widget_site_news')?>');
		}
		// get site_blog
		mgm_get_site_blog=function(){
			jQuery("#dashboard_widget_site_blog").load('<?php echo mgm_admin_ajax_url('&page=mgm/admin&method=dashboard_widget_site_blog')?>');
		}
		
		// get posts_purchased
		mgm_get_posts_purchased=function(){
			jQuery("#dashboard_widget_posts_purchased").load('<?php echo mgm_admin_ajax_url('&page=mgm/admin&method=dashboard_widget_posts_purchased')?>');
		}
		// get member_statistics
		mgm_get_member_statistics=function(){
			jQuery("#dashboard_widget_member_statistics").load('<?php echo mgm_admin_ajax_url('&page=mgm/admin&method=dashboard_widget_member_statistics')?>');
		}

		// get subscription status  
		mgm_get_plugin_subscription_status();
		// get version info
		mgm_get_plugin_version_info();
		// get messages
		mgm_get_plugin_messages();
		// get site news
		mgm_get_site_news();
		// get site blog
		mgm_get_site_blog();
		// get posts_purchased
		mgm_get_posts_purchased();
		// get member_statistics
		mgm_get_member_statistics();
	});
//-->	
</script>		