<!--messages-->
<?php header('Content-Type: text/html; charset=UTF-8');?>
<form name="frmmessages" id="frmmessages" method="post" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/settings&method=messages">
	<?php /* issue#: 353 (subscription introduction and Terms and conditions are reading from custom fields)*/?>
	<?php mgm_box_top('Main Messages');?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Subscription Introduction','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[subs_intro]" id="setting_subs_intro" class="height200px width820px"><?php echo mgm_print_template_content('subs_intro'); ?></textarea>
				<p><div class="tips"><?php _e('This is the text which will appear before the subscription options. HTML format is allowed.','mgm'); ?></div></p>    		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Terms &amp; Conditions','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[tos]" id="setting_tos" class="height200px width820px"><?php echo mgm_print_template_content('tos'); ?></textarea>
				<p><div class="tips"><?php _e('This is your Terms &amp; Conditions text and it will appear on the registration page. Users have to agree your Terms &amp; Conditions in order to register.','mgm'); ?></div></p>    		
			</div>
		</div>

	</div>	

	<?php mgm_box_bottom();?><br />
	<?php mgm_box_top('Post Messages');?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Post Shortcodes','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<div>
					<ul>
						<li><strong>[[purchase_cost]]</strong> = <?php _e('Displays the cost and currency of a purchasable post.','mgm'); ?></li>
						<li><strong>[[register]]</strong> = <?php _e('Displays the register form.','mgm'); ?></li>
						<li><strong>[[login_register]]</strong> = <?php _e('Displays the login or register form.','mgm'); ?></li>
						<li><strong>[[login_register_links]]</strong> = <?php _e('Displays the links for login and register.','mgm'); ?></li>
						<li><strong>[[login_link]]</strong> = <?php _e('Displays only the Login link.','mgm'); ?></li>
						<li><strong>[[facebook_login_button]]</strong> = <?php _e('Displays only the Facebook login button.','mgm'); ?></li>
						<li><strong>[[register_link]]</strong> = <?php _e('Displays only the Register link.','mgm'); ?></li>
						<li><strong>[[membership_types]]</strong> = <?php _e('Displays a list of membership levels that can see the post/page.','mgm'); ?></li>
						<li><strong>[[duration]]</strong> = <?php _e('Displays the number of days the user will have access to the content for.','mgm'); ?></li>								<li><strong>[[currency_sign]]</strong> = <?php _e('Displays currency symbol.','mgm'); ?></li>				
						<li><strong>[[name]]</strong> = <?php _e('Displays user name.','mgm'); ?></li>
						<li><strong>[[username]]</strong> = <?php _e('Displays user username.','mgm'); ?></li>
					</ul>
				</div>
				<p>
					<div class="tips width95"><?php _e('In this section you can change how the messages will display inside the posts. You are free to use HTML coding and special tags.','mgm'); ?>:</div>
				</p>    		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<div>
					<ul>
						<li><strong>[[purchase_cost]]</strong> = <?php _e('Displays the cost and currency of a purchasable post.','mgm'); ?></li>
						<li><strong>[[register]]</strong> = <?php _e('Displays the register form.','mgm'); ?></li>
						<li><strong>[[login_register]]</strong> = <?php _e('Displays the login or register form.','mgm'); ?></li>
						<li><strong>[[login_register_links]]</strong> = <?php _e('Displays the links for login and register.','mgm'); ?></li>
						<li><strong>[[login_link]]</strong> = <?php _e('Displays only the Login link.','mgm'); ?></li>
						<li><strong>[[facebook_login_button]]</strong> = <?php _e('Displays only the Facebook login button.','mgm'); ?></li>						
						<li><strong>[[register_link]]</strong> = <?php _e('Displays only the Register link.','mgm'); ?></li>
						<li><strong>[[membership_types]]</strong> = <?php _e('Displays a list of membership levels that can see the post/page.','mgm'); ?></li>
						<li><strong>[[duration]]</strong> = <?php _e('Displays the number of days the user will have access to the content for.','mgm'); ?></li>								<li><strong>[[currency_sign]]</strong> = <?php _e('Displays currency symbol.','mgm'); ?></li>								
						<li><strong>[[name]]</strong> = <?php _e('Displays user name.','mgm'); ?></li>
						<li><strong>[[username]]</strong> = <?php _e('Displays user username.','mgm'); ?></li>
					</ul>
				</div>
				<p>
					<div class="tips width95"><?php _e('In this section you can change how the messages will display inside the posts. You are free to use HTML coding and special tags.','mgm'); ?>:</div>
				</p>    		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Private Text [before login]','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[private_text]" id="setting_private_text" class="height200px width820px"><?php echo mgm_print_template_content('private_text'); ?></textarea>
				<p>
					<div class="tips width95">
						<?php _e('The following message replaces the text inside the [private]...[/private] tags in your posts and pages when the viewer is not logged in or do not have the right account.','mgm'); ?>
					</div>
				</p>	    		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	   			<p><b><?php _e('Private Text [after login, but no access for membership type]','mgm'); ?>:</b></p> 		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[private_text_no_access]" id="setting_private_text_no_access" class="height200px width820px"><?php echo mgm_print_template_content('private_text_no_access'); ?></textarea>
				<p><div class="tips width95"><?php _e('The following message replaces the text inside the [private]...[/private] tags in your posts and pages when the viewer is logged in but is not allowed to see the rest of the post.','mgm'); ?></div></p>	    		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Private Text [after login, purchasable post]','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[private_text_purchasable]" id="setting_private_text_purchasable" class="height200px width820px"><?php echo mgm_print_template_content('private_text_purchasable'); ?></textarea>
				<p><div class="tips width95"><?php _e('The following message replaces the text inside the [private]...[/private] tags for your purchasable posts when the viewer is logged out or has not purchased the post yet.','mgm'); ?></div></p>	    		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Private Text [before login, purchasable post]','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[private_text_purchasable_login]" id="setting_private_text_purchasable_login" class="height200px width820px"><?php echo mgm_print_template_content('private_text_purchasable_login'); ?></textarea>
				<p>
					<div class="tips width95">
						<?php _e('The following message replaces the text inside the [private]...[/private] tags for your purchasable posts when the viewer is not logged in.<br> Use [purchase_options] for guest purchase.','mgm'); ?>
					</div>
				</p>	    		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Private Text [before login, purchasable pack]','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[private_text_purchasable_pack_login]" id="setting_private_text_purchasable_pack_login" class="height200px width820px"><?php echo mgm_print_template_content('private_text_purchasable_pack_login'); ?></textarea>
				<p>
					<div class="tips width95">
						<?php _e('The following message replaces the text inside the [private]...[/private] tags for your purchasable pack when the viewer is not logged in.<br> Use [purchase_options] for guest purchase.','mgm'); ?>
					</div>
				</p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Private Text [post delay, no access for membership type]','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[private_text_postdelay_no_access]" id="setting_private_text_postdelay_no_access" class="height200px width820px"><?php echo mgm_print_template_content('private_text_postdelay_no_access'); ?></textarea>
				<p>
					<div class="tips width95">
						<?php _e('The following message replaces the text inside the [private postdelay=member:10,silver:20,gold:10]...[/private] tags for your purchasable pack having post dealy when the viewer is visted the post','mgm'); ?>
					</div>
				</p>
			</div>
		</div>		
  		<div class="row">
    		<div class="cell">
	   			<p><b><?php _e('Guest Purchase Text [before login, guest post purchase form ]','mgm'); ?>:</b></p> 		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[text_guest_purchase_pre_button]" id="setting_text_guest_purchase_pre_button" class="height200px width820px"><?php echo mgm_print_template_content('text_guest_purchase_pre_button'); ?></textarea>
				<p>
					<div class="tips width95">
						<?php _e('The following message template updates before buy message for guest purchase form.','mgm'); ?>
					</div>
				</p>	    		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	   			<p><b><?php _e('Register & purchase Text [before login, guest register & purchase post form ]','mgm'); ?>:</b></p> 		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[text_guest_purchase_pre_register]" id="setting_text_guest_purchase_pre_register" class="height200px width820px"><?php echo mgm_print_template_content('text_guest_purchase_pre_register'); ?></textarea>
				<p>
					<div class="tips width95">
						<?php _e('The following message template updates before buy message for guest post register & purchase form.','mgm'); ?>
					</div>
				</p>
			</div>
		</div>
	</div>

	<?php mgm_box_bottom();?>
	
	<?php mgm_box_top('Error Messages');?>
		<?php _e('You will be able to configure error messages in the following sections. If you want to output your user\'s username just use the tag <strong>[[USERNAME]]</strong> .','mgm') ?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Inactive Account','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<textarea name="setting[login_errmsg_null]" id="setting_login_errmsg_null" class="height200px width820px"><?php echo mgm_print_template_content('login_errmsg_null'); ?></textarea>
				<p><div class="tips width95"><?php _e('This error message is shown to the users if they are not subscribed yet or in case their account is inactive for other reasons.','mgm'); ?></div></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Subscription Expired','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<textarea name="setting[login_errmsg_expired]" id="setting_login_errmsg_expired" class="height200px width820px"><?php echo mgm_print_template_content('login_errmsg_expired'); ?></textarea>
				<p><div class="tips width95"><?php _e('When a user\'s subscription expires and the user attempt to login, the following message will appear.','mgm'); ?></div></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Subscription Expired','mgm'); ?> </b> <i><?php _e('(if mebership duration date range )','mgm'); ?></i> <b> : </b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<textarea name="setting[login_errmsg_date_range]" id="setting_login_errmsg_date_range" class="height200px width820px"><?php echo mgm_print_template_content('login_errmsg_date_range'); ?></textarea>
				<p><div class="tips width95"><?php _e('When a user\'s subscription expires and the user attempt to login, the following message will appear (if mebership duration date range ).','mgm'); ?></div></p>
			</div>
		</div>
		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Trial Expired','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<textarea name="setting[login_errmsg_trial_expired]" id="setting_login_errmsg_trial_expired" class="height200px width820px"><?php echo mgm_print_template_content('login_errmsg_trial_expired'); ?></textarea>
				<p><div class="tips width95"><?php _e('When a user\'s trial account expires and the user attempt to login, the following message will appear.','mgm'); ?></div></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Subscription Payment Pending','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[login_errmsg_pending]" id="setting_login_errmsg_pending" class="height200px width820px"><?php echo mgm_print_template_content('login_errmsg_pending'); ?></textarea>
				<p><div class="tips width95"><?php _e('This error message is shown to the users only if their subscription payment is pending.','mgm'); ?></div></p>	    		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Unknown Error in login','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[login_errmsg_default]" id="setting_login_errmsg_default" class="height200px width820px"><?php echo mgm_print_template_content('login_errmsg_default'); ?></textarea>
				<p><div class="tips width95"><?php _e('This error message is shown when login fails for an unexpacted reason. This should not occur in case the system works properly.','mgm'); ?></div></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Subscription cancelled','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[login_errmsg_cancelled]" id="setting_login_errmsg_cancelled" class="height200px width820px"><?php echo mgm_print_template_content('login_errmsg_cancelled'); ?></textarea>
				<p><div class="tips width95"><?php _e('This error message is shown when  subscription has been cancelled.','mgm'); ?></div></p>
			</div>
		</div>	
 	</div>
	<?php mgm_box_bottom();?>
	
	<?php mgm_box_top('Misc. Message Templates');?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Membership Pack Description Template','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[pack_desc_template]" id="setting_pack_desc_template" class="height200px width820px"><?php echo mgm_print_template_content('pack_desc_template','templates'); ?></textarea>
				<p><div class="tips width95"><?php _e(' When the packs are shown to the user they are placed in a certain format (eg: Member - 5 USD per 3 Months), this allows you to change it using any or all of the following hooks: [membership_type], [cost], [currency], [duration], [duration_period]. If your membership packs are a recurring payment and you have limited the number then you can use [num_cycles] below to indicate the number of payments. If you would like to use a Paypal trial then indicate this in the string using [trial_cost], [trial_duration], [trial_duration_period] [description]. Encapsulate any trial specific parts of the string in [if_trial_on][/if_trial_on] and for those that arent using a trial it\'s contents will be removed.','mgm'); ?></div></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Purchasable Post Pack Template','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[ppp_pack_template]" id="setting_ppp_pack_template" class="height200px width820px"><?php echo mgm_print_template_content('ppp_pack_template','templates'); ?></textarea>
				<p><div class="tips width95"><?php _e('When you use [payperpost_pack#num] within a post or page the following template will be called and populated. Use the following hooks and any html you like to create your design: [pack_name] [pack_cost] [pack_currency] [pack_description] [pack_posts].','mgm'); ?></div></p>
			</div>
		</div>	
		
		<!-- issue #988 -->
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Membership Pack Description for Life time Template','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[pack_desc_lifetime_template]" id="setting_pack_desc_lifetime_template" class="height200px width820px"><?php echo mgm_print_template_content('pack_desc_lifetime_template','templates'); ?></textarea>
				<p><div class="tips width95">
				<?php _e('When the pack is shown to the user they are placed in a certain format (eg: Member - 5 USD for Lifetime).','mgm'); ?>
				</div></p>
			</div>
		</div>
		<!-- issue #1649 -->
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Membership Pack Description for Date Range Template','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[pack_desc_date_range_template]" id="setting_pack_desc_date_range_template" class="height200px width820px"><?php echo mgm_print_template_content('pack_desc_date_range_template','templates'); ?></textarea>
				<p><div class="tips width95">
				<?php _e('When the pack is shown to the user they are placed in a certain format (eg: Member - 1.00 USD starts from 01/01/2014 to 10/01/2014).','mgm'); ?>
				</div></p>
			</div>
		</div>		
	</div>	

	<?php mgm_box_bottom();?>	
	
	<?php mgm_box_top('Payment Messages');?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Payment Success Title','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" size="100" name="setting[payment_success_title]" id="setting_payment_success_title" value="<?php echo strip_tags(mgm_print_template_content('payment_success_title')); ?>">
				<p><div class="tips"><?php _e('Payment success title, displayed after successful payments.','mgm'); ?></div></p>
				<?php /*?><a href="javascript:mgm_toggle_img('payment_success_title_default')" id="payment_success_title_default_trig" class="close"><?php _e('Show Default','mgm')?></a>
				<div id="payment_success_title_default" class="displaynone">
					<div class="tips_blue"><?php echo strip_tags(mgm_get_template_default('payment_success_title')); ?></div>
				</div><?php */?>		    		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Payment Success Message','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[payment_success_message]" id="setting_payment_success_message" class="height200px width820px"><?php echo mgm_print_template_content('payment_success_message'); ?></textarea>
				<p><div class="tips"><?php _e('Payment success message, displayed after successful payments. HTML format is allowed.','mgm'); ?></div></p>	    		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Payment Failed Title','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" size="100" name="setting[payment_failed_title]" id="setting_payment_failed_title" value="<?php echo strip_tags(mgm_print_template_content('payment_failed_title')); ?>">
				<p><div class="tips"><?php _e('Payment failed title, displayed after failed payments.','mgm'); ?></div></p>	    		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Payment Failed Message','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[payment_failed_message]" id="setting_payment_failed_message" class="height200px width820px"><?php echo mgm_print_template_content('payment_failed_message'); ?></textarea>
				<p><div class="tips"><?php _e('Payment failed message, displayed after failed payments. HTML format is allowed.','mgm'); ?></div></p>    		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		
			</div>
		</div>
		
	</div>
	<div>
		<input type="checkbox" name="apply_update_to_modules" value="Y" /> <b><?php _e('After update, apply the changes to all payment modules.','mgm')?></b><br />
		<em class="mgm_color_red marginleft15px"><?php _e('WARNING!, there is no going back, all modules will be updated.','mgm')?></em>
	</div>
	<?php mgm_box_bottom();?>
	
	<?php mgm_box_top('Message Templates');?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Private Text Template','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[private_text_template]" id="setting_private_text_template" class="height200px width820px"><?php echo mgm_print_template_content('private_text_template', 'templates'); ?></textarea>
				<p><div class="tips"><?php _e('Wrapper template for private text messages.','mgm'); ?></div></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Register Form Row Template','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[register_form_row_template]" id="setting_register_form_row_template" class="height200px width820px"><?php echo mgm_print_template_content('register_form_row_template', 'templates'); ?></textarea>
				<p><div class="tips"><?php _e('Template for register form field row.','mgm'); ?></div></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Register Form Autoresponder Row Template','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[register_form_row_autoresponder_template]" id="setting_register_form_row_autoresponder_template" class="height200px width820px"><?php echo mgm_print_template_content('register_form_row_autoresponder_template', 'templates'); ?></textarea>
				<p><div class="tips"><?php _e('Template for register form autoresponder field row.','mgm'); ?></div></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
	    		<p><b><?php _e('Profile Form Row Template','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<textarea name="setting[profile_form_row_template]" id="setting_profile_form_row_template" class="height200px width820px"><?php echo mgm_print_template_content('profile_form_row_template', 'templates'); ?></textarea>
				<p><div class="tips"><?php _e('Template for user profile form field row.','mgm'); ?></div></p>
			</div>
		</div>
	</div>	
	<?php mgm_box_bottom(); ?>
	<p class="submit">
		<input class="button" type="submit" name="msgs_update" value="<?php _e('Save Messages','mgm'); ?>"/>
	</p>
</form>
<script language="javascript">
<!--
	jQuery(document).ready(function(){
		var textfields_exclude = ['setting_private_text_template','setting_register_form_row_template','setting_register_form_row_autoresponder_template','setting_profile_form_row_template'];
		// editor
		jQuery("#frmmessages textarea[id]").each(function(){			
			if(-1 == (jQuery.inArray( jQuery(this).attr('id') , textfields_exclude ))) {	
				new nicEditor({fullPanel : true, iconsPath: '<?php echo MGM_ASSETS_URL?>js/nicedit/nicEditorIcons.gif'}).panelInstance(jQuery(this).attr('id')); 			
			}
		});
		// add : form validation
		jQuery("#frmmessages").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmmessages").ajaxSubmit({type: "POST",										  
				  dataType: 'json',		
				  iframe: false,		
				  beforeSerialize: function($form) { 					
					// only on IE
					if(jQuery.browser.msie){
						jQuery($form).find("textarea[id]").each(function(){	
							//issue #997	
							if(-1 == (jQuery.inArray( jQuery(this).attr('id') , textfields_exclude ))) {
								jQuery(this).val(nicEditors.findEditor(jQuery(this).attr('id')).getContent()); 
							}
						});										
					}
				  },		
				  beforeSubmit: function(){	
				  	// show message
					mgm_show_message('#frmmessages', {status:'running', message:'<?php _e('Processing','mgm')?>...'});							
					// focus
					jQuery.scrollTo('#frmmessages',400);	
				  },
				  success: function(data){	
					// message																				
					mgm_show_message('#frmmessages', data);																					
				  }}); // end   		
				return false;											
			},			
			errorClass: 'invalid'
		});							  
	});	
//-->
</script>		