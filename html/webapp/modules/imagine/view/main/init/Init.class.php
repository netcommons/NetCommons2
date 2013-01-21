<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * IMAGINEメイン画面表示
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Imagine_View_Main_Init extends Action
{
	// validatorから受け取るため
    var $display = null;
    
    // 値をセットするため
	var $displayName = null;

    /**
     * IMAGINEメイン画面表示
     *
     * @access  public
     */
    function execute()
    {
		if ($this->display == IMAGINE_DISPLAY_FULL) {
			$this->displayName = "full";
		} else {
			$this->displayName = "compact";	
		}
		
		return "success";
    }
}
?>
