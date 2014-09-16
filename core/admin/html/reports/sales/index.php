<!--reports-->
<div id="sales">
	<form name="frmsales" id="frmsales" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=sales_list" class="marginpading0px">
	<div id="report_sales"></div>			
	</form>
	
</div>

<script language="javascript">
	// onready
	jQuery(document).ready(function(){   
		// load sales
		mgm_report_sales=function(){
			jQuery('#report_sales').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=sales_list'); 
		}
		mgm_report_sales();
	});
</script>