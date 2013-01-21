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
 * @package     Maple.filter
 * @author      Kazunobu Ichihashi <bobchin_ryu@bb.excite.co.jp>
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version     CVS: $Id: Filter_Debug.class.php,v 1.11 2008/02/28 11:15:59 Ryuji.M Exp $
 */

require_once MAPLE_DIR.'/dBug/DumpHelper.class.php';

/**
 * デバッグ情報を表示するFilter
 *
 * @package     Maple.filter
 * @author      Kazunobu Ichihashi <bobchin_ryu@bb.excite.co.jp>
 * @author      TAKAHASHI Kunihiko <kunit@kunit.jp>
 * @author      Hawk <scholar@hawklab.jp>
 * @copyright   2004-2006 The Maple Project
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @access      public
 * @since       3.1.0
 */
class Filter_Debug extends Filter
{
    /** @var array 表示する内容  */
    var $_debugs = array();

    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.1.0
     */
    function Filter_Debug()
    {
        parent::Filter();
    }

    /**
     * デバッグ情報を表示する
     *
     * @access  public
     * @since   3.1.0
     */
    function execute()
    {
    	$log =& LogFactory::getLog();
    	$container =& DIContainerFactory::getContainer();  	
    	//MAPLEデバッグ
		if (defined("MAPLE_DEBUG")) {
			if(MAPLE_DEBUG)
				$maple_debug = _ON;
			else
				$maple_debug = _OFF;
		} else {
			$session =& $container->getComponent("Session");
			if($session) {
				$maple_debug = $session->getParameter("_maple_debug");
			} else {
				$maple_debug = _OFF;
			}
		}
		if($maple_debug) {
			$this->_preFilter();
	        $log->trace("Filter_Debugの前処理が実行されました", "Filter_Debug#execute");
		}    
	        
	    $filterChain =& $container->getComponent("FilterChain");
	    $filterChain->execute();
		
		if($maple_debug) {
	        // Debugはビューを表示した後のみ出力する必要あり
	        // ResponseオブジェクトのResultに値があるときはビューを出力するとみなす
	        //$response =& $container->getComponent("Response");
	        //$result = $response->getResult();
	        //$contentType = $response->getContentType();
	        //if ($result != '' && ($contentType == '' || strrchr(strtolower($contentType),'text/html'))) {
	            $this->_postFilter();    
	        //}
	        $log->trace("Filter_Debugの後処理が実行されました", "Filter_Debug#execute");
		}
    }
    
    /**
     * プリフィルター
     */
    function _preFilter()
    {
        // 何もしない
    }
    
    /**
     * ポストフィルター
     */
    function _postFilter()
    {
        $NO = 'なし';

        $var = (isset($_POST) && (0 < count($_POST)))? $_POST: $NO;
        $this->addParam(_MAPLE_DEBUG_REQUEST.'($_POST)', $var);

        $var = (isset($_GET) && (0 < count($_GET)))? $_GET: $NO;
        $this->addParam(_MAPLE_DEBUG_REQUEST.'($_GET)', htmlspecialchars($var, ENT_QUOTES));

        $var = (isset($_FILES) && (0 < count($_FILES)))? $_FILES: $NO;
        $this->addParam(_MAPLE_DEBUG_REQUEST.'($_FILES)', htmlspecialchars($var, ENT_QUOTES));

        $var = (isset($_SESSION) && (0 < count($_SESSION)))? $_SESSION: $NO;
        $this->addParam(_MAPLE_DEBUG_REQUEST.'($_SESSION)', htmlspecialchars($var, ENT_QUOTES));

        $container =& DIContainerFactory::getContainer();

        $actionChain =& $container->getComponent("ActionChain");
        $dumpHelper =& new DumpHelper();
        foreach ($actionChain->_list as $name => $action) {
        	$result = $dumpHelper->removeCircularReference($action);
        	$this->addParam(_MAPLE_DEBUG_ACTION."({$name})", $result);
            $this->addParam(_MAPLE_DEBUG_ERRORLIST."({$name})", $actionChain->_errorList[$name]);
        }
		
        $result = $dumpHelper->removeCircularReference($container->_components);
        $this->addParam(_MAPLE_DEBUG_DICON, $result);

        $this->_printDebug();
    }

    /**
     * デバッグ情報を追加する
     * 
     * @param string $title タイトル
     * @param mixed $var デバッグ対象の変数
     */
    function addParam($title, $var)
    {
    	//if (OUTPUT_CODE != INTERNAL_CODE) {
        //   $title = mb_convert_encoding($title, OUTPUT_CODE, INTERNAL_CODE);
        //}
        //$this->_debugs[$title] = $var;
        
        $title = $this->_recursiveEncoding($title);
        $var = $this->_recursiveEncoding($var);
        $this->_debugs[$title] = $var;
    }
    
    /**
     * 再帰的にエンコードを変更する
     * 
     * @param mixed $var 出力する内容
     */
    function _recursiveEncoding($var)
    {
        // オブジェクトの場合
        if (is_object($var)) {
            foreach (array_keys(get_object_vars($var)) as $prop) {
                $var->$prop = $this->_recursiveEncoding($var->$prop);
            }
            $result = $var;
        
        // 配列の場合
        } else if (is_array($var)) {
            $result = array();
            foreach (array_keys($var) as $k) {
                $newkey = $this->_recursiveEncoding($k);
                $result[$newkey] = $this->_recursiveEncoding($var[$k]);
            }
        
        // リソースの場合
        } else if (is_resource($var)) {
            $result = $this->_detectEncoding('&resource(' . get_resource_type($var) . ')');
        
        // 文字列、数値、論理型、NULLの場合
        } else {
            if (!is_string($var)) {
                $var = (string)$var;
            }
            $result = $this->_detectEncoding($var);
        }
        return $result;
    }
    
    /**
     * 指定した変数のエンコードを判断して出力エンコードに変更する
     * 
     * @param string $var 出力文字列
     */
    function _detectEncoding($var)
    {
        $nowencode = mb_detect_encoding($var);
        if ($nowencode != OUTPUT_CODE) {
            $var = mb_convert_encoding($var, OUTPUT_CODE, $nowencode);
        }
        return $var;
    }
    
    /**
     * Javascript用のエスケープ処理
     * 
     * @param string $src javascriptソース
     */
    function _escapeJavascript($src)
    {
        return strtr($src, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
    }
    
    /**
     * デバッグを表示する
     */
    function _printDebug()
    {
        ob_start();
        require_once MAPLE_DIR.'/dBug/dBug.php';
        new dBug($this->_debugs, 'array');
        $debug = ob_get_contents();
        ob_end_clean();

        // 表示するHTMLの作成
        $html = <<<HTML
<HTML>
<HEAD>
<TITLE>Maple Debug Console</TITLE>
</HEAD>
<BODY bgcolor=#ffffff>
<table border=0 width=100%><tr bgcolor=#cccccc><th colspan=2>Maple Debug Console</th></tr></table>
{$debug}
</BODY>
</HTML>
HTML;

        // Javascript出力用にエスケープ処理
        $html = $this->_escapeJavascript($html);

        // Javascript出力
        $JS = <<<JS
<SCRIPT language=javascript>
    var title = 'Console';
    _smarty_console = window.open("",title.value,"width=680,height=600,resizable,scrollbars=yes");
    _smarty_console.document.write("$html");
    _smarty_console.document.close();
</SCRIPT>
JS;
        print $JS;
    }
}

?>