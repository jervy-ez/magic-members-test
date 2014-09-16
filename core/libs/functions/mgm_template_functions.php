<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members template/theme functions
 *
 * @package MagicMembers
 * @subpackage Facebook
 * @since 2.6
 */

// check membership
function mgm_member_check($membership_types = array()) {
	$user_ac = mgm_get_user_membership_type();
	return in_array($user_ac, $membership_types);
}

// deprecated / only on tag
function mgm_membership_content_page() {
	global $wpdb;
	
	// current_user
	$current_user = wp_get_current_user();

	$snippet_length = 200;
	$max_loops = 30;
	
	$css_group = mgm_get_css_group();

	$html = '';
	
	//issue #867
	if($css_group != 'none') {
		//expand this if needed
		$css_link_format = '<link rel="stylesheet" href="%s" type="text/css" media="all" />';				
		$css_file = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.pages.css';
		$html .= sprintf($css_link_format, $css_file);
	}		

	$arr_mtlabel = mgm_get_subscribed_membershiptypes_with_label($current_user->ID);
	$membership_level = mgm_stripslashes_deep(implode(', ',$arr_mtlabel));
		
	$member = mgm_get_member($current_user->ID);
	$arr_memberships = mgm_get_subscribed_membershiptypes($current_user->ID, $member );
	$posts = false;
	$blog_home = home_url();

	$sql = 'SELECT DISTINCT(ID), post_name, post_title, post_date, post_content
			FROM
				' . $wpdb->posts . ' p
				JOIN ' . $wpdb->postmeta . ' pm ON (
					p.ID = pm.post_id
					AND p.post_status = "publish"
					AND pm.meta_key LIKE "_mgm_post%"					
					AND post_type = "post"
				)
			ORDER BY post_date DESC';
		
	// get posts	
	$results = $wpdb->get_results($sql);
	// capture only accessible
	$accessible_posts = array();
	//check
	if (count($results) >0) {
		foreach ($results as $id=>$obj) {
			// get post
			$post_obj = mgm_get_post($obj->ID);
			// membership types
			$membership_types = $post_obj->get_access_membership_types();
			//user accessible posts
			if(count(array_intersect($membership_types, $arr_memberships)) > 0){
				$accessible_posts[] = $obj;
			}
			unset($post_obj);
			unset($obj);
		}
	}
		
	if ($members_pages = count($accessible_posts)) {
		$loops = 0;
		foreach ($accessible_posts as $id=>$obj) {		
			//issue #1690
			if(trim($obj->post_name) == 'userprofile'){ continue;}			
			$published    = date('jS F Y', strtotime($obj->post_date));
			$title        = $obj->post_title;
			$full_content = $obj->post_content;
			if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
				$title = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($title);
				$full_content = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($full_content);
			}
			$content = substr(strip_tags($full_content), 0, $snippet_length);
			$content = preg_replace("'\[/?\s?private\s?\]'i",'', $content);
			$ending = (strlen($full_content) > strip_tags($snippet_length) ? '...':'');

			$row = '<div class="row br_bottom">
						<div class="cell mgm_border_top1px_solid_silver">							
							<div class="mgm_post_title_div"><a href="' . get_permalink($obj->ID) . '">' . $title . '</a></div>
							<div class="mgm_post_content_div">' . $content . '</div>
						</div>
						<div class="cell mgm_border_top1px_solid_silver mgm_vertical_align_top">' . $published . '</div>
					</div>';

			$posts .= $row;
			$loops++;

			if ($loops >= $max_loops) {
				break;
			}					
		}

	}

	$table_intro = __('Showing the most recent ','mgm') . $loops . __(' posts of a total ','mgm') . $members_pages . __(' available to you','mgm').'.';

	$html .= '	<div class="mgm_post_membership_level_div" >' . __('Your membership level is:',"mgm") . ' ' . $membership_level . '.</div>
						<div class="mgm_post_access_total_div">
					' . __('You have access to a total of','mgm') . ' ' . $members_pages . ' ' . __('premium', 'mgm') . ' ' .  ($members_pages == 1 ? __('Post', 'mgm'):__('Posts', 'mgm')) . ' 
				</div>';

	if ($members_pages > 0) {
		$html .= $table_intro;

		$html .= '<div class="mgm_margin_bottom_10px mgm_padding_top_10px">
					<div class="table width100">
						<div class="row br_bottom">
							<div class="cell th_div mgm_text_align_left">'.__('Post Title','mgm').'</div>
							<div class="cell th_div mgm_width160px mgm_text_align_left">'.__('Published','mgm').'</div>
						</div>
					' . $posts . '
				</div></div>';
		
		if ($pp = mgm_render_my_purchased_posts($current_user->ID, false, true)) {
			$html .= '<h4>'.__('My Purchased Posts','mgm').'</h4>
			' . $pp;
		}	
	}
	return $html;
}

/**
 * user subscription
 */
function mgm_user_subscription_info($user_id=NULL,$args=array()) {	
	// current user
	if(!$user_id){
		$user = wp_get_current_user();
	}else{	
	// by user id
		$user = get_userdata($user_id);	
	}		
	
	// return when no user
	if(!isset($user->ID) || (isset($user->ID) && (int)$user->ID == 0)){
		return sprintf(__('Please <a href="%s">login</a> to see your subscriptions.', 'mgm'), mgm_get_custom_url('login'));	
	}	
	
	// settings
	$settings = mgm_get_class('system')->get_setting();
	// packs
	$subscription_packs = mgm_get_class('subscription_packs');
	
	$duration_str = $subscription_packs->duration_str;	

	//issue #946
	$duration_str_plu = $subscription_packs->duration_str_plu;
	
	// member
	$member  = mgm_get_member($user->ID);

	//mgm_pr($member);
	// pack
	$pack_id     = $member->pack_id;
	$pack        = $subscription_packs->get_pack($pack_id); 
	//mgm_pr($pack);
	$extend_link = '';
	$subs_package = 'N/A';
	// allow renewal	
	if($pack){					
		// dsc
		$subs_package = $pack['description'];
		//issue#: 478
		$num_cycles = (isset($member->active_num_cycles) && !empty($member->active_num_cycles)) ? $member->active_num_cycles : $pack['num_cycles'] ;
		// check cycles	
		if($num_cycles > 0 && mgm_pack_extend_allowed($pack)){
			$extend_link = ' (<a href="'. mgm_get_custom_url('transactions',false,array('action' => 'extend', 'pack_id'=>$pack_id, 'username' => $user->user_login)) . '">' . __('Extend','mgm') . '</a>)';
		}
	}
	// set others
	$sformat  = mgm_get_date_format('date_format_short');
	
	//issue #946
	// dur, #1452
	if( $member->trial_on ){
		$durstr   = ($member->trial_duration == 1) ? $duration_str[$member->trial_duration_type] : $duration_str_plu[$member->trial_duration_type];
		$durstr  .= ' ( ' . __('Trial','mgm') . ' )';
 	}else{
		$durstr   = ($member->duration == 1) ? $duration_str[$member->duration_type] : $duration_str_plu[$member->duration_type];
	}	
	
	// $durstr   = ($member->duration == 1) ? rtrim($duration_str[$member->duration_type]) : $duration_str[$member->duration_type];
	
	$amount   = (is_numeric($member->amount)) ? sprintf(__('%1$s %2$s','mgm'),number_format($member->amount,2,'.',null),$user->currency):'N/A';
	
	
	$last_pay = $member->last_pay_date ? date($sformat, strtotime($member->last_pay_date)) :'N/A';
	$expiry   = $member->expire_date ? date($sformat, strtotime($member->expire_date)) :'N/A';
	
	//issue #946
	// #1452
	if( $member->trial_on ){
		$duration = $member->trial_duration ? ($member->trial_duration_type == 'l') ? $durstr : ($member->trial_duration . ' ' . $durstr) : 'N/A';
	}else{
		$duration = $member->duration ? ($member->duration_type == 'l') ? $durstr : ($member->duration . ' ' . $durstr) : 'N/A';
	}

		
	// $duration = $member->duration ? (($member->duration_type == 'l') ? $durstr : $member->duration . ' ' . $durstr .($member->duration > 1 ? 's' :'')): 'N/A';

	$membership_type = $member->membership_type;
	// init
	$html = '';
		
	// html	
	$html .= '<div class="table width100 br">';

	// row counter
	$row_ctr = 0;

	// row
	$html .='
		<div class="row alternate br_bottom">
			<div class="cell width25 padding10px">
				<strong>' . __('Access Duration','mgm') . '</strong>
			</div>
			<div class="cell width2 padding10px"><strong>:</strong></div>
			<div class="cell width73 padding10px">' . esc_html($duration) . '</div>
		</div>
		<div class="row br_bottom">
			<div class="cell width25 padding10px"><strong>' . __('Last Payment Date','mgm') . '</strong></div>
			<div class="cell width2 padding10px" ><strong>:</strong></div>
			<div class="cell width73 padding10px">' . esc_html($last_pay) . '</div>
		</div>';
	// counter	
	$row_ctr = 2;	
	// duration				
	if( $member->duration_type != 'l' ) {
		$html .= '
			<div class="row alternate br_bottom">
				<div class="cell width25 padding10px"><strong>' . __('Expiry Date','mgm') . '</strong></div>
				<div class="cell width2 padding10px"><strong>:</strong></div>
				<div class="cell width73 padding10px">' . esc_html($expiry) . $extend_link . '</div>
			</div>';
		// counter	
		$row_ctr++;	
	}

	// cost
	$html .= '
		<div class="row ' . ($row_ctr++ % 2 == 0 ? 'alternate' : '') . ' br_bottom">
			<div class="cell width25 padding10px"><strong>' . __('Membership Cost','mgm') . '</strong></div>
			<div class="cell width2 padding10px"><strong>:</strong></div>
			<div class="cell width73 padding10px">' . ((is_super_admin() ? 'N/A' : esc_html($amount)) . ' ' . mgm_get_class('system')->setting['currency']) .'</div>
		</div>
		
		<div class="row ' . ($row_ctr++ % 2 == 0 ? 'alternate' : '') . ' br_bottom">
			<div class="cell width25 padding10px"><strong>' . __('Membership Level','mgm') . '</strong></div>
			<div class="cell width2 padding10px"><strong>:</strong></div>
			<div class="cell width73 padding10px">' .((is_super_admin() ? 'N/A' : mgm_stripslashes_deep(esc_html(mgm_get_user_membership_type($user->ID)))) .' (<a href="'. mgm_get_custom_url('transactions',false,array('action' => 'upgrade', 'username' => $user->user_login)) . '">' . __('Upgrade','mgm') . '</a>)').'</div>
		</div>
				
		<div class="row ' . ($row_ctr++ % 2 == 0 ? 'alternate' : '') . ' br_bottom">
			<div class="cell width25 padding10px"><strong>' . __('Subscribed Package','mgm') . '</strong></div>
			<div class="cell width2 padding10px"><strong>:</strong></div>
			<div class="cell width73 padding10px">' .mgm_stripslashes_deep(esc_html($subs_package)). '</div>
		</div>';
		
		// append
		if(isset($settings['enable_multiple_level_purchase']) && bool_from_yn($settings['enable_multiple_level_purchase']) && mgm_check_purchasable_level_exists($user->ID, $member)) {
			$html .='
				<div class="row ' . ($row_ctr++ % 2 == 0 ? '' : 'alternate') . ' br_bottom">
					<div class="cell width25 padding10px"><strong>' . __('Other Membership Level(s)','mgm') . '</strong></div>
					<div class="cell width2 padding10px"><strong>:</strong></div>
					<div class="cell width73 padding10px">' .
					((is_super_admin() ? __('N/A','mgm') : '<a href="'.mgm_get_custom_url('transactions',false,array("action" => "purchase_another", "username" => $user->user_login)).'">'. __('Purchase','mgm').' </a>' )).'</div>
				</div>';
		}
		
	// end	
	$html .= '</div>';
		
	// init	
	$unsubscribe =  0;
	// via short code
	if( ! empty($args) ){
		$unsubscribe = (isset($args['unsubscribe'])) ? $args['unsubscribe'] : str_replace('#', '', mgm_array_shift($args));
	}
	// get button
	if( $unsubscribe == 'unsubscribe' ) {
		// stat
		$html .= '<br/><div class="table width100">';
		// button
		$html .= mgm_get_unsubscribe_status_button($member, $user);		
		// end	
		$html .='</div>';			
	}		
	
	// apply filter
	return apply_filters('mgm_user_subscription_html', $html, $user->ID);	
}

/**
 * user other subscriptions
 */
