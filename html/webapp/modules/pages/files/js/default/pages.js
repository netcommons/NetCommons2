var clsPages = Class.create();

clsPages.prototype = {
	initialize: function() {
		this.center_page_id = null;
		this.pages_token = new Object();

		this.move_el = null;		//移動元エレメント
		this.move_td = null;		//移動元エレメント(td-cell)

		this.xOffset = 0;
		this.yOffset = 0;
		this.start_x = 0;				//ドラッグスタート位置x
		this.start_y = 0;				//ドラッグスタート位置y
		this.move_column_el = null;	//移動中ブロックカラムエレメント
		this.insert_tr = null;			//移動中ブロックTRエレメント
		this.insert_el = null;			//移動先エレメント
		this.insert_td = null;			//移動中インサートしたCell

		this.active = null;		//移動中エレメント(popup)
		this.active_style = null;	//移動中エレメントstyle(padding)

		this.insertAction = null;		//移動アクション名称(insertcell or insertrow)
		this.insertMoveEndRowThreadNum = null;		//移動先深さ
		this.insertMoveEndRowParentId = null;		//移動先親ID

		this.inMoveDrag = false;		//移動中かどうか
		this.inChgBlockName = new Object();			//ブロック名称変更中かどうか
		this.inMoveShowHeader = new Object();			//移動用ヘッダー表示中かどうか

		this.inCancelGroupingDrag = false;		//グルーピング解除中かどうか

		//移動DB登録用
		this.insertMoveCellIndex = null;		//移動元Index
		this.insertMoveRowIndex = null;		//移動元Index
		this.insertMoveRowLength = null;		//移動元RowLength
		this.insertMoveCellLength = null;		//移動元CellLength
		//this.insertMoveRowThreadNum = null;	//移動元深さ
		this.insertMoveRowParentId = null;		//移動元親ID
		this.insertMoveRowBlockId = null;		//移動元block_id

		this.show_count = new Object();

		this.winMoveDragStartEvent = new Object();			//移動スタートイベント
		this.winMoveDragGoEvent = new Object();				//移動中イベント
		this.winMoveDragStopEvent = new Object();			//移動ストップイベント
		this.winGroupingEvent  = new Object();				//選択イベント

		this.parentThreadNum = 0;							//親の深さ
		this.parentParentid = 0;							//親のID

		this.cloneTopPos = new Object();
		this.cloneColumnPos = Array();
		this.clonePos = Array();

		//グルーピング用
		this.groupingList = Array();

		//
		//ページ追加時デフォルトpadding(現状、固定)
		//
		this.block_left_padding = 8;
		this.block_right_padding = 8;
		this.block_top_padding = 8;
		this.block_bottom_padding = 8;
	},
	pageInit: function(active_center) {
		Event.observe(document,"mousedown",this.winMouseDownEvent.bindAsEventListener(this),false);
		if($("_settingmode")) {
			// 主担でセンターカラムに１つもモジュールを配置していない場合、メッセージ表示
			var centercolumn_el = $("__centercolumn");
			//var active_center = $_GET('active_center');
			if(active_center == 0 && _nc_layoutmode != "on" && centercolumn_el) {
				var cell_el = Element.getChildElementByClassName(centercolumn_el,"cell");
				if(!cell_el) {
					var centercolumn_inf_mes_el = $("centercolumn_inf_mes");
					if(centercolumn_inf_mes_el) {
						var div = document.createElement("DIV");
						div.innerHTML = pagesLang.centercolumnNoexists;
						centercolumn_inf_mes_el.appendChild(div);
					} else {
						centercolumn_el.innerHTML = "<div id='centercolumn_inf_mes'><div>"+pagesLang.centercolumnNoexists+"</div></div>";
					}
				}
			}
		}
	},
	//ブロック内部でないならばグループ化をキャンセルする
	winMouseDownEvent: function(event) {
		var el = Event.element(event);
		if (Element.hasClassName(el,"header_btn") || Element.hasClassName(el,"header_btn_left")){
			return false;
		}

		var divList = document.getElementsByTagName("div");
		var check_flag = true;
		for (var i = 0; i < divList.length; i++){
			if (Element.hasClassName(divList[i],"cell")){
				var child_el = Element.getChildElement(divList[i]);
				if(child_el && Position.within(child_el, Event.pointerX(event),Event.pointerY(event))) {
					check_flag = false;
					break;
				}
			}
		}
		if(check_flag) {
			//選択をキャンセルする
			var divList = document.getElementsByTagName("div");
			for (var i = 0; i < divList.length; i++){
				if (Element.hasClassName(divList[i],"cell")){
					pagesCls.cancelSelectStyle(divList[i]);
				}
			}
		}
	},
	setToken: function(page_id,token_value,center_flag) {
		if(center_flag)
			this.center_page_id = page_id;
		this.pages_token[page_id] = token_value;
	},
	setShowCount: function(page_id,show_count) {
		this.show_count[page_id] = show_count;
	},
	/*ブロック名称変更*/
	blockChangeName: function(event) {
		//var target_el = this;
		var block_el = Element.getParentElementByClassName(this,"module_box");
		//var title_el = Element.getChildElementByClassName(block_el,"nc_block_title");
		if(Element.hasClassName(this,"nc_block_title") || Element.hasClassName(this,"_block_title_abs")) {
			var title_el = this;
		//} else {
		//	var target_el = this;
		//	var block_el = Element.getParentElementByClassName(target_el,"module_box");
		//	var title_el = Element.getChildElementByClassName(block_el,"nc_block_title");
		//}
			if(Element.getChildElement(title_el) && Element.getChildElement(title_el).tagName == "INPUT") {
				//既に変更済
			} else {
				var text = title_el.innerHTML.trim();

				title_el.innerHTML = "<input style='width:90%;' name=\"title_el\" type=\"text\" maxlength=\"50\" title=\"" + text + "\" value=\"" + text + "\" onblur=\"pagesCls.blockChangeNameCommit(event,this);\" onkeypress=\"if (event.keyCode == 13) {if(browser.isNS && !browser.isSafari) {pagesCls.blockChangeNameCommit(event,this);}else{this.blur();}return false;}\" autocomplete=\"off\" />";
			}
			//フォーカスを最初のinputタグに移動
			commonCls.focus(title_el);

			pagesCls.inChgBlockName[block_el.id] = true;

		}
	},
	/*ブロック名称変更決定*/
	blockChangeNameCommit: function(event,input_el) {
		var event_el = Event.element(event);
		var block_el = Element.getParentElementByClassName(event_el,"module_box");
		var title_el = new Array();
		title_el[0] = Element.getChildElementByClassName(block_el,"nc_block_title");
		title_el[2] = Element.getChildElementByClassName(block_el,"_block_title_event");
		if(!title_el[2]) {title_el[2] = title_el[0];}
		if(block_el) {
			title_el[1] = Element.getChildElementByClassName(block_el,"_block_title_abs");
			//if(Element.hasClassName(block_el,"_move_header")) {
			//	title_el[1] = title_el[0];
			//	title_el[0] = Element.getChildElementByClassName(block_el,"nc_block_title");
			//
			//	block_el = Element.getParentElementByClassName(block_el,"module_box"); //block_el.previousSibling;
			//	//title_el[1] = title_el[0];
			//	//title_el[0] = Element.getChildElementByClassName(block_el,"nc_block_title");
			//} else {
			//	var tmp_block_el =  $("_move_header" + block_el.id); //block_el.nextSibling;
			//	title_el[1] = Element.getChildElementByClassName(tmp_block_el,"nc_block_title");
			//}

			var text = input_el.value.escapeHTML();
			var parent_el = input_el.parentNode;
			if(!parent_el)
				return false;
			var old_text = input_el.title.escapeHTML();
			parent_el.innerHTML = text;
			//if(Element.hasClassName(parent_el,"mod_title_input")) {
			//	var mod_title_flag = true;
			//	Element.removeClassName(parent_el,"mod_title_input");
			//	Element.addClassName(parent_el,"mod_title");
			//}
			var queryParams = commonCls.getParams(block_el);
			var page_id = queryParams["page_id"];

			//var el = input_el.parentNode;
			var params = new Object();

			params["method"] = "post";
			params["param"] = "pages_actionblock_chgblockname" + "&block_name=" + encodeURIComponent(input_el.value);
			params["top_el"] = block_el;
			params["loading_el"] = parent_el;
			params["callbackfunc_error"] = function(old_text,res){commonCls.alert(res); this.innerHTML = old_text;}.bind(parent_el);
			params["func_error_param"] = old_text;
			//if(mod_title_flag) {
				//if(block_el) {
					//var title_el = Element.getChildElementByClassName(block_el,"nc_block_title");
					params["callbackfunc"] = function(text){
						this[0].innerHTML = text;
						if( this[1] ) this[1].innerHTML = text;
						//	var title_el = this[1];
						//} else {
						//	var title_el = this[0];
						//}
						if(text == '' && !Element.hasClassName(this[0], "display-none")) {
							if(_nc_layoutmode=="off") Element.addClassName(this[2],"display-none");
						} else {
							Element.removeClassName(this[2],"display-none");
						}
						pagesCls.inChgBlockName[block_el.id] = false;
					}.bind(title_el);
					params["func_param"] = text;

					//params["callbackfunc"] = function(text){this.innerHTML = text;}.bind(title_el);
					//params["func_param"] = text;
				//}
			//} else {
			//	if(block_el) {
			//		var title_el = Element.getChildElementByClassName(block_el,"mod_title");
			//		params["callbackfunc"] = function(text){this.innerHTML = text;}.bind(title_el);
			//		params["func_param"] = text;
			//	}
			//}
			params["token"] = pagesCls.pages_token[page_id];
			commonCls.send(params);
		}
		//Event.stop(event);
		return false;
	},
	//-----------------------------------------------------------------------------
	// ブロック追加
	//-----------------------------------------------------------------------------
	addBlock: function(event, page_id, module_id_arr) {

		var module_id_arr_list = module_id_arr.split("_");
		var module_id = module_id_arr_list[0];
		var dir_name = module_id_arr_list[1];
		if(module_id == "0") return; // Windows版 Safariが誤動作を起こすため。
		//
		// scriptタグ追加
		//
		if(dir_name != "login") {
			var scripts = document.getElementsByTagName("script");
			for (var i = 0,scripts_len = scripts.length; i < scripts_len; i++) {
				var script_el = scripts[i];
				if(script_el.src.match("common_download_js")) {
					var queryParams = script_el.src.parseQuery();
					var re_dir_name = new RegExp("(.*?)(common_download_js&dir_name=)(.*?)("+dir_name+")(.*)", "i");
					if(	!script_el.src.match(re_dir_name)) {
						commonCls.addScript(_nc_core_base_url + _nc_index_file_name + "?action=common_download_js&dir_name=" + dir_name + "&add_block_flag=1"+"&vs=" + _nc_js_vs);
					}
					break;
				}
			}
		}
		var event_el = Event.element(event);
		var addmobule_box_el = Element.getParentElementByClassName(event_el,"addmobule_box");
		var main_column_el = Element.getChildElementByClassName(addmobule_box_el.parentNode.nextSibling, "main_column");
		//var main_column_el = addmobule_box_el.parentNode.nextSibling;
		var addblock_params = new Object();

		//DB登録
		var postBody = "pages_actionblock_addblock" + "&module_id=" + module_id + "&page_id=" + page_id +
							"&topmargin=" + this.block_top_padding + "&rightmargin=" + this.block_right_padding +
							"&bottommargin=" +this.block_bottom_padding  + "&leftmargin=" + this.block_left_padding +
							"&_show_count=" + pagesCls.show_count[page_id];

		addblock_params["method"] = "post";
		addblock_params["param"] = postBody;
		addblock_params["loading_el"] = event_el;
		//addblock_params["match_str"] = "";
		//addblock_params["target_el"] = div;
		addblock_params["callbackfunc"] = function(res){

											//移動用ヘッダー非表示
											for (var key in pagesCls.inMoveShowHeader) {
												if(pagesCls.inMoveShowHeader[key]) {
													var header_el = $("_move_header" + key);
													Element.addClassName(header_el, "display-none");
													pagesCls.inMoveShowHeader[key] = false;
												}
											}

											//リロードを行わないように修正
											//location.reload();
											var table_el = Element.getChildElement(main_column_el);
											var tr = table_el.getElementsByTagName("tr")[0];
											var column_el = Element.getChildElement(tr);
											if(!column_el) {
												column_el = tr.insertCell(0);
												column_el.className = "column valign-top";
											}
											var div = document.createElement("DIV");
											div.className = "cell";
											div.style.padding = this.block_top_padding + "px" + " " + this.block_right_padding + "px" + " " + this.block_bottom_padding + "px" + " " + this.block_left_padding + "px";
											var child_el = Element.getChildElement(column_el);
											if(child_el) {
												column_el.insertBefore(div, child_el);
											} else {
												column_el.appendChild(div);
											}
											//send
											var queryParams = res.parseQuery();
											commonCls.addBlockTheme(queryParams['theme_name']);
											queryParams['page_id'] = page_id;
											queryParams['module_id'] = module_id;
											queryParams['_layoutmode'] = _nc_layoutmode;
											
											//パラメータ取得
											var add_params = new Object();
											var action_name_list = queryParams['action'].split("_");
											if(action_name_list[1] && action_name_list[1]=="action") {
												add_params["method"] = "post";
											}
											//add_params["method"] = "get";
											add_params["param"] = queryParams;
											add_params["target_el"] = div;
											add_params["callbackfunc"] = function(res){
																			if(browser.isGecko) {
																				//FireFoxでは、CSSが動的に読み込まれた後にすぐスタイルを適用してくれないため
																				/*
																				Element.addClassName(Element.getChildElement(add_params["target_el"]), "collapse_separate");
																				setTimeout(function(){
																					Element.removeClassName(Element.getChildElement(this), "collapse_separate");
																				}.bind(add_params["target_el"]), 100);
																				*/
																			}
																			this.show_count[page_id]++;
																		}.bind(this);
											add_params["callbackfunc_error"] = function(res){this.show_count[page_id]++; commonCls.alert(res); location.reload();}.bind(this);
											commonCls.send(add_params);
											//表示回数カウント++
											//this.show_count[page_id]++;
											//if(_nc_layoutmode == "on") {
											//	setTimeout(function() {
											//		//移動用ヘッダー移動
											//		pagesCls.winMoveResizeHeader();
											//	}.bind(this), 400);
											//}
										  }.bind(this);

		addblock_params["callbackfunc_error"] = function(res){commonCls.alert(res); location.reload();};
		addblock_params["token"] = pagesCls.pages_token[page_id];
		commonCls.send(addblock_params);

		//表示回数カウント++
		//pagesCls.show_count[page_id]++;
		//選択を戻す
		event_el.selectedIndex = 0;
		if(browser.isIE) {
			document.body.focus();
		} else {
			event_el.blur();
		}
	},
	//-----------------------------------------------------------------------------
	// ブロック削除
	//-----------------------------------------------------------------------------
	deleteBlock: function(event, id, confirm_mes, request_flag) {
	
		var target_el;
		if (id == undefined) {
			target_el = Event.element(event);
		} else {
			target_el = $(id);
		}

		var cell_el = Element.getParentElementByClassName(target_el,"cell");
		if(cell_el) {
			var td_el = Element.getParentElement(cell_el);
			var tr_el = Element.getParentElement(td_el);
			var top_el = Element.getChildElement(cell_el);
			var title_el = Element.getChildElementByClassName(top_el,"nc_block_title");
			var parent_cell = Element.getParentElementByClassName(tr_el,"cell");
			if(parent_cell) {
				parent_id = commonCls.getBlockid(parent_cell);
			} else {
				parent_id = 0;
			}
		} else {
			var top_el = Element.getChildElement(document.body);
			var title_el = Element.getChildElementByClassName(top_el,"nc_block_title");
		}
		if(confirm_mes != undefined || confirm_mes != null) {
			var text = title_el.innerHTML.trim();
			if(text == "") text = pagesLang.emptyBlockname;
			if (!commonCls.confirm(confirm_mes.replace("%s", text))) return false;
		}
		var queryParams = commonCls.getParams(top_el);
		var page_id = queryParams["page_id"];

		if(cell_el) {
			var count = 0;
			for (var i = 0; i < td_el.childNodes.length; i++) {
				var div = td_el.childNodes[i];
				if(div && div.tagName == "DIV" && Element.hasClassName(div,"cell")) {
					count++;
				}
			}
			if(count == 1) {
				//もし、その列になにもなくなるのであれば、列削除
				//TODO:すべてのモジュールが削除したら、動かなくなる可能性あり
				//　　 後に要チェック
				Element.remove(td_el);
			} else {
				//ブロック削除
				Element.remove(cell_el);
			}

			var cell_length = tr_el.cells.length;

			//空のグルーピングボックス削除用
			if(cell_length == 0 && parent_id != 0) {
				pagesCls.delEmptyBlock(parent_id);
			}
		} else {
			Element.remove(top_el);
		}
		var delblock_params = new Object();

		delblock_params["method"] = "post";
		delblock_params["param"] = "pages_actionblock_deleteblock" +
		"&_show_count=" + pagesCls.show_count[page_id];
		delblock_params["top_el"] = top_el;
		
		//表示回数カウント++
		pagesCls.show_count[page_id]++;
		
		if(request_flag == false) return;
		
		//delblock_params["loading_el"] = cell_el;
		if(cell_el) {
			delblock_params["callbackfunc_error"] = function(res){commonCls.alert(res);location.reload();};
		}
		delblock_params["token"] = pagesCls.pages_token[page_id];
		commonCls.send(delblock_params);
	},
	//-----------------------------------------------------------------------------
	// 移動用ヘッダー表示
	//-----------------------------------------------------------------------------
	winMoveShowHeader: function(event, el, resize_flag) {
		resize_flag = (resize_flag == undefined) ? false : resize_flag;
		if(event) {
			var el = $(this.id);
		}
		if(typeof pagesCls == 'undefined' || !el || (pagesCls.inMoveDrag || pagesCls.inMoveShowHeader["_move_header" + el.id]))
			return false;
		var _move_header = $("_move_header" + el.id);

		if(_move_header) {

			////commonCls.max_zIndex = commonCls.max_zIndex + 1;
			////_move_header.style.zIndex = commonCls.max_zIndex;
			////_move_header.style.width = "0px";

			//_move_header.style.height = (y2 - y) +"px";
			//var y2 = offset[1] + this.offsetHeight;
			//var _move_header = $("_move_header");
			//var doc = document.createDocumentFragment();
			//var div = document.createElement("DIV");
			//div.innerHTML = _move_header.innerHTML;
			//doc.appendChild(div);
			//var move_bar = Element.getChildElement(doc);
			//Element.addClassName(move_bar, "visible-hide");
			//Element.addClassName(move_bar, "module_box");
			//Element.addClassName(move_bar, "_move_header");

			//move_bar.id = "_move_header" + this.id;
			//move_bar.style.position = "absolute";
			//move_bar.style.left = x +"px";
			//move_bar.style.width = (x2 - x) +"px";
			//move_bar.style.height = (y2 - y) +"px";

			//var title_el = Element.getChildElementByClassName(el,"nc_block_title");
			if(_move_header.style.position != "absolute") {
				_move_header.style.position = "absolute";
				////_move_header.style.position = "absolute";
				if(Element.hasClassName(_move_header, "_move_header")) {
					var _block_title = Element.getChildElementByClassName(_move_header, "_block_title_abs");
					var _block_title_event = Element.getChildElementByClassName(_move_header,"_block_title_event_abs");
					if(!_block_title_event) _block_title_event = _block_title;
					var move_bar = Element.getChildElementByClassName(_move_header,"_move_bar");
					Event.observe(_block_title, "mouseover", commonCls.blockNotice, false, el);
					//ダブルクリックでブロック名称変更
					Event.observe(_block_title_event,"dblclick",pagesCls.blockChangeName.bindAsEventListener(_block_title),false, el);
					//Event.observe(_move_header,"mouseout",pagesCls.winMoveHideHeader.bindAsEventListener(el),false, el);
	
					//移動
					pagesCls.winMoveDragStartEvent[el.id] = pagesCls.winMoveDragStart.bindAsEventListener(el);
					Event.observe(move_bar,"mousedown",pagesCls.winMoveDragStartEvent[el.id],false, el);
					pagesCls.winGroupingEvent[el.id] = pagesCls.onGroupingEvent.bindAsEventListener(el);
					Event.observe(move_bar,"click",pagesCls.winGroupingEvent[el.id],false, el);
				}
			}
			var offset = Position.cumulativeOffset(el);
			var x = offset[0];
			var y = offset[1];
			var x2 = offset[0] + el.offsetWidth;

			_move_header.style.left = x +"px";
			_move_header.style.width = (x2 - x) +"px";
			if(!resize_flag) {
				Element.addClassName(_move_header, "visible-hide");
				Element.removeClassName(_move_header, "display-none");
			}
			if(y < 0)  _move_header.style.top = "0px";
			else _move_header.style.top = y +"px";
			//if(y - _move_header.offsetHeight < 0)  _move_header.style.top = "0px";
			//else _move_header.style.top = (y - _move_header.offsetHeight) +"px";

			//Element.setStyle(_move_header, {"opacity":0.2});

			//setTimeout(function(){
			//	pagesCls.winMoveShowHeaderTimer(_move_header, 0.2);
			//}, 200);
			if(!resize_flag) {
				Element.removeClassName(_move_header, "visible-hide");
				pagesCls.inMoveShowHeader[el.id] = true;
				//pagesCls.inMoveShowHeader["_move_header" + el.id] = true;
				commonCls.moveVisibleHide(_move_header);
				if(event)Event.stop(event);
			}
		}
		//el.style.borderLeft = "1px solid #bfbfbf";
		//el.style.borderBottom = "1px solid #bfbfbf";
		//el.style.borderRight = "1px solid #bfbfbf";

		//_block_title
	},
	winMoveResizeHeader: function() {
		if(_nc_layoutmode == "on" && typeof pagesCls != 'undefined') {
			//移動用ヘッダー移動
			for (var key in pagesCls.inMoveShowHeader) {
				if(pagesCls.inMoveShowHeader[key]) {
					var header_el = $(key);
					pagesCls.winMoveShowHeader(null, header_el, true);
				}
			}
		}
	},
	winMoveHideHeader: function(event) {
		//if(!pagesCls.inMoveDrag)
		//	return false;
		var el = $(this.id);
		var _move_header = $("_move_header" + this.id);
		if(_move_header) {
			if( Position.within(_move_header, Event.pointerX(event), Event.pointerY(event), 2) ) {
				//_move_header内
				return;
			}
			//var event_el = Event.element(event);
			//var block_el = Element.getParentElementByClassName(event_el,"module_box");
			//if(block_el.id != this.id) {
			if( !Position.within( el, Event.pointerX(event), Event.pointerY(event), 2) ) {
				Element.addClassName(_move_header, "display-none");
				//Element.setStyle(_move_header, {"opacity":0.2});
				//setTimeout(function(){
				//	pagesCls.winMoveShowHeaderTimer(_move_header, 1.0, -0.4);
				//}, 200);

				pagesCls.inMoveShowHeader["_move_header" + this.id] = null;
				commonCls.moveVisibleHide(_move_header);
				Event.stop(event);
			}
		}
	},

	//-----------------------------------------------------------------------------
	// 移動
	//-----------------------------------------------------------------------------
	winMoveDragStart: function(event) {
		if(!pagesCls || pagesCls.inMoveDrag)
			return false;

		var el = this.parentNode;

		if(el.tagName == "BODY" || Element.hasClassName(el,"enlarged_display"))
			return false;

		//var title_el = Element.getChildElementByClassName(el,"nc_block_title");
		//if(title_el && Element.getChildElement(title_el) && Element.getChildElement(title_el).tagName == "INPUT") {
		if(pagesCls.inChgBlockName[this.id] == true ) {
			//名称変更中の移動はキャンセル
			return false;
		}

		//色解除
		//if(title_el) commonCls.blockNoticeEnd(null, title_el);

		//移動元カラムセット
		pagesCls.move_column_el = Element.getParentElementByClassName(el,"main_column")
		if(!pagesCls.move_column_el) {
			//グループのみ表示している場合
			pagesCls.move_column_el = Element.getChildElement(document.body);
			pagesCls.parentThreadNum = $("_grouping_thread_num").value;
			pagesCls.parentParentid = $("_grouping_parent_id").value;
			pagesCls.insert_tr = Element.getParentElement(Element.getChildElementByClassName(pagesCls.move_column_el,"column"));
		} else {
			pagesCls.insert_tr = Element.getChildElement(pagesCls.move_column_el,3);
			pagesCls.parentParentid = 0;
		}

		//移動元elセット
		pagesCls.move_el = el;
		pagesCls.move_td = Element.getParentElement(el);

		var paddingLeft = valueParseInt(pagesCls.move_el.style.paddingLeft);
		var paddingRight = valueParseInt(pagesCls.move_el.style.paddingRight);
		var paddingTop = valueParseInt(pagesCls.move_el.style.paddingTop);
		var paddingBottom = valueParseInt(pagesCls.move_el.style.paddingBottom);

		pagesCls.active_style = paddingTop + "px" + " " + paddingRight + "px" + " " + paddingBottom + "px" + " " + paddingLeft + "px";

		//DB登録用
		//var divList = commonCls.move_td.getElementsByTagName("div");
		var count = 1;
		var top_el = Element.getChildElement(pagesCls.move_el);
		var id_name = top_el.id;
		pagesCls.insertMoveRowBlockId = commonCls.getBlockid(top_el);

		for (var i = 0; i < pagesCls.move_td.childNodes.length; i++) {
			var div = pagesCls.move_td.childNodes[i];
			if(div && div.tagName == "DIV" && Element.hasClassName(div,"cell")) {
				if (Element.getChildElement(div).id  == id_name){
					pagesCls.insertMoveRowIndex = count;
					//break;
				}
				count++;
			}
		}

		////移動元情報取得
		pagesCls.insertMoveRowLength = count - 1;
		pagesCls.insertMoveCellIndex = commonCls.cellIndex(pagesCls.move_td) + 1;
		pagesCls.insertMoveCellLength = pagesCls.move_td.parentNode.cells.length;
		var parent_cell = Element.getParentElementByClassName(Element.getParentElement(el),"cell");
		if(parent_cell) {
			pagesCls.insertMoveRowParentId = commonCls.getBlockid(parent_cell);
		} else {
			pagesCls.insertMoveRowParentId = pagesCls.parentParentid;
		}

		pagesCls.insertMoveEndRowThreadNum = null;
		pagesCls.insertMoveEndRowParentId = null;

		// Get cursor offset from window block.
		pagesCls.xOffset = Event.pointerX(event) - Position.cumulativeOffset(pagesCls.move_el)[0];
		//var moveheader_el = $("_move_header" + this.id);
		//if(moveheader_el) var offset_header = moveheader_el.offsetHeight;
		//else var offset_header = 0;
		//pagesCls.yOffset = Event.pointerY(event) - Position.cumulativeOffset(pagesCls.move_el)[1] + offset_header;
		pagesCls.yOffset = Event.pointerY(event) - Position.cumulativeOffset(pagesCls.move_el)[1];

		//スタート位置
		pagesCls.start_x = Event.pointerX(event);
		pagesCls.start_y = Event.pointerY(event);

		// Set document to capture mousemove and mouseup events.

		pagesCls.winMoveDragGoEvent = pagesCls.winMoveDragGo.bindAsEventListener(el);
		pagesCls.winMoveDragStopEvent = pagesCls.winMoveDragStop.bindAsEventListener(el);
		Event.observe(document,"mousemove",pagesCls.winMoveDragGoEvent,true);
		Event.observe(document,"mouseup",pagesCls.winMoveDragStopEvent,true);
		Event.stop(event);

		////commonCls.insert_tr = null;
		pagesCls.inMoveDrag = true;
		pagesCls.insertAction = "";
	},
	//
	//移動挿入先検索
	//
	searchInsertBlock: function(cloneTopPos, x, y, now_thread_num, now_parent_id) {

		//this.cloneTopPos = new Object;
		//this.cloneColumnPos = new Object;
		//this.clonePos = new Object;

		//
		//挿入先検索
		//

		//pagesCls.insertMoveEndRowThreadNum = now_thread_num;		//移動先深さ
		pagesCls.insertMoveEndRowParentId = now_parent_id;		//移動先親ID
		var insert_el = null;
		if(cloneTopPos["el"].tagName == "TR")
			var insert_tr = cloneTopPos["el"];
		else
			var insert_tr = cloneTopPos["grouping_tr_el"];
		if( x >= cloneTopPos['left'] &&
            x <  cloneTopPos['right']) {
            for (var i = 0,col_len = pagesCls.cloneColumnPos[now_parent_id].length; i < col_len; i++) {
				//column
				//最も近いtdエレメントを取得しておく
				var el_left = pagesCls.cloneColumnPos[now_parent_id][i]["left"];
				var el_right = pagesCls.cloneColumnPos[now_parent_id][i]["right"];

				//if(Math.abs(el_right - x) < Math.abs(el_left - x)) {
				//	var sub_direction = "right";
				//	var el_offset_length = Math.abs(el_right - x);
				//} else {
				//	var sub_direction = "left";
				//	var el_offset_length = Math.abs(el_left - x);
				//}
				//if(offset_length == null || el_offset_length < offset_length) {
				//	var offset_length = el_offset_length;
				//	var index = commonCls.cellIndex(cloneColumnPos[i]["el"]);
				//	var direction = sub_direction;
				//}
				//within_x
				if(x >= el_left &&
       				 x <=  el_right) {
       				//移動列取得
					var insert_td = pagesCls.cloneColumnPos[now_parent_id][i]["el"];

					for (var j = 0,row_count = pagesCls.clonePos[now_parent_id][i].length; j < row_count; j++) {
						//elementセット
						if(firstdiv_el == null) {
							var firstdiv_el = pagesCls.clonePos[now_parent_id][i][j]['el'];
						}
						var enddiv_el = pagesCls.clonePos[now_parent_id][i][j]['el'];
						if(this.clonePos[now_parent_id][i][j]['top'] > y && position_el == null) {
							//y座標が越えた最初のエレメント取得
							var position_el = pagesCls.clonePos[now_parent_id][i][j]['el'];
						}
						if(y >= pagesCls.clonePos[now_parent_id][i][j]['top'] &&
   				 			y <=  pagesCls.clonePos[now_parent_id][i][j]['bottom']) {
   				 			//ブロックy座標内部にマウスがある
							insert_el = pagesCls.clonePos[now_parent_id][i][j]['el'];

   				 			if(pagesCls.clonePos[now_parent_id][i][j]['grouping_flag']) {
   				 				//var tdSubList = this.clonePos[now_parent_id][i][j]['el'].getElementsByTagName("td");
								//for (var k = 0; k < tdSubList.length; k++){
								//	if(Element.hasClassName(tdSubList[k],"column")) {
								//		var now_insert_tr = Element.getParentElement(tdSubList[k]);
								//		break;
								//	}
								//}
   				 				//グループ化ブロック
   				 				//if(now_insert_tr) {
   				 					//var grouping_top_el = Element.getParentElementByClassName(insert_el,"module_grouping_box");
   				 					var queryParams = commonCls.getParams(insert_el);
									next_parent_id = parseInt(queryParams["block_id"]);

									insert_el = pagesCls.searchInsertBlock(pagesCls.clonePos[now_parent_id][i][j], x,y,now_thread_num + 1,next_parent_id);
								//}
								break;
   				 			}

							var ex1 = pagesCls.clonePos[now_parent_id][i][j]['left'];
							var ex2 = pagesCls.clonePos[now_parent_id][i][j]['right'];
							var ey1= pagesCls.clonePos[now_parent_id][i][j]['top'];
							var ey2 = pagesCls.clonePos[now_parent_id][i][j]['bottom'];

							var direction = null;
							var offset = Math.ceil((ex2 - ex1)/4);	//左右(ex2 - ex1)/4 pxまで許容範囲(1/4)

							if(x > ex2 - offset) {
								direction = "right";
							} else if(x < ex1 + offset) {
								direction = "left";
							}else if(y > ey1 + (ey2 - ey1)/2) {
								direction = "bottom";
							} else {
								direction = "top";
							}
							/*
							if(y >= ((ey1 - ey2)/(ex1 -ex2))*x + (ey1 -ex1*((ey1 - ey2)/(ex1 -ex2)))) {
								if(y >= ((ey1 - ey2)/(ex2 -ex1))*x + (ey1 -ex2*((ey1 - ey2)/(ex2 -ex1)))) {
									direction = "bottom";
								} else {
									direction = "left";
								}
							} else {
								if(y < ((ey1 - ey2)/(ex2 -ex1))*x + (ey1 -ex2*((ey1 - ey2)/(ex2 -ex1)))) {
									direction = "top";
								} else {
									direction = "right";
								}
							}
							*/
							var index = commonCls.cellIndex(insert_td);
							switch (direction) {
								case "left":
									//insert_tdの左に新列追加
									//if(index != 0)
									//	index = index - 1;
									InsertCell(index,insert_tr);
									//debug.p("insert_tdの左に新列追加");
									break;
								case "right":
									//insert_tdの右に新列追加
									index = index + 1;
									InsertCell(index,insert_tr);
									//debug.p("insert_tdの右に新列追加");
									break;
								case "top":
									//insert_tdの上に新DIV追加
									InsertBeforeEl(insert_el);
									//debug.p("insert_tdの上に新DIV追加");
									break;
								case "bottom":
									//insert_tdの下に新DIV追加
									InsertAfterEl(insert_el);
									//debug.p("insert_tdの下に新DIV追加");
									break;
							}
							break;
						}
					}

					if(insert_el == undefined || insert_el == null) {
						//既存列のブロックとブロックの間、また、ブロック上あるいは下にある
						//その位置に挿入
						//insert_tdの下に新DIV追加
						if(position_el != null){
							insert_el = position_el;
							InsertBeforeEl(insert_el);
						} else {
							//insert_tdの下に挿入
							insert_el = enddiv_el;
							if(enddiv_el) InsertAfterEl(enddiv_el);
						}
					}
					break;
				}
			}
		}
        if(insert_el == null) {
			if(x < cloneTopPos['left']) {
				//左に新列追加
				InsertCell(0,cloneTopPos["el"]);
				//debug.p("左に追加");
			} else {
				//右に新列追加
				var index = insert_tr.cells.length;
				InsertCell(index,insert_tr);
				//debug.p("右に追加");
			}
			var tdList = insert_tr.getElementsByTagName("td");
			for (var i = 0,tdLen = tdList.length; i < tdLen; i++){
				if(Element.hasClassName(tdList[i],"column") && (tdList[i].innerHTML.trim()) == "") {
					Element.remove(tdList[i]);
				}
			}
		}
		return pagesCls.insert_el;

		//新規行追加
		function InsertBeforeEl(el){
			var div = document.createElement("DIV");
			div.className = "cell";
			div.style.padding = pagesCls.active_style;
			pagesCls.insert_td = el.parentNode;
			pagesCls.insert_el = el.parentNode.insertBefore(div, el);

			delMoveEl();

			//DB登録用
			pagesCls.insertAction = "insertrow";
		}
		function InsertAfterEl(el){
			var div = document.createElement("DIV");
			div.className = "cell";
			div.style.padding = pagesCls.active_style;
			pagesCls.insert_td = el.parentNode;
			pagesCls.insert_el = el.parentNode.insertBefore(div, el.nextSibling);

			delMoveEl();

			//DB登録用
			pagesCls.insertAction = "insertrow";

		}
		//新規列追加
		function InsertCell(Index,insert_tr){
			Index = delMoveEl(Index);
			pagesCls.insert_td = insert_tr.insertCell(Index);
			pagesCls.insert_td.className = "column valign-top";
			var div = document.createElement("DIV");
			div.className = "cell";

			div.style.padding = pagesCls.active_style;
			pagesCls.insert_td.appendChild(div);
			pagesCls.insert_el = div;

			//DB登録用
			pagesCls.insertAction = "insertcell";
		}
		//移動元要素削除
		function delMoveEl(Index) {
			Index = (Index == undefined) ? 0 : Index;
			if(pagesCls.move_el != null && pagesCls.move_el != undefined) {
				if (Element.hasClassName(Element.getChildElement(pagesCls.move_el),"column_movedummy")){
					Element.remove(pagesCls.move_el);
				}
				//Element.remove(pagesCls.move_el);
				pagesCls.move_el = null;
				//もし、その列になにもなければ列削除
				//但し、cellsが１の場合は削除しない(TODO:要テスト、後に修正かも)
				var divList = pagesCls.move_td.getElementsByTagName("div");

				if(divList.length == 0 && pagesCls.move_td.parentNode.cells.length > 1){
				//if(divList.length == 0 && pagesCls.move_td.parentNode.cells.length >= 1){
					if(Index > commonCls.cellIndex(pagesCls.move_td)) {
						//削除する列が挿入列より小さければIndex--
						Index = Index - 1;
					}
					Element.remove(pagesCls.move_td);
					pagesCls.move_td = null;
				}
			}
			return Index;
		}
	},


	//
	//移動挿入先取得
	//
	getSearchBlock: function(top_td_el,insert_tr,now_thread_num,now_parent_id) {
		//
		//挿入先取得
		//

		if(!pagesCls.cloneTopPos["el"]) {
			var offset = Position.cumulativeOffset(top_td_el);
			pagesCls.cloneTopPos["el"] = insert_tr;
			pagesCls.cloneTopPos["top"] = offset[1];
			pagesCls.cloneTopPos["right"] = offset[0] + top_td_el.offsetWidth;
			pagesCls.cloneTopPos["bottom"] = offset[1] + top_td_el.offsetHeight;
			pagesCls.cloneTopPos["left"] = offset[0];
		}
		pagesCls.cloneColumnPos[now_parent_id] = Array();
		pagesCls.clonePos[now_parent_id] = Array();

		for (var i = 0,col_len = insert_tr.childNodes.length; i < col_len; i++) {
			var column_el = insert_tr.childNodes[i];
			pagesCls.cloneColumnPos[now_parent_id][i] = new Object();

			var offset = Position.cumulativeOffset(column_el);

			pagesCls.cloneColumnPos[now_parent_id][i]["el"] = column_el;
			pagesCls.cloneColumnPos[now_parent_id][i]["top"] = offset[1];
			pagesCls.cloneColumnPos[now_parent_id][i]["right"] = offset[0] + column_el.offsetWidth;
			pagesCls.cloneColumnPos[now_parent_id][i]["bottom"] = offset[1] + column_el.offsetHeight;
			pagesCls.cloneColumnPos[now_parent_id][i]["left"] = offset[0];
			pagesCls.clonePos[now_parent_id][i] = Array();
			for (var j = 0,row_len = column_el.childNodes.length; j < row_len; j++) {
				var row_el = column_el.childNodes[j];
				var child_el = Element.getChildElement(row_el);
				pagesCls.clonePos[now_parent_id][i][j] = new Object();
				pagesCls.clonePos[now_parent_id][i][j]["grouping_flag"] = false;
				if(Element.hasClassName(child_el, "module_grouping_box")) {
					//Groupingブロック
					var queryParams = commonCls.getParams(row_el);
					next_parent_id = parseInt(queryParams["block_id"]);
					var tdSubList = child_el.getElementsByTagName("td");
					for (var k = 0,tdSubListLen = tdSubList.length; k < tdSubListLen; k++){
						if(Element.hasClassName(tdSubList[k],"column")) {
							var now_insert_tr = Element.getParentElement(tdSubList[k]);
							break;
						}
					}
					pagesCls.clonePos[now_parent_id][i][j]["grouping_flag"] = true;

					pagesCls.clonePos[now_parent_id][i][j]["grouping_tr_el"] = now_insert_tr;

					if(now_insert_tr) pagesCls.getSearchBlock(child_el, now_insert_tr, now_thread_num + 1,next_parent_id);
				}
				var offset = Position.cumulativeOffset(child_el);
				pagesCls.clonePos[now_parent_id][i][j]["el"] = row_el;
				pagesCls.clonePos[now_parent_id][i][j]["top"] = offset[1];
				pagesCls.clonePos[now_parent_id][i][j]["right"] = offset[0] + child_el.offsetWidth;
				pagesCls.clonePos[now_parent_id][i][j]["bottom"] = offset[1] + child_el.offsetHeight;
				pagesCls.clonePos[now_parent_id][i][j]["left"] = offset[0];
			}

		}
	},


	winMoveDragGo: function(event) {
		if(!pagesCls.inMoveDrag)
			return false;

		pagesCls.insert_td = null;
		pagesCls.insert_el = null;

		var x = Event.pointerX(event);
		var y = Event.pointerY(event);
		var width = this.offsetWidth;
		var height = this.offsetHeight;

		// もし、ドラッグスタート位置から、離れていない場合、ドラッグしていないものとみなす
		// 5px以内に設定
		var def_px = 5;
		if(x <= pagesCls.start_x + def_px && x >= pagesCls.start_x - def_px &&
			y <= pagesCls.start_y + def_px && y >= pagesCls.start_y - def_px) {
			return true;
		}

		//40pxづつ移動
		commonCls.scrollMoveDrag(event);

		//コピー
		if(pagesCls.active == null || pagesCls.active == undefined) {
			//pagesCls.active = true;

			//選択されていたら、キャンセルする
			var divList = document.getElementsByTagName("div");
			for (var i = 0; i < divList.length; i++){
				if (Element.hasClassName(divList[i],"cell")){
					pagesCls.cancelSelectStyle(divList[i]);
				}
			}
			pagesCls.active = pagesCls.winCreateCopy(this,width,height);

			//var el_child = commonCls.active.childNodes[0] || commonCls.active.childNodes[1];

			//commonCls.act_width = el_child.offsetWidth;
			//commonCls.act_height = el_child.offsetHeight;

			//移動用ヘッダー非表示
			for (var key in pagesCls.inMoveShowHeader) {
				if(pagesCls.inMoveShowHeader[key]) {
					var header_el = $("_move_header" + key);
					Element.addClassName(header_el, "display-none");
					pagesCls.inMoveShowHeader[key] = false;
				}
			}
			if (Element.hasClassName(pagesCls.move_column_el,"main_column")){
				pagesCls.getSearchBlock(pagesCls.move_column_el, pagesCls.insert_tr,0,0);
			} else {
				//グループ化したブロック内のブロック移動
				pagesCls.getSearchBlock(pagesCls.move_column_el, pagesCls.insert_tr,pagesCls.parentThreadNum,pagesCls.parentParentid);
			}
		}

		//
		//挿入先検索
		//
		if (Element.hasClassName(pagesCls.move_column_el,"main_column")){
			pagesCls.searchInsertBlock(pagesCls.cloneTopPos, x, y, 0, 0);
		} else {
			pagesCls.searchInsertBlock(pagesCls.cloneTopPos, x, y, pagesCls.parentThreadNum, pagesCls.parentParentid);
		}

		if(pagesCls.insert_td && pagesCls.insert_el) {
			pagesCls.move_td = pagesCls.insert_td;
			pagesCls.move_el = pagesCls.insert_el;
		}
		//if(pagesCls.active == true)
		//	return false;

		//commonCls.active.style.visibility = "";

		// Get cursor position.

		Event.stop(event);

		var paddingLeft = valueParseInt(this.style.paddingLeft);
		var paddingTop = valueParseInt(this.style.paddingTop);

		// Move window block based on offset from cursor.
		if(Event.pointerX(event) - pagesCls.xOffset + paddingLeft <= 0){
			pagesCls.active.style.left = -paddingLeft + "px";
		}else{
			pagesCls.active.style.left = (x - pagesCls.xOffset) + "px";
		}

		if(Event.pointerY(event) - pagesCls.yOffset + paddingTop <= 0){
			pagesCls.active.style.top  = -paddingTop + "px";
		}else{
			pagesCls.active.style.top  = (y - pagesCls.yOffset) + "px";
		}

		//移動元エレメント変更
		//if(pagesCls.move_el && Element.getChildElement(pagesCls.move_el) == undefined) {
		if(pagesCls.move_el && (Element.getChildElement(pagesCls.move_el) == null || Element.getChildElement(pagesCls.move_el).innerHTML == "")) {

			var div = document.createElement("DIV");
			var div_child = document.createElement("DIV");

			Element.addClassName(div,"column_movedummy");
			div_child.style.width = pagesCls.active.offsetWidth - valueParseInt(pagesCls.move_el.style.paddingLeft) - valueParseInt(pagesCls.move_el.style.paddingRight) + "px";
			div_child.style.height = pagesCls.active.offsetHeight - valueParseInt(pagesCls.move_el.style.paddingTop) - valueParseInt(pagesCls.move_el.style.paddingBottom) + "px";
			//div.style.paddingTop = valueParseInt(pagesCls.move_el.style.paddingTop) + "px";
			//div.style.paddingRight = valueParseInt(pagesCls.move_el.style.paddingRight) + "px";
			//div.style.paddingBottom = valueParseInt(pagesCls.move_el.style.paddingBottom) + "px";
			//div.style.paddingLeft = valueParseInt(pagesCls.move_el.style.paddingLeft) + "px";
			div.appendChild(div_child);
			pagesCls.move_el.appendChild(div);


			//div.className = "column_movedummy";
			//pagesCls.move_el.appendChild(div);
//			var div = Element.getChildElement(pagesCls.move_el);
//debug.p(pagesCls.move_el.innerHTML);
//			Element.addClassName(div,"column_movedummy");

			////var marginLeft = valueParseInt(commonCls.active.style.marginLeft);
			////var marginRight = valueParseInt(commonCls.active.style.marginRight)
			////var marginTop = valueParseInt(commonCls.active.style.marginTop)
			////var marginBottom = valueParseInt(commonCls.active.style.marginBottom)
			////commonCls.move_el.childNodes[0].style.margin = el_child.style.padding;

			//////commonCls.move_el.childNodes[0].style.margin = marginTop + "px " + marginRight + "px " + marginBottom + "px " + marginLeft + "px";

			//Netscape7.1の場合、テーブルリサイズされないので
			//refresh
			if(browser.isNS){
				pagesCls.move_column_el.style.display = "none";
				pagesCls.move_column_el.style.display = "";
				//pagesCls.column_table.style.display = "none";
				//pagesCls.column_table.style.display = "";
			}
		}
		//commonCls.moveVisibleHide(pagesCls.active);
	},
	winMoveDragStop: function(event) {
		//コピーエレメント削除
		if(pagesCls.active != null && pagesCls.active != undefined) {
			var _move_header = $("_move_header" + Element.getChildElement(pagesCls.active, 2).id);
			//var _move_header = Element.getChildElement(pagesCls.active);
			if(Element.hasClassName(_move_header, "_move_header")) {
				_move_header.style.display = "";
				//Element.remove(_move_header);
			}
			//破線ボックス削除
			Element.remove(Element.getChildElement(pagesCls.move_el));
			//Element.removeClassName(pagesCls.move_el,"column_movedummy");
			//pagesCls.active.disabled ='false';
			pagesCls.move_el.appendChild(Element.getChildElement(pagesCls.active,2));

			////pagesCls.move_el.style.padding = pagesCls.active_style;
			//commonCls.moveVisibleHide(pagesCls.active);

			//選択解除
			pagesCls.cancelSelectStyle(pagesCls.move_el);
			Element.remove(pagesCls.active);
			delete pagesCls.active;
			pagesCls.active = null;
			//pagesCls.active_style = null;
		}

		Event.stopObserving(document,"mousemove",pagesCls.winMoveDragGoEvent,true);
		Event.stopObserving(document,"mouseup",pagesCls.winMoveDragStopEvent,true);
		pagesCls.winMoveDragGoEvent = null;
		pagesCls.winMoveDragStopEvent = null;
		pagesCls.cloneTopPos = new Object();
		pagesCls.cloneColumnPos = Array();
		pagesCls.clonePos = Array();
		Event.stop(event);

		//
		// DB登録処理
		//
		if(pagesCls.insertMoveEndRowParentId != null) {
			var id_name = Element.getChildElement(pagesCls.move_el).id;
			//if(Element.hasClassName(commonCls.block_column[id_name],"leftcolumn")) {
			//	var display_position = 0;
			//} else if(Element.hasClassName(commonCls.block_column[id_name],"centercolumn")) {
			//	var display_position = 1;
			//} else if(Element.hasClassName(commonCls.block_column[id_name],"rightcolumn")) {
			//	var display_position = 2;
			//} else
			//	var display_position = 0;

			//if(commonCls.insertMoveRowParams == null)
			//	commonCls.insertMoveRowParams = "";
			//var new_id_name = commonCls.insertMoveRowAction + "&block_id=" + commonCls.insertMoveRowBlockId + "&thread_num=" + commonCls.insertMoveEndRowThreadNum + "&parent_id=" +valueParseInt(commonCls.insertMoveEndRowParentId) + commonCls.insertMoveRowParams;
			//commonCls.block_el[new_id_name] = commonCls.block_el[id_name];
			//commonCls.block_column[new_id_name] = commonCls.block_column[id_name];
			//if(id_name != new_id_name) {
			//	//グローバル配列登録
			//	delete commonCls.block_el[id_name];
			//	delete commonCls.block_column[id_name];
			//	commonCls.block_el[id_name] = null;
			//	commonCls.block_column[id_name] = null;
			//	id_name = new_id_name;
			//}
			//Element.getChildElement(commonCls.move_el,4).id = new_id_name;

			//初期化
			var insertCellIndex = commonCls.cellIndex(pagesCls.move_td) + 1;
			var insertRowIndex = 1;
			var insertRowLength = 1;
			if(pagesCls.insertAction == "insertrow") {
				var count = 1;
				for (var i = 0; i < pagesCls.move_td.childNodes.length; i++) {
					var div = pagesCls.move_td.childNodes[i];
					if(div.tagName == "DIV" && Element.hasClassName(div,"cell")) {
						if (Element.getChildElement(div).id  == id_name){
							insertRowIndex = count;
							//break;
						}
						count++;
					}
				}
				insertRowLength = count - 1;
				/***後に削除
				var divList = pagesCls.move_td.getElementsByTagName("div");
				var count = 1;
				for (var i = 0; i < divList.length; i++){
					if (Element.hasClassName(divList[i],"cell")){
						//thread_numが等しいものだけカウント
						var el = Element.getChildElement(divList[i],4);
						var queryParams = el.id.parseQuery();
						if(commonCls.insertMoveEndRowThreadNum == queryParams["thread_num"]) {
							if (el && el.id == id_name){
								insertRowIndex = count;
							}
							count++;
						}
					}
				}
				insertRowLength = count - 1;
				*****/
				if(insertRowLength == 1)
					pagesCls.insertAction = "insertcell";
			}

			if(pagesCls.insertAction == "" || pagesCls.insertMoveCellIndex == insertCellIndex &&
				pagesCls.insertMoveRowIndex == insertRowIndex &&
				pagesCls.insertMoveRowLength == insertRowLength &&
				pagesCls.insertMoveRowParentId == pagesCls.insertMoveEndRowParentId) {
				//変更なし
				//処理しない
			} else {
				//移動する場合に空のグルーピングボックス削除用
				if(pagesCls.insertMoveRowLength == 1 && pagesCls.insertMoveCellLength == 1 && pagesCls.insertMoveRowParentId != 0) {
					pagesCls.delEmptyBlock(pagesCls.insertMoveRowParentId);
				}

				var queryParams = commonCls.getParams(pagesCls.move_el);
				var page_id = queryParams["page_id"];

				//DB登録
				var postBody = "pages_action_" + pagesCls.insertAction +
									"&block_id=" + pagesCls.insertMoveRowBlockId +
									"&col_num=" + insertCellIndex + "&row_num=" + insertRowIndex + "&row_len=" + insertRowLength +
									"&parent_id=" + pagesCls.insertMoveEndRowParentId +
									"&pre_col_num=" + pagesCls.insertMoveCellIndex + "&pre_row_num=" + pagesCls.insertMoveRowIndex + "&pre_row_len=" + pagesCls.insertMoveRowLength +
									"&pre_parent_id=" + pagesCls.insertMoveRowParentId +
									"&_show_count=" + pagesCls.show_count[page_id];
				var params = new Object();

				params["method"] = "post";
				params["param"] = postBody;
				params["top_el"] = pagesCls.move_el;
				//params["loading_el"] = parent_el;
				params["callbackfunc_error"] = function(){location.reload();};
				params["token"] = pagesCls.pages_token[page_id];
				commonCls.send(params);

				//表示回数カウント++
				pagesCls.show_count[page_id]++;
			}
		}
		//移動用ヘッダー移動
		//pagesCls.winMoveResizeHeader();

		pagesCls.inMoveDrag = false;
	},
	/*空のブロックならば削除*/
	delEmptyBlock: function(del_parent_id) {
		//parent_idから親を求める
		//var divList = commonCls.column_table.getElementsByTagName("div");
		var divList = pagesCls.move_column_el.getElementsByTagName("div");
		for (var i = 0; i < divList.length; i++){
			if (Element.hasClassName(divList[i],"cell")){
				var id_name = Element.getChildElement(divList[i]).id;
				var block_id = id_name.substr(1, id_name.length);
				if(block_id == del_parent_id) {
					//再帰的に回すためデータ取得
					var tr_el = Element.getParentElement(divList[i],2);
					var td_el = divList[i].parentNode;
					var parent_cell = Element.getParentElementByClassName(Element.getParentElement(divList[i]),"cell");
					if(parent_cell) {
						var parent_id_name = Element.getChildElement(parent_cell).id;
						var parent_id = parent_id_name.substr(1, parent_id_name.length);
					} else {
						var parent_id = 0;
					}
					//親ブロック削除
					Element.remove(divList[i]);

					//もし、その列になにもなくなれば、列削除
					var count = 0;
					for (var i = 0; i < td_el.childNodes.length; i++) {
						var div = td_el.childNodes[i];
						if(div && div.tagName == "DIV" && Element.hasClassName(div,"cell")) {
							count++;
						}
					}
					if(count == 0)
						Element.remove(td_el);

					//親を再帰的にまわす
					if(tr_el.cells.length == 0 && parent_id != 0) {
						pagesCls.delEmptyBlock(parent_id);
					}
				}

			}
		}
	},
	/*移動DB登録処理完了*/
	pageMoveComplete: function(transport) {
		var res = transport.responseText;

		if(res != "" && res != null) {
			res = res.replace(/\\n/ig,"\n");
			alert(res);

			//
			//再描画処理へ
			//
			window.location.reload();
		}
	},
	winCreateCopy: function(el,width,height) {

		//tableまで親を求める
		//el = Element.getParentElement(el,3);

		var Block = document.createElement("div");

		var top_el_id = Element.getChildElement(el).id;
		var _move_header = $("_move_header" + top_el_id);

		Block.appendChild(el);

		//var headerWidth = 0;
		//var headerHight = 0;
		if(_move_header && !Element.hasClassName(_move_header, "display-none")) {
			//if(!Element.hasClassName(_move_header, "display-none")) {
				//Block.innerHTML = _move_header.innerHTML;

				//var header_el = Element.getChildElement(_move_header);
				_move_header.style.position = "absolute";
				var paddingLeft = valueParseInt(el.style.paddingLeft);
				var paddingTop = valueParseInt(el.style.paddingTop);

				_move_header.style.top = paddingTop + "px";
				_move_header.style.left = paddingLeft + "px";
				//debug.p(_move_header.className);
				Element.removeClassName(_move_header, "display-none");
				_move_header.style.display = "block";
				//Block.style.zIndex = commonCls.max_zIndex + 10;
				//debug.p(_move_header.style.left);
				//Element.removeClassName(_move_header, "display-none");
				//Element.addClassName(_move_header, "display-none");
				//headerWidth = _move_header.offsetWidth;
				//headerHight = _move_header.offsetHeight;
			//}
		}

		////if (browser.isNS && !browser.isFirefox){
		//	//Netscape7.1の場合、コピー先のタグにinput type="file"があると
		//	//なぜか問題が発生するため、除去
		//	//var inputList = Block.getElementsByTagName("input");
		//	//for (var i = 0; i < inputList.length; i++){
		//	//	if (inputList[i].type == "file"){
		//	//		inputList[i].type = "text";
		//	//	}
		//	//}
		////}
		Block.style.position = "absolute";
		Block.style.zIndex = commonCls.max_zIndex + 1;
		////Block.style.backgroundColor = "#FFFFFF";
		////Block.childNodes[0].style.overflow = "hidden";

		////重くなるため透過しない
		///*if (browser.isIE || browser.isOpera){
		//	//Opera未対応
		//	Block.style.filter = "Alpha(opacity=" + 50 + ")";
		//}else{
		//	Block.style.MozOpacity = 50/100;
		//}*/
		//////Block.style.visibility = "hidden";
		//Block.disabled ='true';	//IEのみの上、inputタグの色が戻らなくなるためコメント

		//幅、高さ設定
		Block.style.width = width + "px";
		Block.style.height = height + "px";
		//Block.style.width = width + headerWidth + "px";
		//Block.style.height = height + headerHight + "px";

		document.body.appendChild(Block);

		return Block;
	},
	//-----------------------------------
	// グルーピング関数
	//-----------------------------------
	onGroupingEvent: function(event) {
		if(_nc_layoutmode != "on") return;
		var cell_el = Element.getParentElementByClassName(Event.element(event),"cell");
		if(!cell_el)
			return false;
		//var color_num = this.getColorNum(cell_el);
		if(!pagesCls.cancelSelectStyle(cell_el)) {
			pagesCls.setSelectStyle(cell_el);
			pagesCls.onGroupingCheck(cell_el);
		}
	},
	onGroupingCheck : function(cell_el) {
		var main_column_el = Element.getParentElementByClassName(cell_el,"main_column");
		var grouping_flag = false;
		if(!main_column_el) {
			main_column_el = Element.getChildElement(document.body);
			grouping_flag = true;
		}
		//同じ深さ、同じmain_columnのものをグループ化
		//var divList = main_column_el.getElementsByTagName("div");
		var parent_id = this.getParentid(cell_el);
		var divList = document.getElementsByTagName("div");
		this.groupingList = Array();
		var groupingBlockidList = Array();
		var col_num = -1;
		var row_num = 0;
		var count = 0;
		var cell_index = null;
		var pre_cell_index = null;
		for (var i = 0; i < divList.length; i++){
			if (Element.hasClassName(divList[i],"cell")){
				var now_parent_id = this.getParentid(divList[i]);
				if(pagesCls.checkSelectStyle(divList[i])) {
					if(now_parent_id != parent_id) {
						//同じ深さではない
						pagesCls.cancelSelectStyle(divList[i]);
					} else if(grouping_flag == false && main_column_el != Element.getParentElementByClassName(divList[i],"main_column")) {
						//同じカラムではない
						pagesCls.cancelSelectStyle(divList[i]);
					} else {
						cell_index = commonCls.cellIndex(divList[i].parentNode);
						if(pre_cell_index == null || cell_index != pre_cell_index) {
							pre_cell_index = cell_index;
							col_num++;
							row_num = 0;
							this.groupingList[col_num] = Array();
							groupingBlockidList[col_num] =  Array();
						}
						queryParams = commonCls.getParams(divList[i]);
						var block_id = queryParams["block_id"];
						groupingBlockidList[col_num][row_num] = block_id;
						this.groupingList[col_num][row_num] = divList[i];
						row_num++;
						//削除Element取得:行削除用
						//this.delGroupingList[count] = divList[i];
						count++;
					}
				}
			}
		}
		return groupingBlockidList;
	},
	//id内のthread_numから選択枠の色番号(1-4)を取得
	//getColorNum: function(cell_el) {
	//	//var queryParams = id_name.parseQuery();
	//	//var thread_num = queryParams["thread_num"];
	//	var thread_num = this.getThreadnum(cell_el);
	//
	//	//4色のローテーション
	//	var color_num = (valueParseInt(thread_num)+5)%4;
	//	if(color_num == 0)
	//		color_num = 4;
	//	return color_num;
	//},
	//ボックス選択時のスタイル指定
	setSelectStyle: function(el) {
		//var id_name = Element.getChildElement(el).id;
		//var color_num = this.getColorNum(el);
		var main_column = Element.getParentElementByClassName(el,"main_column");
		var grouping_flag = false;
		if(!main_column) {
			main_column = Element.getChildElement(document.body);
			grouping_flag = true;
		}

		if(main_column.id =="__leftcolumn") {
			Element.addClassName(el, "select_leftcolumn");
		} else if(main_column.id =="__centercolumn") {
			Element.addClassName(el, "select_centercolumn");
		} else if(main_column.id =="__rightcolumn") {
			Element.addClassName(el, "select_rightcolumn");
		} else if(main_column.id =="__headercolumn") {
			Element.addClassName(el, "select_headercolumn");
		} else if(grouping_flag) {
			Element.addClassName(el, "select_centercolumn");
		} else {
			return false;
		}
		//Element.getChildElement(el,4).style.padding = "1px";
		//Element.addClassName(Element.getChildElement(el,1), "collapse_separate");
		return true;
	},
	//ボックス選択時のスタイル指定解除
	cancelSelectStyle: function(el) {
		var style_name = this.checkSelectStyle(el);
		if(!style_name) {
			return false;
		}
		Element.removeClassName(el, style_name);
		//Element.removeClassName(Element.getChildElement(el,1), "collapse_separate");
		return true;
	},
	checkSelectStyle: function(el) {
		//var color_num = this.getColorNum(el);
		if(Element.hasClassName(el,"select_leftcolumn")) {
			var style_name = "select_leftcolumn";
		} else if(Element.hasClassName(el,"select_centercolumn")) {
			var style_name = "select_centercolumn";
		} else if(Element.hasClassName(el,"select_rightcolumn")) {
			var style_name = "select_rightcolumn";
		} else if(Element.hasClassName(el,"select_headercolumn")) {
			var style_name = "select_headercolumn";
		} else {
			return false;
		}
		return style_name;

	},
	getThreadnum: function(cell_el, thread_num) {
		thread_num = (thread_num == undefined) ? pagesCls.parentThreadNum : thread_num;

		var parent_cell = Element.getParentElementByClassName(Element.getParentElement(cell_el),"cell");
		if(parent_cell) {
			//再帰処理
			thread_num++;
			thread_num = this.getThreadnum(parent_cell,thread_num);
		}
		return thread_num;
	},
	getParentid: function(cell_el) {
		var parent_cell = Element.getParentElementByClassName(Element.getParentElement(cell_el),"cell");
		if(parent_cell) {
			var queryParams = commonCls.getParams(parent_cell);
			return queryParams["block_id"];
		}
		return 0;
	},
	/*グルーピング処理*/
	addGrouping: function(event, confirm_mes , confirm_error_mes) {
		//var target_el;
		//if (id == undefined) {
		//	target_el = Event.element(event);
		//} else {
		//	target_el = $(id);
		//}

		//var cell_el = Element.getParentElementByClassName(target_el,"cell");

		//var queryParams = commonCls.getParams(cell_el);
		//var ins_block_id = queryParams["block_id"];
		//var page_id = queryParams["page_id"];

		//this.setSelectStyle(cell_el);

		var divList = document.getElementsByTagName("div");
		var cell_el = null;
		for (var i = 0; i < divList.length; i++){
			if (Element.hasClassName(divList[i],"cell")){
				if(pagesCls.checkSelectStyle(divList[i])) {
					cell_el = divList[i];
					var queryParams = commonCls.getParams(cell_el);
					var ins_block_id = queryParams["block_id"];
					var page_id = queryParams["page_id"];
					break;
				}
			}
		}
		if(cell_el == null) {
			commonCls.alert(confirm_error_mes);
			return;
		}

		if (confirm_mes == undefined || !commonCls.confirm(confirm_mes)) return false;


		var groupingBlockidList = this.onGroupingCheck(cell_el);

		//選択解除
		for(var i = 0; i < this.groupingList.length; i++) {
			for(var j = 0; j < this.groupingList[i].length; j++) {
				pagesCls.cancelSelectStyle(this.groupingList[i][j]);
			}
		}

		//
		//グルーピング
		//
		var postBody = "pages_action_grouping&block_id=" + ins_block_id +
						"&_show_count=" + pagesCls.show_count[page_id];
		//グループ化するブロックIDの列を1,2,3:4,5:6,7,8というように記述
		//上記の例だと、1列目 block_id:1,2,3 2列目 block_id:4,5 3列目 block_id 6,7,8というようになる
		postBody = postBody + "&_grouping_list=";
		for(var i = 0; i < groupingBlockidList.length; i++) {
			for(var j = 0; j < groupingBlockidList[i].length; j++) {
				postBody = postBody + groupingBlockidList[i][j];
				if(j != groupingBlockidList[i].length - 1)
					postBody = postBody + ",";
			}
			if(i != groupingBlockidList.length - 1)
				postBody = postBody + ":";
		}
		//退避
		var params_grouping = new Object();
		var div = document.createElement("DIV");
		//commonCls.displayNone(div);
		div.className = "cell";
		var paddingLeft = valueParseInt(cell_el.style.paddingLeft);
		var paddingRight = valueParseInt(cell_el.style.paddingRight);
		var paddingTop = valueParseInt(cell_el.style.paddingTop);
		var paddingBottom = valueParseInt(cell_el.style.paddingBottom);
		div.style.padding = paddingTop + "px" + " " + paddingRight + "px" + " " + paddingBottom + "px" + " " + paddingLeft + "px";
		var temp_html = cell_el.innerHTML;
		div.appendChild(Element.getChildElement(cell_el));
		cell_el.innerHTML = temp_html;

		pagesCls.active = div;

		params_grouping["method"] = "post";
		params_grouping["param"] = postBody;
		params_grouping["loading_el"] = Element.getChildElement(cell_el);
		//params_grouping["target_el"] = cell_el.parentNode;
		params_grouping["target_el"] = cell_el;
		params_grouping["top_el"] = cell_el;
		params_grouping["callbackfunc"] = function(cell_el){
			this.addGroupingComp(cell_el);
		}.bind(this);
		params_grouping["func_param"] = cell_el;
		params_grouping["callbackfunc_error"] = function(res){
			pagesCls.active = null; location.reload();
		};
		params_grouping["token"] = pagesCls.pages_token[page_id];
		commonCls.send(params_grouping);
		//表示回数カウント++
		pagesCls.show_count[page_id]++;

		return false;
	},
	/*グルーピング完了処理*/
	addGroupingComp: function(cell_el) {
		var main_column_el = Element.getParentElementByClassName(cell_el,"main_column");
		if(!main_column_el) {
			main_column_el = Element.getChildElement(document.body);
		}
		var insert_column_el = Element.getChildElementByClassName(cell_el,"column");
		var insert_tr = Element.getParentElement(insert_column_el);
		for(var i = 0; i < this.groupingList.length; i++) {
			for(var j = 0; j < this.groupingList[i].length; j++) {
				if(this.groupingList[i][j] == cell_el) {
					var append_el = pagesCls.active;
				} else {
					var append_el = this.groupingList[i][j];
				}
				if(i==0) {
					//１列目
					insert_column_el.appendChild(append_el);
				} else {
					if(i > insert_tr.cells.length - 1) {
						insert_column_el = insert_tr.insertCell(i);
						insert_column_el.className = "column valign-top";
					}
					insert_column_el.appendChild(append_el);
				}
			}
		}
		pagesCls.active = null;

		//列削除処理
		var tdList = main_column_el.getElementsByTagName("td");
		for (var i = 0; i < tdList.length; i++){
			if (Element.hasClassName(tdList[i],"column")){
				var count = 0;
				for (var j = 0; j < tdList[i].childNodes.length; j++) {
					var div = tdList[i].childNodes[j];
					if(div && div.tagName == "DIV" && Element.hasClassName(div,"cell")) {
						count++;
					}
				}
				if(count == 0) {
					//列削除
					Element.remove(tdList[i]);
				}
			}
		}



		//mozzila系の場合、スクロールホイールが効かなくなるため
		//フォーカスをいったん移す(NSの場合、効かないまま･･･）
		//if(browser.isNS) {
		//	window.blur();
		//	window.focus();
		//}
		return true;
	},
	/* グループ解除 */
	cancelGrouping: function(event, confirm_mes , confirm_error_mes) {
		//グループ化している選択cellを検索
		var divList = document.getElementsByTagName("div");
		confirm_flag = false;
		for (var i = 0; i < divList.length; i++){
			if (Element.hasClassName(divList[i],"cell")){
				if(pagesCls.checkSelectStyle(divList[i])) {
					var queryParams = commonCls.getParams(divList[i]);
					if(queryParams["action"] == "pages_view_grouping" || queryParams["action"] == "pages_action_grouping") {
						//グループ化しているブロック
						if(confirm_flag == false) {
							if (confirm_mes == undefined || !commonCls.confirm(confirm_mes)) return false;
							confirm_flag = true;
						}
						var cell_el = divList[i];
						this.cancelGroupingDetail(cell_el);
					} else {
						pagesCls.cancelSelectStyle(divList[i]);
					}
				}
			}
		}
		if(confirm_flag == false) {
			commonCls.alert(confirm_error_mes);
		}
	},
	cancelGroupingDetail: function(cell_el) {
		if(this.inCancelGroupingDrag == true ) {
			setTimeout(this.cancelGroupingDetail(cell_el), 100);
		}
		this.inCancelGroupingDrag = true;
		var queryParams = commonCls.getParams(cell_el);
		var ins_block_id = queryParams["block_id"];
		var page_id = queryParams["page_id"];

		//
		//グルーピング解除
		//
		var postBody = "pages_action_cancelgrouping&block_id=" + ins_block_id +
						"&_show_count=" + pagesCls.show_count[page_id];

		var params_grouping = new Object();
		params_grouping["method"] = "post";
		params_grouping["param"] = postBody;
		params_grouping["loading_el"] = Element.getChildElement(cell_el);

		//params_grouping["target_el"] = cell_el;
		//params_grouping["target_el"] = cell_el.parentNode;
		params_grouping["top_el"] = cell_el;
		//params_grouping["top_el"] = top_el;
		params_grouping["callbackfunc"] = function(cell_el){this.cancelGroupingComp(cell_el);}.bind(this);
		params_grouping["func_param"] = cell_el;
		params_grouping["callbackfunc_error"] = function(){location.reload();};
		params_grouping["token"] = pagesCls.pages_token[page_id];
		commonCls.send(params_grouping);
		//表示回数カウント++
		pagesCls.show_count[page_id]++;
		return false;

	},
	/*グルーピング解除完了処理*/
	cancelGroupingComp: function(parent_cell_el) {
		this.inCancelGroupingDrag = false;
		//var parent_cell_el = Element.getParentElementByClassName(Event.element(event),"cell");
		var top_el = Element.getChildElement(parent_cell_el);
		var parent_column_el = Element.getParentElementByClassName(top_el,"column");
		var first_column_el = Element.getChildElementByClassName(top_el,"column");
		var tr_el = first_column_el.parentNode;
		//選択されていたら、キャンセルする
		var divList = parent_cell_el.getElementsByTagName("div");
		for (var i = 0; i < divList.length; i++){
			if (Element.hasClassName(divList[i],"cell")){
				pagesCls.cancelSelectStyle(divList[i]);
			}
		}

		var tdList = Array();
		var count_td = 0;
		for (var i = 0; i < tr_el.childNodes.length; i++) {
			var column_el = tr_el.childNodes[i];
			if(column_el && column_el.tagName == "TD" && Element.hasClassName(column_el,"column")) {
				if(column_el == first_column_el) {
					//既存列追加処理
					var divList = Array();
					var count = 0;
					for (var j = 0; j < column_el.childNodes.length; j++) {
						var div = column_el.childNodes[j];
						if(div && div.tagName == "DIV" && Element.hasClassName(div,"cell")) {
							divList[count] = div;
							count++;
						}
					}
					for (var j = divList.length - 1; j >= 0; j--) {
						//既存列追加処理
						parent_column_el.insertBefore(divList[j], parent_cell_el.nextSibling);
					}
				} else {
					//新列追加処理
					tdList[count_td] = column_el;
					count_td++;
				}
			}
		}
		for (var i = tdList.length - 1; i >= 0; i--) {
			//新列追加処理
			parent_column_el.parentNode.insertBefore(tdList[i], parent_column_el.nextSibling);
		}
		//グルーピングノード削除
		Element.remove(parent_cell_el);

	}
}

pagesCls = new clsPages();