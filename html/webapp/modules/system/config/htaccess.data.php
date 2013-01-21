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
$writing_data = "<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase ".$path."
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule (/images/|/css/|/js/|/themes/)(.*)$ ".$path."$1$2 [QSA,L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ ".$path."index.php?_restful_permalink=$1 [QSA,L]
</IfModule>
";
?>