function mgm_user_other_subscriptions_info($user_id=NULL) {
	// current user
	if(!$user_id){
		$user = wp_get_current_user();
	}else{	
	// by user id
		$user = get_userdata($user_id);	
	}		
	
	// return when no user
	if(!isset($user->ID) || (isset($user->ID) && (int)$user->ID == 0)){
		return sprintf(__('Please <a href="%s">login</a> to see your other subscriptions.', 'mgm'), mgm_get_custom_url('login'));	
	}	
	
	// init html
	$html = $othtml = '';
	// member
	$other_members = mgm_get_member($user->ID);
	// check
	if(isset($other_members->other_membership_types) && is_array($other_members->other_membership_types) && !empty($other_members->other_membership_types) > 0) { 
		// packs
		$subscription_packs = mgm_get_class('subscription_packs');
		$duration_str = $subscription_packs->duration_str;

		//issue #946
		$duration_str_plu = $subscription_packs->duration_str_plu;

		$membership_types_obj = mgm_get_class('membership_types');
		$sformat = mgm_get_date_format('date_format_short');	
		$subs_count = 0;
		// loop
		foreach ($other_members->other_membership_types as $key => $member) {			
			//Issue #775
			if(!empty($member)){
				$member = mgm_convert_array_to_memberobj($member, $user->ID);			
				//skip default and expired memberships
				if(in_array($member->status, array(MGM_STATUS_NULL,MGM_STATUS_EXPIRED,MGM_STATUS_PENDING)) || strtolower($member->membership_type) == 'guest' ) continue;
				$pack_id     = $member->pack_id;
				$pack        = $subscription_packs->get_pack($pack_id); 
				$extend_link = '';
				$subs_package = 'N/A';
				// allow renewal	
				if($pack) {			
					// dsc
					$subs_package = $pack['description'];
					//issue#: 478
					$num_cycles = (isset($member->active_num_cycles) && !empty($member->active_num_cycles)) ? $member->active_num_cycles : $pack['num_cycles'] ;
					// check cycles	
					if($num_cycles > 0 && mgm_pack_extend_allowed($pack)){
						$extend_link = ' (<a href="'. mgm_get_custom_url('transactions',false,array('action' => 'extend', 'pack_id'=>$pack_id, 'username' => $user->user_login, 'multiple_purchase' => 'Y')) . '">' . __('Extend','mgm') . '</a>)';
					}			
				}
				// set others				
				//issue #946
				$durstr   = ($member->duration == 1) ? $duration_str[$member->duration_type] : $duration_str_plu[$member->duration_type];
				$amount   = (is_numeric($member->amount)) ? sprintf(__('%1$s %2$s','mgm'),number_format($member->amount,2,'.',null),$user->currency):'N/A';
				$last_pay = $member->last_pay_date ? date($sformat, strtotime($member->last_pay_date)) :'N/A';
				$expiry   = $member->expire_date ? date($sformat, strtotime($member->expire_date)) :'N/A';
				$duration = $member->duration ? (($member->duration_type == 'l') ? $durstr : $member->duration . ' ' . $durstr): 'N/A';
				
				$membership_type = $member->membership_type;

				$othtml .= '<p><div class="table width100 br">
							<div class="row alternate br_bottom">
								<div class="cell width25"><strong>' . __('Access Duration','mgm') . '</strong></div>
								<div class="cell width2"><strong>:</strong></div>
								<div class="cell width73">' . esc_html($duration) . '</div>
							</div>
							<div class="row alternate br_bottom">
								<div class="cell width25"><strong>' . __('Last Payment Date','mgm') . '</strong></div>
								<div class="cell width2"><strong>:</strong></div>
								<div class="cell width73">' . esc_html($last_pay) . '</div>
							</div>';
				

				if($member->duration_type != 'l') {
					$othtml .= '<div class="row alternate br_bottom">
									<div class="cell width25"><strong>' . __('Expiry Date','mgm') . '</strong></div>
									<div class="cell width2"><strong>:</strong></div>
									<div class="cell width73">' . esc_html($expiry). $extend_link . '</div>
								</div>';
				}
								
				$othtml .= '<div class="row alternate br_bottom">
								<div class="cell width25"><strong>' . __('Membership Cost','mgm') . '</strong></div>
								<div class="cell width2"><strong>:</strong></div>
								<div class="cell width73">' . (is_super_admin() ? 'N/A' : esc_html($amount) . ' ' . mgm_get_class('system')->setting['currency']) .'</div>
							</div>
							
							<div class="row alternate br_bottom">
								<div class="cell width25"><strong>' . __('Membership Type','mgm') . '</strong></div>
								<div class="cell width2"><strong>:</strong></div>
								<div class="cell width73">' .(is_super_admin() ? 'N/A' : mgm_stripslashes_deep(esc_html($membership_types_obj->membership_types[$membership_type])).' (<a href="'. mgm_get_custom_url('transactions',false,array('action' => 'upgrade', 'username' => $user->user_login, 'membership_type' => $membership_type, 'prev_pack_id' => $pack_id )) . '">' . __('Upgrade','mgm') . '</a>)') .'</div>
							</div>
							
							<div class="row alternate br_bottom">
								<div class="cell width25"><strong>' . __('Subscribed Package','mgm') . '</strong></div>
								<div class="cell width2"><strong>:</strong></div>
								<div class="cell width73">' .mgm_stripslashes_deep(esc_html($subs_package)). '</div>
							</div>						
						</div></p>';
				
				// status button
				$othtml .= mgm_get_other_unsubscribe_status_button($member, $user, $subs_count);
				//issue #1541
				if($subs_count == 0)
					$subs_count++;				
			}			
		}

		// count			
		if( $subs_count > 0 ) {
			// cancel script
			$cancel_script = '';
			if( in_array($other_members->membership_type, array('free','trial') ) 
				|| $other_members->status == MGM_STATUS_CANCELLED 
				|| ( isset($other_members->status_reset_on) && isset($other_members->status_reset_as) ) ) {
				// script
				$cancel_script = '<script language="javascript">'.
								 'confirm_unsubscribe=function(element){'.										
									'if(confirm("' .__('You are about to unsubscribe. Do you want to proceed?','mgm') . '")){'.																					
										'jQuery(element).closest("form").submit();'.
									'}'.								
								 '}'.
							     '</script>';
			}

			$html .= '<h3>'.__('Other Subscriptions','mgm').'</h3>'.
					 '<p>' . $othtml . $cancel_script . '</p>';
		}
	}	
	
	// filter
	return apply_filters('mgm_other_subscriptions_html',$html, $user->ID);	
}

//rss token/membesrship cancellation form
function mgm_user_membership_info($user_id = NULL) {
	// init
	$user = NULL;
	
	// get user
	if($user_id) $user = get_userdata($user_id);
	
	// get current user
	if(!isset($user->ID)) $user = wp_get_current_user();
	
	// return
	if(!isset($user->ID)) return "";
	
	// token
	$token   = mgm_get_rss_token();
	$url     = home_url();
	$rss_url = add_query_arg(array('feed'=>'rss2','token'=>$token), $url) ;
	$member  = mgm_get_member($user->ID);
	// init
	$html = '';
	// token
	if (mgm_use_rss_token()) {
		$html .= '<div class="mgm_margin_bottom_10px">'.
				 '<h4>'. __('RSS Tokens','mgm'). '</h4>'.
				 '<div class="mgm_margin_bottom_10px">'. __('Your RSS Token is','mgm').': <strong>' .$token. '</strong></div>'.
				 '<div class="mgm_margin_bottom_10px">'.
					 __('Use the following link to access your RSS feed with access to private parts of the site.','mgm').'<br /><br />'.
					'<a href="'. $rss_url .'">'. $rss_url.' </a>'.
				 '</div>'.
				 '</div>';	 
	}

	// add unsubscribe button
	$html .= mgm_get_unsubscribe_status_button($member, $user);
	
	// apply filter
	return apply_filters('mgm_membership_details_html', $html, $user->ID);
}

/**
 * generate status button
 *
 * @param object $member
 * @param object $user
 * @return string $html
 */
function mgm_get_unsubscribe_status_button($member, $user){
	// return if printed earleir
	if( defined('MGM_UNSUBSCRIBE_STATUS_BUTTON') ) return;
	// init
	$html = '';
	// cancelled
	if($member->status == MGM_STATUS_CANCELLED) {
		$html .= '<div class="mgm_margin_bottom_10px ">'.
					'<h4>'. __('Unsubscribed','mgm').'</h4>'.
					'<div class="mgm_margin_bottom_10px mgm_color_red">'.
						 __('You have unsubscribed.','mgm'). 
					'</div>'.
					'</div>';
	}elseif((isset($member->status_reset_on) && isset($member->status_reset_as)) && $member->status == MGM_STATUS_AWAITING_CANCEL) {
		$lformat = mgm_get_date_format('date_format_long');
		$html .= '<div class="mgm_margin_bottom_10px">'.
				'<h4>'. __('Unsubscribed','mgm').'</h4>'.
				'<div class="mgm_margin_bottom_10px mgm_color_red">'.
					 sprintf(__('You have unsubscribed. Your account has been marked for cancellation on <b>%s</b>.','mgm'), date($lformat, strtotime($member->status_reset_on))). 
				'</div>'.
				'</div>';
	}else {		
		// show unsucscribe button			
		if( !is_super_admin() ) {
			// check
			if( $module = $member->payment_info->module ) {
				// if a valid module
				if( $obj_module = mgm_is_valid_module($module, 'payment', 'object') ){
					// output button
					$html .= $obj_module->get_button_unsubscribe(array('user_id'=>$user->ID, 'membership_type' => $member->membership_type));
					$html .= '<script language="javascript">'.
							'confirm_unsubscribe=function(element){'.
								'if(confirm("' .__('You are about to unsubscribe. Do you want to proceed?','mgm') . '")){'.																
									'jQuery(element).closest("form").submit();'.
								'}'.								
							'}'.
						'</script>';
				}				
			}
		}	
	}

	// define
	define('MGM_UNSUBSCRIBE_STATUS_BUTTON', 'DONE');
	// return 
	return $html;
}

/**
 * copy function
 *
 * @deprecated
 */
/*
function mgm_get_unsubscribe_status_button_copy(){
	// cancelled
	if($member->status == MGM_STATUS_CANCELLED) {
		$html .= '<div class="mgm_margin_bottom_10px ">'.
					'<h4>'. __('Unsubscribed','mgm').'</h4>'.
					'<div class="mgm_margin_bottom_10px mgm_color_red">'.
						 __('You have unsubscribed.','mgm'). 
					'</div>'.
					'</div>';
	}elseif((isset($member->status_reset_on) && isset($member->status_reset_as)) && $member->status == MGM_STATUS_AWAITING_CANCEL) {
		$lformat = mgm_get_date_format('date_format_long');
		$html .= '<div class="mgm_margin_bottom_10px">'.
				'<h4>'. __('Unsubscribed','mgm').'</h4>'.
				'<div class="mgm_margin_bottom_10px mgm_color_red">'.
					 sprintf(__('You have unsubscribed. Your account has been marked for cancellation on <b>%s</b>.','mgm'), date($lformat, strtotime($member->status_reset_on))). 
				'</div>'.
				'</div>';
	}else {		
		// show unsucscribe button			
		if(!is_super_admin()) {
			if(!empty($member->payment_info->module)) {
				$module = $member->payment_info->module;			
				$obj_module = mgm_get_module($module,'payment');
				if($module && is_object($obj_module) && method_exists($obj_module, 'get_button_unsubscribe')) {
					// output button
					$html .= mgm_get_module($module,'payment')->get_button_unsubscribe(array('user_id'=>$user->ID, 'membership_type' => $member->membership_type));
					$html .= '<script language="javascript">'.
							'confirm_unsubscribe=function(element){'.
								'if(confirm("' .__('You are about to unsubscribe. Do you want to proceed?','mgm') . '")){'.																
									'jQuery(element).closest("form").submit();'.
								'}'.								
							'}'.
						'</script>';
				}
			}
		}	
	}
}
*/

/**
 * generate status button for other subscription
 *
 * @param object $member
 * @param object $user
 * @return string $html
 */
function mgm_get_other_unsubscribe_status_button($member, $user, &$subs_count){
	// init
	$html = '';
	// cancelled
	if($member->status == MGM_STATUS_CANCELLED) {
		$html .= '<div class="mgm_margin_bottom_10px">'.
				 '<h4>'. __('Unsubscribed','mgm').'</h4>'.
				 '<div class="mgm_margin_bottom_10px mgm_color_red">'.
					__('You have unsubscribed.','mgm'). 
				 '</div>'.
				 '</div>';
	}elseif((isset($member->status_reset_on) && isset($member->status_reset_as)) && $member->status == MGM_STATUS_AWAITING_CANCEL) {
		$lformat = mgm_get_date_format('date_format_long');
		$html .= '<div class="mgm_margin_bottom_10px">'.
				 '<h4>'. __('Unsubscribed','mgm').'</h4>'.
				 '<div class="mgm_margin_bottom_10px mgm_color_red">'.						
					 sprintf(__('You have unsubscribed. Your account has been marked for cancellation on <b>%s</b>.','mgm'), date($lformat, strtotime($member->status_reset_on))). 
				 '</div>'.
				 '</div>';
	}else {		
		// show unsucscribe button			
		if( !is_super_admin() ) {
			// check
			if( $module = $member->payment_info->module ) {
				// if a valid module
				if( $obj_module = mgm_is_valid_module($module, 'payment', 'object') ){
					// output button
					$html .= $obj_module->get_button_unsubscribe(array('user_id'=>$user->ID, 'membership_type' => $member->membership_type));	
					// increment						
					$subs_count++;	
				}
			}		
		}	
	}

	return $html;
}

//user membership details
function mgm_membership_details($user_id = NULL) {	

	$css_group = mgm_get_css_group();		

	// get 
	if($user_id) $user = get_userdata($user_id);
	
	// get current user
	if(!isset($user->ID)){
		$user = wp_get_current_user();
	}
	// return when no user
	if(!$user->ID) {
		return sprintf(__('You need to <a href="%s">login</a> to see that Page'), mgm_get_custom_url('login'));
		// return mgm_redirect(mgm_get_custom_url('login'), NULL, 'javascript', true);		
	}	
		
	// init
	$html = '';

	//issue #867
	if($css_group !='none') {
		//expand this if needed
		$css_link_format = '<link rel="stylesheet" href="%s" type="text/css" media="all" />';				
		$css_file = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.pages.css';
		$html .= sprintf($css_link_format, $css_file);
	}		
	// error
	if(isset($_GET['unsubscribe_errors']) && !empty($_GET['unsubscribe_errors'])) {
		$errors = new WP_Error();		
		$errors->add('unsubscribe_errors', urldecode(strip_tags($_GET['unsubscribe_errors'])), (isset($_GET['unsubscribed'])?'message':'error'));
		$html .= mgm_set_errors($errors, true);		
		unset($errors);		
	}
	// subscription_info
	if( $subinfo_html = mgm_user_subscription_info($user_id) ){
		$html .= sprintf('<h3>%s</h3><p>%s</p>', __('Subscription Information','mgm'), $subinfo_html);	
	}
	// membership_info
	if( $membinfo_html = mgm_user_membership_info($user_id) ){
		$html .= sprintf('<h3>%s</h3><p>%s</p>', __('Membership Information','mgm'), $membinfo_html);
	}	
	// other subscriptions
	if( $oth_subinfo_html = mgm_user_other_subscriptions_info() ){
		$html .= $oth_subinfo_html;
	}
	//issue #1635
	$membership_details_html = '<div class="mgm_membership_details_container">'.$html.'</div>';	
	// return 	
	return $membership_details_html;					
}
// mgm_user_profile
function mgm_user_profile($user_id=NULL){
	// get user
	if($user_id){
		$user = get_userdata($user_id);
	}else{
		$user = wp_get_current_user();
	}	
	
	if(!$user){
		// query string
		$user = mgm_get_user_from_querystring();
	}	
	
	// check
	if(!$user){
		die(__('No user','mgm'));		
	}
	
	// do your code
	do_action('show_user_profile');
}
// accessible contents
function mgm_member_accessible_contents($pagetype = 'admin'){	
	global $wpdb;		
	// current_user
	$current_user = wp_get_current_user();
	// snippet
	$snippet_length = 200;
	// get member
	$member = mgm_get_member($current_user->ID);
	//get all subscribed membership types
	$arr_memberships = mgm_get_subscribed_membershiptypes($current_user->ID, $member);	
	// accessible posts
	$accessible_posts = mgm_get_membership_contents($arr_memberships,'accessible');
	
	// mgm_pr($accessible_posts);
	// posts
	$posts = $accessible_posts['posts'];
	// total
	//$total_posts = $accessible_posts['total_posts'];
	$total_posts = $accessible_posts['total_posts'];
	// total post rows , unfiltered
	$total_post_rows = $accessible_posts['total_post_rows'];
	// pager
	$pager = $accessible_posts['pager'];
	// init output
	$html = $alt = '';

	// table
	$html .= '<div class="table width100 br">'.
			'<div class="row br_bottom">'.
				'<div class="cell th_div width30 padding10px"><b> '.__('Post Title','mgm') . '</b></div>'.
				'<div class="cell th_div width70 padding10px"><b>'.__('Post Content','mgm') .'</b></div>'.
				//issue #920
				//'<div class="cell th_div"><b>'.__('Published','mgm') .'</b></div>'.				
			'</div>';		

		if($total_posts>0) { 
			$pattern = get_shortcode_regex();
			foreach ($posts as $id=>$obj) {
				//issue #1690
				if(trim($obj->post_name) == 'userprofile'){ continue;}
				// set			
				$published = date('jS F Y', strtotime($obj->post_date));
				$title     = $obj->post_title;
				$content   = $obj->post_content;
				// content convert
				if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
					$title   = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($title);
					$content = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($content);
				}				
				//issue#: 443								
				$content = preg_replace('/'.$pattern.'/s', '', $content);
				$content  = substr(strip_tags($content), 0, $snippet_length);				
				$content .= (strlen($content) > $snippet_length ? '...':'');				

				$html .='<div class="row br_bottom'.($alt = ($alt=='') ? 'alternate': '').'">'.
						'<div class="cell width30 padding10px"><a href="'.get_permalink($obj->ID).'">'.$title.'</a></div>'.
						'<div class="cell width70 padding10px">'.$content.'</div>'.
						//issue #920
						//'<div class="cell">'.$published .'</div>'.
						'</div>';
			
				}
		}else{
			$html .= '<div class="row br_bottom'.($alt = ($alt=='') ? 'alternate': '').'">'.
					 '<div class="cell mgm_text_align_center">'.__('No premium contents','mgm').'</div>'.
					 '</div>';
		}	
			
	$html .='</div>';	
	
	// footer
	if($total_posts > 0) {
		$html .= '<div class="mgm_margin10px">';
		if(isset($_GET['section']) && $_GET['section'] == 'accessible') {
			$html .= '<div class="mgm_content_back_link_div">'.
					 '<a href="'.(($pagetype=='admin')? admin_url('profile.php?page=mgm/membership/content') : mgm_get_custom_url('membership_contents')) .'" class="button">'.__('Back','mgm') .'</a>'.
					 '</div>';
		}
		$html .= '<div class="mgm_content_total_post_div">'.
				sprintf(__('You have access to a total of %d premium %s.','mgm'), $total_posts, ($total_posts == 1 ? __('Post', 'mgm'):__('Posts', 'mgm'))).
				'</div>';
		$html .='<div class="mgm_content_total_publish_div">';
		if(isset($_GET['section']) && $_GET['section'] == 'accessible') {
			$html .= '<span class="pager">'.$accessible_posts['pager'].'</span>';
		//}elseif($total_post_rows > $total_posts) {
		//Do not show See All if number of records are <= $total_posts
		}elseif($total_posts > count($posts)) {
			$html .= '<a href="'.(($pagetype=='admin') ? admin_url('profile.php?page=mgm/membership/content&section=accessible') : mgm_get_custom_url('membership_contents', false, array('section' => 'accessible'))).'" class="button">'.__('See All','mgm').'</a>';
		}
		$html .='</div>';	
		$html .='<br/><div class="clearfix"></div>';
		$html .='</div>';	
	}
	return $html;
}

