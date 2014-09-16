<?php
//fetch mgm_roles array from db:
//initiall it was using to store mgm created roles
$mgm_roles = get_option( 'mgm_roles' );
if(is_array($mgm_roles)) {
	//replace mgm roles with mgm_created_roles
	update_option('mgm_created_roles', $mgm_roles);
	//save mgm_roles class object
	mgm_get_option('roles', true);
}
