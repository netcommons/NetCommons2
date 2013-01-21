<?php
/**
 * DB接続のエラー表示用（まだ、インストール前の可能性があるため、テンプレートは固定）
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
$content =
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="" lang="">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title></title>
<style type="text/css">
img {border:0px;}
form {margin:0px;}
body {
	margin:0px; 
	padding:0px; 
	text-decoration:none; 
	color:#6e6e6e;
	margin:0px; 
	padding:0px; 
	text-decoration:none;
	font-size:80%;
	font-family:Arial, sans-serif;
}
table {
	border-width:0px; 
	padding:0px; 
	border-collapse:collapse; 
	font-size: small;
}
.redirect_body {
	background-color:#e0dcfc;
}
.redirect_body .redirect_main{
	margin: 120px 150px 80px;
	text-align:left;
}
.redirect_body .redirect_text{
	padding-left:8px;
	font-size:210%;
	border: 1px solid #ff0000;
	background-color:#ffffff;
}
</style>

</head>
<body class="redirect_body">
<table class="redirect_main" summary="">
  <tr>
    <td class="redirect_text">';
if(defined("INSTALL_PERMISSION_ERROR")) {
	$content .= INSTALL_PERMISSION_ERROR;
} else if(defined("_INSTALL_INI_FAILURE_IS_WRITEABLE_MES")) {
	$content .= _INSTALL_INI_FAILURE_IS_WRITEABLE_MES;
} else {
	/* TODO:英語の文章も表示するべき */
	$content .= 'DB接続に失敗しました。<br />
    	インストールしていないか、正常にインストールされていない<br />
    	可能性があります。<br />
    	webapp/config/install.inc.phpに書き込み権限を設定し、<br />
    	再度、読み込んでください。';
}
$content .= '
    </td>
  </tr>
</table>
</body>
</html>
';
?>