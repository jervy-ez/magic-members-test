<!--reports-->
<div id="projection">
	<form name="frmprojection" id="frmprojection" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=projection" class="marginpading0px">
	<div id="report_projection"></div>			
	</form>
	
</div>

<script language="javascript">
	// onready
	jQuery(document).ready(function(){   
		// load projection
		mgm_report_projection=function(){
			jQuery('#report_projection').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/reports&method=projection_list'); 
		}
		mgm_report_projection();
	});
</script>