// purchased contents
function mgm_member_purchased_contents($pagetype = 'admin'){
	global $wpdb;
	// current_user
	$current_user = wp_get_current_user();
	// snippet
	$snippet_length = 200;	
	// purchased
	$purchased_posts = mgm_get_purchased_posts($current_user->ID);	
	// posts
	$posts = $purchased_posts['posts'];
	// total_posts
	$total_posts = $purchased_posts['total_posts'];
	// total post rows , unfiltered
	// $total_post_rows = $purchased_posts['total_post_rows'];
	// init
	$html = $alt = '' ;

	
	// start output
	$html .= '<div class="table width100 br">'.
			
				'<div class="row br_bottom">'.
					'<div class="cell th_div width25 padding10px"><b>'.__('Post Title','mgm').'</b></div>'.
					'<div class="cell th_div width45 padding10px"><b>'.__('Post Content','mgm').'</b></div>'.
					//issue #920
					//'<div class="cell th_div width15 padding10px"><b>'.__('Published','mgm').'</b></div>'.
					'<div class="cell th_div width15 padding10px"><b>'.__('Purchased','mgm').'</b></div>'.
					'<div class="cell th_div width15 padding10px"><b>'.__('Expiry','mgm').'</b></div>'.
				'</div>';	
	// check		
	if($total_posts>0) { 
		// loop
		foreach($posts as $id=>$obj){
			// set			
			$published = date('jS F Y', strtotime($obj->post_date));
			$purchased = date('jS F Y', strtotime($obj->purchase_dt));
			$title     = $obj->post_title;
			$content   = $obj->post_content;
			if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
				$title   = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($title);
				$content = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($content);
			}
			$content  = preg_replace("'\[/?\s?private\s?\]'i",'', $content);
			//$content  = preg_replace("'\[\[(.*)\]\]'i",'', $content);
			//issue: 314
			$content  = preg_replace("/\[.*?\]/",'', $content);
			$content  = substr(strip_tags($content), 0, $snippet_length);
			$content .= (strlen($content) > $snippet_length ? '...':'');
			//expiry date:
			$expiry = mgm_get_post($obj->ID)->get_access_duration();			
			$expiry = (!$expiry) ? __('Indefinite', 'mgm') : (date('jS F Y',(86400*$expiry) + strtotime($obj->purchase_dt)) . " (" . $expiry . (__(' Day', 'mgm').($expiry > 1?__('s', 'mgm'):'')).")");			
					
		$html .='<div class="row br_bottom '.($alt = ($alt=='') ? 'alternate': '').'">'.
				'<div class="cell width25 padding10px"><a href="'.get_permalink($obj->ID).'">'.$title.'</a></div>'.
				'<div class="cell width45 padding10px">'.$content.'</div>'.
				//issue #920
				//'<div class="cell width25 padding10px">'.$published.'</div>'.
				'<div class="cell width15 padding10px">'.$purchased.'</div>'.								
				'<div class="cell width15 padding10px">'.$expiry.'</div>'.								
			'</div>';			
		}
	}else {
		$html .='<div class="row br_bottom'.($alt = ($alt=='') ? 'alternate': '').'">'.
			'<div class="cell mgm_text_align_center">'.__('No purchased contents','mgm').'</div>'.
			'</div>';
	}			
	$html .='</div>';	
	
	//return $html;			
	if($total_posts > 0 ) {
		$html .= '<div class="mgm_margin10px">';
		if(isset($_GET['section']) && $_GET['section'] == 'purchased') {
			$html .='<div class="mgm_content_back_link_div">'.
				'<a href="'.(($pagetype=='admin') ? admin_url('profile.php?page=mgm/membership/content') : mgm_get_custom_url('membership_contents')).'" class="button">'.__('Back','mgm').'</a>'.
				'</div>';
		}
		$html .= '<div class="mgm_content_total_post_div">'.
			sprintf(__('You have purchased a total of %d %s.','mgm'), $total_posts, ($total_posts == 1 ? __('Post', 'mgm'):__('Posts', 'mgm'))).
			'</div>';
		$html .='<div class="mgm_content_total_publish_div">';
		if(isset($_GET['section']) && $_GET['section'] == 'purchased') {
			$html .='<span class="pager">'.$purchased_posts['pager'].'</span>';
		//}elseif($total_post_rows > $total_posts) {
		//Do not show See All if number of records are <= $total_posts
		}elseif($total_posts > count($posts)) {
			$html .='<a href="'.(($pagetype=='admin') ? admin_url('profile.php?page=mgm/membership/content&section=purchased') : mgm_get_custom_url('membership_contents', false, array('section' => 'purchased'))) .'" class="button">'.__('See All','mgm').'</a>';
		}	
		$html .= '</div>';
		$html .='<br/><div class="clearfix"></div>';
		$html .='</div>';	
	}
	return $html;	
}

// purchasable contents
function mgm_member_purchasable_contents($pagetype = 'admin'){
	global $wpdb;
	// current_user
	$current_user = wp_get_current_user();
	// setting
	$setting = mgm_get_class('system')->get_setting();
	// snippet
	$snippet_length = 200;
	//  member
	$member     = mgm_get_member($current_user->ID);
	$arr_memberships = mgm_get_subscribed_membershiptypes($current_user->ID, $member);	
	// purchasable
	$purchasable_posts = mgm_get_membership_contents($arr_memberships, 'purchasable', $current_user->ID);	
	// posts
	$posts = $purchasable_posts['posts'];	
	// total posts
	$total_posts = $purchasable_posts['total_posts'];
	// total_post_rows
	$total_post_rows = $purchasable_posts['total_post_rows'];
	// init
	$html = $alt = '';
	
	
	// start output
	$html .= '<div class="table width100 br">'.
			
				'<div class="row br_bottom">'.
					'<div class="cell th_div width25 padding10px"><b>'.__('Post Title','mgm').'</b></div>'.
					'<div class="cell th_div width45 padding10px"><b>'.__('Post Content','mgm').'</b></div>'.
					//issue #920
					//'<div class="cell th_div width25"><b>'.__('Published','mgm').'</b></div>'.
					'<div class="cell th_div width15 padding10px"><b>'.__('Price','mgm').'</b></div>'.
					'<div class="cell th_div width15 padding10px"><b></b></div>'.
				'</div>';	

		// check	
		if($total_posts) {	
			$pattern = get_shortcode_regex();
			$currency = mgm_get_setting('currency');
			// loop
			foreach ($posts as $id=>$obj) {
				// check purchasable			
				$published = date('jS F Y', strtotime($obj->post_date));
				$title     = $obj->post_title;
				$content   = $obj->post_content;
				if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
					$title   = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($title);
					$content = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($content);
				}
				// strip_shortcodes
				$content = preg_replace('/'.$pattern.'/s', '', $content);
				$content  = substr(strip_tags($content), 0, $snippet_length);				
				$content .= (strlen($content) > $snippet_length ? '...':'');				
				$html .='<div class="row br_bottom '.($alt = ($alt=='') ? 'alternate': '').'">'.
						'<div class="cell width25 padding10px"><a href="'.get_permalink($obj->ID).'">'.$title.'</a></div>'.
						'<div class="cell width45 padding10px">'.$content.'</div>'.
						//issue #920
						//'<div class="cell width25 padding10px">'.$published.'</div>'.
						'<div class="cell width15 padding10px">'.$obj->purchase_cost. ' ' .$currency .'</div>'.
						'<div class="cell width15 padding10px"><a href="'.get_permalink($obj->ID).'" class="button">'.__('Buy','mgm').'</a></div>'.
						'</div>';
			}
		}else{
			$html .= '<div class="row '.($alt = ($alt=='') ? 'alternate': '').'">'.
					'<div class="cell mgm_text_align_center">'.__('No purchasable contents','mgm').'</div>'.
					'</div>';
		}	
		
		$html .='</div>';	
		
	if($total_posts > 0 ) {
		$html .='<div class="mgm_margin10px">';
		if(isset($_GET['section']) && $_GET['section'] == 'purchasable') {
			$html .='<div class="mgm_content_back_link_div">'.
				  '<a href="'.(($pagetype=='admin') ? admin_url('profile.php?page=mgm/membership/content'): mgm_get_custom_url('membership_contents')).'" class="button">'. __('Back','mgm').'</a>'.
				  '</div>';
		}
		$html .='<div class="mgm_content_total_post_div">'.
				sprintf(__('You have a total of %d premium %s you can purchase and access.','mgm'), $total_posts, ($total_posts == 1 ? __('Post', 'mgm'):__('Posts', 'mgm'))).
				'</div>';
		$html .='<div class="mgm_content_total_publish_div">';
		if(isset($_GET['section']) && $_GET['section'] == 'purchasable') {
			$html .='<span class="pager">'.$purchasable_posts['pager'].'</span>';
		//}elseif($total_post_rows > $total_posts) { 
		//Do not show See All if number of records are <= $total_posts
		}elseif($total_posts > count($posts)) { 
			$html .='<a href="'.(($pagetype=='admin') ? admin_url('profile.php?page=mgm/membership/content&section=purchasable') : mgm_get_custom_url('membership_contents', false, array('section' => 'purchasable') )).'" class="button">'.__('See All','mgm').'</a>';
		}
		$html .='</div>';	
		$html .='<br/><div class="clearfix"></div>';
		$html .='</div>';	
	}	
	return $html;	
}


/**
 * membership accessible/purchasable posts
 *
 * @since 2.0
 * @deprecated 2.6.0
 * @deprecated Use mgm_get_membership_contents()
 * @see mgm_get_membership_contents()
 *
 * @param array|string $membership_types
 * @param string $type ( accessible|purchasable)
 * @param int $user_id
 * @return array
 */
function mgm_get_membership_posts($membership_types, $type='accessible', $user_id=NULL){
	// deprecated
	_deprecated_function( __FUNCTION__, '2.6.0', 'mgm_get_membership_contents()' );
	// return
	return mgm_get_membership_contents($membership_types, $type, $user_id);
}

/**
 * membership accessible/purchasable contents 
 * 
 * @since 2.6.0
 *
 * @param array|string $membership_types
 * @param string $type ( accessible|purchasable )
 * @param int $user_id
 * @param string $posttype ( post|page|custom_post_type )
 * @param int $limit
 * @return array
 */
function mgm_get_membership_contents($membership_types, $type='accessible', $user_id=NULL, $post_type=NULL, $limit=NULL){
	global $wpdb;	
	// issue #920
	$user        = wp_get_current_user();	
	$temp_member = new stdClass();
	$extended_protection = mgm_get_class('system')->setting['content_hide_by_membership'];	

	// membership types
	if(!is_array($membership_types)) $membership_types = array($membership_types);
	
	// sql per page
	$limit_per_page = 50;
	$limit_clause   = '';
	// limit
	if(!$limit || !isset($_GET['section']) || (isset($_GET['section']) && $_GET['section'] != $type)){
		$limit_clause = 'LIMIT ' . $limit_per_page;
	}	
	// get types
	$post_types_in = ($post_type) ? mgm_map_for_in(array($post_type)) : mgm_get_post_types(true);	
	// from
	$sql_from = " FROM " . $wpdb->posts . " A JOIN " . $wpdb->postmeta . " B ON (A.ID = B.post_id ) 
			      WHERE post_status = 'publish' AND B.meta_key LIKE '_mgm_post%' AND post_type IN ({$post_types_in}) ";
				  
	// get count first
	$total_post_rows = $wpdb->get_var("SELECT COUNT(* ) AS total_post_rows {$sql_from}");
	
	// update limit if less posts availble
	if(!empty($limit_clause) && $total_post_rows > $limit_per_page){
		$limit_clause = 'LIMIT ' . $total_post_rows;
	}
	
	// get posts	
	$results = $wpdb->get_results("SELECT DISTINCT(ID), post_name, post_title, post_date, post_content {$sql_from} ORDER BY post_date DESC {$limit_clause}");		
	
	// for purchasable only, get purchased posts
	if($type == 'purchasable'){
		// sql
		$sql = $wpdb->prepare("SELECT `post_id` FROM `" . TBL_MGM_POST_PURCHASES . "` WHERE `user_id` = %d", $user_id );	
		// purchased	
		$purchased = $wpdb->get_results($sql);
		// init
		$purchased_posts = array();
		// check
		if (count($purchased) >0) {
			// loop
			foreach ($purchased as $id=>$obj) {	
				// set		
				$purchased_posts[] = $obj->post_id;				
			}
		}	
	}
	
	// init 
	$posts = array();
	
	// store
	if (count($results) >0) {
		// set counter		
		$total_posts 	= 0;
		// per page
		$posts_per_page = 5;
		// loop
		foreach ($results as $id=>$obj) {
			// post object
			$post_obj = mgm_get_post($obj->ID);
			//access delay - issue #920
			$access_delay = $post_obj->access_delay;
			// post access membership types		
			$access_membership_types = $post_obj->get_access_membership_types();			
			//issue #1376
			$post_category_access_membership_types = mgm_get_post_category_access_membership_types($obj->ID);
			//merging category access/ post accesss
			$access_membership_types = array_merge($access_membership_types,$post_category_access_membership_types);
			//gettign unique access members
			$access_membership_types = array_unique($access_membership_types);
			
			// branch
			switch($type){
				case 'accessible':
					// multiple membership level purchase(issue#: 400) modification
					if(array_diff($access_membership_types, $membership_types) != $access_membership_types){ //if any match found
						
						// issue #920
						$access = true; 
						if($extended_protection =='Y'){
							$temp_member->membership_type = $membership_types[0];
							if(mgm_check_post_access_delay($temp_member, $user, $access_delay)){
								//okey
							}else {
								$access = false; 								
							}
						}
						if($access){
							// increment
							$total_posts++;
							// store
							if( ($limit != '' && $total_posts <= $posts_per_page) || $limit == ''  ) $posts[] = $obj;
						}
					}
				break;
				case 'purchasable':					
					// multiple membership level purchase(issue#: 400) modification
					if(bool_from_yn($post_obj->purchasable) && array_diff($access_membership_types, $membership_types) == $access_membership_types){//if no match
						// not purchased
						if(!in_array($obj->ID, $purchased_posts)){
							// issue #920
							$access = true; 
							if($extended_protection =='Y'){
								$temp_member->membership_type = $membership_types[0];
								if(mgm_check_post_access_delay($temp_member, $user, $access_delay)){
									//okey
								}else {
									$access = false; 								
								}
							}							
							if($access){							
								// increment
								$total_posts++;
								// store
								if( ($limit!='' && $total_posts <= $posts_per_page) || $limit == ''  ) {
									// fetch post price								
									$obj->purchase_cost = mgm_convert_to_currency($post_obj->purchase_cost);
									// store
									$posts[] = $obj;
								}
							}
						}
					}
				break;
			}
			// unset			
			unset($post_obj);			
		}
	}
	// reset total
	if(empty($posts)) $total_posts = 0;
	// pager 
	$pager = '';
	/*if($total_post_rows > $limit_per_page){	
		$pager 	= sprintf('<a href="%s">%s</a>', mgm_get_custom_url('membership_contents', false, array('page'=>2)), __('next','mgm'));
	}*/
	// return 			
	return array( 'total_posts'=> $total_posts, 'posts'=>$posts, 'total_post_rows' => $total_post_rows, 'pager'=>$pager);
}

