<!--access-->
<div id="content_access">
	<form name="frmaccss" id="frmaccss" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/contents&method=protections" method="post">	
		<?php /*?><?php mgm_box_top('Registration Control')?>
		<div class="table" id="regctl-table">
	  		<div class="row">
	    		<div class="cell">
					<p><?php _e('Would you like to use modified registration process?','mgm'); ?></p>
	    		</div>
	    		<div class="cell">
					<b><input type="radio" name="modified_registration" value="Y" <?php if ($data['system_obj']->setting['modified_registration'] == 'Y') { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm')?></b>
					<b><input type="radio" name="modified_registration" value="N"  <?php if ($data['system_obj']->setting['modified_registration'] == 'N') { echo 'checked="true"'; } ?>/> <?php _e('No','mgm')?></b>
	    		</div>
			</div>
		</div>	
		<?php mgm_box_bottom()?><?php */?>
		
		<?php mgm_box_top('Content Protection Settings')?>	

		<div class="table" id="fllctpset-table">	  		
	  		<div class="row">
	  			<div class="cell width20">
					<strong><?php _e('Content Protection','mgm'); ?>:</strong>
				</div>
			</div>	
			<div class="row">
	  			<div class="cell">
					<p><?php _e('Would you like to hide all of your content?','mgm'); ?></p>
					<input type="radio" name="content_protection" value="full" <?php if ($data['system_obj']->setting['content_protection'] == 'full') { echo 'checked="true"'; } ?>/> <b><?php _e('FULL','mgm')?></b>
					<input type="radio" name="content_protection" value="partly"  <?php if ($data['system_obj']->setting['content_protection'] == 'partly') { echo 'checked="true"'; } ?>/> <b><?php _e('PARTLY','mgm')?></b>
					<input type="radio" name="content_protection" value="none"  <?php if ($data['system_obj']->setting['content_protection'] == 'none') { echo 'checked="true"'; } ?>/> <b><?php _e('NONE','mgm')?></b>
					<div id="content_protection_partly_settings" class="<?php echo ($data['system_obj']->setting['content_protection'] == 'partly') ? '' :'displaynone'; ?> mgm_content_protection_pading">
						<?php _e('Word limit for public access','mgm'); ?>: <input type="text" size="10" value="<?php echo $data['system_obj']->setting['public_content_words']?>" name="public_content_words"/> 
					</div>
					<div id="content_protection_allow_html_settings" class="<?php echo in_array($data['system_obj']->setting['content_protection'], array('partly')) ? '' :'displaynone'; ?> mgm_content_protection_pading">
						<span><?php _e('Allow HTML or Text in partly protected content','mgm')?>:</span><br /><br />
						<input type="radio" name="content_protection_allow_html" value="Y" <?php if ($data['system_obj']->setting['content_protection_allow_html'] == 'Y') { echo 'checked="true"'; } ?>/> <b><?php _e('Allow HTML','mgm')?></b>
						<input type="radio" name="content_protection_allow_html" value="N" <?php if ($data['system_obj']->setting['content_protection_allow_html'] == 'N') { echo 'checked="true"'; } ?>/> <b><?php _e('Text Only','mgm')?></b>
					</div>
				</div>			
			</div>
	  		<div class="row">
	  			<div class="cell">
					<div class="tips width97">
						<?php _e('<p><strong>Protect your contents</strong> <br />'.
						         '<u>FULL</u> = Protects contents automatically. All Contents will be protected and users have to login before viewing. [private] tags added manually will be honored.<br /><br>'. 
								 '<u>PARTLY</u> = Protects contents automatically. Part of the content will be free and rest will be viewable after login. [private] tags added manually will be honored.<br><br>'.
								 '<u>NONE</u> =  No protection will be applied. Any settings in post/page setup and [private] tags added manually will be disregarded unless post/page is set as<br> Purchasable.<br><br>'.
								 '<b>Purchasable Post will require login (unless guest purchase is enabled) irrespective of Content Protection settings</b>.</p>','mgm'); ?>
					</div>
				</div>			
			</div>
	  		<div class="row">
	  			<div class="cell">
					<b><?php _e('Extended Content Protection:','mgm'); ?></b><br /><br />
					<?php _e('Hide Content By Membership?','mgm'); ?><br /><br />
					<b><input type="radio" name="content_hide_by_membership" value="Y" <?php if ($data['system_obj']->setting['content_hide_by_membership'] == 'Y') { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm')?></b>
					<b><input type="radio" name="content_hide_by_membership" value="N"  <?php if ($data['system_obj']->setting['content_hide_by_membership'] == 'N') { echo 'checked="true"'; } ?>/> <?php _e('No','mgm')?></b>
				</div>			
			</div>
	  		<div class="row">
	  			<div class="cell">
					<div class="tips width97 paddingtop10px">
						<p><?php _e('Controls whether posts/page are hidden by current user membership type.','mgm'); ?></p>
					</div>
				</div>			
			</div>	  		
			<div class="row">
	  			<div class="cell">
					<b><?php _e('Excerpt Protection:','mgm'); ?></b><br /><br />
					<?php _e('Enable Excerpt Protection?','mgm'); ?><br><br />
					<b><input type="radio" name="enable_excerpt_protection" value="Y" <?php if ($data['system_obj']->setting['enable_excerpt_protection'] == 'Y') { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm')?></b>
					<b><input type="radio" name="enable_excerpt_protection" value="N"  <?php if ($data['system_obj']->setting['enable_excerpt_protection'] == 'N') { echo 'checked="true"'; } ?>/> <?php _e('No','mgm')?></b>
				</div>			
			</div>
	  		<div class="row">
	  			<div class="cell">
					<div class="tips width97 paddingtop10px">
						<p><?php _e('Whether excerpt should be protected or not.','mgm'); ?></p>
					</div>
				</div>			
			</div>
			<div class="row">
	  			<div class="cell">
					<b><?php _e('Excerpt usage in theme:','mgm'); ?></b><br /><br />
					<?php _e('Does theme use excerpt functions?','mgm'); ?><br /><br />
					<b><input type="radio" name="using_the_excerpt_in_theme" value="Y" <?php if ($data['system_obj']->setting['using_the_excerpt_in_theme'] == 'Y') { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm')?></b>
					<b><input type="radio" name="using_the_excerpt_in_theme" value="N"  <?php if ($data['system_obj']->setting['using_the_excerpt_in_theme'] == 'N') { echo 'checked="true"'; } ?>/> <?php _e('No','mgm')?></b>
				</div>			
			</div>
			<div class="row">
	  			<div class="cell">
					<div class="tips width97 paddingtop10px">
						<p><?php _e('Whether the_excerpt() function is being used in theme. Select "No" for regular themes. This helps show content protection messages properly.','mgm'); ?></p>
					</div>
				</div>			
			</div>
			<div class="row">
	  			<div class="cell">
					<b><?php _e('Comments protection:','mgm'); ?></b><br /><br />
					<?php _e('Hide comments by membership ?','mgm'); ?><br /><br />
					<b><input type="radio" name="enable_comments_protection" value="Y" <?php if ($data['system_obj']->setting['enable_comments_protection'] == 'Y') { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm')?></b>
					<b><input type="radio" name="enable_comments_protection" value="N"  <?php if ($data['system_obj']->setting['enable_comments_protection'] == 'N') { echo 'checked="true"'; } ?>/> <?php _e('No','mgm')?></b>
				</div>			
			</div>
	  		<div class="row">
	  			<div class="cell">
					<div class="tips width97 paddingtop10px">
						<p><?php _e('Controls whether comments are hidden by current user membership type.','mgm'); ?></p>
					</div>
				</div>			
			</div>			
		</div>		
		<?php mgm_box_bottom()?>
		
		<?php mgm_box_top('Private Tag Redirection Settings')?>
		
		<div class="table" id="privtagredir-table">
	  		<div class="row">
	    		<div class="cell">
					<p><?php _e('You can use the following options to override the no access messages normally shown between [private][/private] tags.','mgm'); ?></p>
	    		</div>
			</div>
	  		<div class="row">
	    		<div class="cell width30">
					<b><?php _e('No access URL for logged in users','mgm'); ?>:</b>
	    		</div>
			</div>
			<div class="row">	
	    		<div class="cell">
					<input type="text" size="90" name="no_access_redirect_loggedin_users" value="<?php echo esc_html($data['system_obj']->setting['no_access_redirect_loggedin_users']); ?>" /> <br />
					<div class="tips">(<?php _e("Don't forget to type HTTP if it's an external link",'mgm'); ?>)</div>
	    		</div>
			</div>
	  		<div class="row">
	    		<div class="cell width30">
					<b><?php _e('No access URL for logged out users','mgm'); ?>:</b>
	    		</div>
			</div>
			<div class="row">		
	    		<div class="cell">
					<input type="text" size="90" name="no_access_redirect_loggedout_users" value="<?php echo esc_html($data['system_obj']->setting['no_access_redirect_loggedout_users']); ?>" /><br />
					<div class="tips">(<?php _e("Don't forget to type HTTP if it's an external link",'mgm'); ?>)</div>
	    		</div>
			</div>
	  		<div class="row">
	    		<div class="cell width30">
					<b><?php _e('Redirect users on homepage?','mgm'); ?>:</b>
	    		</div>
			</div>
			<div class="row">		
	    		<div class="cell">
					<b><input type="radio" name="redirect_on_homepage" value="Y" <?php if ($data['system_obj']->setting['redirect_on_homepage'] == 'Y') { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm') ?></b>
					<b><input type="radio" name="redirect_on_homepage" value="N"  <?php if ($data['system_obj']->setting['redirect_on_homepage'] == 'N') { echo 'checked="true"'; } ?>/> <?php _e('No','mgm') ?></b>
	    		</div>
			</div>
		</div>		

		<?php mgm_box_bottom()?>
		
		<?php mgm_box_top('RSS Token Settings')?>
		<div class="table" id="regctl-table">
	  		<div class="row">
	    		<div class="cell">
					<b><?php _e('Activate RSS Token - If selected "No", full content view in RSS feeds will be disabled for members only content','mgm'); ?>:</b>
	    		</div>
			</div>
			<div class="row">		
	    		<div class="cell">
					<b><input type="radio" name="use_rss_token" value="Y" <?php if ($data['system_obj']->setting['use_rss_token'] == 'Y') { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm') ?></b>
					<b><input type="radio" name="use_rss_token" value="N" <?php if ($data['system_obj']->setting['use_rss_token'] == 'N') { echo 'checked="true"'; } ?>/> <?php _e('No','mgm') ?></b>
	    		</div>
			</div>
		</div>			
		<?php mgm_box_bottom()?>
		
		<p class="submit">
			<input class="button" type="submit" name="update" value="<?php _e('Save','mgm') ?>" />
		</p>
	</form>	
</div>	
<script language="javascript">
	<!--
	// onready
	jQuery(document).ready(function(){
		// add : form validation
		jQuery("#frmaccss").validate({
			submitHandler: function(form) {   
				jQuery("#frmaccss").ajaxSubmit({type: "POST",
				  url: 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/contents&method=protections',
				  dataType: 'json',			
				  iframe: false,								 
				  beforeSubmit: function(){	
				  	// show message
					mgm_show_message('#content_access', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);	
				  },
				  success: function(data){	
					// message																				
					mgm_show_message('#content_access', data);																	
				  }});// end 
				  // stop submit
				  return false;															
			}
		});			
		
		// bind content protection chnage
		jQuery("#frmaccss :radio[name='content_protection']").bind('click',function(){
			// content_protection_partly_settings
			if(jQuery(this).val() == 'partly'){
				jQuery('#content_protection_partly_settings').slideDown();
				jQuery('#content_protection_allow_html_settings').slideDown();
			}else{
				jQuery('#content_protection_partly_settings').hide();
				jQuery('#content_protection_allow_html_settings').hide();
			}			
		});				  
	});	
	//-->
</script>