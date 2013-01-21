<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * システム管理>>ページスタイル設定画面表示
 * 		ページスタイル設定項目を表示する
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class System_View_Main_Pagestyle extends Action
{
	// リクエストパラメータを受け取るため
	
    // 使用コンポーネントを受け取るため
    var $configView = null;
    var $pagestyleCompmain = null;
    
    // フィルタによりセット
    
    // 値をセットするため
    var $config = null;
    var $theme_names = null;
    var $category_list = null;
    var $pagetheme_list = null;
    var $act_category = null;
    var $image_path = null;
    var $categories = null;
    
    var $type = null;

	/**
	 * execute実行
	 *
	 * @access  public
	 */
    function execute()
    {
        $this->config =& $this->configView->getConfigByCatid(_SYS_CONF_MODID, _PAGESTYLE_CONF_CATID);
        if ($this->config === false) {
            return 'error';
        }

        // それぞれのスペースの現在のテーマ名
        $public = $this->config['default_theme_public']['conf_value'];
        $private = $this->config['default_theme_private']['conf_value'];
        $group= $this->config['default_theme_group']['conf_value'];
        
        // テーマリスト取得
        $background_list = null;
        list($this->category_list, $background_list, $this->pagetheme_list, $this->image_path, $this->act_category['public']) = 
			$this->pagestyleCompmain->getThemeList($public, "page");

		// category_listからそれぞれのスペースのカテゴリの対応付けを行う
		$spaces_count = 3;
		foreach ($this->category_list as $category => $name) {
			if (isset($this->pagetheme_list[$category][$public])) {
				$this->categories['public'] = $category;
				$spaces_count--;
			}
			if (isset($this->pagetheme_list[$category][$private])) {
				$this->categories['private'] = $category;
				$spaces_count--;
			}
			if (isset($this->pagetheme_list[$category][$group])) {
				$this->categories['group'] = $category;
				$spaces_count--;
			}
			if ($spaces_count <= 0) break;
		}
			
		// TODO: group private
        
    	if($this->config['header_flag']['conf_value']) {
			//B C D E
			if($this->config['leftcolumn_flag']['conf_value']) {
				//C E
				if($this->config['rightcolumn_flag']['conf_value']) {
					$this->type = "E";
				} else {
					$this->type = "C";	
				}
			} else {
				//B D
				if($this->config['rightcolumn_flag']['conf_value']) {
					$this->type = "D";
				} else {
					$this->type = "B";	
				}
			}
		} else {
			
			//A F G H
			if($this->config['leftcolumn_flag']['conf_value']) {
				//F H
				if($this->config['rightcolumn_flag']['conf_value']) {
					$this->type = "H";
				} else {
					$this->type = "F";	
				}
			} else {
				//A G
				if($this->config['rightcolumn_flag']['conf_value']) {
					$this->type = "G";
				} else {
					$this->type = "A";	
				}
			}
		}
    	return 'success';
    }
}
?>
