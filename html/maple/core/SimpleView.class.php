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
 * @version     CVS: $Id: SimpleView.class.php,v 1.3 2006/12/01 08:20:01 Ryuji.M Exp $
 */

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
class SimpleView
{
    /**
     * @var array テンプレートに割り当てる値のリスト
     */
    var $_assigns;

    /**
     * @var  String  $templateDir  
     */
    var $templateDir;

    /**
     * @var  String  $_templateEncoding  
     */
    var $templateEncoding;

    /**
     * @var  String  $_outputEncoding  
     */
    var $outputEncoding;
    
    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.2.0
     */
    function SimpleView()
    {
        $this->_assigns = array();
        $this->templateDir = defined('VIEW_TEMPLATE_DIR') ? VIEW_TEMPLATE_DIR : TEMPLATE_DIR;
        
        $this->templateEncoding = INTERNAL_CODE;
        $this->outputEncoding   = OUTPUT_CODE;
    }
    
    /**
     * テンプレートに値を割り当てる
     * 
     * @param string key テンプレートでアクセスする際に使用するキー名
     * @param mixed $value 値
     * @access  public
     * @since   3.2.0
     */
    function assign($key, $value)
    {
        $this->_assigns[$key] = $value;
    }
    
    /**
     * 参照でテンプレートに値を割り当てる
     * 
     * @param string key テンプレートでアクセスする際に使用するキー名
     * @param mixed $value 値
     * @access  public
     * @since   3.2.0
     */
    function assignByRef($key, &$value)
    {
        $this->_assigns[$key] =& $value;
    }
    
    /**
     * テンプレートファイルのフルパスを設定する
     * 
     * @param  String    $template
     */
    function setTemplate($templateDir)
    {
        $this->templateDir = $templateDir;
    }

    /**
     * テンプレートファイルのフルパスを得る
     * 
     * @param  String    $template
     */
    function getTemplate($template)
    {
        return $this->templateDir . $template;
    }
    
    /**
     * テンプレートに値を割り当てた結果を取得する
     * 
     * @param string $template テンプレートファイル名
     * @return string テンプレートの内容
     * @access  public
     * @since   3.2.0
     */
    function fetch($template)
    {
        $template = $this->getTemplate($template);
        if (!file_exists($template)) {
            $_log =& LogFactory::getLog();
            $_log->error(
                "テンプレートファイルがみつかりません($template)",
                __CLASS__.'#'.__FUNCTION__);
            exit;
        }

        extract($this->_assigns);

        ob_start();
        include $template;
        $result = ob_get_contents();
        ob_end_clean();
  
        $result = $this->_convertEncoding($result);
        return $result;
    }
    
    /**
     * テンプレートに値を割り当てた結果を出力する
     * 
     * @param string $template テンプレートファイル名
     * @return string テンプレートの内容
     * @access  public
     * @since   3.2.0
     */
    function display($template)
    {
        $buf = $this->fetch($template);
        print $buf;
        return $buf;
    }

    /**
     * エンコードする
     * 
     * @param string $string エンコードする文字列
     * @return strign エンコード後の文字列
     * @access  public
     * @since   3.2.0
     */    
    function _convertEncoding($string)
    {
        if ($this->outputEncoding != $this->templateEncoding) {
            $string = mb_convert_encoding(
                $string,
                $this->outputEncoding,
                $this->templateEncoding);
        }
        return $string;
    }
}

?>
