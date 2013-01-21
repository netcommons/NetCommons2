<?php
//
// Authors: Ryuji Masukawa
//
// $Id: Logger_DbRegistManage.class.php,v 1.3 2006/10/13 08:51:26 Ryuji.M Exp $
//

/**
 * DBに出力するLogger
 *
 * @author	Ryuji Masukawa
 **/
class Logger_DbRegistManage extends Logger {

	var $_instance;
	
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $db = null;

	function Logger_DbRegistManage() {
		$this->_instance =& Logger_DbRegist::getInstance();
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
	/**
	 * sqlのログを出力
	 *
	 * @param	string	$message	エラーメッセージ
	 * @access	public
	 **/
	function sql_trace($message) {
		$this->_instance->sql_output($message);
	}
}

class Logger_DbRegist {
	
	/**
	 * エラーリスト
	 * 
	 * @var array 
	 * @access private 
	 */
	var $_errors = array();
	
	var $_sql_errors = array();
	
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
	function Logger_DbRegist() {
		set_error_handler("setErrorHandle");
		register_shutdown_function("redisterShutdown"); 
	}
	
	/**
	 * Logger_DbRegistクラスの唯一のインスタンスを返却
	 *
	 * @return	Object Logger_DbRegistクラスのインスタンス
	 * @access	public
	 **/
	function &getInstance($set_instance = null) {
		static $instance = null;
		if ($instance === NULL) {
			$instance = new Logger_DbRegist();
		}
		return $instance;
	}
	
	/**
	 * デバッグログ用のエラーハンドラ関数
	 *  Note:デバッグログでのfile名は、パスなし errLineなし
	 * @access	private
	 */
	function setLoggerHandle($errNo, $errStr, $errFile, $errLine=NULL) {
		if (LOG_LEVEL <= $errNo) {
			$new_error = array( 
				'errno' => $errNo,
				'errstr' => $errStr,
				'errfile' => $errFile,
				'errline' => $errLine 
			);
			$this->_errors[] = $new_error;
		}
	}
	
	function setLoggerSqlHandle($errStr) {
		$new_error = array( 
			'errstr' => $errStr
		);
		$this->_sql_errors[] = $new_error;
		
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
				default:
					$output .=" logger_other\">";
					$output .= "[Unknown_Condition_" . $error['errno'] . "]: ";
			} 
			if($error['errline'] != NULL) 
				$output .= sprintf( "%s in file %s line %s<br />\n", $error['errstr'], $error['errfile'], $error['errline'] );
			else
				$output .= sprintf( "%s in file %s<br />\n", $error['errstr'], $error['errfile'] );
			$output .="</div>";
		}
		$output = "<div class=\"logger_block\">\n".$output."\n</div>";
		//$output = "<hr />\n".$output;
		
		return $output;
	} 
	
	function renderSqlErrors()
	{
		$output = '';
		if (empty($this->_sql_errors)) {
			return $output;
		}
		foreach( $this->_sql_errors as $error )
		{
			$output .="<div class=\"logger\">";
			$output .= $error['errstr'];
			$output .="</div>";
		}
		$output = "<div class=\"logger_block\">\n".$output."\n</div>";
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
		Logger_DbRegist::setLoggerHandle($logLevel, $message, $caller);
	}
	function sql_output($message) {
		Logger_DbRegist::setLoggerSqlHandle($message);
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
		'errfile' => preg_replace("|^" . BASE_DIR . "/|", '', $errFile),
		'errline' => $errLine 
	);
	$error_handler =& Logger_DbRegist::getInstance();
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
	$action_name = $request->getParameter(ACTION_KEY);
	if(!isset($action_name))
		$action_name = DEFAULT_ACTION;
	if(!preg_match("/^popup_view_debug/", $action_name) && !preg_match("/^popup_action_deldebug/", $action_name)){
		$request_uri = $_SERVER['REQUEST_URI'];
		//include debugクラス
		$debugdb =& $container->getComponent("DebugDb");
		//include_once MAPLE_DIR.'/nccore/DebugDb.class.php';
		//$debugdb =& new DebugDb();
		if(!$debugdb->hasDb()) {
			//DBオブジェクト取得
			$db =& $container->getComponent("DbObject");
			$debugdb->setDb(&$db);
		}
			
		$error_handler =& Logger_DbRegist::getInstance();
		$debug_value = $error_handler->renderErrors();
		
		if($debug_value!="") {
			$params = array(
				"debug_type" =>_DEBUG_PHP,
				"param" => $request_uri,
				"action_name" => $action_name,
				"debug_value" => $debug_value
			);
			//リクエストパラメータの最後に「&_=」があった場合は
			//Ajaxからのリクエストとみなす
			//そうでない場合、ログを削除
			if(!preg_match("/&_=$/",$request_uri)){
				$debugdb->delDebugById(null,_DEBUG_PHP);
			}
			
			$debugdb->insDebug($params);
		}
		$debug_value = $error_handler->renderSqlErrors();
		if($debug_value!="") {
			$params = array(
				"debug_type" =>_DEBUG_SQL,
				"param" => $request_uri,
				"action_name" => $action_name,
				"debug_value" => $debug_value
			);
			//リクエストパラメータの最後に「&_=」があった場合は
			//Ajaxからのリクエストとみなす
			//そうでない場合、ログを削除
			if(!preg_match("/&_=$/",$request_uri)){
				$debugdb->delDebugById(null,_DEBUG_SQL);
			}
			
			$debugdb->insDebug($params);
		}
	}
}
	
?>
