<!--general-->
<?php header('Content-Type: text/html; charset=UTF-8');?>
<?php // mgm_pr($data['system_obj']);?>
<form name="frmsetgen" id="frmsetgen" method="post" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=general">
	<?php mgm_box_top('Main Settings');?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Administrator email address','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="text" name="admin_email" value="<?php echo esc_html($data['system_obj']->get_setting('admin_email')); ?>" size="100" maxlength="150" />
				<p><div class="tips width90"><?php _e('Enter the email address where you will receive the notifications.','mgm'); ?></div></p>
			</div>
		</div>
		<div class="row">
    		<div class="cell">
				<p>
				<input type="checkbox" id="enable_login_redirect_url" value="Y" <?php echo ($data['system_obj']->get_setting('login_redirect_url') != '') ? 'checked' : '';?>/>
				<b><?php _e('Redirect after login','mgm'); ?>:</b></p>			
			</div>
		</div>
  		<div class="row">
    		<div class="cell <?php echo ($data['system_obj']->get_setting('login_redirect_url') == '') ? 'displaynone' : '';?>">
				<?php _e('URL', 'mgm')?>: <input type="text" name="login_redirect_url" id="login_redirect_url" value="<?php echo esc_html($data['system_obj']->get_setting('login_redirect_url')); ?>" size="120" />
				<p><div class="tips width90"><?php _e('Url to redirect after successful login, URL short codes : [username]-Displays user username.','mgm'); ?></div></p>
			</div>
		</div>

		<div class="row">
    		<div class="cell">
				<p><input type="checkbox" id="enable_logout_redirect_url" value="Y"  <?php echo ($data['system_obj']->get_setting('logout_redirect_url') != '') ? 'checked' : '';?>/>
				<b><?php _e('Redirect after logout','mgm'); ?>:</b></p>			
			</div>
		</div>
  		<div class="row">
    		<div class="cell <?php echo ($data['system_obj']->get_setting('logout_redirect_url') == '') ? 'displaynone' : '';?>">
				<?php _e('URL', 'mgm')?>: <input type="text" name="logout_redirect_url" id="logout_redirect_url" value="<?php echo esc_html($data['system_obj']->get_setting('logout_redirect_url')); ?>" size="120" />
				<p><div class="tips width90"><?php _e('Url to redirect after successful logout.','mgm'); ?></div></p>
			</div>
		</div>

  		<div class="row">
    		<div class="cell">
				<input type="checkbox" id="enable_category_access_redirect_url" value="Y" <?php echo ($data['system_obj']->get_setting('category_access_redirect_url') != '') ? 'checked' : '';?>/>
				<b><?php _e('Redirect if category access denied','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell <?php echo ($data['system_obj']->get_setting('category_access_redirect_url') == '') ? 'displaynone' : '';?>">
				<?php _e('URL', 'mgm')?>: <input type="text" name="category_access_redirect_url" id="category_access_redirect_url" value="<?php echo esc_html($data['system_obj']->get_setting('category_access_redirect_url')); ?>" size="120" />
				<p><div class="tips width90"><?php _e('Url to redirect if access denied to a category.','mgm'); ?></div></p>
			</div>
		</div>
		
  		<div class="row">
    		<div class="cell">
				<p>
				<input type="checkbox" name="enable_autologin" id="enable_autologin" value="Y" <?php echo (bool_from_yn($data['system_obj']->get_setting('enable_autologin'))) ? 'checked' : '';?>/>
				<b><?php _e('Enable auto login after register','mgm'); ?>:</b></p>
				<p><div class="tips width90"><?php _e('Turn On/Off auto login after register. When "On", Auto login and redirect users to profile page after registration is complete.','mgm'); ?></div></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell <?php echo (!bool_from_yn($data['system_obj']->get_setting('enable_autologin'))) ? 'displaynone' : '';?>">
				<?php _e('Redirect URL', 'mgm')?>: <input type="text" name="autologin_redirect_url" id="autologin_redirect_url" value="<?php echo esc_html($data['system_obj']->get_setting('autologin_redirect_url')); ?>" size="120" />
				<p><div class="tips width90"><?php _e('Url to redirect if after register auto login enabled. By default redirects to profile.URL short codes : [username]-Displays user username','mgm'); ?></div></p>
			</div>
		</div>
		
  		<div class="row">
    		<div class="cell">
				<p><input type="checkbox" name="enable_guest_lockdown" id="enable_guest_lockdown" value="Y" <?php echo (bool_from_yn($data['system_obj']->get_setting('enable_guest_lockdown'))) ? 'checked' : '';?>/>
				<b><?php _e('Enable guest lockdown','mgm'); ?>:</b></p>
				<p><div class="tips width90"><?php _e('Turn On/Off site browsing for guest user. Only specific pages will be given access to login or register.','mgm'); ?></div></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell <?php echo (!bool_from_yn($data['system_obj']->get_setting('enable_guest_lockdown'))) ? 'displaynone' : '';?>">
				<?php _e('Redirect URL', 'mgm')?>: <input type="text" name="guest_lockdown_redirect_url" id="guest_lockdown_redirect_url" value="<?php echo esc_html($data['system_obj']->get_setting('guest_lockdown_redirect_url')); ?>" size="120" />
				<p><div class="tips width90"><?php _e('Url to redirect if guest lockdown enabled. By default redirects to login.','mgm'); ?></div></p>
			</div>
		</div>		
 		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Enable redirection to post/page url after content purchase or login?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="enable_post_url_redirection" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('enable_post_url_redirection'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="enable_post_url_redirection" value="N" <?php if (!bool_from_yn($data['system_obj']->get_setting('enable_post_url_redirection'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off post url redirection. When "On", redirect users to the last visited post url.','mgm'); ?></div></p>
			</div>
		</div>		
 		
 		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Disable emails from payment gateways?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="disable_gateway_emails" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('disable_gateway_emails'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="disable_gateway_emails" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('disable_gateway_emails'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off gateway emails. When "On", stops payment notification emails to the administrator.','mgm'); ?></div></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Disable payment notification emails from modules?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="disable_payment_notify_emails" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('disable_payment_notify_emails'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="disable_payment_notify_emails" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('disable_payment_notify_emails'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off payment notification emails. When "On", stops payment notification emails to users.','mgm'); ?></div></p>
			</div>
		</div>
		
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Disable registration email if Buddypress is enabled?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="disable_registration_email_bp" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('disable_registration_email_bp'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="disable_registration_email_bp" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('disable_registration_email_bp'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off registration notification email if Buddypress plugin is enabled.','mgm'); ?></div></p>
			</div>
		</div>

  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Enable multiple membership level purchase?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="enable_multiple_level_purchase" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('enable_multiple_level_purchase'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="enable_multiple_level_purchase" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('enable_multiple_level_purchase'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off multiple membership level purchase. When "On", allows users to purchase multiple membership levels.','mgm'); ?></div></p>
			</div>
		</div>

		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Enable nested shortcode parsing?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="enable_nested_shortcode_parsing" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('enable_nested_shortcode_parsing'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="enable_nested_shortcode_parsing" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('enable_nested_shortcode_parsing'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off nested Shortcode parsing. When "On", nested shortcodes will be parsed i.e. [private] [gallery] [/private].','mgm'); ?></div></p>
			</div>
		</div>
		
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Enable guest content purchase?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" id="enabley" name="enable_guest_content_purchase" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('enable_guest_content_purchase'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" id="enablen" name="enable_guest_content_purchase" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('enable_guest_content_purchase'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<div id="enable_guest_purchase_setting" class="marginleft10px <?php echo (bool_from_yn($data['system_obj']->get_setting('enable_guest_content_purchase'))) ? 'displayblock' : 'displaynone'; ?>  paddingtop10px">
					<p><?php _e('Links to show on purchase options','mgm')?></p>
					<input type="checkbox" name="guest_content_purchase_options_links[]" value="register_purchase" <?php if (in_array('register_purchase', (array)$data['system_obj']->get_setting('guest_content_purchase_options_links'))) { echo 'checked="true"'; } ?>/> <?php _e('Register & Purchase','mgm'); ?>
					<input type="checkbox" name="guest_content_purchase_options_links[]" value="purchase_only" <?php if (in_array('purchase_only', (array)$data['system_obj']->get_setting('guest_content_purchase_options_links'))) { echo 'checked="true"'; } ?>/> <?php _e('Purchase Only','mgm'); ?>
					<input type="checkbox" name="guest_content_purchase_options_links[]" value="login_purchase" <?php if (in_array('login_purchase', (array)$data['system_obj']->get_setting('guest_content_purchase_options_links'))) { echo 'checked="true"'; } ?>/> <?php _e('Login & Purchase','mgm'); ?>						
				</div>
				<p><div class="tips width90"><?php _e('Turn On/Off guest content purchase. When "On", allows the guest to purchase content without registration.','mgm'); ?></div></p>
			</div>
		</div>

		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Enable logout link?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="enable_logout_link" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('enable_logout_link'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="enable_logout_link" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('enable_logout_link'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off logout link in menu.','mgm'); ?></div></p>
			</div>
		</div>
		
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Enable Schedular Process Inactive Users','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="enable_process_inactive_users" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('enable_process_inactive_users'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="enable_process_inactive_users" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('enable_process_inactive_users'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off auto processing Inactive users by CRON. This is legacy setting, only use when a re-processing of Inactive users are necessary.','mgm'); ?></div></p>
			</div>
		</div>
		<div class="row <?php echo $hide_rbml = !is_super_admin() ? 'displaynone' : ''; ?>">
    		<div class="cell">
				<p><b><?php _e('Enable Role Based Menu Loading','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row" <?php echo $hide_rbml?>>
  			<div class="cell">
				<input type="radio" id="enable_role_based_menu_loading_y" name="enable_role_based_menu_loading" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('enable_role_based_menu_loading'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="enable_role_based_menu_loading" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('enable_role_based_menu_loading'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off Role/Capability Based Menu/Sub menu Loading.','mgm'); ?></div></p>
			</div>
		</div>
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Change the register button text','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="text" name="register_text" value="<?php echo $data['system_obj']->get_setting('register_text'); ?>" size="50" />
				<p><div class="tips width90">
					<?php _e('Change the register button text.','mgm'); ?>
				</div></p>
			</div>
		</div>
		<div class="row <?php echo $hide_rbml = !is_super_admin() ? 'displaynone' : ''; ?>">
    		<div class="cell">
				<p><b><?php _e('Post Delay Preference','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row" <?php echo $hide_rbml?>>
  			<div class="cell">
				<input type="radio" name="post_delay_preference" value="registration_date" <?php if ($data['system_obj']->get_setting('post_delay_preference') == 'registration_date') { echo 'checked="true"'; } ?>/> <?php _e('Registration Date','mgm'); ?>
				<input type="radio" name="post_delay_preference" value="pack_join_date"  <?php if ($data['system_obj']->get_setting('post_delay_preference') == 'pack_join_date') { echo 'checked="true"'; } ?>/> <?php _e('Pack Join Date','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Post delay calculating above selected date peference','mgm'); ?></div></p>
			</div>
		</div>
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Enable autoresponder unsubscribe','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="autoresponder_unsubscribe" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('autoresponder_unsubscribe'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="autoresponder_unsubscribe" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('autoresponder_unsubscribe'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off autoresponder unsubscribe form the mailing list, when user unsubscribes from the site.' ,'mgm'); ?></div></p>
			</div>
		</div>
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Enable  public profile site wide','mgm'); ?>:</b></p>
			</div>
		</div>
		
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="enable_public_profile" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('enable_public_profile'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="enable_public_profile" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('enable_public_profile'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off public profile site wide.' ,'mgm'); ?></div></p>
			</div>
		</div>	
		
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Override Theme for Custom Pages','mgm'); ?>:</b></p>
			</div>
		</div>
		
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="override_theme_for_custom_pages" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('override_theme_for_custom_pages'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="override_theme_for_custom_pages" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('override_theme_for_custom_pages'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Override theme template for Custom Pages(if present). Put register_page.php in active theme to apply custom design to register page.' ,'mgm'); ?></div></p>
			</div>
		</div>	
		
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Unsubscribe Autoresponder on Subscription Expiration','mgm'); ?>:</b></p>
			</div>
		</div>
		
		<div class="row">
  			<div class="cell">
				<input type="radio" name="unsubscribe_autoresponder_on_expire" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('unsubscribe_autoresponder_on_expire'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="unsubscribe_autoresponder_on_expire" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('unsubscribe_autoresponder_on_expire'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Unsubcribe from Autoresponder when Subscription expired.' ,'mgm'); ?></div></p>
			</div>
		</div>	
		
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Disable custom fields on register pages?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="hide_custom_fields" value="Y" <?php if ($data['system_obj']->get_setting('hide_custom_fields') == 'Y') { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="hide_custom_fields" value="N"  <?php if ($data['system_obj']->get_setting('hide_custom_fields') == 'N') { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>
				<input type="radio" name="hide_custom_fields" value="W"  <?php if ($data['system_obj']->get_setting('hide_custom_fields') == 'W') { echo 'checked="true"'; } ?>/> <?php _e('Wordpress Default','mgm'); ?>
				<input type="radio" name="hide_custom_fields" value="C"  <?php if ($data['system_obj']->get_setting('hide_custom_fields') == 'C') { echo 'checked="true"'; } ?>/> <?php _e('MagicMembers Custom','mgm'); ?>															
				<p><div class="tips width90"><?php _e('Turn On/Off custom user fields on all register pages. When "On", custom user fields will be hidden from all new user registration pages, but they will be visible in the profile.','mgm'); ?></div></p>
			</div>
		</div>
		
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Enable Email as Username?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="enable_email_as_username" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('enable_email_as_username'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="enable_email_as_username" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('enable_email_as_username'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off Email as Username. When "On", allows the users to use register/login with Email.','mgm'); ?></div></p>
			</div>
		</div>
		<!-- issue #1464 -->
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Enable default wordpress lost password?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="enable_default_wp_lost_password" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('enable_default_wp_lost_password'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="enable_default_wp_lost_password" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('enable_default_wp_lost_password'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off  default wordpress lost password. When "On", allows the users to change password in wordpress default lost password screen, When "Off" It will send username and password as email.','mgm'); ?></div></p>
			</div>
		</div>		
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Enable new user email notification after user is active ?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="enable_new_user_email_notifiction_after_user_active" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('enable_new_user_email_notifiction_after_user_active'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="enable_new_user_email_notifiction_after_user_active" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('enable_new_user_email_notifiction_after_user_active'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off new user email notification after user is active, When "On", receives the registration email when user become active only, When "Off" receives the registration email when user clicks the register button.','mgm'); ?></div></p>
			</div>
		</div>	

		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Disable test cookie in login forms ?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="disable_testcookie" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('disable_testcookie'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="disable_testcookie" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('disable_testcookie'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off test cookie in login forms, When "On", disables the test cookie in login forms, When "Off" enables the test cookie in login forms.','mgm'); ?></div></p>
			</div>
		</div>		
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Http request timeout','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="get_http_request_timeout" value="<?php echo esc_html($data['system_obj']->get_setting('get_http_request_timeout')); ?>" size="10" />
				<p><div class="tips width90"><?php _e('It will control the http request time out in seconds.','mgm'); ?></div></p>
    		</div>
		</div>			
		<?php

		// set some specific	

		list($mlt_unit, $mlt_expr) = explode(' ', $data['system_obj']->get_setting('multiple_login_time_span', '1 HOUR'));?>
					
		<div class="row">

    		<div class="cell">

    			<p><b><?php _e('Multiple Logins Time Span','mgm'); ?>:</b></p>

    		</div>

		</div>

  		<div class="row">

    		<div class="cell">

				<input type="text" name="multiple_login_time_span_unit" value="<?php echo (int)esc_html($mlt_unit)?>" size="10" />
				
				<select name="multiple_login_time_span_expr" class="width150px;">

					<?php echo mgm_make_combo_options($data['qsa_expr'], $mlt_expr, MGM_KEY_VALUE); ?>		

				</select>	

				<p><div class="tips width90"><?php _e('Multiple logins time duration.','mgm'); ?></div></p>

    		</div>

		</div>
		<?php /*?><div class="row">
    		<div class="cell">
				<p><b><?php _e('Disable custom fields on wordpress register page?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="disable_default_wp_register" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('disable_default_wp_register'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="disable_default_wp_register" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('disable_default_wp_register'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off custom fields on default wordpress register page. When turned off, other plugin can hook into magicmembers custom register page.','mgm'); ?></div></p>
			</div>
		</div><?php */?>
	</div>
	
	<?php mgm_box_bottom();?>

	<?php mgm_box_top('Css Settings');?>
	
	<div class="table">
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Css Settings setup','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<select name="css_settings" class="width100px">
					<?php echo mgm_make_combo_options(mgm_get_available_themes($type='css'),$data['system_obj']->get_setting('css_settings')); ?>	
				</select>					
				<p><div class="tips width90"><?php _e('Setting up the css theme group ','mgm'); ?></div></p>
    		</div>
		</div>
	</div>	
	<?php mgm_box_bottom();?>		

	<?php mgm_box_top('Download Settings');?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Redirection URL - download do not have access.','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="no_access_redirect_download" value="<?php echo $data['system_obj']->get_setting('no_access_redirect_download'); ?>" size="50" />
				<p><div class="tips width90">
					<?php _e('Redirection URL for download denial, users will be redirected to this url who has no access.','mgm'); ?>
				</div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Download Manager Hook','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="download_hook" value="<?php echo esc_html($data['system_obj']->get_setting('download_hook')); ?>" size="50" />
				<p><div class="tips width90"><?php _e('The hook that the download manager looks for. Default is "download" which would form [download#1] within a post','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Download Slug','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="download_slug" value="<?php echo esc_html($data['system_obj']->get_setting('download_slug')); ?>" size="50" />
				<p><div class="tips width90"><?php _e('The slug that appears in download url. After editing, refresh rewrite cache by using permalink settings page and hit save once. Default is "download"','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('External Resource for Downloads','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><input type="checkbox" name="aws_enable_s3" id="aws_enable_s3" value="Y" <?php echo (bool_from_yn($data['system_obj']->get_setting('aws_enable_s3'))) ? 'checked' : '';?>/> 
				<b><?php _e('Enable Amazon s3 for Digital Downloads?','mgm'); ?></b></p>
				<div id="aws_enable_s3_setting" class="<?php echo (bool_from_yn($data['system_obj']->get_setting('aws_enable_s3'))) ? 'displayblock' : 'displaynone'; ?> paddingtop10px">
					<p><b><?php _e('AWS Key','mgm')?>:</b></p>
					<input type="text" name="aws_key" value="<?php echo esc_html($data['system_obj']->get_setting('aws_key')); ?>" size="50" />
					<div class="tips width90">
						<?php printf(__('AWS Key from Amazon Console, See <a href="%s" target="_blank">AWS Security Credentials</a>.','mgm'),'http://aws.amazon.com/security-credentials'); ?>
					</div>
					
					<p><b><?php _e('AWS Secret Key','mgm')?>:</b></p>
					<input type="text" name="aws_secret_key" value="<?php echo esc_html($data['system_obj']->get_setting('aws_secret_key')); ?>" size="80" />
					<div class="tips width90">
						<?php printf(__('AWS Secret Key from Amazon Console, See <a href="%s" target="_blank">AWS Security Credentials</a>.','mgm'),'http://aws.amazon.com/security-credentials'); ?>
					</div>
					
					<?php
					// set some specific	
					list($qsa_unit, $qsa_expr) = explode(' ', $data['system_obj']->get_setting('aws_qsa_expires', '1 HOUR'));?>
					<p><input type="checkbox" name="aws_enable_qsa" id="aws_enable_qsa" value="Y" <?php echo (bool_from_yn($data['system_obj']->get_setting('aws_enable_qsa'))) ? 'checked' : '';?>/> 
					<b><?php _e('Enable Query String Authentication?','mgm'); ?></b></p>					
					<div id="aws_enable_qsa_setting" class="<?php echo (bool_from_yn($data['system_obj']->get_setting('aws_enable_qsa', 'N'))) ? 'displayblock' : 'displaynone'; ?> paddingtop10px">
						<input type="text" name="aws_qsa_expires_unit" value="<?php echo (int)esc_html($qsa_unit)?>" size="10"/>
						<select name="aws_qsa_expires_expr" class="width100px">
							<?php echo mgm_make_combo_options($data['qsa_expr'], $qsa_expr, MGM_KEY_VALUE); ?>	
						</select>
					</div>		
					<div class="tips width90">
						<?php printf(__('AWS Query String Authentication allows you to create a time bound public url to Amazon Resources. The link will bypass your server and traffic while keeping the downloads secure. Please read <a href="%s">here</a>.','mgm'), ' http://docs.amazonwebservices.com/AmazonS3/latest/dev/S3_QSAuth.html'); ?>
					</div>
				</div>
    		</div>
		</div>

	</div>	

	<?php mgm_box_bottom();?>	
	
	<?php mgm_box_top('Email Configuration & Settings');?>
	
	<div class="table">
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Email From','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="from_email" value="<?php echo esc_html($data['system_obj']->get_setting('from_email')); ?>" size="50" />
				<p><div class="tips width90"><?php _e('Email of the sender ','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Email From Name','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="from_name" value="<?php echo esc_html($data['system_obj']->get_setting('from_name')); ?>" size="50" />
				<p><div class="tips width90"><?php _e('Name of the sender (you or your site&rsquo;s name)','mgm'); ?></div></p>
    		</div>
		</div>

		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Email Content Type','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<select name="email_content_type" class="width150px;">
					<?php echo mgm_make_combo_options(array('text/html','text/plain'), $data['system_obj']->get_setting('email_content_type'), MGM_VALUE_ONLY)?>	
				</select>					
				<p><div class="tips width90"><?php _e('Content Type of emails','mgm'); ?></div></p>
    		</div>
		</div>
		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Email Charset','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<select name="email_charset">
					<?php echo mgm_make_combo_options(array('UTF-8','ISO-8859-1','ISO-8859-9','windows-1254'), $data['system_obj']->get_setting('email_charset'), MGM_VALUE_ONLY)?>	
				</select>					
				<p><div class="tips width90"><?php _e('Charset of emails','mgm'); ?></div></p>
    		</div>
		</div>

		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Apply Changes Globally','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<select name="email_headers_global" class="width150px;">
					<?php echo mgm_make_combo_options(array('Y'=>__('Yes','mgm'),'N'=>__('No','mgm')), $data['system_obj']->get_setting('email_headers_global'), MGM_KEY_VALUE)?>	
				</select>					
				<p><div class="tips width90"><?php _e('Apply Email settings behavior, use with caution, if selected as "Yes", it will apply the settings to all outgoing emails, otherwise to Magic Members emails only','mgm'); ?></div></p>
    		</div>
		</div>
	</div>	
	<?php mgm_box_bottom();?>
	
	<?php mgm_box_top('Account Expiration Reminder Email Configuration & Settings');?>
	
	<div class="table">
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Days to Start','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="reminder_days_to_start" value="<?php echo esc_html($data['system_obj']->get_setting('reminder_days_to_start')); ?>" size="50" />
				<p><div class="tips width90"><?php _e('Days to start the email i.e. 5 Days','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Incremental','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="checkbox" name="reminder_days_incremental" value="Y" <?php echo (bool_from_yn($data['system_obj']->get_setting('reminder_days_incremental'))) ? 'checked' : '';?>/>
				<input type="text" name="reminder_days_incremental_ranges" value="<?php echo esc_html($data['system_obj']->get_setting('reminder_days_incremental_ranges')); ?>" <?php echo (bool_from_yn($data['system_obj']->get_setting('reminder_days_incremental')))?'':'disabled=true';?> />
				<p><div class="tips width90"><?php _e('Days range i.e. 5,3,1. With wrong value provided, default will be used','mgm'); ?></div></p>
    		</div>
		</div>
	</div>		
	<?php mgm_box_bottom();?>	
	
	<?php mgm_box_top('Payment/Subscription Settings');?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Select the Currency which will be used for the payments','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<select name="currency" id="currency" class="width200px">					
					<?php echo mgm_make_combo_options(mgm_get_currencies(), $data['system_obj']->get_setting('currency'), MGM_KEY_VALUE)?>
				</select>		
				<p><div class="tips width90"><?php _e('All Payment transaction currency','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Your Subscription Name','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="subscription_name" value="<?php echo esc_html($data['system_obj']->get_setting('subscription_name')); ?>" size="50" />
				<p><div class="tips width90"><?php _e('The name of the membership to display on the order form.<br> Use [blogname], [membership] tags to set blogname and membership respectively.','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Use SSL for Payments?','mgm'); ?></b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="checkbox" name="use_ssl_paymentpage" value="Y"  <?php echo (bool_from_yn($data['system_obj']->get_setting('use_ssl_paymentpage'))) ? 'checked' : '';?>/> <?php _e('Yes, make payments secure','mgm'); ?>.
				<p><div class="tips width90"><?php _e('Do you want to make your payment page secure with SSL gateway? must install SSL before continuing.','mgm'); ?></div></p>
    		</div>
		</div>
	</div>	
	<?php mgm_box_bottom();?>
	
	<?php mgm_box_top('Custom URL Settings');?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Register URL','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="register_url" value="<?php echo esc_html($data['system_obj']->get_setting('register_url')); ?>" size="120" />
				<p><div class="tips width90">
					<?php _e('Custom Register URL for regsiter and related actions. This URL is meant to be updated inside your site, you can create a Wordpress post/page and paste the page url here.<br><u><b>Tag</b></u>: <br><b>[user_register]</b> : Shows Register Form','mgm'); ?>
				</div></p>
    		</div>
		</div>
		<?php if ($data['bp_active']) {?>
		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Share Register URL with Buddypress','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><input type="checkbox" name="share_registration_url_with_bp" id="share_registration_url_with_bp" value="Y" <?php if(bool_from_yn($data['system_obj']->get_setting('share_registration_url_with_bp'))){ echo "checked='checked'";} ?>>&nbsp;<b><?php _e('Is the above Registration URL same as Buddypress Registration URL?','mgm'); ?></b></p>
    		</div>
		</div>
		<?php } ?>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Profile URL','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="profile_url" value="<?php echo esc_html($data['system_obj']->get_setting('profile_url')); ?>" size="120" />
				<p><div class="tips width90">
					<?php _e('Custom Profile URL for profile and related actions. This URL is meant to be updated inside your site, you can create a Wordpress post/page and paste the page url here.<br><u><b>Tag</b></u>: <br><b>[user_profile]</b> : Shows Profile','mgm'); ?>
				</div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('User Public Profile URL','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="userprofile_url" value="<?php echo esc_html($data['system_obj']->get_setting('userprofile_url')); ?>" size="120" />
				<p><div class="tips width90">
					<?php _e('Custom Public Profile URL for public profile and related actions.<br><u><b>Tag</b></u>: <br><b>[user_public_profile]</b> : Shows user public profile.','mgm'); ?>
				</div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Transactions URL','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="transactions_url" value="<?php echo esc_html($data['system_obj']->get_setting('transactions_url')); ?>" size="120" />
				<p><div class="tips width90">
				<?php _e('Transactions URL for redirecting user to payment success/failed page. This URL is meant to be updated inside your site, you can create a Wordpress post/page and paste the page url here.<br><u><b>Tag</b></u>: <br><b>[transactions]</b> : Shows Transaction Details<br>','mgm'); ?>
				</div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Login URL','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="login_url" value="<?php echo esc_html($data['system_obj']->get_setting('login_url')); ?>" size="120" />
				<p><div class="tips width90">
				<?php _e('Login URL for custom login page. <br/><u><b>Tag</b></u>: <br><b>[user_login]</b> : Shows Login Page<br>','mgm'); ?>
				</div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Lost Password URL','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="lostpassword_url" value="<?php echo esc_html($data['system_obj']->get_setting('lostpassword_url')); ?>" size="120" />
				<p><div class="tips width90">
				<?php _e('Lost Password URL for custom Lost Password Page. <br/><u><b>Tag</b></u>: <br><b>[user_lostpassword]</b> : Shows Lost Password Page<br>','mgm'); ?>
				</div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Membership Details URL','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="membership_details_url" value="<?php echo esc_html($data['system_obj']->get_setting('membership_details_url')); ?>" size="120" />
				<p><div class="tips width90">
				<?php _e('Membership Details URL for custom Membership Details Page. <br/><u><b>Tag</b></u>: <br><b>[membership_details]</b> : Shows Membership Details Page<br>','mgm'); ?>
				</div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Membership Contents URL','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="membership_contents_url" value="<?php echo esc_html($data['system_obj']->get_setting('membership_contents_url')); ?>" size="120" />
				<p><div class="tips width90">
				<?php _e('Membership Contents URL for custom Membership Contents Page.<br/><u><b>Tag</b></u>: <br><b>[membership_contents]</b> : Shows Membership Contents Page<br>','mgm'); ?>
				</div></p>
    		</div>
		</div>
		
	</div>	
	<?php mgm_box_bottom();?>	
		
	<?php mgm_box_top('Affiliate Settings');?>
	
	<div class="table">
  		<div class="row">
    		<div class="cell">
				<p><b>
				<?php _e('Make more money with Magic Members. You can earn 30% commission just like our other affiliates!'.
						 'Please enter your Affiliate ID below. If you don\'t have an affiliate account '. 
						 '<a href="http://www.magicmembers.com/affiliates/" target="_blank">click here</a> to create one now!','mgm'); ?>:
				</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="checkbox" name="use_affiliate_link" id="use_affiliate_link" value="Y" <?php echo (get_option('mgm_affiliate_id')) ? 'checked' : '';?>/> <?php _e('Yes, use Affiliate Link','mgm'); ?>.<br />
				<?php _e('Affiliate ID','mgm'); ?>: <input type="text" name="affiliate_id" id="affiliate_id" value="<?php echo get_option('mgm_affiliate_id'); ?>" size="5" <?php echo (!get_option('mgm_affiliate_id')) ? 'disabled' : '';?>/>
				<p><div class="tips width90"><?php _e('Affiliate Link in footer.','mgm'); ?></div></p>
    		</div>
		</div>
	</div>	
	<?php mgm_box_bottom();?>			
	
	<?php mgm_box_top('Date Settings');?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Date Ranges','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<div class="table">
			  		<div class="row">
			    		<div class="cell width10"><b><?php _e('Lower')?>:</b></div>
			    		<div class="cell">
			    			<input type="text" name="date_range_lower" value="<?php echo esc_html($data['system_obj']->get_setting('date_range_lower')); ?>" size="6" maxlength="2"/> 
							<em>- <?php _e('current year','mgm')?> (<?php echo date('Y',strtotime('- '.(int)$data['system_obj']->get_setting('date_range_lower').' YEAR'))?>)</em>
						</div>
			    	</div>
			  		<div class="row">
			    		<div class="cell width10" ><b><?php _e('Upper')?>:</b></div>
			    		<div class="cell">
							<input type="text" name="date_range_upper" value="<?php echo esc_html($data['system_obj']->get_setting('date_range_upper')); ?>" size="6" maxlength="2"/>
							<em> + <?php _e('current year','mgm')?> (<?php echo date('Y',strtotime('+ '.(int)$data['system_obj']->get_setting('date_range_upper').' YEAR'))?>)</em>
						</div>
			    	</div>
			    </div>
				<p><div class="tips width90"><?php _e('Date lower and upper range in all calendar popup.','mgm'); ?></div></p>
    		</div>
    	</div>
    	<div class="row">
			<div class="cell">
				<p><b><?php _e('Date Formats','mgm'); ?>:</b></p>
			</div>
		</div>
   		<div class="row">
    		<div class="cell">
				<div class="table">
					<div class="row">
				    	<div class="cell width10"><b><?php _e('Default')?>:</b></div>
			    		<div class="cell" style="text-align:left;">
							<input type="text" name="date_format" value="<?php echo esc_html($data['system_obj']->get_setting('date_format')); ?>" size="20" />
							<em><?php _e('e.g.','mgm')?>: <?php echo date($data['system_obj']->get_setting('date_format'))?></em>
						</div>				    		
			    	</div>
			  		<div class="row">
			    		<div class="cell width10"><b><?php _e('Long')?>:</b></div>
			    		<div class="cell" style="text-align:left;">
							<input type="text" name="date_format_long" value="<?php echo esc_html($data['system_obj']->get_setting('date_format_long')); ?>" size="20" />
							<em><?php _e('e.g.','mgm')?>: <?php echo date($data['system_obj']->get_setting('date_format_long'))?></em>
						</div>				    		
			    	</div>
			  		<div class="row">
			    		<div class="cell width10"><b><?php _e('Short')?>:</b></div>
			    		<div class="cell" style="text-align:left;">
							<input type="text" name="date_format_short" value="<?php echo esc_html($data['system_obj']->get_setting('date_format_short')); ?>" size="20" />
							<em><?php _e('e.g.','mgm')?>: <?php echo date($data['system_obj']->get_setting('date_format_short'))?></em>
						</div>				    		
			    	</div>
			    </div>
		    	<p><div class="tips width90"><?php _e('Date formats, use php date settings.','mgm'); ?></div></p>
			</div>
		</div>
	</div>	

	<?php mgm_box_bottom();?>
	
	<?php mgm_box_top('Image Settings');?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Thumbnail width','mgm')?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p>
					<input type="text" name="thumbnail_image_width" value="<?php echo esc_html($data['system_obj']->get_setting('thumbnail_image_width')); ?>" size="20" />
				</p>
				<p><div class="tips width90"><?php _e('Thumbnail size image width in pixels.','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Thumbnail height','mgm')?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p>
					<input type="text" name="thumbnail_image_height" value="<?php echo esc_html($data['system_obj']->get_setting('thumbnail_image_height')); ?>" size="20" />
				</p>
				<p><div class="tips width90"><?php _e('Thumbnail size image height in pixels.','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Medium width','mgm')?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p>
					<input type="text" name="medium_image_width" value="<?php echo esc_html($data['system_obj']->get_setting('medium_image_width')); ?>" size="20" />
				</p>
				<p><div class="tips width90"><?php _e('Medium size image width in pixels.','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Medium height','mgm')?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><input type="text" name="medium_image_height" value="<?php echo esc_html($data['system_obj']->get_setting('medium_image_height')); ?>" size="20" /></p>
				<p><div class="tips width90"><?php _e('Medium size image height in pixels.','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Image size','mgm')?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><input type="text" name="image_size_mb" value="<?php echo esc_html($data['system_obj']->get_setting('image_size_mb')); ?>" size="20" /></p>
				<p><div class="tips width90"><?php _e('Image size in MB.','mgm'); ?></div></p>				
    		</div>
		</div>
	</div>	
	<?php mgm_box_bottom();?>
	
	<?php mgm_box_top('Captcha Settings');?>	
	<?php $recaptcha = mgm_get_class('recaptcha');?>
	<div class="table">
  		<div class="row">
    		<div class="cell width25">
				<p><b><?php _e('reCaptcha Public Key','mgm')?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell width75">
				<p><input type="text" name="recaptcha_public_key" value="<?php echo esc_html($data['system_obj']->get_setting('recaptcha_public_key')); ?>" size="60" /></p>
				<p><div class="tips width90"><?php _e('reCAPTCHA Public Key. Generate your key at <br /><b>'.$recaptcha->recaptcha_get_signup_url().'</b>','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('reCaptcha Private Key','mgm')?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><input type="text" name="recaptcha_private_key" value="<?php echo esc_html($data['system_obj']->get_setting('recaptcha_private_key')); ?>" size="60" /></p>
				<p><div class="tips width90"><?php _e('reCAPTCHA Private Key. Generate your key at <br /><b>'.$recaptcha->recaptcha_get_signup_url().'</b>','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('reCAPTCHA API Server Url','mgm')?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><input type="text" name="recaptcha_api_server" value="<?php echo esc_html($data['system_obj']->get_setting('recaptcha_api_server')); ?>" size="60" /></p>
				<p><div class="tips width90"><?php _e('reCAPTCHA API Server Url.','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('reCAPTCHA API Secure Server Url','mgm')?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><input type="text" name="recaptcha_api_secure_server" value="<?php echo esc_html($data['system_obj']->get_setting('recaptcha_api_secure_server')); ?>" size="60" /></p>
				<p><div class="tips width90"><?php _e('reCAPTCHA API Secure Server Url.','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('reCAPTCHA Verify Server Url','mgm')?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><input type="text" name="recaptcha_verify_server" value="<?php echo esc_html($data['system_obj']->get_setting('recaptcha_verify_server')); ?>" size="60" /></p>	
				<p><div class="tips width90"><?php _e('reCAPTCHA Verify Server Url','mgm'); ?></div></p>
    		</div>
		</div>
		
	</div>	
	<?php mgm_box_bottom();?>
	
	<?php mgm_box_top('Redirection Setting');?>
	<div class="table">
  		<div class="row">
    		<div class="cell width30">
				<p><b><?php _e('Redirection Method','mgm')?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell width70">
				<p>
					<select name="redirection_method" class="width150px">
						<?php echo mgm_make_combo_options(array('header'=>'header','javascript'=>'javascript','meta'=>'meta'), $data['system_obj']->get_setting('redirection_method'), MGM_VALUE_ONLY)?>	
					</select>				
				</p>
				<p><div class="tips width90"><?php _e('Redirection method.<br/>Default: Wordpress wp_redirect<br/>Javascript: Javascript redirection<br/>Meta: Html Meta tag redirection','mgm'); ?></div></p>				
    		</div>
		</div>
	</div>	
	<?php mgm_box_bottom();?>
	
	<?php mgm_box_top('Google Analytics eCommerce settings');?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
				<p><input type="checkbox" name="enable_googleanalytics" id="enable_googleanalytics" value="Y" <?php if(bool_from_yn($data['system_obj']->get_setting('enable_googleanalytics'))){ echo "checked='checked'";} ?>>&nbsp;<b><?php _e('Enable Google Analytics on Thankyou Page?','mgm'); ?></b></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<div id="enable_googleanalytics_setting" class="<?php echo (bool_from_yn($data['system_obj']->get_setting('enable_googleanalytics'))) ? 'displayblock' : 'displaynone';?> paddingtop10px">
					<p><b><?php _e('Google Analytics Key')?>:</b></p>
					<input type="text" name="googleanalytics_key" value="<?php echo esc_html($data['system_obj']->get_setting('googleanalytics_key')); ?>" size="50" />
					<p><div class="tips width90"><?php _e('Google Analytics Key for your domain','mgm'); ?></div></p>						
				</div>				
    		</div>
		</div>
	</div>	
	<?php mgm_box_bottom(); ?>
	

	<?php mgm_box_top('Facebook Settings');?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
				<p><input type="checkbox" name="enable_facebook" id="enable_facebook" value="Y" <?php if(bool_from_yn($data['system_obj']->get_setting('enable_facebook'))){ echo "checked='checked'";} ?>>&nbsp;<b><?php _e('Enable Facebook login/registration?','mgm'); ?></b></p>				
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<div id="enable_facebook_setting" class="<?php echo (bool_from_yn($data['system_obj']->get_setting('enable_facebook'))) ? 'displayblock' : 'displaynone';?> paddingtop10px">
					<p><b><?php _e('Facebook API ID')?>:</b></p>
					<input type="text" name="facebook_id" value="<?php echo esc_html($data['system_obj']->get_setting('facebook_id')); ?>" size="50" />
					<p><b><?php _e('Facebook API Key')?>:</b></p>
					<input type="text" name="facebook_key" value="<?php echo esc_html($data['system_obj']->get_setting('facebook_key')); ?>" size="50" />
					<p><div class="tips width90"><?php _e('To connect your site to Facebook, you need a Facebook Application. If you have already created one, please insert your API & Secret key above.<br>Already registered? Find your keys in your <a href="http://www.facebook.com/developers/apps.php" target="_blank">Facebook Application List</a><br>Need to register?<br><ul><li>Visit the <a href="http://www.facebook.com/developers/createapp.php" target="_blank">Facebook Application Setup</a> page</li><li>Get the API information from the <a href="http://www.facebook.com/developers/apps.php" target="_blank">Facebook Application List</a></li><li>Select the application you created, then copy and paste the API key &amp; Application Secret from there.</li></ul>','mgm'); ?></div></p>
				</div>				
    		</div>
		</div>
	</div>	

	<?php mgm_box_bottom(); ?>
	
	
	<?php mgm_box_top('Extend Directory Settings');?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Directory')?>:</b></p>
				<input type="text" name="extend_dir" value="<?php echo get_option('mgm_extend_dir'); ?>" size="100" />
				<div class="tips width90"><?php _e('Extend Directory path for extended modules','mgm'); ?></div>										
			</div>
		</div>
	</div>	
	<?php mgm_box_bottom(); ?>

	<p class="submit floatleft">
		<input class="button" type="submit" name="settings_update" value="<?php _e('Save Settings','mgm') ?>" />
	</p>
	<div class="clearfix"></div>	
</form>
<script language="javascript">
	<!--
	jQuery(document).ready(function(){

		// check bind
		jQuery("#frmsetgen :checkbox[name='reminder_days_incremental']").bind('click',function(){
			jQuery("#frmsetgen :input[name='reminder_days_incremental_ranges']").attr('disabled',!jQuery(this).attr('checked'));
		});		
		
		jQuery.validator.addMethod('checkSpecialChar', function(value, element) {
			return (value).match(/^[A-Za-z0-9_,]+$/);
		}, '<?php _e('Please remove space/special characters','mgm') ?>');
		// add : form validation
		jQuery("#frmsetgen").validate({
			
			
			submitHandler: function(form) {					    					
				jQuery("#frmsetgen").ajaxSubmit({type: "POST",										  
				  dataType: 'json',			
				  iframe: false,								 
				  beforeSubmit: function(){	
				  	// show message
					mgm_show_message('#frmsetgen', {status:'running', message:'<?php _e('Processing','mgm')?>...'}, true);							
				  },
				  success: function(data){	
				  	// show message
				  	mgm_show_message('#frmsetgen', data);																			
				  }}); // end   		
				return false;											
			},
			rules:{download_slug:{required:true,checkSpecialChar:true}},	
			messages: {	},		
			errorClass: 'invalid',
			errorPlacement:function(error, element) {										
				error.insertAfter(element);
			}
		});			
		
		// affiliate link
		jQuery('#use_affiliate_link').bind('click', function(){
			jQuery('#affiliate_id').attr('disabled', !jQuery(this).attr('checked'));
		});
		
		// aws enable s3
		jQuery('#aws_enable_s3').bind('click', function(){
			if(jQuery(this).attr('checked')){	
				jQuery("#aws_enable_s3_setting :input[type='text']").attr('disabled', false).val('');
				jQuery('#aws_enable_s3_setting').fadeIn();
			}else{
				jQuery("#aws_enable_s3_setting :input[type='text']").attr('disabled', true);
				jQuery('#aws_enable_s3_setting').fadeOut();
			}
		});
		
		// aws enable qsa
		jQuery('#aws_enable_qsa').bind('click', function(){
			if(jQuery(this).attr('checked')){	
				jQuery("#aws_enable_qsa_setting :input[type='text']").attr('disabled', false).val('');
				jQuery('#aws_enable_qsa_setting').fadeIn();
			}else{
				jQuery("#aws_enable_qsa_setting :input[type='text']").attr('disabled', true);
				jQuery('#aws_enable_qsa_setting').fadeOut();
			}
		});
		
		// enable Google Analytics
		jQuery('#enable_googleanalytics').bind('click', function(){
			if(jQuery(this).attr('checked')){	
				jQuery("#enable_googleanalytics_setting :input[type='text']").attr('disabled', false).val('');
				jQuery('#enable_googleanalytics_setting').fadeIn();
			}else{
				jQuery("#enable_googleanalytics_setting :input[type='text']").attr('disabled', true);
				jQuery('#enable_googleanalytics_setting').fadeOut();
			}
		});
		
		// enable Facebook login/registration
		jQuery('#enable_facebook').bind('click', function(){
			if(jQuery(this).attr('checked')){	
				jQuery("#enable_facebook_setting :input[type='text']").attr('disabled', false).val('');
				jQuery('#enable_facebook_setting').fadeIn();
				jQuery("#enable_facebook_setting :input[type='text']").attr('disabled', false).val('');
				jQuery('#enable_facebook_setting').fadeIn();
			}else{
				jQuery("#enable_facebook_setting :input[type='text']").attr('disabled', true);
				jQuery('#enable_facebook_setting').fadeOut();
				jQuery("#enable_facebook_setting :input[type='text']").attr('disabled', true);
				jQuery('#enable_facebook_setting').fadeOut();
			}
		});		
		
		// enable/disable guest purchase
		jQuery("#frmsetgen :radio[name='enable_guest_content_purchase']").bind('click', function(){
			if(jQuery(this).val() == 'Y'){
				jQuery('#enable_guest_purchase_setting').fadeIn();
			}else{
				jQuery('#enable_guest_purchase_setting').fadeOut();
				jQuery('#enable_guest_purchase_setting :checkbox').attr('checked', false);
			}
		});
		
		// enable bind
		jQuery("#frmsetgen :checkbox[id^='enable_']").click(function(e){
			var elmid = jQuery(this).attr('id').replace('enable_','');
			// autologin
			if(elmid == 'autologin') elmid = 'autologin_redirect_url';
			if(elmid == 'guest_lockdown') elmid = 'guest_lockdown_redirect_url';
			// check
			if(jQuery(this).attr('checked')){
				jQuery('#'+elmid).attr('disabled', false).val('');
				jQuery('#'+elmid).parent('div').fadeIn();
			}else{
			// uncheck
				jQuery('#'+elmid).attr('disabled', true);
				jQuery('#'+elmid).parent('div').fadeOut();
			}
		});
		// role based menu setting alert
		jQuery("#enable_role_based_menu_loading_y").bind('click', function() {
			if(this.value == 'Y')
				alert('<?php _e('Before saving this setting, please make sure MGM Menu based capabilities have already been assigned to "administrator" and other roles', 'mgm');?>');	
		});
	});
	//-->
</script>