//set module logo image h/w
				//default:in px
//				$img_width = 70;
//				$img_height = 60;
//				$arr_img = @getimagesize($img_url);
//				if(!empty($arr_img)) {
//					//get higher dimension
//					if($arr_img[0] > $arr_img[1] ) {
//						$img_height = 0;
//						$img_width = ($arr_img[0] >= $img_width) ? $img_width: $arr_img[0]; 
//					}else {
//						$img_width = 0;
//						$img_height = ($arr_img[1] >= $img_height) ? $img_height: $arr_img[1]; 
//					}
//				}else 
//					$img_width = 0;
// (sprintf('<img style="margin:10px 0px 0px 15px;"  alt="%s" %s %s />',  $img_url, $mod_obj->name, ($img_width > 0 ? 'width="'.$img_width.'px"' : ''), ($img_height > 0 ? 'height="'.$img_height.'px"' : '') )) .




$html .= 'jQuery(document).ready(function() {'."\n".
						'//mgm_select_pack_modules();'."\n".
						//gateways will be an array of enabled modules
						'var mgm_update_payment_gateways=function(gateways) {'."\n".							
							//get module radios
							'var obj_radio = jQuery("#mgm_payment_gateways_container input[type=\'radio\']");'."\n".							
							'if(gateways.length == 0) {'."\n".
								//hide container								
								'obj_radio.each( function(){ jQuery("#"+this.value+"_container").fadeOut(); } );'."\n".
								'jQuery("#mgm_payment_gateways_container").fadeOut();'."\n".
							'}else {'."\n".
								//hide container just for animation
								'jQuery("#mgm_payment_gateways_container").fadeOut();'."\n".
								//loop through modules to show/hide appicable modules
								' obj_radio.each( function(){'."\n".
									'var modulecode = this.value;'."\n". 
									'var found = false;'."\n".								
									//'jQuery("#"+modulecode+"_container").hide();'."\n".									
									'jQuery.each(gateways,function(i,n){ if(modulecode == n){ found = true;} });'."\n".																		
									'//show/hide each module'."\n".
									'if(found) {'."\n".
										'jQuery("#"+modulecode+"_container").fadeIn();'."\n".
									'}else{'."\n".
										'jQuery("#"+modulecode+"_container").fadeOut();'."\n".
									'}'."\n".
								'} ); '."\n";
								//show container
								//NOTE: comment the below condition to enable module display even if only one exists
					if($module_count != 1)
									$html .= 'jQuery("#mgm_payment_gateways_container").fadeIn();'."\n";
					$html .='}';
							//unset previous selection
							//if only one module exists, uncheck obly for free/trial
							if($module_count === 1){
								//$html .= 'obj_radio.each( function(){ this.checked = (gateways.length == 0 ? false : true); } );'."\n";
								$html .= 'obj_radio.each( function(){ this.checked = true; } );'."\n";
							}else {
								//if multiple module exists, uncheck all							
								$html .= 'obj_radio.each( function(){ this.checked = false; } );'."\n";
							}
					$html .='}'."\n";
					
			$slect_pack_display = array();
			//bind the above function to click event of pack radios		
			foreach ($packs as $pack) {
				$subs_enc = mgm_encode_package($pack);
				$arr_modules = array_diff($pack['modules'], array('mgm_free', 'mgm_trial'));
				//issue #1072
				if ((float)$pack['cost'] == 0.00 && in_array('mgm_manualpay',(array)$pack['modules']) ){
					$arr_modules = array('mgm_manualpay');
				}
				//issue #1019
				if(isset($_POST['mgm_subscription'])){
					if($subs_enc == $_POST['mgm_subscription']){
						$temp_arr_modules = $arr_modules;
					}
				}
				//issue #1234
				if($dispaly_as_selectbox){
					$opt_id = 	$pack['membership_type'].$pack['id'];
					$slect_pack_display[] =	$opt_id;											
					$html .= 'jQuery("#mgm_payment_bindlist").append("<input type=hidden name='.$opt_id.' id='.$opt_id.' value ='.$subs_enc.' />");'."\n";	
					$html .= 'jQuery("#'.$opt_id.'").bind("click",function(){'."\n";
					$html .= 'mgm_update_payment_gateways('.(!empty($arr_modules) ? '[\''.implode('\',\'', $arr_modules).'\']' : '[]').');});'."\n";
				}else {
					$html .= 'jQuery(".mgm_subs_wrapper input[value=\''.$subs_enc.'\']").bind("click",function(){mgm_update_payment_gateways('.(!empty($arr_modules) ? '[\''.implode('\',\'', $arr_modules).'\']' : '[]').');});'."\n";				
				}
					
			}
			//issue #1019
			if(isset($_POST['mgm_subscription'])){
				$html .='mgm_update_payment_gateways('.(!empty($temp_arr_modules) ? '[\''.implode('\',\'', $temp_arr_modules).'\']' : '[]').');';	
			}
			
			//issue #1234
			if($dispaly_as_selectbox){
				
				$html .= 'jQuery("#'.$slect_pack_display[0].'").click();'."\n";

				$html .= 'jQuery("#mgm_subscription").bind("change", function() {'."\n";
		
					$html .= 'var id = jQuery(this).find(":selected")[0].id;'."\n";
					
					$html .= 'jQuery("#"+id.substring(4)).click();'."\n";
		
				$html .= '});'."\n";				
			}
						
			$html .= '});'."\n";




