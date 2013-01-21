<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Install 初期ウィザード表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_View_Main_Init extends Action
{
    // リクエストパラメータを受け取るため
    
    // 使用コンポーネントを受け取るため
    var $installCompmain = null;
    var $fileView =null;
    var $session = null;
    
    // 値をセットするため
    var $lang_list = null;
    
    /**
     * Install 初期ウィザード表示
     *
     * @access  public
     */
    function execute()
    {
    	// セッション登録できるかどうかをチェック
    	$savePath = $this->session->getSavePath();
    	if(!is_writable($savePath) || !is_writable(SMARTY_COMPILE_DIR)) {
    		$file_path = dirname(INSTALL_INC_DIR) . '/templates/main/installerror.php';
			if(file_exists($file_path)) {
				$content = "";
    			include $file_path;
    			echo $content;
				exit;
			}
			echo INSTALL_PERMISSION_ERROR;
			exit;
    	} 	
    	$this->lang_list = $this->_getLangList();
    	$this->installCompmain->setTitle();
    	return 'success';
    }
    
    /**
     * 言語セット取得
     * @return array lang_list
     * @access  private
     */
    function _getLangList() {
    	$module_path = dirname(dirname(dirname(dirname(__FILE__))));
    	//$module_path = transPathSeparator(dirname(dirname(dirname(dirname(__FILE__)))));
    	$lang_path = $module_path . "/language/";
    	return $this->fileView->getCurrentDir($lang_path);
    }
}
?>
