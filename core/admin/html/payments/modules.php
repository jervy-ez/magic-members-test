<!--modules-->
<div class="payment_modules">
	<?php mgm_box_top('Payment Settings');?>		
	<?php //mgm_pr($data['payment_modules']);?>
	<?php /*foreach($data['modules'] as $module):
			echo sprintf('<div id="payment_module_box_container%s">%s</div>', $module['code'], $module['html']);	
		endforeach;*/?>		
	<div id="payment_module_box_containers">
		<?php 
		// format
		$format_str = '<div id="payment_module_box_container_%s">
						   <div class="module_settings_box">
								%s
						   </div>
					   </div>';
		// <div style="vertical-align: middle"><img src="%s/images/ajax/ajax-loader-big.gif"> <br><br> %s</div> 			   
		// loop			   
		foreach($data['payment_modules'] as $module):	
			$str_loading = mgm_get_loading_icon(('Loading ' . mgm_get_module(('mgm_' . $module))->get_name()), 'block');
			echo sprintf($format_str, ('mgm_' . $module), $str_loading);	
		endforeach;?>	
	</div>	
	<div class="clearfix"></div>
	<?php mgm_box_bottom();?>
</div>
<script language="javascript">
	<!--
	jQuery(document).ready(function(){		
		
		if(jQuery('#admin_payments').length) {
			var admin_payments = 'admin_payments';
		}else{
			var admin_payments = 'Payments_Settings';			
		}		
		// update module
		mgm_update_module = function(form, act){		
			// form	
		 	var form_id = jQuery(form).attr('id');		
			// act
			jQuery("#"+form_id+" :input[type='hidden'][name='act']").val(act);
			// post
		 	jQuery(form).ajaxSubmit({type: "POST",										  
				 dataType: 'json',		
				 iframe: false,				 										 
				 beforeSubmit: function(){	
					// show message
					mgm_show_message('#'+form_id, {status:'running', message:'<?php _e('Processing','mgm')?>...'});														
				 },
				 success: function(data){	
				 	// show message
					mgm_show_message('#'+form_id, data);																	
					// success	
					if(data.status=='success'){													
						// create tab for act
						if(act == 'status_update'){
							// set new status					
							if(data.enable == 'Y'){	
								jQuery("#status_label_"+data.module.code).html( '<?php _e('Enabled','mgm');?>' );
								jQuery("#status_label_"+data.module.code).removeClass('s-disabled').addClass('s-enabled');
							}else{
								jQuery("#status_label_"+data.module.code).html( '<?php _e('Disabled','mgm')?>' );
								jQuery("#status_label_"+data.module.code).removeClass('s-enabled').addClass('s-disabled');
							}
							// status update										
							mgm_update_payment_tabs(data);										
						}else if(act == 'logo_update'){
							// load
							jQuery('#'+admin_payments+' .payment_modules #payment_module_box_container_' + data.module.code).load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payments&method=module_setting_box&module='+data.module.code, function(){
								// bind uploader
								mgm_file_uploader('#'+admin_payments+' .payment_modules #module_settings_box_' + data.module.code, mgm_upload_logo);	// check
								// bind status modifier
								mgm_status_modifier(data.module.code);											
							});
						}	
					}				 
				 }
			}); // end 			 	
		 }
		
		// mgm update payment tabs
		 mgm_update_payment_tabs = function(data){	
		 	// undefined
		 	if(data.module == 'undefined') return;
			
			// remove disabled		
			index = 0 ; 		
			// loop each
			jQuery('#'+admin_payments+' .tabs li a[href]').each(function(){
				// remove
				if(data.enable == 'N'){
					// check
					if(jQuery(this).children('span').html() == data.module.name){					
						// remove
						//jQuery('#admin_payments .content-div').tabs('remove', index);	
						mgm_tabs_remove( '#'+admin_payments+' .content-div', index);					
					}			
				}
				// update index
				index++;				
			});			
			
			

			// get length
			try{
				length = jQuery('#'+admin_payments+' .tabs li a[href]').size()//jQuery('#admin_payments .content-div').tabs( 'length' );	
			}catch(x){
				length= 0;
			}		
			// add new tab
			if(data.enable == 'Y'){	
				// url
				var url = 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payments&method=module_settings&module=' + data.module.code;				
				// create at end
				// jQuery('#admin_payments .content-div').tabs('add', ('#' + data.module.code), data.module.name, length);	// use hash code to properly create div	
				// reset
				// jQuery('#admin_payments .content-div').tabs('url', length , url);// set url now
				// select/load
				// jQuery('#admin_payments .content-div').tabs('load', length);	
				// add
				mgm_tabs_add( '#'+admin_payments+' .content-div', url, data.module.name);							
			}

			// refresh
			// jQuery('.content-div').tabs( "refresh" );	
			// jQuery('#admin_payments .content-div').tabs();// reload
		 }	
		 	
		// bind enable/disable
		mgm_status_modifier = function(module){		
			// unbind
			jQuery("#"+admin_payments+" .payment_modules #module_settings_box_" +  module + " :checkbox[name='payment[enable]']").unbind('click', function(){});	
			// attach event
			jQuery("#"+admin_payments+" .payment_modules #module_settings_box_" +  module + " :checkbox[name='payment[enable]']").bind('click', function(){				
				// get form
				var form = jQuery(this).get(0).form;					
				// send	status_update		 		
				mgm_update_module(form, 'status_update');
			});
		}
		 
		// load
		mgm_load_payment_modules=function(){
			// loop
			jQuery("#"+admin_payments+" .payment_modules div[id^='payment_module_box_container_']").each(function(){
				// module 
				var module = jQuery(this).attr('id').replace('payment_module_box_container_','');
				// load
				jQuery(this).load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payments&method=module_setting_box&module='+module, function(){
					// bind uploader for settings_box quick uploads
					mgm_file_uploader('#'+admin_payments+' .payment_modules #module_settings_box_' + module, mgm_upload_logo);		
					// bind status modifier
					mgm_status_modifier(module);					
				});
			})
		}
		// load
		mgm_load_payment_modules();		 
	});	 
	//-->
</script>