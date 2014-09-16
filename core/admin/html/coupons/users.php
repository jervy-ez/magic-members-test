<?php mgm_box_top(sprintf("View Users Registered with Coupon: %s",$data['coupon']->name));?>


<div class="table widefatDiv width100">
	<div class="row headrow">
		<div class="cell theadDivCell width25 textalignleft">
			<?php _e('ID#','mgm')?>
		</div>
		<div class="cell theadDivCell width25 textalignleft">
			<?php _e('Username','mgm')?>
		</div>
		<div class="cell theadDivCell width25 textalignleft">
			<?php _e('Email','mgm')?>
		</div>
		<div class="cell theadDivCell width25 textalignleft">
			<?php _e('Membership Type','mgm')?>
		</div>
	</div>
	<?php 
	// mgm_pr($data);
	// init
	$users_count = 0;
	// check
	if($data['users']): foreach ($data['users'] as $user) : if ($user->ID):				
		// member
		//$member = unserialize($user->mgm_member_options);
		//$member = mgm_convert_array_to_memberobj($member, $user->ID);
		$user = get_userdata($user->ID)	;
		// member
		$member = mgm_get_member($user->ID);		
		// show
		$show = false;
		// check
		if( mgm_member_has_coupon($member, $data['coupon']->id) ) :
			$show = true;
		else:
			if( isset($member->other_membership_types) && !empty($member->other_membership_types)):
				// log
				// mgm_log($member->other_membership_types, 'other_membership_types_'.$user->ID);
				// loop
				foreach ($member->other_membership_types as $key => $member_oth):
					// as object
					$o_mgm_member = mgm_convert_array_to_memberobj($member_oth, $user->ID);
					// log
					// mgm_log($o_mgm_member, 'coupon_users');
					// check
					if( mgm_member_has_coupon($o_mgm_member, $data['coupon']->id) ) :
						$show = true; break;
					endif;
					// unset
					unset($o_mgm_member);
				endforeach;		
			endif;
		endif;
		
		if($show): $users_count++; ?>
	<div class="row brBottom <?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
		<div class="cell width25 textalignleft paddingleftimp10px">
			<?php echo $user->ID ?>
		</div>
		<div class="cell width25 textalignleft paddingleftimp10px">
			<?php echo $user->user_login?>
		</div>
		<div class="cell width25 textalignleft paddingleftimp10px">
			<?php echo $user->user_email ?>
		</div>
		<div class="cell width25 textalignleft paddingleftimp10px">
			<?php echo ucwords(str_replace('_', ' ', $member->membership_type)) ?>
		</div>
	</div>
	<?php
		// unset
		unset($member);
		endif; endif; endforeach; endif;?>

	<?php if($users_count == 0):?>
	<div class="row">
		<div class="cell">
			<?php _e('There are currently no user registered with this coupon.','mgm')?>
		</div>
	</div>	
	<?php endif;?>

	<div class="row">
		<div class="cell">
			<div class="floatleft">	
				<input class="button" type="button" onclick="mgm_coupon_users(false)" value="<?php _e('Back to Coupons', 'mgm') ?>" />
			</div>		
		</div>
	</div>	
</div>
<?php mgm_box_bottom();?>