// member purchased posts
function mgm_get_purchased_posts($user_id){
	global $wpdb;	
	$total_limit = 20;
	$per_page 	 = 5;
	// limit
	if(isset($_GET['section']) && $_GET['section'] == 'purchased'){
		$limit = 'LIMIT '.$total_limit;
	}else{
		$limit = 'LIMIT '.$per_page;
	}
	
	
	// sql
	$sql = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS A.ID,post_title,post_date,post_content,purchase_dt FROM `{$wpdb->posts}` A 
	       					JOIN `" . TBL_MGM_POST_PURCHASES . "` B ON(A.ID=B.post_id) WHERE user_id = %d ORDER BY purchase_dt DESC ".$limit, $user_id );
	// echo $sql;		
	$results = $wpdb->get_results($sql);
	
	// total
	//$total = $wpdb->get_var("SELECT FOUND_ROWS() AS total_rows");
	$total = $wpdb->get_var($wpdb->prepare("SELECT count(B.id) as count FROM `{$wpdb->posts}` A 
	        								JOIN `" . TBL_MGM_POST_PURCHASES . "` B ON(A.ID=B.post_id) WHERE user_id = %d ORDER BY purchase_dt DESC LIMIT %d", $user_id, $total_limit ));
	
	// init 
	$posts = array();
	
	// store
	if (count($results) >0) {
		foreach ($results as $id=>$obj) {			
			$posts[$obj->ID] = $obj;				
		}
	}		
	// return 	
	return array('posts'=>$posts,'total_posts'=>$total, 'pager'=>'');
}

// get next drip feed
function mgm_get_next_drip_feed(){
	// get current user
	$current_user = wp_get_current_user();
	// mgm member object
	if($current_user->ID){
		$member = mgm_get_member($current_user->ID);
	}	
}
//membership contents
function mgm_membership_contents() {

	global $user_ID,$current_user;		
	
	$html = '';

	$css_group = mgm_get_css_group();		
	//issue #867
	if($css_group !='none') {
		//expand this if needed
		$css_link_format = '<link rel="stylesheet" href="%s" type="text/css" media="all" />';				
		$css_file = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.pages.css';
		$html .= sprintf($css_link_format, $css_file);
	}

	if($user_ID) {
		$section = isset($_GET['section']) ? strip_tags($_GET['section']) : 'all';
		$html .= '<div>';
		//accessible contents
		if(in_array($section, array('all','accessible'))) {
			$arr_mtlabel = mgm_get_subscribed_membershiptypes_with_label($user_ID);					
			$html .= '<div class="postbox mgm_margin10px0px" >'.
					'<h3>'.sprintf(__('Your Membership Level "%s" Accessible Contents','mgm'), mgm_stripslashes_deep(implode(', ',$arr_mtlabel))).'</h3>'.
					'<div class="inside">'.
					mgm_member_accessible_contents('user').
					'</div>'.
					'</div>';
		}
		//already purchased contents
		if(in_array($section, array('all','purchased'))) {
			$html .= '<div class="postbox mgm_margin10px0px">'.
					 '<h3>'.__('Purchased Contents','mgm') . '</h3>'.
					 '<div class="inside">'.mgm_member_purchased_contents('user') .'</div></div>';
		}
		//purchasable contents
		if(in_array($section, array('all','purchasable'))) {
			$html .= '<div class="postbox mgm_margin10px0px">'.
					'<h3>'. __('Purchasable Contents','mgm') . '</h3>'.
					'<div class="inside">'.
					mgm_member_purchasable_contents('user') .
					'</div>' .
					'</div>' ;
		}
		//purchased postpacks
		if(in_array($section, array('all','purchased_postpacks'))) {
			$html .= '<div class="postbox mgm_margin10px0px">'.
					'<h3>'. __('Purchased Post Packs','mgm') . '</h3>'.
					'<div class="inside">'.
					mgm_member_purchased_postpacks('user') .
					'</div>' .
					'</div>' ;
		}		
		
		//purchasable postpacks 
		if(in_array($section, array('all','purchasable_postpacks'))) {
			$html .= '<div class="postbox mgm_margin10px0px">'.
					'<h3>'. __('Purchasable Post Packs','mgm') . '</h3>'.
					'<div class="inside">'.
					mgm_member_purchasable_postpacks('user') .
					'</div>' .
					'</div>' ;
		}
					
		$html .= '</div>';
		
	}else {
		$template = mgm_get_template('private_text_template', array(), 'templates');		
		$html = 'You need to be logged in to access this page.';
		$html .= sprintf(__(' Please <a href="%s"><b>login</b> here.</a>','mgm'), mgm_get_custom_url('login', false, array('redirect_to' => get_permalink($post->ID) )));
		$html = str_replace('[message]', $html, $template);
	}
	$html = apply_filters('mgm_membership_contents_html',$html);
	//issue #1635
	$membership_contents_html = '<div class="mgm_membership_contents_container">'.$html.'</div>';	
	// return 	
	return $membership_contents_html;
}
//fetch posts for membership level
function mgm_get_posts_for_level($membership_type = '', $show_all = true) {
	global $wpdb, $post;	
	if(!empty($membership_type)) {
		if(!is_array($membership_type))
			$membership_type = array(0 => $membership_type);
		// get post types
		$post_types_in = mgm_get_post_types(true);
		// id
		$post_id_notin = (is_numeric($post->ID)) ? $post->ID : 0 ; 
		// sql 	
		$limit = 50;
		$per_page = 10;			
		$sql = "SELECT DISTINCT(ID), post_title, post_date, post_content
				FROM " . $wpdb->posts . " A JOIN " . $wpdb->postmeta . " B ON (A.ID = B.post_id ) 
				WHERE post_status = 'publish' AND B.meta_key LIKE '_mgm_post%' 
				AND post_type IN ({$post_types_in}) AND A.id NOT IN($post_id_notin) 
				ORDER BY post_date DESC LIMIT 0,".$limit;					
		// get posts	
		$results = $wpdb->get_results($sql);	
		// chk
		if ( count($results) > 0 ) {
			// set counter		
			$total 		= 0;			
			// loop
			foreach ($results as $id=>$obj) {
				// post
				$post_obj = mgm_get_post($obj->ID);
				$access_types = $post_obj->get_access_membership_types();
				$found = false;
				if(!empty($access_types)) {
					foreach ($access_types as $type) {
						if(in_array($type, $membership_type)){
							$membership = mgm_get_class('membership_types');
							$obj->access_membership_type = $membership->get_type_name($type);							
							$found = true;
							$total++;
							break;
						}
					}
					if($found && ( (isset($_GET['show']) && $_GET['show'] == 'all' || $show_all) || $total <= $per_page))
						$posts[] = $obj;
				}
				// branch				
								
			}						
			return array('posts' => $posts, 'total' => $total);
		}
	}
	return array();
}
//display posts for membership level
function mgm_posts_for_membership($membership_type = '') {

	
	$posts = mgm_get_posts_for_level($membership_type, false);		
	
	$membership_type = (is_array($membership_type)) ? $membership_type : array(0 => $membership_type);
	$levels = '';
	if(!empty($membership_type)) {
		$i = 0;
		$cnt = count($membership_type);
		foreach ($membership_type as $key => $lvl) {
			$sep = '';
			if($i > 0 && $i == $cnt -1)
				$sep = ' and ';
			elseif ($i > 0 )
				$sep = ', ';	
			$membership = mgm_get_class('membership_types');
			$levels .= $sep . '"'.mgm_stripslashes_deep($membership->get_type_name($lvl)).'"';
			$i++;
		}
	}	

	$html = '';

	$css_group = mgm_get_css_group();		
	//issue #867
	if($css_group !='none') {
		//expand this if needed
		$css_link_format = '<link rel="stylesheet" href="%s" type="text/css" media="all" />';				
		$css_file = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.pages.css';
		$html .= sprintf($css_link_format, $css_file);
	}

	
	$html .= '<div class="postbox mgm_margin10px0px">'.
				'<h3>'.sprintf(__('Accessible Contents For %s','mgm'), $levels).'</h3>'.
				'<div class="inside">';	
							
	$html .= '<div class="table width100">'.
				'<div class="row br_bottom">'.
					'<div class="cell th_div width25"> '.__('Post Title','mgm') . '</b></div>'.
					'<div class="cell th_div width45"><b>'.__('Post Content','mgm') .'</b></div>'.
					'<div class="cell th_div width15"><b>'.__('Published','mgm') .'</b></div>'.
					'<div class="cell th_div width15"><b>'.__('Membership Type','mgm') .'</b></div>'.				
				'</div>';
		
	if(isset($posts['total']) && $posts['total'] > 0) {		
		$pattern = get_shortcode_regex();
		$snippet_length = 200;
		foreach ($posts['posts'] as $id=>$obj) {
			// check purchaseble			
			$published = date('jS F Y', strtotime($obj->post_date));
			$title     = $obj->post_title;
			$content   = $obj->post_content;
			if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
				$title   = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($title);
				$content = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($content);
			}				
			$content = preg_replace('/'.$pattern.'/s', '', $content);
			$content  = substr(strip_tags($content), 0, $snippet_length);				
			$content .= (strlen($content) > $snippet_length ? '...':'');				
			$html .='<div class="row br_bottom '.($alt = ($alt=='') ? 'alternate': '').'">'.
					'<div class="cell width25"><a href="'.get_permalink($obj->ID).'">'.$title.'</a></div>'.
					'<div class="cell width45">'.$content.'</div>'.
					'<div class="cell width15">'.$published.'</div>'.					
					'<div class="cell width15">'.mgm_stripslashes_deep($obj->access_membership_type).'</div>'.									'</div>';
		}
				
	}else{
		$html .= '<div class="row br_bottom '.($alt = ($alt=='') ? 'alternate': '').'">'.
				'<div class="cell">'.__('No posts available','mgm').'</div>'.
				'</div>';
	}
	
	$html .='</div>';	
	
	if(isset($posts['total']) && $posts['total'] > 0) {	
		$html .= '<div class="mgm_margin10px">';
		if(isset($_GET['show']) && $_GET['show'] == 'all') {
			$html .= '<div class="mgm_content_back_link_div">'.
					'<a href="'. (add_query_arg(array('show' => 'paged'),mgm_current_url())). '" class="button">'.__('Back','mgm') .'</a>'.
					'</div>';
		}
		$html .= '<div class="mgm_content_total_post_div">'.
				sprintf(__('Total Accessible Contents: %d','mgm'), $posts['total']).
				'</div>';
		$html .='<div class="mgm_content_total_publish_div">';		
		if((!isset($_GET['show']) || (isset($_GET['show']) && $_GET['show'] == 'paged')) && $posts['total'] > count($posts['posts']) )
			$html .= '<a href="'.(add_query_arg(array('show' => 'all'),mgm_current_url())).'" class="button">'.__('See All','mgm').'</a>';
		
		$html .='</div>';	
		$html .='<br/><div class="clearfix"></div>';
		$html .='</div>';	
	}
				
	$html .= '</div>'.
			 '</div>';
			 
	$html = apply_filters('mgm_posts_for_membership_html', $html);		 
			 
	return $html;
}
//generate scripts for custom photo upload
function mgm_upload_script_js($form_id, $images) {
	if(is_super_admin())
		$upload_url = mgm_get_custom_url('profile');
	else {
		//the below url doesn't exist but will capture file_upload query
		$upload_url = trailingslashit(site_url()) .'upload';
	}
	$upload_url = add_query_arg(array('file_upload'=>'image'), $upload_url); 
	$user = wp_get_current_user();	
	$field_name = $images[0];
	$field_type = ($user->ID > 0) ? 'profile' : 'register';

	$js = 'jQuery(document).ready(function(){';
	
	$js .= 'jQuery( "#'.$form_id.'").attr( "enctype", "multipart/form-data" );';
 	$js .= 'jQuery( "#'.$form_id.'").attr( "encoding", "multipart/form-data" );';	 	
	//$js .= 'jQuery("#uploader_loading").hide();';
 	for ($i=0;$i< count($images);$i++){
		$js .= 'jQuery("#uploader_loading_'.$images[$i].'").hide();';
	}	
	 // mgm_profile_photo_upload
	$js .= 'mgm_profile_photo_upload = function(obj) {'."\n".		 	
			//'if(jQuery(obj).val().toString().is_empty()==false){'."\n".					
				'if(!(/\.(png|jpe?g|gif)$/i).test(jQuery(obj).val().toString())){'."\n".
					'alert(\''.__('Please upload only gif,jpg and png files', 'mgm').'\');'."\n".
					'return;'."\n".
				'}'."\n".

				'var field_id = jQuery(obj).attr("id");'."\n".
				'var field_name_arr = jQuery(obj).attr("name").split("[");'."\n".
				'var field_name_arr = field_name_arr[1].split("]");'."\n".			
				'var field_name ="'.$field_name.'";'."\n".
				'var field_images = "'.implode(",",$images).'";'."\n".
				'var arr = field_images.split(",");'."\n".
				
				'var length = arr.length,element = null;'."\n".
				'for (var i = 0; i < length; i++) {'."\n".
					'if(arr[i] == field_name_arr[0]){'."\n".
						'field_name =field_name_arr[0];'."\n".
						' break;'."\n".
					'}'."\n".
				'}'."\n".				
				'var field_type = "'.$field_type.'";'."\n".
				'var fid =id="mgm_'.$field_type.'_field_"+field_name+"_hidden";'."\n".
				'var fname = "mgm_'.$field_type.'_field["+field_name+"]";'."\n".
				'var uid = "uploader_loading_"+field_name;'."\n".			
				// process upload 		
				// vars													
				//'var form_id = jQuery(jQuery(obj).get(0).form).attr(\'id\');'."\n".					
				'jQuery("#"+uid).show();'."\n".	
				// upload 				
				'jQuery.ajaxFileUpload({'."\n".						
						'url:\''. ($upload_url) .'\','."\n". 
						'secureuri:false,'."\n".
						'fileElementId:jQuery(obj).attr(\'id\'),'."\n".
						'dataType: \'json\','."\n".						
						'success: function (data, status){'."\n".	
							// uploaded					
							'if(data.status==\'success\'){'."\n".
								'jQuery("#"+uid).hide();'."\n".																	
								// change file								
								'var cont_obj = jQuery("#'.($form_id).' :file[name=\'"+jQuery(obj).attr(\'name\')+"\']").parent();'."\n".								
								'cont_obj.fadeOut();'."\n".
								'setTimeout(function(){'."\n".
									//remove
									'var html =\'&lt;img style="width:\'+data.upload_file.width+\'px;" src="\'+data.upload_file.file_url+\'"&gt;\';'."\n".
									'html +=\'&lt;input type="hidden" id="\'+fid+\'" name="\'+fname+\'" value="\'+data.upload_file.file_url+\'"&gt;\';'."\n".
									'html +=\'&nbsp;&lt;span onclick=delete_upload(this,"\'+data.upload_file.hidden_field_name+\'","\'+field_name+\'")&gt;\';'."\n".
									'html +=\'&lt;img style="cursor:pointer;" src="'.MGM_ASSETS_URL . '/images/icons/cross.png" alt="'.__('Delete','mgm').'" title="'.__('Delete','mgm').'"&gt;&lt;/span&gt;\';'."\n". 
									//'html +='."\n".	
									'cont_obj.html(html);'."\n".																
									'cont_obj.html(cont_obj.text());'."\n".//convert to html characters																
									'//alert(data.message);'."\n".
									'cont_obj.fadeIn();'."\n".
								'},\'300\');'."\n".	
								//temp message:
																								
							'}else{'."\n".
								'jQuery("#"+uid).hide();'."\n".
								'mgm_file_uploader("#'.($form_id).'", mgm_profile_photo_upload);'."\n".
								'alert(data.message);'."\n".
							'}'."\n".						
						'},'."\n".
						'error: function (data, status, e){'."\n".
							'jQuery("#"+uid).hide();'."\n".
							'mgm_file_uploader("#'.($form_id).'", mgm_profile_photo_upload);'."\n".
							'alert(\''.__('Error occured in upload','mgm').'\');'."\n".							
						'}'."\n".
					'}'."\n".
				')'."\n".		
				// end
		//	'}'."\n".			 
		 '}'."\n";		
	 // bind uploader	 
	$js .= 'mgm_file_uploader("#'.($form_id).'", mgm_profile_photo_upload);'."\n";
		 
	$js .= 'delete_upload = function(container, hidden_field_name,field_name){'."\n".
		   'var fid = "mgm_'.$field_type.'_field_"+field_name;'."\n".
		   'var fhid = "mgm_'.$field_type.'_field_"+field_name+"_hidden";'."\n".
		   'var fname = "mgm_'.$field_type.'_field["+field_name+"]";'."\n".
		   'var uid = "uploader_loading_"+field_name;'."\n".		   
		   'var obj_parent = jQuery(container).parent();'."\n".						
		   'obj_parent.fadeOut();'."\n".
		   'setTimeout(function(){'."\n".
		   'var html = \'&lt;input type="file" class="mgm_field_file" id="\'+fid+\'" name="\'+fname+\'"&gt;&lt;input type="hidden" id="\'+fhid+\'" name="\'+fname+\'" value=""&gt;&nbsp;&lt;img id="\'+uid+\'" src="'.esc_url( admin_url( 'images/wpspin_light.gif' ) ).'" alt="'.__('Loading','mgm').'" title="'.__('Loading','mgm').'"&gt;\''."\n".
		   'obj_parent.html(html);'."\n".				
		   'obj_parent.html(obj_parent.text());'."\n".	//convert to html characters				
		   'mgm_file_uploader("#'.($form_id).'", mgm_profile_photo_upload);'."\n".
		   'jQuery("#"+uid).hide();'."\n".				
		   'obj_parent.fadeIn();'."\n".
		   '},\'300\');'."\n".				
		   '}'."\n";		
	$js .= '});';	
	return "\n".'<script type="text/javascript">'."\n".$js."\n".'</script>';
}

/**
 * get custom logout link
 *
 * @param string $label
 * @param bool $return
 * @return string $url
 */
function mgm_logout_link($label, $return = true) {
	// logged in user:	
	$user = wp_get_current_user();
	// if no login
	if(!isset($user->ID) || (isset($user->ID) && $user->ID == 0 ) ) return "";
	// label	
	if(empty($label)) $label = __('Logout', 'mgm');	
	// logout url
	$logout_url = mgm_logout_url(wp_logout_url(), '');
	// logout link
	$logout_link = sprintf('<a href="%s">%s</a>', $logout_url, $label);	
	// return
	if($return) return $logout_link;
	// print otherwise 
	print $logout_link;	
}
/**
 * Membership extend link
 *
 * @param string $label : link lable
 * @param boolean $return: whether return the link or echo
 * @return the link
 */
function mgm_membership_extend_link($label, $return = true) {
	//default label
	if(empty($label))
		$label = __('Extend', 'mgm');
	
	$extend_link = "";
	//logged in user:	
	$user = wp_get_current_user();
	
	if(!isset($user->ID) || (isset($user->ID) && $user->ID == 0 ) || is_super_admin())
		return "";
		
	$subscription_packs = mgm_get_class('subscription_packs');	
	$member  = mgm_get_member($user->ID);	
	$pack_id     = $member->pack_id;
	if($pack_id) {
		$pack        = $subscription_packs->get_pack($member->pack_id); 
		$num_cycles = (isset($member->active_num_cycles) && !empty($member->active_num_cycles)) ? $member->active_num_cycles : $pack['num_cycles'] ;
		// check cycles	
		if($num_cycles > 0 && mgm_pack_extend_allowed($pack)) {
			$extend_link = '<a href="'. mgm_get_custom_url('transactions',false,array('action' => 'extend', 'pack_id'=>$pack_id, 'username' => $user->user_login)) . '">' . $label . '</a>';
		}
	}
	//if return
	if($return)
		return $extend_link;
	else 
		echo $extend_link;	 			 
}

/**
 * add google analytics on transaction page
 *
 * @param array $transaction
 * @return string $html
 */
function mgm_add_google_analytics_after_payment_processed($trans) {
	global $mgm_scripts;
	// obj
	$system_obj = mgm_get_class('system');
	// init
	$html = '';
	// check
	if( ! bool_from_yn( $system_obj->get_setting('enable_googleanalytics') ) 
		|| trim( $system_obj->get_setting('googleanalytics_key') ) == '' // check settings
		|| in_array($trans['module'], array('free', 'trial'))            // skip free/trial transactions
		|| $trans['status'] != MGM_STATUS_ACTIVE			             // skip failed/incomplete transactions	
	)
	// return	
	return $html;
	
	// type
	if($trans['payment_type'] == 'subscription_purchase') {
		$cost = $trans['data']['trial_on'] ? $trans['data']['trial_cost'] : $trans['data']['cost'];
		$sku_id = (isset($trans['data']['id'])) ? $trans['data']['id'] : $trans['data']['pack_id']; 
	}else {
		//incomplete
		$cost = $sku_id = '';
	}
	
	// name
	$name = mgm_stripslashes_deep( isset($trans['data']['item_name']) ? $trans['data']['item_name'] : $system_obj->get_subscription_name($trans['data']));
	// category
	$category = ucfirst(str_replace('_', ' ', $trans['payment_type']));

	// issue #1316	
	$html .= "\n".'<script type="text/javascript">'."\n".
				//'try {'."\n".
					'var _gaq = _gaq || [];'."\n".					
					'_gaq.push(["_setAccount", "'.$system_obj->get_setting('googleanalytics_key').'"]);'."\n".
					
					'_gaq.push(["_trackPageview"]);'."\n".
					
					'_gaq.push(["_addTrans",'.
					  '"' .$trans['id']. '", '.            // order ID - required
					  '"' .get_option('blogname'). '",'.  // affiliation or store name
					  '"' .$cost. '",'.                   // total - required
					  '"",'.                              // tax
					  '"",'.                              // shipping
					  '"",'.                              // city
					  '"",'.                              // state or province
					  '""'.                               // country
					  ']);'."\n".
					  
					'_gaq.push(["_addItem",'.
					  '"' .$trans['id']. '",'.             // order ID - required
					  '"' .$sku_id. '",'.                 // SKU/code
					  '"' .$name. '",'.                   // product name
					  '"' .$category. '",'.               // category or variation
					  '"' .$cost. '",'.                   // unit price - required
					  '"1"'.                              // quantity - required
					']);'."\n".
										
					'_gaq.push(["_trackTrans"]);'."\n".

				//'} catch(err) {}'."\n\n".
	
	
				 '(function() {'."\n".
				    'var ga = document.createElement("script"); ga.type = "text/javascript"; ga.async = true;'."\n".
				    'ga.src = ("https:" == document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js";'."\n".
				    'var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ga, s);'."\n".
				  '})();'."\n\n".	
					
				'</script>'."\n";
	
	// return
	return $html;
}
// add
add_filter( 'mgm_payment_processed_page_analytics', 'mgm_add_google_analytics_after_payment_processed', 10);
/**
 * membership accessible/purchasable contents 
 * 
 * @since 2.6.0
 *
 * @param array|string $membership_types
 * @param int $user_id
 * @param string $taxonomy ( category|post_tag|custom_taxonomy )
 * @return array
 */
function mgm_get_membership_taxonomies($membership_types, $user_id=NULL, $taxonomy='category'){
	global $wpdb;	
	// membership types
	if(!is_array($membership_types)) $membership_types = array($membership_types);		
	// term
	$post_terms = mgm_get_class('post_'.($taxonomy=='category' ? 'category' : 'taxonomy'));	
	// init
	$taxonomies = array();				
	// loop set				
	foreach($post_terms->get_access_membership_types() as $term_id=>$access_membership_types ) {		
		// check
		if($access_membership_types){ 			
			// multiple level membership
			if(array_diff($access_membership_types, $membership_types) != $membership_types){
				// name
				$term = get_term( $term_id, $taxonomy );
				// store
				$taxonomies[] = array('id'=>$term_id, 'name'=>$term->name);
			}
		}			
	}
	
	// reset total
	if(empty($taxonomies)) $total_taxonomies = 0;
	
	// return 			
	return array( 'total_taxonomies'=> $total_taxonomies, 'taxonomies'=>$taxonomies);
}	

//user payment history
function mgm_user_payment_history() {

	global $user_ID,$current_user, $wpdb;

	$html = '';

	$css_group = mgm_get_css_group();		
	//issue #867
	if($css_group !='none') {
		//expand this if needed
		$css_link_format = '<link rel="stylesheet" href="%s" type="text/css" media="all" />';				
		$css_file = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.pages.css';
		$html .= sprintf($css_link_format, $css_file);
	}
	$data =array();
	
	if($user_ID) {

		//$user_ID =258;
		
		$pattern_one = '"user_id":'.$user_ID.',';
		$pattern_two = '"user_id":"'.$user_ID.'",';

		//payment success check
		$pay_succ = " AND `status_text` =  'Last payment was successful' ";
		
		$user_check = "(`data` LIKE '%".$pattern_one."%' OR `data` LIKE '%".$pattern_two."%')";
		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM `".TBL_MGM_TRANSACTION."` WHERE `module` IS NOT NULL AND (".$user_check.") ".$pay_succ." ORDER BY `transaction_dt` DESC";
		
		$data['transactions'] = $wpdb->get_results($sql);

		$html .= '<div class="postbox mgm_margin10px0px"><h4>'.__("Payment History","mgm") . '</h4></div>';
		
		$html .= '<div>';

		$html .= '<div class="table mgm_payment_history_container">
			<div class="row br_bottom">
				<div class="cell th_div width50px maxwidth50px mgm_text_align_left"><b>'. __("S.No","mgm") .'</b></div>
				<div class="cell th_div width140px maxwidth140px mgm_text_align_left"><b>'. __("Type","mgm") .'</b></div>
				<div class="cell th_div width140px maxwidth140px mgm_text_align_left"><b>'. __("Module","mgm") .'</b></div>
				<div class="cell th_div width100px maxwidth100px mgm_text_align_left"><b>'. __("Amount","mgm") .'</b></div>					
				<div class="cell th_div width100px maxwidth100px mgm_text_align_left"><b>'. __("Transaction Date","mgm") .'</b></div>
			</div>';
		
		if(count($data['transactions'])>0): 
			$i=1;
			foreach($data['transactions'] as $tran_log):
				$html .= '<div class="row br_bottom">';
					$json_decoded = json_decode($tran_log->data);
			   		//$user_obj = $data[$json_decoded->user_id];
			   		//echo $user_obj->user_login;
			   		
				$html .= '
					<div class="cell width50px maxwidth50px mgm_text_align_left">'. ucwords($i++).' </div>
			   		<div class="cell width140px maxwidth140px mgm_text_align_left">'. ucwords(str_replace("_"," ",$tran_log->payment_type)).' </div>
			   		<div class="cell width140px maxwidth140px mgm_text_align_left">'.ucwords($tran_log->module).'</div>
			   		<div class="cell width100px maxwidth100px mgm_text_align_left">'.$json_decoded->cost.' </div>
			   		<div class="cell width100px maxwidth100px mgm_text_align_left">'. date(MGM_DATE_FORMAT_SHORT, strtotime($tran_log->transaction_dt)).'  </div>
					</div> ';  
			endforeach; 
		else:
			$html .= '<div class="row br_bottom"><div class="cell mgm_text_align_center">'. __("No transactions found..!","mgm").'</div></div>';
		endif;
		$html .= '</div>';

		$html .= '</div>';
		
	}else {
		$template = mgm_get_template('private_text_template', array(), 'templates');		
		$html = 'You need to be logged in to access this page.';
		$html .= sprintf(__(' Please <a href="%s"><b>login</b> here.</a>','mgm'), mgm_get_custom_url('login', false, array('redirect_to' => get_permalink($post->ID) )));
		$html = str_replace('[message]', $html, $template);
	}
	$html = apply_filters('mgm_user_payment_history_html',$html);

	return $html;
}

/**
 * generate members list - short code content
 */
function mgm_generate_member_list($args=array()){
	global $wpdb, $post;	
	
	//issue #1327
	$show_level  = (isset($args['show_level'])) ? $args['show_level'] : null;
	
	$show_level_members = array();
	
	if(!empty($show_level) && $show_level != null) {
		
		$show_level = explode(',',$show_level);
		
		$show_level_count = count($show_level);

		for ($i=0; $i < $show_level_count ; $i++) {
			
			$level_match_members = mgm_get_members_with('membership_type', $show_level[$i]);
			
			if(!empty($level_match_members))
				$show_level_members = array_merge($show_level_members,$level_match_members);
		}
		
		if(!empty($show_level_members)) { 
			$show_level_members = array_unique($show_level_members);
		}
	}
	
	// current url	
	$current_url = get_permalink($post->ID);
	// append ? why?
	// if( !strpos($current_url,'?') !== false) $current_url = ($current_url . '?');
	
	// echo $current_url;
	// init pager
	$pager = new mgm_pager();
	// init data
	$data = $custom_search_fields = $custom_sort_fields = $custom_user_list = $user_list = array();
	// css group	
	$css_group = mgm_get_css_group();
	// html
	$html ='';
	// check
	if($css_group != 'none') {
		// expand this if needed
		$css_link_format = '<link rel="stylesheet" href="%s" type="text/css" media="all" />';				
		$css_file = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.pages.css';
		$html .= sprintf($css_link_format, $css_file);
	}
	// search fields
	$data['search_fields'] = array(''=> __('Select','mgm'), 'username'=> __('Username','mgm'), 
								   'id'=> __('User ID','mgm'), 'email'=> __('User Email','mgm'), 
	                               'first_name' => __('First Name','mgm') ,'last_name' => __('Last Name','mgm'), 
								   'membership_type'=> __('Membership Type','mgm'), 'reg_date'=> __('Registration Date','mgm'), 
								   'last_payment'=> __('Last Payment','mgm'), 'expire_date'=> __('Expiration Date','mgm'), 
								   'fee'=> __('Fee','mgm'), 'status'=> __('Status','mgm') );	
	
	// sort fields							  
	$data['sort_fields'] = array('id'=> __('User ID','mgm'),'username'=> __('Username','mgm'), 'email'=> __('User Email','mgm'),
	                             'reg_date'=> __('Registration Date','mgm'));								   
	
	// order fields							  
	$data['order_fields'] = array('desc'=> __('DESC','mgm'),'asc'=> __('ASC','mgm'));								   
		
	// custom_fields
	$custom_fields = mgm_get_class('member_custom_fields');
	
	// getting custom fileds and skip the search fields if allready exists.
	foreach ($custom_fields->custom_fields as $custom_field) {
		if (!array_key_exists($custom_field['name'],$data['search_fields'])){
			$custom_search_fields[$custom_field['name']] = $custom_field['label'] ;
		}
	}

	// getting custom fileds and skip the sort fields if allready exists.
	foreach ($custom_fields->custom_fields as $custom_field) {
		if (!array_key_exists($custom_field['name'],$data['sort_fields'])){
			if ($custom_field['name']!='status')
				$custom_sort_fields[$custom_field['name']] = $custom_field['label'] ;
		}
	}
	
	// filter
	$sql_filter = $data['search_field_name'] = $data['search_field_value'] = '';
	
	// field value
	if(isset($_REQUEST['query']))
		$search_field_value = $_REQUEST['query'];
	else 
		$search_field_value = '';
	
	// field name
	if(isset($_REQUEST['by']))
		$search_field_name = $_REQUEST['by'];
	else 
		$search_field_name = '';
	
	// sort field
	if(isset($_REQUEST['sort_field']))
		$sort_field_name = $_REQUEST['sort_field'];
	else 
		$sort_field_name = '';
	
	// sort order type //order_type
	if(isset($_REQUEST['sort_order']))
		$sort_order_type = $_REQUEST['sort_order'];// change
	else 
		$sort_order_type = '';	

	// members
	$active_members = mgm_get_members_with('status', MGM_STATUS_ACTIVE);// wrongly called,use constant
					
	// check
	if(!empty($search_field_name)) {	
		// clean	
		$search_field_value = $wpdb->escape($search_field_value);// for sql
		$search_field_name = $wpdb->escape($search_field_name);// for sql	
		
		// view data	
		$data['search_field_name'] 	= $search_field_name;
		$data['search_field_value'] = trim($search_field_value);
		
		// current date
		$curr_date = mgm_get_current_datetime();
		$current_date = $curr_date['timestamp'];		
		
		// check
		if(array_key_exists($search_field_name,$custom_search_fields)) {
			// members
			$members = mgm_get_members_with_customfiled($search_field_name, $search_field_value);			
			//issue #1327
			if(!empty($show_level_members)){
				$members = array_intersect($show_level_members,$members);							
			}			
			//check
			$members_in = (count($members)==0) ? 0 : (implode(',', $members));
			// set filter
			$sql_filter = " AND `ID` IN ({$members_in})";	
		} else {		
			// by field
			switch($search_field_name){
				case 'username':
					// issue#: 347(LIKE SEARCH)					
					$filter = " AND `user_login` LIKE '%{$search_field_value}%'";	
					// matched
					$matched_members = mgm_get_members_with_sql_filter($filter);
					//issue #1327
					if(!empty($show_level_members)){
						$matched_members = array_intersect($show_level_members,$matched_members);							
					}
					// common
					$members = array_intersect($active_members,$matched_members);							
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";							
				break;	
				case 'id':
					// filter
					$filter = " AND `ID` = '".(int)$search_field_value."'";
					// match
					$matched_members = mgm_get_members_with_sql_filter($filter);	
					//issue #1327
					if(!empty($show_level_members)){
						$matched_members = array_intersect($show_level_members,$matched_members);							
					}										
					// common
					$members = array_intersect ($active_members,$matched_members);							
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";						
				break;
				case 'email':
					// issue#: 347(LIKE SEARCH)
					$filter = " AND `user_email` LIKE '%{$search_field_value}%'";
					// match
					$matched_members = mgm_get_members_with_sql_filter($filter);	
					//issue #1327
					if(!empty($show_level_members)){
						$matched_members = array_intersect($show_level_members,$matched_members);							
					}					
					// common
					$members = array_intersect ($active_members,$matched_members);
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";								
				break;	
				case 'membership_type':
					// match
					$matched_members = mgm_get_members_with('membership_type', $search_field_value);
					//issue #1327
					if(!empty($show_level_members)){
						$matched_members = array_intersect($show_level_members,$matched_members);							
					}					
					// common
					$members = array_intersect ($active_members,$matched_members);
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";			
				break;	
				case 'reg_date':
					// check
					if(empty($search_field_value)){
						$search_field_value = date('Y-m-d',$current_date);
					}
					// convert 
					$search_field_value = mgm_format_inputdate_to_mysql($search_field_value);	
					// set filter				
					$filter = " AND DATE_FORMAT(`user_registered`,'%Y-%m-%d') = '{$search_field_value}'";
					// match
					$matched_members = mgm_get_members_with_sql_filter($filter);	
					//issue #1327
					if(!empty($show_level_members)){
						$matched_members = array_intersect($show_level_members,$matched_members);							
					}					
					// common
					$members = array_intersect ($active_members,$matched_members);							
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";					
				break;	
				case 'last_payment':
					// check
					if(empty($search_field_value)){
						$search_field_value = date('Y-m-d',$current_date);
					}
					// convert
					$search_field_value = mgm_format_inputdate_to_mysql($search_field_value);	
					// match
					$matched_members = mgm_get_members_with('last_pay_date', $search_field_value);
					//issue #1327
					if(!empty($show_level_members)){
						$matched_members = array_intersect($show_level_members,$matched_members);							
					}					
					// common
					$members = array_intersect ($active_members,$matched_members);				
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";
				break;
				case 'expire_date':
					// check
					if(empty($search_field_value)){
						$search_field_value = date('Y-m-d',$current_date);
					}
					// convert
					$search_field_value = mgm_format_inputdate_to_mysql($search_field_value);					
					// match
					$matched_members = mgm_get_members_with('expire_date', $search_field_value);
					//issue #1327
					if(!empty($show_level_members)){
						$matched_members = array_intersect($show_level_members,$matched_members);							
					}					
					// common
					$members = array_intersect ($active_members,$matched_members);								
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";
				break;
				case 'fee':
					// match
					$matched_members = mgm_get_members_with('amount', $search_field_value);
					//issue #1327
					if(!empty($show_level_members)){
						$matched_members = array_intersect($show_level_members,$matched_members);							
					}					
					// common
					$members = array_intersect ($active_members,$matched_members);								
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";
				break;
				/*				
				case 'status':
					// members
					$members    = mgm_get_members_with('status', $search_field_value);
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";
				break;
				*/
				case 'first_name':
				case 'last_name':
					// members
					$matched_members = mgm_get_members_with($search_field_name, $search_field_value);
					//issue #1327
					if(!empty($show_level_members)){
						$matched_members = array_intersect($show_level_members,$matched_members);							
					}					
					// common
					$members = array_intersect ($active_members,$matched_members);								
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND `ID` IN ({$members_in})";
				break;
			}
		}
	}

	// filters via shortcode args
	$use_field  = (isset($args['use_field'])) ? $args['use_field'] : null;
	$use_filter = (isset($args['use_filter'])) ? $args['use_filter'] : '';
	$sort_by    = (isset($args['sort_by'])) ? $args['sort_by'] : null;
	$sort_type  = (isset($args['sort_type'])) ? $args['sort_type'] : null;
	
	// use shortcode field
	if(!empty($use_field)) $use_field = explode(',',$use_field);
	// use shortcode flter
	if(!empty($use_filter)) $use_filter = explode(',',$use_filter);
	// use shortcode sort
	if(!empty($sort_by)) $sort_by = explode(',',$sort_by);
	// use shortcode sort order
	if(!empty($sort_type)) $sort_type = explode(',',$sort_type);
	//setting up the default list fiedls
	if(empty($use_field)) $use_field = array('image','first_name','last_name','email');
	// check length
	$use_field_len = count($use_field);
	
	//getting user fillter options from short code. 
	$arr_filter_search = array();
	if(!empty($use_filter)){
		$use_filter_len = count($use_filter);
		for($k=0;$k<$use_filter_len;$k++ ){
			if (array_key_exists($use_filter[$k],$data['search_fields'])  ){
				$arr_filter_search[$use_filter[$k]] = $data['search_fields'][$use_filter[$k]];
			}elseif(array_key_exists($use_filter[$k],$custom_search_fields)){
				$arr_filter_search[$use_filter[$k]] = $custom_search_fields[$use_filter[$k]];
			}
		}
		$data['search_fields'] = $arr_filter_search;
	}

	//getting sort by options from short code. 
	$arr_sort_search = array();
	if(!empty($sort_by)){
		$use_sort_len = count($sort_by);
		for($k=0;$k<$use_sort_len;$k++ ){

			if (array_key_exists($sort_by[$k],$data['sort_fields'])  ){
				$arr_sort_search[$sort_by[$k]] = $data['sort_fields'][$sort_by[$k]];
			}elseif(array_key_exists($sort_by[$k],$custom_sort_fields)){
				$arr_sort_search[$sort_by[$k]] = $custom_sort_fields[$sort_by[$k]];
			}		
		}
		$data['sort_fields'] = $arr_sort_search;
	}
	
	//getting sort type options from short code. 
	$arr_sort_type = array();
	if(!empty($sort_type)){		
		$use_sort_type_len = count($sort_by);
		for($k=0;$k<$use_sort_type_len;$k++ ){
			if (array_key_exists($sort_type[$k],$data['order_fields'])  ){
				$arr_sort_type[$sort_type[$k]] = $data['order_fields'][$sort_type[$k]];
			}		
		}
		$data['order_fields'] = $arr_sort_type;
	}

	//issue #1301
	//setting default sort order field as sort fields first value
	if(empty($sort_field_name)) {
		$sort_field_name = array_shift(array_keys($data['sort_fields']));
	}
	//setting default sort order type as sort order fields first value
	if(empty($sort_order_type)) {
		$sort_order_type = array_shift(array_keys($data['order_fields']));		
	}
	
	//setting page limit
	$page_limit = (isset($args['page_limit'])) ? (int)$args['page_limit'] : 20 ;	
	// page limit		
	$data['page_limit'] = isset($_REQUEST['page_limit']) ? (int)$_REQUEST['page_limit'] : $page_limit;
	// page no
	$data['page_no'] = isset($_REQUEST['page_no']) ? (int)$_REQUEST['page_no'] : 1;		
	// limit
	$sql_limit = $pager->get_query_limit($data['page_limit']);	
	// order
	$sql_order = $data['sort_field'] = $data['sort_type'] = '';
	// sort
	$sort_field_name = $wpdb->escape($sort_field_name);// for sql
	$sort_order_type = $wpdb->escape($sort_order_type);// for sql	

	// check
	if(isset($sort_field_name)){
		// set
		$data['sort_field'] = $sort_field_name;
		$data['sort_type']  = $sort_order_type;
		// init	
		$custom_sort = false;
		// check
		if(array_key_exists($sort_field_name,$custom_sort_fields)) {			
			
			$show_level_member = array_intersect ($active_members,$show_level_members);				
			// members
			$sql_order_by = mgm_userlist_customfield_sort($sort_field_name, $sort_order_type, $sql_filter,$show_level_member);
			
			// limit
			$lim = str_replace('LIMIT','',$sql_limit);
			$lim = explode(',',$lim);
			// init
			$temp_array = array();
			// loop
			for($i=trim($lim[0]); $i< ($lim[0]+$lim[1]); $i++ ){
				if(!empty($sql_order_by[$i]))
					$temp_array[] =$sql_order_by[$i];
			}
			
			//check for active members
			//$temp_array = array_intersect ($active_members,$temp_array);								
			
			$in_order = (count($temp_array)==0) ? 0 : (implode(',', $temp_array));
			// order
 			//$in_order = implode(',',$temp_array);
			if(!empty($temp_array)) {
	 			// set
				$sql_order = " ORDER BY FIELD( ID, {$in_order} ) ";
			}else {
				$sql_order ='';
			}
			// sql
			$sql = "SELECT * FROM `{$wpdb->users}` WHERE ID != 1 AND `ID` IN ({$in_order}) {$sql_order}";
			//  list
			$custom_user_list = $wpdb->get_results($sql);	
			// flag
			$custom_sort = true;			
		} else {				
			// by name
			switch($sort_field_name){
				case 'username':
					$sql_order_by = "user_login";
				break;
				case 'id':
					$sql_order_by = "ID";
				break;
				case 'email':
					$sql_order_by = "user_email";
				break;
				case 'membership_type':
				break;
				case 'reg_date':
					$sql_order_by = "user_registered";
				break;
			}			
			// set
			if(isset($sql_order_by)) $sql_order = " ORDER BY {$sql_order_by} {$sort_order_type}";
		}			
	}
	// default			
	if(!isset($sql_order_by)) $sql_order = " ORDER BY ID desc";		
	
	//default active members ids
	if(empty($sql_filter)) {
		//issue #1327
		if(!empty($show_level_members)) {			
			// common
			$members = array_intersect ($active_members,$show_level_members);								
			$members_in = (count($members)==0) ? 0 : (implode(',', $members));
		}else {
			$members_in = (count($active_members)==0) ? 0 : (implode(',', $active_members));
		
		}
		// set filter
		$sql_filter = " AND `ID` IN ({$members_in})";		
	}
	
	// get members		
	$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM `{$wpdb->users}` WHERE ID != 1 {$sql_filter} {$sql_order} {$sql_limit}";
	// users
	$user_list = $wpdb->get_results($sql);	
	
	// echo $wpdb->last_query;
	// set
	if($custom_sort) {
		$data['users'] = $custom_user_list;
	}else {
		$data['users'] = $user_list;		
	}

	// page url
	$data['page_url']   = add_query_arg(array('query'=>$search_field_value,'by'=>$search_field_name,'sort_field'=>$sort_field_name,'sort_order'=>$sort_order_type), $current_url);
	                      //$url.'query='.$search_field_value.'&by='.$search_field_name.'&sort_field='.$sort_field_name.'&order_type='.$order_type;
	// get page links
	$data['page_links'] = $pager->get_pager_links($data['page_url']);	
	// total pages
	$data['page_count'] = $pager->get_page_count();
	// total rows/results
	$data['row_count']  = $pager->get_row_count();
	

	//sort by filed 
	$sort_field_html = sprintf('<select id="sort_field" name="sort_field" class="width100px">%s</select>', 
	              	   mgm_make_combo_options($data['sort_fields'], $data['sort_field'], MGM_KEY_VALUE));

	//order by asc/desc
	$sort_order_html = sprintf('<select id="sort_order" name="sort_order" class="width100px">%s</select>', 
				 	   mgm_make_combo_options($data['order_fields'], $data['sort_type'], MGM_KEY_VALUE));

	//search by
	$search_by_html = sprintf('<select id="by" name="by" class="width100px">%s</select>',
		   			  mgm_make_combo_options($data['search_fields'], $data['search_field_name'], MGM_KEY_VALUE));
	
	
	
	//search box
	$html = '<div>
				<form method="get" action="'.$current_url.'">
					<h5>'.__('Search Our Members','mgm').':</h5>
					<input type="text" id="query" name="query" value="'.$data['search_field_value'].'" /> 
					'.__('in','mgm').' '.$search_by_html.' '.__('sort by','mgm').' '.$sort_field_html.' '.$sort_order_html.'					
					<input class="button" type="submit" id="submit" value="'.__('Submit','mgm').'" />
					<input type="hidden" name="search" id="search" value="search" />
				</form>
			</div>';

	$html .= '<div><table><tr>';

	for ($i=0; $i<$use_field_len;$i++) {
		$html .= sprintf('<th class="th_div mgm_text_align_left mgm_column_%s" id="mgm_column_%s"><label><b>%s</b></label></th>',$use_field[$i],$use_field[$i],ucwords(str_replace('_',' ',$use_field[$i])));			
	}
	
	$html .= '</tr>';
	
	$enable_public_profile = mgm_get_class('system')->get_setting('enable_public_profile');	
	
	if(!empty($data['users'])){
		foreach($data['users'] as $user) {
			// user object
			$user = get_userdata($user->ID);
			// mgm member object
			$member = mgm_get_member($user->ID);
	
			$html .='<tr>';
			for ($i=0; $i<$use_field_len;$i++) {
	
				$app_user_filed = 'user_'.$use_field[$i];	
				
				if(isset($member->custom_fields->$use_field[$i]) || 
					isset($user->$use_field[$i]) ||
					isset($user->$app_user_filed)||$use_field[$i]=='image'){
	
					if($use_field[$i] == 'image'){
						//is_multisite,network_home_url
						$html .= sprintf('<td align="left" valign="top" class="mgm_%s_value">', $use_field[$i]);
						if(bool_from_yn($enable_public_profile)){
							//$profile_url = add_query_arg(array('username'=>$user->user_login), site_url('userprofile'));
							$profile_url = network_site_url().'/userprofile/?username='.$user->user_login; 
							$html .= sprintf('<a class="tern_wp_member_gravatar" href="%s">%s</a>', $profile_url,get_avatar($user->ID,60));							
						}else {
							$html .= sprintf('<a href="javascript://">%s</a>', get_avatar($user->ID,60));
						}
						$html .='</td>';
					}else{
						//getting data from user object
						if (isset( $user->$use_field[$i]) ){
							$member->custom_fields->$use_field[$i]=$user->$use_field[$i];
						}
						//getting data from user object
						if($use_field[$i] == 'email'){
							$member->custom_fields->$use_field[$i] = $user->$app_user_filed;
						}
						// val unserialize
						$val = maybe_unserialize($member->custom_fields->$use_field[$i]);
						// array to string
						if( is_array($val) ) $val = implode(', ', $val);	
						// set
						$html .= sprintf('<td align="left" valign="top" class="mgm_%s_value"><b>%s</b></td>', $use_field[$i],$val);
					}
				}
			}
			$html .= '</tr>';
		}
	} else {
		$html .= '<tr><td colspan="'.$use_field_len.'" align="center">' . __('No members found','mgm').' ...! </td></tr>';		
	}
	$html .= '</table></div><br/>';
	
	$html .='<div class="mgm_page_links_div">';
	
	if($data['page_links']):
		$html .='<div class="pager-wrap">'. $data['page_links'].'</div><div class="clearfix"></div>';
	endif; 
	
	$html .='</div><br/>';
	//issue #1635
	$users_list_html = '<div class="mgm_user_list_container">'.$html.'</div>';
	
	return $users_list_html ;
		
}

//generate face book login button  short code content
function mgm_generate_facebook_login($use_default_links = true){
	// buttons		 
	$buttons = '';
	$url = get_permalink();
	$callback_url  = strpos($url,'?') !== false ? $url : $url . '?';
	$callback_url .='connect=facebook';
	// check auto login
	if($html = mgm_try_auto_login()) return $html;
	// process hooked logins i.e. facebook connect
	$errors = array();
	// process
	$errors = mgm_pre_process_facebook_login($errors);	
	// buttons
	$buttons = mgm_login_form_facebook_button($buttons,$callback_url);
	// set error !
	if(isset($errors) && is_object($errors)) {
		// get error
		if($error_html = mgm_set_errors($errors, true)){
			$buttons .= $error_html;
		}
	}
	// return	
	return $buttons;
}

//generate public profile short code content
function mgm_user_public_profile($args=array()){
	
	$html = '';
	
	$enable_public_profile = mgm_get_class('system')->setting['enable_public_profile'];	
	$public_profile = (isset($args['public_profile']) ? $args['public_profile'] : 'false');

	$css_group = mgm_get_css_group();
	
	if($css_group !='none') {
		//expand this if needed
		$css_link_format = '<link rel="stylesheet" href="%s" type="text/css" media="all" />';				
		$css_file = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.pages.css';
		$html .= sprintf($css_link_format, $css_file);
	}		

	if($enable_public_profile =='Y' || $public_profile == 'true') {

		// get
		if(isset($_GET['username'])) {
	
			$username = sanitize_user($_GET['username']);
			$user = get_user_by('login', $username);			
			
			if(!empty($user)) {

				$member = mgm_get_member($user->ID);
				
				$cf_profile_page = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_public_profile'=> true)));	
				
				$filed_status = mgm_custom_filed_status('show_public_profile');

				if(($filed_status && isset($member->custom_fields->show_public_profile) && $member->custom_fields->show_public_profile =='Y') || !$filed_status) {
					// html	
					$html .= '<span id="get_avatar"></span>';
					
					$html .= '<div class="table width100 br">';
				
					/*	
					$html .= '<div class="table width100 br">
								<div class="row alternate br_bottom">
									<div class="cell width25 padding10px">
										<strong>' . __('About','mgm') . '</strong>
									</div>
									<div class="cell width2 padding10px"><strong>:</strong></div>
									<div class="cell width73">' . esc_html($user->description) . '</div>
								</div>
					
								<div class="row alternate br_bottom">
									<div class="cell width25 padding10px">
										<strong>' . __('First Name','mgm') . '</strong>
									</div>
									<div class="cell width2 padding10px"><strong>:</strong></div>
									<div class="cell width73">' . esc_html($user->first_name) . '</div>
								</div>
				
								<div class="row alternate br_bottom">
									<div class="cell width25 padding10px">
										<strong>' . __('Last Name','mgm') . '</strong>
									</div>
									<div class="cell width2 padding10px"><strong>:</strong></div>
									<div class="cell width73">' . esc_html($user->last_name) . '</div>
								</div>
								
								<div class="row alternate br_bottom">
									<div class="cell width25 padding10px">
										<strong>' . __('Display Name','mgm') . '</strong>
									</div>
									<div class="cell width2 padding10px"><strong>:</strong></div>
									<div class="cell width73">' .esc_html($user->display_name) . '</div>
								</div>
								
								<div class="row alternate br_bottom">
									<div class="cell width25 padding10px">
										<strong>' . __('Email : ','mgm') . '</strong>
									</div>
									<div class="cell width2 padding10px"><strong>:</strong></div>
									<div class="cell width73">' .esc_html($user->user_email) . '</div>
								</div>';
	*/							
								
					
								foreach ($cf_profile_page as $field){
									
	
										//continue;
										
									if(isset($member->custom_fields->$field['name']) && $field['type']!='image'){
										
										
										if(in_array($field['name'],array('display_name','last_name','first_name','description','email','user_email'))){
										
											//issue #1294
											if(isset($member->custom_fields->$field['name']) == $field['name']) {
											
												if($field['name'] =='email') {
													$field['name'] = 'user_email';
												}
													
												$html .='<div class="row alternate br_bottom">
															<div class="cell width25 padding10px"><strong>' . $field['label'] . '</strong></div>
															<div class="cell width2 padding10px" ><strong>:</strong></div>
															<div class="cell width73 padding10px">' . esc_html($user->$field['name']) . '</div>
														</div>';										
											}										
										}else {
											
											$html .='<div class="row alternate br_bottom">
														<div class="cell width25 padding10px"><strong>' . $field['label'] . '</strong></div>
														<div class="cell width2 padding10px" ><strong>:</strong></div>
														<div class="cell width73 padding10px">';
											
											// val unserialize - issue #1422
											$val = maybe_unserialize($member->custom_fields->$field['name']);
											// array to string
											if( is_array($val) ) $val = implode(', ', $val);										
											
											$html .= $val.'</div></div>';
										}
									}else {
										if(trim($field['type']) == 'image') {								
											$avatar = get_avatar($user->ID,90);
											$append_avatar = 'jQuery("#get_avatar").append("'.$avatar.'");';
											$html .= '<script> jQuery(document).ready(function(){ '.$append_avatar.' }); </script>';
										}
									}
								}
					$html .= '</div>';
				}else{
					$html .= '<div class="table width100 br">
						<div class="row alternate br_bottom">
							<div class="cell width25 padding10px">
								<strong>' .__('User not allowed to show his profile public.','mgm') . '</strong>
							</div>
						</div>
						</div>';					
				}				
			} else {
				$html .= '<div class="table width100 br">
					<div class="row alternate br_bottom">
						<div class="cell width25 padding10px">
							<strong>' .sprintf(__('No user profile found with this user name " %s ".','mgm'),$username) . '</strong>
						</div>
					</div>
					</div>';
			}
		} else {
				$html .= '<div class="table width100 br">
					<div class="row alternate br_bottom">
						<div class="cell width25 padding10px">
							<strong>' . __('You dont have access to view content of this page.','mgm') . '</strong>
						</div>
					</div>
					</div>';
		}
	}
	//issue #1635
	$user_public_profile_html = '<div class="mgm_user_public_profile_container">'.$html.'</div>';	
	// return 	
	return $user_public_profile_html;

}

//fb registration form		 
function mgm_generate_facebook_registration(){
	
	// fb registration form		 
	$fb_registration_form = '';
	$url = get_permalink();
	$callback_url =  strpos($url,'?') !== false ? $url : $url.'?';
	$callback_url .='connect=facebook_registration';

	// check auto login
	if($html = mgm_try_auto_login()) return $html;

	$fb_registration_form = mgm_registration_form_facebook_form($fb_registration_form,$callback_url);
	
	mgm_pre_process_facebook_registration();
	
	// set error !
	if(isset($errors) && is_object($errors)) {
		// get error
		if($error_html = mgm_set_errors($errors, true)){
			$fb_registration_form .= $error_html;
		}
	}
		
	return $fb_registration_form;

}

/**
 * Getting member purchased post packs for current user
 */
function mgm_member_purchased_postpacks($pagetype = 'admin'){
	
	global $wpdb;
	// current_user
	$current_user = wp_get_current_user();
	// snippet
	$snippet_length = 200;	
	// purchased
	$purchased_postpacks = mgm_get_member_postpacks($current_user->ID,'accessible');	
	// posts
	$postpacks = $purchased_postpacks['postpacks'];
	// total_posts
	$total_postpacks = $purchased_postpacks['total_postpacks'];

	// init
	$html = $alt = '' ;
	// start output
	$html .= '<div class="table width100 br">'.
			
				'<div class="row br_bottom">'.
					'<div class="cell th_div width25 padding10px"><b>'.__('Post Pack Title','mgm').'</b></div>'.
					'<div class="cell th_div width45 padding10px"><b>'.__('Post Pack Description','mgm').'</b></div>'.
					'<div class="cell th_div width15 padding10px"><b>'.__('Purchased','mgm').'</b></div>'.
					'<div class="cell th_div width15 padding10px">&nbsp;</div>'.
				'</div>';	
	// check		
	// $currency = mgm_get_setting('currency');
	// id, name, description,
	if($total_postpacks>0) { 
		// loop
		foreach($postpacks as $id=>$obj){
			// set			

			$title     = $obj->name;
			$content   = $obj->description;
			$purchased = date('jS F Y', strtotime($obj->purchase_dt));
			
			if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
				$title   = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($title);
				$content = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($content);
			}
			$content  = preg_replace("'\[/?\s?private\s?\]'i",'', $content);
			$content  = preg_replace("/\[.*?\]/",'', $content);
			$content  = substr(strip_tags($content), 0, $snippet_length);
			$content .= (strlen($content) > $snippet_length ? '...':'');

		$html .='<div class="row br_bottom '.($alt = ($alt=='') ? 'alternate': '').'">'.
				'<div class="cell width25 padding10px"><a href="#">'.$title.'</a></div>'.
				'<div class="cell width45 padding10px">'.$content.'</div>'.
				'<div class="cell width15 padding10px">'.$purchased.'</div>'.
				'<div class="cell width15 padding10px">&nbsp;</div>'.
			'</div>';			
		}
	}else {
		$html .='<div class="row br_bottom'.($alt = ($alt=='') ? 'alternate': '').'">'.
			'<div class="cell mgm_text_align_center">'.__('No purchased post packs','mgm').'</div>'.
			'</div>';
	}			
	$html .='</div>';	

	//return $html;			
	if($total_postpacks > 0 ) {
		$html .= '<div class="mgm_margin10px">';
		if(isset($_GET['section']) && $_GET['section'] == 'purchased_postpacks') {
			$html .='<div class="mgm_content_back_link_div">'.
				'<a href="'.(($pagetype=='admin') ? admin_url('profile.php?page=mgm/membership/content') : mgm_get_custom_url('membership_contents')).'" class="button">'.__('Back','mgm').'</a>'.
				'</div>';
		}
		$html .= '<div class="mgm_content_total_post_div">'.
			sprintf(__('You have purchased a total of %d %s.','mgm'), $total_postpacks, ($total_postpacks == 1 ? __('Post Pack', 'mgm'):__('Post Packs', 'mgm'))).
			'</div>';
		$html .='<div class="mgm_content_total_publish_div">';
		if(isset($_GET['section']) && $_GET['section'] == 'purchased_postpacks') {
			$html .='<span class="pager">'.$purchased_postpacks['pager'].'</span>';
		//}elseif($total_post_rows > $total_posts) {
		//Do not show See All if number of records are <= $total_posts
		}elseif($total_postpacks > count($postpacks)) {
			$html .='<a href="'.(($pagetype=='admin') ? admin_url('profile.php?page=mgm/membership/content&section=purchased_postpacks') : mgm_get_custom_url('membership_contents', false, array('section' => 'purchased_postpacks'))) .'" class="button">'.__('See All','mgm').'</a>';
		}	
		$html .= '</div>';
		$html .='<br/><div class="clearfix"></div>';
		$html .='</div>';	
	}
		
	return $html;
}
/**
 * Getting member purchasable post packs for current user
 */
