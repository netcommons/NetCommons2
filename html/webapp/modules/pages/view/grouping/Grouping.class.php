<?php
include_once MODULE_DIR.'/headerinc/view/main/Main.class.php';

class Pages_View_Grouping extends Action
{
	// 使用コンポーネントを受け取るため
	var $blocksView = null;
	var $pagesView = null;
	var $requestMain = null;
	var $preexecute = null;
	var $getData = null;
	var $pagesCompmain = null;
	var $response = null;
	var $session = null;
	var $request = null;
	var $modulesView = null;
	var $commonMain = null;
	
	var $_renderer = null;
	
	//リクエストパラメータを受け取るため
	var $block_id = null;
	var $blocktheme_name = null;
	
	function execute()
	{
		//ヘッダー表示：非表示
		$header = $this->request->getParameter("_header");
		if(!isset($header)) {
			//default:ヘッダーを表示する
			$header = 1;
		}
		
		//出力用バッファをクリア(消去)し、出力のバッファリングをオフ
		//$ob_buffer = ob_get_contents();
		//ob_end_clean();
		
		$this->_renderer =& SmartyTemplate::getInstance();
       
        $main_blocks =& $this->getData->getParameter("blocks");
        if($main_blocks[$this->block_id]['action_name'] != "pages_view_grouping") {
        	//エラー
        	exit;
		}
		
		if($header) {
        	//ヘッダー読み込み		
        	$headerInc = new Headerinc_View_Main();
        }
        $pages_obj = $this->getData->getParameter("pages");
        //main_action_nameをassign
        $this->_renderer->assign('main_action_name',$this->session->getParameter('main_action_name'));
        //$this->_renderer->assign('main_block_id',null);
		//$this->_renderer->assign('main_block_id',$this->block_id);
		
        $page_id = $main_blocks[$this->block_id]['page_id'];
     
        $css = array();
        $js = array();
        $block_objs = array();
        $block_objs_id = array();
        $_theme_list = $this->session->getParameter("_theme_list");
        if($this->blocktheme_name != null) {
			if($this->blocktheme_name == "_auto") {
				$this->blocktheme_name = $_theme_list[$pages_obj[$page_id]['display_position']];
			}
			$main_blocks[$this->block_id]['theme_name'] = $this->blocktheme_name;
		} else if($main_blocks[$this->block_id]['theme_name'] == "") {
			$main_blocks[$this->block_id]['theme_name'] = $_theme_list[$pages_obj[$page_id]['display_position']];
        }
        $themeStrList = explode("_", $main_blocks[$this->block_id]['theme_name']);
		if(count($themeStrList) == 1) {
			$themeDir = "/themes/".$main_blocks[$this->block_id]['theme_name']."/templates";
		} else {
			$bufthemeStr = array_shift ( $themeStrList );
			$themeDir = "/themes/".$bufthemeStr."/templates/".implode("/", $themeStrList);
		}
        $all_headerinc_arr = array("css"=> array(
												"blocks:style.css".$main_blocks[$this->block_id]['theme_name'] => 
												BASE_URL ."/". $themeDir . "/style.css"
												));
        //-------------------------------------------------
        //ブロック情報取得
        //-------------------------------------------------
        $blocks = $this->blocksView->getBlockByPageId($page_id);
        //$page_obj = $this->pagesView->getPageById($page_id);
        $main_thread_num = $main_blocks[$this->block_id]['thread_num'];
        $include_dir_name_arr = array();
        $include_dir_name_css_arr = array();
        $include_block_id_css_arr = array();
        if(is_array($blocks)) {
			$count = 0;
			
			//parent_id取得
			//if($main_blocks[$this->block_id]['parent_id'] == 0)
				$parent_id = $main_blocks[$this->block_id]['block_id'];
			//else
			//	$parent_id = $main_blocks[$this->block_id]['parent_id'];
 			$parent_arr = array($main_blocks[$this->block_id]['block_id']);	
 			//modulesオブジェクト取得
 			$moduleidList = array();
			foreach($blocks as $block) {
				$moduleidList[$block['module_id']] = $block['module_id'];
			}
			
			//$modules_obj = null;
			$func = array($this, 'fetchcallback');
			list($modules_obj, $include_dir_name_arr) = $this->modulesView->getModulesById($moduleidList, $func);
		
			$this->getData->setParameter("modules",$modules_obj);
			
			foreach($blocks as $block) {
				if($block['url'] =="" || $block['url'] == null || $block['url'] == BASE_URL) {
					$mysite = true;	
					$blocks[$count]['full_path'] = BASE_URL.INDEX_FILE_NAME."?action=".$block['action_name']."&block_id=".$block['block_id']."&page_id=".$block['page_id'];	//絶対座標に変換
					//$blocks[$count]['full_path'] = BASE_URL.INDEX_FILE_NAME."?action=".$block['action_name'].$block['parameters']."&block_id=".$block['block_id']."&page_id=".$block['page_id'];	//絶対座標に変換
				} else {
					$mysite = false;
					$blocks[$count]['full_path'] = $block['url']."?action=".$block['action_name']."&block_id=".$block['block_id']."&page_id=".$block['page_id'];	//絶対座標に変換
					//$blocks[$count]['full_path'] = $block['url']."?action=".$block['action_name'].$block['parameters']."&block_id=".$block['block_id']."&page_id=".$block['page_id'];	//絶対座標に変換
				}
				//グループ化したブロックの子供ならばデータ取得
				if($block['block_id'] == $this->block_id) {
					$blocks[$count]['theme_name'] = $main_blocks[$this->block_id]['theme_name'];
					$block_objs[$pages_obj[$page_id]['display_position']][$block['parent_id']][$block['col_num']][$block['row_num']] = $blocks[$count];
					$block_objs_id[$block['block_id']] =& $blocks[$count];
					//$this->getData->setParameter("blocks",$block_objs_id);
				} else if(in_array($block['parent_id'],$parent_arr)) {
					$block_objs[$pages_obj[$page_id]['display_position']][$block['parent_id']][$block['col_num']][$block['row_num']] = $blocks[$count];
					$block_objs_id[$block['block_id']] =& $blocks[$count];
					$this->getData->setParameter("blocks",$block_objs_id);
					if($block['action_name'] != "pages_view_grouping") {
						//既に読み込んでいる場合、スキップ
						if($header && !isset($headerinc_buf[$block['url'].":".$block['action_name'].":".$block['theme_name'].":".$block['temp_name']])) {
							if($mysite) {
								//自サイト
								//include_header読み込み
								$headerInc->setParams($block['action_name'],$block['block_id'],$block['theme_name'],$block['temp_name']);
								$res = $headerInc->execute();
								if($res) {
									$this->pagesCompmain->setHeaderArray($res,$block,$all_headerinc_arr);
								}
							} else {
								//他サイト
								$header_url = $block['url']."?action=headerinc_view_main&action_name=".$block['action_name'].$block['parameters']."&theme_name=".$block['theme_name']."&temp_name=".$block['temp_name']."&print_flag=1";
								//TODO:他サイトの場合、キャッシュをもたせるようなしくみが必要
								//また無駄なフィルターをチェックしないようにする？
								$res = $this->requestMain->getResponseHtml($header_url);
								$this->pagesCompmain->setHeaderArray($res,$block,$all_headerinc_arr);
							}
							$headerinc_buf[$block['url'].":".$block['action_name'].":".$block['theme_name'].":".$block['temp_name']] = true;
						}
						//権限設定
						//$this->session->setParameter("_auth_id",$pages_obj[$block['page_id']]['authority_id']);
						//$this->session->setParameter("_hierarchy", intval($pages_obj[$block['page_id']]['hierarchy']));
						
						//ob_start();
						if($mysite) {
							//parametersの配列分解
							$parameters = split("&", $block['parameters']);
							$params_sub = array();
							foreach($parameters as $parameter) {
								$paramArray = split('=',$parameter);
								if($paramArray[0] != "")
		      						$params_sub[$paramArray[0]] = $paramArray[1];
							}
							$params = array("action" =>$block['action_name'], "theme_name" =>$block['theme_name'], "temp_name" =>$block['temp_name'], "page_id" =>$page_id, "module_id" =>$block['module_id'], "block_id" =>$block['block_id'],"_header" =>"0","_output" =>"0");
							$params = array_merge($params,$params_sub);
							
							if($block['shortcut_flag']) {
								$buf_id = $params['block_id'];
								$params['block_id'] = $params['_ref_block_id'];
								$params['_ref_block_id'] = $buf_id;
							}
							$this->commonMain->getTopId($block['block_id'], $block['module_id']);
							$this->preexecute->preExecute($block['action_name'], $params);
							
							$html[$block['block_id']] = $this->response->getResult();
						} else {
							//TODO:他サイトの場合、キャッシュをもたせるようなしくみが必要
							$html[$block['block_id']] = $this->requestMain->getResponseHtml($blocks[$count]['full_path']);
						}
					} else {
						$parent_arr[] = $block['block_id'];
					}
				}
				$include_dir_name_css_arr[$block['theme_name']] = $block['theme_name'];
				$include_block_id_css_arr[] = $block['block_id'];
								
				$count++;
			}
        } else {
        	//エラー
        	exit;
        }
        //ページタイトルをセッションに代入
    	$this->session->setParameter("_page_title",$main_blocks[$this->block_id]['block_name']);

        
		//-------------------------------------------------
        // コンテンツ
        //-------------------------------------------------
		if(is_array($block_objs)) {
			$template_dir = WEBAPP_DIR . "/templates/"."main/";
			$this->_renderer->setTemplateDir($template_dir);
			//キャッシュ処理をしない
			//$caching = $this->_renderer->getCaching();
    		//$this->_renderer->setCaching(0);
    		$main_result = $this->pagesCompmain->setPageFetch($block_objs,$pages_obj,$html,$template_dir,$pages_obj[$page_id]['display_position'],$main_blocks[$this->block_id]['parent_id'],$main_blocks[$this->block_id]['thread_num'],true);
			
			//キャッシュ処理を元に戻す
			//$this->_renderer->setCaching($caching);
			if(!$header) {
				$script_str =& $this->getData->getParameter("script_str");
				$main_result = $main_result."<script class=\"nc_script\" type=\"text/javascript\">" . $script_str. "</script>";
			}
		}
		//-------------------------------------------------
        // ヘッダー
        //-------------------------------------------------
        if($header) {
	        $this->pagesCompmain->showHeader($pages_obj[$page_id],$include_dir_name_arr, $all_headerinc_arr, $include_dir_name_css_arr, $include_block_id_css_arr);
        }
		if(isset($main_result)) {
			print $main_result;
			
			if($header) {
				print "<input type=\"hidden\" id=\"_grouping_thread_num\" value=\"".($main_blocks[$this->block_id]['thread_num']+1)."\"/>";
				print "<input type=\"hidden\" id=\"_grouping_parent_id\" value=\"".$main_blocks[$this->block_id]['block_id']."\"/>";
			}	
		}
		
		//-------------------------------------------------
        // フッター
        //-------------------------------------------------
        if($header) {
        	$this->pagesCompmain->showFooter($pages_obj[$page_id], $include_dir_name_arr);
        }
		return 'success';
	}
	
	/**
	 * fetch時コールバックメソッド
	 * @result adodb object
	 * @array  function parameter
	 * @return array $menus_obj
	 * @access	private
	 * TODO:pages_view_mainと同等のものなので統一可能
	 */
	function fetchcallback($result) {
		$modules = null;
		$dir_name_arr = array();
		while ($module = $result->fetchRow()) {
			$pathList = explode("_", $module['action_name']);
			$module["module_name"] = $this->modulesView->loadModuleName($pathList[0]);
			$modules[$pathList[0]] = $module;
			$dir_name_arr[] = $pathList[0];
		}
		
		return array($modules, $dir_name_arr);
	}
}
?>
