<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members html functions
 *
 * @package MagicMembers
 * @subpackage Facebook
 * @since 2.6
 */
 
/**
 * Combo Generator 
 * @param  Options Array  List
 * @param  Scalar/Array Default/Selected Value/Values
 * @param  String Type of Select Array [MGM_VALUE_ONLY : 1 / MGM_KEY_VALUE : 2]   
 */  
function mgm_make_combo_options($combo_opts, $combo_sel='', $combo_type=1, $skip_opts=false, $sel_match='DEFAULT'){		
	$combo = '';   	    
	// type
	switch($combo_type){		
		case 1:	// MGM_VALUE_ONLY
			// array only has values
			foreach($combo_opts as $opt_val){	
				// skip	
				if(is_array($skip_opts) && in_array($opt_val, $skip_opts)) continue;
				// start
				$combo.="<option value='$opt_val' ";
				// check
				if(isset($combo_sel)){
					// array
					if(is_array($combo_sel) && in_array($opt_val,$combo_sel)){						
						$combo .= " selected ";		   
					}else if(is_string($opt_val) && is_string($combo_sel) && trim($opt_val)==trim($combo_sel)){
						// scalar
						$combo .= " selected ";		  
					}else if(is_int($opt_val) && is_int($combo_sel) && $opt_val==$combo_sel){
						// scalar
						$combo .= " selected ";		  
					}
				}	 
				// end
				$combo.=">$opt_val</option>";
			}
		break;		
		case 2: // MGM_KEY_VALUE
			// array  has key and values
			foreach($combo_opts as $opt_key=>$opt_val){
				// skip	
				if(is_array($skip_opts) && in_array($opt_key, $skip_opts)) continue;
				// start
				$combo.="<option value='$opt_key' ";	
				// check	
				if(isset($combo_sel)){
					// array
					if(is_array($combo_sel) && in_array($opt_key,$combo_sel)){
						$combo .= " selected ";		   
					}else if(is_string($combo_sel)){
						if($sel_match=='DEFAULT' && trim($combo_sel)==trim($opt_key)){
						// string/int default match key
							$combo .= " selected ";		  
						}else if($sel_match=='REVERSE' && trim($combo_sel)==trim($opt_val)){
						// string/int reverse match value 
							$combo .= " selected ";	
						}		  
					}
				}
				// end
				$combo .= ">$opt_val</option>";
			}
		break; 
	} 
	// return
	return $combo;   
}
/**
 * alias
 */
function mgm_make_select_options($combo_opts, $combo_sel='', $combo_type=1, $skip_opts=false, $sel_match='DEFAULT'){
	return mgm_make_combo_options($combo_opts, $combo_sel, $combo_type, $skip_opts, $sel_match);
} 

# radio generator 
function mgm_make_radio_group($radio_gname,$radio_gopts,$radio_gsel,$combo_type=1,$event="",$attr="",$style="ul"){    
    $radio=""; 	
    switch(intval($combo_type)){
	  case 1:	   
	   // array only has values only
	   foreach($radio_gopts as $opt_val){
	     $radio.="%style_begin%<input type='radio' class='radio' name='{$radio_gname}' value='{$opt_val}'";
		 if($radio_gsel==$opt_val){
		   $radio.=" checked ";		  
		 }
		 $radio.=" valign='absmiddle' {$event}>&nbsp;{$opt_val}&nbsp;%style_end%";
	   }
	   break; 
	  case 2:
	   //array  has key and values
	   foreach($radio_gopts as $opt_key=>$opt_val){
	     $radio.="%style_begin%<input type='radio' class='radio' name='{$radio_gname}' value='{$opt_key}'";
		 if($radio_gsel==$opt_key){
		   $radio.=" checked ";
		 }
		 $radio.=" valign='absmiddle' {$event}>&nbsp;{$opt_val}&nbsp;%style_end%";
	   }
	   break;	  
	} 	
	
	// apply style
	switch($style){
		case "ul":
		case "ol":
		default:
			$radio = str_replace(array("%style_begin%","%style_end%"),array("<li><div>","</div></li>"),$radio);
			$radio = "<{$style} class='list-table'>{$radio}</{$style}>";
		break;
	}
	// return
	return $radio;
  }
  
