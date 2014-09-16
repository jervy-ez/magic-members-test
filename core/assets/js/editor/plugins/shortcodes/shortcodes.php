<?php 
	//$siteUrl = stripslashes($_POST['siteUrl']);
	$mgm_assets_url   = stripslashes($_POST['mgm_assets_url']);
	$includes_url  = stripslashes($_POST['includes_url']);
	$admin_url     = stripslashes($_POST['admin_url']);
	$blog_version     = stripslashes($_POST['blog_version']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel='stylesheet' href='<?php echo $admin_url; ?>load-styles.php?c=1&amp;dir=ltr&amp;load=admin-bar,wp-admin&amp;ver=7f0753feec257518ac1fec83d5bced6a' type='text/css' media='all' /> 
<link rel='stylesheet' id='mgm-ui-css-css'  href='<?php echo $mgm_assets_url; ?>css/default/mgm/jquery.ui.css?ver=<?php echo $blog_version; ?>' type='text/css' media='all' /> 
<link rel='stylesheet' id='mgm-admin-css-css'  href='<?php echo $mgm_assets_url; ?>css/admin/mgm.adminui.css?ver=<?php echo $blog_version; ?>' type='text/css' media='all' /> 
<style>
#message.success{color:green;}
</style>
<?php if (version_compare($blog_version, '3.6', '>=')) { ?>
<script type="text/javascript" src="<?php echo $includes_url; ?>js/jquery/jquery.js"></script>
<?php } ?>
<!--<script type="text/javascript" src="../../../../../../../../../wp-includes/js/tinymce/tiny_mce_popup.js?ver=345-20110908"></script>-->
<script type="text/javascript" src="<?php echo $includes_url; ?>js/tinymce/tiny_mce_popup.js?ver=345-20110908"></script>


<script type='text/javascript' src='<?php echo $admin_url; ?>load-scripts.php?c=1&amp;load=jquery,utils&amp;ver=edec3fab0cb6297ea474806db1895fa7'></script> 
<script type='text/javascript' src='<?php echo $mgm_assets_url; ?>js/string.js?ver=<?php echo $blog_version; ?>'></script> 
<script type='text/javascript' src='<?php echo $mgm_assets_url; ?>js/jquery/jquery.ajaxfileupload.js?ver=<?php echo $blog_version; ?>'></script> 
<script type='text/javascript' src='<?php echo $mgm_assets_url; ?>js/jquery/jquery.validate.pack.js?ver=<?php echo $blog_version; ?>'></script>
<script type='text/javascript' src='<?php echo $mgm_assets_url; ?>js/jquery/jquery.metadata.js?ver=<?php echo $blog_version; ?>'></script>
<script type='text/javascript' src='<?php echo $mgm_assets_url; ?>js/helpers.js?ver=<?php echo $blog_version; ?>'></script> 

<script type="text/javascript">

	function protect_shortcode_insert(){
		
		var shortcode = jQuery('#protect_shortcode').val();
		
		var shortcode_argument = jQuery('#protect_shortcode_argument').val();
		
		var _close_tag = '[/'+shortcode+']';
		
		if(shortcode_argument.trim() ==""){
			var _open_tag  = '['+shortcode+']';
		}else {
			var _open_tag  = '['+shortcode+'#'+shortcode_argument+']';			
		}
		
		var _get_content =  tinyMCEPopup.editor.selection.getContent({format : 'html'});
	
		var _append_tags = _open_tag + _get_content + _close_tag;
	
		var _get_content = tinyMCEPopup.editor.execCommand('mceInsertContent', false, _append_tags);
		//var set_content =   tinyMCEPopup.selection.setContent(_append_tags);

		tinyMCEPopup.close();
	}

	function purchase_shortcode_insert(){
		
		var shortcode = jQuery('#purchase_shortcode').val();
		
		var shortcode_argument = jQuery('#purchase_shortcode_argument').val();
		
		var _close_tag = '[/'+shortcode+']';
		
		if(shortcode_argument.trim() ==""){
			var _open_tag  = '['+shortcode+']';
		}else {
			
			var different_argument_passing_list = new Array('user_upgrade','user_purchase_another_membership');
			
			if(jQuery.inArray( shortcode, different_argument_passing_list ) !=-1){
				var _open_tag  = '['+shortcode+' '+shortcode_argument+']';
			}else{
				var _open_tag  = '['+shortcode+'#'+shortcode_argument+']';
			}			
		}
		
		var _get_content =  tinyMCEPopup.editor.selection.getContent({format : 'html'});
	
		var _append_tags = _open_tag;
	
		var _get_content = tinyMCEPopup.editor.execCommand('mceInsertContent', false, _append_tags);
		//var set_content =   tinyMCEPopup.selection.setContent(_append_tags);

		tinyMCEPopup.close();
	}

	function other_shortcode_insert(){
		
		var shortcode = jQuery('#other_shortcode').val();
		
		var shortcode_argument = jQuery('#other_shortcode_argument').val();
		
		var _close_tag = '[/'+shortcode+']';
		
		if(shortcode_argument.trim() ==""){
			var _open_tag  = '['+shortcode+']';
		}else {
			
			var different_argument_passing_list = new Array('user_register','user_list','logout_link');
			
			if(jQuery.inArray( shortcode, different_argument_passing_list ) !=-1){
				var _open_tag  = '['+shortcode+' '+shortcode_argument+']';
			}else{
				var _open_tag  = '['+shortcode+'#'+shortcode_argument+']';
			}		
		}
		
		var _get_content =  tinyMCEPopup.editor.selection.getContent({format : 'html'});
	
		var _append_tags = _open_tag;
	
		var _get_content = tinyMCEPopup.editor.execCommand('mceInsertContent', false, _append_tags);
		//var set_content =   tinyMCEPopup.selection.setContent(_append_tags);

		tinyMCEPopup.close();
	}

	jQuery(document).ready(function(){	
		jQuery('#protect_shortcode_insert').bind('click',function(){
			protect_shortcode_insert();
		});
		jQuery('#purchase_shortcode_insert').bind('click',function(){
			purchase_shortcode_insert();
		});
		jQuery('#other_shortcode_insert').bind('click',function(){
			other_shortcode_insert();
		});

		// value_option change
		jQuery("#protectshortcode select[name='protect_shortcode']").bind('change', function(){

			var hide_argument = new Array('no_access','private');
			// check
			option_selected = jQuery(this).val();
			jQuery("#protectargument").show();
			//if found hide the argument list
			if(jQuery.inArray( option_selected, hide_argument ) !=-1){
				jQuery("#protectargument").hide();
			}			
		});		
		
		// value_option change
		jQuery("#purchaseshortcode select[name='purchase_shortcode']").bind('change', function(){

			var hide_argument = new Array('subscription_packs');
			// check
			option_selected = jQuery(this).val();
			jQuery("#purchaseargument").show();
			//if found hide the argument list
			if(jQuery.inArray( option_selected, hide_argument ) !=-1){
				jQuery("#purchaseargument").hide();
			}			
		});		
		
		// value_option change
		jQuery("#othershortcode select[name='other_shortcode']").bind('change', function(){

			var hide_argument = new Array('user_profile','lost_password','user_facebook_login','user_payment_history','membership_contents','membership_details','user_other_subscriptions','user_contents_by_membership','user_subscription','user_login');
			
			// check
			option_selected = jQuery(this).val();
			jQuery("#otherargument").show();
			//if found hide the argument list
			if(jQuery.inArray( option_selected, hide_argument ) !=-1){
				jQuery("#otherargument").hide();
			}			
		});		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
	});

</script>
</head>
<body>
<?php echo stripslashes($_POST['shortcodes']);?>
</body>
</html>
