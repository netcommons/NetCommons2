<?php
// $Id: UPLOAD_ERR.php,v 1.3 2007/04/17 10:09:56 arpad Exp $


/**
 * Replace upload error constants
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/features.file-upload.errors
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.3 $
 * @since       PHP 4.3.0
 */
if (!defined('UPLOAD_ERR_OK')) {
    define('UPLOAD_ERR_OK', 0);
}

if (!defined('UPLOAD_ERR_INI_SIZE')) {
    define('UPLOAD_ERR_INI_SIZE', 1);
}

if (!defined('UPLOAD_ERR_FORM_SIZE')) {
    define('UPLOAD_ERR_FORM_SIZE', 2);
}

if (!defined('UPLOAD_ERR_PARTIAL')) {
    define('UPLOAD_ERR_PARTIAL', 3);
}

if (!defined('UPLOAD_ERR_NO_FILE')) {
    define('UPLOAD_ERR_NO_FILE', 4);
}

if (!defined('UPLOAD_ERR_NO_TMP_DIR')) {
    define('UPLOAD_ERR_NO_TMP_DIR', 6);
}

if (!defined('UPLOAD_ERR_CANT_WRITE')) {
    define('UPLOAD_ERR_CANT_WRITE', 7);
}

if (!defined('UPLOAD_ERR_EXTENSION')) {
    define('UPLOAD_ERR_EXTENSION', 8);
}
?>