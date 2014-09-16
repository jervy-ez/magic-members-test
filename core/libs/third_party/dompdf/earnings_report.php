<?php
	require_once("dompdf_config.inc.php");
	$html=$_POST['earnings'];
	$dompdf = new DOMPDF();
	$dompdf->load_html($html);
	$dompdf->render();
	$dompdf->stream("earnings_report.pdf", array("Attachment" => false));
	exit(0);
?>