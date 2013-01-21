<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * [[機能説明]]
 *  携帯管理モジュールアップデートクラス
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Mobile_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;

    /**
     * execute実行
     *
     * @access  public
     */
	function execute()
	{
		$adodb = $this->db->getAdoDbObject();
		$metaTables = $adodb->MetaTables();
		if (!in_array($this->db->getPrefix()."mobile_menu_detail", $metaTables)) {
			$sql = "CREATE TABLE `".$this->db->getPrefix()."mobile_menu_detail` (".
					"`block_id`            int(11) NOT NULL,".
					"`page_id`             int(11) NOT NULL,".
					"`visibility_flag`     tinyint(1) NOT NULL default 1,".
					"`room_id`             int(11) NOT NULL default 0,".
					"`insert_time`         varchar(14) NOT NULL default '',".
					"`insert_site_id`      varchar(40) NOT NULL default '',".
					"`insert_user_id`      varchar(40) NOT NULL default '',".
					"`insert_user_name`    varchar(255) NOT NULL,".
					"`update_time`         varchar(14) NOT NULL default '',".
					"`update_site_id`      varchar(40) NOT NULL default '',".
					"`update_user_id`      varchar(40) NOT NULL default '',".
					"`update_user_name`    varchar(255) NOT NULL,".
					"PRIMARY KEY  (`block_id`,`page_id`)".
				") ENGINE=MyISAM;";
			$result = $this->db->execute($sql);
			if ($result === false) {
				return false;
			}
		}

		// itemsに新しいレコードを追加
		$add_items_params = array(
							array( "item_name"=>"USER_ITEM_TEXTHTML_MODE",
									"type"=>USER_TYPE_RADIO, "tag_name"=>"mobile_texthtml_mode",
									"system_flag"=>1,"require_flag"=>0,"define_flag"=>1,"display_flag"=>0,"allow_public_flag"=>0,"allow_email_reception_flag"=>0,
									"col_num"=>1,"row_num"=>99 ),
							array( "item_name"=>"USER_ITEM_IMGDSP_SIZE",
									"type"=>USER_TYPE_RADIO,"tag_name"=>"mobile_imgdsp_size",
									"system_flag"=>1,"require_flag"=>0,"define_flag"=>1,"display_flag"=>0,"allow_public_flag"=>0,"allow_email_reception_flag"=>0,
									"col_num"=>1,"row_num"=>99 )
		);
		$authority_params = array(
						array( "user_authority_id"=>_AUTH_ADMIN, "under_public_flag"=>USER_EDIT, "self_public_flag"=>USER_EDIT, "over_public_flag"=>USER_EDIT ),
						array( "user_authority_id"=>_AUTH_CHIEF, "under_public_flag"=>USER_PUBLIC, "self_public_flag"=>USER_EDIT, "over_public_flag"=>USER_NO_PUBLIC ),
						array( "user_authority_id"=>_AUTH_MODERATE, "under_public_flag"=>USER_PUBLIC, "self_public_flag"=>USER_EDIT, "over_public_flag"=>USER_NO_PUBLIC ),
						array( "user_authority_id"=>_AUTH_GENERAL, "under_public_flag"=>USER_NO_PUBLIC, "self_public_flag"=>USER_EDIT, "over_public_flag"=>USER_NO_PUBLIC ),
						array( "user_authority_id"=>_AUTH_GUEST, "under_public_flag"=>USER_NO_PUBLIC, "self_public_flag"=>USER_EDIT, "over_public_flag"=>USER_NO_PUBLIC )
		);
		$desc_params = array(
			"USER_ITEM_TEXTHTML_MODE"=>array( "description"=>"USER_ITEM_MES_TEXTHTML_MODE_DESCRIPTION" ),
			"USER_ITEM_IMGDSP_SIZE"=>array( "description"=>"USER_ITEM_MES_IMGDSP_SIZE_DESCRIPTION" )
		);
		$opt_params = array(
			"USER_ITEM_TEXTHTML_MODE"=>array( "options"=>"USER_ITEM_TEXTHTML_MODE_TEXT|USER_ITEM_TEXTHTML_MODE_HTML|", "default_selected"=>"0|0|" ),
			"USER_ITEM_IMGDSP_SIZE"=>array( "options"=>"USER_ITEM_IMGDSP_SIZE_240|USER_ITEM_IMGDSP_SIZE_480|USER_ITEM_IMGDSP_SIZE_ORG|", "default_selected"=>"0|0|0|" )
		);

		foreach( $add_items_params as $item ) {
			// まだ追加対象の項目が入っていないことを確認する
			$ret = $this->db->countExecute( "items", array( "item_name"=>$item['item_name'] ) );
			if( $ret == 1 ) {
				// もう存在していたら次へ
				continue;
			}
			// 1列目の最終行の値を調べる
			$row_max = $this->db->maxExecute( "items", "row_num", array( "col_num"=>1 ) );
			// 最終行の値をセット
			$item['row_num'] = $row_max+1;
			// 新規追加
			$ret = $this->db->insertExecute( "items", $item, true, "item_id" );
			if( $ret == false ) {
				return false;
			}
			// 追加したItem_idを覚えておく
			$item_id = $ret;

			// items_authorities_linkに追加
			foreach( $authority_params as $auth_param ) {
				$auth_param += array( "item_id"=>$item_id );
				$ret = $this->db->insertExecute( "items_authorities_link", $auth_param );
				if( $ret == false ) {
					$this->db->deleteExecute( "items", array( "item_id"=>$item_id ) );
					return false;
				}
			}

			// items_desc
			$desc = $desc_params[ $item['item_name'] ];
			$desc += array( "item_id"=>$item_id );
			$ret = $this->db->insertExecute( "items_desc", $desc );
			if( $ret == false ) {
				$this->db->deleteExecute( "items", array( "item_id"=>$item_id ) );
				$this->db->deleteExecute( "items_authorities_link", array( "item_id"=>$item_id ) );
				return false;
			}

			// items_option
			$opt = $opt_params[ $item['item_name'] ];
			$opt += array( "item_id"=>$item_id );
			$ret = $this->db->insertExecute( "items_options", $opt );
			if( $ret == false ) {
				$this->db->deleteExecute( "items", array( "item_id"=>$item_id ) );
				$this->db->deleteExecute( "items_authorities_link", array( "item_id"=>$item_id ) );
				$this->db->deleteExecute( "items_desc", array( "item_id"=>$item_id ) );
				return false;
			}
		}

		$whereParams = array(
			'options' => 'USER_ITEM_IMGDSP_SIZE_240|USER_ITEM_IMGDSP_SIZE_480|USER_ITEM_IMGDSP_SIZE_ORG'
		);
		$ret = $this->db->countExecute("items_options", $whereParams);
		if ($ret > 0) {
			$setParams = array(
				'options' => 'USER_ITEM_IMGDSP_SIZE_240|USER_ITEM_IMGDSP_SIZE_480|USER_ITEM_IMGDSP_SIZE_ORG|'
			);
			$ret = $this->db->updateExecute("items_options", $setParams, $whereParams);
		}

		return true;
	}
}
?>
