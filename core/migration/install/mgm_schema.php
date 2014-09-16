<?php 
 /** 
  * Schema Create
  */ 	
 
 global $wpdb;
 
 // charset and collate
 $charset_collate = mgm_get_charset_collate();
	 
 // countries : mgm_countries
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_COUNTRY . "` (
		`id` int(11) UNSIGNED AUTO_INCREMENT,
		`name` VARCHAR(100) NOT NULL,
		`code` VARCHAR(3) NOT NULL,
		PRIMARY KEY (`id`)
	    ) {$charset_collate} COMMENT = 'countries'";
 $wpdb->query($sql); 
 
 // coupons : mgm_coupons 
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_COUPON . "` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` varchar(150) NOT NULL,
		`value` varchar(150) NOT NULL,
		`description` varchar(255) NULL,
		`use_limit` int(11) UNSIGNED NULL,
		`used_count` INT( 11 ) UNSIGNED NULL,
		`product` TEXT NULL,
		`expire_dt` datetime NULL,
		`create_dt` datetime NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `name` (`name`)
	    ) {$charset_collate} COMMENT = 'coupons'";
 $wpdb->query($sql);
 
 // addons : mgm_addons 
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_ADDON . "` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` varchar(150) NOT NULL,		
		`description` varchar(255) NULL,		
		`expire_dt` datetime NULL,
		`create_dt` datetime NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `name` (`name`)
	    ) {$charset_collate} COMMENT = 'addons'";
 $wpdb->query($sql);
 
  // addon options : mgm_addon_options
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_ADDON_OPTION . "` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`addon_id` int(11) UNSIGNED NOT NULL,	
		`option` varchar(150) NOT NULL,				
		`price` decimal(10,2) UNSIGNED NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `addon_option` (`addon_id`,`option`)
	    ) {$charset_collate} COMMENT = 'addon options'";
 $wpdb->query($sql);
 
 // addon purchases : mgm_addon_purchases
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_ADDON_PURCHASES . "` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) unsigned NULL,
		`addon_option_id` int(11) unsigned NOT NULL,		
		`purchase_dt` datetime NULL,
		`transaction_id` bigint(20) unsigned NULL,
		PRIMARY KEY (`id`)
		) {$charset_collate} COMMENT = 'addon purchases log'";
 $wpdb->query($sql); 
 
 // downloads : mgm_downloads
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_DOWNLOAD . "` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`title` VARCHAR (150) NOT NULL,
		`filename` VARCHAR (255) NOT NULL ,
		`real_filename` VARCHAR (255) NULL ,
		`filesize` VARCHAR (10) NULL ,
		`post_date` DATETIME NOT NULL,
		`members_only` enum('N','Y') NOT NULL,
		`user_id` bigint(20) unsigned NOT NULL,
		`download_limit` INT( 11 ) UNSIGNED NULL,
		`code` VARCHAR (50) NOT NULL,
		`restrict_acces_ip` enum('N','Y') NOT NULL,
		`is_s3_torrent` enum('N','Y') NOT NULL,
		`expire_dt` datetime NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `title` (`title`)
	    ) {$charset_collate} COMMENT = 'downloads'";
 $wpdb->query($sql); 
 
 // download limit : mgm_download_limit_assoc
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_DOWNLOAD_LIMIT_ASSOC . "` (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`download_id` int(11) UNSIGNED NOT NULL,
		`user_id` bigint(20) unsigned NULL,
		`ip_address` VARCHAR( 60 ) NULL DEFAULT NULL,
		`count` INT NOT NULL,
		PRIMARY KEY (`id`)
		) {$charset_collate} COMMENT = 'download limit associations'";
 $wpdb->query($sql);
 
 // download attributes : mgm_download_attributes @deprecated
 /*$sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_DOWNLOAD_ATTRIBUTE . "` (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`download_id` int(11) UNSIGNED NOT NULL,
		`attribute_id` int(11) UNSIGNED NOT NULL,
		`value` varchar(255) default NULL,
		PRIMARY KEY (`id`)
		) {$charset_collate} COMMENT = 'download attributes'";
 $wpdb->query($sql);*/
 
 // download attribute types : mgm_download_attribute_types  @deprecated
 /*$sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_DOWNLOAD_ATTRIBUTE_TYPE . "` (
		`id` int(11) UNSIGNED NOT NULL auto_increment,
		`field_type_id` int(11) UNSIGNED NOT NULL,
		`name` varchar(150) NOT NULL,
		`description` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
		) {$charset_collate} COMMENT = 'download attribute types'";
 $wpdb->query($sql);*/
 
 	
 // download post assoc : mgm_download_post_assoc
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_DOWNLOAD_POST_ASSOC . "` (
		`download_id` int(11) UNSIGNED NOT NULL,
		`post_id` bigint(20) UNSIGNED NOT NULL,
		PRIMARY KEY (`download_id`,`post_id`)
		) {$charset_collate} COMMENT = 'download post association'";
 $wpdb->query($sql); 
 
 // posts purchased : mgm_posts_purchased
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_POST_PURCHASES . "` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) unsigned NULL,
		`post_id` bigint(20) unsigned NOT NULL,
		`is_gift` enum('N','Y') NOT NULL,
		`purchase_dt` datetime NULL,
		`is_expire` enum('Y','N') NOT NULL,
		`guest_token` varchar(10) NULL,
		`guest_coupon` varchar(20) NULL,
		`view_count` int(11) unsigned NULL,
		`transaction_id` bigint(20) unsigned NULL,
		PRIMARY KEY (`id`)
		) {$charset_collate} COMMENT = 'post purchase/gift log'";
 $wpdb->query($sql); 	
	
 // post pack : mgm_post_pack
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_POST_PACK . "` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` varchar(150) NOT NULL,
		`cost` DECIMAL( 10, 2 ) UNSIGNED NOT NULL,
		`description` varchar(255),
		`product` TEXT NULL,
		`modules` TEXT NULL,
		`create_dt` datetime NULL,
		PRIMARY KEY (`id`)
		) {$charset_collate} COMMENT = 'post packs'";
 $wpdb->query($sql);
	
 // post pack post assoc : mgm_post_pack_post_assoc
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_POST_PACK_POST_ASSOC . "` (
		`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`pack_id` int(11) UNSIGNED NOT NULL,
		`post_id` bigint(20) UNSIGNED NOT NULL,
		`create_dt` datetime NULL,
		PRIMARY KEY (`id`)
		) {$charset_collate} COMMENT = 'post pack associations'";
 $wpdb->query($sql);	 
 
 // templates : mgm_templates
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_TEMPLATE . "` (
		`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`name` VARCHAR( 250 ) NOT NULL ,
		`type` ENUM( 'emails', 'messages', 'templates' ) NOT NULL ,
		`content` TEXT NOT NULL ,
		`create_dt` DATETIME NOT NULL 
		) {$charset_collate} COMMENT = 'template contents'";
 $wpdb->query($sql); 
 
 // transaction logs : mgm_transactions
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_TRANSACTION . "` (
		`id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`user_id` bigint(20) unsigned NULL,
		`payment_type` ENUM( 'post_purchase', 'subscription_purchase' ) NOT NULL ,
		`module` varchar(150) NULL,
		`data` TEXT NULL ,
		`status` varchar(100) NULL,
		`status_text` varchar(255) NULL,
		`transaction_dt` DATETIME NOT NULL
		) {$charset_collate} COMMENT = 'transaction data log'";
 $wpdb->query($sql);
 
 // transaction options, custom fields : mgm_transaction_options
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_TRANSACTION_OPTION . "` (
		`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`transaction_id` bigint(20) UNSIGNED NOT NULL,			
		`option_name` VARCHAR( 255 ) NOT NULL ,
		`option_value` TEXT NOT NULL,
		 UNIQUE KEY `transaction_id` (`transaction_id`,`option_name`)
		) {$charset_collate} COMMENT = 'transaction options'";
 $wpdb->query($sql);	

 // protected urls : mgm_post_protected_urls
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_POST_PROTECTED_URL . "` (
		`id` BIGINT(20) UNSIGNED AUTO_INCREMENT,
		`url` VARCHAR(255) NOT NULL,
		`post_id` BIGINT(20) UNSIGNED NULL,
		`membership_types` TEXT NULL,		
		PRIMARY KEY (`id`)
		) {$charset_collate} COMMENT = 'protected post url'";
 $wpdb->query($sql);  
 
 // rest api keys : mgm_rest_api_keys
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_REST_API_KEY . "` (
		`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		`api_key` varchar(40) NOT NULL,
		`level` smallint(5) UNSIGNED NOT NULL,		
		`create_dt` DATETIME NOT NULL,
		PRIMARY KEY (`id`)
		) {$charset_collate} COMMENT = 'rest api keys'";
 $wpdb->query($sql);
 
 // rest api levels : mgm_rest_api_levels
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_REST_API_LEVEL . "` (
		`id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
		`level` smallint(5) UNSIGNED NOT NULL,
		`name` varchar(100) NOT NULL,
		`permissions` text NOT NULL,
		`limits` int(11) UNSIGNED NULL,
		PRIMARY KEY (`id`)
		) {$charset_collate} COMMENT = 'rest api access levels'";
 $wpdb->query($sql);
 
 // rest api logs : mgm_rest_api_logs
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_REST_API_LOG . "` (
		`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		`api_key` varchar(40) NOT NULL,
		`uri` varchar(255) NOT NULL,
		`method` varchar(6) NOT NULL,
		`params` text NOT NULL,		
		`ip_address` varchar(15) NOT NULL,		
		`is_authorized` enum('Y','N') NOT NULL,
		`create_dt` DATETIME NOT NULL,
		PRIMARY KEY (`id`)
		) {$charset_collate} COMMENT = 'rest api logs'";
 $wpdb->query($sql);
 
 // login logs : multiple_login_records
 $sql = "CREATE TABLE IF NOT EXISTS `" . TBL_MGM_MULTIPLE_LOGIN_RECORDS . "` (
	  	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	  	`user_id` bigint(20) DEFAULT NULL,
	  	`pack_id` int(11) DEFAULT NULL,
	  	`ip_address` varchar(30) NOT NULL,
	  	`login_at` datetime,
	  	`logout_at` datetime NULL DEFAULT NULL,	  	
	  	PRIMARY KEY (`id`)
	) {$charset_collate} COMMENT = 'multiple login records'";
 $wpdb->query($sql);


 // ---------------------------------------------- country data ---------------------------------------------------------		
 // insert countries
 $sql = "INSERT IGNORE INTO `" . TBL_MGM_COUNTRY. "` (`id`, `name`, `code`) VALUES 
		(1, 'Asia/Pacific Region', 'AP'), (2, 'Europe', 'EU'), (3, 'Andorra', 'AD'), 
		(4, 'United Arab Emirates', 'AE'), (5, 'Afghanistan', 'AF'), (6, 'Antigua and Barbuda', 'AG'), 
		(7, 'Anguilla', 'AI'), (8, 'Albania', 'AL'), (9, 'Armenia', 'AM'), (10, 'Netherlands Antilles', 'AN'), (11, 'Angola', 'AO'),
		(12, 'Antarctica', 'AQ'), (13, 'Argentina', 'AR'), (14, 'American Samoa', 'AS'), (15, 'Austria', 'AT'), 
		(16, 'Australia', 'AU'), (17, 'Aruba', 'AW'), (18, 'Azerbaijan', 'AZ'), (19, 'Bosnia and Herzegovina', 'BA'), 
		(20, 'Barbados', 'BB'), (21, 'Bangladesh', 'BD'), (22, 'Belgium', 'BE'), (23, 'Burkina Faso', 'BF'), 
		(24, 'Bulgaria', 'BG'), (25, 'Bahrain', 'BH'), (26, 'Burundi', 'BI'), (27, 'Benin', 'BJ'), (28, 'Bermuda', 'BM'), 
		(29, 'Brunei Darussalam', 'BN'), (30, 'Bolivia', 'BO'), (31, 'Brazil', 'BR'), (32, 'Bahamas', 'BS'), (33, 'Bhutan', 'BT'), 
		(34, 'Bouvet Island', 'BV'), (35, 'Botswana', 'BW'), (36, 'Belarus', 'BY'), (37, 'Belize', 'BZ'), (38, 'Canada', 'CA'), 
		(39, 'Cocos (Keeling) Islands', 'CC'), (40, 'Congo, The Democratic Republic of the', 'CD'), (41, 'Central African Republic', 'CF'), 
		(42, 'Congo', 'CG'), (43, 'Switzerland', 'CH'), (44, 'Cote D\'Ivoire', 'CI'), (45, 'Cook Islands', 'CK'), 
		(46, 'Chile', 'CL'), (47, 'Cameroon', 'CM'), (48, 'China', 'CN'), (49, 'Colombia', 'CO'), (50, 'Costa Rica', 'CR'), 
		(51, 'Cuba', 'CU'), (52, 'Cape Verde', 'CV'), (53, 'Christmas Island', 'CX'), (54, 'Cyprus', 'CY'), 
		(55, 'Czech Republic', 'CZ'), (56, 'Germany', 'DE'), (57, 'Djibouti', 'DJ'), (58, 'Denmark', 'DK'), 
		(59, 'Dominica', 'DM'), (60, 'Dominican Republic', 'DO'), (61, 'Algeria', 'DZ'), (62, 'Ecuador', 'EC'), 
		(63, 'Estonia', 'EE'), (64, 'Egypt', 'EG'), (65, 'Western Sahara', 'EH'), (66, 'Eritrea', 'ER'), (67, 'Spain', 'ES'), 
		(68, 'Ethiopia', 'ET'), (69, 'Finland', 'FI'), (70, 'Fiji', 'FJ'), (71, 'Falkland Islands (Malvinas)', 'FK'), 
		(72, 'Micronesia, Federated States of', 'FM'), (73, 'Faroe Islands', 'FO'), (74, 'France', 'FR'), 
		(75, 'France, Metropolitan', 'FX'), (76, 'Gabon', 'GA'), (77, 'United Kingdom', 'GB'), (78, 'Grenada', 'GD'), 
		(79, 'Georgia', 'GE'), (80, 'French Guiana', 'GF'), (81, 'Ghana', 'GH'), (82, 'Gibraltar', 'GI'), (83, 'Greenland', 'GL'), 
		(84, 'Gambia', 'GM'), (85, 'Guinea', 'GN'), (86, 'Guadeloupe', 'GP'), (87, 'Equatorial Guinea', 'GQ'), (88, 'Greece', 'GR'), 
		(89, 'South Georgia and the South Sandwich Islands', 'GS'), (90, 'Guatemala', 'GT'), (91, 'Guam', 'GU'), (92, 'Guinea-Bissau', 'GW'), 
		(93, 'Guyana', 'GY'), (94, 'Hong Kong', 'HK'), (95, 'Heard Island and McDonald Islands', 'HM'), (96, 'Honduras', 'HN'), (97, 'Croatia', 'HR'), 
		(98, 'Haiti', 'HT'), (99, 'Hungary', 'HU'), (100, 'Indonesia', 'ID'), (101, 'Ireland', 'IE'), (102, 'Israel', 'IL'), (103, 'India', 'IN'), 
		(104, 'British Indian Ocean Territory', 'IO'), (105, 'Iraq', 'IQ'), (106, 'Iran, Islamic Republic of', 'IR'), (107, 'Iceland', 'IS'), (108, 'Italy', 'IT'),
		(109, 'Jamaica', 'JM'), (110, 'Jordan', 'JO'), (111, 'Japan', 'JP'), (112, 'Kenya', 'KE'), (113, 'Kyrgyzstan', 'KG'), (114, 'Cambodia', 'KH'),
		(115, 'Kiribati', 'KI'), (116, 'Comoros', 'KM'), (117, 'Saint Kitts and Nevis', 'KN'), (118, 'Korea, Democratic People\'s Republic of', 'KP'), 
		(119, 'Korea, Republic of', 'KR'), (120, 'Kuwait', 'KW'), (121, 'Cayman Islands', 'KY'), (122, 'Kazakstan', 'KZ'),
		(123, 'Lao People\'s Democratic Republic', 'LA'), (124, 'Lebanon', 'LB'), (125, 'Saint Lucia', 'LC'), (126, 'Liechtenstein', 'LI'), 
		(127, 'Sri Lanka', 'LK'), (128, 'Liberia', 'LR'), (129, 'Lesotho', 'LS'), (130, 'Lithuania', 'LT'), (131, 'Luxembourg', 'LU'), 
		(132, 'Latvia', 'LV'), (133, 'Libyan Arab Jamahiriya', 'LY'), (134, 'Morocco', 'MA'), (135, 'Monaco', 'MC'), 
		(136, 'Moldova, Republic of', 'MD'), (137, 'Madagascar', 'MG'), (138, 'Marshall Islands', 'MH'), (139, 'Macedonia', 'MK'), 
		(140, 'Mali', 'ML'), (141, 'Myanmar', 'MM'), (142, 'Mongolia', 'MN'), (143, 'Macau', 'MO'), (144, 'Northern Mariana Islands', 'MP'), 
		(145, 'Martinique', 'MQ'), (146, 'Mauritania', 'MR'), (147, 'Montserrat', 'MS'), (148, 'Malta', 'MT'), (149, 'Mauritius', 'MU'), 
		(150, 'Maldives', 'MV'), (151, 'Malawi', 'MW'), (152, 'Mexico', 'MX'), (153, 'Malaysia', 'MY'), (154, 'Mozambique', 'MZ'), 
		(155, 'Namibia', 'NA'), (156, 'New Caledonia', 'NC'), (157, 'Niger', 'NE'), (158, 'Norfolk Island', 'NF'), (159, 'Nigeria', 'NG'), 
		(160, 'Nicaragua', 'NI'), (161, 'Netherlands', 'NL'), 
		(162, 'Norway', 'NO'), (163, 'Nepal', 'NP'), (164, 'Nauru', 'NR'), (165, 'Niue', 'NU'), (166, 'New Zealand', 'NZ'),
		(167, 'Oman', 'OM'), (168, 'Panama', 'PA'), (169, 'Peru', 'PE'), (170, 'French Polynesia', 'PF'), (171, 'Papua New Guinea', 'PG'), 
		(172, 'Philippines', 'PH'), (173, 'Pakistan', 'PK'), (174, 'Poland', 'PL'), (175, 'Saint Pierre and Miquelon', 'PM'), 
		(176, 'Pitcairn Islands', 'PN'), (177, 'Puerto Rico', 'PR'), (178, 'Palestinian Territory', 'PS'), (179, 'Portugal', 'PT'),
		(180, 'Palau', 'PW'), (181, 'Paraguay', 'PY'), (182, 'Qatar', 'QA'), (183, 'Reunion', 'RE'), (184, 'Romania', 'RO'),
		(185, 'Russian Federation', 'RU'), (186, 'Rwanda', 'RW'), (187, 'Saudi Arabia', 'SA'), (188, 'Solomon Islands', 'SB'), 
		(189, 'Seychelles', 'SC'), (190, 'Sudan', 'SD'), (191, 'Sweden', 'SE'), (192, 'Singapore', 'SG'), (193, 'Saint Helena', 'SH'),
		(194, 'Slovenia', 'SI'), (195, 'Svalbard and Jan Mayen', 'SJ'), (196, 'Slovakia', 'SK'), (197, 'Sierra Leone', 'SL'), 
		(198, 'San Marino', 'SM'), (199, 'Senegal', 'SN'), (200, 'Somalia', 'SO'), (201, 'Suriname', 'SR'), 
		(202, 'Sao Tome and Principe', 'ST'), (203, 'El Salvador', 'SV'), (204, 'Syrian Arab Republic', 'SY'),
		(205, 'Swaziland', 'SZ'), (206, 'Turks and Caicos Islands', 'TC'), (207, 'Chad', 'TD'), (208, 'French Southern Territories', 'TF'), 
		(209, 'Togo', 'TG'), (210, 'Thailand', 'TH'), (211, 'Tajikistan', 'TJ'), (212, 'Tokelau', 'TK'), (213, 'Turkmenistan', 'TM'),
		(214, 'Tunisia', 'TN'), (215, 'Tonga', 'TO'), (216, 'Timor-Leste', 'TL'), (217, 'Turkey', 'TR'), (218, 'Trinidad and Tobago', 'TT'),
		(219, 'Tuvalu', 'TV'), (220, 'Taiwan', 'TW'), (221, 'Tanzania, United Republic of', 'TZ'), (222, 'Ukraine', 'UA'), (223, 'Uganda', 'UG'), 
		(224, 'United States Minor Outlying Islands', 'UM'), (225, 'United States', 'US'), (226, 'Uruguay', 'UY'), (227, 'Uzbekistan', 'UZ'),
		(228, 'Holy See (Vatican City State)', 'VA'), (229, 'Saint Vincent and the Grenadines', 'VC'), (230, 'Venezuela', 'VE'), 
		(231, 'Virgin Islands, British', 'VG'), (232, 'Virgin Islands, U.S.', 'VI'), (233, 'Vietnam', 'VN'), (234, 'Vanuatu', 'VU'), 
		(235, 'Wallis and Futuna', 'WF'), (236, 'Samoa', 'WS'), (237, 'Yemen', 'YE'), (238, 'Mayotte', 'YT'), (239, 'Serbia', 'RS'), 
		(240, 'South Africa', 'ZA'), (241, 'Zambia', 'ZM'), (242, 'Montenegro', 'ME'), (243, 'Zimbabwe', 'ZW'), (244, 'Anonymous Proxy', 'A1'), 
		(245, 'Satellite Provider', 'A2'), (246, 'Other', 'O1'), (247, 'Aland Islands', 'AX'), (248, 'Guernsey', 'GG'), (249, 'Isle of Man', 'IM'),
		(250, 'Jersey', 'JE'), (251, 'Saint Barthelemy', 'BL'), (252, 'Saint Martin', 'MF');";
  $wpdb->query($sql);
  
  // ---------------------------------------------- api default data ---------------------------------------------------------
  // level
  $sql = "INSERT IGNORE INTO `" . TBL_MGM_REST_API_LEVEL. "` (`id`, `level`, `name`, `permissions`, `limits`) VALUES 
		 (1, 1, 'full access', '[]', 1000);";
  $wpdb->query($sql);
  // keys
  $sql = "INSERT IGNORE INTO `" . TBL_MGM_REST_API_KEY. "` (`id`, `api_key`, `level`, `create_dt`) VALUES 
		 (1, '".mgm_create_token()."', '1', NOW());";
  $wpdb->query($sql);  
  
// end of file