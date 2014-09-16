<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin reports module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin_reports extends mgm_controller{
 	
	// construct
	function __construct()
	{		
		$this->mgm_admin_reports();
	}
	
	// construct php4
	function mgm_admin_reports(){		
		// load parent
		parent::__construct();
	}
	
	// index
	function index(){
		// data
		$data = array();		
		// load template view
		$this->load->template('reports/index', array('data'=>$data));		
	}
	

	// sales
	function sales(){	

		$data = array();	
		// load template view
		$this->load->template('reports/sales/index', array('data'=>$data));	
	}

	// earnings
	function earnings(){
		// load template view
		$this->load->template('reports/earnings/index', array('data'=>$data));	
	}
	
	// projection
	function projection(){
		
		$data = array();	
		// load template view
		$this->load->template('reports/projection/index', array('data'=>$data));	

	}

	// projection list
	function projection_list(){	

		$data = array();	
		
		if(isset($_POST['projection_date_start'])){
			$date_start = $_POST['projection_date_start'];				
		}else {
			$date_start='';
		}
		
		if(isset($_POST['projection_date_end'])){
			$date_end   = $_POST['projection_date_end'];
		}else {
			$date_end='';
		}
		if(isset($_POST['projection_membership_type'])){
			$member_type   = $_POST['projection_membership_type'];
		}else {
			$member_type='all';
		}

		// getting the projection result
		$data = $this->get_expected_recurring_earning($date_start,$date_end,$member_type);
		$this->load->template('reports/projection/list', array('data'=>$data));	
	
	}
	
	//Recurring earnings
	function recurring_earnings($date_start='',$date_end='',$member_type='all'){
		global $wpdb;		
		$start = 0;
		$limit = 1000;
		//user meta fields
		$fields= array('user_id','meta_value');	
		// sql
		$sql = "SELECT count(*) FROM `{$wpdb->usermeta}` WHERE `meta_key` = 'mgm_member_options' AND `user_id` <> 1";	
		$count  = $wpdb->get_var($sql);		
		// packs
		$packs = mgm_get_class('subscription_packs');		
		//current date
		$curr_date = mgm_get_current_datetime();
		$current_date = $curr_date['timestamp'];
		
		$on_going = array();
		$recuring_cycles = array();
		$dt = array();			
		
		//setting the default end date	
		if(empty($date_end)){
			$end_date =date('Y-m-d',$current_date);
		}else {
			$end_date =date('Y-m-d',strtotime($date_end));	
		}
		//setting the default start date
		if(empty($date_start)){
			$start_date = date('Y-m-d',strtotime("$end_date - 3 months"));	
		}else {
			$start_date =date('Y-m-d',strtotime($date_start));				
		}		
		//count	
		if($count) {			
			for( $k = $start; $k < $count; $k = $k + $limit ) {
				$users = mgm_patch_partial_user_member_options($k, $limit, $fields);
				//checking each user recurring payments
				foreach($users as $user) {	
					$user_id = $user->user_id;
					$member = unserialize($user->meta_value);
					// convert
					$member = mgm_convert_array_to_memberobj($member, $user_id);
					$last_pay_date = $member->last_pay_date;					
					$membership_type =$member->membership_type;					
					
					if($membership_type ==$member_type || $member_type == 'all') {	
						
						if(!empty($last_pay_date) && $member->amount > 0 && strtotime($last_pay_date) > $member->join_date ){
							
							
							if(strtotime($last_pay_date) >= strtotime($start_date) && strtotime($last_pay_date)  > $member->join_date){
							
								// check pack		
								$subs_pack = null;											
								if($member->pack_id){
									$subs_pack = $packs->get_pack($member->pack_id);
								}					
								if(empty($subs_pack)){						
									if($member_type != 'all'){
										$subs_pack = $packs->validate_pack($member->amount, $member->duration, $member->duration_type, $member_type);
									}else {
										$subs_pack = $packs->validate_pack($member->amount, $member->duration, $member->duration_type, $membership_type);
									}
								}				
								
								$num_cycles = (isset($member->active_num_cycles) && !empty($member->active_num_cycles)) ? $member->active_num_cycles : $subs_pack['num_cycles'] ;					
								$num_cycles =trim($num_cycles);
								$rebilled = 0;						
			
								if($num_cycles > 1 ){		
									$rebilled = $member->rebilled;
									if($num_cycles > $rebilled){					
										
										$num = $num_cycles - $rebilled ;
										
										// first cycle we wil consider as sale.
										for($i=0 ; $i < ($num -1) ;$i++){
			
											$duration = $i * $subs_pack['duration'];								
											//days
											if($member->duration_type == 'd'){
												$d =date('Y-m-d',strtotime("$last_pay_date - ".$duration." days"));	
											}
											//months
											if($member->duration_type == 'm'){
												$d =date('Y-m-d',strtotime("$last_pay_date - ".$duration." months"));	
											}
											//years
											if($member->duration_type == 'y'){
												$d =date('Y-m-d',strtotime("$last_pay_date - ".$duration." years"));	
											}
											if(strtotime($d) > $member->join_date && strtotime($d) <=strtotime($end_date) && strtotime($d) >=strtotime($start_date) ) {
												if(!isset($recuring_cycles[$d])){
													$dt[]=$d;
													$recuring_cycles[$d]['cost'] =	$subs_pack['cost'];
													$recuring_cycles[$d]['last_pay_date'] = $d;
												}else{
													$recuring_cycles[$d]['cost'] =	$test[$d]['cost']+$subs_pack['cost'];
												}
											}
										}							
										//mgm_pr($recuring_cycles);
									}
								}
								
								if($num_cycles == 0 && $num_cycles != '' && !empty($subs_pack)){
									
									$lp_date = $last_pay_date;
		
									//days
									if($member->duration_type == 'd'){
										$i=1;	
										while (strtotime($lp_date) > $member->join_date && strtotime($lp_date) >= strtotime($start_date)) {
											$duration = $i * $subs_pack['duration'];
											$d =date('Y-m-d',strtotime("$last_pay_date - ".$duration." days"));	
											$lp_date =$d;
											$i++;
											
											if(strtotime($lp_date) > $member->join_date && strtotime($d) >= strtotime($start_date) && strtotime($d) <=strtotime($end_date)) {
												if(!isset($on_going[$d])){
													$dt[]=$d;
													$on_going[$d]['cost'] =	$subs_pack['cost'];
													$on_going[$d]['last_pay_date'] = $d;
												}else{
													$on_going[$d]['cost'] =$on_going[$d]['cost']+$subs_pack['cost'];
												}
											}
										}
									}//days end						
									
									
									//months
									if($member->duration_type == 'm'){
										$i=1;	
										while (strtotime($lp_date) > $member->join_date && strtotime($lp_date) >= strtotime($start_date)) {
											$duration = $i * $subs_pack['duration'];
											$d =date('Y-m-d',strtotime("$last_pay_date - ".$duration." months"));	
											$lp_date =$d;
											$i++;
											
											if(strtotime($lp_date) > $member->join_date && strtotime($d) >= strtotime($start_date) && strtotime($d) <=strtotime($end_date)) {
												if(!isset($on_going[$d])){
													$dt[]=$d;
													$on_going[$d]['cost'] =	$subs_pack['cost'];
													$on_going[$d]['last_pay_date'] = $d;
												}else{
													$on_going[$d]['cost'] =$on_going[$d]['cost']+$subs_pack['cost'];
												}								
											}
										}
									}//moths end						
		
									
									//years
									if($member->duration_type == 'y'){
										$i=1;	
										while (strtotime($lp_date) > $member->join_date && strtotime($lp_date) >= strtotime($start_date)) {
											$duration = $i * $subs_pack['duration'];
											$d =date('Y-m-d',strtotime("$last_pay_date - ".$duration." years"));	
											$lp_date =$d;
											$i++;
											
											if(strtotime($lp_date) > $member->join_date && strtotime($d) >= strtotime($start_date) && strtotime($d) <=strtotime($end_date)) {
												if(!isset($on_going[$d])){
													$dt[]=$d;
													$on_going[$d]['cost'] =	$subs_pack['cost'];
													$on_going[$d]['last_pay_date'] = $d;
												}else{
													$on_going[$d]['cost'] = $on_going[$d]['cost']+$subs_pack['cost'];
												}								
											}
										}
									}//years end							
								}
							}
						}						
					}					
					
					//checking recurring payments for other membership levels of a user
					if(isset($member->other_membership_types) && is_object($member->other_membership_types) && !empty($member->other_membership_types) ) {
						
						foreach ($member->other_membership_types as $key => $other_member) {
							
							if(!empty($other_member)){			
								
								$other_member = mgm_convert_array_to_memberobj($other_member, $user_id);						
								
								$membership_type =$other_member->membership_type;
								
								$last_pay_date = $other_member->last_pay_date;
								
								if($membership_type ==$member_type || $member_type == 'all'){	
									
									//mgm_pr($other_member);		
							
									if(!empty($last_pay_date) && $other_member->amount > 0 && strtotime($last_pay_date) > $other_member->join_date ){
																		
										if(strtotime($last_pay_date) >= strtotime($start_date) && strtotime($last_pay_date) > $other_member->join_date){
		
											// check pack		
											$subs_pack = null;											
											if($other_member->pack_id){
												$subs_pack = $packs->get_pack($member->pack_id);
											}					
											if(empty($subs_pack)){						
												if($member_type != 'all'){
													$subs_pack = $packs->validate_pack($other_member->amount, $other_member->duration, $other_member->duration_type, $member_type);
												}else {
													$subs_pack = $packs->validate_pack($other_member->amount, $other_member->duration, $other_member->duration_type, $membership_type);
												}
											}				
											
											$num_cycles = (isset($other_member->active_num_cycles) && !empty($other_member->active_num_cycles)) ? $other_member->active_num_cycles : $subs_pack['num_cycles'] ;					
											$num_cycles =trim($num_cycles);
											$rebilled = 0;						
						
											if($num_cycles > 1 ){		
												$rebilled = $other_member->rebilled;
												if($num_cycles > $rebilled){					
													
													$num = $num_cycles - $rebilled ;
													
													// first cycle we wil consider as sale.
													for($i=0 ; $i < ($num -1) ;$i++){
						
														$duration = $i * $subs_pack['duration'];								
														//days
														if($other_member->duration_type == 'd'){
															$d =date('Y-m-d',strtotime("$last_pay_date - ".$duration." days"));	
														}
														//months
														if($other_member->duration_type == 'm'){
															$d =date('Y-m-d',strtotime("$last_pay_date - ".$duration." months"));	
														}
														//years
														if($other_member->duration_type == 'y'){
															$d =date('Y-m-d',strtotime("$last_pay_date - ".$duration." years"));	
														}
														if(strtotime($d) > $other_member->join_date && strtotime($d) <=strtotime($end_date) && strtotime($d) >=strtotime($start_date) ) {
															if(!isset($recuring_cycles[$d])){
																$dt[]=$d;
																$recuring_cycles[$d]['cost'] =	$subs_pack['cost'];
																$recuring_cycles[$d]['last_pay_date'] = $d;
															}else{
																$recuring_cycles[$d]['cost'] =	$test[$d]['cost']+$subs_pack['cost'];
															}
														}
													}							
													//mgm_pr($recuring_cycles);
												}
											}									
											
											if($num_cycles == 0 && $num_cycles != '' && !empty($subs_pack)){
											
												$lp_date = $last_pay_date;	
												//days
												if($other_member->duration_type == 'd'){
													$i=1;	
													while (strtotime($lp_date) > $other_member->join_date && strtotime($lp_date) >= strtotime($start_date)) {
														$duration = $i * $subs_pack['duration'];
														$d =date('Y-m-d',strtotime("$last_pay_date - ".$duration." days"));	
														$lp_date =$d;
														$i++;
														
														if(strtotime($lp_date) > $other_member->join_date && strtotime($d) >= strtotime($start_date) && strtotime($d) <=strtotime($end_date)) {
															if(!isset($on_going[$d])){
																$dt[]=$d;
																$on_going[$d]['cost'] =	$subs_pack['cost'];
																$on_going[$d]['last_pay_date'] = $d;
															}else{
																$on_going[$d]['cost'] =$on_going[$d]['cost']+$subs_pack['cost'];
															}								
														}
													}
												}//days end						
		
												//months
												if($other_member->duration_type == 'm'){
													$i=1;
													while (strtotime($lp_date) > $other_member->join_date && strtotime($lp_date) >= strtotime($start_date)) {
														$duration = $i * $subs_pack['duration'];
														$d =date('Y-m-d',strtotime("$last_pay_date - ".$duration." months"));	
														$lp_date =$d;
														$i++;
															
														if(strtotime($lp_date) > $other_member->join_date && strtotime($d) >= strtotime($start_date) && strtotime($d) <=strtotime($end_date)) {
															if(!isset($on_going[$d])){
																$dt[]=$d;
																$on_going[$d]['cost'] =	$subs_pack['cost'];
																$on_going[$d]['last_pay_date'] = $d;
															}else{
																$on_going[$d]['cost'] =$on_going[$d]['cost']+$subs_pack['cost'];
															}								
														}
													}
												}//moths end						
					
												
												//years
												if($other_member->duration_type == 'y'){
													$i=1;	
													while (strtotime($lp_date) > $other_member->join_date && strtotime($lp_date) >= strtotime($start_date)) {
														$duration = $i * $subs_pack['duration'];
														$d =date('Y-m-d',strtotime("$last_pay_date - ".$duration." years"));	
														$lp_date =$d;
														$i++;
														
														if(strtotime($lp_date) > $other_member->join_date && strtotime($d) >= strtotime($start_date) && strtotime($d) <=strtotime($end_date)) {
															if(!isset($on_going[$d])){
																$dt[]=$d;
																$on_going[$d]['cost'] =	$subs_pack['cost'];
																$on_going[$d]['last_pay_date'] = $d;
															}else{
																$on_going[$d]['cost'] = $on_going[$d]['cost']+$subs_pack['cost'];
															}								
														}
													}
												}//years end									
											}									
										}
									}
								}
								unset($other_member);							
							}
						}
					}					
					unset($user);
					unset($member);					
				} //users
			}					
		}

		sort($dt);
		$dt = array_unique ($dt);
		$dcount = count($dt);
					
		$recuring = array();
		// MMigrating the recuring limited rebilled  and on going recuring to array
		for($i = 0;$i < $dcount; $i++){

			$udt = $dt[$i];
			
			if(!empty($udt)) {
				$data[$udt]['date'] = $udt;
				//issue #1311
				if (isset($recuring_cycles[$udt]['cost']) && array_key_exists($udt, $recuring_cycles)) {	
					$recuring[$udt]['cost'] = $recuring[$udt]['cost'] + $recuring_cycles[$udt]['cost'];
				}
				if (isset($on_going[$udt]['cost']) && array_key_exists($udt, $on_going)) {
					$recuring[$udt]['cost'] = $recuring[$udt]['cost'] + $on_going[$udt]['cost'];
				}
			}
		}
		// return recurring payment dates and recurring payments
		return array('dates'=>$dt,'recurring'=>$recuring);		
	}

	//return the excepted recurring earnings 
	function get_expected_recurring_earning($date_start,$date_end,$member_type){
		global $wpdb;	
		$start = 0;
		$limit = 1000;
		//user meta fields
		$fields= array('user_id','meta_value');	
		// sql
		$sql = "SELECT count(*) FROM `{$wpdb->usermeta}` WHERE `meta_key` = 'mgm_member_options' AND `user_id` <> 1";	
		$count  = $wpdb->get_var($sql);		
		// init 
		$members = array();	
		// packs
		$packs = mgm_get_class('subscription_packs');
		$sformat = mgm_get_date_format('date_format_short');
		$curr_date = mgm_get_current_datetime();
		$current_date = $curr_date['timestamp'];	
		$on_going = array();
		$recuring_cycles = array();
		$dt = array();	

		//setting the default dates	
		if(empty($date_end)){
			$end_date =date('Y-m-d',$current_date);
			$end_date = date('Y-m-d',strtotime("$end_date + 6 months"));	
		}else{
			$end_date = mgm_format_inputdate_to_mysql($date_end,$sformat);						
			//$end_date=str_replace('/','-',$date_end);
		}

		if(!empty($date_start)){
			$start_date = mgm_format_inputdate_to_mysql($date_start,$sformat);						
			//$start_date=str_replace('/','-',$date_start);
			if( strtotime($start_date) > $current_date){
				$current_date = strtotime($start_date);
			}
		}		
		//count	
		if($count) {
			
			for( $k = $start; $k < $count; $k = $k + $limit ) {
				$users = mgm_patch_partial_user_member_options($k, $limit, $fields);				
				
				foreach($users as $user) {	
					$user_id = $user->user_id;
					$member = unserialize($user->meta_value);
					// convert
					$member = mgm_convert_array_to_memberobj($member, $user_id);
					
					$expire_date = $member->expire_date;
					$membership_type =$member->membership_type;
					if(!empty($expire_date) && $member->status =='Active' && $member->amount > 0){
		
						if($membership_type ==$member_type || $member_type == 'all'){
							
							if(strtotime($expire_date ) >= $current_date){
								//echo "<br/>Expire_date : ".$expire_date;
								//echo "<br/>Current_date : ".date('Y-m-d',$current_date);
								
								// check pack		
								$subs_pack = null;		
												
								if($member->pack_id){
									$subs_pack = $packs->get_pack($member->pack_id);
								}
								
								if(empty($subs_pack)){
									
									if($member_type != 'all'){
										$subs_pack = $packs->validate_pack($member->amount, $member->duration, $member->duration_type, $member_type);
									}else {
										$subs_pack = $packs->validate_pack($member->amount, $member->duration, $member->duration_type, $membership_type);
									}
								}
									
		
								$num_cycles = (isset($member->active_num_cycles) && !empty($member->active_num_cycles)) ? $member->active_num_cycles : $subs_pack['num_cycles'] ;
								
								$num_cycles =trim($num_cycles);
								$rebilled = 0;						
		
								if($num_cycles > 0 ){		
									
									$rebilled = $member->rebilled;
									
									if($num_cycles > $rebilled){
										
										$num = $num_cycles - $rebilled ;
										
										for($i=1 ; $i <= $num ;$i++){
			
											$duration = $i * $subs_pack['duration'];								
											//days
											if($member->duration_type == 'd'){
												$d =date('Y-m-d',strtotime("$expire_date + ".$duration." days"));	
											}
											//months
											if($member->duration_type == 'm'){
												$d =date('Y-m-d',strtotime("$expire_date + ".$duration." months"));	
											}
											//years
											if($member->duration_type == 'y'){
												$d =date('Y-m-d',strtotime("$expire_date + ".$duration." years"));	
											}
											if(strtotime($d) <=strtotime($end_date)) {
												if(!isset($recuring_cycles[$d])){
													$dt[]=$d;
													$recuring_cycles[$d]['cost'] =	$subs_pack['cost'];
													$recuring_cycles[$d]['expire_date'] = $d;
												}else{
													$recuring_cycles[$d]['cost'] =	$test[$d]['cost']+$subs_pack['cost'];
												}
											}
										}
			
										
									}
			
								}//num_cycles >0
								
								if($num_cycles == 0 && $num_cycles != '' && !empty($subs_pack)){
		
									$exp_date = $expire_date;
		
									//days
									if($member->duration_type == 'd'){
										$i=1;	
										while (strtotime($exp_date) <=strtotime($end_date)) {
											$duration = $i * $subs_pack['duration'];
											$d =date('Y-m-d',strtotime("$expire_date + ".$duration." days"));	
											$exp_date =$d;
											$i++;
											
											if(strtotime($d) <=strtotime($end_date)) {
												if(!isset($on_going[$d])){
													$dt[]=$d;
													$on_going[$d]['cost'] =	$subs_pack['cost'];
													$on_going[$d]['expire_date'] = $d;
												}else{
													$on_going[$d]['cost'] =$on_going[$d]['cost']+$subs_pack['cost'];
												}								
											}
										}
									}//days end						
		
									//months
									if($member->duration_type == 'm'){
										$i=1;	
										while (strtotime($exp_date) <=strtotime($end_date)) {
											$duration = $i * $subs_pack['duration'];
											$d =date('Y-m-d',strtotime("$expire_date + ".$duration." months"));	
											$exp_date =$d;
											$i++;
											
											if(strtotime($d) <=strtotime($end_date)) {
												if(!isset($on_going[$d])){
													$dt[]=$d;
													$on_going[$d]['cost'] =	$subs_pack['cost'];
													$on_going[$d]['expire_date'] = $d;
												}else{
													$on_going[$d]['cost'] =$on_going[$d]['cost']+$subs_pack['cost'];
												}								
											}
										}
									}//moths end						
		
									
									//years
									if($member->duration_type == 'y'){
										$i=1;	
										while (strtotime($exp_date) <=strtotime($end_date)) {
											$duration = $i * $subs_pack['duration'];
											$d =date('Y-m-d',strtotime("$expire_date + ".$duration." years"));	
											$exp_date =$d;
											$i++;
											
											if(strtotime($d) <=strtotime($end_date)) {
												if(!isset($on_going[$d])){
													$dt[]=$d;
													$on_going[$d]['cost'] =	$subs_pack['cost'];
													$on_going[$d]['expire_date'] = $d;
												}else{
													$on_going[$d]['cost'] = $on_going[$d]['cost']+$subs_pack['cost'];
												}								
											}
										}
									}//years end		
									
								}//$num_cycles ==0
							}				
							//EXPIRE DATE GRATER THAN CURRENT DATE
						}//Membership Type
					}//EMPTY EXPIRE DATE
			
					//checking recurring payments for other membership levels of a user
					if(isset($member->other_membership_types) && is_object($member->other_membership_types) && !empty($member->other_membership_types) ) {
						
						foreach ($member->other_membership_types as $key => $other_member) {
							
							if(!empty($other_member)){
								
								$other_member = mgm_convert_array_to_memberobj($other_member, $user_id);						
								
								$membership_type =$other_member->membership_type;
								
								$expire_date = $other_member->expire_date;
						
								if(!empty($expire_date) && $other_member->status =='Active' && $other_member->amount > 0){
									
									if($membership_type ==$member_type || $member_type == 'all'){
										
										if(strtotime($expire_date ) >= $current_date){
											//echo "<br/>Expire_date : ".$expire_date;
											//echo "<br/>Current_date : ".date('Y-m-d',$current_date);
											
											// check pack		
											$subs_pack = null;		
															
											if($other_member->pack_id){
												$subs_pack = $packs->get_pack($other_member->pack_id);
											}
											
											if(empty($subs_pack)){
												
												if($member_type != 'all'){
													$subs_pack = $packs->validate_pack($other_member->amount, $other_member->duration, $other_member->duration_type, $member_type);
												}else {
													$subs_pack = $packs->validate_pack($other_member->amount, $other_member->duration, $other_member->duration_type, $membership_type);
												}
											}
												
					
											$num_cycles = (isset($other_member->active_num_cycles) && !empty($other_member->active_num_cycles)) ? $other_member->active_num_cycles : $subs_pack['num_cycles'] ;
											
											$num_cycles =trim($num_cycles);
											$rebilled = 0;						
					
											if($num_cycles > 0 ){		
												
												$rebilled = $member->rebilled;
												
												if($num_cycles > $rebilled){
													
													$num = $num_cycles - $rebilled ;
													
													for($i=1 ; $i <= $num ;$i++){
						
														$duration = $i * $subs_pack['duration'];								
														//days
														if($other_member->duration_type == 'd'){
															$d =date('Y-m-d',strtotime("$expire_date + ".$duration." days"));	
														}
														//months
														if($other_member->duration_type == 'm'){
															$d =date('Y-m-d',strtotime("$expire_date + ".$duration." months"));	
														}
														//years
														if($other_member->duration_type == 'y'){
															$d =date('Y-m-d',strtotime("$expire_date + ".$duration." years"));	
														}
														if(strtotime($d) <=strtotime($end_date)) {
															if(!isset($recuring_cycles[$d])){
																$dt[]=$d;
																$recuring_cycles[$d]['cost'] =	$subs_pack['cost'];
																$recuring_cycles[$d]['expire_date'] = $d;
															}else{
																$recuring_cycles[$d]['cost'] =	$test[$d]['cost']+$subs_pack['cost'];
															}
														}
													}
						
													
												}
						
											}//num_cycles >0
											
											if($num_cycles == 0 && $num_cycles != '' && !empty($subs_pack)){
					
												$exp_date = $expire_date;
					
												//days
												if($other_member->duration_type == 'd'){
													$i=1;	
													while (strtotime($exp_date) <=strtotime($end_date)) {
														$duration = $i * $subs_pack['duration'];
														$d =date('Y-m-d',strtotime("$expire_date + ".$duration." days"));	
														$exp_date =$d;
														$i++;
														
														if(strtotime($d) <=strtotime($end_date)) {
															if(!isset($on_going[$d])){
																$dt[]=$d;
																$on_going[$d]['cost'] =	$subs_pack['cost'];
																$on_going[$d]['expire_date'] = $d;
															}else{
																$on_going[$d]['cost'] =$on_going[$d]['cost']+$subs_pack['cost'];
															}								
														}
													}
												}//days end						
					
												//months
												if($other_member->duration_type == 'm'){
													$i=1;	
													while (strtotime($exp_date) <=strtotime($end_date)) {
														$duration = $i * $subs_pack['duration'];
														$d =date('Y-m-d',strtotime("$expire_date + ".$duration." months"));	
														$exp_date =$d;
														$i++;
														
														if(strtotime($d) <=strtotime($end_date)) {
															if(!isset($on_going[$d])){
																$dt[]=$d;
																$on_going[$d]['cost'] =	$subs_pack['cost'];
																$on_going[$d]['expire_date'] = $d;
															}else{
																$on_going[$d]['cost'] =$on_going[$d]['cost']+$subs_pack['cost'];
															}								
														}
													}
												}//moths end						
					
												
												//years
												if($other_member->duration_type == 'y'){
													$i=1;	
													while (strtotime($exp_date) <=strtotime($end_date)) {
														$duration = $i * $subs_pack['duration'];
														$d =date('Y-m-d',strtotime("$expire_date + ".$duration." years"));	
														$exp_date =$d;
														$i++;
														
														if(strtotime($d) <=strtotime($end_date)) {
															if(!isset($on_going[$d])){
																$dt[]=$d;
																$on_going[$d]['cost'] =	$subs_pack['cost'];
																$on_going[$d]['expire_date'] = $d;
															}else{
																$on_going[$d]['cost'] = $on_going[$d]['cost']+$subs_pack['cost'];
															}								
														}
													}
												}//years end		
												
											}//$num_cycles ==0
										}				
										//EXPIRE DATE GRATER THAN CURRENT DATE
									}//Membership Type								
								}						
							}
						}
					}									
				}//users				
				
			}
		}
		//exit;
		sort($dt);
		$dt = array_unique ($dt);

		$dcount = count($dt);
		$data[0]['date_start'] = $date_start;
		$data[0]['date_end'] = $date_end;
		$data[0]['member_type'] = $member_type;
		
		// Migrating the recuring  and on going data to array
		for($i = 0;$i < $dcount; $i++){

			$udt = $dt[$i];
			
			if(!empty($udt)) {
				$data[$i]['date'] = $udt;
				//issue #1311
				if (isset($recuring_cycles[$udt]['cost']) && array_key_exists($udt, $recuring_cycles)) {	
					$data[$i]['cost'] = $data[$i]['cost'] + $recuring_cycles[$udt]['cost'];
				}
				if (isset($on_going[$udt]['cost']) && array_key_exists($udt, $on_going)) {
					$data[$i]['cost'] = $data[$i]['cost'] + $on_going[$udt]['cost'];
				}
			}
		}

		return $data;						
	}
 
	// sales list
	function sales_list(){	
				
		$data = array();	
		
		if(isset($_POST['bk_date_start'])){
			$date_start = $_POST['bk_date_start'];				
		}else {
			$date_start='';
		}
		
		if(isset($_POST['bk_date_end'])){
			$date_end   = $_POST['bk_date_end'];
		}else {
			$date_end='';
		}
		if(isset($_POST['bk_membership_type'])){
			$member_type   = $_POST['bk_membership_type'];
		}else {
			$member_type='all';
		}
		// getting the sales result
		$data = $this->get_sales($date_start,$date_end,$member_type);

		// load template view
		$this->load->template('reports/sales/list', array('data'=>$data));	
	}

	// return sales list
	function get_sales($date_start,$date_end,$member_type){

		// global
		global $wpdb;	
		$data = array();	

		// preparing query based on dates
		if(!empty($date_start) && !empty($date_end)){
			
/*			$date_end=str_replace('/','-',$date_end);
			$end_date  = date('Y-m-d', strtotime( $date_end));
				
			$date_start=str_replace('/','-',$date_start);
			$start_date  = date('Y-m-d',  strtotime( $date_start));	
*/			
			//issue #1311
			$sformat = mgm_get_date_format('date_format_short');
			
			$start_date = mgm_format_inputdate_to_mysql($date_start,$sformat);	
			$end_date = mgm_format_inputdate_to_mysql($date_end,$sformat);						

			$start_date .= ' 00:00:00';
			$end_date .= ' 23:59:59';

			//Issue #733
			//$condition =" WHERE transaction_dt BETWEEN  '$start_date' AND  '$end_date'";
			$condition =" AND status_text = 'Last payment was successful' AND transaction_dt BETWEEN  '$start_date' AND  '$end_date'";

		}else{
			//Issue #733
			//$condition =" ORDER BY  `transaction_dt` DESC LIMIT 10";
			$condition ="  AND status_text = 'Last payment was successful' ORDER BY  `transaction_dt` DESC LIMIT 10";			
		}
		// sql
		$sql = "SELECT * FROM `".TBL_MGM_TRANSACTION ."` WHERE 1 {$condition}";
		// row
		$rows  = $wpdb->get_results($sql);
		// reset data
		
		$subscription = array();
		$purchase = array();
		$d = array();

		// looping the results
		foreach ($rows as $row){

			$row->data = json_decode($row->data,true);

			extract($row->data);
			// Storing subscription data to array
			if(trim($row->payment_type) =='subscription_purchase'){

					$dt=strtok($row->transaction_dt," ");
					
					if(isset($subscription[$dt])){
						// validating membership type
						if($membership_type ==$member_type || $member_type == 'all'){
							$subscription[$dt]['date']=	$dt;
					   		//issue #1311
							if($trial_on) {
								$subscription[$dt]['cost'] += $trial_cost; 
							}else {
								$subscription[$dt]['cost'] +=$cost;
							}
							
							$subscription[$dt]['count'] ++;
						}
					}else {
						// validating membership type
						if($membership_type ==$member_type || $member_type == 'all'){
							$d[] = $dt;	
							$subscription[$dt]['date']=	$dt;
					   		//issue #1311
							if($trial_on) {
								$subscription[$dt]['cost'] += $trial_cost; 
							}else {
								$subscription[$dt]['cost'] +=$cost;
							}
							$subscription[$dt]['count'] =1;
						}
					}
			}
			// Storing pay per post data to array
			if(trim($row->payment_type) =='post_purchase'){

				$dt=strtok($row->transaction_dt," ");
				
				if(isset($purchase[$dt])){
					// validating membership type
					if($membership_type ==$member_type || $member_type == 'all'){
						$purchase[$dt]['date']=	$dt;
						$purchase[$dt]['cost'] +=$cost;
						$purchase[$dt]['count'] ++;
					}
				}else {
					// validating membership type
					if($membership_type ==$member_type || $member_type == 'all'){
						$d[] = $dt;	
						$purchase[$dt]['date']=	$dt;
						$purchase[$dt]['cost'] =$cost;
						$purchase[$dt]['count'] =1;

					}						
				}
			}
			
		}

		$d = array_unique ($d);
		$dcount = count($d);
		$data[0]['date_start'] = $date_start;
		$data[0]['date_end'] = $date_end;
		$data[0]['member_type'] = $member_type;

		// Migrating the pay per post and subscription data to array
		for($i = 0;$i < $dcount; $i++){

			$udt = $d[$i];
			
			if(!empty($udt)) {
				$data[$i]['date'] = $udt;
				
				if (array_key_exists($udt, $purchase)) {
					
					$data[$i]['purchase'] = $purchase[$udt]['cost'];
					$data[$i]['pcount'] = $purchase[$udt]['count'];
				}else {
					$data[$i]['purchase'] = 0;
					$data[$i]['pcount'] = 0;
					
				}
				
				if (array_key_exists($udt, $subscription)) {
	
					$data[$i]['subscription'] = $subscription[$udt]['cost'];
					$data[$i]['scount'] = $subscription[$udt]['count'];
				}else {
					$data[$i]['subscription'] = 0;
					$data[$i]['scount'] = 0;
					
				}

			}
		}
		return $data;	
	}

/*	//Download sales pdf
	function download_sales_pdf(){
		
		$data = array();	
		
		if(isset($_GET['bk_date_start'])){
			$date_start = $_GET['bk_date_start'];				
		}else {
			$date_start='';
		}
		
		if(isset($_GET['bk_date_end'])){
			$date_end   = $_GET['bk_date_end'];
		}else {
			$date_end='';
		}
		if(isset($_GET['bk_membership_type'])){
			$member_type   = $_GET['bk_membership_type'];
		}else {
			$member_type='all';
		}
		if (isset($_GET['bk_report_type'] )){
			$report_type = $_GET['bk_report_type'];
		} else {
			$report_type = '';
		}
		
		if (!empty($report_type) && $report_type == 'earnings' ) {
			$data = $this->get_earnings($date_start,$date_end,$member_type);
			$this->load->template('reports/earnings/downloadpdf', array('data'=>$data));
		} 
		elseif (!empty($report_type) && $report_type == 'projection' ) {
			$data = $this->get_expected_recurring_earning($date_start,$date_end,$member_type);
			$this->load->template('reports/projection/downloadpdf', array('data'=>$data));
		}		
		else {
			$data = $this->get_sales($date_start,$date_end,$member_type);
			$this->load->template('reports/sales/downloadpdf', array('data'=>$data));
		}		
	}	
*/
	// earnings
	function earnings_index() {	
        $data = array();	
		// load template view
		$this->load->template('reports/earnings/index', array('data'=>$data));	
	}
		
	// earnings list
	function earnings_list(){	
				
		$data = array();	
		
		if(isset($_POST['bk_date_start'])){
			$date_start = $_POST['bk_date_start'];				
		}else {
			$date_start='';
		}
		
		if(isset($_POST['bk_date_end'])){
			$date_end   = $_POST['bk_date_end'];
		}else {
			$date_end='';
		}
		if(isset($_POST['bk_membership_type'])){
			$member_type   = $_POST['bk_membership_type'];
		}else {
			$member_type='all';
		}
		// getting the sales result
		$data = $this->get_earnings($date_start,$date_end,$member_type);

		// load template view
		$this->load->template('reports/earnings/list', array('data'=>$data));	
	}

	// return earnings list
	function get_earnings($date_start,$date_end,$member_type){

		// global
		global $wpdb;	
		$data = array();	
		
		$curr_date = mgm_get_current_datetime();
		$current_date = $curr_date['timestamp'];
		
		// preparing query based on dates
		if(!empty($date_start) && !empty($date_end)){
/*			$date_end=str_replace('/','-',$date_end);
			$end_date  = date('Y-m-d', strtotime( $date_end));
			
			$date_start=str_replace('/','-',$date_start);	
			$start_date  = date('Y-m-d',  strtotime( $date_start));	
*/
			//issue #1311
			$sformat = mgm_get_date_format('date_format_short');

			$start_date = mgm_format_inputdate_to_mysql($date_start,$sformat);	
			$end_date = mgm_format_inputdate_to_mysql($date_end,$sformat);						
			
			$start_date .= ' 00:00:00';
			$end_date .= ' 23:59:59';
			
			//Issue #733
			//$condition =" WHERE transaction_dt BETWEEN  '$start_date' AND  '$end_date'";
			$condition = " AND status_text = 'Last payment was successful' AND transaction_dt BETWEEN  '$start_date' AND  '$end_date'";

		}else{
		
			//setting the default end date	
			if(empty($date_end)){
				$date_end = $end_date =date('Y-m-d',$current_date);
				$end_date .= ' 23:59:59';
			}
			//setting the default start date
			if(empty($date_start)){
				$date_start=$start_date = date('Y-m-d',strtotime("$end_date - 3 months"));
				$start_date .= ' 00:00:00';	
			}			
			
			//$condition =" WHERE transaction_dt BETWEEN  '$start_date' AND  '$end_date'";
			$condition = " AND status_text = 'Last payment was successful' AND transaction_dt BETWEEN  '$start_date' AND  '$end_date'";
			
			//Issue #733
			//$condition =" ORDER BY  `transaction_dt` DESC LIMIT 10";
			//$condition = " AND status_text = 'Last payment was successful' ORDER BY  `transaction_dt` DESC LIMIT 10";			
		}
		// sql
		$sql = "SELECT * FROM `" . TBL_MGM_TRANSACTION . "` WHERE 1 {$condition}";
		// mgm_log($sql);	
		// row
		$rows  = $wpdb->get_results($sql);
		// reset data
		
		$subscription = array();
		$purchase = array();
		$d = array();

		// looping the results
		foreach ($rows as $row){

			$row->data = json_decode($row->data,true);

			extract($row->data);
			// Storing subscription data to array
			if(trim($row->payment_type) =='subscription_purchase'){

				$dt=strtok($row->transaction_dt," ");
				
				if (isset($subscription[$dt])) {
					if ($membership_type ==$member_type || $member_type == 'all') {
						$subscription[$dt]['date']=	$dt;
				   		//issue #1311
						if($trial_on) {
							$subscription[$dt]['cost'] += $trial_cost; 
						}else {
							$subscription[$dt]['cost'] +=$cost;
						}								
						//$subscription[$dt]['cost'] +=$cost;								
						$subscription[$dt]['count'] ++;
					}
				} else {
					if ($membership_type ==$member_type || $member_type == 'all') {
						$d[] = $dt;
						$subscription[$dt]['date']=	$dt;
				   		//issue #1311
						if($trial_on) {
							$subscription[$dt]['cost'] += $trial_cost; 
						}else {
							$subscription[$dt]['cost'] +=$cost;
						}								
						//$subscription[$dt]['cost'] =$cost;
						$subscription[$dt]['count'] =1;
					}
				}
			}
			// Storing pay per post data to array
			if (trim($row->payment_type) =='post_purchase') {

				$dt=strtok($row->transaction_dt," ");
				
				if (isset($purchase[$dt])) {
					// validating membership type
					if($membership_type ==$member_type || $member_type == 'all'){
						$purchase[$dt]['date']=	$dt;
						$purchase[$dt]['cost'] +=$cost;
						$purchase[$dt]['count'] ++;
					}
				} else {
					// validating membership type
					if ($membership_type ==$member_type || $member_type == 'all') {
						$d[] = $dt;	
						$purchase[$dt]['date']=	$dt;
						$purchase[$dt]['cost'] =$cost;
						$purchase[$dt]['count'] =1;

					}						
				}
			}
			
		}
		//getting recurring earnings and dates
		$recurring_data = $this->recurring_earnings($date_start,$date_end,$member_type);

		$d = array_unique ($d);
		$md = array_merge($d,$recurring_data['dates']);
		$d = array_unique ($md);
		
		sort($d);	
		
		$recurring =$recurring_data['recurring'];
		
		$dcount = count($d);
		$data[0]['date_start'] = $date_start;
		$data[0]['date_end'] = $date_end;
		$data[0]['member_type'] = $member_type;

		// Migrating the pay per post,recurring and subscription data to array
		for ($i = 0;$i < $dcount; $i++) {

			$udt = $d[$i];
			
			if (!empty($udt)) {
				$data[$i]['date'] = $udt;
				
				if (array_key_exists($udt, $purchase)) {
					
					$data[$i]['purchase'] = $purchase[$udt]['cost'];
				} else {
					$data[$i]['purchase'] = 0;
				}
				
				if (array_key_exists($udt, $subscription)) {
	
					$data[$i]['subscription'] = $subscription[$udt]['cost'];
				} else {
					$data[$i]['subscription'] = 0;
				}
				
				if (isset($recurring) && array_key_exists($udt, $recurring)) {

					$data[$i]['recurring'] = $recurring[$udt]['cost'];
				
				} else {
					$data[$i]['recurring'] = 0;
				}

			}
		}		
		return $data;	
	}

	// payment history
	function payment_history(){	
		$data = array();	
		// load template view
		$this->load->template('reports/payment_history/index', array('data'=>$data));	
	}

	// payment history list
	function payment_history_list(){	
		global $wpdb;		
		// pager
		$pager = new mgm_pager();
		// data
		$data = array();			
		// search fields
		$data['search_fields'] = array(''=> __('Select','mgm'), 'username'=> __('Username','mgm'), 'id'=> __('User ID','mgm'), 
		                              'email'=> __('User Email','mgm'), 'first_name' => __('First Name','mgm') ,'last_name' => __('Last Name','mgm'), 
									  'payment_type'=> __('Payment Type','mgm'), 'membership_type'=> __('Membership Type','mgm'));
		// sort fields							  
		$data['sort_fields'] = array('username'=> __('Username','mgm'), 'id'=> __('User ID','mgm'), 'email'=> __('User Email','mgm'));	

		// filter
		$sql_filter = $data['search_field_name'] = $data['search_field_value'] = '';
		
		$payment_type ='';
		// check
		if(isset($_POST['search_field_name'])) {
			// issue#: 219
			$search_field_name  = $_POST['search_field_name']; // for sql			
			$search_field_value = $wpdb->escape($_POST['search_field_value']);// for sql
			$search_field_value = trim($search_field_value);
			// view data	
			$data['search_field_name'] 	= $_POST['search_field_name'];						
			//issue #1281
			$data['search_field_value'] = htmlentities($_POST['search_field_value'], ENT_QUOTES, "UTF-8");// for display
			// by field
			switch($search_field_name){
				case 'username':
					// issue#: 347(LIKE SEARCH)
					$sql_filter = " AND user.user_login LIKE '%{$search_field_value}%'";	
				break;	
				case 'id':
					$sql_filter = " AND user.ID = '".(int)$search_field_value."'";	
				break;
				case 'email':
					// issue#: 347(LIKE SEARCH)
					$sql_filter = " AND user.user_email LIKE '%{$search_field_value}%'";			
				break;	
				case 'membership_type':
					// members
					$members    = mgm_get_members_with('membership_type', $search_field_value);
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND user.ID IN ({$members_in})";			
				break;	
				case 'payment_type':
					$payment_type =$search_field_value;
				break;
				case 'first_name':
				case 'last_name':
					// members
					$members    = mgm_get_members_with($search_field_name, $search_field_value);
					//check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND user.ID IN ({$members_in})";
				break;
			}
		}
				
		// page limit		
		$data['page_limit'] = isset($_REQUEST['page_limit']) ? (int)$_REQUEST['page_limit'] : 20;// 20
		// page no
		$data['page_no']    = isset($_REQUEST['page_no']) ? (int)$_REQUEST['page_no'] : 1;		
		// limit
		$sql_limit = $pager->get_query_limit($data['page_limit']);	

		// page url
		$data['page_url'] = 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=payment_history_list';
		// search term
		$search_term = '';
		// search provided
		if( !empty($data['search_field_value']) ){
			$search_term = sprintf('where <b>%s</b> is <b>%s</b>', (isset($data['search_fields'][$search_field_name]) ? $data['search_fields'][$search_field_name] : ''), $data['search_field_value']);
		}	

		if(!empty($payment_type)){
			$con = " AND transaction.payment_type =  '".$payment_type."' ";
		}else {
			$con ='';
		}
		//payment success check
		$pay_succ = " AND transaction.status_text =  'Last payment was successful'";

		$transaction_sql = "SELECT SQL_CALC_FOUND_ROWS * FROM ".TBL_MGM_TRANSACTION." transaction LEFT JOIN {$wpdb->users} user ON transaction.user_id = user.ID ";
		$transaction_sql .= "WHERE transaction.module IS NOT NULL";
		$transaction_sql .= $pay_succ;
		$transaction_sql .= $con;
		$transaction_sql .= $sql_filter;
		$transaction_sql .= " ORDER BY transaction.transaction_dt DESC {$sql_limit}";

		$data['transactions'] =  $wpdb->get_results($transaction_sql);
		
		if(!empty($data['transactions'])){
			// get page links
			$data['page_links'] = $pager->get_pager_links($data['page_url']);	
			// total pages
			$data['page_count'] = $pager->get_page_count();
			// total rows/results
			$data['row_count']  = $pager->get_row_count();
			// message
			$data['message'] = sprintf(__('%d %s matched %s','mgm'), $data['row_count'], ($data['row_count']>1 ? 'transactions' : 'transaction'), $search_term);				
		}else {
			// message
			$data['message'] = sprintf(__(' No transactions matched %s','mgm'), $search_term);				
		}
			
		// load template view
		$this->load->template('reports/payment_history/list', array('data'=>$data));	
	}

	//export payment history
	function payment_history_export(){

		global $wpdb;		
		// data
		$data = array();			
		// filter
		$sql_filter = $payment_type = '';
		 
		// check
		if(isset($_POST['search_field_name'])) {
			// issue#: 219
			$search_field_name  = $_POST['search_field_name']; // for sql			
			$search_field_value = $wpdb->escape($_POST['search_field_value']);// for sql
			$search_field_value = trim($search_field_value);
			// by field
			switch($search_field_name){
				case 'username':
					// issue#: 347(LIKE SEARCH)
					$sql_filter = " AND user.user_login LIKE '%{$search_field_value}%'";	
				break;	
				case 'id':
					$sql_filter = " AND user.ID = '".(int)$search_field_value."'";	
				break;
				case 'email':
					// issue#: 347(LIKE SEARCH)
					$sql_filter = " AND user.user_email LIKE '%{$search_field_value}%'";			
				break;	
				case 'membership_type':
					// members
					$members    = mgm_get_members_with('membership_type', $search_field_value);
					// check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND user.ID IN ({$members_in})";			
				break;	
				case 'payment_type':
					$payment_type =$search_field_value;
				break;
				case 'first_name':
				case 'last_name':
					// members
					$members    = mgm_get_members_with($search_field_name, $search_field_value);
					//check
					$members_in = (count($members)==0) ? 0 : (implode(',', $members));
					// set filter
					$sql_filter = " AND user.ID IN ({$members_in})";
				break;
			}
		}

		if(!empty($payment_type)){
			$con = " AND transaction.payment_type =  '".$payment_type."' ";
		}else {
			$con ='';
		}
		//payment success check
		$pay_succ = " AND transaction.status_text =  'Last payment was successful'";

		$transaction_sql = "SELECT SQL_CALC_FOUND_ROWS * FROM ".TBL_MGM_TRANSACTION." transaction LEFT JOIN {$wpdb->users} user ON transaction.user_id = user.ID ";
		$transaction_sql .= "WHERE transaction.module IS NOT NULL";
		$transaction_sql .= $pay_succ;
		$transaction_sql .= $con;
		$transaction_sql .= $sql_filter;
		$transaction_sql .= " ORDER BY transaction.transaction_dt DESC {$sql_limit}";

		$data['transactions'] =  $wpdb->get_results($transaction_sql);		
		$export_transactions = array();
		if(count($data['transactions'])>0): 
	 		foreach($data['transactions'] as $tran_log):
	 			$json_decoded = json_decode($tran_log->data);
				$user_obj = get_userdata($json_decoded->user_id);
				//empty obj
				$row = new stdClass();				
				if(!empty($user_obj)) {
					//export fields
					$row->id           		= $user_obj->ID;
					$row->username     		= $user_obj->user_login;
					$row->firstname    		= $user_obj->first_name ;
					$row->lastname     		= $user_obj->last_name;
					$row->email        		= $user_obj->user_email;
					$row->payment_type 		= ucwords(str_replace('_',' ',$tran_log->payment_type));
					$row->module 	   		= ucwords($tran_log->module);
					$row->amount 	       	= ($json_decoded->trial_on) ? $json_decoded->trial_cost : $json_decoded->cost;
					$row->transaction_date 	= date(MGM_DATE_FORMAT_SHORT,strtotime($tran_log->transaction_dt));
				}
				// cache
				$export_transactions[] = $row;	
				// unset 
				unset($row);
 			endforeach;
 		endif;
		
		// message
		$message = __('Error while exporting transactions. Could not find any transaction with requested search parameters.', 'mgm');
	 	// default response
		$response = array('status'=>'error','message' => $message);
	
		// check
		if (count($export_transactions)>0) {
			// success
			$success = count($export_transactions);
			// create
			if(mgm_post_var('export_format') == 'csv'){
				$filename= mgm_create_csv_file($export_transactions, 'export_transactions');			
			}else{
				$filename= mgm_create_xls_file($export_transactions, 'export_transactions');			
			}			
			// src
			$file_src = MGM_FILES_EXPORT_URL . $filename;				
			// message
			$response['message'] = sprintf(__('Successfully exported %d %s.', 'mgm'), $success, 
			($success>1 ? 'transactions' : 'transaction'));
			
			$response['status']  = 'success';
			$response['src']     = $file_src;
		}
		// return response
		echo json_encode($response); exit();
	}
	
 }
// return name of class 
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin_reports.php