//mgm_pr($options);
		// check
		// if(count($options)) {
			// value
			// $value = $this->_filtered_value($field,$name,$value);
			// return
			// return mgm_make_checkbox_group(sprintf('%s[]',$this->_get_element_name($field,$name)),$options,$value,MGM_VALUE_ONLY,'','div');
		// }	
		// return default
		// return $this->field_type_input($field,$name,$value);	

/*
		//issue #1234
		if($dispaly_as_selectbox){
			$options .= '<option value="'.$subs_enc.'" id="'.$opt_id.'" '.$selected.'>' . mgm_stripslashes_deep($packs_obj->get_pack_desc($pack)) . '</option>';			
		}else {					
			// html
			$html.= '<div class="mgm_subs_wrapper '.$pack['membership_type'].'">
						 <div class="mgm_subs_option '.$pack['membership_type'].'">
							' . sprintf('<input type="radio" %s class="mgm_subs_radio" name="mgm_subscription" value="%s" />', $checked, $subs_enc) . '
						 </div>
						 <div class="mgm_subs_pack_desc '.$pack['membership_type'].'">
							' . mgm_stripslashes_deep($packs_obj->get_pack_desc($pack)) . '
						 </div>
						 <div class="clearfix '.$pack['membership_type'].'"></div>
						 <div class="mgm_subs_desc '.$pack['membership_type'].'">
							' . mgm_stripslashes_deep($pack['description']) . '
						 </div>
					 </div>';
		}


		//issue #1234
		if($dispaly_as_selectbox){
			$options .= '<option value="'.$subs_enc.'" id="'.$opt_id.'" '.$selected.'>' . mgm_stripslashes_deep($packs_obj->get_pack_desc($pack)) . '</option>';	
		}else {							
			$html.= '<div class="mgm_subs_wrapper '.$pack['membership_type'].'">
						 <div class="mgm_subs_option '.$pack['membership_type'].'">
							' . sprintf('<input type="radio" %s class="mgm_subs_radio" name="mgm_subscription" value="%s" />', $checked, $subs_enc) . '
						 </div>
						 <div class="mgm_subs_pack_desc '.$pack['membership_type'].'">
							' . mgm_stripslashes_deep($packs_obj->get_pack_desc($pack)) . '
						 </div>
						 <div class="clearfix '.$pack['membership_type'].'"></div>
						 <div class="mgm_subs_desc '.$pack['membership_type'].'">
							' . mgm_stripslashes_deep($pack['description']) . '
						 </div>
					 </div>';
		}
		*/