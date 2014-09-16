<script language="javascript">
		//<![CDATA[
		jQuery(document).ready(function(){	
			// attach loading mask
			mgm_ajax_loader();
			// tabs
			mgm_primary_tabs = jQuery('#mgm-panel-content').tabs(
			{ fx: { opacity: 'toggle' }, idPrefix: 'ui-tabs-primary', 
			  load : function(event,ui){				  
				// set next urls								  	
				jQuery('#mgm-panel-mainmenu li a[href][title]').each(function(index){		
					// home page already									
					if(index>0){													
						// get url
						var new_url = jQuery(this).attr('href').replace('#','').replace('_','/');			
						// add if not added 
						if(jQuery.inArray(new_url, mgm_primary_tab_urls) == -1){			
							// set url																		
							mgm_primary_tabs.tabs('url', index, 'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/' + new_url);
							// push in cache
							mgm_primary_tab_urls.push(new_url);
						}
					}
				});		
				//var tab_id = jQuery(ui.panel).attr('id');
				// create secondary tabs										
				mgm_secondary_tabs = jQuery('.content-div').tabs({					
					fx: { opacity: 'toggle' }, cache: false, idPrefix: 'ui-tabs-secondary',
					spinner: '<?php _e('Loading...','mgm')?>',
					load: function(event,ui){ mgm_attach_tips();}, 
					select: function(event,ui){jQuery('#message').remove()}											  
				}); // end secondary tabs						
			  }}); // end primary tabs	
			  
			  
			// add last item css
		  	jQuery('#mgm-panel-mainmenu li:last').addClass('last');					
		});
		//]]>
	</script>	