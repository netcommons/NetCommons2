<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Install インストール完了
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_View_Complete extends Action
{
    // リクエストパラメータを受け取るため
    
    // 使用コンポーネントを受け取るため
    var $installCompmain = null;
    var $session = null;
    
    // 値をセットするため
    var $finish = null;
    
    /**
     * Install インストール完了
     *
     * @access  public
     */
    function execute()
    {
    	$this->finish = $this->_getFinishMes();
    	$this->installCompmain->setTitle();
    	//$this->_removeSession();
    	$this->session->removeParameter("_site_name");
    	@chmod(INSTALL_INC_DIR . "/". "install.inc.php", 0444);

		$cookie_params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 86400, $cookie_params['path'], $cookie_params['domain'], $cookie_params['secure']);

    	return 'success';
    }
    
    
    /**
     * インストール完了メッセージ取得
     * @return array lang_list
     * @access  private
     */
    function _getFinishMes() {
    	$module_path = dirname(dirname(dirname(__FILE__)));
    	//$module_path = transPathSeparator(dirname(dirname(dirname(__FILE__))));
    	$lang_path = $module_path . "/language/".$this->session->getParameter("_lang");
    	
    	$content = "";
    	include $lang_path.'/'.INSTALL_FINISH_MES_FILENAME;
    	return $content;
    }
    
    
    /**
     * セッションクリア
     * グローバルファイルのインストールで既に削除されるためコメントアウト
     * @access  private
     */
    /*
    function _removeSession() {
    	$this->session->removeParameter("database");
    	$this->session->removeParameter("dbhost");
    	$this->session->removeParameter("dbusername");
    	$this->session->removeParameter("dbpass");
    	$this->session->removeParameter("dbname");
    	$this->session->removeParameter("dbprefix");
    	$this->session->removeParameter("dbpersist");
    	$this->session->removeParameter("base_url");
    	$this->session->removeParameter("base_dir");
    	$this->session->removeParameter("fileuploads_dir");
    	$this->session->removeParameter("htdocs_dir_select");
    	$this->session->removeParameter("htdocs_dir");
    	$this->session->removeParameter("style_dir");
    	$this->session->removeParameter("detail_flag");
    	$this->session->removeParameter("core_base_url");
    	
    	$this->session->removeParameter("install_handle");
    	$this->session->removeParameter("install_login_id");
    	$this->session->removeParameter("install_pass");
    	$this->session->removeParameter("install_confirm_pass");
    	
    	$this->session->removeParameter("install_self_site_id");
    	$this->session->removeParameter("install_user_id");
    }
    */
}
?>
