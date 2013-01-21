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
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: ConverterManager.class.php,v 1.3 2006/10/27 08:47:10 Ryuji.M Exp $
 */

require_once MAPLE_DIR .'/converter/Converter.interface.php';

/**
 * Converterを管理するクラス
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class ConverterManager
{
    /**
     * @var Converterを保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_list;

    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function ConverterManager()
    {
        $this->_list = array();
    }

    /**
     * Convertを行う
     *
     * @param   array   $params Convertする条件が入った配列
     * @access  public
     * @since   3.0.0
     */
    function execute($params)
    {
        if (!is_array($params) || (count($params) < 1)) {
            return true;
        }

        // ConverterのListを生成
        $this->_buildConverterList($params);

        //
        // Convertを実行
        //
        $this->_convert($params);

        return true;
    }

    /**
     * ConverterのListを生成
     *
     * @param   array   $params Convertする条件が入った配列
     * @access  private
     * @since   3.0.0
     */
    function _buildConverterList($params)
    {
        $log =& LogFactory::getLog();

        foreach ($params as $key => $value) {
            $key   = preg_replace("/\s+/", "", $key);
            $value = preg_replace("/\s+/", "", $value);

            if ($key == "") {
                $log->error("Converterの指定が不正です", "ConverterManager#_buildConverterList");
                continue;
            }

            //
            // $key は attribute.name のパターン
            //
            $keyArray = explode(".", $key);
            if (count($keyArray) != 2) {
                break;
            }
            $attribute = $keyArray[0];     // 属性の名前
            $name      = $keyArray[1];     // Converterの名前 
            //$name      = strtolower($name);

            $className = "Converter_" . ucfirst($name);
            $filename  = CONVERTER_DIR . "/${className}.class.php";

            if (!(@include_once $filename) or !class_exists($className)) {
                $log->error("存在していないConverterが指定されています(${name})", "ConverterManager#_buildConverterList");
                continue;
            }

            //
            // 既に同名のConverterが追加されていたら何もしない
            //
            if (isset($this->_list[$name]) && is_object($this->_list[$name])) {
                continue;
            }

            //
            // オブジェクトの生成に失敗していたらエラー
            //
            
            $converter =& new $className();

            if (!is_object($converter)) {
                $log->error("Convererの生成に失敗しました(${name})", "ConverterManager#_buildConverterList");
                return false;
            }

            $this->_list[$name] =& $converter;
        }
    }

    /**
     * Converterを実行
     *
     * @param   array   $params Convertする条件が入った配列
     * @access  private
     * @since   3.0.0
     */
    function _convert($params)
    {
        $log =& LogFactory::getLog();

        foreach ($params as $key => $value) {
            $key   = preg_replace("/\s+/", "", $key);
            $value = preg_replace("/\s+/", "", $value);

            if ($key == "") {
                $log->error("Converterの指定が不正です", "ConverterManager#_convert");
                continue;
            }

            //
            // $key は attribute.name のパターン
            //
            $keyArray = explode(".", $key);
            if (count($keyArray) != 2) {
                break;
            }
            $attribute = $keyArray[0];     // 属性の名前
            $name      = $keyArray[1];     // Converterの名前 
            //$name      = strtolower($name);

            //
            // $value にはConvert後の値を入れる変数名がセットできる
            //
            $newAttribute = $value;

            //
            // Converterを取得
            //
            $converter =& $this->_list[$name];

            if (!is_object($converter)) {
                continue;
            }

            //
            // attributeに * が指定されている場合は
            // リクエストパラメータ全てが変換対象となる
            //
            $container =& DIContainerFactory::getContainer();
            $request =& $container->getComponent("Request");

            if ($attribute == '*') {
                $attribute = join(",", array_keys($request->getParameters()));
            }

            if (preg_match("/,/", $attribute)) {
                $attributes = array();
                foreach (explode(",", $attribute) as $param) {
                    if ($param) {
                       $attributes[$param] = $request->getParameter($param);
                    }
                }
            } else {
                $attributes = $request->getParameter($attribute);
            }

            //
            // Converterを適用
            //
            $result = $converter->convert($attributes);

            if ($newAttribute != "") {
                $request->setParameter($newAttribute, $result);
            } else {
                if (is_array($attributes)) {
                    foreach ($result as $key => $value) {
                        if ($key) {
                            $request->setParameter($key, $value);
                        }
                    }
                } else {
                    $request->setParameter($attribute, $result);
                }
            }
        }
    }
}
?>
