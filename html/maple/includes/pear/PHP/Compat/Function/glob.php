<?php
// $Id: glob.php,v 1.7 2007/04/17 10:09:56 arpad Exp $


if (!defined('GLOB_ERR')) {
    define('GLOB_ERR', 1);
}
if (!defined('GLOB_MARK')) {
    define('GLOB_MARK', 2);
}
if (!defined('GLOB_NOSORT')) {
    define('GLOB_NOSORT', 4);
}
if (!defined('GLOB_NOCHECK')) {
    define('GLOB_NOCHECK', 16);
}
if (!defined('GLOB_NOESCAPE')) {
    define('GLOB_NOESCAPE', 64);
}
if (!defined('GLOB_BRACE')) {
    define('GLOB_BRACE', 1024);
}
if (!defined('GLOB_ONLYDIR')) {
    define('GLOB_ONLYDIR', 8192);
}

/**
 * Replace glob()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/glob
 * @author      Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.7 $
 * @since       PHP 4.3.0
 * @require     PHP 3.0.9 (preg_replace)
 */
function php_compat_glob($pattern, $flags = 0)
{
    $return_failure = ($flags & GLOB_NOCHECK) ? array($pattern) : false;
    
    // build path to scan files
    $path = '.';
    $wildcards_open = '*?[';
    $wildcards_close = ']';
    if ($flags & GLOB_BRACE) {
        $wildcards_open .= '{';
        $wildcards_close .= '}';
    }
    $prefix_length = strcspn($pattern, $wildcards_open);
    if ($prefix_length) {
        if (DIRECTORY_SEPARATOR == '\\')  {
            $sep = ($flags & GLOB_NOESCAPE) ? '[\\\\/]' : '(?:/|\\\\(?![' . $wildcards_open . $wildcards_close . ']))';
        } else {
            $sep = '/';
        }
        if (preg_match('#^(.*)' . $sep . '#', substr($pattern, 0, $prefix_length), $matches)) {
            $path = $matches[1];
        }
    }
    $recurse = (strpos($pattern, DIRECTORY_SEPARATOR, $prefix_length) !== false);
    
    // scan files
    $files = php_compat_glob_scan_helper($path, $flags, $recurse);
    if ($files === false) {
        return $return_failure;
    }

    // build preg pattern
    $pattern = php_compat_glob_convert_helper($pattern, $flags);
    $convert_patterns = array('/\{([^{}]*)\}/e', '/\[([^\]]*)\]/e');
    $convert_replacements = array(
        'php_compat_glob_brace_helper(\'$1\', $flags)',
        'php_compat_glob_charclass_helper(\'$1\', $flags)'
    );
    while ($flags & GLOB_BRACE) {
        $new_pattern = preg_replace($convert_patterns, $convert_replacements, $pattern);
        if ($new_pattern == $pattern) {
            break;
        }
        $pattern = $new_pattern;
    }
    $pattern = '#^' . $pattern . '\z#';

    // process files
    $results = array();
    foreach ($files as $file => $dir) {
        if (!preg_match($pattern, $file, $matches)) {
            continue;
        }
        if (($flags & GLOB_ONLYDIR) && !$dir) {
            continue;
        }
        $file = (($flags & GLOB_MARK) && $dir) ? $file . DIRECTORY_SEPARATOR : $file;
        if ($flags & GLOB_BRACE) {
            // find last matching subpattern
            $rank = 0;
            for ($i = 1, $matchc = count($matches); $i < $matchc; $i++) {
                if (strlen($matches[$i])) {
                    $rank = $i;
                }
            }
            $file = array($file, $rank);
        }
        $results[] = $file;
    }
    
    if ($flags & GLOB_BRACE) {
        usort($results, ($flags & GLOB_NOSORT) ? 'php_compat_glob_brace_nosort_helper' : 'php_compat_glob_brace_sort_helper');
    } else if ($flags & GLOB_NOSORT) {
        usort($results, 'php_compat_glob_nosort_helper');
    } else {
        sort($results);
    }
    
    // array_values() for php 4 +
    $reindex = array();
    foreach ($results as $result) {
        $reindex[] = ($flags & GLOB_BRACE) ? $result[0] : $result;
    }

    if (($flags & GLOB_NOCHECK) && !count($reindex)) {
        return $return_failure;
    }
    return $reindex;
}

/**
 * Scans a path
 *
 * @param string $path
 *  the path to scan
 * @param int $flags
 *  the flags passed to glob()
 * @param bool $recurse
 *  true to scan recursively
 * @return mixed
 *  an array of files in the given path where the key is the path,
 *  and the value is 1 if the file is a directory, 0 if it isn't.
 *  Returns false on unrecoverable errors, or all errors when
 *  GLOB_ERR is on.
 */
function php_compat_glob_scan_helper($path, $flags, $recurse = false)
{
    if (!is_readable($path)) {
        return false;
    }
    $results = array();
    if (is_dir($path)) {
        $fp = opendir($path);
        if (!$fp) {
            return ($flags & GLOB_ERR) ? false : array($path);
        }
        if ($path != '.') {
            $results[$path] = 1;
        }
        while (($file = readdir($fp)) !== false) {
            if ($file[0] == '.' || $file == '..') {
                continue;
            }
            $filepath = ($path == '.') ? $file : $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filepath)) {
                $results[$filepath] = 1;
                if (!$recurse) {
                    continue;
                }
                $files = php_compat_glob_scan_helper($filepath, $flags);
                if ($files === false) {
                    if ($flags & GLOB_ERR) {
                        return false;
                    }
                    continue;
                }
                // array_merge for php 4 +
                foreach ($files as $rfile => $rdir) {
                    $results[$rfile] = $rdir;
                }
                continue;
            }
            $results[$filepath] = 0;
        }
        closedir($fp);
    } else {
        $results[$path] = 0;
    }
    return $results;
}

