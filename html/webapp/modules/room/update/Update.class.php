<?php
/**
 * 会員管理アップデートクラス
 *
 * @package     NetCommons.components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
class Room_Update extends Action
{
	//使用コンポーネントを受け取るため
	var $db = null;
	var $pagesAction = null;

	function execute()
	{
		$sql = "SELECT P.room_id, "
					. "MAX(PU.role_authority_id) M "
				. "FROM {pages} P "
				. "LEFT JOIN {pages_users_link} PU "
					. "ON P.room_id = PU.room_id "
				. "WHERE (P.space_type = ? "
						. "OR  P.space_type = ?) "
					. "AND P.private_flag = ? "
					. "AND P.thread_num = ? "
				. "GROUP BY P.room_id "
				. "HAVING (M = ? "
						. "OR M IS NULL)";
		$bindValues = array(
			_SPACE_TYPE_PUBLIC,
			_SPACE_TYPE_GROUP,
			_OFF,
			1,
			0
		);

		$garbages = $this->db->execute($sql, $bindValues);
		if ($garbages === false) {
			$this->db->addError();
			return false;
		}

		foreach ($garbages as $garbage) {
			if (!$this->pagesAction->deleteRoom($garbage['room_id'])) {
				return false;
			}
		}

		return true;
	}
}
?>
