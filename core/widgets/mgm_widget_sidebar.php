<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Sidebar Widgets
 *
 * @package MagicMembers
 * @since 2.0 
 */
global $mgm_sidebar_widget;
$mgm_sidebar_widget = mgm_get_class('sidebar_widget');

// login widget ---------------------------------------------------------------------
include('sidebar/widget_login.php');// @todo, create class architechture for wp 3
// end login widget -----------------------------------------------------------------

// register widget ------------------------------------------------------------------
include('sidebar/widget_register.php');// @todo, create class architechture for wp 3
// end register widget --------------------------------------------------------------

// status widget --------------------------------------------------------------------
include('sidebar/widget_status.php');// @todo, create class architechture for wp 3
// end status widget ----------------------------------------------------------------

// text widget-----------------------------------------------------------------------
include('sidebar/widget_text.php');// @todo, create class architechture for wp 3
// end text widget ------------------------------------------------------------------


/**
 * register widgets
 */
function mgm_widgets_init(){
	// register widgets
	// login widget -------------------------------------------------------------------
	// @include('sidebar/widget_login2.php');// @todo, create class architechture for wp 3
	// end login widget ---------------------------------------------------------------
	// register
	if( class_exists('MGM_Login_Widget2') ){
		register_widget( 'MGM_Login_Widget2' );// pending
	}

	// register widget ----------------------------------------------------------------
	// @include('sidebar/widget_register2.php');// @todo, create class architechture for wp 3
	// end register widget ------------------------------------------------------------
	// register
	if( class_exists('MGM_Register_Widget2') ){
		register_widget( 'MGM_Register_Widget2' );// pepnding
	}

	// status widget ------------------------------------------------------------------
	@include('sidebar/widget_status2.php');// @todo, create class architechture for wp 3
	// end status widget --------------------------------------------------------------
	// register
	if( class_exists('MGM_Status_Widget2') ){
		register_widget( 'MGM_Status_Widget2' );// working...
	}
	
	// text widget2--------------------------------------------------------------------
	@include('sidebar/widget_text2.php');// @todo, test class architechture for wp 3
	// register
	if( class_exists('MGM_Text_Widget2') ){
		register_widget( 'MGM_Text_Widget2' );// done
	}	
	// end text widget ----------------------------------------------------------------
}
// ad on init
add_action('init', 'mgm_widgets_init', 1);
// end file core/widgets/mgm_widget_sidebar.php