<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Maple - PHP Web Application Framework
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package     Maple.core
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: CmdArgs.class.php,v 1.1 2006/10/13 08:50:13 Ryuji.M Exp $
 */

require_once('Console/Getopt.php');

/**
 * CmdArgsクラス
 * 
 * Console_Getoptに対するWrapper
 * short-optionとlong-optionをまとめたり
 * 
 * @since 06/07/16 14:46
 * @author Hawk
 */
class CmdArgs
{
    var $_errorHandler;

    /**
     * コマンドラインパラメータをDTOに変換
     * 
     * @since 06/07/16 20:58
     * @param  array    $attrs
     * @param  array    $args
     * @return Object or null
     */
    function &args2Dto($attrs, $args, $includeScript=false)
    {
        $result = null;
        $getopt =& new Console_Getopt();
        $method = $includeScript ? 'getopt' : 'getopt2';
        
        $getoptArgs = $this->buildGetoptArgs($attrs);
        $r = $getopt->$method($args, $getoptArgs['short'], $getoptArgs['long']);

        if(is_a($r, 'PEAR_Error')) {
            $this->_error($r->getMessage());
            return $result;
        }

        $result =& $this->buildDto($attrs, $r);
        return $result;
    }
    
    /**
     * 
     * @since 06/07/16 16:21
     * @param  String    $attrs
     * @return String
     */
    function buildGetoptArgs($attrs)
    {
        $longOpts  = array();
        $shortOpts = "";
        
        foreach($attrs as $k => $v) {
            if(preg_match('/(.):([-a-zA-Z0-9]+)/', $k, $m)) {
                $short= $m[1];
                $long = $m[2];

                if($v == 'optional') {
                    $short.='::';
                    $long .='==';
                } elseif($v == 'required' || strlen($v) > 0) {
                    $short.=':';
                    $long .='=';
                }
                
                $shortOpts .= $short;
                $longOpts[] = $long;
            }
        }

        return array('short' => $shortOpts, 'long' => $longOpts);
    }

    /**
     * 余ったパラメータはargsという名前の配列にまとめる
     * 
     * @since 06/07/16 14:56
     * @param  array    $attrs
     * @param  array    $getoptResult
     * @return object or null
     */
    function &buildDto($attrs, $getoptResult)
    {
        $ret = new stdClass;
        $normalArgs = $getoptResult[1];
        $namedArgs  = $this->_opt2Assoc($getoptResult[0]);

        $result = true;

        foreach($attrs as $k => $type) {
            if(preg_match('/(.):([-a-zA-Z0-9]+)/', $k, $m)) {
                $short= $m[1];
                $long = $m[2];

                $propName = $this->_camelize($long);
                $ret->$propName = $this->_getNamedArgValue($namedArgs, $short, $long, $type);
                continue;
            }
            
            if(count($normalArgs) > 0) {
                $ret->$k = array_shift($normalArgs);
                continue;
            }
            if($type == 'optional') {
                $ret->$k = null;
                continue;
            }
            if($type == 'required') {
                $result = false;
                $this->_error("{$k} is not specified");
            } else {
                $ret->$k = $type;
            }
        }
        $ret->args = $normalArgs;

        if(!$result) {
            $ret = null;
        }
        return $ret;
    }

    /**
     * 
     * @since 06/07/16 16:42
     * @param  String    $hy
     * @return String
     */
    function _camelize($str, $delimiter='-')
    {
        if(strlen($str) == 0) {
            return $str;
        }
        return $str{0} . substr(str_replace(
            ' ', '',
            ucwords(
                str_replace($delimiter, ' ', $str))), 1);

    }

    /**
     * 
     * @since 06/07/16 16:37
     * @param  arrayg    $namedArgs
     * @param  String    $short
     * @param  String    $long
     * @param  String    $type
     * @return mixed
     */
    function _getNamedArgValue($namedArgs, $short, $long, $type)
    {
        $value = $this->_getValue($namedArgs, $short, $long);
        
        switch($type) {
          case "required":
            break;

          case "":
            return $this->_isSpecified($namedArgs, $short, $long);

          case "optional":
            if($value == null) {
                return $this->_isSpecified($namedArgs, $short, $long);
            }
            break;
            
          default:
            if($value == null) {
                return $type;
            }
        }
        return $value;
    }

    /**
     * 
     * @since 06/07/16 16:17
     * @param  array    $opts
     * @return array
     */
    function _opt2Assoc($opts)
    {
        $ret = array();
        foreach($opts as $entry) {
            $ret[$entry[0]] = $entry[1];
        }
        return $ret;
    }

    /**
     * 
     * @access private
     * @since 06/07/16 16:20
     * @param  array    $namedArgs
     * @param  String    $short
     * @param  String    $long
     * @return mixed
     */
    function _getValue($namedArgs, $short, $long)
    {
        return isset($namedArgs[$short]) ? $namedArgs[$short]
        : (isset($namedArgs["--{$long}"]) ? $namedArgs["--{$long}"] : null);
    }

    /**
     * 
     * @access private
     * @since 06/07/16 16:20
     * @param  array    $namedArgs
     * @param  String    $short
     * @param  String    $long
     * @return mixed
     */
    function _isSpecified($namedArgs, $short, $long)
    {
        return array_key_exists($short, $namedArgs) ||
               array_key_exists("--".$long, $namedArgs);
    }

    function setErrorHandler($_handler)
    {
        $this->_errorHandler = $_handler;
    }
    
    function _error($msg)
    {
        if(is_callable($this->_errorHandler)) {
            call_user_func($this->_errorHandler, $msg);
        }
    }
    
}

?>
