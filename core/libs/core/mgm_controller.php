<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members admin modules parent class
 * base class for admin modules
 *
 * @package MagicMembers
 * @since 2.5
 */
class mgm_controller extends mgm_object{
	// loader
	public $load;
	public $process;
	
	// construct
	function __construct(){		
		// php4 construct
		$this->mgm_controller();
	}
	
	// php4 construct
	function mgm_controller(){
		// parent
		parent::__construct();	
		// no saving
		$this->saving = false;	
		// loader
		$this->load = & new mgm_loader();		
		// processor
		$this->process = & new mgm_processor();
	}	
	
	// init
	function init($method=null){	
		// set instance
		$this->process->set_instance($this);	
		// call
		$this->process->call($method);
	}	
	
	// clear cache
	function _clear_cache(){
		// reset
		if(isset($_REQUEST['do_action']) && $_REQUEST['do_action'] == 'reload'){
			// delete transient
			delete_transient( $this->transient );
		}
	}
	
	// get query params
	function _get_query_params($sort=array()){
		// params
		$params = array('list'=>array(),'search'=>array(),'sort'=>array());
		// search
		if(isset($_POST['do_action']) && $_POST['do_action']=='list'){
			// list
			$params['list']   = $_POST['list'];
			// search
			$params['search'] = $_POST['search'];
			// sort
			$params['sort']   = $_POST['sort'];		
			// delete transient
			delete_transient( $this->transient );
			// cache
			set_transient($this->transient, $params, 60*60*1);// 1 hr in cache	
			// return
			return $params;
		}else{
			// check cached
			if ( $params = get_transient( $this->transient ) ) {
				// return cache
				return $params;
			}else{
				// set default
				// list
				$params['list']   = array('page_number'=>'1','page_limit'=>20); 
				// search
				$params['search'] = array('field'=>'','value'=>'');
				// sort
				$params['sort']   = !empty($sort) ? $sort : array('field'=>'create_dt','type'=>'DESC');
				// return
				return $params;
			}
		}		
		// return 
		return $params;
	}
}
// end of file core/libs/core/mgm_controller.php