function mgm_member_purchasable_postpacks($pagetype = 'admin'){
	global $wpdb;
	// current_user
	$current_user = wp_get_current_user();
	// snippet
	$snippet_length = 200;	
	// purchased
	$purchasable_postpacks = mgm_get_member_postpacks($current_user->ID,'purchasable');	
	// posts
	$postpacks = $purchasable_postpacks['postpacks'];
	// total_posts
	$total_postpacks = $purchasable_postpacks['total_postpacks'];

	// init
	$html = $alt = '' ;
	// start output
	$html .= '<div class="table width100 br">'.
				'<div class="row br_bottom">'.
					'<div class="cell th_div width25 padding10px"><b>'.__('Post Pack Title','mgm').'</b></div>'.
					'<div class="cell th_div width45 padding10px"><b>'.__('Post Pack Description','mgm').'</b></div>'.
					'<div class="cell th_div width15 padding10px"><b>'.__('Price','mgm').'</b></div>'.
					'<div class="cell th_div width15 padding10px"><b></b></div>'.
				'</div>';	

	// check		
	$currency = mgm_get_setting('currency');
	// id, name, description,
	if($total_postpacks>0) { 
		// loop
		foreach($postpacks as $id=>$obj){
			// set			

			$title     = $obj->name;
			$content   = $obj->description;
			if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
				$title   = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($title);
				$content = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($content);
			}
			$content  = preg_replace("'\[/?\s?private\s?\]'i",'', $content);
			$content  = preg_replace("/\[.*?\]/",'', $content);
			$content  = substr(strip_tags($content), 0, $snippet_length);
			$content .= (strlen($content) > $snippet_length ? '...':'');

		$html .='<div class="row br_bottom '.($alt = ($alt=='') ? 'alternate': '').'">'.
				'<div class="cell width25 padding10px"><a href="#">'.$title.'</a></div>'.
				'<div class="cell width45 padding10px">'.$content.'</div>'.
				'<div class="cell width15 padding10px">'.$obj->cost. ' ' .$currency.'</div>'.
				'<div class="cell width15 padding10px">'.mgm_get_postpack_purchase_button($obj->id).'</div>'.
			'</div>';			
		}
	}else {
		$html .='<div class="row br_bottom'.($alt = ($alt=='') ? 'alternate': '').'">'.
			'<div class="cell mgm_text_align_center">'.__('No purchased post packs','mgm').'</div>'.
			'</div>';
	}			
	$html .='</div>';	

	//return $html;			
	if($total_postpacks > 0 ) {
		$html .= '<div class="mgm_margin10px">';
		if(isset($_GET['section']) && $_GET['section'] == 'purchasable_postpacks') {
			$html .='<div class="mgm_content_back_link_div">'.
				'<a href="'.(($pagetype=='admin') ? admin_url('profile.php?page=mgm/membership/content') : mgm_get_custom_url('membership_contents')).'" class="button">'.__('Back','mgm').'</a>'.
				'</div>';
		}
		$html .= '<div class="mgm_content_total_post_div">'.
			sprintf(__('You have a total of %d %s you can purchase and access.','mgm'), $total_postpacks, ($total_postpacks == 1 ? __('Post Pack', 'mgm'):__('Post Packs', 'mgm'))).
			'</div>';
		$html .='<div class="mgm_content_total_publish_div">';
		if(isset($_GET['section']) && $_GET['section'] == 'purchasable_postpacks') {
			$html .='<span class="pager">'.$purchasable_postpacks['pager'].'</span>';
		//}elseif($total_post_rows > $total_posts) {
		//Do not show See All if number of records are <= $total_posts
		}elseif($total_postpacks > count($postpacks)) {
			$html .='<a href="'.(($pagetype=='admin') ? admin_url('profile.php?page=mgm/membership/content&section=purchasable_postpacks') : mgm_get_custom_url('membership_contents', false, array('section' => 'purchasable_postpacks'))) .'" class="button">'.__('See All','mgm').'</a>';
		}	
		$html .= '</div>';
		$html .='<br/><div class="clearfix"></div>';
		$html .='</div>';	
	}
		
	return $html;
	
}
/**
 * member accessible/purchasable post packs 
 * @param string $type ( accessible|purchasable )
 * @param int $user_id
 * @param int $limit
 * @return array
 */

