<!--reports-->
<div id="earnings">
	<form name="frmearnings" id="frmearnings" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=earnings_list" class="marginpading0px">
	<div id="report_earnings"></div>		
	</form>
	
</div>

<script language="javascript">
	// onready
	jQuery(document).ready(function(){   
		// load earnings
		mgm_report_earnings=function(){
			jQuery('#report_earnings').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=earnings_list'); 
		}
		mgm_report_earnings();
	});
</script>