<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * システムConfig登録
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class System_Action_Pagestyle extends Action
{
	
	// リクエストパラメータを受け取るため
	var $align = null;
	var $topmargin = null;
	var $rightmargin = null;
	var $bottommargin = null;
	var $leftmargin = null;
	var $default_themes = null;
	var $system_pagelayout_type = null;
	
	//使用コンポーネント
	var $config = null;
	
    /**
     * DB登録
     *
     * @access  public
     */
    function execute()
    {
        $value = ($this->align) ? $this->align : SYSTEM_DEFAULT_ALIGN;
        if (!$this->_update('align', $value)) return 'error';
        
        $value = ($this->topmargin != null) ? $this->topmargin : SYSTEM_DEFAULT_MARGIN;
        if (!$this->_update('topmargin', $value)) return 'error';

        $value = ($this->rightmargin != null) ? $this->rightmargin : SYSTEM_DEFAULT_MARGIN;
        if (!$this->_update('rightmargin', $value)) return 'error';
        
        $value = ($this->bottommargin != null) ? $this->bottommargin : SYSTEM_DEFAULT_MARGIN;
        if (!$this->_update('bottommargin', $value)) return 'error';
        
        $value = ($this->leftmargin != null) ? $this->leftmargin : SYSTEM_DEFAULT_MARGIN;
        if (!$this->_update('leftmargin', $value)) return 'error';
        
        //if ($this->defalut_themes) {
        	$value = ($this->default_themes[0]) ? $this->default_themes[0] : SYSTEM_DEFAULT_THEME;
        	if (!$this->_update('default_theme_public', $value)) return 'error';
        	$value = ($this->default_themes[1]) ? $this->default_themes[1] : SYSTEM_DEFAULT_THEME;
        	if (!$this->_update('default_theme_private', $value)) return 'error';
        	$value = ($this->default_themes[2]) ? $this->default_themes[2] : SYSTEM_DEFAULT_THEME;
        	if (!$this->_update('default_theme_group', $value)) return 'error';
        //}
        
        $type = ($this->system_pagelayout_type) ? $this->system_pagelayout_type : SYSTEM_DEFAULT_PAGESTYLE_LAYOUT_TYPE;
        $layouts = array();
		//$layouts['footer'] = _ON;
		switch ($type) {
		case "A":
			$layouts['header'] = _OFF;
			$layouts['left'] = _OFF;
			$layouts['right'] = _OFF;
			break;
		case "B":
			$layouts['header'] = _ON;
			$layouts['left'] = _OFF;
			$layouts['right'] = _OFF;
			break;
		case "C":		
			$layouts['header'] = _ON;
			$layouts['left'] = _ON;
			$layouts['right'] = _OFF;
			break;
		case "D":	
			$layouts['header'] = _ON;
			$layouts['left'] = _OFF;
			$layouts['right'] = _ON;
			break;
		case "E":
			$layouts['header'] = _ON;
			$layouts['left'] = _ON;
			$layouts['right'] = _ON;
			break;
		case "F":
			$layouts['header'] = _OFF;
			$layouts['left'] = _ON;
			$layouts['right'] = _OFF;
			break;
		case "G":
			$layouts['header'] = _OFF;
			$layouts['left'] = _OFF;
			$layouts['right'] = _ON;
			break;
		case "H":
			$layouts['header'] = _OFF;
			$layouts['left'] = _ON;
			$layouts['right'] = _ON;
			break;
		}
    	if (!$this->_update('header_flag', $layouts['header'])) return 'error';
    	//if (!$this->_update('footer_flag', $layouts['footer'])) return 'error';
    	if (!$this->_update('leftcolumn_flag', $layouts['left'])) return 'error';
    	if (!$this->_update('rightcolumn_flag', $layouts['right'])) return 'error';
    	
    	return 'success';
    }
    
    function _update($name, $value) {
    	$status = $this->config->updConfigValue(_SYS_CONF_MODID, $name, $value, _PAGESTYLE_CONF_CATID);
    	return $status;
    }
}
?>
