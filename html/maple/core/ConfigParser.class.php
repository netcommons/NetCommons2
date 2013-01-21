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
 * @package     Maple.filter.DIContainer2
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 */

/**
 * 設定ファイルをパースするクラス
 * key[subkey][index] = value 等の配列構造にも対応している
 *
 * ファイルをパースしながらシーケンシャルに何らかの設定を行う用途を想定しているので、
 * 設定内容そのものは保存しない
 * 必要ならイベントを利用して適宜サブクラスで実装する
 *
 * @author Hawk
 * @package maple.core
 * @access public
 */
class ConfigParser
{
    /**
     * INIファイルの解析に用いる関数
     * parse_ini_file (PHP4) or ConfigParser::read_ini_file (PHP5)
     *
     * @var callback
     */
    var $_parse_function;

    /**
     * Constructor
     *
     *
     */
    function ConfigParser()
    {
        if(version_compare(phpversion(), "5.0.0", ">=")){
            $this->_parse_function = array(&$this, 'read_ini_file');
        } else {
            $this->_parse_function = 'parse_ini_file';
        }
    }

    /**
     * @access public
     * @param string filename
     * @return bool
     */
    function start($filename)
    {
        if(!($root = @call_user_func($this->_parse_function, $filename, true))) {
            $this->doError($filename);
            return false;
        }
        $this->doStart($filename);

        $this->doRootBeforeParse($filename, $root);

        foreach($root as $sectionName => $values) {
            if(!is_array($values)) {
                /*
                    セクションを持たない値
                */
                $this->doProperty($sectionName, $values);
                continue;
            }

            $this->doSectionBeforeParse($sectionName, $values);

            $values = $this->_resolveArray($sectionName, $values);

            $this->doSectionAfterParse($sectionName, $values);
        }

        $this->doEnd($filename);
        return true;
    }


    /**
     * keyの中に表現された配列構造を実体化する
     *
     * @access private
     * @param string   section name
     * @param Array    array('varname[key]' => value)
     * @return Array   array(varname => array(key => value))
     */
    function _resolveArray($sectionName, $arr)
    {
        $result = array();
        foreach($arr as $key => $value) {
            /* MUST NOT USE $value (use &$arr[$key]) */
            $this->doValueBeforeParse($sectionName, $arr, $key, $value);

            if(!preg_match('/^([^\[]+)\[(.+)\]$/', $key, $matches)) {
                $result[$key] =& $arr[$key];
                continue;
            }
            $parentKey = $matches[1];
            $keys = explode('][', $matches[2]);

            array_unshift($keys, $parentKey);
            $lastKey = array_pop($keys);

            $tmp =& $result;
            foreach($keys as $elm) {
                $elm = trim($elm, "'\"");
                if(!isset($tmp[$elm]) || !is_array($tmp[$elm])) {
                    $tmp[$elm] = array();
                }
                $tmp =& $tmp[$elm];
            }
            $tmp[trim($lastKey, "'\"")] =& $arr[$key];
        }
        return $result;
    }

    /* --- definitions of Event ---  */

    /**
     * パースが開始されたときに発生する
     *
     * @access public
     * @param string    ファイル名
     */
    function doStart($filename)
    {

    }

    /**
     * 設定ツリー全体に対する処理の機会を提供する
     * 将来の実装によっては doStart の直後に発生するとは限らないので注意
     *
     * @access public
     * @param string     ファイル名
     * @param Array        ルート
     */
    function doRootBeforeParse($filename, &$root)
    {

    }

    /**
     * セクションに属さない値をパースするときに発生
     *
     * @access public
     * @param string
     * @param string
     */
    function doProperty($key, $value)
    {

    }

    /**
     * セクションのパース開始時に発生
     *
     * @access public
     * @param string    セクション名
     * @param Array        セクションに含まれる値の配列
     */
    function doSectionBeforeParse(&$sectionName, &$values)
    {

    }

    /**
     * 各値のパース開始時に発生。この段階ではキーは 'key[subkey]' などのまま
     * なお 値 をオブジェクトなどの参照で置換する場合、$value を使うことは出来ない
     * $values[$key] =& $obj
     * などのようにする
     *
     * @access public
     * @param string     セクション名
     * @param Array        セクションに含まれる値の配列
     * @param string     解析中のキー
     * @param string    解析中の値
     */
    function doValueBeforeParse($sectionName, &$values, &$key, &$value)
    {

    }

    /**
     * セクションのパース終了時に発生
     * 単にファイルから値を読み込みたいだけの場合、このイベントをフックするのが一番手っ取り早い
     *
     * @access public
     * @param string    セクション名
     * @param Array        含まれる値の配列
     */
    function doSectionAfterParse($sectionName, $values)
    {

    }

    /**
     * パース正常終了時に発生
     *
     * @access public
     * @param string    ファイル名
     */
    function doEnd($filename)
    {

    }

    /**
     * エラー発生時に発生
     *
     * @access public
     * @param string    ファイル名
     */
    function doError($filename)
    {

    }

    /**
     * PHP5用 parse_ini_fileの代替
     *
     *
     */
    function read_ini_file($filename, $process_sections = false)
    {
            if(!$lines = file($filename)) {
                return false;
            }

            $result= array();
            $crr   =& $result;

            $sec_reg = '/^\[(.+)\]$/';
            $val_reg = '/^(.+?)\s*=\s*("?.*?"?)\s*$/';
            $qval_reg= '/^"(.*)"/U';
            $ini_bool = array(
                    'yes' => '1', 'on' => '1', 'true' => '1',
                    'no'  => '',  'off'=> '',  'false'=> '', 'none' => ''
            );

            foreach($lines as $line) {
                    if($line{0}==';' || ($line = trim($line)) == "") {
                            continue;
                    } elseif(preg_match($val_reg, $line, $m)) {
                            $crr[$m[1]] = preg_match($qval_reg, ($v=$m[2]), $_m) ? $_m[1] :
                                    (isset($ini_bool[$l=strtolower($v)]) ? $ini_bool[$l] :
                                            (defined($v) ? constant($v) :
                                             trim(preg_replace('/;.*/', '', $v))));
                    } elseif($process_sections && preg_match($sec_reg, $line, $m)) {
                            $sec = $m[1];
                            $result[$sec] = array();
                            $crr =& $result[$sec];
                    }
            }
            return $result;
    }


}
?>
