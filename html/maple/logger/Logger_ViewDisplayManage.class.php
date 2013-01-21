<?php
//
// Authors: Ryuji Masukawa
//
// $Id: Logger_ViewDisplayManage.class.php,v 1.13 2008/07/07 05:20:34 Ryuji.M Exp $
//

/**
 * ファイルに出力するLogger
 *
 * @author	Ryuji Masukawa
 **/
class Logger_ViewDisplayManage extends Logger {

	var $_instance;

	function Logger_ViewDisplayManage() {
		$this->_instance =& Logger_ViewDisplay::getInstance();
	}
	
	/**
	 * fatalレベル以上のログを出力
	 *
	 * @param	string	$message	エラーメッセージ
	 * @access	public
	 **/
	function sql_trace($message, $caller = null) {
		$this->_instance->output(LEVEL_SQL, $message, $caller);
	}
	
	
	/**
	 * fatalレベル以上のログを出力
	 *
	 * @param	string	$message	エラーメッセージ
	 * @access	public
	 **/
	function fatal($message, $caller = null) {
		$this->_instance->output(LEVEL_FATAL, $message, $caller);
	}

	/**
	 * errorレベル以上のログを出力
	 *
	 * @param	string	$message	エラーメッセージ
	 * @access	public
	 **/
	function error($message, $caller = null) {
		$this->_instance->output(LEVEL_ERROR, $message, $caller);
	}

	/**
	 * warnレベル以上のログを出力
	 *
	 * @param	string	$message	エラーメッセージ
	 * @access	public
	 **/
	function warn($message, $caller = null) {
		$this->_instance->output(LEVEL_WARN, $message, $caller);
	}

	/**
	 * infoレベル以上のログを出力
	 *
	 * @param	string	$message	エラーメッセージ
	 * @access	public
	 **/
	function info($message, $caller = null) {
		$this->_instance->output(LEVEL_INFO, $message, $caller);
	}

	/**
	 * debugレベル以上のログを出力
	 *
	 * @param	string	$message	エラーメッセージ
	 * @access	public
	 **/
	function debug($message, $caller = null) {
		$this->_instance->output(LEVEL_DEBUG, $message, $caller);
	}

	/**
	 * traceレベル以上のログを出力
	 *
	 * @param	string	$message	エラーメッセージ
	 * @access	public
	 **/
	function trace($message, $caller = null) {
		$this->_instance->output(LEVEL_TRACE, $message, $caller);
	}
}

class Logger_ViewDisplay {
	
	/**
	 * エラーリスト
	 * 
	 * @var array 
	 * @access private 
	 */
	var $_errors = array();
	
	//var $_container = null;
	
	/**
	 * fatal error (E_USER_ERROR=256)
	 *
	 * @var boolean
	 * @access private
	 */
	var $_isFatal = false;
	
	/**
	 * コンストラクター
	 *
	 * @access	private
	 */
	function Logger_ViewDisplay() {
		set_error_handler("setErrorHandle");
		register_shutdown_function("redisterShutdown"); 

		//$this->_container =& DIContainerFactory::getContainer();
	}
	
	/**
	 * Logger_ViewDisplayクラスの唯一のインスタンスを返却
	 *
	 * @return	ObjectLogger_ViewDisplayクラスのインスタンス
	 * @access	public
	 **/
	function &getInstance($set_instance = null) {
		static $instance = null;
		if ($instance === NULL) {
			$instance = new Logger_ViewDisplay();
		}
		return $instance;
	}
	
	/**
	 * デバッグログ用のエラーハンドラ関数
	 *  Note:デバッグログでのfile名は、パスなし errLineなし
	 * @access	private
	 */
	function setLoggerHandle($errNo, $errStr, $errFile, $errLine=NULL) 
	{
		$container =& DIContainerFactory::getContainer();
		
		$write_flag = false;
		if($errNo != LEVEL_SQL)	{
			if (defined("PHP_DEBUG")) {
				if(PHP_DEBUG)
					$php_debug = 1;
				else
					$php_debug = 0;
				if (defined("TRACE_LOG_LEVEL")) {
					$trace_log_level = TRACE_LOG_LEVEL;
				} else {
					$trace_log_level = LEVEL_TRACE;	
				}
			} else {
				//if($this->_container)
					$session =& $container->getComponent("Session");
				
				if(is_object($session)) {
					$php_debug = $session->getParameter("_php_debug");
					$trace_log_level = $session->getParameter("_trace_log_level");
				} else {
					$php_debug = 0;
					$trace_log_level = LEVEL_TRACE;
				}
			}
			if($php_debug) {
				if ($trace_log_level <= $errNo) {
					$write_flag = true;
				}
			}
		} else {
			//SQLデバッグ
			if (defined("SQL_DEBUG")) {
				if(SQL_DEBUG)
					$sql_debug = 1;
				else
					$sql_debug = 0;
			} else {
				$session =& $container->getComponent("Session");
				if(is_object($session)) {
					$sql_debug = $session->getParameter("_sql_debug");
				} else {
					$sql_debug = 0;
				}
			}
			if($sql_debug) {
				$actionChain =& $container->getComponent("ActionChain");
		    	$action_name = $actionChain->getCurActionName();
		    	$pathList = explode("_", $action_name);
		    	if(!(count($pathList) > 1 && $pathList[0] == "common" && $pathList[1] == "download")) {
					$write_flag = true;
		    	}
			}
		}
		if($write_flag) {
			$new_error = array( 
				'errno' => $errNo,
				'errstr' => $errStr,
				'errfile' => $errFile,
				'errline' => $errLine 
			);
			$this->_errors[] = $new_error;	
		}
	}
	
