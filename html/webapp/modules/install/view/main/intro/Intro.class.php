<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Install イントロダクション表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Install_View_Main_Intro extends Action
{
    // リクエストパラメータを受け取るため
    //var $select_lang = null;
    
    // 使用コンポーネントを受け取るため
    var $installCompmain = null;
    var $session = null;
    
    // 値をセットするため
    var $intro = null;
    
    /**
     * Install イントロダクション表示
     *
     * @access  public
     */
    function execute()
    {
    	$this->intro = $this->_getIntro();
    	
    	$this->installCompmain->setTitle();
    	return 'success';
    }
    
    
    /**
     * イントロダクション取得
     * @return array lang_list
     * @access  private
     */
    function _getIntro() {
    	$module_path = dirname(dirname(dirname(dirname(__FILE__))));
    	//$module_path = transPathSeparator(dirname(dirname(dirname(dirname(__FILE__)))));
    	$lang_path = $module_path . "/language/".$this->session->getParameter("_lang");
    	
    	$content = "";
    	include $lang_path.'/'.INSTALL_WELCOME_MES_FILENAME;
    	return $content;
    }
    
    
    
}
?>