/**
 * Converts a section of a glob pattern to a PCRE pattern
 *
 * @param string $input
 *  the pattern to convert
 * @param int $flags
 *  the flags passed to glob()
 * @return string
 *  the escaped input
 */
function php_compat_glob_convert_helper($input, $flags)
{
    $opens = array(
        '{' => array('}', 0),
        '[' => array(']', 0),
        '(' => array(')', 0)
    );
    $ret = '';
    for ($i = 0, $len = strlen($input); $i < $len; $i++) {
        $skip = false;
        $c = $input[$i];
        $escaped = ($i && $input[$i - 1] == '\\' && ($flags & GLOB_NOCHECK == false));
        
        // skips characters classes and subpatterns, they are escaped in their respective helpers
        foreach ($opens as $k => $v) {
            if ($v[1]) {
                if ($c == $v[0] && !$escaped) {
                    --$opens[$k][1];
                    $ret .= $c;
                    continue 2;
                }
                $skip = true;
            }
        }
        if (isset($opens[$c])) {
            if (!$escaped) {
                ++$opens[$c][1];
            }
            $ret .= $c;
            continue;
        }
        if ($skip) {
            $ret .= $c;
            continue;
        }
        
        // converts wildcards
        switch ($c) {
        case '*':
            $ret .= $escaped ? '*' : '.*';
            continue 2;
        case '?':
            if ($escaped) {
                continue;
            }
            $ret .= '.';
            continue 2;
        }
        $ret .= preg_quote($c, '#');
    }
    return $ret;
}

/**
 * Converts glob braces
 *
 * @param string $brace
 *  the contents of the braces to convert
 * @param int $flags
 *  the flags passed to glob()
 * @return string
 *  a PCRE subpattern of alternatives
 */
function php_compat_glob_brace_helper($brace, $flags)
{
    $alternatives = explode(',', $brace);
    for ($i = count($alternatives); $i--;) {
        $alternatives[$i] = php_compat_glob_convert_helper($alternatives[$i], $flags);
    }
    return '(?:(' . implode(')|(', $alternatives) . '))';
}

/**
 * Converts glob character classes
 *
 * @param string $class
 *  the contents of the class to convert
 * @param int $flags
 *  the flags passed to glob()
 * @return string
 *  a PCRE character class
 */
function php_compat_glob_charclass_helper($class, $flags)
{
    if (strpos($class, '-') !== false) {
        $class = strtr($class, array('-' => '')) . '-';
    }
    if (strpos($class, ']') !== false) {
        $class = ']' . strtr($class, array(']' => ''));
    }
    if (strpos($class, '^') !== false) {
        $class = '\^' . strtr($class, array('^' => ''));
    }
    return '[' . strtr($class, array('#' => '\#')) . ']';
}

/**
 * Callback sort function for GLOB_NOSORT
 *
 * Sorts first by the base name, then in reverse by the extension
 */
function php_compat_glob_nosort_helper($a, $b)
{
    $operands = array(array('full' => $a), array('full' => $b));
    foreach ($operands as $k => $v) {
        $v['pos'] = strrpos($v['full'], '.');
        if ($v['pos'] === false) {
            $v['pos'] = strlen($v['full']) - 1;
        }
        $v['slash'] = strrpos($v['full'], DIRECTORY_SEPARATOR);
        if ($v['slash'] === false) {
            $v['slash'] = strlen($v['full']) - 1;
        }
        $operands[$k]['dir'] = substr($v['full'], 0, $v['slash']);
        $operands[$k]['base'] = substr($v['full'], $v['slash'], $v['pos'] - $v['slash']);
        $operands[$k]['ext'] = substr($v['full'], $v['pos'] + 1);
    }
    $dir_cmp = strcmp($operands[0]['dir'], $operands[1]['dir']);
    if ($dir_cmp == 0) {
        $base_cmp = strcmp($operands[0]['base'], $operands[1]['base']);
        if ($base_cmp == 0) {
            $ext_cmp = strcmp($operands[0]['ext'], $operands[1]['ext']);
            return -$ext_cmp;
        }
        return $base_cmp;
    }
    return -$dir_cmp;
}

/**
 * Callback sort function for GLOB_BRACE
 *
 * Each argument should be an array where the first element is the
 * file path, and the second is its rank. The rank is the number of
 * alternatives between this match and the beginning of the brace.
 */
function php_compat_glob_brace_sort_helper($a, $b)
{
    if ($a[1] == $b[1]) {
        return strcmp($a[0], $b[0]);
    }
    return ($a[1] < $b[1]) ? -1 : 1;
}

/**
 * Callback sort function for (GLOB_BRACE | GLOB_NOSORT)
 *
 * Receives the same arguments php_compat_glob_brace_sort_helper()
 */
function php_compat_glob_brace_nosort_helper($a, $b)
{
    if ($a[1] == $b[1]) {
        $len_a = strlen($a[0]);
        $len_b = strlen($b[0]);
        if ($len_a == $len_b) { 
            return -strcmp($a[0], $b[0]);
        }
        return ($len_a < $len_b) ? -1 : 1;
    }
    return ($a[1] < $b[1]) ? -1 : 1;
}

if (!function_exists('glob')) {
    function glob($pattern, $flags = 0)
    {
        return php_compat_glob($pattern, $flags);
    }
}

?>