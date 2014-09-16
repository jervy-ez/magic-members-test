<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members components parent class
 * base class for modules, plugins, widgets etc
 *
 * @package MagicMembers
 * @since 2.5
 */
class mgm_component extends mgm_object{
	// loader
	public $load    = NULL; 
	public $process = NULL;
	public $type    = 'component';
	
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_component();
	}
	
	// php4 construct
	function mgm_component(){
		// parent
		parent::__construct();		
		// loader
		$this->load = & new mgm_loader();		
		// processor
		$this->process = & new mgm_processor();
	}	
	
	// init
	function init(){	
		// set instance
		$this->process->set_instance($this);		
		// call
		$this->process->call();
	}		
}
// end of file core/libs/core/mgm_component.php
