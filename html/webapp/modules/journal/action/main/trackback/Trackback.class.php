<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * トラックバック処理
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Journal_Action_Main_Trackback extends Action
{
	// リクエストパラメータを受け取るため
	var $post_id = null;
	var $title = null;
	var $url = null;
	var $excerpt = null;
	var $blog_name = null;
	var $user_id = null;
	
    // 使用コンポーネントを受け取るため
    var $journalAction = null;
    var $db = null;
    var $request = null;
 
    // 値をセットするため
    var $block_id = null;
    
    /**
     * [[機能説明]]
     *
     * @access  public
     */
    function execute()
    {
    	require_once "Net/TrackBack.php";
    	// check trackback ID
		if(!preg_match("/^\d+$/" , $this->post_id)) {
			exit(Net_TrackBack::getPingXML(false , "Invalid trackback ID")); 
		}
		
    	$params = array("post_id" => $this->post_id);
		$post = $this->db->selectExecute("journal_post", $params);
		if($post === false || !isset($post[0])) {
			exit(Net_TrackBack::getPingXML(false , "Invalid trackback ID")); 
		}

		if (!$this->journalAction->checkSite($this->url)) {
			$params = array("page_id" => $post[0]["room_id"]);
			$pages = $this->db->selectExecute("pages", $params);
			if ($pages === false || !isset($pages[0])) {
				exit(Net_TrackBack::getPingXML(false , "Invalid trackback ID")); 
			}
			$space_type = $pages[0]["space_type"];
			$private_flag = $pages[0]["private_flag"];
			$default_entry_flag = $pages[0]["default_entry_flag"];
			if($space_type != _SPACE_TYPE_PUBLIC && !($private_flag == _ON && $default_entry_flag == _ON)) {
				exit(Net_TrackBack::getPingXML(false , "Invalid authority")); 
			}
		}

		$params = array("journal_id" => $post[0]['journal_id']);
		$journal = $this->db->selectExecute("journal", $params);
		if ( $journal === false || !isset($journal[0])) {
			exit(Net_TrackBack::getPingXML(false , "Invalid trackback ID")); 
		}
		$trackback['journal_id'] = $post[0]['journal_id'];

		if(!Net_TrackBack::isPing()) {
			//ブロックID取得処理
			$params = array("journal_id" => $post[0]['journal_id']);
			$block = $this->db->selectExecute("journal_block", $params);
			if($block === false || !isset($block[0])) {
				exit(Net_TrackBack::getPingXML(false , "Invalid trackback ID")); 
			}
			$this->request->setParameter("post_id", $this->post_id);
			$this->request->setParameter("block_id", $block[0]['block_id']);
			
			$link = BASE_URL. INDEX_FILE_NAME.
									"?action=". DEFAULT_ACTION .
									"&active_action=journal_view_main_detail".
									"&post_id=". $this->post_id.
									"&block_id=". $block[0]['block_id'].
									"#_". $block[0]['block_id'];
			// rss mode
			if(isset($_GET['__mode']) && $_GET['__mode'] == "rss") {
				if($post[0]['direction_flag'] === JOURNAL_TRACKBACK_RECEIVE) {
					$trackback_data_array[] = array(
						"title" => $post[0]['title'],
						"url" => $post[0]['link'],
						"excerpt" => $post[0]['content']
					);
					$xml = Net_TrackBack::toRSSXML($trackback_data_array, $post[0]['title'], $link, $post[0]['content'], "ja");
					$xml = encoding_set( $xml , "UTF-8");
					exit( $xml );
				}
			}else{
				header("Location: $link");
			}
		}else {
			$trackback_receive_flag = $journal[0]['trackback_receive_flag'];
			if($trackback_receive_flag != _ON) {
				//受信禁止の場合
				exit(Net_TrackBack::getPingXML(false , "Invalid trackback ID")); 
			}
			// save trackback
			$post_id = intval($this->post_id);
			$data['blog_name'] = $this->blog_name;
			$data['title'] = $this->title;
			$data['description'] = $this->excerpt;
			$data['post_id'] = $post_id;
			$data['link'] = false;  // surpress PHP warnig

			$trackback = array();
			if(!empty($this->user_id)) {
				$trackback['user_id'] = $this->user_id;
			}
			if($this->journalAction->setTrackbackValues($trackback, $data, $this->url, JOURNAL_TRACKBACK_RECEIVE)){
				if($this->journalAction->saveTrackback($trackback)){
					exit(Net_TrackBack::getPingXML(true));
				}
			}
		}
    }
}
?>