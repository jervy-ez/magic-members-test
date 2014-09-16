	<?php if(count($data['users'])==0):?>
	<tr class="<?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
		<td colspan="7" align="center">
			<?php _e('No members','mgm')?>					 					
		</td>
	</tr>
	<?php else: 
	// packs
	$s_packs           = mgm_get_class('subscription_packs');	
	$m_types           = mgm_get_class('membership_types');	
	$duration_exprs    = $s_packs->get_duration_exprs();// not used	
	$date_format       = mgm_get_date_format('date_format');
	$date_format_time  = mgm_get_date_format('date_format_time');	
	$email_as_username = bool_from_yn(mgm_get_setting('enable_email_as_username')); 
	// loop users		
	foreach($data['users'] as $user):
		// user object
		$user = get_userdata($user->ID);	
		// mgm member object
		$member = mgm_get_member($user->ID);
		// pack desc, issue #: 509					
		if (strtolower($member->membership_type) == 'guest'):
			$pack_desc = __('N/A','mgm');
		else:
			// member data
			$currency        = esc_html($member->currency);
			$amount          = esc_html($member->amount);						
			$duration        = esc_html($member->duration);
			$duration_type   = $member->duration_type;   
			$membership_type = esc_html($member->membership_type);						
			$num_cycles		 = (isset($member->active_num_cycles)) ? $member->active_num_cycles : null;		
			// pack
			$pack_id         = (int)$member->pack_id;	
			// pack desc
			$pack_desc       = $pack_error = '';			
			// get pack desc
			if((int)$pack_id>0):	
				// check pack exists																			
				if($pack = $s_packs->get_pack($pack_id)):
					// update pack with mgm_member vars:
					$pack['duration'] 		= $duration;
					$pack['duration_type'] 	= $duration_type;
					$pack['cost'] 			= $amount;
					$pack['membership_type']= $membership_type;
					// num cycles
					if(!is_null($num_cycles)) $pack['num_cycles'] = $num_cycles;								
					// set pack desc
					$pack_desc = $s_packs->get_pack_desc($pack);
				else:
				// error
					$pack_error = sprintf(__('Pack #%d Removed, Assign new Pack to Member','mgm'), $pack_id);
				endif;
			endif;
			// use set
			if(!$pack_desc):
				// set form member data
				$pack = array('membership_type'=>$membership_type,'cost'=>$amount,'currency'=>$currency,'duration'=>$duration,'duration_type'=>$duration_type,'num_cycles'=>(!empty($num_cycles) ? $num_cycles :  0));
				// desc
				$pack_desc = $s_packs->get_pack_desc($pack);
			endif;
			  
			// hide old content                   
			if ($member->hide_old_content && $member->join_date):
				$pack_desc .= sprintf('<div><b>%s:</b> %s</div>', __('Limited PRE','mgm'), date($date_format, $member->join_date));
			endif;
		endif;
		// pack join
		$pack_desc .= sprintf('<div class="overline"><b>%s:</b> %s</div>', __('PACK JOIN','mgm'), (empty($member->join_date) ? __('N/A','mgm') : date($date_format_time, $member->join_date)));
		
		// pack error
		if($pack_error):
			$pack_desc .= sprintf('<div class="overline"><b class="mgm_color_red">%s:</b> %s</div>', __('PACK ERROR','mgm'), $pack_error );
		endif;
		// register
		$register_date = sprintf('<div><b>%s:</b> %s</div>', __('REGISTER','mgm'), date($date_format, strtotime($user->user_registered)));					
		// expire
		$expire_date   = sprintf('<div><b>%s:</b> %s</div>', __('EXPIRY','mgm'), (empty($member->expire_date) ? __('N/A','mgm') : date($date_format_time, strtotime($member->expire_date))));
	
		// build status value
		$subs_status = sprintf('<span class="%s"><b>%s</b></span>', mgm_get_status_css_class($member->status), esc_html($member->status));
		// status_str 
		if (!empty($member->status_str)):
			$subs_status .= '<br />' . esc_html($member->status_str);
		endif;
		// last pay
		$subs_status .= sprintf('<div class="overline"><b>%s:</b> %s</div>',__('LAST PAY','mgm'), (empty($member->last_pay_date) ? __('N/A','mgm') : date($date_format, strtotime($member->last_pay_date))));
		// last transaction
		if(isset($member->transaction_id) && ((int)$member->transaction_id>0)):
			$subs_status .= sprintf('<div class="overline"><b>%s</b> # %d</div>', __('TRANSACTION','mgm'), (int)$member->transaction_id);
		endif;
		
		// fix module
		/*if(!isset($member->payment_info->module) && isset($member->transaction_id)):
			// tran
			$tran = mgm_get_transaction($member->transaction_id);
			// check
			if(isset($tran['module'])):
				// set
				$member->payment_info->module = 'mgm_' . $tran['module'];
				// save
				$member->save();
			endif;
		endif;*/
		
		// module transaction info
		if(isset($member->payment_info->module) && ($module_obj = mgm_is_valid_module($member->payment_info->module, 'payment', 'object'))):
			$subs_status .= $module_obj->get_transaction_info($member, $date_format);
		endif;
		
		// payment check
		if(isset($member->last_payment_check_type) && isset($member->last_payment_check_date)):
			$subs_status .= sprintf('<div class="overline"><b>%s:</b> %s - %s</div>', __('LAST PAYMENT CHECK','mgm'), date($date_format, strtotime($member->last_payment_check_date)), strtoupper($member->last_payment_check_type));						
		endif;?>
	<tr class="<?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="member_row_<?php echo $user->ID ?>">					
	   <td width="1%" align="center">
			<input type="checkbox" name="members[]" id="user_<?php echo $user->ID ?>" value="<?php echo $user->ID ?>" />				   
	   </td>
	   <td width="14%">
	   		<div id="user-<?php $user->ID ?>">
		   		<label for="user_<?php $user->ID ?>"><strong>
		   			<?php echo esc_html($user->first_name . ' ' . $user->last_name) ?> 
		   			[<a href="user-edit.php?user_id=<?php echo $user->ID?>" target="_blank" title="<?php _e('Edit User','mgm')?>"><?php echo $user->ID ?></a>]
		   			<?php if( !$email_as_username ): echo '<br>'.esc_html($user->user_login); endif;?>
				</strong></label>
				<div><a href="mailto:<?php echo esc_html($user->user_email) ?>"><?php echo esc_html($user->user_email) ?></a></div>
			</div>							
		</td>
		<td width="85%" colspan="5">					
			<table width="100%" cellpadding="1" cellspacing="0" border="0" class="nested-table">
				<tr class="<?php echo $alt;?>">
					<td width="15%"><?php echo mgm_stripslashes_deep($m_types->get_type_name($member->membership_type));?></td>
					<td width="15%"><?php echo $pack_desc?></td>
					<td width="15%"><?php echo $register_date?></td>
					<td width="15%"><?php echo $expire_date?></td>
					<td width="25%"><?php echo $subs_status?></td>
				</tr>
			</table>
			<?php 
			//Issue #775
			$con = 0;
			foreach ($member->other_membership_types as $oth_member_check):
				if(!empty($oth_member_check)):
					$con++;
				endif;
			endforeach;
			if(isset($member->other_membership_types) && is_array($member->other_membership_types) && !empty($member->other_membership_types) && $con > 0 ):?>						
			<ul id="membership_tree_<?php echo $user->ID; ?>">
				<li>
					<strong class="underline"><?php _e('Other Memberships', 'mgm') ?></strong>
					<ul>
						<?php foreach ($member->other_membership_types as $key => $member_oth):
								// other
								$member_oth = mgm_convert_array_to_memberobj($member_oth, $user->ID);
								// check
								if(isset($member_oth->membership_type) && !empty($member_oth->membership_type) && !in_array($member_oth->membership_type, array('trial', 'guest'))):
									//issue #: 509
									if (strtolower($member_oth->membership_type) == 'guest'):
										$pack_desc_oth = __('N/A','mgm');
									else:
										// member data
										$amount_oth      	 = esc_html($member_oth->amount);
										$currency_oth    	 = esc_html($member_oth->currency);
										$duration_oth        = esc_html($member_oth->duration);
										$duration_type_oth   = $member_oth->duration_type;   
										$membership_type_oth = esc_html($member_oth->membership_type);
										$pack_id_oth         = $member_oth->pack_id;	
										$num_cycles_oth      = (isset($member_oth->active_num_cycles)) ? $member_oth->active_num_cycles : null;	
										// get pack desc
										if((int)$pack_id_oth > 0):
											// get pack
											$pack_oth      = $s_packs->get_pack($pack_id_oth); 
											// desc
											$pack_desc_oth = $s_packs->get_pack_desc($pack_oth);
										else:
										// use set
											$pack_oth      = array('membership_type'=>$membership_type_oth,'cost'=>$amount_oth,'currency'=>$currency_oth,'duration'=>$duration_oth,'duration_type'=>$duration_type_oth,'num_cycles'=> (!is_null($num_cycles_oth) ? $num_cycles_oth: 0) );
											// desc
											$pack_desc_oth = $s_packs->get_pack_desc($pack_oth);
										endif;
										  
										// hide old content                   
										if ($member_oth->hide_old_content && $member_oth->join_date):														
											$pack_desc_oth .= sprintf('<div><span class="mgm_color_gray">%s:</span> %s</div>', __('Limited PRE','mgm'), date($date_format, $member_oth->join_date));
										endif;
									endif;
									// pack join
									$pack_desc_oth .= sprintf('<div class="overline"><b>%s:</b> %s</div>', __('PACK JOIN','mgm'), (empty($member_oth->join_date) ? __('N/A','mgm') : date($date_format_time, $member_oth->join_date)));
									
									// register
									$register_date_oth = sprintf('<div><b>%s:</b> %s</div>', __('REGISTER','mgm'), date($date_format, strtotime($user->user_registered)));					
									// expire
									$expire_date_oth   = sprintf('<div><b>%s:</b> %s</div>', __('EXPIRY','mgm'), (empty($member_oth->expire_date) ? __('N/A','mgm') : date($date_format_time, strtotime($member_oth->expire_date))));
								
									// build status value
									$subs_status_oth = sprintf('<span class="%s"><b>%s</b></span>', mgm_get_status_css_class($member_oth->status), esc_html($member_oth->status));
									// status_str 
									if (!empty($member_oth->status_str)):
										$subs_status_oth .= '<br />' . esc_html($member_oth->status_str);
									endif;
									// last pay
									$subs_status_oth .= sprintf('<div class="overline"><span class="mgm_color_gray">%s:</span> %s</div>',__('LAST PAY','mgm'), (empty($member_oth->last_pay_date) ? __('N/A','mgm') : date($date_format, strtotime($member_oth->last_pay_date))));
									// last transaction
									if(isset($member_oth->transaction_id) && ((int)$member_oth->transaction_id>0)):
										$subs_status_oth .= sprintf('<div class="overline"><span class="mgm_color_gray">%s#</span> %d</div>', __('TRANSACTION','mgm'), (int)$member_oth->transaction_id);
									endif;												
									// module transaction info
									if(isset($member_oth->payment_info->module) && ($module_object = mgm_is_valid_module($member_oth->payment_info->module,'payment','object'))):
										$subs_status_oth .= $module_object->get_transaction_info($member_oth, $date_format);
									endif;?>
						<li>
							<table width="100%" cellpadding="1" cellspacing="0" border="0" class="nested-table">
								<tr class="<?php echo $alt;?>">								
									<td width="10%">
										<!-- <input onclick="mgm_uncheck_other_memberships(this,'<?php //echo $user->ID; ?>');" type="checkbox" name="ps_mem[<?php //echo $user->ID ?>][]" id="user_mem_<?php //echo $user->ID ?>_<?php //echo $member_oth->membership_type ?>" value="<?php //echo $member_oth->membership_type ?>" /> -->
										<input class="otherMems" type="checkbox" name="ps_mem[<?php echo $user->ID ?>][]" id="user_mem_<?php echo $user->ID ?>_<?php echo $member_oth->membership_type ?>" value="<?php echo $member_oth->membership_type ?>" />
										<input type="hidden" name="ps_mem_index[<?php echo $user->ID ?>][<?php echo $member_oth->membership_type ?>]" id="user_mem_index_<?php echo $user->ID ?>_<?php echo $key ?>" value="<?php echo $key ?>" />
									</td>
									<td width="15%"><?php echo mgm_stripslashes_deep($m_types->get_type_name($member_oth->membership_type));?></td>
									<td width="15%"><?php echo $pack_desc_oth?></td>
									<td width="15%"><?php echo $register_date_oth?></td>
									<td width="15%"><?php echo $expire_date_oth?></td>
									<td width="25%"><?php echo $subs_status_oth ?></td>
								</tr>
							</table>
						</li>
						<?php endif; endforeach;?>
					</ul>
				</li>
			</ul>
			<script type="text/javascript">						
				arr_ul_ids[arr_ul_ids.length] = 'membership_tree_<?php echo $user->ID; ?>';											
			</script>
			<?php endif;?>											
		</td>					
	</tr>
	<?php endforeach; endif;?>