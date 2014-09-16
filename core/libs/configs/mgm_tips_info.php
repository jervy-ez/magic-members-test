<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
 // make global
 global $mgm_tips_info;	
 // text tips
 $mgm_tips_info=array();
 
// Dashboards

 $mgm_tips_info['recentmessages']      =__('You will be informed about Magic Members news and updates with this section.','mgm');
 $mgm_tips_info['subscriptioninformation']      =__('All of our customers who bought Magic Members plugin will also get one year free update and full customer support. When your subscription expires, you can extend your account by clicking "Extend" link.','mgm');
 $mgm_tips_info['purchasedpostslast5']      =__('If you set any PPP, you can view your last 5 purchased posts in this section','mgm');
 $mgm_tips_info['memberstatistics']      =__('In this section you can view your account types and how many users they have.','mgm');
 $mgm_tips_info['versioninformation']      =__('This section will display a notification when Magic Members plugin has a new version. When a new version appears, you will see an "Update" button for you to download lastest version of Magic Members.','mgm');
 $mgm_tips_info['magicmembersnews']      =__('In this section you will view the latest Magic Members news and information','mgm');
 $mgm_tips_info['magicmembersblog']      =__('In this section you will view the latest Magic Members blog posts. You can access latest tips about how to run a membership sites.','mgm');

