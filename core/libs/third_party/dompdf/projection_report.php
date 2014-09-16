<?php
	require_once("dompdf_config.inc.php");
	$html=$_POST['projection'];
	$dompdf = new DOMPDF();
	$dompdf->load_html($html);
	$dompdf->render();
	$dompdf->stream("projection_report.pdf", array("Attachment" => false));
	exit(0);
?>