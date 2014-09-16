<?php
	require_once("dompdf_config.inc.php");
	$html=$_POST['sales'];
	$dompdf = new DOMPDF();
	$dompdf->load_html($html);
	$dompdf->render();
	$dompdf->stream("sales_report.pdf", array("Attachment" => false));
	exit(0);
?>