// Members & Roles

	// Members

 $mgm_tips_info['registeredmembers']      =__('In this section you can view all your registered members and their detailed membership information such as Account Type, Pack, Registered Date, Last Pay Date, Expiration Date, Pack Join Date and Status','mgm');
 $mgm_tips_info['advancedsearch']      =__('Advanced Search gives you the ability to sort the users by their username, user ID, user e-mail, account type, registration date, last payment, expiration date, fee and status.','mgm');
 $mgm_tips_info['updateuseraccounts']      =__('After you sort your users with Advanced Search you can change their status, membership type and membership expiration date with this section.','mgm');
 $mgm_tips_info['exportuserdata']             =__('You can select the membership type you would like to export and hit "backup". You can also exclude members with expired membership.','mgm');
 
 	// Roles

 $mgm_tips_info['subscriptionoptions']        =__('In this section you can setup as many registration options as you like, each can have different subscription name, period and price. Allowable values for Paypal duration types are as follows: for days; allowable range is 1 to 90, for months; allowable range is 1 to 24, for years; allowable range is 1 to 5. Unpredictable results may occur is these guidelines are not met. If you are using a Paypal trial then make sure to set Trial to on using the dropdown box to the left of the necessary packs.');
 $mgm_tips_info['magicaccounttypes']      =__('When a user signs up they select a subscription which in turn associates their account with an account type. You can create or delete these account types in this section.','mgm');

 	// Coupons

 $mgm_tips_info['couponlist']      =__('Coupons can be generated to either offer a fixed price or percentage discount on current subscription or to generate new subscription. If you want to specify a percentage simply add % at the end of the value so 17% for a secret membership use "#10_r_premium" where "10" is the value, "r" is what it should repeat on and "premium" the account type. You can also export the users who use the specific coupon codes.','mgm');
 
 // Content Control
 
 	// Access

 $mgm_tips_info['registrationcontrol']      =__('An extra step to the registration process asking the new user to select an account type and pay will be added. If you would like to do this process through your sidebar, please select "NO"','mgm');
 $mgm_tips_info['fullcontentprotectionsettings']      =__('Only select if you do not want users with the wrong account type to have no access including no access to teasers/extracts or error meesage to a protected post. Blogs using Pay Per Post or who want to provide additional info to their users should select "No". When enabled users with the wrong access level will see a "Page Not Found - 404. Most bloggers select "No"','mgm');
 $mgm_tips_info['privatetagredirectionsettings']      =__('When the users don\'t have the clearance to the content, instead of showing the message it will redirect the users to defined page.','mgm');
 $mgm_tips_info['rsstokensettings']      =__('This section lets you decide to give access for the full feed content to the members or not.','mgm');
 
 	// Downloads

 $mgm_tips_info['alldownloads']      =__('In this section you can add new downloads. Downloads can be associated with pages and posts. When associated, "downloads" take on the page or post permissions including post purchased. Non associated downloads are accessible by all.','mgm');
 
 	// Page

 $mgm_tips_info['pageexcludesettings']      =__('The pages is this section will always be excluded from the menu regardless of access level. Please note that they can still be accessed directly.','mgm');
 
 	// PPP
	
 $mgm_tips_info['purchasedposts']      =__('In this section you can monitor purchased posts which were bought through Pay Per Post','mgm');
 $mgm_tips_info['seperatepurchases']      =__('In this section you can add or remove access for specific users who purchased the posts manually.','mgm');
 $mgm_tips_info['giftapostpage']      =__('In this section you can send a post or a page to a specific user as a gift.','mgm');
 
 	// PPP Packs

 $mgm_tips_info['postpacks']      =__('PPP Packs are groups of purchasable posts which can be sold as one item. When users buy the pack they will be marked as purchasing each of the contained posts on the PPP page','mgm');
 
 	// Custom User Fields
	
 //$mgm_tips_info['customprofilefields']        =__('These fields can be set to allow you to gather additional data about users, including address, birthdays, date of birth. You can also specify values, generating simple captcha or prelaunch signups<br>','mgm');
 $mgm_tips_info['customfields']      =__('To Specify the order custom fields appear on the signup box drag the fields from the Deactivated Fields container into the Activated Fields container. Once there, you can drag the fields to change the order that they will be shown on the registration form.','mgm');
 $mgm_tips_info['magicmembercustomregistrationfields']      =__('In this section you can view/edit/delete the custom fields that you created.','mgm');
 $mgm_tips_info['createcustomfields']      =__('In this section you can create a new custom field to show on the registration form','mgm');
 
 // Payment Options
 
 	// Free

 $mgm_tips_info['freeoptions']      =__('Allow users to signup for a free account which expires automatically after a number of days.','mgm');
 
 	// Trial
 
 $mgm_tips_info['trialoptions']      =__('Allow users to signup for a free trial account which expires automatically after a number of days. You can set the number of days/months/years before a trial account expires on the Subscription Options page. Simply set the Account Type to "Trial" to use this gateway.','mgm');
 
 	// PayPal Standard
	
 $mgm_tips_info['paypalstandardoptions']      =__('In this section you can easily enter your information and setup a PayPal Standard payment gateway. It\'s always a good idea to switch your site from "Live Switch" to "Test Mode" until you finish and test all of your settings.','mgm');

 	// Worldpay
	
 $mgm_tips_info['worldpaysettings']      =__('In this section you can easily enter your information and setup a Worldpay payment gateway','mgm');
 
 	// Clickbank
	
 $mgm_tips_info['clickbanksettings']      =__('In this section you can easily enter your information and setup a Clickbank payment gateway with your Clickbank product','mgm');
 
 	// Payment Modules

 $mgm_tips_info['activepaymentmodules']      =__('This section allows you to activate any additional modules you have, these are normally payment gateways','mgm');
 
 // Misc. Settings
 
 	//Settings
	
 $mgm_tips_info['mainsettings']      =__('This section contains most of the general settings related to the function of Magic Members.','mgm');
 $mgm_tips_info['emailconfigurationsettings']      =__('In this section you can set your Name and E-mail adress for the e-mails that will send by the system automatically.','mgm');
 $mgm_tips_info['removemagicmembers']      =__('In this section you can remove Magic Members. This action is irreversible. Use with caution','mgm');
 
 	// Setup

 $mgm_tips_info['magicmemberssetup']      =__('This page allows you to setup Magic Members permissions for multiple posts at a time. It allows you to select one or more account types and then one or more posts. Private tags will automatically be added if partial post protection is enabled.','mgm');
 
 	// Messages
	
 $mgm_tips_info['messages']                   =__('Here you can edit the various messages shown to the user unless noted these messages can use HTML but not PHP some messages can contain special tags such as login and register.','mgm');
 $mgm_tips_info['mainmessages']      =__('In this section you can edit "Subscription Introduction" and "Terms & Conditions" messages. The messages can contain HTML.','mgm');
 $mgm_tips_info['postmessages']      =__('In this section you can edit how the messages inside the posts show. Messages can contain HTML and Special Tags that are allowed.','mgm');
 $mgm_tips_info['errormessages']      =__('In this section you can edit the various error messages.The messages can contain HTML and [[USERNAME]] tag when needed.','mgm');
 $mgm_tips_info['templates']      =__('In this section you can edit "Membership Pack Description Template" and "Purchasable Post Pack Template" messages. The messages can contain HTML.','mgm');
 
 // Support Docs
 
 	// Installation
	
 $mgm_tips_info['generalinformation']      =__('In this section you can find general information about Magic Members plugin.','mgm');
	
	// Trouble Shooting
	
 $mgm_tips_info['troubleshooting']      =__('If you\'re not able to find your answers in our training videos, support guides, or comprehensive FAQs, simply contact our support staff and they will be happy to help you.','mgm');
	
	// Tutorials
	
 $mgm_tips_info['tutorials']      =__('Coming Soon.','mgm');