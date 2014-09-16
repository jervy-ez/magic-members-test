<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
// routers, mainly used in api actions
global $mgm_routes;
// members -----------------------------------------------------------------------------------------
// member posts
$mgm_routes['members/(\d+)/posts']                            = 'members/posts/$1';
// member export by id
$mgm_routes['members/(\d+)/export']                           = 'members/export/$1';
// member import by id
$mgm_routes['members/(\d+)/import']                           = 'members/import/$1';
// member by id
$mgm_routes['members/(\d+)']                                  = 'members/index/$1';

// membership types --------------------------------------------------------------------------------
// membership type create/update/delete
$mgm_routes['membership_types/(create|update|delete)']        = 'membership_types/$1';
// posts/taxonomies by membership type code
$mgm_routes['membership_types/(.*)/(posts|taxonomies)/(.*)']  = 'membership_types/$2/$1/$3';
// members by membership type code
$mgm_routes['membership_types/(.*)/members']                  = 'membership_types/members/$1';	
$mgm_routes['membership_types/members']                       = 'membership_types/members';													   
// membership types by code
$mgm_routes['membership_types/(.*)']                          = 'membership_types/index/$1';

// subscription packages----------------------------------------------------------------------------
// subscription package create/update/delete
$mgm_routes['subscription_packages/(create|update|delete)']   = 'subscription_packages/$1';
// members by subscription package id
$mgm_routes['subscription_packages/(\d+)/members']            = 'subscription_packages/members/$1';														   
// subscription package by id
$mgm_routes['subscription_packages/(\d+)']                    = 'subscription_packages/index/$1';

// downloads----------------------------------------------------------------------------------------
// downloads create/update/delete
$mgm_routes['downloads/(create|update|delete)']               = 'downloads/$1';
// downloads by id
$mgm_routes['downloads/(\d+)']                                = 'downloads/index/$1';

// coupons----------------------------------------------------------------------------------------
// coupons create/update/delete
$mgm_routes['coupons/(create|update|delete)']                 = 'coupons/$1';
// coupons by id
$mgm_routes['coupons/(\d+)']                                  = 'coupons/index/$1';

// end file

