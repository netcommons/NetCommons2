<?php
/**
 * Insert Data 
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 * @param      $modules[$dir_name], $self_site_id, $admin_user_id
 */
if(file_exists(HTDOCS_DIR . '/install/logo.gif')) {
	$logo_path = CORE_BASE_URL."/install/logo.gif";
} else {
	$logo_path = CORE_BASE_URL."/htdocs/install/logo.gif";
}
$data = "
# --
# -- NetCommons Insert Data
# --

# -- --------------------------------------------------------

# -- 
# -- テーブルのダンプデータ `announcement`
# -- 
INSERT INTO `announcement` (`block_id`, `content`, `more_content`, `more_title`, `hide_more_title`, `room_id`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES (1, '<img src=\"".$logo_path."\" alt=\"NetCommons\" title=\"NetCommons\" />".INSTALL_INSERT_DATA_LOGO_NOTE."', '', '', '', 1, '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."', '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."');
INSERT INTO `announcement` (`block_id`, `content`, `more_content`, `more_title`, `hide_more_title`, `room_id`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES (2, '<div class=\"align-right\"><a href=\".".INDEX_FILE_NAME."?action=pages_view_main&amp;active_center=search_view_main_center\" class=\"link nowrap\"><img class=\"icon\" alt=\"".INSTALL_INSERT_DATA_SEARCH_ALT."\" src=\"".CORE_BASE_URL."/themes/images/icons/default/search.gif\" />".INSTALL_INSERT_DATA_SEARCH_TITLE."</a></div>', '', '', '', 1, '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."', '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."');
INSERT INTO `announcement` (`block_id`, `content`, `more_content`, `more_title`, `hide_more_title`, `room_id`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES (3, '".INSTALL_INSERT_DATA_ANNOUNCEMENT."', '', '', '', 1, '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."', '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."');

# -- 
# -- テーブルのダンプデータ `blocks`
# -- 
INSERT INTO `blocks` (`block_id`, `page_id`, `module_id`, `site_id`, `root_id`, `parent_id`, `thread_num`, `col_num`, `row_num`, `url`, `action_name`, `parameters`, `block_name`, `theme_name`, `temp_name`, `leftmargin`, `rightmargin`, `topmargin`, `bottommargin`, `min_width_size`, `shortcut_flag`, `copyprotect_flag`, `display_scope`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES (1, 3, ".$modules['announcement']['module_id'].", '".$self_site_id."', 0, 0, 0, 1, 1, '', '".$modules['announcement']['action_name']."', '', '', '', 'default', 8, 8, 8, 8, 0, 0, 0, 4, '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."', '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."');
INSERT INTO `blocks` (`block_id`, `page_id`, `module_id`, `site_id`, `root_id`, `parent_id`, `thread_num`, `col_num`, `row_num`, `url`, `action_name`, `parameters`, `block_name`, `theme_name`, `temp_name`, `leftmargin`, `rightmargin`, `topmargin`, `bottommargin`, `min_width_size`, `shortcut_flag`, `copyprotect_flag`, `display_scope`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES (2, 3, ".$modules['announcement']['module_id'].", '".$self_site_id."', 0, 0, 0, 2, 1, '', '".$modules['announcement']['action_name']."', '', '', '', 'default', 8, 8, 38, 8, 0, 0, 0, 4, '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."', '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."');
INSERT INTO `blocks` (`block_id`, `page_id`, `module_id`, `site_id`, `root_id`, `parent_id`, `thread_num`, `col_num`, `row_num`, `url`, `action_name`, `parameters`, `block_name`, `theme_name`, `temp_name`, `leftmargin`, `rightmargin`, `topmargin`, `bottommargin`, `min_width_size`, `shortcut_flag`, `copyprotect_flag`, `display_scope`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES (3, 13, ".$modules['announcement']['module_id'].", '".$self_site_id."', 0, 0, 0, 1, 1, '', '".$modules['announcement']['action_name']."', '', '".INSTALL_INSERT_DATA_ANNOUNCEMENT_TITLE."', '', 'default', 8, 8, 8, 8, 600, 0, 0, 4, '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."', '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."');
INSERT INTO `blocks` (`block_id`, `page_id`, `module_id`, `site_id`, `root_id`, `parent_id`, `thread_num`, `col_num`, `row_num`, `url`, `action_name`, `parameters`, `block_name`, `theme_name`, `temp_name`, `leftmargin`, `rightmargin`, `topmargin`, `bottommargin`, `min_width_size`, `shortcut_flag`, `copyprotect_flag`, `display_scope`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES (4, 4, ".$modules['menu']['module_id'].", '".$self_site_id."', 0, 0, 0, 1, 1, '', '".$modules['menu']['action_name']."', '', '".$modules['menu']['module_name']."', '', 'default', 8, 8, 8, 8, 0, 0, 0, 4, '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."', '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."');
INSERT INTO `blocks` (`block_id`, `page_id`, `module_id`, `site_id`, `root_id`, `parent_id`, `thread_num`, `col_num`, `row_num`, `url`, `action_name`, `parameters`, `block_name`, `theme_name`, `temp_name`, `leftmargin`, `rightmargin`, `topmargin`, `bottommargin`, `min_width_size`, `shortcut_flag`, `copyprotect_flag`, `display_scope`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES (5, 4, ".$modules['imagine']['module_id'].", '".$self_site_id."', 0, 0, 0, 1, 2, '', '".$modules['imagine']['action_name']."', '', '".$modules['imagine']['module_name']."', 'sideline_default', 'default', 8, 8, 8, 8, 0, 0, 0, 4, '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."', '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."');

# --カレンダー
# --新着
# -- 
# -- テーブルのダンプデータ `imagine_block`
# -- 

INSERT INTO `imagine_block` (`block_id`, `display`, `room_id`, `insert_time`, `insert_site_id`, `insert_user_id`, `insert_user_name`, `update_time`, `update_site_id`, `update_user_id`, `update_user_name`) VALUES (5, 0, 1, '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."', '".$time."', '".$self_site_id."', '".$admin_user_id."', '".$admin_handle."');

# -- 
# -- テーブルのダンプデータ `blocks_seq_id`
# -- 
INSERT INTO `blocks_seq_id` (`id`) VALUES (5);

";

unset($logo_path);
?>