function mgm_get_member_postpacks ($user_id = '',$type='accessible') {

	global $wpdb;	
	
	$total_limit = 20;
	$per_page 	 = 5;
	
	if($type == 'accessible') {
		// limit
		if(isset($_GET['section']) && $_GET['section'] == 'purchased_postpacks'){
			$limit = 'LIMIT '.$total_limit;
		}else{
			$limit = 'LIMIT '.$per_page;
		}
	}else {
		// limit
		if(isset($_GET['section']) && $_GET['section'] == 'purchasable_postpacks'){
			$limit = 'LIMIT '.$total_limit;
		}else{
			$limit = 'LIMIT '.$per_page;
		}
		
	}
	
	$purchased_postpacks = mgm_get_purchased_postpacks($user_id);
	
	$condition = '';
	//purchasable
	if(!empty($purchased_postpacks)) {		
		
		$pp_pack = array();
				
		foreach ($purchased_postpacks as $key => $purchased_postpack) {
			$pp_pack[]=$key;
		}
		
		$p_postpacks = implode(',',$pp_pack);
		 
		if($type == 'accessible')
			$condition =  " WHERE id IN (".$p_postpacks.")";
		else
			$condition =  " WHERE id NOT IN (".$p_postpacks.")";
	}else {
		if($type == 'accessible')
			$condition =  " WHERE id IN (0)";
		else if($type == 'purchasable')
			$condition =  "";
	}
	
	// sql
	$sql = "SELECT SQL_CALC_FOUND_ROWS id, name, description, create_dt, cost FROM `" . TBL_MGM_POST_PACK . "` {$condition} ORDER BY id DESC {$limit}";
	//echo $sql;		
	$results = $wpdb->get_results($sql);
	
	// total
	$total = $wpdb->get_var("SELECT count(id) as count FROM `" . TBL_MGM_POST_PACK . "` {$condition} ORDER BY id DESC LIMIT {$total_limit}");
	
	// init 
	$posts_packs = array();
	
	// store
	if (count($results) >0) {
		foreach ($results as $id=>$obj) {			
			if(!empty($purchased_postpacks)) { 	$obj->purchase_dt = $purchased_postpacks[$obj->id]; }
			$posts_packs[$obj->id] = $obj;				
		}
	}		
	// return 	
	return array('postpacks'=>$posts_packs,'total_postpacks'=>$total, 'pager'=>'');
}

