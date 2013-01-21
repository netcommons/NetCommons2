var clsCommon = Class.create();

clsCommon.prototype = {
	initialize: function() {

		this.moduleList =  new Object();			//モジュールリスト
		this.show_x = new Object();				//位置情報x
		this.show_y = new Object();				//位置情報y
		//this.show_z = new Object();				//位置情報z
		this.move_div = new Object();		//移動中エレメント
		//移動中のデータ保存用
		this.pre_show_x = new Object();
		this.pre_show_y = new Object();
		this.start_x = new Object();			//ドラッグスタート位置x
		this.start_y = new Object();			//ドラッグスタート位置y
		this.speedx = new Object();			//惰性移動用スピードx
		this.speedy = new Object();			//惰性移動用スピードy

		this.inShowLoading = Array();				//ローディング中エレメント配列
		this.inModalEvent = Array();				//モーダルメソッド

		this.max_zIndex = 999;						//最大深度:TODO:後に修正かも！

		this.hideElement =  new Object();			//非表示エレメントリスト

		this.winMoveDragStartEvent = new Object();			//移動スタートイベント
		this.winMoveDragGoEvent = new Object();			//移動中イベント
		this.winMoveDragStopEvent = new Object();			//移動ストップイベント

		this.closeCallbackFuncEvent = new Object();		//removeBlockでブロックを閉じた場合（右上のXボタン）のコールバック処理

		this.inMoveDrag = new Object();

		this.referComp =  new Object();					//コンポーネント参照元

		this.referObject = null;					//アップロード用オブジェクト参照元

		//アップロード関連
		this.inAttachment = Array();							//アップロード中配列
		this.attachmentCallBack = new Object();				//アップロードCallBack関数保持
		this.attachmentErrorCallBack = new Object();			//アップロードエラー時CallBack関数保持
		this.attachmentTarget = new Object();					//アップロードターゲットエレメント保持

		this.error_mes = "error_message:";						//エラーメッセージ用文字列
		this.fatal_error_mes = "^<br \/>\n<b>Fatal error<\/b>:(.|\n|\r|\t)*<\/b>";

		//this.cssBlockCustom = "block_custom.css";
		//ツールチップ用
		this.toolTipPopup   = null;
		this.inToolTipEvent = new Object();
		this.toolTipPopupTimer = null;

		this.sess_timer = null;
		this.timeout_time = null;
		this.session_timeout_alert = null;
	},
	/*共通初期関数*/
	commonInit: function(session_timeout_alert, timeout_time) {
		// ヘッダーボタン移動
		//var bodyWidth = Position.getWinOuterWidth() + document.documentElement.scrollLeft;
		var header_menu_el = $("header_menu");
		var bodyWidth = Position.getWinOuterWidth();
		if(header_menu_el && header_menu_el.offsetWidth > bodyWidth) {
			var menu_right_el = Element.getChildElementByClassName(header_menu_el,"menu_right");
			if(menu_right_el) {
				var header_margin = header_menu_el && header_menu_el.offsetWidth - bodyWidth;
				Element.setStyle(menu_right_el, {"paddingRight":header_margin+"px"});
			}
		}

		// 1分前
		this.timeout_time = timeout_time*1000 - 60*1000;
		this.session_timeout_alert = session_timeout_alert;
		this.setTimeoutAlert();
		//Event.observe(document,"mousedown",this.winMouseDownEvent.bindAsEventListener(this),false);

	},
	setTimeoutAlert: function() {
		if(this.sess_timer != null) {
			clearTimeout(this.sess_timer);
			this.sess_timer = null;
		}
		if(_nc_user_id != '0') {
			this.sess_timer = setTimeout(function(){commonCls.alert(this.session_timeout_alert);}.bind(this), this.timeout_time);
		}
	},
	/*ページモジュール初期関数*/
	moduleInit: function(id, chief_flag) {
		var el = $(id);
		if(!el) return;
		var parent_el = Element.getParentElement(el);
		var absolute_flag = false;
		if(parent_el && !Element.hasClassName(parent_el,"cell")
				&& !Element.hasClassName(parent_el,"main_column")
				&& parent_el.tagName != "BODY" && !Element.hasClassName(parent_el,"enlarged_display"))
			absolute_flag = true;
		commonCls.parentWinInit(el, absolute_flag, chief_flag);
	},
	/* ローディング中表示　　　　　　　　　 									  */
	/* inShowLoading[id_name+parameters]に 									　    */
	/* フラグセット(表示中かどうか)												　*/
	/* show_x,show_y指定 or loading_el(ローディングする箇所のエレメント指定)とする*/
	showLoading: function(id_name, parameters, show_x, show_y, loading_el) {
		id_name = (id_name != undefined && id_name != null) ? id_name : "";
		parameters = (parameters != undefined && parameters != null) ? parameters : "";

		commonCls.hideLoading(id_name, parameters);

		var div_parent = document.createElement("DIV");
		div_parent.innerHTML = "<div class=\"loading\"><img text=\"loading\" alt=\"loading\" src=\"" + _nc_core_base_url + "/images/common/indicator.gif\"/></div>";
		var div = div_parent.childNodes[0];

		//値セット
		//document.body.appendChild(div_parent);
		this.inShowLoading[id_name + parameters] = div_parent;

		Element.addClassName(div,"loading");

		//ローディング中は、ほかの処理をさせたくないため透明画像 or iframeを挿入(FireFox(XAMPP)の場合、固まるため)
		commonCls.showModal(null,div_parent);

		if(loading_el && (show_x==undefined && show_y == undefined)) {
			//loging_imageのoffset(大きさの半分の値)
			//loadingimageに合わせて変更しなければならない
			//現状、固定値
			var loading_imege_offset_x = 8;
			var loading_imege_offset_y = 8;

			//エレメントの中心にローディング表示
			var offset = Position.cumulativeOffset(loading_el);

			var ex1 = offset[0];
			var ey1 = offset[1];

			div.style.left = (ex1 + (loading_el.offsetWidth/2) - loading_imege_offset_x) +"px";
			div.style.top  = (ey1 + (loading_el.offsetHeight/2) - loading_imege_offset_y) +"px";
		} else {
			//show_x, show_yの位置にローディング表示
			div.style.left = show_x +"px";
			div.style.top = show_y +"px";
		}
		document.body.appendChild(div_parent);
	},
	/* ローディング中非表示　　　　　　　　　 */
	hideLoading: function(id_name, parameters) {
		id_name = (id_name != undefined && id_name != null) ? id_name : "";
		parameters = (parameters != undefined && parameters != null) ? parameters : "";
		//削除
		if(this.inShowLoading[id_name + parameters]) {
			commonCls.stopModal(this.inShowLoading[id_name + parameters]);
			Element.remove(this.inShowLoading[id_name + parameters]);
			this.inShowLoading[id_name + parameters] = null;
			//null配列削除
			//this.inShowLoading = this.inShowLoading.compact();
			return true;
		}
		return false;
	},
	showModal: function(event, el, loading_flag) {
		el = (event == undefined || event == null) ? el : this;
		//var w = (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth || 0);
		//var h = (window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight || 0);
		var scroll_left = (document.documentElement.scrollLeft || document.body.scrollLeft || 0);
		var scroll_top = (document.documentElement.scrollTop || document.body.scrollTop || 0);
		var offset = 0;
		var w = Position.getWinOuterWidth();
		var h = Position.getWinOuterHeight();
		el.style.width =  (w + scroll_left - offset)  +"px";
		el.style.height =  (h + scroll_top - offset) +"px";
//el.style.border = "5px solid #000000";
		if(loading_flag) {
			el.style.backgroundColor = "#cccccc";
			Element.setStyle(el, {"opacity":0.2});
		}
		el.style.position = "absolute";
		el.style.left = "0px";
		el.style.top = "0px";

		if(event == undefined || (event.type != "scroll" && event.type != "resize")) {
			commonCls.max_zIndex = commonCls.max_zIndex + 1;
			el.style.zIndex = commonCls.max_zIndex;

			commonCls.inModalEvent[el] = commonCls.showModal.bindAsEventListener(el);
			Event.observe(window,"scroll",commonCls.inModalEvent[el],false);
			Event.observe(window,"resize",commonCls.inModalEvent[el],false);
			if(browser.isIE) {
				var img_blank = document.createElement("img");
				img_blank.src = _nc_core_base_url + "/images/common/blank.gif";
				el.appendChild(img_blank);
			}

			if(browser.isIE) {
				if(img_blank==undefined) {
					var img_blank = Element.getChildElement(el);
				}
				img_blank.style.width = el.style.width;
				img_blank.style.height = el.style.height;
			}
		}
	},
	stopModal: function(el) {
		Event.stopObserving(window,"scroll", commonCls.inModalEvent[el], false);
		Event.stopObserving(window,"resize",commonCls.inModalEvent[el],false);
		commonCls.inModalEvent[el] = null;
	},
	/* ブロック画面表示切替用　　　　　　 */
	sendView: function(id, parameter, params, headermenu_flag) {
		//Event.unloadCache(id);
		var top_el = $(id);

		//パラメータ取得
		if(params == undefined) {
			var params = new Object();
		}

		params["focus_flag"] = 1;
		if(typeof parameter == 'string') {
			var re_action = new RegExp("^action=", 'i');
			if(parameter.match(re_action)) {
				params["param"] = parameter;
			} else {
				params["param"] = {"action":parameter};
			}
		} else {
			params["param"] = parameter;
		}

		params["top_el"] = top_el;
		var content = "";
		if(headermenu_flag != null && headermenu_flag != undefined) {
			var headermenu = Element.getChildElementByClassName(top_el,"_headermenu");
			if(headermenu) {
				var div_headermenu = document.createElement("DIV");
				div_headermenu.className = headermenu.className;
				div_headermenu.innerHTML = headermenu.innerHTML;
				params["headermenu"] = div_headermenu;
			}
		}

		//var _move_header_el = $("_move_header" + id);
		//if(_move_header_el && !Element.hasClassName(_move_header_el, "display-none")) {
		//	var header_flag = true;
		//}
		if(params["target_el"] === undefined) params["target_el"] = top_el.parentNode;
		if(params["loading_el"] === undefined) params["loading_el"] = top_el;

		//params["callbackfunc"] = function() {
		//	var _move_header_el = $("_move_header" + id);
		//	if(_move_header_el) {
		//		_move_header_el.onmouseover(null, _move_header_el);
		//	}
		//	if(typeof pagesCls != 'undefined') {
		//		if(header_flag) {
		//			var _move_header_el = $("_move_header" + id);
		//			Element.removeClassName(_move_header_el, "display-none")
		//		}
		//		pagesCls.winMoveResizeHeader();
		//	}
		//}.bind(this);
		commonCls.send(params);
		//var url = commonCls.send(params);
		//if(content != "") {
		//	var url_el = Element.getChildElementByClassName(top_el,"_url");
		//	url_el.value = url;
		//}
	},
	/* POST用 */
	sendPost: function(id, parameter, post_params) {
		//Event.unloadCache(id);
		var top_el = null;
		if(id) {
			top_el = $(id);
		}

		//パラメータ取得
		if(post_params == undefined) {
			var post_params = new Object();
		}

		if(typeof parameter == 'string') {
			var re_action = new RegExp("^action=", 'i');
			if(parameter.match(re_action)) {
				post_params["param"] = parameter;
			} else {
				post_params["param"] = {"action":parameter};
			}
		} else {
			post_params["param"] = parameter;
		}
		if(!post_params["method"]) post_params["method"] = "post";

		post_params["top_el"] = top_el;
		if(post_params["loading_el"] === undefined) post_params["loading_el"] = top_el;

		commonCls.send(post_params);
	},
	// 画面再表示
	sendRefresh: function(id, params) {
		commonCls.sendView(id, commonCls.getUrl($(id)).parseQuery(), params);
	},
	// ポップアップでブロック表示
	sendPopupView: function(event, parameter, params) {
		if(params == undefined) {
			var params = new Object();
		}
		if(parameter != undefined && parameter != null) {
			params["param"] = parameter;
		} else {
			parameter = params["param"];
		}

		if(params['top_el'] != undefined || params['top_el'] != null) {
			params['url'] = commonCls._paramEncode(params['param'], params['form_el']);
			params = commonCls._setParam(params);
			//params['top_el'] = null;
			params['top_el_id'] = params['top_el'].id;
			var id = commonCls._getId(params['url']);
		} else if(parameter && parameter.tagName == undefined && typeof parameter == 'object') {
			var param_str = "";
			for(key in parameter) {
				if(param_str != "") {
					param_str += "&";
				}
				param_str += key + "=" + parameter[key];
			}
			var id = commonCls._getId(param_str);
		} else {
			var id = commonCls._getId(parameter);
		}
		/*
		if(parameter && parameter.tagName == undefined && typeof parameter == 'object') {
			var param_str = "";
			for(key in parameter) {
				if(param_str != "") {
					param_str += "&";
				}
				param_str += key + "=" + parameter[key];
			}
			var id = commonCls._getId(param_str);
		} else {
			var id = commonCls._getId(parameter);
		}
		*/
		if(!commonCls.moduleList[id] || params['modal_flag'] == true) {
			commonCls.moduleList[id] = "dummy";
			if(!params["loading_el"] && event) {
				params["loading_el"] = Event.element(event);
			}

			params["create_flag"] = true;
			if(event) {
				params["event"] = event;
			}
			params["callbackfunc_error"] = function(res) {commonCls.alert(res);commonCls.moduleList[id]=null;}.bind(id);
			commonCls.send(params);
		} else if(commonCls.moduleList[id] == "dummy") {
			//表示中
		} else {
			//既に表示中
			//最前面：位置移動処理
			var current_el = commonCls.moduleList[id];
			var top_el = params["top_el"]
			if(params['center_flag']) {
				var center_position = commonCls.getCenterPosition(current_el, top_el);
				var x = center_position[0];
				var y = center_position[1];
			} else {
				var x = (params['x'] != null && params['x'] != undefined) ? params['x'] : Event.pointerX(event);
				var y = (params['y'] != null && params['y'] != undefined) ? params['y'] : Event.pointerY(event);
			}

			current_el.style.left = x + "px";
			current_el.style.top = y + "px";
			var move_pos = commonCls.moveAutoPosition(current_el);
			if(move_pos != null) {
				x = move_pos[0];
				y = move_pos[1];
			}
			//commonCls.show_x[id] = x;
			//commonCls.show_y[id] = y;
			if(commonCls.moduleList[id].style.zIndex != commonCls.max_zIndex) {
				commonCls.max_zIndex = commonCls.max_zIndex + 1;
				current_el.style.zIndex = commonCls.max_zIndex;
			}
		}
		//if(event) {
		//	Event.stop(event);
		//}
	},
	// closeCallbackFunc：removeBlockでブロックを閉じた場合（右上のXボタン）のコールバック処理
	closeCallbackFunc: function(id, func) {
		this.closeCallbackFuncEvent[id] = func;
	},
	//ブロック表示：非表示
	displayBlockChange: function(id) {
		var block_el = $(id);
		//ブロック内容
		var content = Element.getChildElementByClassName(block_el,"content");
		commonCls.displayChange(content);

		commonCls.moveVisibleHide(block_el);
	},
	//ブロックを閉じる
	removeBlock: function(id) {
		var block_el = $(id);
		if (typeof id != 'string') {
			id = id.id;
		}
		var block_el = $(id);
		if(block_el.parentNode && block_el.parentNode.tagName.toLowerCase() == "body") {
			// ブロックそのものがメインに表示しているため、処理しない
			return true;
		}

		Event.unloadCache(id);
		if(id) {
			var _global_modal_dialog = $("_global_modal_dialog" + id);
		}
		if(!_global_modal_dialog) {
			_global_modal_dialog = $("_global_modal_dialog");
		}
		if(_global_modal_dialog) {
			Element.remove(_global_modal_dialog);
			commonCls.stopModal(_global_modal_dialog);
		}
		var get_id = commonCls._getId(block_el);
		delete commonCls.moduleList[get_id];
		commonCls.moduleList[get_id] = null;
		commonCls.displayChange(block_el);
		commonCls.moveVisibleHide(block_el);
		var parent_el = block_el.parentNode;
		Element.remove(block_el);
		if(parent_el && Element.hasClassName(parent_el,"_global_create_block")) {
			Element.remove(parent_el);
		}
		if(this.closeCallbackFuncEvent[id]) {
			this.closeCallbackFuncEvent[id]();
			delete this.closeCallbackFuncEvent[id];
			this.closeCallbackFuncEvent[id] = null;
		}
	},
	//el下のエレメント中の特定エレメントを非表示へ
	//IE限定
	moveVisibleHide: function(el) {
		el = (el && el.nodeType==1) ? el : this;
		//visible-hide


		//var divList = box_el.getElementsByTagName("div");

		//エレメントの中心にローディング表示
		var offset = Position.cumulativeOffset(el);

		var ex1 = offset[0];
		var ex2 = el.offsetWidth + ex1;
		var ey1 = offset[1];
		var ey2 = el.offsetHeight + ey1;

		var id_name = (el.id == "" || Element.hasClassName(el,"_global_create_block")) ? el.childNodes[0].id : el.id;

		//コンテキストメニュー
		/*
		var context = Element.getChildElementByClassName(el,"context");
		if(context) {
			var con_offset = Position.cumulativeOffset(context);
			var con_ex1 = con_offset[0];
			var con_ex2 = context.offsetWidth + con_ex1;
			var con_ey1 = con_offset[1];
			var con_ey2 = context.offsetHeight + con_ey1;
		} else {
			var con_ex1 = 0;
			var con_ex2 = 0;
			var con_ey1 = 0;
			var con_ey2 = 0;
		}
		*/
		//初期化
		if(commonCls.hideElement[id_name] == null)
			commonCls.hideElement[id_name] = Array();

		//非表示にする配列
		if(browser.isIE && browser.version < 7) {
			var tags = new Array("applet", "select", "object","embed");
		} else {
			var tags = new Array("embed", "object");
		}
		var tags_length = tags.length;
		for (var k = tags_length; k > 0; ) {
			var target_ar = document.getElementsByTagName(tags[--k]);
			var target_ar_length = target_ar.length;
			for (var i = target_ar_length; i > 0;) {
				var target = target_ar[--i];

				offset = Position.cumulativeOffset(target);
				var cx1 = offset[0];
				var cx2 = target.offsetWidth + cx1;
				var cy1 = offset[1];
				var cy2 = target.offsetHeight + cy1;
				//if(Element.hasClassName(target,"visible-hide")) {
				//	Element.removeClassName(target,"visible-hide");
				//}
				//if (((cx1 > ex2) || (cx2 < ex1) || (cy1 > ey2) || (cy2 < ey1)) && ((cx1 > con_ex2) || (cx2 < con_ex1) || (cy1 > con_ey2) || (cy2 < con_ey1))) {
				//if (((cx1 > ex2) || (cx2 < ex1) || (cy1 > ey2) || (cy2 < ey1)) && ((cx1 > 0) || (cx2 < 0) || (cy1 > 0) || (cy2 < 0))) {
				if (((cx1 > ex2) || (cx2 < ex1) || (cy1 > ey2) || (cy2 < ey1))) {
					if(Element.hasClassName(target,"visible-hide")) {
						for (var key = 0,hide_el_length = commonCls.hideElement[id_name].length; key < hide_el_length; key++) {
						//for(var key in commonCls.hideElement[id_name]){
							var value = commonCls.hideElement[id_name][key];
							if(value) {
								if(target == value) {
									commonCls.hideElement[id_name][key] = null;
									Element.removeClassName(target,"visible-hide");
									break;
									//chk_flag = false;
								}
							}
						}
						//if(!chk_flag)
						//	Element.removeClassName(target,"visible-hide");
						//commonCls.hideElement[target] = null;

					}
				} else {
					//移動している内部
					var children = el.getElementsByTagName('*') || document.all;
					var chk_flag = true;
					//if (((cx1 > con_ex2) || (cx2 < con_ex1) || (cy1 > con_ey2) || (cy2 < con_ey1))) {
						var children_length = children.length;
						for (var j = 0; j < children_length; j++) {
							var child = children[j];
							if(child == target) {
								chk_flag = false;
								break;
							}
						}
					//}
					if(chk_flag) {
						if(!Element.hasClassName(target,"visible-hide")) {
							//commonCls.hideElement[target] = id_name;
							commonCls.hideElement[id_name][commonCls.hideElement[id_name].length] = target;
							Element.addClassName(target,"visible-hide");
						}
					} else {
						if(Element.hasClassName(target,"visible-hide")) {
							Element.removeClassName(target,"visible-hide");
							//commonCls.hideElement[target] = null;
							for (var key = 0,hide_el_length = commonCls.hideElement[id_name].length; key < hide_el_length; key++) {
								var value = commonCls.hideElement[id_name][key];
								if(value) {
									if(target == value)
										commonCls.hideElement[id_name][key] = null;
								}
							}
						}
					}

				}
			}
		}
	},
	//-----------------------------------------------------------------------------
	// 移動
	//-----------------------------------------------------------------------------
	winMoveDragStart: function(event) {
		var page_id_name =this.id;
		var id = commonCls._getId(this);

		//移動中はキャンセル
		if(commonCls.inMoveDrag[id]) {
			return false;
		}

		// ポップアップを閉じる
		//commonCls.closePopupMenu(event);

		//深度を最前面へ
		//すでに最前面ならば処理しない
		var this_el = commonCls.moduleList[id];

		if(this_el.style.zIndex != commonCls.max_zIndex) {
			commonCls.max_zIndex = commonCls.max_zIndex + 1;
			this_el.style.zIndex = commonCls.max_zIndex;
			//if(this_el != this)
			//	commonCls.moveVisibleHide(this_el.childNodes[0]);
			//else
				commonCls.moveVisibleHide(this_el);
		}
		commonCls.move_div[id] = this_el;

		//スタート位置
		commonCls.start_x[id] = Event.pointerX(event);
		commonCls.start_y[id] = Event.pointerY(event);

		commonCls.show_x[id] = valueParseInt(commonCls.moduleList[id].style.left);
		commonCls.show_y[id] = valueParseInt(commonCls.moduleList[id].style.top);

		commonCls.pre_show_x[id] = valueParseInt(commonCls.moduleList[id].style.left);
		commonCls.pre_show_y[id] = valueParseInt(commonCls.moduleList[id].style.top);

		//イベント追加
		commonCls.winMoveDragGoEvent[id] = commonCls.winMoveDragGo.bindAsEventListener(this);
		commonCls.winMoveDragStopEvent[id] = commonCls.winMoveDragStop.bindAsEventListener(this);
		Event.observe(document,"mousemove",commonCls.winMoveDragGoEvent[id],true);
		Event.observe(document,"mouseup",commonCls.winMoveDragStopEvent[id],true);
		Event.stop(event);

		//ブロックコンテキスト非表示
		//commonCls.displayNone($("block_context_menu"));

		commonCls.inMoveDrag[id] = true;
	},
	winMoveDragGo: function(event) {
		var page_id_name =this.id;
		var id = commonCls._getId(this);

		//移動中はキャンセル
		if(!commonCls.inMoveDrag[id]) {
			return false;
		}

		var x = Event.pointerX(event);
		var y = Event.pointerY(event);

		// もし、ドラッグスタート位置から、離れていない場合、ドラッグしていないものとみなす
		// 5px以内に設定
		var def_px = 5;
		if(x <= commonCls.start_x[id] + def_px && x >= commonCls.start_x[id] - def_px &&
			y <= commonCls.start_y[id] + def_px && y >= commonCls.start_y[id] - def_px) {
			return false;
		}

		//スクロールバー移動
		//40pxづつ移動
		//commonCls.scrollMoveDrag(event);

		var show_x = commonCls.show_x[id] - (commonCls.start_x[id] - x);
		var show_y = commonCls.show_y[id] - (commonCls.start_y[id] - y);
		if(show_x < 0)
			show_x = 0;
		if(show_y < 0)
			show_y = 0;

		commonCls.pre_show_x[id] = valueParseInt(commonCls.moduleList[id].style.left);
		commonCls.pre_show_y[id] = valueParseInt(commonCls.moduleList[id].style.top);
		commonCls.moduleList[id].style.left = show_x +"px";
		commonCls.moduleList[id].style.top = show_y +"px";

		commonCls.moveVisibleHide(commonCls.moduleList[id]);

		Event.stop(event);
	},
	winMoveDragStop: function(event) {
		var page_id_name =this.id;
		var id = commonCls._getId(this);

		if(!commonCls.inMoveDrag[id]) {
			commonCls.inMoveDrag[id] = false;
			return false;
		}

		//イベントストップ
		Event.stopObserving(document,"mousemove",commonCls.winMoveDragGoEvent[id],true);
		Event.stopObserving(document,"mouseup",commonCls.winMoveDragStopEvent[id],true);
		commonCls.winMoveDragGoEvent[id] = null;
		commonCls.winMoveDragStopEvent[id] = null;
		Event.stop(event);

		//モジュール
		commonCls.show_x[id] = valueParseInt(commonCls.moduleList[id].style.left);
		commonCls.show_y[id] = valueParseInt(commonCls.moduleList[id].style.top);

		var interval = 50;
		commonCls.speedx[id] = (commonCls.show_x[id]-commonCls.pre_show_x[id]);
		commonCls.speedy[id] = (commonCls.show_y[id]-commonCls.pre_show_y[id]);

		if(commonCls.speedx[id] > 10)
			commonCls.speedx[id] = 10;
		else if(commonCls.speedx[id] < -10)
			commonCls.speedx[id] = -10;

		if(commonCls.speedy[id] > 10)
			commonCls.speedy[id] = 10;
		else if(commonCls.speedy[id] < -10)
			commonCls.speedy[id] = -10;

		setTimeout("commonCls.winMoveDragStopAfter(\""+ id +"\")", interval);
		commonCls.inMoveDrag[id] = false;
	},
	winMoveDragStopAfter: function(id) {
		//if(commonCls.inMoveDrag[page_id_name])
		//	return false;

		if(commonCls.speedx[id] > 0)
			commonCls.speedx[id] = commonCls.speedx[id] - 1;
		else if(commonCls.speedx[id] < 0)
			commonCls.speedx[id] = commonCls.speedx[id] + 1;

		if(commonCls.speedy[id] > 0)
			commonCls.speedy[id] = commonCls.speedy[id] - 1;
		else if(commonCls.speedy[id] < 0)
			commonCls.speedy[id] = commonCls.speedy[id] + 1;

		var show_x = valueParseInt(commonCls.move_div[id].style.left) + commonCls.speedx[id];
		var show_y = valueParseInt(commonCls.move_div[id].style.top) + commonCls.speedy[id];
		if(show_x < 0)
			show_x = 0;
		if(show_y < 0)
			show_y = 0;

		commonCls.move_div[id].style.left = show_x +"px";
		commonCls.move_div[id].style.top = show_y +"px";

		commonCls.show_x[id] = show_x;
		commonCls.show_y[id] = show_y;

		//中心点を求める
		//commonCls.center_box_x[page_id_name] = commonCls.width_box[page_id_name] + commonCls.show_x[page_id_name];
		//commonCls.center_box_y[page_id_name] = commonCls.width_box[page_id_name] + commonCls.show_y[page_id_name];
		if(commonCls.speedx[id] != 0 || commonCls.speedy[id] != 0) {
			var interval = 50;
			setTimeout("commonCls.winMoveDragStopAfter(\""+ id +"\")", interval);
		}
	},
	/* enterMode */
	blockNotice: function(event, el) {
		//if(typeof pagesCls == 'undefined' || pagesCls.inMoveDrag)
		//	return false;
		if(typeof(Event.element) != 'undefined') {
			var el = (el == undefined) ? Event.element(event) : el;
			if(!Element.hasClassName(el,"highlight")) {
				var rgbBack = commonCls.getRGBtoHex(Element.getStyle(el, "backgroundColor"));
				if (rgbBack == "transparent") {
					var parent_el = el;
					while (rgbBack == "transparent") {
						if(parent_el.tagName == "BODY") {
							rgbBack = new Object();
				        	rgbBack.r = 255;
				        	rgbBack.g = 255;
				        	rgbBack.b = 255;
				        	break;
				        }
			        	var parent_el = parent_el.parentNode;
			        	rgbBack = commonCls.getRGBtoHex(Element.getStyle(parent_el, "backgroundColor"));
			    	}
				}

				//Element.removeClassName(el,"_block_titlecolor");
				Element.addClassName(el,"highlight");
				setTimeout(function(){
					commonCls.blockNoticeTimer(el, rgbBack);
				}, 200);
			}
		}
	},
	blockNoticeTimer: function(el,rgbBack) {
		var offset = 10;
		var rgb = commonCls.getRGBtoHex(Element.getStyle(el, "backgroundColor"));

		if (rgb == "transparent") {
			var parent_el = el;
			while (rgb == "transparent") {
				if(parent_el.tagName == "BODY") {
					rgb = new Object();
		        	rgb.r = 255;
		        	rgb.g = 255;
		        	rgb.b = 255;
		        	break;
		        }
	        	var parent_el = parent_el.parentNode;
	        	rgb = commonCls.getRGBtoHex(Element.getStyle(parent_el, "backgroundColor"));
	    	}
		}
		if(rgb.r > rgbBack.r) rgb.r = (rgb.r - offset < rgbBack.r) ? rgbBack.r : rgb.r - offset;
		else if(rgb.r < rgbBack.r) rgb.r = (rgb.r + offset > rgbBack.r) ? rgbBack.r : rgb.r + offset;
		if(rgb.g > rgbBack.g) rgb.g = (rgb.g - offset < rgbBack.g) ? rgbBack.g : rgb.g - offset;
		else if(rgb.g < rgbBack.g) rgb.g = (rgb.g + offset > rgbBack.g) ? rgbBack.g : rgb.g + offset;
		if(rgb.b > rgbBack.b) rgb.b = (rgb.b - offset < rgbBack.b) ? rgbBack.b : rgb.b - offset;
		else if(rgb.b < rgbBack.b) rgb.b = (rgb.b + offset > rgbBack.b) ? rgbBack.b : rgb.b + offset;
		//if(rgb.r > 255 ) rgb.r = 255;
		//if(rgb.g > 255 ) rgb.g = 255;
		//if(rgb.b > 255 ) rgb.b = 255;
		//if(rgb.r < 0 ) rgb.r = 0;
		//if(rgb.g < 0 ) rgb.g = 0;
		//if(rgb.b < 0 ) rgb.b = 0;
		Element.setStyle(el, {"backgroundColor":commonCls.getHex(rgb.r,rgb.g,rgb.b)});
		if(rgb.r == rgbBack.r && rgb.g == rgbBack.g && rgb.b == rgbBack.b) {
			if(Element.hasClassName(el,"highlight"))Element.removeClassName(el,"highlight");
			Element.setStyle(el, {"backgroundColor":""});
			//Element.addClassName(el,"_block_titlecolor");
		} else {
			setTimeout(function(){
				commonCls.blockNoticeTimer(el, rgbBack);
			}, 200);
		}
	},
	/*********************************************************/
	/*URLのパラメータ取得関数								 */
	/*@param object top_el or cell_el or block_id			 */
	/*@return array array['key']の形で取得 					 */
	/*********************************************************/
	getParams: function(top_el) {
		var url = commonCls.getUrl(top_el);
		if(url) {
			var re_cut = new RegExp(".*\\?", "i");
			url = url.replace(re_cut,"").replace(/&amp;/g,"&");
			var queryParams = url.parseQuery();
			return queryParams;
		} else
			return false;
	},
	/*********************************************************/
	/*URL取得関数											 */
	/*@param object top_el or cell_el or block_id			 */
	/*@return string					 					 */
	/*********************************************************/
	getUrl: function(top_el) {
		if (typeof top_el == 'string') {
			top_el = $(top_el);
		} else if(top_el.tagName == "DIV" && Element.hasClassName(top_el,"cell")) {
			top_el = Element.getChildElement(top_el);
		}
		var url_el = $("_url"+ top_el.id);
		if(!url_el){url_el = Element.getChildElementByClassName(top_el,"_url");}
		if(url_el) {
			return url_el.value.replace(/&amp;/g,"&");
		} else
			return false;
	},
	/*********************************************************/
	/*block_id取得関数										 */
	/*@param object top_el or cell_el						 */
	/*@return string block_id			 					 */
	/*********************************************************/
	getBlockid: function(top_el) {
		if(top_el.tagName == "DIV" && Element.hasClassName(top_el,"cell")) {
			top_el = Element.getChildElement(top_el);
		}
		var id_name = top_el.id;
		if(!id_name) {
			return false;

		}
		return id_name.substr(1, id_name.length);
	},
	setToken: function(id, token_value) {
		var token_el = $(id);
		if(token_el) token_el.value = token_value;
	},
	getToken: function(top_el) {
		if (typeof top_el == 'string') {
			top_el = $(top_el);
		} else if(top_el.tagName == "DIV" && Element.hasClassName(top_el,"cell")) {
			top_el = Element.getChildElement(top_el);
		}
		var token_el = $("_token"+ top_el.id);
		if(!token_el){token_el = Element.getChildElementByClassName(top_el,"_token");}
		if(token_el) {
			return token_el.value;
		} else
			return false;
	},
	/*********************************************************/
	/*Ajax送信一般関数										 */
	/*@param object											 */
	/*top_el：モジュール毎のトップエレメント（必須)		  　 */
	/*param：object(string)パラメータ						 */
	/*form_el：form内のフィールド名とその値のリストを		 */
	/*         パラメータに加える                            */
	/*method：get or post (default get)						 */
	/*match_str：正常かどうかのMatch文字列(正規表現)	 	 */
	/*			 nullの場合、空文字以外が返ってきた場合  	 */
    /*			 alert表示								 	 */
    /*target_el：ターゲットエレメント						 */
    /*loading_el：ロード画像出力先エレメント			 	 */
    /*         load_elの中心にload画像を表示			 	 */
    /*loading_x：load画像位置x座標						 	 */
    /*        ロード画像がnullの場合、使用				 	 */
    /*loading_y：load画像位置y座標						 	 */
	/*header_flag：ヘッダーありなし defaultなし		 	 	 */
	/*callbackfunc:コールバック関数						 	 */
	/*callbackfunc_error:エラー時コールバック関数		     */
	/*func_param:コールバック関数パラメータ				     */
	/*func_error_param:エラー時コールバック関数パラメータ　  */
	/*create_flag: target_el指定がない場合、				 */
	/*             動的に要素を作成するか default しない	 */
	/*center_flag:create_flagがtrueの場合、　				 */
	/*            新規エレメントの位置を画面の中心へ		 */
	/*            create_flagがfalseの場合、				 */
	/*            センターカラムに表示。					 */
	/*            センターカラムがない場合、target_elに表示	 */
	/*				(target_el必須)							 */
	/*x:create_flagがtrueの場合、新規エレメントの表示位置　  */
	/*y:create_flagがtrueの場合、新規エレメントの表示位置　  */
	/*modal_flag:create_flagがtrueの場合、modalにするかどうか*/
	/*focus_flag:top_el指定があれば、最初の位置　　　　      */
	/*      へfocusを移動       　　　　                     */
	/*debug:trueの場合、送信内容をalertする				 　  */
	/*token:tokenを手動でセットする場合に使用			 　  */
	/*********************************************************/
	send: function(params_obj) {
		//
		//paramセット
		//
		if(params_obj['url'] == null || params_obj['url'] == undefined) {
			params_obj['url'] = commonCls._paramEncode(params_obj['param'], params_obj['form_el']);
		}
		if(params_obj['url'] == "") {
			var error_mes = "The parameter is illegal.";
			if(callbackfunc_error){
				if(params_obj['func_error_param'] == undefined) {params_obj['callbackfunc_error'](error_mes);}
				else{params_obj['callbackfunc_error'](params_obj['func_error_param'],error_mes);}
			} else {
				_debugShow(error_mes);
			}
			return false;
		}
		params_obj['method'] = (params_obj['method'] == undefined || params_obj['method'] == null) ? "get" : params_obj['method'];
		params_obj['token'] = (params_obj['token'] == undefined || params_obj['token'] == null) ? "" : params_obj['token'];
		//if(loading_el && Element.hasClassName(loading_el.parentNode,"cell")) {
		//	loading_el = Element.getChildElement(loading_el,4);
		//}
		params_obj['header_flag'] = (params_obj['header_flag'] == undefined || params_obj['header_flag'] == null) ? false : params_obj['header_flag'];
		params_obj['create_flag'] = (params_obj['create_flag'] == undefined || params_obj['create_flag'] == null) ? false : params_obj['create_flag'];
		params_obj['center_flag'] = (params_obj['center_flag'] == undefined || params_obj['center_flag'] == null) ? false : params_obj['center_flag'];
		if(params_obj['create_flag'] && !params_obj['center_flag']) {
			params_obj['x'] = (params_obj['x'] == undefined || params_obj['x'] == null) ? Event.pointerX(params_obj['event']) : params_obj['x'];
			params_obj['y'] = (params_obj['y'] == undefined || params_obj['y'] == null) ? Event.pointerY(params_obj['event']) : params_obj['y'];
		}
		params_obj['show_main_flag'] = false;
		if(!params_obj['create_flag'] && params_obj['center_flag']) {
			params_obj['center_col'] = $("_centercolumn");
			if(params_obj['center_col']) {
				params_obj['show_main_flag'] = true;
			}
		}
		params_obj['eval_flag'] = (params_obj['eval_flag'] == undefined || params_obj['eval_flag'] == null) ? 1 : parseInt(params_obj['eval_flag']);

		//params_obj['script_flag'] = (params_obj['script_flag'] == undefined || params_obj['script_flag'] == null) ? true : params_obj['script_flag'];

		//
		//URL
		//
		if(params_obj['top_el_id'] == undefined && (params_obj['top_el'] != undefined && params_obj['top_el'] != null)) {
			params_obj = commonCls._setParam(params_obj);
		} else {
			params_obj['top_el_id'] = "";
		}
		if(params_obj['token']) {params_obj['url'] = params_obj['url'] + "&_token=" + params_obj['token'];}
		if(params_obj['header_flag']) {params_obj['url'] += "&_header=1";}else{ params_obj['url'] += "&_header=0";}
		//show_main_flag
		if(params_obj['show_main_flag']) {params_obj['url'] += "&_show_main_flag=1";}

		if(params_obj['debug']) {commonCls._debugShow(params_obj['url']);}

		params_obj['complete_flag'] = false;
		new Ajax.Request(_nc_base_url + _nc_index_file_name , {
			method:     params_obj['method'],
            noautoeval: true,
			parameters: params_obj['url'],
			requestHeaders: ["Referer",_nc_current_url],
			onLoading: function() {
				if(!this['complete_flag'] && (this['loading_el'] || (this['loading_x'] && this['loading_y']))) {
					commonCls.showLoading(this['top_el_id'],this['url'],this['loading_x'],this['loading_y'],this['loading_el']);
				}
			}.bind(params_obj),
			onComplete: function(transport) {
				_nc_global_script_write_html = '';
				_nc_global_script_span = null;
				this['complete_flag'] = true;
				if(this['debug']) {commonCls._debugShow(transport.responseText);}
				//ローディング非表示
				if(this['loading_el'] || (this['loading_x'] && this['loading_y'])){commonCls.hideLoading(this['top_el_id'],this['url']);}
				if(this['target_el'] && this['top_el_id'] != "" && this['target_el'].id == this['top_el_id']) {
					this['target_el'] = this['target_el'].parentNode;
				}
				var target_flag = false;
				if(this['create_flag']) {
					//var position_el = this['target_el'];
					this['target_el'] = document.createElement("DIV");
					Element.addClassName(this['target_el'],"_global_create_block");
					target_flag = true;
				}
				//前処理
				// 開発中は、congigテーブルのphp_debugをonにすること
				//
				if(_nc_debug) var res = commonCls.AjaxResultStr(transport.responseText);
				else var res = transport.responseText;
				//var res = transport.responseText;  /* ログを表示しない設定にしている場合、前処理を行うだけ時間の無駄 */
				if((this['match_str'] != null && this['match_str'] != undefined && commonCls.matchContentElement(res,this['match_str'])) ||
					((this['match_str'] == null || this['match_str'] == undefined) && !commonCls.matchErrorElement(res))) {
					res = commonCls.cutErrorMes(res);
					//正常
					if(this['target_el'] || this['show_main_flag']) {
						if(browser.isGecko && this['target_el']) {
							var hidden_el = this['target_el'];
							hidden_el.style.visibility = "hidden";
							//Element.setStyle(hidden_el, {"opacity":0});
						}
						//Element.extend(hidden_el);
						//hidden_el.setStyle({opacity:0});
						//if(target_flag) {
						//	hidden_el.style.position = "absolute";
						//	hidden_el.style.left = -10000 + "px";
						//}
						if(!this['create_flag'] && this['center_flag']) {
							//center_column show
							if(this['center_col']) {
								this['center_col'].innerHTML = "<div class='enlarged_display'>"+res+"</div>";
							}
						}
						/*
						Popupが２つ以上重なるとmodalが解除されるため、修正
						if(target_flag) {
							if(this['modal_flag']) {
								var div_parent = document.createElement("DIV");
								div_parent.id = "_global_modal_dialog";
								commonCls.showModal(null, div_parent, true);
								document.body.appendChild(div_parent);
							}
							document.body.appendChild(this['target_el']);
						}
						*/
						if(!this['show_main_flag']) {
							var div_write = document.createElement('div');
						    div_write.innerHTML = res;

						    if(target_flag) {
								if(this['modal_flag']) {
									var div_parent = document.createElement("DIV");
									if(this['target_el']) {
										var child_target_el = Element.getChildElement(div_write);
										if(child_target_el.id) {
											div_parent.id = "_global_modal_dialog" + child_target_el.id;
										}
									}
									if(!div_parent.id) {
										div_parent.id = "_global_modal_dialog";
									}
									commonCls.showModal(null, div_parent, true);
									document.body.appendChild(div_parent);
								}
								document.body.appendChild(this['target_el']);
							}

							//
							// document.writeするガジェットに対応するため。_nc_dwScriptCountを挿入
							//
							_nc_dwScriptCount = 0;
						    _nc_dwScriptList = Array();

						    var scriptList = div_write.getElementsByTagName("script");
						    var addScriptList = Array();
						    var addParentScriptList = Array();
						    var count = 0;
						    for (var i = 0,scriptLen = scriptList.length; i < scriptLen; i++){
						    	if(!Element.hasClassName(scriptList[i], "nc_script")) {
						    		_nc_dwScriptList[count] = scriptList[i];
							    	if((browser.isIE || browser.isSafari)) {
										if((scriptList[i].src == undefined || scriptList[i].src == "")) {
											addScriptList[count] = scriptList[i];
										} else {
											var script_el = document.createElement('script');
							    			script_el.setAttribute('type', 'text/javascript');
											script_el.setAttribute('src', scriptList[i].src);
											addScriptList[count] = script_el;
										}
										//script_el.innerHTML = scriptList[i].innerHTML;
										//var script_el = scriptList[i].cloneNode(true);

							    	} else {
								    	var script_el = document.createElement('script');
								    	script_el.id = "_nc_script"+ count;
								    	script_el.type = "text/javascript";
								    	//script_el.defer = "defer";
								    	script_el.innerHTML = "_nc_dwScriptCount = " + count + "; Element.remove($(\"_nc_script"+ count +"\"));";
								    	//scriptList[i].parentNode.insertBefore(script_el, scriptList[i]);
								    	addScriptList[count] = script_el;
								    }
								    addParentScriptList[count] = scriptList[count];
							    	count++;
							    }
						    }

						    if(div_el == undefined) var div_el = null;
							_nc_ajaxFlag = true;
							this['target_el'].innerHTML = "";
						    if(this['target_el'] && Element.hasClassName(this['target_el'],"module_box")) {
						    	Event.unloadCache(this['target_el']);
						    }
						    for (var i = 0, n = div_write.childNodes.length; i < n ; ++i) {
						        this['target_el'].appendChild(div_write.childNodes[i]);
						        n--;
						        i--;
						    }
						    for (var i = 0,scriptLen = addScriptList.length; i < scriptLen; i++){
						    	if((browser.isIE || browser.isSafari) && (addScriptList[i].src == undefined || addScriptList[i].src == "") && addScriptList[i].innerHTML != "") {
						    		eval(addScriptList[i].innerHTML);
						    	} else {
						    		addParentScriptList[i].parentNode.insertBefore(addScriptList[i], addParentScriptList[i]);
						    	}
						    }
						    setTimeout(function(){_nc_ajaxFlag = false;}, 1000);
						    //this['target_el'].innerHTML = res;
							if(target_flag) {
								this['target_el'] = Element.getChildElement(this['target_el']);
							}
							////var buf_left = target_el.style.left;
							////target_el.style.left = -10000+"px";
							if(this['headermenu']) {
								var content_el = Element.getChildElementByClassName(this['target_el'],"content");
								content_el.parentNode.insertBefore(this['headermenu'], content_el);
							}
						}
					}
					if(this['top_el_id'] && this['focus_flag']){
						var a_el = $("_href"+this['top_el_id']);
						if(a_el) a_el.focus();	//グループ化したブロック以外
						//最初のエレメントにフォーカスを移動
						//var form = this['target_el'].getElementsByTagName("form")[0];
						//if(form) {commonCls.focusComp(form);} else{commonCls.focusComp(this['target_el']);}
					}

					//後処理
					if(this['eval_flag'] && (!this['target_el'] || browser.isIE || browser.isSafari || browser.isOpera || (browser.isFirefox && browser.version >= 4))) {
						commonCls.AjaxResultScript(transport.responseText);
					}
					//if(this['script_flag']) commonCls.AjaxResultScript(transport.responseText, this['target_el']);
					//if(buf_left) {
					//	target_el.style.left = buf_left;
					//}
					//画面の中心へ
					if(this['create_flag'] && this['center_flag']) {
						var center_position = commonCls.getCenterPosition(this['target_el'], this['top_el']);
						this['x'] = center_position[0];
						this['y'] = center_position[1];
					}
					//位置移動
					if(this['create_flag']) {
						//target_el.style.left = x+"px";
						//target_el.style.top = y+"px";
						//var id_name = target_el.childNodes[0].id;
						var id_name = this['target_el'].id;
						var id = commonCls._getId(this['target_el']);
						if(id) {
							//エレメント位置
							commonCls.show_x[id] = this['x'];
							commonCls.show_y[id] = this['y'];
						}
						this['target_el'].parentNode.style.left = commonCls.show_x[id] +"px";
						this['target_el'].parentNode.style.top = commonCls.show_y[id] +"px";
						var move_pos = commonCls.moveAutoPosition(this['target_el'].parentNode);
						//if(move_pos != null) {
						//	commonCls.show_x[id] = move_pos[0];
						//	commonCls.show_y[id] = move_pos[1];
						//}

					//	commonCls.moveVisibleHide(target_el);
					}

					//if(Element.hasClassName(target_el,"system")) {
					//	target_el.style.position = "static";
					//	target_el.style.left = "0px";
					//}
					//if(hidden_el && hidden_el.style.left == -10000 + "px") {
					//	hidden_el.style.left = "0px";
					//}
					if(browser.isGecko && hidden_el) {
						//Element.setStyle(hidden_el, {"opacity":""});
						hidden_el.style.visibility = "visible";
					}
					//if(this['target_el'] && this['focus_flag']){
					//	//最初のエレメントにフォーカスを移動
					//	var form = this['target_el'].getElementsByTagName("form")[0];
					//	if(form) {commonCls.focusComp(form);} else{commonCls.focusComp(this['target_el']);}
					//}

					if(this['callbackfunc']){
						if (transport.getResponseHeader("Content-Type") == "text/xml" && transport.responseXML) {
							res = transport.responseXML;
						}

						if(this['func_param'] == undefined) {this['callbackfunc'](res);}
						else {this['callbackfunc'](this['func_param'],res);}
					}
					////document.write = document._write;
					eval("document.write('');");
					return true;
				} else {
					res = commonCls.cutErrorMes(res);
					if(res !== "") {
						var re_html = new RegExp("^<!DOCTYPE html", 'i');
						if(!res.match(re_html)) {
							var re_script = new RegExp('<script.*?>((.|\n|\r|\t)*?)<\/script>', 'ig');
							res = res.replace(re_script,"");
						}

						if(this['callbackfunc_error']){
							if(this['func_error_param'] == undefined) {this['callbackfunc_error'](res);}
							else{this['callbackfunc_error'](this['func_error_param'],res);}
						} else {
							commonCls.alert(res);
						}
						//後処理
						if(this['eval_flag']) {
							commonCls.AjaxResultScript(transport.responseText);
						}
						//if(this['script_flag']) commonCls.AjaxResultScript(transport.responseText);
					}
					return false;
				}
			}.bind(params_obj)
		});

		commonCls.setTimeoutAlert();
		//if(method == "get") {
		//	return _nc_base_url + _nc_index_file_name + "?" + url;
		//}
	},
	_setParam: function(params_obj) {
		if (typeof params_obj['top_el'] == 'string') {
			params_obj['top_el_id'] = params_obj['top_el'];
			params_obj['top_el'] = $(params_obj['top_el']);
		} else {
			params_obj['top_el_id'] = params_obj['top_el'].id;
		}
		if(params_obj['token'] == "") {
			var token_el = $("_token"+ params_obj['top_el_id']);
			if(!token_el){token_el = Element.getChildElementByClassName(params_obj['top_el'],"_token");}
			if(token_el){params_obj['token'] = token_el.value;}
		}
		var queryParams = commonCls.getParams(params_obj['top_el']);
		if(queryParams) {
			var page_id = queryParams["page_id"];
			var block_id = (queryParams["block_id"] == undefined) ? 0 : queryParams["block_id"];
			var module_id = queryParams["module_id"];
			var params_id = "";
			var queryParams = params_obj['url'].parseQuery();
			if(page_id && !queryParams['page_id'])params_id += "&page_id=" + page_id;
			if(block_id && !queryParams['block_id'])params_id += "&block_id=" + block_id;
			if(module_id && !queryParams['module_id'])params_id += "&module_id=" + module_id;
			if(!queryParams['prefix_id_name']) {
				//
				// idがすでにprefix_id_nameつきであれば、prefix_id_nameを作成
				//
				if(block_id != 0) var suffix_id = block_id;
				else var suffix_id = module_id;
				if(suffix_id != undefined && suffix_id.length + 1 != params_obj['top_el_id'].length) {
					var re_suffix_id = new RegExp("_"+suffix_id + "$", "i");
					var replace_str = params_obj['top_el_id'].replace(re_suffix_id,"");
					if(replace_str == params_obj['top_el_id']) {
						var re_suffix_id = new RegExp("_"+block_id + "$", "i");
						var replace_str = params_obj['top_el_id'].replace(re_suffix_id,"");
					}
					replace_str = replace_str.substr(1,replace_str.length - 1);
					if(replace_str != "") {
						params_id += "&prefix_id_name=" + replace_str;
					}
				}
			}
			params_obj['url'] = params_obj['url'] + params_id;
		}
		return params_obj;
	},
	//
	// 画面かはみ出ているポップアップ位置を修正する
	// @param element target_el(absolute element)
	// @param move_pos default:both(both or x or y)
	//            both x-y座標移動
	//            x    x座標移動
	//            y    y座標移動
	//
	moveAutoPosition: function(target_el, move_pos_str) {
		move_pos_str = (move_pos_str == undefined) ? "both" : move_pos_str;
		var move_pos = new Array();
		var buf_left = valueParseInt(target_el.style.left);
		var buf_top = valueParseInt(target_el.style.top);
		if(!browser.isGecko || buf_left <= 0) move_pos[0] = target_el.offsetLeft;
		else move_pos[0] = buf_left;
		if(!browser.isGecko || buf_top <= 0) move_pos[1] = target_el.offsetTop;
		else move_pos[1] = buf_top;

		var move_pos_flag = false;

		var popupX1 = move_pos[1] + target_el.offsetHeight;
		var bodyX1 = Position.getWinOuterHeight() + (window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop);
		if(Element.hasClassName(target_el,"_global_create_block")) {
			var popupX2 = move_pos[0] + Element.getChildElement(target_el).offsetWidth;
		} else {
			var popupX2 = move_pos[0] + target_el.offsetWidth;
		}
		var bodyX2 = Position.getWinOuterWidth() + (window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft);

		if ((move_pos_str == "both" || move_pos_str == "y") && popupX1 > bodyX1) {
			var buf_y =  move_pos[1] - (popupX1 - bodyX1);
			move_pos[1] =  (buf_y > 0) ? buf_y : (move_pos[1] > 0 ? move_pos[1] : 0);
			target_el.style.top = move_pos[1] +"px";
			move_pos_flag = true;
		}
		if ((move_pos_str == "both" || move_pos_str == "x") && popupX2 > bodyX2) {
			move_pos[0] =  move_pos[0] - (popupX2 - bodyX2);
			if (move_pos[0] < 0) {
				move_pos[0] = 0;
			}
			target_el.style.left = move_pos[0] +"px";
			move_pos_flag = true;
		}
		if(move_pos_flag) {
			return move_pos;
		}

		return null;
	},
	_debugShow: function(error_mes) {
		if(typeof debug == 'object') {debug.p(error_mes);} else {commonCls.alert(error_mes);}
	},
	getCenterPosition: function(target_el, position_el) {
		Position.prepare();
		var offset_target = new Object();
		offset_target[0] = 0;
		offset_target[1] = 0;
		if(position_el == undefined) {
			var w = Position.getWinOuterWidth() + Position.deltaX;
			var h = Position.getWinOuterHeight() + Position.deltaY;
		} else {
			position_el = $(position_el);
			var w = position_el.offsetWidth;
			var h = position_el.offsetHeight;
			offset_target = Position.cumulativeOffset(position_el);
		}
		var position = new Object();
		position[0] = ((w - target_el.offsetWidth) / 2) + offset_target[0];
		position[1] = ((h - target_el.offsetHeight) / 2) + offset_target[1];
		if(position[0] < 0) {
			position[0] = 0;
		}
		if(position[1] < 0) {
			position[1] = 0;
		}
		return position;
	},
	_paramEncode: function(parameter, form_el) {
		var return_param = "";
		if(parameter != undefined || parameter != null) {
			if (typeof parameter == 'object') {
				var queryComponents = new Array();
				for(var key in parameter) {
				  if (typeof parameter[key] == 'object' || typeof parameter[key] == 'array') {
				  	queryComponents = createParam(parameter[key], encodeURIComponent(key),queryComponents);
				  } else {
			      	var queryComponent = encodeURIComponent(key) + '=' + encodeURIComponent(parameter[key]);
			      	if (queryComponent) {
			          queryComponents.push(queryComponent);
			      	}
			      }
			    }
			    return_param = queryComponents.join('&');
			} else if(typeof parameter == 'string') {
				parameter = parameter.unescapeHTML();
				//文字列でも許す仕様にしているが、個別でencode処理を入れなければならない
				var re_base_url = new RegExp("^" + _nc_base_url + _nc_index_file_name +"\\?", "i");
				return_param = parameter.replace(re_base_url,"");
				if (!commonCls.matchContentElement(return_param,"action=")) {
					return_param = "action=" + return_param;
				}
			}
		}
		if(form_el && form_el.tagName == "form") {
			return_param = (return_param == "") ? Form.serialize(form_el) : return_param + "&" + Form.serialize(form_el);
		}
		function createParam(parameter, key_str,queryComponents) {
			var ret_array = queryComponents;
			for(var key in parameter) {
				var key_sub_str= key_str + "["+key+"]";

				if (typeof parameter[key] == 'object' || typeof parameter == 'array') {
					ret_array.push(createParam(parameter[key], key_sub_str,queryComponents));
				} else {
					var str = typeof parameter[key];
					if (str == 'string') {
						ret_array.push(key_sub_str + "=" + encodeURIComponent(parameter[key]));
					}
				}
			}
			return ret_array;
		}
		return return_param;
	},
	/*
	 *  send 前処理
	 *	res:ajax.transport.responseTextの結果文字列
	 */
	AjaxResultStr: function(res) {

		//<scrip>-</script>までを削除する
		//var re_script = new RegExp('<script.*?>((.|\n|\r|\t)*?)<\/script>', 'ig');

		//res = res.replace(re_script,"");
		/*logrerタグをdebugで表示*/
		//if(!debug_flag) {
			var re_log = new RegExp("<div class=\"logger_block\">(.|\n|\r|\t)*<\/div>", 'i');
			var logger_block = res.match(re_log);
			if(logger_block) {
				var count = 0;
		    	for(var i = 0; i < logger_block.length; i++) {
					if(logger_block[i].trim() != "") {
						if(count == 0) {
							var winlogger = window.open("", "",
			       				 "height=200,width=400,menubar=yes,scrollbars=yes,resizable=yes");
							winlogger.document.open("text/html", "replace");
							winlogger.document.write("<HTML><HEAD><link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\""+_nc_core_base_url + _nc_index_file_name + "?action=common_download_css&amp;dir_name=/css/common.css&amp;header=0"+"\" /></HEAD><BODY>");
							winlogger.document.write(logger_block[i].trim());
						} else {
							winlogger.document.write(logger_block[i].trim());
						}
						count++;
					}
				}
				if(count != 0) {
					var re_fatal = new RegExp(commonCls.fatal_error_mes, 'i');
					var fatal_mes = res.match(re_fatal);
					if(fatal_mes) winlogger.document.write(fatal_mes[0].trim());
					winlogger.document.write("</BODY></HTML>");
					winlogger.document.close();
			   		winlogger.focus();
				}

				/*
				 *if(debug) {
				 *	for(var i = 0; i < logger_block.length; i++) {
				 *		if(logger_block[i].trim() != "")
				 *			debug.p(logger_block[i]);
				 *	}
				 *}
				*/
				res = res.replace(re_log,"").trim();
			} else {
				/* Fatal error check*/
				var re_fatal = new RegExp(commonCls.fatal_error_mes, 'i');
				if(res.match(re_fatal)) {
					var winlogger = window.open("", "",
	       				 "height=200,width=400,menubar=yes,scrollbars=yes,resizable=yes");
					winlogger.document.open("text/html", "replace");
					winlogger.document.write("<HTML><HEAD><link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\""+_nc_core_base_url + _nc_index_file_name + "?action=common_download_css&amp;dir_name=/css/common.css&header=0"+"\" /></HEAD><BODY>"+res+"</BODY></HTML>");
					winlogger.document.close();
			   		winlogger.focus();
			   		res = commonCls.error_mes + res;
				}
			}
		//}

		/*
		 *if(target_el) {
		 *	target_el.style.visibility = "hidden";
		 *}
		 */
		return res;
	},
	/*　
	 *  send 後処理
	 *	res:ajax.transport.responseTextの結果文字列
	 */
	AjaxResultScript: function(res) {
		//処理速度向上のため１つまで実行
		//var re_script = new RegExp('<script.*?>((.|\n|\r|\t)*?)<\/script>', 'i');
		//var re_common_global_match_str = res.match(re_script);
		//if(re_common_global_match_str != null) {
		//	var script_str = RegExp.$1;
		//	if(script_str != "") {
		//		try{eval(script_str);}catch(e){
		//			debug.p("Error Script:"+script_str);
		//		}
		//	}
		//}

		//TODO:現状、先頭にclass="nc_script"がないものは実効していないが、その他にも対応すべき
		var re_script = new RegExp('<script class=\"nc_script\"[^>]*?>((.|\n|\r|\t)*?)<\/script>', 'ig');
		var re_common_global_array = res.match(re_script);

		var re_common_global_array_eval = "";

		if(re_common_global_array) {
			for(var common_global_counter = 0; common_global_counter < re_common_global_array.length; common_global_counter++) {
				////re_common_global_array[common_global_counter] = re_common_global_array[common_global_counter].replace(re_script,"$1");
				//re_common_global_array[common_global_counter] = re_common_global_array[common_global_counter].replace(re_header,"");
				//re_common_global_array[common_global_counter] = re_common_global_array[common_global_counter].replace(re_footer,"");
				var re_common_global_array_eval = re_common_global_array[common_global_counter].replace(re_script,"$1");
				if(re_common_global_array_eval.trim() == "") {
					//現状、処理しない
				} else {
					eval(re_common_global_array_eval);
				}
			}
		}
	},
	parentWinInit: function(el,absolute_flag, chief_flag) {

		var re_underbar = new RegExp('^_', 'i');
		var el = (el.id == null || el.id == "") ? Element.getChildElement(el) : el;
		var module_id_name = el.id;

		if(el && module_id_name && module_id_name.match(re_underbar)) {

			var id = commonCls._getId(el);
			if(absolute_flag) {
				var parent_el = el.parentNode;
				if(parent_el) {
					parent_el.style.position = "absolute";
					////el.style.left = -10000 + "px";
					////commonCls.moveVisibleHide(el);
					if(parent_el.style.zIndex != commonCls.max_zIndex) {
						commonCls.max_zIndex = commonCls.max_zIndex + 1;
						parent_el.style.zIndex = commonCls.max_zIndex;
					}
					//commonCls.moveVisibleHide(el);
					setTimeout(commonCls.moveVisibleHide.bind(el), 0);
					commonCls.moduleList[id] = parent_el;
				}

				/*
				 *var offset = Position.cumulativeOffset(el);
				 *var x = offset[0];
				 *var y = offset[1];
				 *var id_name = module_id_name.replace(re_underbar,"");
				 */


				//モジュール移動イベント追加処理
				//commonCls.moduleList[module_id_name] = el.parentNode;
				//エレメント位置
				//commonCls.show_x[id] = x;
				//commonCls.show_y[id] = y;
				//el.style.left = commonCls.show_x[id] +"px";
				//el.style.top = commonCls.show_y[id] +"px";
			}
			//else {
			//	el.style.position = "";
			//}

			//commonCls.show_z[module_id_name] = z;
			/*
			var move_bar = $("move_bar"+module_id_name);
			//var move_bar = Element.getChildElementByClassName(el,"move_bar");
			if(move_bar) {
				if(absolute_flag) {
					commonCls.winMoveDragStartEvent[id] = commonCls.winMoveDragStart.bindAsEventListener(el);
					Event.observe(move_bar,"mousedown",commonCls.winMoveDragStartEvent[id],false, el);
				} else {
					if(chief_flag) {
						pagesCls.winMoveDragStartEvent[id] = pagesCls.winMoveDragStart.bindAsEventListener(el);
						Event.observe(move_bar,"mousedown",pagesCls.winMoveDragStartEvent[id],false, el);
						var _block_title = Element.getChildElementByClassName(move_bar,"nc_block_title");
						var _block_title_event = Element.getChildElementByClassName(move_bar,"_block_title_event");
						if(!_block_title_event) _block_title_event = _block_title;
						if(_block_title_event) {
							Event.observe(_block_title, "mouseover", commonCls.blockNotice, false, el);
							//Event.observe(_block_title, "mouseout", commonCls.blockNoticeEnd, false);
							//ダブルクリックでブロック名称変更
							Event.observe(_block_title_event,"dblclick",pagesCls.blockChangeName.bindAsEventListener(_block_title),false, el);
						}
					} else {
						Element.removeClassName(move_bar,"move_bar");
					}
				}
			}
			*/
			if(absolute_flag) {
				var move_bar = Element.getChildElementByClassName(el,"_move_bar");
				commonCls.winMoveDragStartEvent[id] = commonCls.winMoveDragStart.bindAsEventListener(el);
				Event.observe(move_bar,"mousedown",commonCls.winMoveDragStartEvent[id],false, el);
			} else {
				if(chief_flag) {
					//pagesCls.winMoveDragStartEvent[id] = pagesCls.winMoveDragStart.bindAsEventListener(el);
					var _block_title = Element.getChildElementByClassName(el,"nc_block_title");
					var _block_title_event = Element.getChildElementByClassName(el,"_block_title_event");

					// グループ化してあるブロックは、実際のグループ化したブロックのタイトルエレメントがどうか再度、チェックする
					// (子供ブロックのエレメントである可能性があるため)
					if(_block_title && Element.hasClassName(el,"module_grouping_box")) {
						// グループ化しているブロック
						var buf_module_box_el = Element.getParentElementByClassName(_block_title,"module_box");
						if(buf_module_box_el.id != el.id) _block_title = null;
					}
					if(_block_title_event && Element.hasClassName(el,"module_grouping_box")) {
						// グループ化しているブロック
						var buf_module_box_el = Element.getParentElementByClassName(_block_title_event,"module_box");
						if(buf_module_box_el.id != el.id) _block_title_event = null;
					}

					//Event.observe(move_bar,"mousedown",pagesCls.winMoveDragStartEvent[id],false, el);
					//var _block_title = Element.getChildElementByClassName(move_bar,"nc_block_title");
					//var _block_title_event = Element.getChildElementByClassName(move_bar,"_block_title_event");
					if(!_block_title_event) _block_title_event = _block_title;
					if(_block_title_event) {
						Event.observe(_block_title, "mouseover", commonCls.blockNotice, false, el);
						//Event.observe(_block_title, "mouseout", commonCls.blockNoticeEnd, false);
						//ダブルクリックでブロック名称変更
						Event.observe(_block_title_event,"dblclick",pagesCls.blockChangeName.bindAsEventListener(_block_title),false, el);
					}
					var theme_header_flag = true;
					var theme_top_el = $("_theme_top" + el.id);
					if(theme_top_el) {
						var move_bar = Element.getChildElementByClassName(theme_top_el,"_move_bar");
						if(move_bar) {
							//上部にバーを表示を表示させない
							theme_header_flag = false;
							pagesCls.winMoveDragStartEvent[el.id] = pagesCls.winMoveDragStart.bindAsEventListener(el);
							Event.observe(move_bar,"mousedown",pagesCls.winMoveDragStartEvent[el.id],false, el);
							pagesCls.winGroupingEvent[el.id] = pagesCls.onGroupingEvent.bindAsEventListener(el);
							Event.observe(move_bar,"click",pagesCls.winGroupingEvent[el.id],false, el);
						}

					}
					//上部にバーを表示
					if(theme_header_flag && _nc_layoutmode == "on") {
						//移動用ヘッダー移動
						setTimeout(function() {
							//移動用ヘッダー移動
							pagesCls.winMoveResizeHeader();

							////pagesCls.winMoveShowHeader(null, el);
						}.bind(this), 200);
						Event.observe(el,"mouseover",pagesCls.winMoveShowHeader.bindAsEventListener(el),false, el);
						Event.observe(el,"mouseout",pagesCls.winMoveHideHeader.bindAsEventListener(el),false, el);
					}
				}
				// else {
				//	Element.removeClassName(move_bar,"move_bar");
				//}
			}
		}
	},
	/*エラーエレメントかどうかかどうか*/
	/*str:Html						   */
	matchErrorElement: function(str) {
		var match_table = new RegExp("^(?:" + this.error_mes + "|<!DOCTYPE html){1}.+", "i");
		if (typeof str == 'string' && str.match(match_table)) {
			return true;
		}
		//var re_doctype = new RegExp("^<!DOCTYPE html", 'i');
		//if(str.match(re_doctype)) {
		//	return true;
		//}
		return false;
	},
	/*モジュールコンテンツかどうか     */
	/*str:Html						   */
	/*match_str:matchする正規表現文字列*/
	matchContentElement: function(str,match_str) {
		if (match_str == "") {
			if(str == "") {
				return true;
			} else {
				return false;
			}
		} else {
			var match_div = new RegExp(match_str, "i");
			if (str.match(match_div)) {
				return true;
			} else {
				return false;
			}
		}
	},
	cutParamByUrl: function(url) {
		var re_cut = new RegExp(".*\\?", "i");
		url = "?" + url.replace(re_cut,"");
		//url = "?action=" + url.replace(re_cut,"");
		return url;
	},
	/*各グローバル配列KEY取得関数     */
	_getId: function(top_el,name) {
		var key = (name == undefined || name == null) ? "" : "?" + name ;
		if (typeof top_el == 'string'){
			var queryParams = commonCls.cutParamByUrl(top_el).parseQuery();
		} else {
			var queryParams = commonCls.getParams(top_el);
		}

		if(queryParams["block_id"] != null && queryParams["block_id"] != 0) {
			key += "&block_id=" + queryParams["block_id"];
		}
		if(queryParams["page_id"] != null && queryParams["page_id"] != 0) {
			key += "&page_id=" + queryParams["page_id"];
		}
		if((queryParams["block_id"] == null || queryParams["block_id"] == 0) && queryParams["action"] != "") {
			key += "&dir_name=" + queryParams["action"].split("_")[0]
			//key += "&module_id=" + queryParams["module_id"];
		}
		//dialog
		var prefix_id_name = queryParams["prefix_id_name"];
		if(prefix_id_name != null) {
			key += "&prefix_id_name=" + prefix_id_name;
		}
		return key;
	},
	/********************************/
	/*一般関数						*/
	/********************************/
	alert: function(str) {
		if(typeof str != 'string') return "";
		var re_html = new RegExp("^<!DOCTYPE html", 'i');
		if(str.match(re_html)) {
			document._write(str);
		} else {
			str = commonCls.cutErrorMes(str);
			str = str.unescapeHTML();
			str = str.replace(/\\n/ig,"\n");
			str = str.replace(/(<br(?:.|\s|\/)*?>)/ig,"\n");

			if(str != "") {
				alert(str);
			}
		}
	},
	confirm: function(str) {
		if(typeof str != 'string') return "";
		var re_html = new RegExp("^<!DOCTYPE html", 'i');
		if(str.match(re_html)) {
			document.write(str);
		} else {
			str = str.unescapeHTML();
			str = str.replace(/\\n/ig,"\n");
			str = str.replace(/(<br(?:.|\s|\/)*?>)/ig,"\n");
			return confirm(str);
		}
	},
	cutErrorMes: function(str) {
		if(typeof str != 'string') return "";
		var re_error = new RegExp("^" + this.error_mes, 'i');
		if(str.match(re_error)) {
			str = str.substr(this.error_mes.length,str.length);
		}
		return str;
	},
	//表示：非表示切り替え
	displayChange: function(el) {
		el = $(el);
		var elestyle = el.style;
		if (elestyle.display == "none" || Element.hasClassName(el,"display-none")) {
			this.displayVisible(el);
		} else {
			this.displayNone(el);
		}
		//if(Element.hasClassName(el,"display-none")) {
		//	commonCls.displayVisible(el);
		//} else {
		//	commonCls.displayNone(el);
		//}
	},
	displayNone: function(el) {
		var elestyle = el.style;
		if (elestyle.display) {
			elestyle.display = "none";
		}
		Element.addClassName(el,"display-none");
	},
	displayVisible: function(el) {
		var elestyle = el.style;
		var display = "";										//block
		if (el.tagName == "TR") display = "";					//table-row
		else if (el.tagName == "TD") display = "";				//table-cell
		else if (el.tagName == "TABLE") display = "";			//table
		elestyle.display = display;
		if(Element.hasClassName(el,"display-none")) {
			Element.removeClassName(el,"display-none");
		}
		//designModeがonのものが含まれていたら、再度、off,on。再表示した際、offのようになるため
		try {
			if (!(browser.isIE || browser.isOpera || browser.isSafari)) {
				var iframeList = el.getElementsByTagName("iframe");
				for (var i = 0; i < iframeList.length; i++){
					if(iframeList[i].contentWindow.document.designMode == "on") {
						iframeList[i].contentWindow.document.designMode = "off";
						iframeList[i].contentWindow.document.designMode = "on";
					}
				}
			}
		}catch(e){}
	},
	visibilityChange: function(el) {
		el = $(el);
		if(Element.hasClassName(el,"visible-hide")) {
			commonCls.visibilityVisible(el);
		} else {
			commonCls.visibilityNone(el);
		}
	},
	visibilityNone: function(el) {
		Element.addClassName(el,"visible-hide");
	},
	visibilityVisible: function(el) {
		if(Element.hasClassName(el,"visible-hide")) {
			Element.removeClassName(el,"visible-hide");
		} else {
			el.style.visibility = "visible";
		}
		//designModeがonのものが含まれていたら、再度、off,on。再表示した際、offのようになるため
		//if (!(browser.isIE || browser.isOpera || browser.isSafari)) {
		//	var iframeList = el.getElementsByTagName("iframe");
		//	for (var i = 0; i < iframeList.length; i++){
		//		if(iframeList[i].contentWindow.document.designMode == "on") {
		//			iframeList[i].contentWindow.document.designMode = "off";
		//			iframeList[i].contentWindow.document.designMode = "on";
		//		}
		//	}
		//}
	},
	cellIndex: function(element) {
		if(browser.isSafari) {
			//safariがcellIndex未対応なため
			for (var i = 0; i < element.parentNode.childNodes.length; i++) {
				if(element.parentNode.childNodes[i] == element) {
					return i;
				}
			}
		} else {
			return element.cellIndex;
		}
		return 0;
	},
	//フルスケールで画像表示
	showPopupImageFullScale: function(this_el) {
		if($("_fullscall_image")) {
			return;
		}
		var img_el = Element.getChildElement(this_el);
		var div_el = document.createElement("DIV");

		//var offset = Position.cumulativeOffsetScroll(this_el);
		Element.setStyle(div_el, {opacity:0.7});
		div_el.id = "_global_full_scale";
		div_el.style.backgroundColor = "#666666";
		document.body.appendChild(div_el);
		commonCls.showModal(null, div_el);
		var new_img_el = document.createElement("IMG");
		//new_img_el.style.left = offset[0] + "px";
		//new_img_el.style.top = offset[1] + "px";
		commonCls.max_zIndex = commonCls.max_zIndex + 1;
		new_img_el.style.zIndex = commonCls.max_zIndex;
		new_img_el.style.position = "absolute";
		new_img_el.src = img_el.src;
		new_img_el.style.visibility = "hidden";
		new_img_el.id = "_fullscall_image";
		document.body.appendChild(new_img_el);

		var center_position = commonCls.getCenterPosition(new_img_el, img_el);
		new_img_el.style.left = center_position[0] + "px";
		new_img_el.style.top = center_position[1] + "px";
		new_img_el.style.visibility = "visible";
		commonCls.moveVisibleHide(div_el);
		div_el.onmousedown = function() {
			commonCls.displayChange(div_el);
			commonCls.moveVisibleHide(div_el);
			Element.remove(div_el);
			Element.remove(new_img_el);
		}
		new_img_el.onmousedown = function() {
			commonCls.displayChange(div_el);
			commonCls.moveVisibleHide(div_el);
			Element.remove(div_el);
			Element.remove(new_img_el);
		}
	},
	/**
	 * common_download_cssのスタイルシートを追加する
	 * @param   dir_name	common_download_cssのdir_nameパラメータ
	 * @param   media		media名(MediaDescタイプ)
	 * @return  boolean
	 **/
	addCommonLink: function (dir_name, media, document_object){
		document_object = (document_object == undefined || document_object == null) ? document : document_object;
		var nLink = null;
		var new_dir_name_arr = new Array();
		var del_dir_name_arr = new Array();
		var common_css_flag = false;
		for(var i=0; (nLink = document_object.getElementsByTagName("LINK")[i]); i++) {
			if(Element.hasClassName(nLink, "_common_css")) {
				common_css_flag = true;
				var queryParams = nLink.href.unescapeHTML().parseQuery();
				if(!queryParams["dir_name"])
					continue;
				var dir_name_arr = queryParams["dir_name"].split("|");
				var current_dir_name_arr = dir_name.split("|");
				for (var j = 0; j < current_dir_name_arr.length; j++){
					var pos = dir_name_arr.indexOf(current_dir_name_arr[j]);
					if(pos == -1) {
						var new_pos = new_dir_name_arr.indexOf(current_dir_name_arr[j]);
						if(new_pos != -1) break;
						new_dir_name_arr[new_dir_name_arr.length] = current_dir_name_arr[j];
					} else {
						del_dir_name_arr[del_dir_name_arr.length] = current_dir_name_arr[j];
					}
				}
			}
		}
		del_dir_name_arr.each(function(del_value) {
			new_dir_name_arr = new_dir_name_arr.without(del_value);
		}.bind(this));

		var new_dir_name = new_dir_name_arr.join("|");
		if(new_dir_name == "") {
			if(common_css_flag) {
				return true;
			} else {
				new_dir_name = dir_name
			}
		}
		var css_name = _nc_core_base_url + _nc_index_file_name + "?action=common_download_css&amp;dir_name="+new_dir_name+"&amp;header=0&amp;vs="+_nc_css_vs;
		return commonCls._addLink(css_name, media, document_object, "_common_css");
	},
	/**
	 * スタイルシートを追加する
	 * @param   css_name		CSSファイル名称
	 * @param   media		media名(MediaDescタイプ)
	 * @return  boolean
	 **/
	addLink: function (css_name, media, document_object){
		document_object = (document_object == undefined || document_object == null) ? document : document_object;
		var nLink = null;
		for(var i=0; (nLink = document_object.getElementsByTagName("LINK")[i]); i++) {
			if(nLink.href == css_name) {
				//既に追加済
				return true;
			}
		}
		return commonCls._addLink(css_name, media, document_object);
	},
	_addLink: function (css_name, media, document_object, class_name){
		if(typeof document_object.createStyleSheet != 'undefined') {
			document_object.createStyleSheet(css_name.unescapeHTML());
			var oLinks = document_object.getElementsByTagName('LINK');
			var nLink = oLinks[oLinks.length-1];
   			// stylesheet object createStyleSheet([sURL] [, iIndex])
   			// iIndexは省略可。省略するとスタイルシート集合の最後に追加。
		} else if(document_object.styleSheets){
  			var nLink=document_object.createElement('LINK');
			nLink.rel="stylesheet";
			nLink.type="text/css";
			nLink.media= (media ? media : "screen");
			nLink.href=css_name.unescapeHTML();
			var oHEAD=document_object.getElementsByTagName('HEAD').item(0);
			oHEAD.appendChild(nLink);
		}
		if(class_name != undefined) {
			Element.addClassName(nLink, class_name);
		}
		return true;
	},

	/**
	 * Scriptをdocument.writeする
	 * @param   src_name		SRC名称(絶対パス)
	 * @return  boolean
	 **/
	scriptDocWrite: function (src_name, document_object){
		document_object = (document_object == undefined || document_object == null) ? document : document_object;
		var nScript = null;
		for(var i=0; (nScript = document_object.getElementsByTagName("SCRIPT")[i]); i++) {
			if(nScript.src != "" && nScript.src == src_name) {
				//既に追加済
				return;
			}
		}
		document_object.open();
		document_object.write('<script type="text/javascript" src= "' + src_name + '"></script>');
		document_object.close();
	},

	/**
	 * Scriptを追加する
	 * @param   src_name		SRC名称
	 * @return  boolean
	 **/
	addScript: function (src_name, document_object){
		document_object = (document_object == undefined || document_object == null) ? document : document_object;
		var nScript = null;
		for(var i=0; (nScript = document_object.getElementsByTagName("SCRIPT")[i]); i++) {
			if(nScript.src != "" && nScript.src == src_name) {
				//既に追加済
				return true;
			}
		}
		var nScript=document_object.createElement('SCRIPT');
		nScript.type="text/javascript";
		nScript.src=src_name;
		var oHEAD=document_object.getElementsByTagName('HEAD').item(0);
		oHEAD.appendChild(nScript);
		return true;
	},
	/**
	 * フォーム内の指定セレクトオブジェクトで選択されている項目を
	 * 別のセレクトオブジェクトに移動する
	 *
	 * @param   frm		対象フォームオブジェクト
	 * @param	efrom	セレクトオブジェクト名称文字列
	 * @param	eto		セレクトオブジェクト名称文字列
	 * @return  none
	 **/
	frmTransValue: function (frm, efrom, eto){
	    var ef = frm.elements[efrom];
	    var et = frm.elements[eto];
	    while (ef.selectedIndex != -1) {
	    	if(!ef.disabled) {
		        et.length = et.length + 1;
		        et.options[et.length - 1].value = ef.options[ef.selectedIndex].value;
		        et.options[et.length - 1].text = ef.options[ef.selectedIndex].text;
		        ef.options[ef.selectedIndex] = null;
	        }
	    }
	},
	/**
	 * フォーム内の指定セレクトオブジェクトの項目を移動する
	 *
	 * @param   frm		対象フォームオブジェクト
	 * @param	e		セレクトオブジェクト名称文字列
	 * @param	move	移動方向（上下(1,-1)、最上部(2)、最下部(-2)）
	 * @return  none
	 **/
	frmMoveListBox: function(frm, e, move) {
		var selectindx = frm.elements[e].selectedIndex;
		if (selectindx != -1){
			if (move == 1) {
				for( i = 0; i < frm.elements[e].length; i++ ){
					if( frm.elements[e].options[i].selected ){
						if( i <= 0 ) {
							continue;
						}
						var optText = frm.elements[e].options[i].text;
	    				var optValue = frm.elements[e].options[i].value;
						frm.elements[e].options[i].text = frm.elements[e].options[i-1].text;
						frm.elements[e].options[i].value = frm.elements[e].options[i-1].value;
						frm.elements[e].options[i-1].text = optText;
						frm.elements[e].options[i-1].value = optValue;
						frm.elements[e].options[i-1].selected=true;
						frm.elements[e].options[i].selected=false;
					}
				}
			} else if (move > 1) {
				//最上部へ移動
				var j=0;
				for( i = 0; i < frm.elements[e].length; i++ ){
					if( frm.elements[e].options[i].selected ){
						if( i <= 0 ) {
							continue;
						}
						var optText = frm.elements[e].options[i].text;
	    				var optValue = frm.elements[e].options[i].value;
						var eleOption = document.createElement("option");
						eleOption.value = optValue;
						eleOption.text = optText;
						frm.elements[e].options[i] = null;
						commonCls.frmAddOption(frm.elements[e], eleOption, j);
						frm.elements[e].options[j].selected=true;
						j++;
					}
				}
			} else if (move == -1) {
				for( i = frm.elements[e].length-1; i >= 0; i-- ){
					if( frm.elements[e].options[i].selected ){
						if( i >= frm.elements[e].length-1 ) {
							continue;
						}
						var optText = frm.elements[e].options[i].text;
	    				var optValue = frm.elements[e].options[i].value;
						frm.elements[e].options[i].text = frm.elements[e].options[i+1].text;
						frm.elements[e].options[i].value = frm.elements[e].options[i+1].value;
						frm.elements[e].options[i+1].text = optText;
						frm.elements[e].options[i+1].value = optValue;
						frm.elements[e].options[i+1].selected=true;
						frm.elements[e].options[i].selected=false;
					}

				}
			} else if (move < -1) {
				var j=frm.elements[e].length - 1;
				for( i = frm.elements[e].length-1; i >= 0; i-- ){
					if( frm.elements[e].options[i].selected ){
						if( i >= frm.elements[e].length-1 ) {
							continue;
						}
						var optText = frm.elements[e].options[i].text;
	    				var optValue = frm.elements[e].options[i].value;
						var eleOption = document.createElement("option");
						eleOption.value = optValue;
						eleOption.text = optText;
						frm.elements[e].options[i] = null;
						commonCls.frmAddOption(frm.elements[e], eleOption, j);
						frm.elements[e].options[j].selected=true;
						j--;
					}
				}
			}
		}
	},
	/**
	 * セレクトオブジェクトにオプションオブジェクトを追加する
	 *
	 * @param   eleSelect		対象セレクトオブジェクト
	 * @param	eleOption		対象オプションオブジェクト
	 * @param	index			挿入位置(0以上）
	 * @return  none
	 **/
	frmAddOption: function(eleSelect, eleOption, index) {
		if (browser.isNS){
			eleSelect.insertBefore(eleOption, eleSelect.options[index]);
		}else{
			eleSelect.options.add( eleOption, index );
		}
	},
	/**
	 * フォーム内の指定セレクトオブジェクトを全て解除状態にする
	 *
	 * @param   frm		対象フォームオブジェクト
	 * @param	e		セレクトオブジェクト名称文字列
	 * @return  none
	 **/
	frmAllReleaseList: function(frm, e) {
		frm.elements[e].selectedIndex = -1;
	},
	/**
	 * フォーム内の指定セレクトオブジェクトを全て選択状態にする
	 *
	 * @param   frm		            対象フォームオブジェクト
	 * @param	e		            セレクトオブジェクト名称文字列
	 * @param	disabled_flag		disable中のものを選択しないかどうか default:false
	 * @return  none
	 **/
	frmAllSelectList: function(frm, e, disabled_flag) {
		if ( frm.elements[e] == undefined ) {
		}else{
	    	var n = frm.elements[e].length;
	    	for (var i = 0; i < n ; i++) {
	    		if((disabled_flag == undefined || disabled_flag == false) || !frm.elements[e].options[i].disabled) {
	    			frm.elements[e].options[i].selected = true;
	    		}
	    	}
		}
	},
	/**
	 * フォーム内の指定ラジオボタンを全てONにする
	 *
	 * @param   frm		対象フォームオブジェクト
	 * @param	value	対象ラジオボタンの値
	 * @param   func   ONにするコールバック関数(default:checkedするのみ)
	 * @return  none
	 **/
	frmAllSelectRadio: function(frm, value, callback_checked_func) {
	    for ( i=0; i < frm.elements.length; i++ ){
	        if ( frm.elements[i].type == 'radio' ){
				if(frm.elements[i].value == value && !frm.elements[i].disabled) {
					if(callback_checked_func == undefined) {
						frm.elements[i].checked = true;
					} else {
						callback_checked_func(frm.elements[i]);
					}
				}
	        }
	    }
	},
	/**
	 * フォーム内の指定チェックボックスを全てON/OFFにする
	 *
	 * @param   frm		対象フォームオブジェクト
	 * @param	e		チェックボックス名称文字列
	 * @param	value	チェックボックスの値
	 * @return  none
	 **/
	frmAllChecked: function(frm, e, value) {
			if ( frm.elements[e] == undefined ) {
					frm.elements[e].checked = value;
		 }else{
	    	var n = frm.elements[e].length;

		    if ( n == undefined ) {
		    	frm.elements[e].checked = value;
		    } else {
			    for (var i = 0; i < n ; i++) {
			        frm.elements[e][i].checked = value;
			    }
		    }
		}
	},
   /**
	 * 文字列に一定文字数毎に改行を挿入する
	 *
	 * @param   str	        文字列(必須)
	 * @param   number_char	改行を入れる文字数(任意)　default:30
	 * @return  str         改行された文字列
	 **/
	setLineBreak: function(str, number_char)	{
		if(number_char == undefined) number_char = 30;
		var reg_exp_obj = new RegExp("((?:.|\s){" + number_char + "})", "g");
		return str.replace(reg_exp_obj, "$1<br />");
	},
	/**
	 * プリント用共通メソッド
	 *
	 * @param   el			印刷したいエレメント(el or string)(必須)
	 * @param   width	    プレビュー画面の広さ default:600
	 * @param	height      プレビュー画面の高さ default:600
	 * @param   header_flag	プレビュー画面の閉じる等のヘッダー表示 default:true
	 * @param   window_name	プレビュー画面のWindowタイトル
	 * @return  none
	 **/
	print: function(el, width, height, header_flag, window_name)	{
		width = (width == undefined) ? 600 : width;
		height = (height == undefined) ? 600 : height;
		header_flag = (header_flag == undefined) ? true : header_flag;
		window_name = (window_name == undefined) ? commonLang.printTitle : window_name;
		if(header_flag) {
			var html = "<div class=\"print_header\"><a class=\"print_btn link\" href=\"javascript:window.close();\">"+commonLang.close+"</a>"+
					commonLang.separator+"<a class=\"print_btn link\" href=\"javascript:window.print();\">"+commonLang.print+"</a></div>";
		} else {
			var html = "";
		}
		var print_script = "";
		var disabled_script = "window.opener.commonCls.disableLink(document.body, \"print_btn\", true);";
		var re_script = new RegExp('<script.*?>((.|\n|\r|\t)*?)<\/script>', 'ig');
		if(typeof el == 'string') {
			html += "<div class=\"outerdiv\">";
			html += el.replace(re_script,"");
		} else {
			if(!el.id) {
				var print_id = "_global_print_el";
				el.id = print_id;
			} else {
				var print_id = el.id;
			}
			html += "<div id=\""+ el.id +"\" class=\"outerdiv"+ el.className +"\">";
			if(!browser.isGecko) {
				html += el.innerHTML.replace(re_script,"");
			} else {
				var append_el = el.cloneNode(true);
				print_script = "document.getElementById('"+print_id+"').appendChild(print_el);"+disabled_script;
			}
		}
		html += "</div>";

		var features="location=no, menubar=no, status=yes, scrollbars=yes, resizable=yes, toolbar=no";
		if (width) {
			if (window.screen.width > width)
				features+=", left="+(window.screen.width-width)/2;
			else width=window.screen.width;
			features += ", width="+width;
 		}
		if (height) {
			if (window.screen.height > height)
				features+=", top="+(window.screen.height-height)/2;
			else height=window.screen.height;
			features+=", height="+height;
		}
		var head = document.getElementsByTagName("head")[0];
		var links = head.getElementsByTagName("link");
		var linkText = "<link rel=\"stylesheet\" type=\"text/css\" media=\"print\" href=\""+_nc_core_base_url + _nc_index_file_name + "?action=common_download_css&amp;dir_name=/css/print.css&amp;header=0"+"\" />";
		linkText += "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\""+_nc_core_base_url + _nc_index_file_name + "?action=common_download_css&amp;dir_name=/css/print_preview.css&amp;header=0"+"\" />";
		for (var i = 0; i < links.length; i++) {
			var link = links[i];
			if (link.getAttribute("type") == "text/css") {
				linkText += "<link ";
				linkText += "rel=\"" + link.getAttribute("rel") + "\" ";
				linkText += "type=\"" + link.getAttribute("type") + "\" ";
				linkText += "media=\"" + link.getAttribute("media") + "\" ";
				linkText += "href=\"" + link.getAttribute("href") + "\" ";
				linkText += "/>\n";
			}
		}

		var scriptText = '';
		if(print_script == "") {
			var scriptTextPrint =  "<script>function Init() {setTimeout(function(){"+print_script+disabled_script+" print();}, 500);}</script>";
		} else {
			var scriptTextPrint =  "<script>function Init() {setTimeout(function(){"+print_script+" print();}, 500);}</script>";
		}
		var scriptList = document.getElementsByTagName("script");
		for (var i = 0,scriptLen = scriptList.length; i < scriptLen; i++){
	    	if((scriptList[i].src != undefined && scriptList[i].src != "")) {
				scriptText += "<script type=\"text/javascript\" src=\""+scriptList[i].src+"\"></script>";
			}
	    }
		var winprint = window.open("", "PrintPreview", features);
		if(append_el != undefined) {
			winprint.print_el = append_el;
		}
		winprint.document.open("text/html");
		/* ブロック内部のデザインは維持しない */
		winprint.document.write("<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"><html><head><title>" + window_name + "</title><style> html,body {background-image : none !important; padding:0px !important; margin:0px !important;}</style>" + linkText + scriptText + "</head>"+"<body class=\"print_preview\" onload=\"Init();\">"+html+scriptTextPrint+"</body></html>");
		winprint.document.close();
	},
	/* element内、リンク、ボタン無効化 */
	/* enable_class : 無効にしないクラス */
	disableLink: function(el, enable_class, parent_flag)	{
		el = $(el);
		var hasClassName = (parent_flag == undefined) ? function(el, class_name) {return commonCls.hasClassName(el, class_name);}.bind(this) : function(el, class_name) {return window.parent.commonCls.hasClassName(el, class_name);}.bind(this);
		var aList = el.getElementsByTagName("A");
		for (var i = 0,aLen = aList.length; i < aLen; i++) {
			if(enable_class != undefined && !hasClassName(aList[i], enable_class)) {
				aList[i].onclick = function(){return false;};
			}
		}
		var inputList = el.getElementsByTagName("INPUT");
		for (var i = 0,inputLen = inputList.length; i < inputLen; i++) {
			if(enable_class != undefined && !hasClassName(inputList[i], enable_class) &&
				inputList[i].type.toLowerCase() == "button") {
				inputList[i].onclick = function(){return false;};
			}
		}

		// print_preview_nonescrollクラスがあれば、広さ、高さの指定を行わない
		//var print_preview_nonescroll_fields = Element.getElementsByClassName(el, "print_preview_nonescroll");
		//print_preview_nonescroll_fields.each(function(print_nonescroll_el) {
		//	Element.setStyle(print_nonescroll_el, {width:'auto'});
		//	Element.setStyle(print_nonescroll_el, {height:'auto'});
		//}.bind(this));
	},
	/**
	 * ツールチップを表示するイベント登録
	 *
	 * @param   observe_el	ツールチップを表示するel(必須)
	 * @param   top_id	    Top_el.id or Top_el(必須)
	 * @param	show_mes    内容の文字列(必須)
	 * @param	show_second 表示時間[単位：ミリ秒　default 3000](任意)
	 * @return  none
	 **/
	observeTooltip: function(observe_el, top_id, show_mes, show_second)	{
		if (top_id == 'string') {
			var top_el = $(top_id);
		} else {
			var top_el = top_id;
			top_id = top_id.id;
		}
		commonCls.inToolTipEvent["mouseover"+top_id] = function(event) {
														commonCls.showTooltip(event, show_mes, show_second);
												   }.bindAsEventListener(this);
		commonCls.inToolTipEvent["mouseout"+top_id] = function(event) {
														commonCls.closeTooltip(event);
												   }.bindAsEventListener(this);

		Event.observe(observe_el, "mouseover", commonCls.inToolTipEvent["mouseover"+top_id],false, top_el);
		Event.observe(observe_el, "mouseout",  commonCls.inToolTipEvent["mouseout"+top_id], false, top_el);
	},
	/**
	 * ツールチップを表示する(mouseover)
	 *
	 * @param   event	    event(必須)
	 * @param	show_mes    内容の文字列(必須)
	 * @param	show_second 表示時間[単位：ミリ秒　default 5000](任意)
	 * @return  none
	 **/
	showTooltip: function(event, show_mes, show_second)	{
		if(this.toolTipPopup == null) {
			this.toolTipPopup   = new compPopup(null, "popupTooltip");
			this.toolTipPopup.observing = false;
			this.toolTipPopup.modal = false;
			this.toolTipPopup.setTitle('Tooltip');
			this.toolTipPopup.loadObserver = function() {
				// 表示位置変更-Height
				var popupY2 = this.popupElement.offsetTop + this.popupElement.offsetHeight;
				var bodyY2 = Position.getWinOuterHeight() + document.documentElement.scrollTop;
				if (popupY2 > bodyY2) {
					var new_position = new Array();
					new_position[0] = this.popupElement.offsetLeft;
					if (new_position[0] < 0) {
						new_position[0] = 0;
					}
					new_position[1] = this.popupElement.offsetTop - (popupY2 - bodyY2);
					this.setPosition(new_position);
				}
			}.bind(this.toolTipPopup);
		}

		if(this.toolTipPopup.isVisible()) return;	//表示中

		var observe_el = Event.element(event);
		//if(show_mes == undefined) {
		//	if(observe_el.title != "") show_mes = observe_el.title;
		//	else show_mes = observe_el.innerHTML;
		//}
		if(show_second == undefined) show_second = 5000;	//5秒

		var div = document.createElement("DIV");
		Element.addClassName(div, "tooltipClass");
		div.innerHTML = show_mes;
		var offset = 20;	//offset 20px
		var position = new Object;
	    position[0] = Event.pointerX(event);
	    position[1] = Event.pointerY(event) + offset;
	    this.toolTipPopup.setPosition(position);
	    this.toolTipPopup.showPopup(div);
	    if(this.toolTipPopupTimer != null) {
	    	clearTimeout(this.toolTipPopupTimer);
	    	this.toolTipPopupTimer = null;
	    }
	    this.toolTipPopupTimer = setTimeout(function(){this.closeTooltip(event)}.bind(this), show_second);
	},
	/**
	 * ツールチップを閉じる(mouseout)
	 *
	 * @param   event	    event(現状：未使用)
	 * @return  none
	 **/
	closeTooltip: function(event)	{
		if(this.toolTipPopup == null || !this.toolTipPopup.isVisible()) return;	//既に表示されていない
		this.toolTipPopup.closePopup();
	},
	/**
	 * 会員詳細表示
	 *
	 * @param   event	    event
	 * @param   int			user_id
	 * @return  none
	 **/
	showUserDetail: function(event, user_id) {
		// ログインしていない
		if(_nc_user_id == "0") return;
		user_id = (user_id == undefined) ? 0 : user_id;
		var param_popup = new Object();
		var user_params = new Object();
		param_popup = {
						"action":"userinf_view_main_init",
						"prefix_id_name":"popup_userinf"+user_id,
						"user_id":user_id,
						"theme_name": "system"
					};
		// エラーになってもエラーメッセージは表示しない
		user_params['callbackfunc_error'] = function(res){};
		//var top_el = $(this.id);
		//user_params['target_el'] = top_el;
		//user_params['center_flag'] = true;
		//user_params['modal_flag'] = true;
		commonCls.sendPopupView(event, param_popup, user_params);
	},

	/*********************************************************/
	/*ファイルアップロード送信一般関数						 */
	/*@param object											 */
	/*param：パラメータ(Object)（必須)						 */
	/*top_el：モジュール毎のトップエレメント（必須)		  　 */
	/*match_str：正常かどうかのMatch文字列(正規表現)	 	 */
	/*			 指定しない場合、エラー文字列以外は正常終了　*/
	/*download_action：ダウンロードアクション名				 */
	/*                 default:common_download_main			 */
	/*form_prefix：input=fileを含むformのtarget属性名称prefix*/
	/*             default:attachment_form			         */
	/*             form_prefix+id							 */
    /*target_el：ターゲットエレメント						 */
	/*header_flag：ヘッダーありなし defaultなし		 	 	 */
	/*callbackfunc:コールバック関数						 	 */
	/*callbackfunc_error:エラー時コールバック関数		     */
	/*			 		 指定しなければalert表示			 */
	/*debug:trueの場合、受信内容をalertする				 　  */
	/*timeout_flag:0の場合、タイムアウトチェックをしない     */
	/*********************************************************/
	sendAttachment: function(params_obj) {
		//paramセット
		if (typeof params_obj['top_el'] == 'string') {
			var id = params_obj['top_el'];
			var top_el = $(params_obj['top_el']);
		} else {
			var id = params_obj['top_el'].id;
			var top_el = params_obj['top_el'];
		}
		var match_str = params_obj['match_str'];
		var form_prefix = (params_obj['form_prefix'] != undefined && params_obj['form_prefix'] != null) ? params_obj['form_prefix'] : "attachment_form";
		var form_target = form_prefix + id;
		var download_action = (params_obj['download_action'] != undefined && params_obj['download_action'] != null) ? params_obj['download_action'] : "common_download_main";
		var header_flag = (params_obj['header_flag'] != undefined && params_obj['header_flag'] != null) ? params_obj['header_flag'] : 0;
		var callbackfunc = (params_obj['callbackfunc'] != undefined && params_obj['callbackfunc'] != null) ? params_obj['callbackfunc'] : null;
		var callbackfunc_error = (params_obj['callbackfunc_error'] != undefined && params_obj['callbackfunc_error'] != null) ? params_obj['callbackfunc_error'] : null;
		var target_el = (params_obj['target_el'] != undefined && params_obj['target_el'] != null) ? params_obj['target_el'] : null;
		var debug_param = (params_obj['debug'] != undefined && params_obj['debug'] != null) ? params_obj['debug'] : 0;
		var timeout_flag = (params_obj['timeout_flag'] != undefined && params_obj['timeout_flag'] != null) ? params_obj['timeout_flag'] : 1;

		if(debug_param) {debug_param = 1;}

		if(commonCls.inAttachment[form_target] != null) {
			//アップロード中
			return;
		}
		commonCls.inAttachment[form_target] = true;

		if (params_obj['document_obj']) {
			var document_object = params_obj['document_obj'];
			var formList = document_object.getElementsByTagName("form");
		} else {
			var document_object = document;
			var formList = top_el.getElementsByTagName("form");
		}
		for (var i = 0; i < formList.length; i++){
			if(formList[i].target == form_target) {
				if(params_obj['param'] != undefined || params_obj['param'] != null) {
					var action_flag = false;
					var name_arr = new Array();
					var value_arr = new Object();
					var count = 0;
					for(var key in params_obj['param']){
						if(key == "action") {
							action_flag = true;
						}
						name_arr[count] = key;
						value_arr[key] = encodeURIComponent(params_obj['param'][key]);
						count++;
					}
					if(!action_flag) {
						//actionがない場合、エラー
						return false;
					}
					var token_el = Element.getChildElementByClassName(top_el, "_token");
					var queryParams = commonCls.getParams(top_el);
					var block_id = (queryParams["block_id"] == undefined) ? 0 : queryParams["block_id"];
					var page_id = queryParams["page_id"];
					var module_id = queryParams["module_id"];

					name_arr[count++] = "download_action_name";
					value_arr['download_action_name'] = download_action;
					name_arr[count++] = "_attachment_callback";
					value_arr['_attachment_callback'] = "tmp_" + form_target;
					name_arr[count++] = "_header";
					value_arr['_header'] = header_flag;
					if(token_el) {
						name_arr[count++] = "_token"
						value_arr['_token'] = token_el.value;
					}
					name_arr[count++] = "block_id";
					value_arr['block_id'] = block_id;
					name_arr[count++] = "page_id";
					value_arr['page_id'] = page_id;
					name_arr[count++] = "module_id";
					value_arr['module_id'] = module_id;
					if(!queryParams['prefix_id_name']) {
						//
						// idがすでにprefix_id_nameつきであれば、prefix_id_nameを作成
						//
						if(block_id != 0) var att_suffix_id = block_id;
						else var att_suffix_id = module_id;
						if(att_suffix_id.length + 1 != id.length) {
							var att_re_suffix_id = new RegExp("_"+att_suffix_id + "$", "i");
							var att_replace_str = id.replace(att_re_suffix_id,"");
							if(att_replace_str == id) {
								var att_re_suffix_id = new RegExp("_"+block_id + "$", "i");
								var att_replace_str = id.replace(att_re_suffix_id,"");
							}
							att_replace_str = att_replace_str.substr(1,att_replace_str.length - 1);
							if(att_replace_str != "") {
								name_arr[count++] = "prefix_id_name";
								value_arr['prefix_id_name'] = att_replace_str;
							}
						}
					} else {
						name_arr[count++] = "prefix_id_name";
						value_arr['prefix_id_name'] = queryParams["prefix_id_name"];
					}

					//check
					name_arr = _checkInputTag(name_arr, formList[i]);

					this.attachmentCallBack[form_target] = callbackfunc;
					this.attachmentErrorCallBack[form_target] = callbackfunc_error;
					this.attachmentTarget[form_target] = target_el;

					var div=document_object.createElement('div');
					div.id = "tmp_" + form_target;
					//formList[i].id = "tmp_form_" + form_target;
					div.style.visibility = "hidden";
					div.innerHTML='<iframe src="about:blank" name="' + form_target + '" style="width:0px;height:0px;"></iframe>';
					document_object.body.appendChild(div);
					for (var j = 0; j < name_arr.length; j++){
						if(value_arr[name_arr[j]] || value_arr[name_arr[j]] == 0) {
							if(name_arr[j] != "action") {
								_createHiddenTag(name_arr[j],value_arr[name_arr[j]],formList[i]);
							} else {
								var action_name = value_arr[name_arr[j]];
							}
						}
					}
					formList[i].action = _nc_base_url + _nc_index_file_name + '?action=' + action_name;
					if(action_name != undefined) {
						_createHiddenTag("action", action_name, formList[i]);
					}
					formList[i].method = "post";
					//formList[i].enctype = "multipart/form-data";
					commonCls.referObject = document_object;
					formList[i].submit();
					commonCls._attachmentChecker(form_target, match_str, debug_param, 0, timeout_flag);

					var attachment_hiddenfields = Element.getElementsByClassName(formList[i], "_attachment_hidden");
					attachment_hiddenfields.each(function(el) {
						Element.remove(el);
					}.bind(this));
					return true;
				}
			}
		}
		return false;
		function _checkInputTag(name_arr, form_el){
			var inputList = form_el.getElementsByTagName("input");
			for (var j = 0; j < inputList.length; j++){
				if(inputList[j].name) {
					var pos = name_arr.indexOf(inputList[j].name);
					if(pos >= 0) {
						name_arr[pos] = null;
					}
				}
			}
			return name_arr.compact();
		}
		/*Hidden属性クリエイトメソッド*/
		function _createHiddenTag(key_name, value, form_el){
			//var input = form_el[key_name];
			//if(!input) {
				var input=document_object.createElement('input');
				input.setAttribute("name",key_name,1);
				input.setAttribute("type","hidden",1);
				input.value = value;
				Element.addClassName(input, "_attachment_hidden");
				form_el.appendChild(input);
			//} else if(input.getAttribute("type") == "hidden") {
			//	input.value = value;
			//}
		}
	},
	_attachmentChecker: function(form_target, match_str, debug_param, totaltime, timeout_flag) {
		var iframe_target_el = commonCls.referObject.getElementById("tmp_" + form_target);
		//var re_iframe = new RegExp('^<iframe', 'ig');
		if(browser.isSafari) {
			if(Element.getChildElement(iframe_target_el).contentWindow) {
				if(Element.getChildElement(iframe_target_el).contentWindow.document && Element.getChildElement(iframe_target_el).contentWindow.document.body) {
/*debug.p(Element.getChildElement(iframe_target_el).contentWindow.document.body.innerHTML);*/
					var div = Element.getChildElementByClassName(Element.getChildElement(iframe_target_el).contentWindow.document.body, "_attachment_result");

					if(div) {

						Element.addClassName(iframe_target_el, "_attachment_end")
					}
				}
			}
		}
		if(totaltime > 30000 && timeout_flag == 1) {
			if (!commonCls.confirm(commonLang.upload_timeout_confirm)) {
				Element.remove(iframe_target_el);
				commonCls.inAttachment[form_target] = null;
				return;
			}
			totaltime = 0;
		}

		if(!Element.hasClassName(iframe_target_el, "_attachment_end")) {
			if(match_str == null || match_str == undefined) {
				setTimeout("commonCls._attachmentChecker('"+form_target+"',"+match_str+","+debug_param+","+(totaltime+200)+","+timeout_flag+")", 200);
			} else {
				setTimeout("commonCls._attachmentChecker('"+form_target+"','"+match_str+"',"+debug_param+","+(totaltime+200)+","+timeout_flag+")", 200);
			}
		} else {
			iframe_target_el.innerHTML = Element.getChildElement(iframe_target_el).contentWindow.document.body.innerHTML;
			var callback_func = commonCls.attachmentCallBack[form_target];
			var callbackfunc_error = commonCls.attachmentErrorCallBack[form_target];
			var target_el = commonCls.attachmentTarget[form_target];
			commonCls.attachmentCallBack[form_target] = null;
			commonCls.attachmentErrorCallBack[form_target] = null;
			commonCls.attachmentTarget[form_target] = null;
			commonCls.inAttachment[form_target] = null;
			var div = Element.getChildElementByClassName(iframe_target_el, "_attachment_result");
			if(div) {
				var response = new Object();
				for (var i = 0; i < div.childNodes.length; i++) {
					var file = div.childNodes[i];
					response[i] = new Object();
					for (var j = 0; j < file.childNodes.length; j++) {
						response[i][file.childNodes[j].title] = file.childNodes[j].innerHTML;
					}
				}
				Element.remove(div);
			}
			var res = iframe_target_el.innerHTML;
			//前処理
			if(_nc_debug) var res = commonCls.AjaxResultStr(res);
			if(debug_param) {
				if(typeof debug == 'object') {
					debug.p(res);
				} else {
					commonCls.alert(res);
				}
			}
			commonCls.referObject = null;
			if((match_str != null && match_str != undefined && commonCls.matchContentElement(res,match_str)) ||
				((match_str == null || match_str == undefined) && !commonCls.matchErrorElement(res))) {
				//正常
				if(target_el) {
					target_el.innerHTML = res;
				}
				//後処理
				//commonCls.AjaxResultScript(res);
				if(callback_func) {
					callback_func(response, res);
				}
			} else {
				//異常
				res = commonCls.cutErrorMes(res);
				if(callbackfunc_error) {
					callbackfunc_error(response, res);
				} else {
					commonCls.alert(res);
				}
			}
			Element.remove(iframe_target_el);
		}
	},
	// img -src画像変換
	// @param element el(img_el or parent_img_el)
	// @param string  prev_name:変換前の画像名称
	// @param string  change_name:変換後の画像名称
	// @param string  title_alt:変換後のtitle,alt
	imgChange: function(el, prev_name, change_name, alt_title_str) {
		var img_el = (el.tagName.toLowerCase() == "img") ? el : el.getElementsByTagName("img")[0];
		if(img_el) {
			prev_name=prev_name.replace(/(\!|"|'|\(|\)|\-|\=|\^|\\|\||\[|\{|\+|\:|\*|\]|\}|\,|\<|\.|\>|\/|\?)/g,"\\$1");
			var re = new RegExp(prev_name + "$", "i");
			img_el.src = img_el.src.replace(re, change_name);
			if(alt_title_str != undefined) {
				img_el.title = alt_title_str;
				img_el.alt = alt_title_str;
			}
		}
	},
	tabsetActive: function(this_el) {
		var targetEl = this_el;
		if(!Element.hasClassName(targetEl,"comptabset_tabset")) {
			var targetEl = Element.getParentElementByClassName(targetEl,"comptabset_tabset");
		}
		var tab_el = Element.getParentElementByClassName(targetEl,"comptabset_tabs");
		var tableList = tab_el.getElementsByTagName("table");
		var active_flag = true;
		for (var i = 0; i < tableList.length; i++){
			if(Element.hasClassName(tableList[i],"comptabset_tabset")) {
				if(targetEl == tableList[i] || targetEl.parentNode == tableList[i]) {
					if(Element.hasClassName(tableList[i],"comptabset_active")) {
						active_flag = false;
						break;
					} else {
						Element.addClassName(tableList[i],"comptabset_active");
					}
				} else {
					Element.removeClassName(tableList[i],"comptabset_active");
				}
			}
		}
		return active_flag;
	},
	/* _headermenu<{$id}>からActiveなタブにfocusする */
	tabsetFocus: function(id) {
		var headermenu_el = $("_headermenu"+ id);
		var active_el = Element.getChildElementByClassName(headermenu_el,"comptabset_active");
		var a_el = active_el.getElementsByTagName("a")[0];
		commonCls.focus(a_el);
	},
	/* top_id->formからフォーカスを移動 */
	focus: function(id) {
		//ポップアップの場合、focus>windo移動となるため、focus処理をtimerで行う
		if (typeof id == 'string') {
			setTimeout("commonCls.focusComp('"+id+"')", 300);
		} else {
			setTimeout(function(){commonCls.focusComp(this);}.bind(id), 300);
		}
	},
	focusComp: function(id, error_count) {
		try {
			error_count = (error_count == undefined) ? 0 : error_count;
			if (typeof id == 'string') {
				var top_el = $(id);
				var form = top_el.getElementsByTagName("form")[0];
				if(form) {
					var result = Form.focusFirstElement(form);
				}
			} else {
				var result = false;
				if(id.nodeType == 1) {
					var top_el = id;
					var name =id.tagName.toLowerCase();
					if(name == 'input' || name == 'select' || name == 'textarea') {
						id.focus();
						id.select();
						result = true;
					} else if(name == 'a') {
						id.focus();
						result = true;
					} else if(name == 'form') {
						result = Form.focusFirstElement(id);
					} else {
						var inputList = id.getElementsByTagName("input");
						for (var i = 0; i < inputList.length; i++){
							if ((inputList[i].type == "text" || inputList[i].type == "select" || inputList[i].type == "textarea")
								&& !inputList[i].disabled){
								inputList[i].focus();
								inputList[i].select();
								result = true;
								break;
							}
						}
					}
				}
			}
			if(!result && top_el) {
				var a_el = top_el.getElementsByTagName("a")[0];
				if(a_el) a_el.focus();
			}
		}catch(e){
			if(error_count < 5) {
				//
				// 5回までフォーカス移動リトライ
				//
				error_count++;
				if (typeof id == 'string') {
					setTimeout("commonCls.focusComp('"+id+"'," + error_count + ")", 300);
				} else {
					setTimeout(function(){commonCls.focusComp(this, error_count);}.bind(id), 300);
				}
			}
		}
	},
	addBlockTheme: function(theme_name) {
		var themeStrList = theme_name.split("_");
		if(themeStrList.length == 1) {
			var template_block_dir = "themes/" + theme_name + "/css/";
		} else {
			theme_name = themeStrList.shift();
			var template_block_dir = "themes/" + theme_name + "/css/" + themeStrList.join("/") + "/";
		}

		//スタイルシート追加処理
		commonCls.addCommonLink("/" + template_block_dir+"style.css");

		//スタイルシート追加処理
		//commonCls.addLink(_nc_base_url + "/" + template_block_dir+"style.css");
		//ブロックテーマがカスタマイズされていた場合
		//commonCls.addLink(_nc_base_url + "/" + template_block_dir + commonCls.cssBlockCustom);
	},
	//移動中のスクロール移動
	scrollMoveDrag: function(event, offset) {
		//スクロールバー移動
		//40pxづつ移動
		var offset = (offset == undefined) ? 40 : offset;

		Position.prepare();
		if(Event.pointerX(event) - Position.deltaX  > Position.getWinOuterWidth() - offset) {
			scrollTo(Position.deltaX + offset -10, Position.deltaY);
		}else if(Event.pointerX(event)  <= Position.deltaX + offset && Position.deltaX > 0) {
			scrollTo(Position.deltaX - offset, Position.deltaY);
		}

		if(Event.pointerY(event) - Position.deltaY  > Position.getWinOuterHeight() - offset) {
			scrollTo(Position.deltaX, Position.deltaY + offset);
		} else if(Event.pointerY(event)  <= Position.deltaY + offset && Position.deltaY > 0) {
			scrollTo(Position.deltaX, Position.deltaY - offset);
		}
	},
	/* 色取得一般メソッド */
	// RBG値から HSL値を取得
	getHSL : function(r, g, b)
	{
		var h,s,l,v,m;
		var r = r/255;
		var g = g/255;
		var b = b/255;
		v = Math.max(r, g), v = Math.max(v, b);
		m = Math.min(r, g), m = Math.min(m, b);
		l = (m+v)/2;
		if (v == m) var sl_s = 0, sl_l = Math.round(l*255),sl_h=0;
		else
		{
			if (l <= 0.5) s = (v-m)/(v+m);
			else s = (v-m)/(2-v-m);
			if (r == v) h = (g-b)/(v-m);
			if (g == v) h = 2+(b-r)/(v-m);
			if (b == v) h = 4+(r-g)/(v-m);
			h = h*60; if (h<0) h += 360;
			var sl_h = Math.round(h/360*255);
			var sl_s = Math.round(s*255);
			var sl_l = Math.round(l*255);
		}
		return { h : sl_h, s : sl_s , l : sl_l };
	},

	// HSL値から RBG値を取得
	getRBG : function(h, s, l)
	{
		var r, g, b, v, m, se, mid1, mid2;
		h = h/255, s = s/255, l = l/255;
		if (l <= 0.5) v = l*(1+s);
		else v = l+s-l*s;
		if (v <= 0) var sl_r = 0, sl_g = 0, sl_b = 0;
		else
		{
			var m = 2*l-v,h=h*6, se = Math.floor(h);
			var mid1 = m+v*(v-m)/v*(h-se);
			var mid2 = v-v*(v-m)/v*(h-se);
			switch (se)
			{
				case 0 : r = v;    g = mid1; b = m;    break;
				case 1 : r = mid2; g = v;    b = m;    break;
				case 2 : r = m;    g = v;    b = mid1; break;
				case 3 : r = m;    g = mid2; b = v;    break;
				case 4 : r = mid1; g = m;    b = v;    break;
				case 5 : r = v;    g = m;    b = mid2; break;
			}
			var sl_r = Math.round(r*255);
			var sl_g = Math.round(g*255);
			var sl_b = Math.round(b*255);
		}
		return { r : sl_r, g : sl_g , b : sl_b };
	},
	getRGBtoHex : function(color) {
		if(color.r ) return color;
		if(color == "transparent" || color.match("^rgba")) return "transparent";
		if(color.match("^rgb")) {
			color = color.replace("rgb(","");
			color = color.replace(")","");
			color_arr = color.split(",");
			return { r : parseInt(color_arr[0]), g : parseInt(color_arr[1]) , b : parseInt(color_arr[2]) };
		}
		if ( color.indexOf('#') == 0 )
			color = color.substring(1);
		var red   = color.substring(0,2);
		var green = color.substring(2,4);
		var blue  = color.substring(4,6);
		return { r : parseInt(red,16), g : parseInt(green,16) , b : parseInt(blue,16) };
	},
	getHex : function(r, g, b)
	{
		var co = "#";
		if (r < 16) co = co+"0"; co = co+r.toString(16);
		if (g < 16) co = co+"0"; co = co+g.toString(16);
		if (b < 16) co = co+"0"; co = co+b.toString(16);
		return co;
	},
	getColorCode: function(el , property_name) {
		if(property_name == "borderColor" || property_name == "border-color") {
			property_name = "borderTopColor";
		}
		if(property_name == "borderTopColor" || property_name == "borderRightColor" ||
			property_name == "borderBottomColor" || property_name == "borderLeftColor") {
			var width = Element.getStyle(el, property_name.replace("Color","")+"Width");
			if(width == "" || width == "0px" || width == "0") {
				return "transparent";
			}

		}
		var rgb = Element.getStyle(el, property_name);
		if(rgb == undefined || rgb == null) {
			return "transparent";
		} else if (rgb.match("^rgba") && rgb != "transparent" && rgb.substr(0, 1) != "#") {
			rgb = rgb.substr(5, rgb.length - 6);
			var rgbArr = rgb.split(",");
			if(rgbArr[3].trim() == "0")
				rgb = "";
			else
				rgb = commonCls.getHex(parseInt(rgbArr[0]),parseInt(rgbArr[1]),parseInt(rgbArr[2]));
		} else if (rgb.match("^rgb") && rgb != "transparent" && rgb.substr(0, 1) != "#") {
			rgb = rgb.substr(4, rgb.length - 5);
			var rgbArr = rgb.split(",");

			rgb = commonCls.getHex(parseInt(rgbArr[0]),parseInt(rgbArr[1]),parseInt(rgbArr[2]));
		} else if(rgb.substr(0, 1) != "#"){
			//windowtext等
			if(property_name == "backgroundColor") {
				return "transparent";
			}
			return "";
		}
		return rgb;
	},
	colorCheck: function(event) {
		if(((event.ctrlKey && !event.altKey) || event.keyCode == 229 || event.keyCode == 46 || event.keyCode == 8 ||
			(event.keyCode >= 37 && event.keyCode <= 40) || event.keyCode == 9 || event.keyCode == 13 ||
			(event.keyCode >= 96 && event.keyCode <= 105) ||
			(event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 65 && event.keyCode <= 70)))
			return true;
		return false;
	},
	numberCheck: function(event) {
		if(((event.ctrlKey && !event.altKey) || event.keyCode == 229 || event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 13 ||
			(event.keyCode >= 96 && event.keyCode <= 105) ||
			(event.keyCode >= 37 && event.keyCode <= 40) || (!event.shiftKey && event.keyCode >= 48 && event.keyCode <= 57)))
			return true;
		return false;
	},
	numberConvert: function(event) {
		if(event.keyCode == 13 || event.type == "blur") {
			var event_el = Event.element(event);
			var num_value = event_el.value;
			var en_num = "0123456789.,-+";
			var em_num = "０１２３４５６７８９．，－＋";
			var str = "";
			for (var i=0; i< num_value.length; i++) {
				var c = num_value.charAt(i);
				var n = em_num.indexOf(c,0);
				var m = en_num.indexOf(c,0);
				if (n >= 0) {c = en_num.charAt(n);str += c;
				} else if (m >= 0) str += c;
			}
			if(num_value != str) event_el.value = str;
			return true;
		}
		return false;
	},
	//safariの場合、parent.Event.observe等でエラーとなるのでメソッド化
	observe: function(element, name, observer, useCapture, top_el) {
		Event.observe(element, name, observer, useCapture, top_el);
	},
	stopObserving: function(element, name, observer, useCapture) {
		Event.stopObserving(element, name, observer, useCapture);
	},
	stop: function(event) {
		Event.stop(event);
	},
	setStyle: function(el, value) {
		Element.setStyle(el, value);
	},
	hasClassName: function(el, class_name) {
		return Element.hasClassName(el,class_name);
	},

	changeAuthority: function(checkbox, id) {
		if (checkbox.type != "checkbox"
				|| checkbox.id.length == 0) {
			return;
		}

		var name = checkbox.id.substr(0, checkbox.id.length - id.length);
		var ahuthId = name.match(/\d+$/);
		name = name.substr(0, name.length - ahuthId.length);

		while (checkbox.checked) {
			ahuthId++;
			var element = $(name + ahuthId + id);
			if (element == null) break;

			element.checked = true;
		}

		while (!checkbox.checked) {
			ahuthId--;
			var element = $(name + ahuthId + id);
			if (element == null) break;

			element.checked = false;
		}
	},

	// javascript動的ロード
    load : function(src, check, next, timeout) {
		src = src.replace(/&amp;/g,"&");
		check = new Function('return !!(' + check + ')');
		if (!check()) {
			var script = document.createElement('script');
				script.src = src;
			document.body.appendChild(script);
		}
		this.wait(check, next, timeout);
	},

	// 動的ロードの待機
	wait: function  (check, next, timeout) {
		if (!check()) {
			setTimeout(function() {
				if(timeout != undefined) {
					timeout = timeout - 100;
					if(timeout < 0) return;
				}
				if (!check()) setTimeout(arguments.callee, 100);
				else if(next != null) next();
			}, 100);
 		} else if(next != null)
 			next();
 	},

 // 正規表現のエスケープ処理
 	escapeRegExp: function  (str) {
 		return str.replace(/([\\\/\^\$\*\+\?\{\|\}\[\]])/g,"\\$1");
 	}
}
commonCls = new clsCommon();