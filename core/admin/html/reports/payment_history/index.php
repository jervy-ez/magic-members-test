<!--reports-->
<div id="payment_history">
	<form name="frmPaymentHistory" id="frmsales" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=payment_history_list" class="marginpading0px">
	<div id="report_payment_history"></div>			
	</form>
	<iframe id="ifrm_backup_two" 
			src="" 
			allowtransparency="true" 
			width="0" 
			height="0" 
			frameborder="0">
	</iframe>	
</div>

<script language="javascript">
	// onready
	jQuery(document).ready(function(){   

		// load list
		mgm_payment_history_list=function(){
			// hide old message
			mgm_hide_message('#payment_history');
			// load
			jQuery('#report_payment_history').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=payment_history_list'); 
		}

		// show list, keep the last query
		mgm_show_payment_history_list=function(m) {
			var _m = m || false;
			// post
			jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=payment_history_list', type: 'POST', cache:false, data : jQuery("#search-table :input").serialize(),
				beforeSend: function(){	
					// show message
					mgm_show_message('#payment_history', {status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
				 },
				 success:function(data){																																		
					// append 
					jQuery('#report_payment_history').html(data);					
					// show message
					if(_m){
						mgm_show_message('#payment_history', {status:'success', message: jQuery('#last_search_message').html() }, true);
					}else{
						mgm_hide_message('#payment_history');
					}								 
				 }
			});
		}	

		// show list, keep the last query
		mgm_export_payment_history_list=function(m) {
			var _m = m || false;
			// export format
			jQuery("#search-table :input[name='export_format']").val(jQuery("#payment_history select[id='select_export_format']").val());
			// post
			jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=payment_history_export', 
						type: 'POST', 
						cache:false, 
						data : jQuery("#search-table :input").serialize(),
						dataType:'json',
						beforeSend: function(){	
							// show message
							mgm_show_message('#payment_history', 
												{status:'running', message:'<?php _e('Processing','mgm')?>...'},true);														
				 },
				 success:function(data){																											// show message
					if(_m){
						mgm_show_message('#payment_history', data);	
						// set backup
						jQuery('#ifrm_backup_two').attr('src', data.src);										
															
					}else{
						mgm_hide_message('#payment_history');
					}								 
				 }
			});
		}		
		mgm_payment_history_list();
	});
</script>