/**
 * user purchased contents	 
 */
function mgm_generate_purchased_contents(){
	
	$css_group = mgm_get_css_group();

	$html = '';

	if($css_group != 'none') {
		//expand this if needed
		$css_link_format = '<link rel="stylesheet" href="%s" type="text/css" media="all" />';				
		$css_file = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.pages.css';
		$html .= sprintf($css_link_format, $css_file);
	}
	//already purchased contents	
	$html .= 	'<div class="postbox mgm_margin10px0px">'.
					'<h3>'. __('Purchased Contents','mgm') . '</h3>'.
					'<div class="inside">'.mgm_member_purchased_contents('user') .'</div>' .
				'</div>' ;
	return 	$html;
}
/**
 * user purchasable contents	 
 */
function mgm_generate_purchasable_contents(){		
	
	$css_group = mgm_get_css_group();

	$html = '';

	if($css_group != 'none') {
		//expand this if needed
		$css_link_format = '<link rel="stylesheet" href="%s" type="text/css" media="all" />';				
		$css_file = MGM_ASSETS_URL . 'css/'.$css_group.'/mgm.pages.css';
		$html .= sprintf($css_link_format, $css_file);
	}
	//purchasable contents	
	$html.= 	'<div class="postbox mgm_margin10px0px">'.
					'<h3>'. __('Purchasable Contents','mgm') . '</h3>'.
					'<div class="inside">'.mgm_member_purchasable_contents('user') .'</div>' .
				'</div>' ;
	return 	$html;
		
}
/**
 *  Admin user edit screen unsubscribe option	 
 */
