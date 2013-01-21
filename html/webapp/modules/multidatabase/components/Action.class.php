<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * 汎用データベース登録コンポーネント
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Multidatabase_Components_Action
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

	/**
	 * コンストラクター
	 *
	 * @access	public
	 */
	function Multidatabase_Components_Action()
	{
		$this->_container =& DIContainerFactory::getContainer();
		$this->_db =& $this->_container->getComponent("DbObject");
		$commonMain =& $this->_container->getComponent("commonMain");
		$this->_uploads =& $commonMain->registerClass(WEBAPP_DIR.'/components/uploads/Action.class.php', "Uploads_Action", "uploadsAction");
	}

	function fgetcsv_reg (&$handle, $length = null, $d = ',', $e = '"') {
        $d = preg_quote($d);
        $e = preg_quote($e);
        $_line = "";
        $eof = false;
        while ($eof != true) {
            $_line .= (empty($length) ? fgets($handle) : fgets($handle, $length));
            $itemcnt = preg_match_all('/'.$e.'/', $_line, $dummy);
            if ($itemcnt % 2 == 0) $eof = true;
        }
        $_csv_line = preg_replace('/(?:\r\n|[\r\n])?$/', $d, trim($_line));
        $_csv_pattern = '/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';
        preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
        $_csv_data = $_csv_matches[1];
        for($_csv_i=0;$_csv_i<count($_csv_data);$_csv_i++){
            $_csv_data[$_csv_i]=preg_replace('/^'.$e.'(.*)'.$e.'$/s','$1',$_csv_data[$_csv_i]);
            $_csv_data[$_csv_i]=str_replace($e.$e, $e, $_csv_data[$_csv_i]);
        }
        return empty($_line) ? false : $_csv_data;
    }

	/**
	 * 汎用データベース削除処理
	 * @param  int    multidatabase_id
	 * @return boolean
	 * @access public
	 */
	function deleteMdb($multidatabase_id) {
		if (empty($multidatabase_id)) {
    		return false;
    	}
    	$params = array(
			"multidatabase_id" => $multidatabase_id
		);

    	$result = $this->_db->selectExecute("multidatabase", $params);
    	if($result === false) {
    		return false;
    	}

    	if(isset($result[0])) {
    		$contents = $this->_db->selectExecute("multidatabase_content", $params);
    		if($contents === false) {
    			return false;
    		}
    		if(!empty($contents)) {
    			foreach($contents as $key => $val) {
    				$result = $this->deleteContent($val['content_id']);
    				if ($result === false) {
    					return false;
    				}
    			}
    		}

    		$result = $this->_db->deleteExecute("multidatabase_metadata", $params);
	    	if($result === false) {
	    		return false;
	    	}

	    	$result = $this->_db->deleteExecute("multidatabase", $params);
	    	if($result === false) {
	    		return false;
	    	}

			//--URL短縮形関連 Start--
			$container =& DIContainerFactory::getContainer();
			$abbreviateurlAction =& $container->getComponent("abbreviateurlAction");
			$result = $abbreviateurlAction->deleteUrlByContents($multidatabase_id);
			if ($result === false) {
				return false;
			}
			//--URL短縮形関連 End--
    	}

    	return true;
	}

	/**
	 * メタデータ削除処理
	 * @param  int    metadata_id
	 * @return boolean
	 * @access public
	 */
	function deleteMetadata($metadata_id) {
		if (empty($metadata_id)) {
    		return false;
    	}
    	$params = array(
			"metadata_id" => $metadata_id
		);

    	$metadata = $this->_db->selectExecute("multidatabase_metadata", $params);
    	if($metadata === false) {
    		return false;
    	}

    	if(isset($metadata[0])) {
    		$metadata_contents = $this->_db->selectExecute("multidatabase_metadata_content", $params);
    		if($metadata_contents === false) {
    			return false;
    		}
    		if(!empty($metadata_contents)) {
    			foreach($metadata_contents as $key => $val) {
    				$result = $this->deleteMetadataContent($val['metadata_content_id']);
    				if ($result === false) {
    					return false;
    				}
    			}
    		}

	    	$result = $this->_db->deleteExecute("multidatabase_metadata", $params);
	    	if($result === false) {
	    		return false;
	    	}
    	}

    	return true;
	}

	/**
	 * コンテンツ削除処理
	 * @param  int    content_id
	 * @return boolean
	 * @access public
	 */
	function deleteContent($content_id) {
		if (empty($content_id)) {
    		return false;
    	}
    	$params = array(
			"content_id" => $content_id
		);

    	$result = $this->_db->selectExecute("multidatabase_content", $params);
    	if($result === false) {
    		return false;
    	}

    	if(isset($result[0])) {
    		$metadata_contents = $this->_db->selectExecute("multidatabase_metadata_content", $params);
    		if($metadata_contents === false) {
    			return false;
    		}
    		if(!empty($metadata_contents)) {
    			foreach($metadata_contents as $key => $val) {
    				$result = $this->deleteMetadataContent($val['metadata_content_id']);
    				if ($result === false) {
    					return false;
    				}
    			}
    		}

	    	$result = $this->_db->deleteExecute("multidatabase_content", $params);
	    	if($result === false) {
	    		return false;
	    	}

	    	$result = $this->_db->deleteExecute("multidatabase_comment", $params);
	    	if($result === false) {
	    		return false;
	    	}
    	}

		//--新着情報関連 Start--
		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");
		$result = $whatsnewAction->delete($content_id);
    	if ($result === false) {
			return false;
		}
		//--新着情報関連 End--

    	return true;
	}

	/**
	 * コンテンツのデータ削除処理
	 * @param  int    metadata_content_id
	 * @return boolean
	 * @access public
	 */
	function deleteMetadataContent($metadata_content_id) {
		if (empty($metadata_content_id)) {
    		return false;
    	}
    	$params = array(
			"metadata_content_id" => $metadata_content_id
		);

    	$data = $this->_db->selectExecute("multidatabase_metadata_content", $params);
    	if($data === false) {
    		return false;
    	}

    	if(isset($data[0])) {
    		$metadata= $this->_db->selectExecute("multidatabase_metadata", array("metadata_id" => $data[0]['metadata_id']));
    		if($metadata === false) {
    			return false;
    		}

    		if(isset($metadata[0])) {
    			if($metadata[0]['type'] == MULTIDATABASE_META_TYPE_FILE || $metadata[0]['type'] == MULTIDATABASE_META_TYPE_IMAGE) {
    				$result = $this->_db->deleteExecute("multidatabase_file", $params);
					if($result === false) {
	    				return false;
	    			}

    				//画像とファイル削除
					if(!empty($data[0]['content'])) {
						$pathList = explode("&", $data[0]['content']);
						if(isset($pathList[1])) {
							$upload_id = intval(str_replace("upload_id=","", $pathList[1]));
							if(!empty($upload_id)) {
								$result = $this->_uploads->delUploadsById($upload_id);
								if ($result === false) {
									return false;
								}
							}
						}
					}
    			}
    		}

	    	$result = $this->_db->deleteExecute("multidatabase_metadata_content", $params);
	    	if($result === false) {
	    		return false;
	    	}
    	}

    	return true;
	}

	/**
	 * コンテンツ番号データを変更する
	 *
     * @return boolean	true or false
	 * @access	public
	 */
	function updateContentSequence() {
		$request =& $this->_container->getComponent("Request");

		$dragSequence = $request->getParameter("drag_sequence");
		$dropSequence = $request->getParameter("drop_sequence");

		$params = array(
			$request->getParameter("multidatabase_id"),
			$dragSequence,
			$dropSequence
		);

        if ($dragSequence > $dropSequence) {
        	$sql = "UPDATE {multidatabase_content} ".
					"SET display_sequence = display_sequence + 1 ".
					"WHERE multidatabase_id = ? ".
					"AND display_sequence < ? ".
					"AND display_sequence > ?";
        } else {
        	$sql = "UPDATE {multidatabase_content} ".
					"SET display_sequence = display_sequence - 1 ".
					"WHERE multidatabase_id = ? ".
					"AND display_sequence > ? ".
					"AND display_sequence <= ?";
        }

		$result = $this->_db->execute($sql, $params);
		if($result === false) {
			$this->_db->addError();
			return false;
		}

		if ($dragSequence > $dropSequence) {
			$dropSequence++;
		}
		$params = array(
			$dropSequence,
			$request->getParameter("drag_content_id")
		);

    	$sql = "UPDATE {multidatabase_content} ".
				"SET display_sequence = ? ".
				"WHERE content_id = ?";
        $result = $this->_db->execute($sql, $params);
		if($result === false) {
			$this->_db->addError();
			return false;
		}

		return true;
	}

	/**
	 * 新着情報にセットする
	 *
     * @return bool
	 * @access	public
	 */
	function setWhatsnew($content_id) {
		$params = array("content_id" => $content_id);
    	$content = $this->_db->selectExecute("multidatabase_content", $params);
		if (empty($content)) {
			return false;
		}
		
		$multidatabase = $this->_db->selectExecute("multidatabase", array("multidatabase_id" => $content[0]['multidatabase_id']));
		if (empty($multidatabase)) {
			return false;
		}

		$whatsnewAction =& $this->_container->getComponent("whatsnewAction");

		if ($content[0]["temporary_flag"] == MULTIDATABASE_STATUS_RELEASED_VALUE && $content[0]["agree_flag"] == MULTIDATABASE_STATUS_AGREE_VALUE) {
			$result = $this->getWhatsnewTitle($content_id, $multidatabase[0]['title_metadata_id']);
			if ($result === false) {
				return false;
			}

			$whatsnew = array(
				"unique_id" => $content_id,
				"title" => $result["title"],
				"description" => $result["description"],
				"action_name" => "multidatabase_view_main_detail",
				"parameters" => "content_id=". $content_id . "&multidatabase_id=" . $content[0]["multidatabase_id"],
				"insert_time" => $content[0]["insert_time"],
				"insert_user_id" => $content[0]["insert_user_id"],
				"insert_user_name" => $content[0]["insert_user_name"]
			);

			$result = $whatsnewAction->auto($whatsnew);
			if ($result === false) {
				return false;
			}
		} else {
			$result = $whatsnewAction->delete($content_id);
			if($result === false) {
				return false;
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
		if ($status == MULTIDATABASE_STATUS_RELEASED_VALUE  && $agree_flag == MULTIDATABASE_STATUS_AGREE_VALUE
				&& (!$edit_flag
					|| $before_post['temporary_flag'] == MULTIDATABASE_STATUS_BEFORE_RELEASED_VALUE
					|| $before_post['agree_flag'] == MULTIDATABASE_STATUS_WAIT_AGREE_VALUE)) {
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
	 * 新着情報のタイトル・詳細を取得
	 *
	 * @access	public
	 */
	function getWhatsnewTitle($content_id, $title_metadata_id) {
		$sql = "SELECT meta.metadata_id, meta.type, meta_con.content," .
						" meta_con.insert_time, meta_con.update_time, file.file_name" .
				" FROM {multidatabase_metadata_content} meta_con" .
				" INNER JOIN {multidatabase_metadata} meta ON (meta_con.metadata_id = meta.metadata_id)" .
				" LEFT JOIN {multidatabase_file} file ON (meta_con.metadata_content_id=file.metadata_content_id)" .
				" WHERE meta_con.content_id=?".
				" AND (meta.metadata_id=? OR meta.type=".MULTIDATABASE_META_TYPE_WYSIWYG." OR meta.type=".MULTIDATABASE_META_TYPE_TEXTAREA.")" .
				" ORDER BY meta.display_pos, meta.display_sequence";

		$params = array("content_id" => $content_id, "metadata_id" => $title_metadata_id);
		$result = $this->_db->execute($sql, $params, null, null, true, array($this,"_callbackWhatsnewTitle"), $title_metadata_id);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		return $result;
	}

	/**
	 * 新着情報のコールバック関数
	 *
	 * @access	private
	 */
	function _callbackWhatsnewTitle(&$recordSet, $title_metadata_id) {
		$result = array("title"=>null, "description"=>null);
		while ($row = $recordSet->fetchRow()) {
			if ($row['metadata_id'] == $title_metadata_id && !isset($result["title"])) {
				switch ($row["type"]) {
					case MULTIDATABASE_META_TYPE_FILE:
					case MULTIDATABASE_META_TYPE_IMAGE:
						if (empty($row["file_name"])) {
							$title = MULTIDATABASE_NOTITLE;
						} else {
							$title = $row["file_name"];
						}
						$result["title"] = $title;
						break;
					case MULTIDATABASE_META_TYPE_WYSIWYG:
						$container =& DIContainerFactory::getContainer();
						$convertHtml =& $container->getComponent("convertHtml");
			    		$title = $convertHtml->convertHtmlToText($row["content"]);
			    		$title = preg_replace("/\\\n/", " ", $title);
						$title = mb_substr($title, 0, _SEARCH_CONTENTS_LEN + 1, INTERNAL_CODE);
						$result["title"] = $title;
						break;
					case MULTIDATABASE_META_TYPE_AUTONUM:
						$title = intval($row["content"]);
						$result["title"] = $title;
						break;
					case MULTIDATABASE_META_TYPE_DATE:
						if (empty($row["content"])) {
							$title = MULTIDATABASE_NOTITLE;
						} else {
							$title = timezone_date_format($row["content"], _DATE_FORMAT);
						}
						$result["title"] = $title;
						break;
					case MULTIDATABASE_META_TYPE_INSERT_TIME:
						$title = timezone_date_format($row["insert_time"], _FULL_DATE_FORMAT);
						$result["title"] = $title;
						break;
					case MULTIDATABASE_META_TYPE_UPDATE_TIME:
						$title = timezone_date_format($row["update_time"], _FULL_DATE_FORMAT);
						$result["title"] = $title;
						break;
					case MULTIDATABASE_META_TYPE_MULTIPLE:
						if (empty($row["content"])) {
							$title = MULTIDATABASE_NOTITLE;
						} else {
							$multipleArr = explode("|",$row["content"]);
							$title = $multipleArr[0];
						}
						$result["title"] = $title;
						break;
					default:
						$result["title"] = $row["content"];
						break;
				}
				continue;
			}
			if ($row['metadata_id'] != $title_metadata_id && !isset($result["description"])) {
				$result["description"] = $row["content"];
				continue;
			}

			if (isset($result["title"]) && isset($result["description"])) { break; }
		}
		return $result;
	}
	
	/**
	 * ダウンロード回数記入
	 *
	 * @access	private
	 */
	function setDownloadCount($upload_id) {
		if(empty($upload_id)) {
			return false;
		}
		
		$params = array(
			"upload_id" => $upload_id
		);
		$sql = "UPDATE {multidatabase_file} ".
				"SET download_count = download_count + 1 ".
				"WHERE upload_id = ?";
		$result = $this->_db->execute($sql, $params);
		if ($result === false) {
			$this->_db->addError();
			return false;
		}
		
		return true;
	}

}
?>
