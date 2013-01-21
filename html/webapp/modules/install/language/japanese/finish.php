<?php
/**
 * finish
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
$content .=
"<u><b>サイト</b></u>
<p>インストールされたサイトを見るには、<a href='".BASE_URL.INDEX_FILE_NAME."?action=pages_view_main'>ここ</a>をクリックしてください。</p>
<p>但し、「webapp/config/install.inc.php」を書き込み不可(chmod 444)に設定しなければ、インストーラの最初へ戻ります。</p>
<u><b>NetCommonsの使い方について</b></u>
<p>ユーザーマニュアルは現在作成されておりません。もうしばらくお待ちください。</p>
<u><b>サポート</b></u>
<p><a href='http://www.netcommons.org/' target='_blank'>NetCommons日本語公式サイト</a>を訪問ください。
</p>
<u><b>注意</b></u>
<div style='padding:5px 0px;color:#ff0000;'>htdocs直下をドキュメントルートに設定してあるか、.htaccessが有効になっているか再度確認してください。</div>
";
?>