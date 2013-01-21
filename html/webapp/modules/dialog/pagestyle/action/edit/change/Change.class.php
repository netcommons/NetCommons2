<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ページスタイル一時変更セッション登録
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

class Dialog_Pagestyle_Action_Edit_Change extends Action
{
	
	// リクエストパラメータを受け取るため
	var $page_id = null;
	var $theme_name = null;
	
	var $page_name = null;
	var $align = null;
	var $leftmargin = null;
	var $rightmargin = null;
	var $topmargin = null;
	var $bottommargin = null;
	
	//レイアウト
	var $header_flag = null;
	var $leftcolumn_flag = null;
	var $rightcolumn_flag = null;
	//配色
	var $body_background = null;
	var $headercolumn_background = null;
	var $leftcolumn_background = null;
	var $centercolumn_background = null;
	var $rightcolumn_background = null;
	var $footercolumn_background = null;
	
	var $body_backgroundColor = null;
	var $headercolumn_backgroundColor = null;
	var $leftcolumn_backgroundColor = null;
	var $centercolumn_backgroundColor = null;
	var $rightcolumn_backgroundColor = null;
	var $footercolumn_backgroundColor = null;
	
	var $headercolumn_borderTop = null;
	var $leftcolumn_borderTop = null;
	var $centercolumn_borderTop = null;
	var $rightcolumn_borderTop = null;
	var $footercolumn_borderTop = null;
	
	var $headercolumn_borderRight = null;
	var $leftcolumn_borderRight = null;
	var $centercolumn_borderRight = null;
	var $rightcolumn_borderRight = null;
	var $footercolumn_borderRight = null;
	
	var $headercolumn_borderBottom = null;
	var $leftcolumn_borderBottom = null;
	var $centercolumn_borderBottom = null;
	var $rightcolumn_borderBottom = null;
	var $footercolumn_borderBottom = null;
	
	var $headercolumn_borderLeft = null;
	var $leftcolumn_borderLeft = null;
	var $centercolumn_borderLeft = null;
	var $rightcolumn_borderLeft = null;
	var $footercolumn_borderLeft = null;
	
	var $session = null;
	
	
    /**
     * セッションにページスタイルのデータを登録
     * @access  public
     */
    function execute()
    {
    	$_allow_layout_flag = $this->session->getParameter("_allow_layout_flag");
    	$pagestyle_list = $this->session->getParameter("pagestyle_list");
    	if(is_array($pagestyle_list)) {
	    	foreach($pagestyle_list as $key=>$pagetheme) {
	    		if($key != $this->page_id) {
	    			//クリア
	    			unset($pagestyle_list[$key]);	
	    		}
	    	}
    	}
    	if($_allow_layout_flag) {
	    	$loop_array = array("page_name","align","leftmargin","rightmargin","topmargin","bottommargin",
								"theme_name","header_flag","leftcolumn_flag","rightcolumn_flag",
								"body_background","headercolumn_background","leftcolumn_background","centercolumn_background",
								"rightcolumn_background","footercolumn_background",
	    						"body_backgroundColor","headercolumn_backgroundColor","leftcolumn_backgroundColor","centercolumn_backgroundColor",
								"rightcolumn_backgroundColor","footercolumn_backgroundColor",
								"headercolumn_borderTop","leftcolumn_borderTop","centercolumn_borderTop","rightcolumn_borderTop","footercolumn_borderTop",
								"headercolumn_borderRight","leftcolumn_borderRight","centercolumn_borderRight","rightcolumn_borderRight","footercolumn_borderRight",
								"headercolumn_borderBottom","leftcolumn_borderBottom","centercolumn_borderBottom","rightcolumn_borderBottom","footercolumn_borderBottom",
								"headercolumn_borderLeft","leftcolumn_borderLeft","centercolumn_borderLeft","rightcolumn_borderLeft","footercolumn_borderLeft");
    	} else {
    		//レイアウト、配色、align,marginの変更を許さない
    		//左カラムを安易に削除したり、配色を変更したりできないようにするため
    		$loop_array = array("page_name", "theme_name");
    	}
  		foreach($loop_array as $value) {  	
			if($this->$value !== null) {
				$pagestyle_list[$this->page_id][$value] = $this->$value;
				switch($value) {
					case "body_backgroundColor":
						unset($pagestyle_list[$this->page_id]["body_background"]);
						$pagestyle_list[$this->page_id]["body_background"] = $pagestyle_list[$this->page_id][$value]." none";
						break;
					case "headercolumn_backgroundColor":
						unset($pagestyle_list[$this->page_id]["headercolumn_background"]);
						$pagestyle_list[$this->page_id]["headercolumn_background"] = $pagestyle_list[$this->page_id][$value]." none";
						break;
					case "leftcolumn_backgroundColor":
						unset($pagestyle_list[$this->page_id]["leftcolumn_background"]);
						$pagestyle_list[$this->page_id]["leftcolumn_background"] = $pagestyle_list[$this->page_id][$value]." none";
						break;
					case "centercolumn_backgroundColor":
						unset($pagestyle_list[$this->page_id]["centercolumn_background"]);
						$pagestyle_list[$this->page_id]["centercolumn_background"] = $pagestyle_list[$this->page_id][$value]." none";
						break;
					case "rightcolumn_backgroundColor":
						unset($pagestyle_list[$this->page_id]["rightcolumn_background"]);
						$pagestyle_list[$this->page_id]["rightcolumn_background"] = $pagestyle_list[$this->page_id][$value]." none";
						break;
					case "footercolumn_backgroundColor":
						unset($pagestyle_list[$this->page_id]["footercolumn_background"]);
						$pagestyle_list[$this->page_id]["footercolumn_background"] = $pagestyle_list[$this->page_id][$value]." none";
						break;
				}
				//$this->session->setParameter(array("pagestyle_list",$this->page_id,$value), $this->$value);
			}
  		}
  		$this->session->setParameter("pagestyle_list", $pagestyle_list);
		return 'success';
    }
}
?>
