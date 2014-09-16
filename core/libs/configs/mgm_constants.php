<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
// Constants
// control constants
define('MGM_BUILD'                  , '2.8.0'); // development build, let this controlled by priyabrata, branch.sub-branch/svn release
define('MGM_STAGE'                  , 'dev');// rc-#/stable for distribution, control flags for development
// notify constants
define('MGM_DEVELOPER_EMAIL'        , 'developer@magicmembers.com');// optional
define('MGM_REPORTBUG_EMAIL'        , 'reportbug@magicmembers.com');// optional

// service constants 
// moved to classes/mgm_auth.php  for security
// define('MGM_SERVICE_DOMAIN', 'http://localhost/magicmediagroup/'); only open when testing

// information constants
define('MGM_GET_FORUM_URL'          , 'http://www.magicmembers.com/support/rss/topics');
define('MGM_GET_NEWS_URL'           , 'http://www.magicmembers.com/?cat=19&feed=rss2');
define('MGM_GET_BLOG_URL'           , 'http://www.magicmembers.com/?cat=3&feed=rss2');

// affilte id
define('MGM_AFFILIATE_ID'           , '1');// default 

// system constants
// accesses , @ToDo we should fix lang issue here 
define('MGM_ACCESS_PRIVATE'         , __('Private', 'mgm'));
define('MGM_ACCESS_PUBLIC'          , __('Public', 'mgm'));

// statuses, @ToDo we should fix lang issue here 
define('MGM_STATUS_NULL'            , __('Inactive','mgm'));
define('MGM_STATUS_ACTIVE'          , __('Active','mgm'));
define('MGM_STATUS_EXPIRED'         , __('Expired','mgm'));
define('MGM_STATUS_PENDING'         , __('Pending','mgm'));
define('MGM_STATUS_TRIAL_EXPIRED'   , __('Trial Expired','mgm'));
define('MGM_STATUS_CANCELLED'       , __('Cancelled','mgm'));
define('MGM_STATUS_ERROR'           , __('Error','mgm'));
define('MGM_STATUS_AWAITING_CANCEL' , __('Awaiting Cancelled','mgm'));

// date formats
define('MGM_DATE_FORMAT'            , 'M jS Y'); // default
define('MGM_DATE_FORMAT_TIME'       , 'M jS Y g:i A'); // default
define('MGM_DATE_FORMAT_LONG'       , 'F j, Y');
define('MGM_DATE_FORMAT_LONG_TIME'  , 'F j, Y g:i A');
define('MGM_DATE_FORMAT_LONG_TIME2' , 'l, F j, Y g:i A');//// Monday, May 26, 2012 
define('MGM_DATE_FORMAT_SHORT'      , 'm/d/Y');
define('MGM_DATE_FORMAT_INPUT'      , 'm/d/Y');

// comobo options
define('MGM_VALUE_ONLY'             , 1);
define('MGM_KEY_VALUE'              , 2);

// api uri
define('MGM_API_URI_PATH'          , 'mgmapi');
define('MGM_API_URI_PREFIX'        , SITECOOKIEPATH . MGM_API_URI_PATH);
define('MGM_API_ALLOW_HOST'        , 'all');// all, none or named
define('MGM_API_KEY_VAR'           , 'X-MGMAPI-KEY');
define('MGM_API_CLASS_PREFIX'      , 'mgm_api_');
define('MGM_API_ALLOW_IP'		   , 'all');

// editor plugin
define('MGM_EDITOR_PLUGIN'         , 'On');// On|Off, enable/disable editor plugin

// debug log
define('MGM_DEBUG_LOG'             , ((MGM_STAGE=='dev') ? TRUE : FALSE));

// upgrader api availability
define('MGM_AUTO_UPGRADER'         , ((MGM_STAGE=='dev') ? TRUE : FALSE));// false will disable api, deprecated, using real time check using MGMS

// end of file