	/**
	 * エラー文字列描画処理
	 * NOTE: LEVEL_XXXと定義済の定数が同じにならないように配慮すること
	 * @access	private
	 */
	function renderErrors()
	{
		$output = '';
		if ($this->_isFatal) {
			$output .= 'This page cannot be displayed due to an internal error.<br/><br/>';
		}
		if (empty($this->_errors)) {
			return $output;
		}
		foreach( $this->_errors as $error )
		{
			$output .="<div class=\"logger";
			switch ( $error['errno'] )
			{
				case E_USER_NOTICE:
					$output .=" logger_notice\">"; 
					$output .= "[User_Notice]: ";
					break;
				case E_USER_WARNING:
					$output .=" logger_warning\">";
					$output .= "[User_Warning]: ";
					break;
				case E_USER_ERROR:
					$output .=" logger_error\">";
					$output .= "[User_Error]: ";
					break;
				case E_NOTICE:
					$output .=" logger_notice\">";
					$output .= "[PHP_Notice]: ";
					break;
				case E_WARNING:
					$output .=" logger_warning\">";
					$output .= "[PHP_Warning]: ";
					break;
				case LEVEL_FATAL:
					$output .=" logger_fatal\">";
					$output .= "[LOG_FATAL]: ";
					break;
				case LEVEL_ERROR:
					$output .=" logger_error\">";
					$output .= "[LOG_ERROR]: ";
					break;
				case LEVEL_WARN:
					$output .=" logger_warning\">";
					$output .= "[LOG_WARN]: ";
					break;
				case LEVEL_INFO:
					$output .=" logger_notice\">";
					$output .= "[LOG_INFO]: ";
					break;
				case LEVEL_DEBUG:
					$output .=" logger_debug\">";
					$output .= "[LOG_DEBUG]: ";
					break;
				case LEVEL_TRACE:
					$output .=" logger_trace\">";
					$output .= "[LOG_TRACE]: ";
					break;
				case LEVEL_SQL:
					$output .=" logger_sql\">";
					$output .= "[SQL]: ";
					break;
				default:
					$output .=" logger_other\">";
					$output .= "[Unknown_Condition_" . $error['errno'] . "]: ";
			} 
			if($error['errline'] != NULL) 
				$output .= sprintf( "%s in file %s line %s<br />\n", htmlspecialchars($error['errstr'], ENT_QUOTES), $error['errfile'], $error['errline'] );
			else {
				if($error['errfile'] != NULL) 
					$output .= sprintf( "%s in file %s<br />\n", htmlspecialchars($error['errstr'], ENT_QUOTES), $error['errfile'] );
				else
					$output .= sprintf( "%s<br />\n", $error['errstr']);
			}
			$output .="</div>";
		}
		if($output != "")
			$output = "<div class=\"logger_block\">\n".$output."\n</div>";
		//$output = "<hr />\n".$output;
		
		return $output;
	} 
	
	/**
	 * ログを出力する関数
	 *
	 * @param	integer	$logLevel	ログレベル
	 * @param	string	$message	エラーメッセージ
	 * @param	mixed	$caller		呼び出し元
	 * @access	public
	 * @since	3.0.0
	 **/
	function output($logLevel, $message, $caller) {
		
		Logger_ViewDisplay::setLoggerHandle($logLevel, $message, $caller);
	}
	
	/**
	 * エラーハンドル
	 * 
	 * @param array エラー情報の配列
	 * @access public 
	 * @return void
	 */
	function handleError($error)
	{	
		if (($error['errno'] & error_reporting()) != $error['errno']) {
			return;
		}
		$this->_errors[] = $error;
		if ($error['errno'] == E_USER_ERROR) {
			$this->_isFatal = true;
			exit();
		}
	} 
}
/**
 * ユーザ定義のエラーハンドラ関数
 *
 * @access	public
 */
function setErrorHandle($errNo, $errStr, $errFile, $errLine) {
	$new_error = array( 
		'errno' => $errNo,
		'errstr' => $errStr,
		'errfile' => preg_replace("|^" . urlencode(BASE_DIR) . "/|", '', $errFile),
		'errline' => $errLine 
	);
	$error_handler =& Logger_ViewDisplay::getInstance();
	$error_handler->handleError($new_error);
}
/**
 * シャットダウン時に実行する関数
 *
 * @access	public
 */
function redisterShutdown() {
	$container =& DIContainerFactory::getContainer();
	$request =& $container->getComponent("Request");
	if($request) {
		$action_name = $request->getParameter(ACTION_KEY);
	
		//TODO:ヘッダー出力ではログを出力不可 後に削除するかも
		if(!preg_match("/^headerinc/", $action_name)){		
			$error_handler =& Logger_ViewDisplay::getInstance();
			echo $error_handler->renderErrors();
		}
	}
}
	
?>
