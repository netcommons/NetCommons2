<?php
//
// $Id: ErrorExtraList.class.php,v 1.11 2008/08/15 04:40:18 snakajima Exp $
//

require_once MAPLE_DIR.'/core/ErrorList.class.php';

/**
 * 各入力フィールドのエラーを保持するクラス
 *
  * @author	Ryuji Masukawa
 */
class ErrorExtraList extends ErrorList
{
	/**
     * コンストラクタ
     *
     * @access  public
     */
    function ErrorExtraList()
    {
        $this->ErrorList();
    }
    
    /**
     * 現在エラーがあるかどうかを返却
     *
     * @return  boolean エラーがあるかどうかの真偽値(true/false)
     * @access  public
     * @since   3.0.0
     */
    function isExists()
    {
        return (count($this->_list) > 0);
    }
    
    /**
     * 登録されているエラー文字列をScript形式(\nを改行コードに使用)の文字列として返却
     * @param 　array(boolean  エラー文字列のみ返すかどうかのフラグ default true)
     * @return  string   エラー文字列のScript形式の文字列→最初のエラーが起こったパラメータ:エラー文字列\nエラー文字列\nエラー文字列\n
     * @access  public
     */
    function getMessagesString($params)
    {
    	$errorKeyFlag = true;
    	foreach ($params as $param) {
    		$errorKeyFlag = $param;
    	}
        $container =& DIContainerFactory::getContainer();
        $actionChain =& $container->getComponent("ActionChain");
        $errorList =& $actionChain->getCurErrorList();

        $err_str = "";
   			
        foreach ($errorList->_list as $key => $value) {
        	foreach ($value as $sub_key => $sub_value) {
        		if($sub_value == "") {
        			continue;
        		}
        		if($err_str == "" && !$errorKeyFlag) {
        			$err_str .= preg_replace("/&amp;/i", '&', htmlspecialchars($key, ENT_QUOTES)).":";
        		} 
        		
        		//if($err_str=="" && !$errorKeyFlag) {
        		//	$arrKey = explode(",",$key);
        		//	$err_str .= $arrKey[0].":";
        		//}
        		
        		$err_str .= preg_replace("/\n/","<br />", htmlspecialchars(preg_replace("/(<br>|<br \/>|<br\/>){1}/","\n", $sub_value), ENT_QUOTES))."<br />";
        		
        		//$err_str .= preg_replace("/\n/","<br />", htmlspecialchars(preg_replace("/[<br>|<br \/>|<br\/>]/","\n", $sub_value), ENT_QUOTES))."<br />";
        		
        		//$err_str .= preg_replace("/&amp;/i", '&', htmlspecialchars(preg_replace("[<br>|<br />|<br/>]","\\n", $sub_value), ENT_QUOTES))."<br />";
        		//$err_str .= preg_replace("\n","<br />",preg_replace("/&amp;/i", '&', htmlspecialchars(preg_replace("[<br>|<br />|<br/>]","\n", $sub_value), ENT_QUOTES)))."<br />";
        		
        	}
        }
        return $err_str;
    }
    
    
    /**
     * エラー文字列を追加
     *
     * @param   string  $key    エラーが発生した項目
     * @param   string  $str    エラー文字列
     * @access  public
     * @since   3.0.0
     */
    function add($key, $value)
    {
        if (!isset($this->_list[$key])) {
            $this->_list[$key] = array();
        }
        // TODO:commonMain に同じような処理がある
        //&npspを空白
		$patterns[] = "/\&nbsp;/u";
		$replacements[] = " ";
		
		//&quot;を"
		$patterns[] = "/\&quot;/u";
		$replacements[] = "\"";
		
		//&lt;を<
		$patterns[] = "/\&lt;/u";
		$replacements[] = "<";
		
		//&gt;を>
		$patterns[] = "/\&gt;/u";
		$replacements[] = ">";
		
		//&acute;を´
		$patterns[] = "/\&acute;/u";
		$replacements[] = "´";
		
		//&cedil;を¸
		$patterns[] = "/\&cedil;/u";
		$replacements[] = "¸";
		
		//&circ;を?
		$patterns[] = "/\&circ;/u";
		$replacements[] = "?";
		
		//&lsquo;を‘
		$patterns[] = "/\&lsquo;/u";
		$replacements[] = "‘";
		
		//&rsquo;を’
		$patterns[] = "/\&rsquo;/u";
		$replacements[] = "’";
		
		//&ldquo;を“
		$patterns[] = "/\&ldquo;/u";
		$replacements[] = "“";
		
		//&rdquo;を”
		$patterns[] = "/\&rdquo;/u";
		$replacements[] = "”";

		//&amp;を&
		$patterns[] = "/\&amp;/u";
		$replacements[] = "&";
		
		//&amp;を&
		$patterns[] = "/\&amp;/u";
		$replacements[] = "&";

		//&apos;を'
		$patterns[] = "/\&apos;/u";
		$replacements[] = "'";

		//&#039;を'
		$patterns[] = "/\&#039;/u";
		$replacements[] = "'";
		
        $this->_list[$key][] = preg_replace($patterns, $replacements, $value);
    }
}
?>
