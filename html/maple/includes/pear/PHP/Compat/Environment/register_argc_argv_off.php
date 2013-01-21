<?php
// $Id: register_argc_argv_off.php,v 1.4 2008/11/01 20:15:11 arpad Exp $


/**
 * Emulate enviroment register_argc_argv=off
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/manual/en/ini.core.php#ini.register-argc-argv
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.4 $
 */
if (isset($_GLOBALS['argc']) || isset($_SERVER['argc'])) {
    unset($GLOBALS['argc'], $GLOBALS['argv'], $_SERVER['argc'], $_SERVER['argv']);
    
    // Register the change
    ini_set('register_argc_argv', 'off');
}
