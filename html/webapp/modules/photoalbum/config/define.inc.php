<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * フォトアルバム定数定義
 *
 * @package     NetCommons Components
 * @author      Noriko Arai,Ryuji Masukawa
 * @copyright   2006-2007 NetCommons Project
 * @license     http://www.netcommons.org/license.txt  NetCommons License
 * @project     NetCommons Project, supported by National Institute of Informatics
 * @access      public
 */

define("PHOTOALBUM_DISPLAY_LIST", 0);
define("PHOTOALBUM_DISPLAY_SLIDE", 1);

define("PHOTOALBUM_SLIDE_TYPE_FADE", 0);			// フェード
define("PHOTOALBUM_SLIDE_TYPE_BLINDS", 1);			// ブラインド
define("PHOTOALBUM_SLIDE_TYPE_CHECKERBOARD", 2);	// 市松模様
define("PHOTOALBUM_SLIDE_TYPE_STRIPS", 3);			// 斜め開き
define("PHOTOALBUM_SLIDE_TYPE_BARN", 4);			// ドア
define("PHOTOALBUM_SLIDE_TYPE_GRADIENTWIPE", 5);	// グラディエント
define("PHOTOALBUM_SLIDE_TYPE_IRIS", 6);			// アイリス
define("PHOTOALBUM_SLIDE_TYPE_WHEEL", 7);			// 風車
define("PHOTOALBUM_SLIDE_TYPE_PIXELATE", 8);		// ピクセレイト
define("PHOTOALBUM_SLIDE_TYPE_RADIALWIPE", 9);		// 放射状
define("PHOTOALBUM_SLIDE_TYPE_RANDOMBARS", 10);		// 雨
define("PHOTOALBUM_SLIDE_TYPE_SLIDE", 11);			// スライド
define("PHOTOALBUM_SLIDE_TYPE_RANDOMDISSOLVE", 12);	// 雪
define("PHOTOALBUM_SLIDE_TYPE_SPIRAL", 13);			// 螺旋
define("PHOTOALBUM_SLIDE_TYPE_STRETCH", 14);		// ストレッチ
define("PHOTOALBUM_SLIDE_TYPE_RANDOM", 15);			// ランダム

define("PHOTOALBUM_SLIDE_TYPE_FADE_VALUE", "progid:DXImageTransform.Microsoft.Fade(duration=1)");
define("PHOTOALBUM_SLIDE_TYPE_BLINDS_VALUE", "progid:DXImageTransform.Microsoft.Blinds(Duration=1,bands=20)");
define("PHOTOALBUM_SLIDE_TYPE_CHECKERBOARD_VALUE", "progid:DXImageTransform.Microsoft.Checkerboard(Duration=1,squaresX=20,squaresY=20)");
define("PHOTOALBUM_SLIDE_TYPE_STRIPS_VALUE", "progid:DXImageTransform.Microsoft.Strips(Duration=1,motion=rightdown)");
define("PHOTOALBUM_SLIDE_TYPE_BARN_VALUE", "progid:DXImageTransform.Microsoft.Barn(Duration=1,orientation=vertical)");
define("PHOTOALBUM_SLIDE_TYPE_GRADIENTWIPE_VALUE", "progid:DXImageTransform.Microsoft.GradientWipe(duration=1)");
define("PHOTOALBUM_SLIDE_TYPE_IRIS_VALUE", "progid:DXImageTransform.Microsoft.Iris(Duration=1,motion=out)");
define("PHOTOALBUM_SLIDE_TYPE_WHEEL_VALUE", "progid:DXImageTransform.Microsoft.Wheel(Duration=1,spokes=12)");
define("PHOTOALBUM_SLIDE_TYPE_PIXELATE_VALUE", "progid:DXImageTransform.Microsoft.Pixelate(maxSquare=10,duration=1)");
define("PHOTOALBUM_SLIDE_TYPE_RADIALWIPE_VALUE", "progid:DXImageTransform.Microsoft.RadialWipe(Duration=1,wipeStyle=clock)");
define("PHOTOALBUM_SLIDE_TYPE_RANDOMBARS_VALUE", "progid:DXImageTransform.Microsoft.RandomBars(Duration=1,orientation=vertical)");
define("PHOTOALBUM_SLIDE_TYPE_SLIDE_VALUE", "progid:DXImageTransform.Microsoft.Slide(Duration=1,slideStyle=push)");
define("PHOTOALBUM_SLIDE_TYPE_RANDOMDISSOLVE_VALUE", "progid:DXImageTransform.Microsoft.RandomDissolve(Duration=1,orientation=vertical)");
define("PHOTOALBUM_SLIDE_TYPE_SPIRAL_VALUE", "progid:DXImageTransform.Microsoft.Spiral(Duration=1,gridSizeX=40,gridSizeY=40)");
define("PHOTOALBUM_SLIDE_TYPE_STRETCH_VALUE", "progid:DXImageTransform.Microsoft.Stretch(Duration=1,stretchStyle=push)");

define("PHOTOALBUM_ALBUM_SORT_NONE", 0);	// なし
define("PHOTOALBUM_ALBUM_SORT_NEW", 1);		// 新着順
define("PHOTOALBUM_ALBUM_SORT_VOTE", 2);	// 得票順

define("PHOTOALBUM_SAMPLR_JACKET_DIR", "common/");
define("PHOTOALBUM_SAMPLR_JACKET_PATH", HTDOCS_DIR. "/images/photoalbum/". PHOTOALBUM_SAMPLR_JACKET_DIR);
define("PHOTOALBUM_JACKET_WIDTH", 85);
define("PHOTOALBUM_JACKET_HEIGHT", 85);

define("PHOTOALBUM_THUMBNAIL_WIDTH", 75);
define("PHOTOALBUM_THUMBNAIL_HEIGHT", 50);
define("PHOTOALBUM_THUMBNAIL_STYLE", "width:%dpx;height:%dpx;clip:rect(%dpx,%dpx,%dpx,%dpx);margin-left:%dpx;margin-top:%dpx;");

define("PHOTOALBUM_PHOTO_SORT_NONE", 0);		// なし
define("PHOTOALBUM_PHOTO_SORT_DATE_DESC", 1);	// 日付(新しい順)
define("PHOTOALBUM_PHOTO_SORT_DATE_ASC", 2);	// 日付(古い順)
define("PHOTOALBUM_PHOTO_SORT_PHOTO_NAME", 3);	// 名前順
define("PHOTOALBUM_PHOTO_SORT_VOTE", 4);		// 得票順

define("PHOTOALBUM_PREFIX_REFERENCE", "photoalbum_reference");
define("PHOTOALBUM_PREFIX_ALBUM_LIST", "photoalbum_album_list");

define("PHOTOALBUM_INPUT_FILE_SIZE", 40);

define("PHOTOALBUM_MOBILE_ALBUM_LIST_LENGTH", 5);
define("PHOTOALBUM_MOBILE_PHOTO_LIST_LENGTH", 20);

?>