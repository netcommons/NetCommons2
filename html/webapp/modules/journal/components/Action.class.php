<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 日誌登録コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Components_Action
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var DIコンテナを保持
	 *
	 * @access	private
	 */
	var $_container = null;

	var $post_trackback_data = array();
	var $tb_result=array();

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Journal_Components_Action()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
	}

	/**
	 *
	 * 日誌削除処理
	 * @param  int    journal_id
	 * @return boolean
	 * @access public
	 */
	function delJournal($journal_id) {
		$params = array(
			"journal_id"=>intval($journal_id)
		);
    	$result = $this->_db->deleteExecute("journal", $params);
    	if($result === false) {
    		return false;
    	}

		//--新着情報関連 Start--
		$sql = "SELECT post_id ".
				" FROM {journal_post}" .
				" WHERE journal_id = ? ";
		$postIDs = $this->_db->execute($sql, $params, null, null, false);
		if ($postIDs === false) {
        	$this->_db->addError();
        	return $postIDs;
		}
		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");
		foreach ($postIDs as $post_id) {
			$result = $whatsnewAction->delete($post_id[0]);
			if($result === false) {
				return false;
			}
		}
		//--新着情報関連 End--

    	$result = $this->_db->deleteExecute("journal_post", $params);
    	if($result === false) {
    		return false;
    	}

    	$result = $this->_db->deleteExecute("journal_category", $params);
    	if($result === false) {
    		return false;
    	}

		//--URL短縮形関連 Start--
		$container =& DIContainerFactory::getContainer();
		$abbreviateurlAction =& $container->getComponent("abbreviateurlAction");
		$result = $abbreviateurlAction->deleteUrlByContents($journal_id);
		if ($result === false) {
			//return false;
		}
		//--URL短縮形関連 End--

    	return true;
	}

	function setTrackbackValues(&$trackback, $tb_rss_data, $trackback_url, $direction, $entry=null){
		// init $tb_rss_data
		$tb_rss_key = array("post_id", "blog_name", "title", "description", "link");
		foreach( $tb_rss_key as $key ){
			if(!isset($tb_rss_data[$key]) ) {
				$tb_rss_data[$key] = "";
			}
		}
		// post_id
		if( $direction == JOURNAL_TRACKBACK_TRANSMIT ){
			if( empty($entry) || !$trackback_url ) {
				return false;
			}
			$post_id = $entry['post_id'];
		}elseif( $direction == JOURNAL_TRACKBACK_RECEIVE ){
			$post_id = $tb_rss_data['post_id'];
		}else{
			return false;
		}
		// check post_id
		if( ! preg_match("/^\d+$/", $post_id) ) {
			return false;
		}

		$trackback['post_id'] = $post_id;
		$trackback['tb_url'] = $trackback_url;
		$trackback['blog_name'] = mb_convert_encoding($tb_rss_data['blog_name'], _CHARSET, "auto");
		$trackback['blog_title'] = mb_convert_encoding($tb_rss_data['title'], _CHARSET, "auto");
		$trackback['description'] = mb_convert_encoding( $tb_rss_data['description'], _CHARSET, "auto");
		$trackback['link'] = $tb_rss_data['link'];
		$trackback['direction_flag'] = $direction;

		return true;
	}

	function saveTrackback($trackback) {
		//存在しないpostにトラックバックさせない
        $params = array("post_id" => intval($trackback['post_id']));
        $post = $this->_db->selectExecute("journal_post", $params);
    	if($post === false || !isset($post[0])) {
    		return false;
    	}

    	//トラックバックの承認
    	$params = array("journal_id" => intval($post[0]['journal_id']));
    	$journal = $this->_db->selectExecute("journal", $params);
		if($journal === false || !isset($journal[0])) {
    		return false;
    	}
    	//偽造のリクエストに記事のタイトルと概要以外の情報が持ってないから、トラックバックをしたユーザが判断できない、管理者でも承認するようにする
		//$session =& $this->_container->getComponent("Session");
    	//$_auth_id = $session->getParameter("_auth_id");
		//if($_auth_id < _AUTH_CHIEF && $journal[0]['comment_agree_flag'] == _ON) {
		$session =& $this->_container->getComponent("Session");
		$time = timezone_date();
		$footer_array = array();
		$footer_array['insert_time'] = $time;
		$footer_array['insert_site_id'] = "";
		$footer_array['insert_user_id'] = "";
		$footer_array['insert_user_name'] = "";
		$footer_array['update_time'] = $time;
		$footer_array['update_site_id'] = "";
		$footer_array['update_user_id'] = "";
		$footer_array['update_user_name'] = "";
		if(!empty($trackback['user_id']) && $this->checkSite($trackback['tb_url'])) {
			//自サイトからのトラックバックだったら、user_idを付いている場合
			$userView =& $this->_container->getComponent("usersView");
			$user = $userView->getUserById($trackback['user_id']);
			$user_auth_id = $user['user_authority_id'];
			if($user_auth_id < _AUTH_CHIEF && $journal[0]['comment_agree_flag'] == _ON && $trackback['direction_flag'] == JOURNAL_TRACKBACK_RECEIVE) {
				$agree_flag = JOURNAL_STATUS_WAIT_AGREE_VALUE;
			}else {
				$agree_flag = JOURNAL_STATUS_AGREE_VALUE;
			}
			$site_id = $session->getParameter("_site_id");
			$footer_array['insert_site_id'] = $site_id;
			$footer_array['insert_user_id'] = $user['user_id'];
			$footer_array['insert_user_name'] = $user['handle'];
			$footer_array['update_site_id'] = $site_id;
			$footer_array['update_user_id'] = $user['user_id'];
			$footer_array['update_user_name'] = $user['handle'];
		}else {
			//自サイト以外のトラックバックだったら
	    	if($journal[0]['comment_agree_flag'] == _ON && $trackback['direction_flag'] == JOURNAL_TRACKBACK_RECEIVE) {
				$agree_flag = JOURNAL_STATUS_WAIT_AGREE_VALUE;
			}else {
				$agree_flag = JOURNAL_STATUS_AGREE_VALUE;
			}
		}

    	$params = array(
    		"parent_id" => intval($trackback['post_id']),
    		"tb_url" => $trackback['tb_url'],
    		"direction_flag" => $trackback['direction_flag']
    	);
    	$order_params = array(
    		"insert_time" => "DESC"
    	);
    	$tb_obj = $this->_db->selectExecute("journal_post", $params, $order_params);
    	if($tb_obj === false) {
    		return false;
    	}

    	if(empty($tb_obj)) {
    		$params = array(
	    		"journal_id" => intval($post[0]['journal_id']),
	    		"root_id" => intval($trackback['post_id']),
	    		"parent_id" => intval($trackback['post_id']),
	    		"title" => $trackback['blog_title'],
	    		"content" => $trackback['description'],
    			"agree_flag" => $agree_flag,
	    		"blog_name" => $trackback['blog_name'],
	    		"direction_flag" => $trackback['direction_flag'],
	    		"tb_url" => $trackback['tb_url'],
	    		"room_id" => $post[0]['room_id']
	    	);
	    	$params = array_merge($params, $footer_array);
	    	$post_id = $this->_db->insertExecute("journal_post", $params, false, "post_id");
	    	if($post_id === false) {
	    		return false;
	    	}

	    	//メール送信データ登録
	    	if($journal[0]['comment_agree_flag'] == _ON && $agree_flag == JOURNAL_STATUS_WAIT_AGREE_VALUE) {
	    		$session->setParameter("journal_mail_post_id", array("post_id" => $post_id, "agree_flag" => JOURNAL_STATUS_WAIT_AGREE_VALUE));
	    		$preexecute =& $this->_container->getComponent("preexecuteMain");
	    		$result = $preexecute->preExecute("journal_action_main_mail");

	    	}

	    	//--新着情報関連 Start--
	    	$params = array("journal_id" => intval($post[0]['journal_id']));
	        $journal_block = $this->_db->selectExecute("journal_block", $params, null, 1);
	    	if($journal_block === false) {
	    		return false;
	    	}
	    	$block_id = isset($journal_block[0]) ? $journal_block[0]['block_id'] : 0;
	    	$count = $this->_db->countExecute("journal_post", array("parent_id"=>$trackback['post_id'], "direction_flag != " . JOURNAL_TRACKBACK_TRANSMIT => null));
			if ($count === false) {
				return false;
			}
			if($agree_flag == JOURNAL_STATUS_AGREE_VALUE) {
				$commonMain =& $this->_container->getComponent("commonMain");
				$time = timezone_date();
				if(intval($time) < intval($post[0]['journal_date'])) {
					// 未来ならば、日誌の記事の時間をセット
					$time = $post[0]['journal_date'];
				}
				$whatsnew = array(
					"unique_id" => $trackback['post_id'],
					"title" => $post[0]['title'],
					"description" => $post[0]['content'],
					"action_name" => "journal_view_main_detail",
					"parameters" => "post_id=". $trackback['post_id'] . "&trackback_flag=1&block_id=".$block_id."#".$commonMain->getTopId($block_id),
					"count_num" => $count,
					"child_flag" => _ON,
					"room_id" => $post[0]['room_id'],
					"insert_time" => $time,
					"insert_user_id" => $post[0]['insert_user_id'],
					"insert_user_name" => $post[0]['insert_user_name']
				);
				$whatsnewAction =& $this->_container->getComponent("whatsnewAction");
				$result = $whatsnewAction->auto($whatsnew, _ON);
				if($result === false) {
					return false;
				}
			}else {
				if($count == 0) {
					$result = $whatsnewAction->delete($trackback['post_id'], _ON);
				}
			}

			//--新着情報関連 End--

    	}else {
    		$params = array(
	    		"blog_name" => $trackback['blog_name'],
	    		"title" => $trackback['blog_title'],
	    		"content" => $trackback['description'],
    			"agree_flag" => $agree_flag
	    	);

	    	$where_params = array(
	    		"post_id" => intval($tb_obj[0]['post_id']),
	    		"tb_url" => $trackback['tb_url'],
	    		"direction_flag" => $trackback['direction_flag']
	    	);
	    	$result = $this->_db->updateExecute("journal_post", $params,  $where_params, true);
	    	if($result === false) {
	    		return false;
	    	}
    	}

    	return true;
    }

    function createTrackbackData($params = null, $blog_name, $blog_url){
    	if(empty($params)) {
    		return true;
    	}
		$data = array();
		//$data['action'] = "journal_action_main_trackback";
		$data['url'] = $blog_url;
		$data['title'] = mb_convert_encoding($params['title'], _CHARSET, "auto");
		$data['excerpt'] = mb_convert_encoding($params['content'], _CHARSET, "auto");
		$data['blog_name'] = mb_convert_encoding($blog_name, _CHARSET, "auto");

		$this->post_trackback_data = $data;

		return true;
	}

	function getTrackbackUrl($post, $old_trackback_url) {
		// $post_trackback_urls 作成
		$trackback_url_array = array();
		if(!$old_trackback_url){
			$trackback_url_add_array = explode("\n", trim($post['tb_url']));
			$trackback_url_del_array = array();
		}else{
			$old_trackbackurl_array =  array();
			$new_trackbackurl_array =  array();
			foreach(explode("\n", trim($old_trackback_url)) as $key=>$value){
				if($value = trim($value))
					array_push($old_trackbackurl_array, $value);
			}
			foreach(explode("\n", trim($post['tb_url'])) as $key=>$value){
				if($value = trim($value))
					array_push($new_trackbackurl_array, $value);
			}
			$trackback_url_add_array = array_unique(array_diff($new_trackbackurl_array, $old_trackbackurl_array));
			$trackback_url_del_array = array_unique(array_diff($old_trackbackurl_array, $new_trackbackurl_array));
		}

		// URLチェック
		if(isset($trackback_url_del_array)){
			// 削除URL
			foreach( $trackback_url_del_array as $key=>$url ){
				if(!$this->checkTrackbackUrl($url)){
					unset($trackback_url_del_array[$key]);
				}else{
					$trackback_url_array[$url] = "del";
				}
			}
		}
		if(isset($trackback_url_add_array)){
			// 追加URL
			foreach($trackback_url_add_array as $key=>$url){
				if(!$this->checkTrackbackUrl($url)) {
					unset($trackback_url_add_array[$key]);
				}else{
					$trackback_url_array[$url] = "add";
				}
			}
		}
		return $trackback_url_array;
	}

	function checkTrackbackUrl($tb_url){
		if($tb_url){
			$url_array = parse_url($tb_url);
			if( isset($url_array['scheme']) && $url_array['scheme'] && ($url_array['scheme']=='http' || $url_array['scheme']=='https') && $url_array['host'] && $url_array['path'] ){
				return true;
			}else{
				return false;
			}
		}
	}

	function weblogPostTrackback($trackback_url){
		require_once "Net/TrackBack.php";
		$user_agent = "NetCommons Journal TrackBack System";
		if($this->checkSite($trackback_url)) {
			$session =& $this->_container->getComponent("Session");
    		$user_id = $session->getParameter("_user_id");
			$trackback_url = $trackback_url."&user_id=".$user_id;
		}
		$return_from_tb_server = Net_TrackBack::sendPing($trackback_url, $this->post_trackback_data, $user_agent, 'utf-8');

		if( $return_from_tb_server === true ){
			$this->tb_result[$trackback_url] = true;
			return true;
		}else{
			$this->tb_result[$trackback_url] = false;
			return false;
		}
	}

	function getRSSFromTrackbackURL($tb_url){
		require_once "Net/TrackBack.php";
		$user_agent = "NetCommons Journal module";
		if(!empty($tb_url)){
			$tb_url = trim( $tb_url, "?" );
			$url_array = parse_url( $tb_url );
			if( $url_array['scheme']=='http' && $url_array['host'] && $url_array['path'] ){
		        $params = array('method' => HTTP_REQUEST_METHOD_GET);
		        if( preg_match( "/\?/", $url_array['path'] ) ){
					$tb_url .= "&__mode=rss";
				}else{
					$tb_url .= "?__mode=rss";
				}
				$req =& new HTTP_Request($tb_url, $params);
		        $req->addHeader('User-Agent', $this->user_agent);
				$req->sendRequest();
				$request_code = $req->getResponseCode();

				if( $request_code == "200" ){
					return $req->getResponseBody();
				}
			}
		}
		return false;
	}

	function parseXML($xml){
		require_once "XML/Unserializer.php";
		$data = array("encoding" => "" );
		if( trim($xml) ){
			foreach( explode( "\n", $xml ) as $xml_line){
				if( preg_match( "/<\?xml.+encoding=[\"\']+([^\"]+)[\"\']+\?>/i", $xml_line,$match) )
					$encoding = strtoupper( $match[1] );
				break;
			}
			if( empty( $encoding )&& function_exists('mb_detect_encoding') ){
				$encoding = mb_detect_encoding($xml);
			}
			$Unserializer = &new XML_Unserializer();

			if( $status = $Unserializer->unserialize($xml) ){
				$unserialize_data = $Unserializer->getUnserializedData();
				$data['encoding'] = $encoding;
				if (!empty($unserialize_data)) {
					$data['title'] = $unserialize_data['rss']['channel']['title'];
					$data['description'] = $unserialize_data['rss']['channel']['description'];
					$data['link'] = $unserialize_data['rss']['channel']['link'];
				} else {
					$data['title'] = '';
					$data['description'] = '';
					$data['link'] = '';
				}
				return $data;
			}
		}
		return false;
	}

	function removeTrackback($post_id, $tb_url="", $direction="") {
		$params = array(
			"root_id" => intval($post_id)
		);
		if($tb_url) {
			array_merge($params, array("tb_url" => $tb_url));
		}
		if($direction) {
			array_merge($params, array("direction_flag" => $direction));
		}
		$result = $this->_db->deleteExecute("journal_post", $params);
		if($result){
			$this->tb_result[$tb_url] = true;
			return true;
		}else{
			$this->tb_result[$tb_url] = false;
			return false;
		}
    }

    function getWeblogMsg(){
    	$trackback_result_msg = "";
		if(!empty($this->tb_result)) {
			foreach($this->tb_result as $tb_url=>$result ){
				if(!$result) {
					$trackback_result_msg = JOURNAL_TRACKBACK_MESSAGE_FAILED;
				}
				//$trackback_result_msg .= $tb_url . "=&gt;" . $result . ".\n";
			}
		}
		return $trackback_result_msg;
	}

	/**
	 * 新着情報にセットする
	 *
     * @return bool
	 * @access	public
	 */
	function setWhatsnew($post_id) {
		$params = array("post_id" => $post_id);
    	$posts = $this->_db->selectExecute("journal_post", $params);

		if (empty($posts)) {
			return false;
		}

		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");

		if ($posts[0]["status"] == JOURNAL_POST_STATUS_REREASED_VALUE && $posts[0]["agree_flag"] == JOURNAL_STATUS_AGREE_VALUE) {
			$whatsnew = array(
				"unique_id" => $post_id,
				"title" => $posts[0]["title"],
				"description" => $posts[0]["content"]."<br /><br />".$posts[0]["more_content"],
				"action_name" => "journal_view_main_detail",
				"parameters" => "post_id=". $post_id . "&comment_flag=1",
				"insert_time" => $posts[0]["journal_date"],
				"insert_user_id" => $posts[0]["insert_user_id"],
				"insert_user_name" => $posts[0]["insert_user_name"],
				"update_time" => $posts[0]["journal_date"],
				"child_update_time" => $posts[0]["journal_date"]
			);
			$result = $whatsnewAction->auto($whatsnew);
			if ($result === false) {
				return false;
			}

			// journal_dateが、未来かどうかを判断し、
			// 未来ならば、コメントの新着も未来に書き換え
			// 未来でなければ、コメントのデータのMAXをとり、
			// その日付に更新
			$where_params = array(
				"root_id" => $post_id
			);
			$insert_time = $this->_db->maxExecute("journal_post", "insert_time", $where_params);
			if(isset($insert_time)) {
				// コメントあり
				$time = timezone_date();
				$journal_date = $posts[0]["journal_date"];
				$whatsnew['child_flag'] = _ON;
				if(intval($journal_date) < intval($time)) {
					// 過去
					$whatsnew['insert_time'] = $journal_date;
					$whatsnew['child_update_time'] = $insert_time;
				}
				$result = $whatsnewAction->auto($whatsnew, _OFF);
				if ($result === false) {
					return false;
				}
			}
		} else {
			$result = $whatsnewAction->delete($post_id);
			if($result === false) {
				return false;
			}
		}
		return true;
	}

	/**
	 * 新着情報にセットする(コメント)
	 *
     * @return bool
	 * @access	public
	 */
	function setCommentWhatsnew($comment_id) {
		$params = array("post_id" => $comment_id);
    	$comment = $this->_db->selectExecute("journal_post", $params);
		if (empty($comment)) {
			return false;
		}
		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");
		$count = $this->_db->countExecute("journal_post", array("parent_id"=>$comment[0]['parent_id'], "direction_flag != " . JOURNAL_TRACKBACK_TRANSMIT => null));
		if ($count === false) {
			return false;
		}

		if ($comment[0]["agree_flag"] == JOURNAL_STATUS_AGREE_VALUE) {
			$journal_post = $this->_db->selectExecute("journal_post", array("post_id"=>$comment[0]['parent_id']));
			if ($journal_post === false && !isset($journal_post[0])) {
				return false;
			}
			$time = timezone_date();
			if(intval($time) < intval($journal_post[0]['journal_date'])) {
				// 未来ならば、日誌の記事の時間をセット
				$time = $journal_post[0]['journal_date'];
			}

			$whatsnew = array(
				"unique_id" => $comment[0]['parent_id'],
				"title" => $journal_post[0]['title'],
				"description" => $journal_post[0]['content'],
				"action_name" => "journal_view_main_detail",
				"parameters" => "post_id=". $comment[0]['parent_id'] . "&comment_flag=1",
				"count_num" => $count,
				"child_flag" => _ON,
				"child_update_time" => $time,
				"insert_time" => $journal_post[0]['journal_date'],
				"insert_user_id" => $journal_post[0]['insert_user_id'],
				"insert_user_name" => $journal_post[0]['insert_user_name']
			);
			$result = $whatsnewAction->auto($whatsnew);
			if($result === false) {
				return false;
			}
		}else {
			if($count == 0) {
				$result = $whatsnewAction->delete($comment[0]['parent_id'], _ON);
			}
		}

		return true;
	}

	/**
	 * 投稿回数をセットする
	 *
     * @return bool
	 * @access	public
	 */
	function setMonthlynumber($edit_flag, $status, $agree_flag=null, $before_post=null) {
		$monthlynumberAction =& $this->_container->getComponent("monthlynumberAction");
		$session =& $this->_container->getComponent("Session");

		// --- 投稿回数更新 ---
		if ($status == JOURNAL_POST_STATUS_REREASED_VALUE  && $agree_flag == JOURNAL_STATUS_AGREE_VALUE
				&& (!$edit_flag
					|| $before_post['status'] == JOURNAL_POST_STATUS_BEFORE_REREASED_VALUE
					|| $before_post['agree_flag'] == JOURNAL_STATUS_WAIT_AGREE_VALUE)) {
			if (!$edit_flag) {
				$params = array(
					"user_id" => $session->getParameter("_user_id")
				);
			} else {
				$params = array(
					"user_id" => $before_post['insert_user_id']
				);
			}
			if (!$monthlynumberAction->incrementMonthlynumber($params)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * 自サイトかどうかのチェック
	 *
     * @return bool
	 * @access	public
	 */
	function checkSite($url) {
		if(substr($url, 0, strlen(BASE_URL)) != BASE_URL || strpos($url, "user_id") !== false) {
			return false;
		}
		return true;
	}

	/**
	 * トラックバックの処理
	 *
     * @return bool
	 * @access	public
	 */
	function setTrackBack($journal_obj, $post_id, $params) {
		$trackback_result = "";
		$journalView =& $this->_container->getComponent("journalView");
		$trackbacks = $journalView->getChildDetail($post_id, JOURNAL_TRACKBACK_TRANSMIT);
    	$old_tb_url = "";
    	if (!empty($trackbacks)) {
			foreach ($trackbacks as $key=>$val) {
				$old_tb_url .= $val['tb_url']. "\n";
			}
		}
		if ($params['tb_url'] != "" || $old_tb_url != "") {
			// 送信するブログ名称、URLを設定
			$pre_blogname = array("{X-MODULE_NAME}", "{X-USER}", "{X-SITE_NAME}");
			$configView =& $this->_container->getComponent("configView");
			$config =& $configView->getConfigByCatid(_SYS_CONF_MODID, _GENERAL_CONF_CATID);
			$session =& $this->_container->getComponent("Session");
			$ext_blogname = array(JOURNAL_NAME, $session->getParameter("_handle"), $config['sitename']['conf_value']);
			$blog_name = str_replace($pre_blogname, $ext_blogname, $journal_obj['transmit_blogname']);
	        $blog_url = BASE_URL."/?action=pages_view_main&active_action=journal_view_main_detail&post_id=".$post_id."&block_id=".$journal_obj['block_id']."#". $session->getParameter("_id");
	        $trackback_urls = $this->getTrackbackUrl($params, $old_tb_url);
	        foreach ($trackback_urls as $url=>$type) {
	            if ($type=="add") {
	                $this->createTrackbackData($params, $blog_name, $blog_url);
	                if ($this->weblogPostTrackback($url)) {
	                    //$response_xml = $this->journalAction->getRSSFromTrackbackURL($url);
	                    //$tb_rss_data = $this->journalAction->parseXML($response_xml);
	                    $trackback_param = array("journal_id" => $journal_obj['journal_id']);
	                    $tb_rss_data = array();
	                    $params['post_id'] = $post_id;
	                    $this->setTrackbackValues($trackback_param, $tb_rss_data, $url, JOURNAL_TRACKBACK_TRANSMIT, $params);
	                    $this->saveTrackback($trackback_param);
	                }
	            } elseif ($type=="del") {
	                $this->removeTrackback($post_id, $blog_url, JOURNAL_TRACKBACK_TRANSMIT);
	            }
	        }

	        //メッセージ
			$trackback_result = $this->getWeblogMsg();
		}

		return $trackback_result;
	}
}
?>