/**
 *  Admin user edit screen unsubscribe option	 
 */
function mgm_admin_user_unsubscribe($user_ID=false, $return=false){
	//check logged in user is super admin:
	$is_admin = (is_super_admin()) ? true : false;
	if($is_admin) {
		add_filter('admin_footer_text', 'mgm_change_footer_admin', 9999);
		add_filter( 'update_footer', 'mgm_change_footer_version', 9999);
		add_filter('in_admin_footer', 'mgm_admin_user_unsubscribe_process',10,2);
	}	
}
/**
 *  Change footer admin	 
 */
function mgm_change_footer_admin () { 
	return '&nbsp;';
}
/**
 *  Change footer version	 
 */
function mgm_change_footer_version() { 
	return ' ';
}
/**
 *  Admin user edit screen unsubscribe option process	 
 */
function mgm_admin_user_unsubscribe_process($user_ID=false, $return=false){
	// get user
	if (!$user_ID) $user_ID = mgm_get_user_id();
	// get form object
	if (is_object($user_ID)) $user_ID = $user_ID->ID;
	// member
	$member  = mgm_get_member($user_ID);
	// init
	$user = new stdClass();
	$user->ID =$user_ID;

	$html ='';
	// error
	if(isset($_GET['unsubscribe_errors']) && !empty($_GET['unsubscribe_errors'])) {
		$errors = new WP_Error();		
		$errors->add('unsubscribe_errors', urldecode(strip_tags($_GET['unsubscribe_errors'])), (isset($_GET['unsubscribed'])?'message':'error'));
		$html .= mgm_set_errors($errors, true);		
		unset($errors);		
	}	

	$html .= mgm_get_admin_user_unsubscribe_status_button($member, $user);
	 
	if(isset($member->other_membership_types) && is_array($member->other_membership_types) && count($member->other_membership_types) > 0) {
		foreach ($member->other_membership_types as $key => $memtypes){
			$memtypes = mgm_convert_array_to_memberobj($memtypes,$user_ID);
			$html .=  mgm_get_admin_user_unsubscribe_status_button($memtypes, $user);
		}		
	}

	echo $html;
}
/**
 *  Admin user edit screen unsubscribe staus button	 
 */
function mgm_get_admin_user_unsubscribe_status_button($member, $user){
	// init
	$html = '';
	// object
	$packs_obj = mgm_get_class('subscription_packs');
	$pack_name = $packs_obj->get($member->pack_id);	
	// cancelled
	if($member->status == MGM_STATUS_CANCELLED) {		
		$html .= '<span class="s-active"><b>'.$pack_name['description'].'</b></span>';
		$html .= '<div class="mgm_margin_bottom_10px s-expired">'.
					'<h4>'. __('Unsubscribed','mgm').'</h4>'.
					'<div class="mgm_margin_bottom_10px mgm_color_red">'.
						 __('You have unsubscribed.','mgm'). 
					'</div>'.
					'</div>';
		$html .='<hr/>';
	}elseif((isset($member->status_reset_on) && isset($member->status_reset_as)) && $member->status == MGM_STATUS_AWAITING_CANCEL) {
		$lformat = mgm_get_date_format('date_format_long');
		$html .= '<span class="s-active"><b>'.$pack_name['description'].'</b></span>';		
		$html .= '<div class="mgm_margin_bottom_10px s-expired">'.
				'<h4>'. __('Unsubscribed','mgm').'</h4>'.
				'<div class="mgm_margin_bottom_10px mgm_color_red">'.
					 sprintf(__('You have unsubscribed. Your account has been marked for cancellation on <b>%s</b>.','mgm'), date($lformat, strtotime($member->status_reset_on))). 
				'</div>'.
				'</div>';
		$html .='<hr/>';
	}else {		
		// show unsucscribe button			
		if( is_super_admin() ) {
			// check
			if( $module = $member->payment_info->module ) {
				// if a valid module
				if( $obj_module = mgm_is_valid_module($module, 'payment', 'object') ){
					// output button
					$html .= '<span class="s-active"><b>'.$pack_name['description'].'</b></span>';					
					$html .= $obj_module->get_button_unsubscribe(array('user_id'=>$user->ID, 'membership_type' => $member->membership_type));
					$html .= '<script language="javascript">'.
							'confirm_unsubscribe=function(element){'.
								'if(confirm("' .__('You are about to unsubscribe. Do you want to proceed?','mgm') . '")){'.																
									'jQuery(element).closest("form").submit();'.
								'}'.								
							'}'.
						'</script>';
					$html .='<hr/>';

				}				
			}
		}	
	}	
	
	return $html;
}
/**
 * Magic Members parse download shortcode tag
 * @package MagicMembers
 * @desc parse download tag embeded in templates, works via wp shortcode api 
 * @param array
 * @return string
 */ 
function mgm_shortcode_download_parse($args){				
	global $wpdb;		
	//explode values
	$args= explode('#',$args[0]);
	// get system
	$system_obj = mgm_get_class('system');
	// hook
	$hook = $system_obj->get_setting('download_hook', 'download');
	// slug
	$slug = $system_obj->get_setting('download_slug', 'download');
	//link
	$link ='';
	// count
	if (count($args)) {		
		//sql
		$sql = "SELECT id, title, filename, post_date, members_only, user_id,code FROM `" . TBL_MGM_DOWNLOAD . "` WHERE id = {$args[1]}";
		// get downloads	
		$downloads = $wpdb->get_results($sql);
		// if has downloads
		if ($downloads) {
			// loop
			foreach($downloads as $download) {
				// download url
				$download_url = mgm_download_url($download, $slug);
				// trim last slash
				$download_url = rtrim($download_url, '/');
			
				if(isset($args[2])){
					switch (trim($args[2])) {
						case 'size':
							// Download link with filesize
							$link    = '<a href="'.$download_url . '" title="'.$download->title.'" >'.$download->title.' - '.mgm_file_get_size($download->filename).'</a>';							
							break;
						case 'image':
							// image
							$download_image_button = sprintf('<img src="%s" alt="%s" />',MGM_ASSETS_URL . 'images/download.gif', $download->title);
							// add filter
							$download_image_button = apply_filters('mgm_download_image_button', $download_image_button, $download->title);
							// Image link
							$link    = '<a href="'.$download_url . '" title="'.$download->title.'">'.$download_image_button.'</a>';							
							break;
						case 'button':
							// Button link
							$link    = '<input type="button" name="btndownload-'.$download->id.'" onclick="window.location=\''.$download_url.'\'" title="'.$download->title.'" value="'.__('Download','mgm').'"/>';							
							break;
						case 'url':
							// Download url
							$link    = $download_url;
							break;
					}
				}else {
					// Download link
					$link    = '<a href="' . $download_url . '" title="' . $download->title . '" >' . $download->title . '</a>';					
				}			
			}
		}
	}	
	return $link;
}
/**
 * user unsubscribe
 */
function mgm_user_unsubscribe_info($user_id=NULL,$args=array()) {

	// current user
	if(!$user_id){
		$user = wp_get_current_user();
	}else{	
	// by user id
		$user = get_userdata($user_id);	
	}		
	
	// return when no user
	if(!isset($user->ID) || (isset($user->ID) && (int)$user->ID == 0)){
		return sprintf(__('Please <a href="%s">login</a> to see your unsubscribe button.', 'mgm'), mgm_get_custom_url('login'));	
	}
	// member
	$member  = mgm_get_member($user->ID);
	// init
	$html = '';
	// button
	$html .= mgm_get_unsubscribe_status_button($member, $user);	
	//other members count
	$subs_count = 0;
	// check
	if(isset($member->other_membership_types) && !empty($member->other_membership_types)) { 		
		// loop
		foreach ($member->other_membership_types as $key => $other_member) {	
			//check			
			if(!empty($other_member)) {
				//check
				if(is_array($other_member)) $other_member = mgm_convert_array_to_memberobj($other_member, $user->ID);
				// status button
				$html .= mgm_get_other_unsubscribe_status_button($other_member, $user, $subs_count);
				//check
				if($subs_count == 0) $subs_count++;	
			}
		}
	}
	//return
	return $html;	
}
// end of file