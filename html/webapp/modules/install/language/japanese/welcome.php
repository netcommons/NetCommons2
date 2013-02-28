<?php
/**
 * introduction
 *
 * @package     NetCommons
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */
$content .=
"<u><b>はじめに</b></u>
<p>
&nbsp;&nbsp;NetCommonsは、大学共同利用機関法人情報・システム研究機構　国立情報学研究所が開発したPHPによるオープンソースの情報共有基盤システムです。 
本ソフトウェアの著作権は情報・システム研究機構およびプログラムを作成した各個人に帰属し、<a href='http://www.freebsd.org/copyright/freebsd-license.html' target='_blank'>FreeBSDライセンス</a>（参考：<a href='http://www.jp.freebsd.org/www.FreeBSD.org/ja/copyright/freebsd-license.html' target='_blank'>日本語訳</a>）によって公開されています。本ソフトウェアを利用する方は、FreeBSDライセンスの精神をご理解の上、ご利用ください。

本ソフトウェアはソースコード形式であれバイナリ形式であれ、変更の有無に関わらず、以下の条件を満たす限りにおいて、再配布および使用を許可します:
<ul>
<li>ソースコード形式で再配布する場合、上記著作権表示、本条件書および下記責任限定規定を必ず含めてください。</li>
<li>バイナリ形式で再配布する場合、上記著作権表示、本条件書および下記責任限定規定を、配布物とともに提供される文書 および/または他の資料に必ず含めてください。</li>
</ul>
&nbsp;&nbsp;本ソフトウェアは国立情報学研究所NetCommons PROJECT によって、“現状のまま” 提供されるものとします。本ソフトウェアについては、明示黙示を問わず、商用品として通常そなえるべき品質をそなえているとの保証も、特定の目的に適合するとの保証を含め、何の保証もなされません。 事由のいかんを問わず、損害発生の原因いかんを問わず、且つ、 責任の根拠が契約であるか厳格責任であるか (過失その他) 不法行為であるかを問わず、 情報・システム研究機構、国立情報学研究所、NetCommons PROJECTおよび寄与者も、仮にそのような損害が発生する可能性を知らされていたとしても、本ソフトウェアの使用から発生した直接損害、間接損害、偶発的な損害、特別損害、懲罰的損害または結果損害のいずれに対しても (代替品または サービスの提供; 使用機会、データまたは利益の損失の補償; または、業務の中断に対する補償を含め) 責任をいっさい負いません。
<p>
&nbsp;&nbsp;本ソフトウェアの名称である「NetCommons」およびロゴは大学共同利用機関法人情報・システム研究機構の知的財産として商標登録されています。商標「NetCommons」の利用を希望される場合には、大学共同利用機関法人情報・システム研究機構　国立情報学研究所（郵便番号101-8430 東京都千代田区一ツ橋２－１－２）までお問い合わせください。
</p>
<u><b>必要なソフトウエア</b></u>
<p>
<ul>
<li>ウェブサーバ(<a href='http://www.apache.org/' target='_blank'>Apache</a>, IIS, Roxen, など)</li>
<li><a href='http://www.php.net/' target='_blank'>PHP</a> 4.3.9以降</li>
<li>データベースサーバ（<a href='http://www.mysql.com/' target='_blank'>MySQL</a> Database 3.23.XX以降）</li>
</ul>
</p>
<u><b>準備</b></u>
<ul>
<li>ウェブサーバ、PHP、データベースサーバを適切にセットアップする。</li>
<div style='color:#ff0000;'>※&nbsp;ウェブサーバは、htdocs直下をドキュメントルートに設定するか、.htaccessを有効にしてください。</div>
<li>NetCommons用にデータベースを１つ準備する。</li>
<li>上記のデータベースにアクセスできる、データベースサーバのユーザアカウントを準備する。</li>
<li>htdocs/、uploads/、templates_c/ディレクトリと、install.inc.phpファイルをPHPから書込み可能にする。</li>
<li>ブラウザのクッキーとJavaScriptをオンにする。</li>
</ul>
<u><b>インストール</b></u>
<p>
このウィザードに従ってください。</p>
"
?>