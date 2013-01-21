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
 * @version     CVS: $Id: GlobalConfig.class.php,v 1.1 2006/10/13 08:50:12 Ryuji.M Exp $
 */

/**
 * GlobalConfig
 *
 * プログラム全体の設定情報を扱うためのクラス
 * PHPの定数を透過的に扱うことが出来る
 *
 * preferConstantによってPHP定数の優先順位が変わる
 * trueにすると
 *   PHP定数 > setValueで設定された値 > importSectionsでまとめて読み込んだ値
 * falseにすると
 *   setValueで設定された値 > importSectionsでまとめて読み込んだ値 > PHP定数
 *
 * @package     Maple.core
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.2.0
 */
class GlobalConfig
{
    /**
     * @var  array  importSectionsで読み込まれた設定値が保存される
     */
    var $_values = array();

    /**
     * @var  String  setValueで設定された値が保存される
     */
    var $_overwriteValues = array();

    /**
     * @var  String  importSectionsで読み込んだ値を、セクション単位でまとめるための配列
     */
    var $_sections = array();

    /**
     * @var  String  定数としてエクスポート可能な名前を表す正規表現
     */
    var $_exportableConstPattern = '/^[A-Z_][A-Z0-9_]*$/';

    /**
     * @var  String  定数を他の値に対して優先するか否か
     */
    var $_preferConstant = false;

    /**
     * コンストラクタ
     *
     * @access public
     * @param bool $preferConstant  [optional] if true, constants have priority over
     *                                         any other values.
     */
    function GlobalConfig($preferConstant=false)
    {
        $this->_preferConstant = $preferConstant;
    }

    /**
     * 定数を優先するか
     *
     * @access public
     * @param  boolean    $bool
     */
    function setPreferConstant($bool)
    {
        $this->_preferConstant = $bool;
    }

    /**
     * 値がsetValueメソッドによって上書きされているか
     * どうかを調べる
     *
     * @access private
     * @param  String    $key
     * @return boolean
     * @see    setValue()
     */
    function _isOverwritten($key)
    {
        return array_key_exists($key, $this->_overwriteValues);
    }

    /**
     * 定数が利用可能かどうかを調べる
     *
     * @access private
     * @param  String    $key
     * @return boolean
     */
    function _canUseConstant($key)
    {
        return defined($key);
    }

    /**
     * 配列を一括して取り込む
     * 自動的にupdateValuesを呼び出し、保持されている値を更新する
     *
     * @access public
     * @param  array    $arr
     */
    function importSections($arr)
    {
        $this->_sections = $arr + $this->_sections;
        $this->updateValues();
    }


    /**
     * 保持されている値を更新する
     *
     * @access public
     */
    function updateValues()
    {
        foreach($this->_sections as $sec => $arr) {
            $prefix = $this->getValue($sec, "");

            foreach($arr as $k => $v) {
                $this->_values[$k] = $prefix . $v;
            }
        }
    }

    /**
     * 設定値を得る
     * 定数として宣言されているか、
     * 定数を優先するか、
     * 上書きされているか、
     * 等の要素を考慮して設定値は決まる
     *
     * @access public
     * @param  String    $key
     * @param  mixed    $default
     * @return mixed
     */
    function getValue($key, $default=null)
    {
        if($this->_preferConstant && $this->_canUseConstant($key)) {
            return constant($key);

        } elseif($this->_isOverwritten($key)) {
            return $this->_overwriteValues[$key];

        } elseif(isset($this->_values[$key])) {
            return $this->_values[$key];

        } elseif(!$this->_preferConstant && $this->_canUseConstant($key)) {
            return constant($key);

        }
        return $default;
    }

    /**
     * 値が設定されているかどうかを調べる
     *
     * @access public
     * @param  String    $key
     * @param  boolean    $checkConst [optional] 定数をチェックするか
     * @return boolean
     */
    function hasValue($key, $checkConst=false)
    {
        return ($this->_isOverwritten($key) ||
           isset($this->_values[$key]) ||
           ($checkConst && $this->_canUseConstant($key)));
    }

    /**
     * 値を設定する
     * このメソッドで設定した値は
     * $_overwriteValuesとして独自に管理される
     *
     * @access public
     * @param  mixed    $key
     * @param  mixed    $value
     * @param  boolean  $autoUpdate [optional]
     *                  if ture, call updateValues() after overwriting
     */
    function setValue($key, $value, $autoUpdate=false)
    {
        $this->_overwriteValues[$key] = $value;
        if($autoUpdate) {
            $this->updateValues();
        }
    }

    /**
     * セクション単位で設定値を配列の形で得る
     *
     * @access public
     * @param  String    $sec
     * @return array
     */
    function getSection($sec)
    {
        if(!isset($this->_sections[$sec])) {
            return null;
        }

        $result = array();
        foreach($this->_sections[$sec] as $k => $v) {
            if($this->_isOverwritten($k) ||
               ($this->_preferConstant && $this->_canUseConstant($k))) {
                //上書きされているか、
                //定数として独自に定義されている場合は、
                //セクション内に含めない
                continue;
            } else {
                $result[$k] = $this->getValue($k);
            }
        }
        return $result;
    }

    /**
     * INIファイルから設定を読み込む
     *
     * @access public
     * @param  String    $filename
     * @return boolean
     */
    function loadFromFile($filename)
    {
		if (version_compare(phpversion(), '5.3.0', '>=')) {
			$arr = @parse_ini_file($filename, true, INI_SCANNER_RAW);
		} else {
			$arr = @parse_ini_file($filename, true);
		}
        if(!($arr)) {
            return false;
        }
        $this->importSections($arr);
        return true;
    }

    /**
     * 設定値を定数としてエクスポートする
     *
     * @access public
     * @return String
     */
    function exportConstants()
    {
        $allValues = $this->_overwriteValues + $this->_values;
        foreach($allValues as $k => $v) {
            if(!defined($k) && $this->isConstName($k)) {
                define($k, $v);
            }
        }
    }

    /**
     * 定数としてエクスポート可能な名称か調べる
     *
     * @access public
     * @param  String    $key
     * @return String
     */
    function isConstName($key)
    {
        return preg_match($this->_exportableConstPattern, $key);
    }


    /**
     * ファイルから設定を読み込み、
     * 定数としてのエクスポートまでを行うstaticメソッド
     *
     * @static
     * @access public
     * @return boolean
     */
    function loadConstantsFromFile($filename)
    {
        $config =& new GlobalConfig(true);
        if(!$config->loadFromFile($filename)) {
            return false;
        }
        $config->exportConstants();
    }


}

?>
