<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members pager utility class
 *
 * @package MagicMembers
 * @since 2.5
 */
class mgm_pager{
	// vars
	var $page_no  = 'page_no';	
	var $page_url = 'admin-ajax.php?action=mgm_admin_ajax_action&page=';
	
	// constructor
	function __construct($options=array()) {
		// php4
		$this->mgm_pager($options);
	}
	
	// php4 construct
	function mgm_pager($options=array()){
		// init stuff
		if(is_array($options)){
			// loop
			foreach($options as $key=>$val){
				$this->{$key} = $val;
			}
		}
	}
	
	// get query limit
	function get_query_limit($page_limit=50){
		global  $wpdb;
		// set
		$this->page_limit   = $page_limit;
		$this->page_current = isset($_REQUEST[$this->page_no]) ? $_REQUEST[$this->page_no] : 1;
		// offset
		$this->page_offset  = ($this->page_current - 1) * $this->page_limit;
		// send query limit
		return $wpdb->prepare(" LIMIT %d, %d", (int)$this->page_offset, (int)$this->page_limit);
	}
	
	// pager_links
	function get_pager_links($page_url='',$args=array()) {
		global $wpdb;
		
		// page url
		if($page_url) $this->page_url = $page_url;		
		// total		
		$this->row_count = $wpdb->get_var("SELECT FOUND_ROWS() AS row_count");
		// page count
		$this->page_count = ceil($this->row_count / $this->page_limit);
		// text
		$paging_text = '';
		// when greater
		if ( $this->row_count > $this->page_limit ) { // have to page the results
			// text
			$paging_text = paginate_links( array(
				'total'    => $this->page_count,
				'current'  => $this->page_current,
				'base'     => $this->page_url . ((!preg_match('/\?/', $this->page_url)) ? '?' : '&') . '%_%',
				'format'   => $this->page_no.'=%#%',
				'add_args' => $args
			) );
			
			// format
			if ( $paging_text ) {
				$paging_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %d-%d of %d records ', 'mgm' ) . '</span> %s',
							   number_format_i18n( ( $this->page_current - 1 ) * $this->page_limit + 1 ), 
							   number_format_i18n( min( $this->page_current * $this->page_limit, $this->row_count ) ),
							   number_format_i18n( $this->row_count ), 							   
							   $paging_text);
			}
		}	
		// return
		return str_replace('page-numbers', '', $paging_text);
	}
	
	// page count
	function get_page_count(){
		// return 
		return $this->page_count;
	}
	
	// row count
	function get_row_count(){
		// return 
		return $this->row_count;
	}
}
// core/libs/utilities/mgm_pager.php