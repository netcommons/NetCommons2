<?php
include_once dirname(__FILE__).'/jcode_1.35a/jcode_wrapper.php';

function mb_convert_encoding($str, $to, $from = 'auto')
{
	$str = jcode_convert_encoding($str, $to, $from);	
	return $str;
}

function mb_strlen($str)
{
	return jstrlen(jcode_convert_encoding($str, "EUC-JP", _CHARSET));
}

function mb_substr($str, $start = 0, $length = NULL)
{
	if (!isset($length)) {
		$length = mb_strlen($str) - $start;
	}
	$rtn = jsubstr(jcode_convert_encoding($str, "EUC-JP", _CHARSET), $start, $length);
	return jcode_convert_encoding($rtn, _CHARSET, "EUC-JP");
}
function mb_detect_encoding($str) {
	$encode = AutoDetect($str);
	
	if ($encode == 0) {
		$encode = "ASCII";
	} elseif ($encode == 1) {
		$encode = "EUC-JP";
	} elseif ($encode == 2) {
		$encode = "SJIS";
	} elseif ($encode == 3) {
		$encode = "ISO-2022-JP";
	} elseif ($encode == 4) {
		$encode = "UTF-8";
	} elseif ($encode == 5) {
		$encode = "";
	}

	return $encode;
}
?>