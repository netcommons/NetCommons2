<?php
/**
 * configデータ
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
$writing_data = "RewriteEngine on
RewriteRule ^images/(.*) ".CORE_BASE_URL."/images/$1 [R=301,L]
RewriteEngine on
RewriteCond %{QUERY_STRING} ^(.*?)action=common_download_css(.*) [NC]
RewriteRule ^(.*) ".CORE_BASE_URL."/$1 [R=301,L]
RewriteEngine on
RewriteCond %{QUERY_STRING} ^(.*?)action=common_download_js(.*) [NC]
RewriteRule ^(.*) ".CORE_BASE_URL."/$1 [R=301,L]
";
?>