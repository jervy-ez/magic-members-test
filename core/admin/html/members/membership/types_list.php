<?php 
// membership types
//mgm_pr($data['membership_types']);
// loop
foreach($data['membership_types'] as $membership_type):?>	

	<div class="row <?php echo ($alt = ($alt=='') ? 'alternate': '');?> brBottom" id="row-<?php echo $membership_type['code']; ?>">		
		<div class="cell width40">	
			<div class="floatleft">
				<?php if(in_array($membership_type['code'],array('guest', 'trial', 'free'))):?>
				<b><?php echo esc_html(mgm_stripslashes_deep($membership_type['name']));?></b>
				<?php else:?>
				<input type="text" name="membership_type_names[<?php echo $membership_type['code']?>]" size="30" maxlength="250" value="<?php echo esc_html(mgm_stripslashes_deep($membership_type['name']));?>" />
				<?php endif;?>
			</div>
			<div class="floatright">
				<a href="javascript:mgm_toggle_mt_advanced('adv-<?php echo $membership_type['code']; ?>')" id="adv-<?php echo $membership_type['code']; ?>-trig" title="<?php _e('Advanced Settings','mgm')?>">
					<img src="<?php echo MGM_ASSETS_URL ?>images/icons/plus.png" />
				</a>
			</div>
			<div class="clearfix"></div>			
		</div>
		<div class="cell width60">	
			<?php 
			if(in_array($membership_type['code'], array('free','trial','guest'))):
				echo sprintf('<span class="mgm_proxy_not_installed">%s</span>', __('System defined.','mgm'));
			else:?>
			<input type="checkbox" name="remove_membership_type[]" value="<?php echo $membership_type['code']?>" />
			<?php _e('Delete and move this membership type\'s members to ','mgm') ?><br />
			<select name="move_membership_type_to[<?php echo $membership_type['code']?>]" class="width40" disabled="disabled">
				<option value="none">--none--</option>
				<?php echo mgm_make_combo_options($data['membership_types_combo'], '', MGM_KEY_VALUE, array('guest', 'trial', $membership_type['code']));?>
			</select> 
			<?php endif;?><br /><br /> 			
			<?php $login_redirect_url = strlen($membership_type['login_redirect']) > 7 ? $membership_type['login_redirect'] : '';?>
			<input type="checkbox" name="update_login_redirect_url[]" value="<?php echo $membership_type['code']?>" <?php echo !empty($login_redirect_url) ? 'checked' : ''?>/>
			<?php _e('Login Redirect URL:','mgm') ?><br /> 	
			<input type="text" name="login_redirect_url[<?php echo $membership_type['code']?>]" size="100" maxlength="1000" value="<?php echo $login_redirect_url?>" <?php echo empty($login_redirect_url) ? 'disabled="disabled"' : ''?>/><br />			
			<?php $logout_redirect_url = strlen($membership_type['logout_redirect']) > 7 ? $membership_type['logout_redirect'] : '';?>
			<input type="checkbox" name="update_logout_redirect_url[]" value="<?php echo $membership_type['code']?>" <?php echo !empty($logout_redirect_url) ? 'checked' : ''?>/>
			<?php _e('Logout Redirect URL:','mgm') ?><br /> 	
			<input type="text" name="logout_redirect_url[<?php echo $membership_type['code']?>]" size="100" maxlength="1000" value="<?php echo $logout_redirect_url?>" <?php echo empty($logout_redirect_url) ? 'disabled="disabled"' : ''?>/>
		</div>
	</div>
	<div class="row <?php echo $alt ;?> displaynone" id="adv-<?php echo $membership_type['code']; ?>">		
		<div class="cell">	
			<?php $membership_enc = base64_encode($membership_type['code']);?>
			<div class="padding10px">	
				<div class="table widefatDiv width100">
					<div class="row headrow">		
						<div class="cell theadDivCell">
							<b><?php _e('Subscription Packages','mgm')?></b>
						</div>
					</div>
					<div class="row">		
						<div class="cell width125px">
							<?php _e('Custom URL','mgm')?>
						</div>
						<div class="cell width10px">:</div>
						<div class="cell">
							<?php echo mgm_get_custom_url('register',false,array('membership'=>$membership_enc));?>
						</div>
					</div>
					<div class="row">		
						<div class="cell width125px">
							<?php _e('Wordpress URL','mgm')?>
						</div>
						<div class="cell width10px">:</div>
						<div class="cell">
							<?php echo mgm_get_custom_url('register',true,array('membership'=>$membership_enc));?>
						</div>
					</div>
					<div class="row">		
						<div class="cell width125px"><?php _e('Tag','mgm')?></div>
						<div class="cell width10px">:</div>
						<div class="cell">
							<?php echo sprintf('[user_register membership=%s]',$membership_type['code']);?>
						</div>
					</div>
				</div>				
			</div>
		</div>
	</div>
<?php endforeach; unset($membership_types_combo);?>	