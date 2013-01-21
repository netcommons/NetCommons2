<?php
//
// Authors: Ryuji Masukawa
//
// $Id: Filter_DbObject.class.php,v 1.8 2008/06/11 13:05:14 Ryuji.M Exp $
//

require_once MAPLE_DIR.'/core/BeanUtils.class.php';

/**
 * DB接続を行うFilter
 *
 * @author	Ryuji Masukawa
 **/
class Filter_DbObject extends Filter {
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;
	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 * @since	3.0.0
	 */
	function Filter_DbObject() {
		parent::Filter();
	}
	
	/**
	 * DBオブジェクトをゲットする
	 *　
	 * @return	object	$db
	 * @access	public
	 */
	function &getDb() {
		return $this->_db;	
	}

	/**
	 * DB接続し、DBオブジェクトをsetterする
	 *
	 **/
	function execute() {
		$log =& LogFactory::getLog();
		$log->trace("Filter_DbObjectの前処理が実行されました", "Filter_DbObject#execute");

		//
		// カレントのActionを取得
		//
		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$action =& $actionChain->getCurAction();

		//
		// 設定ファイルに入っていた値を設定
		//
		$db =& $container->getComponent("DbObject");

		//DB 接続
		if(defined(DATABASE_PCONNECT) && DATABASE_PCONNECT == _ON) {
			$db->setOption('persistent', DATABASE_PCONNECT);
		}
		$db->setDsn(DATABASE_DSN);
		$result = $db->connect();
		if(!$result) {
			
			$file_path = dirname(INSTALL_INC_DIR) . '/templates/main/installerror.php';
			if(file_exists($file_path)) {
				$content = "";
    			include $file_path;
    			echo $content;
				exit;
			} else {
				$log->error("DB接続に失敗しました", "Filter_DbObject#execute");
			}
		}
		//テーブルPrefix
		$db->setPrefix(DATABASE_PREFIX);
		
		$this->_db =& $db;
		
		$filterChain =& $container->getComponent("FilterChain");
		$filterChain->execute();

		$log->trace("Filter_DbObjectの後処理が実行されました", "Filter_DbObject#execute");
	}
}
?>
