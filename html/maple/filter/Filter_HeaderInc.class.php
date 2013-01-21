<?php
//
// $Id: Filter_HeaderInc.class.php,v 1.8 2007/06/18 11:24:39 Ryuji.M Exp $
//

/**
 * インクルードするJavascriptをActionChainに登録する
 *
 * @author	Ryuji Masukawa
 **/
class Filter_HeaderInc extends Filter {
	/**
	 * @var ヘッダーでincludeするjavascript配列
	 *
	 * @access	private
	 **/
	var $_script;
	
	/**
	 * @var ヘッダーでincludeするcss配列
	 *
	 * @access	private
	 **/
	var $_css;
	
	/**
	 * @var ヘッダーでfetchするtemplate配列
	 *
	 * @access	private
	 **/
	var $_template;
	var $_template_dir;
	
	/**
	 * コンストラクター
	 *
	 * @access	public	
	 */
	function Filter_HeaderInc() {
		parent::Filter();
	}

	/**
	 * インクルードの処理を実行
	 *
	 * @access	public
	 **/
	function execute() {
		
		$container =& DIContainerFactory::getContainer();
		
		$request =& $container->getComponent("Request");
		$header = $request->getParameter("header");
		//ヘッダーが表示するならば
		if(!isset($header) || $header == 1) {
			
			$attributes = $this->getAttributes();
			foreach ($attributes as $key => $value) {
				/*
				if($key == "js" || preg_match("/_js/", $key)) {
					$include_files = $value;
					if(isset($include_files)) {
						$include_pathList = explode(",", $include_files);
						foreach($include_pathList as $include_file){
							$this->setScriptInc($include_file);
				        }
					}
				}
				*/
				//if($key == "css" || preg_match("/_css/", $key)) {
					$include_files = $value;
					if(isset($include_files)) {
						$include_pathList = explode(",", $include_files);
						
						foreach($include_pathList as $include_file){
							$this->setCssInc($include_file);
				        }
					}
				//}	
			}
			//key:js,css固定
			/* jsは１つに統合したためコメント
			$include_files = $this->getAttribute("js");
			if(isset($include_files)) {
				$include_pathList = explode(",", $include_files);
				
				foreach($include_pathList as $include_file){
					$this->setScriptInc($include_file);
		       }
			}
			*/
			/*
	        $include_files = $this->getAttribute("css");
	        if(isset($include_files)) {
				$include_pathList = explode(",", $include_files);
				
				foreach($include_pathList as $include_file){
					$this->setCssInc($include_file);
		        }
	        }
	        */
		}
		
		$log =& LogFactory::getLog();
		$log->trace("Filter_HeaderIncの前処理が実行されました", "Filter_HeaderInc#execute");
		
		//
		// 後処理
		//
		
		$filterChain =& $container->getComponent("FilterChain");
		$filterChain->execute();

		$log->trace("Filter_HeaderIncの後処理が実行されました", "Filter_HeaderInc#execute");
	}
	
	/**
	 * JavaScript配列を設定する
	 * @param string $value
	 * @access	public
	 **/
	/* jsは１つに統合したためコメント
    function setScriptInc($value)
	{
		if(!isset($this->_script[$value])) {
			$pathList = explode(":", $value);
			$this->_script[$value] = $pathList[0] . "/" . $pathList[1];
		}
	}
	*/
	
	/**
	 * JavaScript配列を取得する
	 * @return	array	インクルードするJavascriptの配列
	 * @access	public
	 **/
	/* jsは１つに統合したためコメント
    function getScriptInc()
	{
		return $this->_script;
	}
	*/
	
	/**
	 * Css配列を設定する
	 * @param string $value
	 * @access	public
	 **/
    function setCssInc($value)
	{
		if(!isset($this->_css[$value])) {
			$pathList = explode(":", $value);
			$this->_css[$value] = $pathList[0] . "/" . $pathList[1];
		}
	}
	
	/**
	 * Css配列を取得する
	 * @return	array	インクルードするJavascriptの配列
	 * @access	public
	 **/
    function getCssInc()
	{
		return $this->_css;
	}
}
?>