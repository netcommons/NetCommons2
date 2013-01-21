<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ログインモジュール表示用クラス
 *
 */
class Login_Components_View 
{
	/**
	 * @var ConfigViewオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_config = null;
	
	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	
	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Login_Components_View() 
	{
		$log =& LogFactory::getLog();
		$log->trace("component login View のコンストラクタが実行されました", "loginView#Login_View");

		$this->_container =& DIContainerFactory::getContainer();
		$this->_config =& $this->_container->getComponent("configView");

		$renderer =& SmartyTemplate::getInstance();
		$renderer->assign('autoregist_use', $this->getAutoregistUse() );
		$renderer->assign('autoregist_disclaimer', $this->getDisclaimer() );
	}
	
	/**
	 * 新規登録が可能かシステム設定を調べ返す
	 * @param 
	 * @return 
	 * @access	public
	 */
	function getAutoregistUse() {
		$autoregist_use_arry = $this->_config->getConfigByConfname(_SYS_CONF_MODID, 'autoregist_use');
		if( $autoregist_use_arry != null ) {
			if( isset( $autoregist_use_arry['conf_value'] ) ) {
				return ($autoregist_use_arry['conf_value']);
			}
		}
		return false;
	}
	/**
	 * 新規登録時、登録規約を表示することになっているかシステム設定を調べ返す
	 * @param 
	 * @return 
	 * @access	public
	 */
	function getDisclaimer() {
		$disclaimer_use_arry = $this->_config->getConfigByConfname(_SYS_CONF_MODID, 'autoregist_disclaimer');
		if( $disclaimer_use_arry != null ) {
			if( isset( $disclaimer_use_arry['conf_value'] ) ) {
				return ($disclaimer_use_arry['conf_value']);
			}
		}
		return false;
	}
}
?>