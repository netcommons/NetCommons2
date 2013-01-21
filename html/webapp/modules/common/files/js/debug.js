/* ****************************** debug.js
 original from http://homepage1.nifty.com/kuraman/js/debug.html
 modified by Kouichirou Eto
 modified by Kazunobu Ichihashi
 
 <<使用方法>>
 １．debug.jsをダウンロードし、デバッグするHTMLにロードする。
   <script language="JavaScript" src="debug.js"></script>
 ２．debug.p()メソッドをコールする。引数に任意のオブジェクトを指定する。
   var str = "任意の文字列";
   var int = 0;
   var function = function(){};
   var object = new Object();
   debug.p(str);
   debug.p(int);
   debug.p(function);
   debug.p(object);
 
 <<メソッド一覧>>
 print(variable)       : variableの内容を出力バッファに保存
 flush()               : 出力バッファの内容をデバッグウィンドウに出力
 clear()               : 出力バッファの内容をクリア
 setDebug(true | false): デバッグ情報を出力する(true)か出力しない(false)かを設定
 inspect(obj)          : オブジェクトの内容をわかりやすい文字列にする
 p(obj)                : inspectした結果を表示する
*/
var debug = new debug();

function debug() {

  // プロパティ
  this.html = "";     // 出力するデバッグ情報のバッファ
  this.hWin = null;   // デバッグ情報を表示するウィンドウオブジェクト
  this.bDebug = true; // デバッグするかどうかのフラグ

  this.now_bgcolor = "";        // 現在の背景色
  this.bgcolor1    = "#FAF5D4"; // 背景色１
  this.bgcolor2    = "#EBCC67"; // 背景色２

	this.level = 0;     // 現在の表示階層
	this.maxLevel = 10; // 最大表示階層（デフォルト10階層）
  // メソッド
  
  /**
   * デバッグを出力するかどうか
   *
   * @param flag boolean true:デバッグする | false:デバッグしない
   */
  this.setDebug = function(flag) {
    this.bDebug = flag;
  }

  /**
   * デバッグ情報の出力バッファをクリアする
   */
  this.clear = function() {
    this.html = "";
    this.flush();
  }

  /**
   * デバッグ情報をデバッグウィンドウに出力する
   */
  this.flush = function() {
    if (false == this.bDebug) return;
    if (null == this.hWin || this.hWin.closed) {
      this.hWin = window.open("", "debug",
        "height=200,width=400,menubar=yes,scrollbars=yes,resizable=yes");
    }
    this.hWin.document.open("text/html", "replace");
    this.hWin.document.write(this.html);
    this.hWin.document.close();
    this.hWin.focus();
  }

  /**
   * デバッグ情報をバッファに追加する
   *
   * @param html string デバッグする変数
   */
  this.print = function(html) {
	  /**
	   * HTML文字列をパースする（内部関数）
	   *
	   * @param str string HTML文字列をパースする文字列
	   */
	  function parseHtml(str){
	  	// 順番が重要！後で「&」を変換するとパースしたものも変換されてしまう
	  	str = str.replace(/&/g, "&amp;");   // 必ず一番先
	  	str = str.replace(/</g, "&lt;");
	  	str = str.replace(/>/g, "&gt;");
	  	str = str.replace(/\"/g, "&quot;");
	  	str = str.replace(/\n/g, "<br>\n"); // 必ず"<" ">"より後ろ
	  	return str;
	  }
  	this.now_bgcolor = (this.now_bgcolor == this.bgcolor1) ? this.bgcolor2 : this.bgcolor1;
    this.html += ("<div style='background-color:"+this.now_bgcolor+"'>" + parseHtml(html) + "</div>\n");
  }

  /**
   * オブジェクトの内容を文字列にする
   *
   * @param obj オブジェクト
   */
  this.inspect = function(obj) {
    if (typeof obj == "number") {
      // 数値の場合
      return ""+obj;

    } else if (typeof obj == "string") {
      // 文字列の場合
      return "\""+obj+"\"";

    } else if (typeof obj == "function") {
      // 関数の場合
      return ""+obj;

    } else if (typeof obj == "object" && obj != null) {
      //objがnullだとエラーとなる
      // オブジェクトの場合
      if(!obj.tagName) {
      	var str = this.to_s(obj, "");
      } else {
      	//html elementの場合、そのまま表示
      	return "<"+(typeof obj)+":"+obj.tagName+">";
      }
      return "{"+str+"}";
    } else {
      // 上記以外の場合
      return "<"+(typeof obj)+":"+obj+">";
    }
  }

	/**
	 * オブジェクトの内容を文字列にする（内部関数）
	 *
	 * @param obj オブジェクト
	 * @param indent インデント
	 */
  this.to_s = function(obj, indent){
    var delimiter = ", \n";    // 区切り文字
    var inner_indent = "　　"; // インデント文字

		this.level += 1;
		if(this.maxLevel < this.level){
			return ""+this.maxLevel+"階層以上は省略します";
		}

	  var str = "";
	  for (key in obj) {
	  	// 区切り文字
		  if (str != "") str += delimiter;
	  	str += indent;
		//keyがfileCreatedDate,mimeType,fileModifiedDate,fileSize･･･だとエラーとなる
	    var value = obj[key];
	    if (!value) {
	    	// キーがあるが値がない場合
	      str += ""+key+"=>undefined";
	      continue;
	    }
	    
			if (typeof value == "number") {
			  // 数値の場合（ key=>value ）
			  str += ""+key+"=>"+value+"";

			} else if (typeof value == "string") {
			  // 文字列の場合（ key=>"value" ）
			  str += ""+key+'=>"'+value+'"';

			} else if (typeof value == "function") {
			  // 関数の場合（ key() ）
			  str += ""+key+"()";

			} else if (typeof value == "object") {
			  // オブジェクトの場合（ key=>value ）
			  value = "\n" + this.to_s(value, indent+inner_indent);
			  str += ""+key+"=>"+value+"";

			} else {
			  // 上記以外の場合（ key=><type:value> ）
		  	  str += ""+key+"=><"+(typeof value)+":"+value+">";
			}
	  }
	  if (str == ""){
	  	// オブジェクトにプロパティがない場合はそのまま表示
	    str += ""+obj;
	  }

		this.level -= 1;

	  return str;
  }

  /**
   * オブジェクトの内容を文字列にして表示する
   *
   * @param elem object オブジェクト
   */
  this.p = function(elem) {
    this.print(this.inspect(elem));
    this.flush();
  }
}
