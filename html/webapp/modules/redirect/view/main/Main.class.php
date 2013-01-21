<?php

/*他サイトのデータ取得用：指定URLのデータを取得する */
/*@param:　redirect_url=指定URL　					*/
class Redirect_View_Main extends Action
{
	// 使用コンポーネントを受け取るため
	var $request=null;
	
	function execute()
	{
		//出力用バッファをクリア(消去)し、出力のバッファリングをオフ
		//ob_end_clean();
	
		//リクエストの内容をすべて取得し指定URLを求める
		$container =& DIContainerFactory::getContainer();
		$request =& $container->getComponent("Request");
		$parameters = $request->getParameters();
		$url = "";
		$url_parameters = "";
		$url_parameters_sub = "";
		foreach($parameters as $key => $parameter) {
			if($key == "_redirect_url") {
				$url = $parameter;
			} else if($key == "_sub_action") {
				$url_parameters .= "?".ACTION_KEY."=".$parameter;
			} else if($key != ACTION_KEY) {
				$url_parameters_sub .= "&amp;".$key."=".$parameter;
			}
		}
		$url_parameters .= $url_parameters_sub;
		
		//
		// 他サイトから取得する場合の文字列作成
		//
		$encryption =& $container->getComponent("encryptionView");
		$url_parameters .= $encryption->getRedirectUrl();
			
        $res = $this->request->getResponseHtml($url.$url_parameters);
        print $res;
		return;
	}
}
?>
