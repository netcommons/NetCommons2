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
 * @package     Maple
 * @author      Kazunobu Ichihashi <bobchin_ryu@bb.excite.co.jp>
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: SimpleView4Maple.class.php,v 1.1 2006/10/13 08:50:12 Ryuji.M Exp $
 */

require_once MAPLE_DIR .'/core/BeanUtils.class.php';
require_once MAPLE_DIR .'/core/SimpleView.class.php';

/**
 * PHPの書式をテンプレートでそのまま利用する簡易テンプレートクラス
 *
 * @package     Maple
 * @author      Kazunobu Ichihashi <bobchin_ryu@bb.excite.co.jp>
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.2.0
 */
class SimpleView4Maple extends SimpleView
{
    /**
     * @var array htmlspecialcharsを適用後のアクションのプロパティ
     */
    var $_actionProps;

    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.2.0
     */
    function SimpleView4Maple()
    {
        parent::SimpleView();
    }

    /**
     * Simple4Mapleクラスの唯一のインスタンスを返却
     *
     * @return  Object  Simple4Mapleクラスのインスタンス
     * @access  public
     * @since   3.2.0
     */
    function &getInstance()
    {
        static $instance;
        if ($instance === null) {
            $instance = new SimpleView4Maple();
        }
        return $instance;
    }

    /**
     * 唯一のインスタンスに対して設定を行う
     * 
     * @static
     * @param  array    $opts
     * @since  3.2.0
     */
    function setOptions($opts)
    {
        $instance =& SimpleView4Maple::getInstance();

        if(isset($opts['aliasFuncName'])) {
            $instance->setAliasFuncName($opts['aliasFuncName']);
            unset($opts['aliasFuncName']);
        }

        foreach($opts as $attr => $value) {
            if(array_key_exists($attr, $instance)) {
                $instance->$attr = $value;
            }
        }
    }

    /**
     * Actionをセットする
     *
     * @param   Object  $action Actionのインスタンス
     * @access  public
     * @since   3.2.0
     */
    function setAction(&$action)
    {
        $this->_actionProps = array();
        $util =& new BeanUtils;
        $util->toArray(get_object_vars($action), $this->_actionProps, $action);
        
        $this->assign('h', $this->_actionProps);
        $this->assignByRef('action', $action);
    }
    
    /**
     * ErrorListをセットする
     *
     * @param   Object  $errorList  ErrorListのインスタンス
     * @access  public
     * @since   3.2.0
     */
    function setErrorList(&$errorList)
    {
        $this->assignByRef('errorList', $errorList);
    }
    
    /**
     * Tokenをセットする
     *
     * @param   Object  $token  Tokenのインスタンス
     * @access  public
     * @since   3.2.0
     */
    function setToken(&$token)
    {
        $this->assignByRef('token', $token);
    }
    
    /**
     * Sessionをセットする
     *
     * @param   Object  $session    Sessionのインスタンス
     * @access  public
     * @since   3.2.0
     */
    function setSession(&$session)
    {
        $this->assignByRef('session', $session);
    }

    /**
     * ScriptNameをセットする
     *
     * @param   string  $scriptName ScriptName
     * @access  public
     * @since   3.2.0
     */
    function setScriptName($scriptName)
    {
        $this->assign('scriptName', $scriptName);
    }

    /**
     * htmlspecialchars関数のエイリアス関数名を指定する
     * 
     * @param string $name エイリアス名
     * @access  public
     * @since   3.2.0
     */
    function setAliasFuncName($name)
    {
        static $alias;
        if ($alias !== null) {
            return;
        }
        if (function_exists($name)) {
            $_log =& LogFactory::getLog();
            $_log->error("既に関数が存在します($name)", __CLASS__.'#'.__FUNCTION__);
            return;
        }
        
        $src = <<<SRC
            function $name(\$str, \$ref = true)
            {
                \$instance =& SimpleView4Maple::getInstance();
                return \$instance->h(\$str, \$ref);
            }
SRC;
        eval($src);
        $alias = $name;
    }

    /**
     * htmlspecialchars関数のエイリアス関数の実体
     * 
     * @param string $str サニタイズする文字列
     * @param boolean $ref アクションのプロパティを参照するかどうか
     * @return string サニタイズ後の文字列
     * @access  public
     * @since   3.2.0
     */    
    function h($str, $ref = true)
    {
        $prop =& $this->_actionProps;
        if ($ref) {
            if (isset($prop[$str])) {
                return $prop[$str];
            } else {
                return htmlspecialchars($str, ENT_QUOTES);
            }
        } else {
            return htmlspecialchars($str, ENT_QUOTES);
        }
    }

}

?>
