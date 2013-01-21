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
 * @version     CVS: $Id: Request.class.php,v 1.3 2006/07/03 01:24:19 Ryuji.M Exp $
 */

/**
 * POST/GETで受け取った値を格納する
 *
 * @package     Maple
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.0.0
 */
class Request
{
    /**
     * @var POT/GETで受け取った値を保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_params;

    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.0.0
     */
    function Request()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $request = $_POST;
        } else {
            $request = $_GET;
        }

        if (get_magic_quotes_gpc()) {
            $request = $this->_stripSlashesDeep($request);
        }

        if (!ini_get("mbstring.encoding_translation") &&
            (INPUT_CODE != INTERNAL_CODE)) {
			mb_convert_encoding($request, INTERNAL_CODE, INPUT_CODE);
             //mb_convert_variables(INTERNAL_CODE, INPUT_CODE, $request);
        }

        $this->_params = $request;
    }

    /**
     * stripslashes() 関数を再帰的に実行する
     *
     * @param   mixed  $value  処理する変数
     * @return  mixed  処理結果
     * @access  private
     * @see     http://www.php.net/manual/ja/function.stripslashes.php#AEN181588
     * @since   3.1.0
     */
    function _stripSlashesDeep($value)
    {
        if (is_array($value)) {
            $value = array_map(array($this, '_stripSlashesDeep'), $value);
        } else {
            $value = stripslashes($value);
        }
        return $value;
    }

    /**
     * REQUEST_METHODの値を返却
     *
     * @return  string  REQUEST_METHODの値
     * @access  public
     * @since   3.1.0
     */
    function getMethod()
    {
        return $_SERVER["REQUEST_METHOD"];
    }

    /**
     * POST/GETの値を返却
     *
     * @param   string  $key    パラメータ名
     * @return  string  パラメータの値
     * @access  public
     * @since   3.0.0
     */
    function getParameter($key)
    {
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        }
    }

    /**
     * POST/GETの値を返却(オブジェクトを返却)
     *
     * @param   string  $key    パラメータ名
     * @return  Object  パラメータの値
     * @access  public
     * @since   3.0.0
     */
    function &getParameterRef($key)
    {
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        }
    }

    /**
     * POST/GETの値をセット
     *
     * @param   string  $key    パラメータ名
     * @param   string  $value  パラメータの値
     * @access  public
     * @since   3.0.0
     */
    function setParameter($key, $value)
    {
        $this->_params[$key] = $value;
    }

    /**
     * POST/GETの値をセット(オブジェクトをセット)
     *
     * @param   string  $key    パラメータ名
     * @param   Object  $value  パラメータの値
     * @access  public
     * @since   3.0.0
     */
    function setParameterRef($key, &$value)
    {
        $this->_params[$key] =& $value;
    }

    /**
     * POST/GETの値を返却(配列で返却)
     *
     * @param   string  $key    パラメータ名
     * @return  string  パラメータの値(配列)
     * @access  public
     * @since   3.0.0
     */
    function getParameters()
    {
        return $this->_params;
    }

    /**
     * アクションを切り分ける
     *
     * フォーム内にsubmitボタンが2つあってきりわけた場合に
     * アクションを切り分け
     * <input type="submit" name="dispatch_A_B_C_D" value="OK">
     *   ⇒ action を A_B_C_D にきりかえ
     * <input type="submit" name="dispatch_E_F_G_H" value="OK">
     *   ⇒ action を E_F_G_H にきりかえ
     *
     * @access  public
     * @since   3.0.0
     */
    function dispatchAction()
    {
        $params = $this->getParameters();

        if (count($params) < 1) {
            return;
        }

        foreach ($params as $key => $value) {
            if (preg_match("/^dispatch_/", $key)) {
                $action = preg_replace("/^dispatch_/", "", $key);
                $action = preg_replace("/_x$/", "", $action);
                $action = preg_replace("/_y$/", "", $action);
                $this->setParameter(ACTION_KEY, $action);
                break;
            }
        }
    }
}
?>
