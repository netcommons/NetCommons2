<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フォトアルバムデータ登録コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Photoalbum_Components_Action
{
	/**
	 * @var DBオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_db = null;

	/**
	 * @var Requestオブジェクトを保持
	 *
	 * @access	private
	 */
	var $_request = null;

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Photoalbum_Components_Action()
	{
		$container =& DIContainerFactory::getContainer();
		$this->_db =& $container->getComponent("DbObject");
		$this->_request =& $container->getComponent("Request");
	}

	/**
	 * フォトアルバムデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setPhotoalbum()
	{
		$params = array(
			"photoalbum_name" => $this->_request->getParameter("photoalbum_name"),
			"album_authority" => intval($this->_request->getParameter("album_authority")),
			"album_new_period" => intval($this->_request->getParameter("album_new_period"))
		);

		$photoalbumID = $this->_request->getParameter("photoalbum_id");
		if (empty($photoalbumID)) {
			$result = $this->_db->insertExecute("photoalbum", $params, true, "photoalbum_id");
		} else {
			$params["photoalbum_id"] = $photoalbumID;
			$result = $this->_db->updateExecute("photoalbum", $params, "photoalbum_id", true);
		}
		if (!$result) {
			return false;
		}

        if (!empty($photoalbumID)) {
        	return true;
        }

		$photoalbumID = $result;
		$this->_request->setParameter("photoalbum_id", $photoalbumID);
        if (!$this->setBlock()) {
			return false;
		}

		return true;
	}

	/**
	 * フォトアルバムデータを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deletePhotoalbum()
	{
		$params = array(
			"photoalbum_id" => $this->_request->getParameter("photoalbum_id")
		);

    	if (!$this->_db->deleteExecute("photoalbum_block", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("photoalbum_comment", $params)) {
    		return false;
    	}

		$sql = "SELECT photo_id, upload_id ".
					"FROM {photoalbum_photo} ".
					"WHERE photoalbum_id = ?";
		$photos = $this->_db->execute($sql, $params);
		if ($photos === false) {
			$this->_db->addError();
			return false;
		}
		if (!$this->deletePhotoFile($photos)) {
    		return false;
    	}
    	if (!$this->_db->deleteExecute("photoalbum_photo", $params)) {
    		return false;
    	}

		$sql = "SELECT album_id, upload_id ".
					"FROM {photoalbum_album} ".
					"WHERE photoalbum_id = ?";
		$albums = $this->_db->execute($sql, $params);
		if ($albums === false) {
			$this->_db->addError();
			return false;
		}
		$container =& DIContainerFactory::getContainer();
		$whatsnewAction =& $container->getComponent("whatsnewAction");
		$commonMain =& $container->getComponent("commonMain");
		$uploads =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");
		foreach ($albums as $album) {
			if (!$whatsnewAction->delete($album['album_id'])) {
				return false;
			}
			if (!$uploads->delUploadsById($album["upload_id"])) {
				return false;
			}
		}
    	if (!$this->_db->deleteExecute("photoalbum_album", $params)) {
    		return false;
    	}

    	if (!$this->_db->deleteExecute("photoalbum", $params)) {
    		return false;
    	}

		return true;
	}

	/**
	 * アップロードファイルを削除する
	 *
	 * @param  string	$photos	写真データ配列
     * @return boolean	true or false
	 * @access	public
	 */
	function deletePhotoFile($photos)
	{
		if (empty($photos)) {
			return true;
		}

		$container =& DIContainerFactory::getContainer();
		$commonMain =& $container->getComponent("commonMain");
		$uploads =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");
		$photoIDs = array();
		foreach ($photos as $photo) {
			if (!$uploads->delUploadsById($photo["upload_id"])) {
				return false;
			}

			$photoIDs[] = $photo["photo_id"];
		}

		$sql = "DELETE FROM {photoalbum_user_photo} ".
					"WHERE photo_id IN (". implode(",", $photoIDs). ")";
		if (!$this->_db->execute($sql)) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * フォトアルバム用ブロックデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setBlock()
	{
		$blockID = $this->_request->getParameter("block_id");

		$params = array($blockID);
		$sql = "SELECT block_id ".
				"FROM {photoalbum_block} ".
				"WHERE block_id = ?";
		$blockIDs = $this->_db->execute($sql, $params);
		if ($blockIDs === false) {
			$this->_db->addError();
			return false;
		}

		$params = array(
			"block_id" => $blockID,
			"photoalbum_id" => $this->_request->getParameter("photoalbum_id")
		);

		$container =& DIContainerFactory::getContainer();
		$actionChain =& $container->getComponent("ActionChain");
		$actionName = $actionChain->getCurActionName();
		if (!empty($blockIDs) &&
				$actionName == "photoalbum_action_edit_current") {
			if (!$this->_db->updateExecute("photoalbum_block", $params, "block_id", true)) {
				return false;
			}

			return true;
		}

		if ($actionName == "photoalbum_action_edit_current") {
			$photoalbumView =& $container->getComponent("photoalbumView");
			$photoalbum = $photoalbumView->getDefaultPhotoalbum();
		}
		if ($actionName == "photoalbum_action_edit_entry") {
			$this->_request->setParameter("photoalbum_id", null);
			$photoalbum = $this->_request->getParameter("photoalbum");
		}
		if (!empty($photoalbum)) {
			$this->_request->setParameter("display", $photoalbum["display"]);
			$this->_request->setParameter("slide_type", $photoalbum["slide_type"]);
			$this->_request->setParameter("slide_time", $photoalbum["slide_time"]);
			$this->_request->setParameter("size_flag", $photoalbum["size_flag"]);
			$this->_request->setParameter("width", $photoalbum["width"]);
			$this->_request->setParameter("height", $photoalbum["height"]);
			$this->_request->setParameter("album_visible_row", $photoalbum["album_visible_row"]);
		}

		$params["display"] = intval($this->_request->getParameter("display"));
		$params["slide_type"] = intval($this->_request->getParameter("slide_type"));
		$params["slide_time"] = intval($this->_request->getParameter("slide_time"));
		$params["size_flag"] = intval($this->_request->getParameter("size_flag"));
		$params["album_visible_row"] = intval($this->_request->getParameter("album_visible_row"));

		if ($params["display"] == PHOTOALBUM_DISPLAY_SLIDE) {
			$params["display_album_id"] = intval($this->_request->getParameter("display_album_id"));
		} else {
			$params["display_album_id"] = 0;
		}
		if ($params["size_flag"] == _ON
				|| empty($blockIDs)) {
			$params["width"] = intval($this->_request->getParameter("width"));
			$params["height"] =  intval($this->_request->getParameter("height"));
		}

		if (!empty($blockIDs)) {
			$result = $this->_db->updateExecute("photoalbum_block", $params, "block_id", true);
		} else {
			$result = $this->_db->insertExecute("photoalbum_block", $params, true);
		}
        if (!$result) {
			return false;
		}

		$session =& $container->getComponent("Session");
		$session->removeParameter("photoalbum_visible_row". $blockID);

		return true;
	}

	/**
	 * アルバムデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setAlbum()
	{
		$albumID = $this->_request->getParameter("album_id");
		
		if (empty($albumID)) {
			$params = array(
				"photoalbum_id" => $this->_request->getParameter("photoalbum_id")
			);
    		$count = $this->_db->countExecute("photoalbum_album", $params);
			$albumSequence = $count + 1;
		} else {
			$params["album_id"] = $albumID;
			$modifier_album = $this->_db->selectExecute("photoalbum_album", $params);
			if (!isset($modifier_album[0])) {
				return false;
			}
		}

		$container =& DIContainerFactory::getContainer();
		$photoalbumView =& $container->getComponent("photoalbumView");

		$uploadID = intval($this->_request->getParameter("upload_id"));
		$albumJacket = $this->_request->getParameter("album_jacket");
		if (empty($uploadID)) {
			$imageSize = $photoalbumView->getImageSize($albumJacket);
		} else {
			$imageSize = $photoalbumView->getImageSize($uploadID);
		}

		$params = array(
			"album_name" => $this->_request->getParameter("album_name"),
			"upload_id" => $uploadID,
			"album_jacket" => $albumJacket,
			"width" => $imageSize[0],
			"height" => $imageSize[1],
			"album_description" => $this->_request->getParameter("album_description"),
			"photo_new_period" => intval($this->_request->getParameter("photo_new_period")),
			"vote_flag" => intval($this->_request->getParameter("vote_flag")),
			"comment_flag" => intval($this->_request->getParameter("comment_flag")),
			"public_flag" => intval($this->_request->getParameter("public_flag"))
		);
		
		$blockID = $this->_request->getParameter("block_id");
		if (empty($albumID)) {
			$container =& DIContainerFactory::getContainer();
			$session =& $container->getComponent("Session");
			
			$session->setParameter("photoalbum_album_sort". $blockID, PHOTOALBUM_ALBUM_SORT_NEW);
			$session->removeParameter("photoalbum_page_number". $blockID);
		}

		$insertFlag = false;
		if (empty($albumID)) {
			$params["photoalbum_id"] =  $this->_request->getParameter("photoalbum_id");
			$params["photo_upload_time"] = timezone_date();
			$params["album_sequence"] = $albumSequence;
			$result = $this->_db->insertExecute("photoalbum_album", $params, true, "album_id");
			$albumID = $result;
			$insertFlag = true;
		} else {
			$params["album_id"] = $albumID;
			$result = $this->_db->updateExecute("photoalbum_album", $params, "album_id", true);
		
			//--新着情報関連 Start--
			// 非公開から公開した場合を考え、既に写真があった場合
			// 新着に登録
			// アルバム登録時は、写真が一枚もないので
			// 新着には登録しない
			$whatsnewAction =& $container->getComponent("whatsnewAction");
			if($modifier_album[0]['photo_count'] != 0 && $modifier_album[0]['public_flag'] == _OFF && $params['public_flag'] == _ON) {
				
				$commonMain =& $container->getComponent("commonMain");
				$id = $commonMain->getTopId($blockID, $this->_request->getParameter("module_id"), "");
				$photoalbum_photo_params = array(
					"album_id" => $albumID,
					"photoalbum_id" => $this->_request->getParameter("photoalbum_id")
				);
				$photoalbum_photo_order_params = array(
					"insert_time" => "DESC"
				);
				$photoalbum_photo = $this->_db->selectExecute("photoalbum_photo", $photoalbum_photo_params, null, 1);
				if(!isset($photoalbum_photo[0])) {
					return false;
				}
				
				$whatsnew = array(
					"unique_id" => $albumID,
					"title" => $params["album_name"],
					"description" => $params["album_description"],
					"action_name" => "photoalbum_view_main_init",
					"parameters" => "album_id=". $albumID."&block_id=".$blockID."#photoalbum_album".$id."_".$albumID,
					"count_num" => $modifier_album[0]['photo_count'],
					"insert_time" => $photoalbum_photo[0]["insert_time"],
					"insert_user_id" => $photoalbum_photo[0]["insert_user_id"],
					"insert_user_name" => $photoalbum_photo[0]["insert_user_name"]
				);
				$result = $whatsnewAction->auto($whatsnew, _ON);
				if ($result === false) {
					return false;
				}	
			} else if($modifier_album[0]['photo_count'] != 0 && $modifier_album[0]['public_flag'] == _ON && $params['public_flag'] == _OFF) {
				$result = $whatsnewAction->delete($albumID);
				if($result === false) {
					return false;
				}
			}
			//--新着情報関連 End--
			
		}
		if (!$result) {
			return false;
		}

		return true;
	}

	/**
	 * アルバムデータを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteAlbum()
	{
		$params = array(
			"album_id" => $this->_request->getParameter("album_id")
		);

    	if (!$this->_db->deleteExecute("photoalbum_comment", $params)) {
    		return false;
    	}

		$sql = "SELECT photo_id, upload_id ".
					"FROM {photoalbum_photo} ".
					"WHERE album_id = ?";
		$photos = $this->_db->execute($sql, $params);
		if ($photos === false) {
			$this->_db->addError();
			return false;
		}
		if (!$this->deletePhotoFile($photos)) {
    		return false;
    	}
    	if (!$this->_db->deleteExecute("photoalbum_photo", $params)) {
    		return false;
    	}

		$sql = "SELECT upload_id, album_sequence ".
					"FROM {photoalbum_album} ".
					"WHERE album_id = ?";
		$albums = $this->_db->execute($sql, $params);
		if ($albums === false) {
			$this->_db->addError();
			return false;
		}
		$container =& DIContainerFactory::getContainer();
		$commonMain =& $container->getComponent("commonMain");
		$uploads =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");

		if (!$uploads->delUploadsById($albums[0]["upload_id"])) {
			return false;
		}
    	if (!$this->_db->deleteExecute("photoalbum_album", $params)) {
    		return false;
    	}

		$params = array(
			"photoalbum_id" => $this->_request->getParameter("photoalbum_id")
		);
		$sequenceParam = array(
			"album_sequence" => $albums[0]["album_sequence"]
		);
		if (!$this->_db->seqExecute("photoalbum_album", $params, $sequenceParam)) {
			return false;
		}

		$container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");

		$blockID = $this->_request->getParameter("block_id");
		$session->removeParameter("photoalbum_page_number". $blockID);

		//--新着情報関連 Start--
		$whatsnewAction =& $container->getComponent("whatsnewAction");
		$result = $whatsnewAction->delete($this->_request->getParameter("album_id"));
		if($result === false) {
			return false;
		}
		//--新着情報関連 End--

		return true;
	}

	/**
	 * アップロードされた写真データを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function upload()
	{
		$albumID = $this->_request->getParameter("album_id");
		$photoalbumID = $this->_request->getParameter("photoalbum_id");
		$photoSequence = 0;

		$container =& DIContainerFactory::getContainer();
		$commonMain =& $container->getComponent("commonMain");
		$uploadsAction =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");
		$files = $uploadsAction->uploads();

		$params = array(
			"album_id" => $this->_request->getParameter("album_id")
		);
		$maxPhotoID = $this->_db->maxExecute("photoalbum_photo", "photo_id", $params);

    	foreach($files as $file) {
    		if($file['error_mes'] != "") {
    			continue;
    		}
	    	$imageSize = getimagesize(FILEUPLOADS_DIR. "photoalbum/". $file["physical_file_name"]);
	    	if (empty($imageSize)) {
	    		return false;
	    	}
	    	$photoSequence++;

	    	$params = array(
				"album_id" => $albumID,
				"photoalbum_id" => $photoalbumID,
				"photo_name" => str_replace(".". $file["extension"], "", $file["file_name"]),
				"photo_sequence" => $photoSequence,
				"upload_id" => $file["upload_id"],
				"photo_path" => "?". ACTION_KEY. "=". $file["action_name"]. "&upload_id=". $file["upload_id"],
				"width" => $imageSize[0],
				"height" => $imageSize[1]
			);
	    	if (!$this->_db->insertExecute("photoalbum_photo", $params, true, "photo_id")) {
	    		$uploadsAction->delUploadsById($file["upload_id"]);
	    		return false;
	    	}
    	}
    	
    	$params = array(
    		$albumID,
    		$maxPhotoID
    	);
		$sql = "UPDATE {photoalbum_photo} ".
				"SET photo_sequence = photo_sequence + ". $photoSequence. " ".
				"WHERE album_id = ? ".
				"AND photo_id <= ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		$params = array(
			"album_id" => $this->_request->getParameter("album_id")
		);
		$photoSequence = $this->_db->maxExecute("photoalbum_photo", "photo_sequence", $params);
		if (empty($photoSequence)) {
			return false;
		}
		$params = array(
			"album_id" => $albumID,
			"photo_count" => $photoSequence,
			"photo_upload_time" => timezone_date()
		);
		if (!$this->_db->updateExecute("photoalbum_album", $params, "album_id", true)) {
    		return false;
    	}
    	
    	//--新着情報関連 Start--
    	
    	$whatsnewAction =& $container->getComponent("whatsnewAction");
		$commonMain =& $container->getComponent("commonMain");
		$photoalbum_block = $this->_db->selectExecute("photoalbum_block", array("photoalbum_id" => $photoalbumID), null, 1);
		if(isset($photoalbum_block[0])) {			
	    	$album_id = $this->_request->getParameter("album_id");
	    	$photoalbum_album = $this->_db->selectExecute("photoalbum_album", array("album_id" => $album_id));
			if (!isset($photoalbum_album[0])) {
				return false;
			}
	    	if($photoalbum_album[0]['public_flag'] == _ON) {
	    		$id = $commonMain->getTopId($photoalbum_block[0]['block_id'], $this->_request->getParameter("module_id"), "");
	    		$whatsnew = array(
					"unique_id" => $album_id,
					"title" => $photoalbum_album[0]['album_name'],
					"description" => $photoalbum_album[0]['album_description'],
					"action_name" => "photoalbum_view_main_init",
					"parameters" => "album_id=". $albumID."&block_id=".$photoalbum_block[0]['block_id']."#photoalbum_album".$id."_".$album_id,
					"count_num" => $photoSequence,
					"insert_time" => timezone_date()
				);
				$result = $whatsnewAction->auto($whatsnew, _ON);
				if($result === false) {
					return false;
				}
	    	}
	    	
		}
		
		//--新着情報関連 End--
		

    	return true;
	}

	/**
	 * 写真表示順データを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setPhotoSequence() {
		$photoIDs = $this->_request->getParameter("photo_ids");
    	$sequence = 1;
    	if(is_array($photoIDs)) {
	        foreach($photoIDs as $photoID) {
		    	$params = array(
		    		"photo_id" => $photoID,
					"photo_sequence" => $sequence
				);
		    	if (!$this->_db->updateExecute("photoalbum_photo", $params, "photo_id", true)) {
		    		return false;
		    	}
		    	$sequence++;
	        }
    	}
    	return true;
	}

	/**
	 * 写真データを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setPhoto() {
    	$params = array(
    		"photo_id" => $this->_request->getParameter("photo_id"),
			"photo_name" => $this->_request->getParameter("photo_name"),
			"photo_description" => $this->_request->getParameter("photo_description")
		);
    	if (!$this->_db->updateExecute("photoalbum_photo", $params, "photo_id", true)) {
    		return false;
    	}

    	return true;
	}

	/**
	 * 写真データを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deletePhoto()
	{
		$params = array(
			"photo_id" => $this->_request->getParameter("photo_id")
		);

    	if (!$this->_db->deleteExecute("photoalbum_comment", $params)) {
    		return false;
    	}

		$sql = "SELECT photo_id, album_id, upload_id, photo_sequence ".
				"FROM {photoalbum_photo} ".
				"WHERE photo_id = ?";
		$photos = $this->_db->execute($sql, $params);
		if ($photos === false) {
			$this->_db->addError();
			return false;
		}
		if (!$this->deletePhotoFile($photos)) {
    		return false;
    	}
    	if (!$this->_db->deleteExecute("photoalbum_photo", $params)) {
    		return false;
    	}

		$params = array(
			"album_id" => $photos[0]["album_id"]
		);
		$sequenceParam = array(
			"photo_sequence" => $photos[0]["photo_sequence"]
		);
		if (!$this->_db->seqExecute("photoalbum_photo", $params, $sequenceParam)) {
			return false;
		}

		$sql = "UPDATE {photoalbum_album} ".
				"SET photo_count = photo_count - 1 ".
				"WHERE album_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		//--新着情報関連 Start--
		// 新着にあれば、更新、なければそのまま
		$params = array(
			"unique_id" => $this->_request->getParameter("album_id"),
			"module_id" => $this->_request->getParameter("module_id")
		);
		$whatsnew = $this->_db->selectExecute("whatsnew", $params, null, 1);
		if(isset($whatsnew[0])) {
			// データあり
			$container =& DIContainerFactory::getContainer();
	    	$whatsnewAction =& $container->getComponent("whatsnewAction");
	    	$whatsnew[0]['count_num'] = intval($whatsnew[0]['count_num']) - 1;
	    	if($whatsnew[0]['count_num'] == 0) {
	    		$result = $whatsnewAction->delete($this->_request->getParameter("album_id"));
	    	} else {
	    		$result = $whatsnewAction->update($whatsnew[0]);
	    	}
			if($result === false) {
				return false;
			}
		}
		
		//--新着情報関連 End--

		return true;
	}

	/**
	 * コメントデータを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function setComment()
	{
		$commentID = $this->_request->getParameter("comment_id");
		if (empty($commentID)) {
			$params = array(
				"photo_id" => $this->_request->getParameter("photo_id"),
				"album_id" => $this->_request->getParameter("album_id"),
				"photoalbum_id" => $this->_request->getParameter("photoalbum_id"),
				"comment_value" => $this->_request->getParameter("comment_value")
			);
			$result = $this->_db->insertExecute("photoalbum_comment", $params, true, "comment_id");
		} else {
			$params = array(
				"comment_id" => $this->_request->getParameter("comment_id"),
				"comment_value" => $this->_request->getParameter("comment_value")
			);
			$result = $this->_db->updateExecute("photoalbum_comment", $params, "comment_id", true);
		}
		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * コメントデータを削除する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function deleteComment()
	{
		$params = array(
			"comment_id" => $this->_request->getParameter("comment_id")
		);

    	if (!$this->_db->deleteExecute("photoalbum_comment", $params)) {
    		return false;
    	}

		return true;
	}

	/**
	 * 投票データを登録する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function vote()
	{
        $photoID = $this->_request->getParameter("photo_id");

        $container =& DIContainerFactory::getContainer();
		$session =& $container->getComponent("Session");
		$userID = $session->getParameter("_user_id");
		if (empty($userID)) {
			$votes = $session->getParameter("photoalbum_votes");
			$votes[] = $photoID;
			$session->setParameter("photoalbum_votes", $votes);
 		} else {
			$params = array(
				"user_id" => $userID,
				"photo_id" => $photoID,
				"vote_flag" => _ON
			);
	        if (!$this->_db->insertExecute("photoalbum_user_photo", $params, true)) {
				return false;
			}
		}

		$params = array($photoID);
		$sql = "UPDATE {photoalbum_photo} ".
				"SET photo_vote_count = photo_vote_count + 1 ".
				"WHERE photo_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		$params = array($this->_request->getParameter("album_id"));
		$sql = "UPDATE {photoalbum_album} ".
				"SET album_vote_count = album_vote_count + 1 ".
				"WHERE album_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}

		return true;
	}
}
?>