<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members database functions
 *
 * @package MagicMembers
 * @since 2.5
 */
// enum to array
function mgm_enum_field_values($table,$column,$excludes=''){
	global $wpdb; 		
	// exclude
	$to_exclude   = explode(',', $excludes);
	$to_exclude[] = 'enum';
	$to_exclude[] = 'set';
	// init
	$fields = array();  
	$enum   = $wpdb->get_row("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");  
	$tok    = strtok($enum->Type, ")(',");
	while($tok !== false) {      
		// set
		if(!in_array($tok,$to_exclude)){
			$fields[] = $tok;
		}
		// tokonize 
		$tok = strtok(")(',");
	}
	// return
	return $fields;
}
// array of fields 
function mgm_field_values($table, $key, $value, $where='', $orderby='', $join=''){
	//increased memory limit
	@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', '2048M' ) );
	@set_time_limit(0);
	global $wpdb;	
	// order by
	$orderby = (!empty($orderby)) ? $orderby : "`{$value}` ASC"; 
	// sql
	$sql     = "SELECT {$key},{$value} FROM `{$table}` {$join} WHERE 1 {$where} ORDER BY {$orderby}";	
	$rows    = $wpdb->get_results($sql);
	// split alias
	$key     = mgm_alias_split($key);
	$value   = mgm_alias_split($value);
	// store
	$_array  = array();
	// captured
	if($rows){		
		// loop
		foreach($rows as $row){
			// set
			$_array[$row->$key] = $row->$value;
		}
	} 
	// return
	return $_array;
}

/**
 * check duplicate
 * 
 * @param string tablename
 * @param array fields
 * @param string sql clause
 * @param array source
 */
function mgm_is_duplicate($table, $fields, $extra_clause='', $source=''){			
	global $wpdb; 	
	
	// reset source
	$source = is_array($source) ? $source : $_REQUEST;
	// init 
	$fld_clauses = array();
	// loop
	foreach($fields as $fld){  
		// set    
		$fld_clauses[] = " `{$fld}` = '{$source[$fld]}' ";
	}		
	// join		
	$fld_clause = implode(' AND ', $fld_clauses);	
	// extra
	if(!empty($extra_clause)){
		// check
		if(!preg_match('/^AND/i',$extra_clause)){
			// extra_clause
			$extra_clause = 'AND ' . $extra_clause;
		}
	}
	// get var
	$count = $wpdb->get_var("SELECT COUNT(*) AS _CNT FROM `{$table}` WHERE {$fld_clause} {$extra_clause} ");	
	// return 
	return ($count>0) ? true : false;	
} 
// single quote wrap helper
function mgm_single_quote($field_data){
	// return
	return "'{$field_data}'";
}
// map to quotes
function mgm_map_for_in($data){
	// return
	return implode(',', array_map('mgm_single_quote', $data)); 
}
// field alias split helper
function mgm_alias_split($alias){
	// CONCAT(first_name,last_name) AS name => returns name
	if(preg_match("/(\s+)AS(\s+)/i",$alias)){
		list($discard,$alias) = preg_split("/(\s+)AS(\s+)/i",$alias);
	}
	
	// A.id => return id
	if(preg_match("/[a-zA-Z]\.(.*)/",$alias)){		
		list($discard,$alias) = explode('.',$alias);		
	}
	
	// return
	return $alias;
}

//purchased successful transactions - drip feed calculations -issue#: 262
function mgm_purchased_transactions($membership_type){
	global $wpdb;	

	$con ='"'.$membership_type.'"';
	// sql
	$sql  = " SELECT `data` , `transaction_dt` FROM `" . TBL_MGM_TRANSACTION . "`";
	$sql .= " WHERE `payment_type` = 'subscription_purchase'";
	$sql .= " AND `status_text` = 'Last payment was successful' ";
	$sql .= " AND data LIKE '%".$con."%'";
	// row
	$rows    = $wpdb->get_results($sql);
	// return
	return $rows;
}


// end of file