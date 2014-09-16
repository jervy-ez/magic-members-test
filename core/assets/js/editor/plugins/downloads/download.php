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
<script type='text/javascript' src='<?php echo $mgm_assets_url; ?>js/helpers.js?ver=<?php echo $blog_version; ?>'></script> 
<script type='text/javascript' src='<?php echo $mgm_assets_url; ?>js/string.js?ver=<?php echo $blog_version; ?>'></script> 
<script type='text/javascript' src='<?php echo $mgm_assets_url; ?>js/jquery/jquery.ajaxfileupload.js?ver=<?php echo $blog_version; ?>'></script> 
<script type='text/javascript' src='<?php echo $mgm_assets_url; ?>js/jquery/jquery.ajaxfileupload.js?ver=<?php echo $blog_version; ?>'></script> 
<script type='text/javascript' src='<?php echo $mgm_assets_url; ?>js/jquery/jquery.validate.pack.js?ver=<?php echo $blog_version; ?>'></script>
<script type='text/javascript' src='<?php echo $mgm_assets_url; ?>js/jquery/jquery.metadata.js?ver=<?php echo $blog_version; ?>'></script>
<script type='text/javascript' src='<?php echo $mgm_assets_url; ?>js/helpers.js?ver=<?php echo $blog_version; ?>'></script> 

<script type="text/javascript">

	function _download_option(){
		var radioButtons = document.getElementsByName("download_option");
		 for (var x = 0; x < radioButtons.length; x ++) {
		 	 if (radioButtons[x].checked) {
		 	 	return radioButtons[x].id;
		 	 }
		 }
	}

	function insert(){
		
		var tag =_download_option();
		var _option ='';
		if(tag =='link')
			_option = "["+document.getElementById('download_hook').value+"#" + document.getElementById('download_link').value+"]";
		else
			_option = "["+document.getElementById('download_hook').value+"#" + document.getElementById('download_link').value+"#"+tag+"]";
		
		tinyMCEPopup.editor.execCommand('mceInsertClipboardContent', false, {content : _option, wordContent : true});
		tinyMCEPopup.close();
	}

jQuery(document).ready(function(){	

	jQuery('#insert').bind('click',function(){
		insert();
	});
	
	var subURL='';
	if(jQuery('#submitUrlID')) {
		subURL = jQuery('#submitUrlID').val();
	}
	
	jQuery("#frmdwnadd").validate({
		submitHandler: function(form) {  
			jQuery.ajax({
                //url : form.action,
                url : subURL,
                type: 'post',
                data: jQuery(form).serialize(),
                dataType: 'json',
                success: function(data, statusText, xhr, $form) {
                    if(data.status == 'success') {
                    	_option = "["+data.download_hook+"#" +data.download_id+"]";
              			tinyMCEPopup.editor.execCommand('mceInsertClipboardContent', false, {content : _option, wordContent : true});
						tinyMCEPopup.close();
                    }else {
 						if(data.status == 'error') {
							jQuery('#title').parent().append('<label for="title" generated="true" class="invalid">'+data.message+'</label>');
						}                   	
                    }
                     
                }
            });
		 	return false;		
		},
		rules:{
			title :"required",
			download_file :{required : function(){ return (jQuery('#direct_url').val().toString().is_empty() ? true : false )}}
		},
		messages:{
			title :"Please enter title",
			download_file :"Please upload the file or set direct url"
		},
		errorClass: 'invalid'			
	});
	
	
	jQuery("#submit_download").click(function(){
		if(jQuery("#frmdwnadd").valid()){
			jQuery("#frmdwnadd").submit();
		return false;
		}
	});

	
	function mgm_download_file_upload(obj){
	
		if(jQuery(obj).val().toString().is_empty()==false){	
			// check ext	
			if((/\.(exe|bin|php|pl|cgi)$/i).test(jQuery(obj).val().toString())) {
				alert("Please do not upload unsafe files");
				return;
			}	
			//var bUrl='<?php //echo $siteUrl; ?>/wp-admin/';
			var bUrl='<?php echo $admin_url; ?>';
			// process upload 		
			// vars													
			var form_id = jQuery(jQuery(obj).get(0).form).attr('id');					
			
			//alert('form_id ' + form_id);	
			// before send, remove old message
			jQuery('#'+form_id+' #message').remove();		
			// create new message
			jQuery('#'+form_id).prepend('<div id="message" class="running"><span>Processing...</span></div>');
			// remove old hidden
			jQuery("#"+form_id+" :input[type='hidden'][name='download_file_new']").remove();
			jQuery.ajaxFileUpload({
				url:bUrl+'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/downloads&method=file_upload', 
				secureuri:false,
				fileElementId:jQuery(obj).attr('id'),
				dataType: 'json',						
				success: function (data, status){	
					// uploaded					
					if(data.status=='success'){										
						// change file
						jQuery("#"+form_id+" :file[name='"+jQuery(obj).attr('name')+"']").parent().html(data.file_info.file_url);
						// remove
						jQuery("#"+form_id+" :file[name='"+jQuery(obj).attr('name')+"']").remove();
						// set hidden
						jQuery('#'+form_id).append('<input type="hidden" name="download_file_new" value="'+data.file_info.file_url+'">');
						jQuery('#'+form_id).append('<input type="hidden" name="download_file_new_realname" value="'+data.file_info.real_name+'">');									
						
						// show message
						mgm_show_message('#'+form_id, data);																
					}											
				},
				error: function (data, status, e){
					alert('Error occured in upload');
				}
			});		
		}
	}
	// bind uploader
	mgm_file_uploader('#download_manage', mgm_download_file_upload);

});

</script>
<link href="css/download.css?ver=345-20110908" rel="stylesheet" type="text/css" />
</head>
<body>
<?php echo stripslashes($_POST['downloads']);?>
</body>
</html>
