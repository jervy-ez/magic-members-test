<?php
//mgm strstr - issue #1701
function mgm_strstr($haystack, $needle, $before_needle = false) {
    if (!$before_needle) {
    	return strstr($haystack, $needle);
    }else {
    	return substr($haystack, 0, strpos($haystack, $needle));
    }
}

if (empty($_REQUEST['csv_url'])){
	die("<b>Empty file! download aborted!</b>");
}

$filepath = mgm_strstr(__FILE__, 'plugins', true) . 'uploads/mgm/exports/' . basename($_REQUEST['csv_url']);

// check connection
if( connection_status() != 0 ) {
	die("<b>Connection failed!</b>");
}

// see if the file exists
if (!is_file($filepath)){
	die("<b>404 File not found!</b>");
}
	// size
$size = @filesize($filepath);
$fileinfo = @pathinfo($filepath);
// error
if ($size == 0 ) {
	die('Empty file! download aborted');
}
// workaround for IE filename bug with multiple periods / multiple dots in filename
// that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe
// set filename
$filename = (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) ? preg_replace('/\./', '%2e', $fileinfo['basename'], substr_count($fileinfo['basename'],'.') - 1) : $fileinfo['basename'];
// extension
$file_extension = strtolower($fileinfo['extension']);
	
// flag
$is_resume = true;
$range = '';
// check if http_range is sent by browser (or download manager)
if($is_resume && isset($_SERVER['HTTP_RANGE'])){
	// split
	list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
	// check
	if ($size_unit == 'bytes'){
		// multiple ranges could be specified at the same time, but for simplicity only serve the first range
		// http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
		list($range, $extra_ranges) = explode(',', $range_orig, 2);
	}
}
//figure out download piece from range (if set)
if ($range) {
	list($seek_start, $seek_end) = explode('-', $range, 2);
}

// set start and end based on range (if set), else set defaults
// also check for invalid ranges.
$seek_end = (empty($seek_end)) ? ($size - 1) : min(abs(intval($seek_end)),($size - 1));
$seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);
//issue #1375
session_write_close();
//add headers if resumable
if ($is_resume){
	// Only send partial content header if downloading a piece of the file (IE workaround)
	if ($seek_start > 0 || $seek_end < ($size - 1)) {
		// header
		header('HTTP/1.1 206 Partial Content');
	}
	// others
	header('Accept-Ranges: bytes');
	header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$size);
}

//headers for IE Bugs (is this necessary?)
//header("Cache-Control: cache, must-revalidate");
//header("Pragma: public");
header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: '.($seek_end - $seek_start + 1));
//open the file
$fp = @fopen($filepath, 'rb');
// seek to start of missing part
@fseek($fp, $seek_start);
//start buffered download
while(!feof($fp)){
	//reset time limit for big files
	@set_time_limit(0);
	@ini_set('memory_limit', 1073741824);	// 1024M
	print(@fread($fp, 1024*8));
	@ob_flush();
	@flush();
	// sleep
	@usleep(1000);
	// flush
	@ob_end_flush();	//keep this as there were some memory related issues(#545)
}
// close
@fclose($fp);
/* : OLD CODE	
header('Content-Type: application/csv');
$file = basename($_REQUEST['csv_url']);
header('Content-Disposition: attachment; filename='.$file);
header('Pragma: no-cache');
readfile($_REQUEST['csv_url']);*/
?>
