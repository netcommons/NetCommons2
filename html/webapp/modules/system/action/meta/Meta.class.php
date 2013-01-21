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

class System_Action_Meta extends Action
{
	
	// リクエストパラメータを受け取るため
	var $meta_author = null;
	var $meta_copyright = null;
	var $meta_keywords = null;
	var $meta_description = null;
	var $meta_robots = null;
	var $meta_rating = null;
	
	//使用コンポーネント
	var $config = null;
	
    /**
     * DB登録
     *
     * @access  public
     */
    function execute()
    {
        $value = ($this->meta_author) ? $this->meta_author : "";	//SYSTEM_DEFAULT_META_AUTHOR;
    	if (!$this->_update('meta_author', $value)) return 'error';

    	$value = ($this->meta_copyright) ? $this->meta_copyright : "";	//SYSTEM_DEFAULT_META_COPYRIGHT;
    	if (!$this->_update('meta_copyright', $value)) return 'error';
    	
    	$value = ($this->meta_keywords) ? $this->meta_keywords : "";	//SYSTEM_DEFAULT_META_KEYWORDS;
    	if (!$this->_update('meta_keywords', $value)) return 'error';
    	
    	$value = ($this->meta_description) ? $this->meta_description : "";	//SYSTEM_DEFAULT_META_DESCRIPTION;
    	if (!$this->_update('meta_description', $value)) return 'error';
    	
    	$value = ($this->meta_robots) ? $this->meta_robots : SYSTEM_DEFAULT_META_ROBOTS;
    	if (!$this->_update('meta_robots', $value)) return 'error';
    	
    	$value = ($this->meta_rating) ? $this->meta_rating : SYSTEM_DEFAULT_META_RATING;
    	if (!$this->_update('meta_rating', $value)) return 'error';
        
    	return 'success';
    }
    
    function _update($name, $value) {
    	$status = $this->config->updConfigValue(_SYS_CONF_MODID, $name, $value, _META_CONF_CATID);
    	return $status;
    }
}
?>