# Checkbox generator 
# args : @name,@options array,@selected value/array,@type-key/value  
function mgm_make_checkbox_group($check_gname,$check_gopts,$check_gsel,$combo_type=1,$attr="",$style="ul"){    
    $checkbox="";   	
	
    switch(intval($combo_type)){
	  case 1:	   
	   // array only has values
	   foreach($check_gopts as $opt_val){
	     $checkbox.="%style_begin%<input type='checkbox' class='radio' name='{$check_gname}' value='{$opt_val}' {$attr}";
		 if(is_array($check_gsel) && in_array($opt_val,$check_gsel)){
		   $checkbox.=" checked ";		   
		 }else if(is_string($check_gsel) && trim($check_gsel)==trim($opt_val)){
		   $checkbox.=" checked ";
	     }		 
		 $checkbox.=" valign='absmiddle'>&nbsp;$opt_val&nbsp;%style_end%";
	   }
	   break; 
	  case 2:
	   //array  has key and values
	   foreach($check_gopts as $opt_key=>$opt_val){
	     $checkbox.="%style_begin%<input type='checkbox' class='radio' name='{$check_gname}' value='{$opt_key}' {$attr}";
		 if(is_array($check_gsel) && in_array($opt_key,$check_gsel)){
		   $checkbox.=" checked ";		   
		 }else if(is_string($check_gsel) && trim($check_gsel)==trim($opt_key)){
		   $checkbox.=" checked ";
	     }			 
		 $checkbox.=" valign='absmiddle'>&nbsp;$opt_val&nbsp;%style_end%";
	   }
	   break;	  
	}
	
	// apply style
	switch($style){
		case "ul":
		case "ol":
			$checkbox=str_replace(array("%style_begin%","%style_end%"),array("<li><div>","</div></li>"),$checkbox);
			$checkbox="<{$style} class='list-table'>{$checkbox}</{$style}>";
		break;
		case "div":	
			$checkbox=str_replace(array("%style_begin%","%style_end%"),array("<div>","</div>"),$checkbox);
			$checkbox="<{$style} class='list-table'>{$checkbox}</{$style}>";
		break;
		case "table":
			$checkbox=str_replace(array("%style_begin%","%style_end%"),array("<td>","</td>"),$checkbox);
			$checkbox="<{$style}><tr>{$checkbox}</tr></{$style}>";
		break;
	}
	// return
	return $checkbox;
}
// select list of time 
function mgm_time_select($name="time",$properties=""){  
  $default=array("hour"=>date('g'),"minute"=>date('i'),"second"=>date('s'),
                 "meridian"=>date('A'),"readonly"=>false);  
  $properties =is_array($properties)? $properties : $default;
  # make vars
  extract($properties);
  
  $readonly =($readonly)?"disabled class='readonly'":"";

  echo "<select name='hour[$name]' {$readonly}>";
  echo mgm_make_combo_options(array_merge(array("00"),range(1,12)),$hour,MGM_VALUE_ONLY);
  echo "</select>";
  
  echo "<select name='minute[$name]' {$readonly}>";
  echo mgm_make_combo_options(array_merge(array("00"),range(1,60)),$minute,MGM_VALUE_ONLY);
  echo "</select>";
  
  /*
  echo "<select name='second' {$readonly_class}>";
  echo mgm_make_combo_options(range(1,60),$second,MGM_VALUE_ONLY);
  echo "</select>"; 
  */
  
  echo "<select name='meridian[$name]' {$readonly}>";
  echo mgm_make_combo_options(array('AM','PM'),$meridian,MGM_VALUE_ONLY);
  echo "</select>"; 
}
# check box selected
function mgm_check_if_match($self_value,$set_value,$default_value=""){
	if(is_string($set_value) && empty($set_value) && ($self_value==$default_value)){
	  	echo "checked";
	}else if(is_array($set_value) && in_array($self_value, $set_value)){
		echo "checked";		
	}else if($self_value==$set_value){
	  	echo "checked";
	}
}
# selct selected
function mgm_select_if_match($self_value,$set_value,$default_value=""){
	if(is_string($set_value) && empty($set_value) && ($self_value==$default_value)){
		echo "selected";
	}else if(is_array($set_value) && in_array($self_value, $set_value)){
		echo "checked";	  
	}else if($self_value==$set_value){
		echo "selected";
	}
}
?>