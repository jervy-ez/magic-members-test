<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
   /**
    * Table Names
    */	
	global $wpdb;
	// prefix
	define('MGM_TABLE_PREFIX'                , 'mgm_');	
 	// tables	
	define('TBL_MGM_COUNTRY'                 , $wpdb->prefix . MGM_TABLE_PREFIX . 'countries');
	define('TBL_MGM_COUPON'                  , $wpdb->prefix . MGM_TABLE_PREFIX . 'coupons');
	
	define('TBL_MGM_ADDON'                   , $wpdb->prefix . MGM_TABLE_PREFIX . 'addons');
	define('TBL_MGM_ADDON_OPTION'            , $wpdb->prefix . MGM_TABLE_PREFIX . 'addon_options');
	define('TBL_MGM_ADDON_PURCHASES'         , $wpdb->prefix . MGM_TABLE_PREFIX . 'addon_purchases');
	
	define('TBL_MGM_DOWNLOAD'                , $wpdb->prefix . MGM_TABLE_PREFIX . 'downloads');
	define('TBL_MGM_DOWNLOAD_ATTRIBUTE'      , $wpdb->prefix . MGM_TABLE_PREFIX . 'download_attributes');// @deprecated
	define('TBL_MGM_DOWNLOAD_ATTRIBUTE_TYPE' , $wpdb->prefix . MGM_TABLE_PREFIX . 'download_attribute_types');// @deprecated
	define('TBL_MGM_DOWNLOAD_POST_ASSOC'     , $wpdb->prefix . MGM_TABLE_PREFIX . 'download_post_assoc');
	define('TBL_MGM_DOWNLOAD_LIMIT_ASSOC'    , $wpdb->prefix . MGM_TABLE_PREFIX . 'download_limit_assoc');
	
	define('TBL_MGM_POST_PURCHASES'          , $wpdb->prefix . MGM_TABLE_PREFIX . 'post_purchases');//@old posts_purchased
	define('TBL_MGM_POST_PACK'               , $wpdb->prefix . MGM_TABLE_PREFIX . 'post_packs');
	define('TBL_MGM_POST_PACK_POST_ASSOC'    , $wpdb->prefix . MGM_TABLE_PREFIX . 'post_pack_post_assoc');
	define('TBL_MGM_POST_PROTECTED_URL'      , $wpdb->prefix . MGM_TABLE_PREFIX . 'post_protected_urls');	
	
	define('TBL_MGM_REST_API_KEY'            , $wpdb->prefix . MGM_TABLE_PREFIX . 'rest_api_keys');	
	define('TBL_MGM_REST_API_LEVEL'          , $wpdb->prefix . MGM_TABLE_PREFIX . 'rest_api_levels');	
	define('TBL_MGM_REST_API_LOG'            , $wpdb->prefix . MGM_TABLE_PREFIX . 'rest_api_logs');	
	
	define('TBL_MGM_TEMPLATE'                , $wpdb->prefix . MGM_TABLE_PREFIX . 'templates');	
	define('TBL_MGM_TRANSACTION'             , $wpdb->prefix . MGM_TABLE_PREFIX . 'transactions');
	define('TBL_MGM_TRANSACTION_OPTION'      , $wpdb->prefix . MGM_TABLE_PREFIX . 'transaction_options');
	
	// keep login records
	define('TBL_MGM_MULTIPLE_LOGIN_RECORDS'  , $wpdb->prefix . MGM_TABLE_PREFIX . 'multiple_login_records');

	// Epoch gateway - DataPlus tables , renamed on 23/12/2011 to match mgm conventions
	// Keep the Epoch Table names as it is - required for Epoch servers	
	define('TBL_MGM_EPOCH_TRANS_STATUS'      , 'EpochTransStats');
	define('TBL_MGM_EPOCH_CANCEL_STATUS'     , 'MemberCancelStats');
// end of file