var clsUser = Class.create();
var userCls = Array();

clsUser.prototype = {
	initialize: function(id) {
		this.id = id;
		
		this.dndMgrSetting = null;
		this.dndCustomDraggable = null;
		this.dndCustomDropzone = null;
		// 項目追加
		this.popupPreview = null;
		
		this.grid = null;
		this.SearchResultRows = 18;		//検索結果一覧Grid行数default
		
		this.select_user = 0;	//全選択用
		this.select_lang = new Object();
	},
	/* 会員検索 */
	initSearch: function(form_el) {
		commonCls.moveAutoPosition($(this.id).parentNode);
		commonCls.focus(form_el);
	},
	/* 会員検索結果 */
	initSearchResult: function(count, visible_rows) {
		if(count == 0) return;
		this.select_user = 0;
		var opts = {
			prefetchBuffer : true,
			sort : true,
			onscroll : this.updateHeader.bind(this),
			requestParameters : new Array("select_user="+this.select_user),
			onSendCallback : function(res, url) {
								if(url.match(/&sort_col=/)) {
									// ソート
									if(this.select_user == 1) {
										this.selAllCheck($('searchresult_release_all'+this.id));
									}
								}
							}.bind(this)
		};
		if(visible_rows == undefined) visible_rows = this.SearchResultRows;
		
		//トップエレメントID、表示行数、トータル行数、取得アクション名称、オプション
		this.grid = new compLiveGrid (this.id, visible_rows, count, "user_action_main_searchresult", opts);
		
		commonCls.moveAutoPosition($(this.id).parentNode);
	},
	/* 会員検索結果 - 会員選択 */
	chgRadioSearchResult: function(this_el) {
		var grid_row_el = Element.getParentElementByClassName(this_el, "grid_row");
		if(this_el.checked) {
			Element.addClassName(grid_row_el, "highlight");
		} else {
			Element.removeClassName(grid_row_el, "highlight");
		}
	},
	/* 全選択 */
	selAllCheck: function(this_el, release_all_lang) {
		if(!this.select_lang[0]) {
			this.select_lang[0] = this_el.value;
			this.select_lang[1] = release_all_lang;
		}
		if(this.select_user == 0) {
			// 全選択
			this_el.value = this.select_lang[1];
			this.select_user = 1;
		} else {
			// 全解除
			this_el.value = this.select_lang[0];
			this.select_user = 0;
		}
		this.grid.setRequestParams("select_user="+this.select_user);
		var searchresult_grid_el = $('searchresult_grid'+this.id);
		var inputList = searchresult_grid_el.getElementsByTagName("input");
		for (var i = 0,inputLen = inputList.length; i < inputLen; i++) {
			if(inputList[i] && inputList[i].type == "checkbox") {
				if(this.select_user) {
					inputList[i].checked = true;
				} else {
					inputList[i].checked = false;
				}
				this.chgRadioSearchResult(inputList[i]);
			}
		}
	},
	/* 選択した会員の削除 */
	delUsers: function() {
		var del_params = new Object();
		del_params["method"] = "post";
		del_params["top_el"] = $(this.id);
		del_params["param"] = "action=user_action_admin_seldelete&select_user="+ this.select_user +"&refresh_flag="+1;
		var searchresult_grid_el = $('searchresult_grid'+this.id);
		var inputList = searchresult_grid_el.getElementsByTagName("input");
		for (var i = 0,inputLen = inputList.length; i < inputLen; i++) {
			if(inputList[i] && (inputList[i].type == "checkbox" || inputList[i].type == "hidden")) {
				if(inputList[i].checked) {
					var set_value = 1;
				} else {
					var set_value = 0;
				}
				del_params["param"] += "&" + inputList[i].name + "=" + set_value;
			}
		}
		var top_el = $(this.id);
		var user_search_main_el = Element.getChildElementByClassName(top_el, "user_search_main");
		del_params["target_el"] = user_search_main_el;
		
		commonCls.send(del_params);
	},
	/* 会員登録 */
	initRegist: function(el, user_id) {
		commonCls.moveAutoPosition($(this.id).parentNode);
		commonCls.focus(el);
		if(!commonCls.closeCallbackFuncEvent[this.id]) {
			// 閉じるボタンコールバック　セッションクリア処理
			commonCls.closeCallbackFuncEvent[this.id] = function() {
				var close_params = new Object();
				close_params["method"] = "post";
				close_params["param"] = "action=user_action_admin_registclose&user_id="+user_id;
				commonCls.send(close_params);
			}
		}
	},
	initSelauth: function(count, visible_rows) {
		if(count == 0) return;
		
		//トップエレメントID、表示行数、トータル行数、取得アクション名称、オプション
		new compLiveGrid (this.id, visible_rows, count);
		
		commonCls.moveAutoPosition($(this.id).parentNode);
	},
	initRegistConfirm: function(count, visible_rows) {
		var top_el = $(this.id);
		
		if(count != 0) {
			//トップエレメントID、表示行数、トータル行数、取得アクション名称、オプション
			new compLiveGrid (this.id, visible_rows, count);
		}
		var tabset = new compTabset(top_el);
		tabset.render();
		
		commonCls.moveAutoPosition($(this.id).parentNode);
	},
	/* 項目設定 */
	initSetting: function(el) {
		commonCls.moveAutoPosition($(this.id).parentNode);
		//項目追加へfocus移動
		commonCls.focus(el);
		// ドラッグ
		this.dndCustomDraggable = Class.create();
		this.dndCustomDraggable.prototype = Object.extend((new compDraggable), {
			endDrag: function() {
				//
				// 高さ変更  
				//
				var drop_params = this.getParams();
		    	var id = drop_params['id'];
				var left_drop_event_el = $("user_drop_event1"+id);
				var right_drop_event_el = $("user_drop_event2"+id);
				left_drop_event_el.style.height = "0px";	//初期化
				right_drop_event_el.style.height = "0px";	//初期化
				left_drop_event_el.style.height = (left_drop_event_el.parentNode.offsetHeight - left_drop_event_el.previousSibling.offsetHeight) + "px";
				right_drop_event_el.style.height = (right_drop_event_el.parentNode.offsetHeight - right_drop_event_el.previousSibling.offsetHeight) + "px";
				
				var draggable = this.htmlElement;
      			Element.setStyle(draggable, {opacity:""});
			}
		});
		// ドロップ
		this.dndCustomDropzone = Class.create();
		this.dndCustomDropzone.prototype = Object.extend((new compDropzone), {
			showHover: function(event) {
				var htmlElement = this.getHTMLElement();
				if ( this._showHover(htmlElement) )
					return;
				if(Element.hasClassName(htmlElement, "user_drop_event")) {
					this.showChgSeqHover(event, "top");
				} else {
					this.showChgSeqHover(event);
				}
			},

			hideHover: function(event) {
				this.hideChgSeqHover(event);
			},
			accept: function(draggableObjects) {
				var htmlElement = this.getHTMLElement();
				if(Element.hasClassName(htmlElement, "user_drop_event")) {
					//ドロップエレメント変更
					var row_len = htmlElement.previousSibling.rows.length;
					if(row_len != 0) {
						var row_el = htmlElement.previousSibling.rows[row_len - 1];
						this.acceptChgSeq(draggableObjects, row_el, "bottom");
					} else {
						var theGUI = draggableObjects[0].getDroppedGUI();
						if ( Element.getStyle( theGUI, "position" ) == "absolute" )
						{
							theGUI.style.position = "static";
							theGUI.style.top = "";
							theGUI.style.left = "";
						}
						var tbody = Element.getChildElement(htmlElement.previousSibling);
						if(tbody == null || tbody.tagName.toLowerCase() != "tbody") tbody = htmlElement.previousSibling;
						tbody.appendChild(theGUI);
					}
				} else {
					this.acceptChgSeq(draggableObjects);
				}
			},
			save: function(draggableObjects) {
				var drop_params = this.getParams();
		    	var id = drop_params['id'];
		    	var top_el = $(id);
		    	var pos = this.ChgSeqPosition;
		    	var drop_el = this.getHTMLElement();				// ドロップ対象エレメント
		    	var col_num = 0;
		    	var drag_el = draggableObjects[0].getHTMLElement();		// ドラッグ対象エレメント
		    	var drag_item_id = drag_el.id.replace("user_chg_row"+ id + "_","");
				if(pos == null) {
					return false;
				}    	
		    	if(Element.hasClassName(drop_el, "user_drop_event")) {
		    		var row_len = drop_el.previousSibling.rows.length;
		    		if(row_len != 0) {
		    			pos = "bottom";
		    			drop_el = drop_el.previousSibling.rows[row_len - 1];
		    			var drop_item_id = drop_el.id.replace("user_chg_row"+ id + "_","");
		    		} else {
		    			var drop_item_id = drag_item_id;
		    			if(drop_el.id == "user_drop_event1"+id) {
		    				//左カラム新規行
		    				col_num = 1;
		    			} else {
		    				//右カラム新規行
		    				col_num = 2;
		    			}
		    		}
		    	} else {
		    		var drop_item_id = drop_el.id.replace("user_chg_row"+ id + "_","");
		    	}
		    	var chgseq_params = new Object();
		    	chgseq_params["param"] = {"action":"user_action_admin_setting", "drag_item_id":drag_item_id,
													"drop_item_id":drop_item_id,
													"position":pos,
													"addnew_col_num":col_num
													};
				chgseq_params["method"] = "post";
				chgseq_params["top_el"] = top_el;
				chgseq_params["loading_el"] = drag_el;
	
				commonCls.send(chgseq_params);
				return true;
			}
		});
		
		this.dndMgrSetting = new compDragAndDrop();
		this.dndMgrSetting.registerDraggableRange($("user_item_setting"+this.id));
		
		var top_el = $(this.id);
		//var user_colfields = Element.getElementsByClassName(top_el, "user_chg_col");
		//user_colfields.each(function(col_el) {
		//	this.dndMgrSetting.registerDropZone(new this.dndCustomDropzone(col_el, {"id":this.id}));
		//}.bind(this));
		
		var left_drop_event_el = $("user_drop_event1"+this.id);
		var right_drop_event_el = $("user_drop_event2"+this.id);
		this.dndMgrSetting.registerDropZone(new this.dndCustomDropzone(left_drop_event_el, {"id":this.id}));
		this.dndMgrSetting.registerDropZone(new this.dndCustomDropzone(right_drop_event_el, {"id":this.id}));
		//高さ指定
		left_drop_event_el.style.height = (left_drop_event_el.parentNode.offsetHeight - left_drop_event_el.previousSibling.offsetHeight) + "px";
		right_drop_event_el.style.height = (right_drop_event_el.parentNode.offsetHeight - right_drop_event_el.previousSibling.offsetHeight) + "px";
		
		var user_rowfields = Element.getElementsByClassName(top_el, "user_chg_seq");
		user_rowfields.each(function(row_el) {
			var top_row_el = Element.getParentElementByClassName(row_el,"user_chg_row");
			this.dndMgrSetting.registerDraggable(new this.dndCustomDraggable(top_row_el, row_el, {"id":this.id}));
			this.dndMgrSetting.registerDropZone(new this.dndCustomDropzone(top_row_el, {"id":this.id}));
		}.bind(this));
		
	},
	/* 項目追加 */
	initPopupItem: function(form_el) {
		commonCls.focus(form_el.item_name);
	},
	showPopupItem: function(event, item_id) {
		item_id = (item_id == undefined) ? 0 : item_id;
		var param_popup = new Object();
		var params = new Object();
		param_popup = {
						"action":"user_view_admin_itemdetail",
						"item_id":item_id,
						"prefix_id_name":"popup"
					};
		var top_el = $(this.id);
		params['target_el'] = top_el;
		params['center_flag'] = true;
		params['modal_flag'] = true;
		commonCls.sendPopupView(event, param_popup, params);
	},
	/* 項目名称blur時 */
	blurItemName : function(this_el, form_el) {
		if(form_el.define_flag.checked && this_el.value != "") {
			//constantした値を取得
			var blur_param = new Object();
			blur_param["param"] = {
							"action":"user_view_admin_definename",
							"item_name":this_el.value,
							"prefix_id_name":"popup"
						};
			blur_param["callbackfunc"] = function(res) {
				this_el.nextSibling.innerHTML = res;
			}
			commonCls.send(blur_param);
		} else {
			this_el.nextSibling.innerHTML = "";
		}
	},
	/* 入力タイプ変更 */
	chgItemEditType: function(this_el) {
		var target_el = this_el.nextSibling;
		commonCls.displayNone(target_el);
		commonCls.displayNone(target_el.nextSibling);
		switch (this_el.value) {
			case "email":
			case "mobile_email":
				commonCls.displayVisible(target_el.nextSibling);
				break;
			case "checkbox":
			case "select":
			case "radio":
				commonCls.displayVisible(target_el);
				break;
		}
	},
	/* リスト追加 */
	addOption: function(this_el) {
		var form_el = $("user_additem_form"+this.id);
		form_el.options_len.value = parseInt(form_el.options_len.value) + 1;
		var top_el = $(this.id);
		var addoption_param = new Object();
		addoption_param["param"] = {
						"action":"user_action_admin_addoption",
						"iteration":form_el.options_len.value - 1,
						"prefix_id_name":"popup"
					};
		addoption_param["method"] = "post";
		addoption_param["callbackfunc"] = function(res) {
			var div_parent = document.createElement("DIV");
			div_parent.innerHTML = res;
			var options_el = $("user_items_options" + this.id);
			options_el.appendChild(Element.getChildElement(div_parent));
			div_parent = null;
			var inputList = options_el.getElementsByTagName("input");
			commonCls.focus(inputList[inputList.length - 2]);
		}.bind(this);
		addoption_param['top_el'] = top_el;
		commonCls.send(addoption_param);
	},
	delOption: function(this_el) {
		this_el.parentNode.removeChild(this_el);
		var form_el = $("user_additem_form"+this.id);
		//form_el.options_len.value = parseInt(form_el.options_len.value) - 1;
	},
	previewItem: function(this_el, addItem_parent_id) {
		var top_el = $(this.id);
		var form_el = $("user_additem_form"+this.id);
		//パラメータ設定
		var preview_params = new Object();

		preview_params["method"] = "post";
		if(addItem_parent_id) {
			var action_name = "user_action_admin_additem";
		} else {
			var action_name = "user_action_admin_preview";
		}
		preview_params["param"] = "action=" + action_name + "&prefix_id_name=popup&"+ Form.serialize(form_el);
		preview_params["top_el"] = top_el;
		preview_params["loading_el"] = top_el;
		//preview_params["target_el"] = top_el;
		preview_params["callbackfunc"] = function(res) {
			if(addItem_parent_id) {
				//親をリロード
				commonCls.removeBlock(this.id);
				commonCls.sendView(addItem_parent_id, {'action':'user_view_admin_setting'}); 
			} else {
				if(this.popupPreview == null || !$(this.popupPreview.popupID)) this.popupPreview = new compPopup(this.id, "_preview" + this.id);
				////this.popupPreview.loadObserver = this.focusUrl.bind(this);
				var div = document.createElement("DIV");
				Element.addClassName(div, "popupClass");
				div.innerHTML = res;
				this.popupPreview.showPopup(div, this_el);
			}
		}.bind(this);
		preview_params["callbackfunc_error"] = function(res) {
			commonCls.alert(res);
			commonCls.focus($(id));
		}.bind(this);
		commonCls.send(preview_params);
	},
	/* 項目追加 */
	addItem: function(this_el, module_id) {
		this.previewItem(this_el, "_" + module_id);
	},
	/* 項目削除 */
	delItem: function(item_id, this_el) {
		var top_el = $(this.id);
		var del_params = new Object();
		del_params["method"] = "post";
		del_params["param"] = "action=user_action_admin_delitem&item_id="+ item_id;
		del_params["top_el"] = top_el;
		del_params["callbackfunc_error"] = function(res) {
			commonCls.alert(res);
			commonCls.sendView(this.id, {'action':'user_view_admin_setting'});
		}.bind(this);
		commonCls.send(del_params);
		//行削除処理
		var row_el = Element.getParentElementByClassName(this_el, "user_chg_row");
		Element.remove(row_el);
		//
		// 高さ変更  
		//
		var left_drop_event_el = $("user_drop_event1"+this.id);
		var right_drop_event_el = $("user_drop_event2"+this.id);
		left_drop_event_el.style.height = "0px";	//初期化
		right_drop_event_el.style.height = "0px";	//初期化
		left_drop_event_el.style.height = (left_drop_event_el.parentNode.offsetHeight - left_drop_event_el.previousSibling.offsetHeight) + "px";
		right_drop_event_el.style.height = (right_drop_event_el.parentNode.offsetHeight - right_drop_event_el.previousSibling.offsetHeight) + "px";
	},
	/* 項目Display変更 */
	chgdisplayItem: function(item_id, display_flag, this_el) {
		if(display_flag) {
			commonCls.displayChange(this_el);
			commonCls.displayChange(this_el.previousSibling);
		} else {
			commonCls.displayChange(this_el);
			commonCls.displayChange(this_el.nextSibling);
		}
		var top_el = $(this.id);
		var chg_params = new Object();
		chg_params["method"] = "post";
		chg_params["param"] = "action=user_action_admin_chgdisplayitem&item_id=" + item_id +"&display_flag=" + display_flag;
		chg_params["top_el"] = top_el;
		chg_params["callbackfunc_error"] = function(res) {
			commonCls.alert(res);
			commonCls.sendView(this.id, {'action':'user_view_admin_setting'});
		}.bind(this);
		commonCls.send(chg_params);
	},
	/* 検索実行 */
	searchUser: function() {
		var top_el = $(this.id);
		var user_search_main_el = Element.getChildElementByClassName(top_el, "user_search_main");
		var form_el = $("form" + this.id);
		this.formSerializeMain("user_action_main_search", form_el, user_search_main_el);
	},
	/* 共通、 Form.serialize-send()処理*/
	formSerializeMain: function(action_name, form_el, target_el, close_flag) {
		var top_el = $(this.id);
		var serialize_params = new Object();
		serialize_params["method"] = "post";
		serialize_params["param"] = "action=" + action_name +"&op=setting"+ "&"+ Form.serialize(form_el);
		serialize_params["top_el"] = top_el;
		serialize_params["loading_el"] = target_el;
		if(close_flag) {
			serialize_params["callbackfunc"] = function(res){commonCls.removeBlock(this);}.bind(top_el);
		} else {
			if(form_el.sending_email && form_el.sending_email.value == "" && action_name == "user_action_admin_regist") {
				// 新規作成画面へ
				serialize_params["target_el"] = $(this.id);
			} else {
				serialize_params["target_el"] = target_el;
			}
			if(action_name == "user_view_admin_regist_selroom") {
				serialize_params["callbackfunc_error"] = function(res){this.focusError(res);}.bind(this);
			}
		}
		commonCls.send(serialize_params);
	},
	/*全レコード中何件～何件まで表示したかを表示する場合、使用(1 - 10 of 100)*/
	updateHeader: function (liveGrid, offset) {
		var record_num_el = $("user_record_num" + this.id);
		record_num_el.innerHTML = (offset+1) + " - " +
									(offset+liveGrid.metaData.getPageSize());
	},
	/* 検索 >> 会員編集 */
	showEditUser: function(event, user_id) {
		user_id = (user_id == undefined) ? 0 : user_id;
		var param_popup = new Object();
		var user_params = new Object();
		param_popup = {
						"action":"user_view_admin_regist_init",
						"prefix_id_name":"popup_user"+user_id,
						"user_id":user_id,
						"theme_name": "system"
					};
		user_params['method'] = "post";
		commonCls.sendPopupView(event, param_popup, user_params);
	},
	/* 会員削除 */
	delUser: function(event, user_id) {
		var del_params = new Object();
		del_params["method"] = "post";
		del_params["top_el"] = $(this.id);
		del_params["param"] = "action=user_action_admin_delete&user_id="+user_id+"&refresh_flag="+1;
		var top_el = $(this.id);
		var user_search_main_el = Element.getChildElementByClassName(top_el, "user_search_main");
		del_params["target_el"] = user_search_main_el;
		
		commonCls.send(del_params);
	},
	/* 会員登録-Fileプレビュー*/
	previewFile: function (event, this_el, item_id, upload_count, user_id) {
		var upd_params = new Object();
		upd_params["param"] = {'action': "user_action_admin_upload_image", "item_id": item_id};
		
		upd_params["top_el"] = $(this.id);
		upd_params["method"] = "post";
		
		if(user_id != undefined && user_id != 0) upd_params["param"]['unique_id'] = user_id;
		upd_params["callbackfunc"] = function(file_list, res){
			// 正常終了
			var user_filecomp_el = Element.getParentElementByClassName(this_el,"user_filecomp");
			var parent_img_el = user_filecomp_el.nextSibling;
			var hidden_el = parent_img_el.nextSibling;
			var url = "?action="+ file_list[upload_count]['action_name'] + "&upload_id=" + file_list[upload_count]['upload_id'];
			Element.getChildElement(parent_img_el).src = url;
			hidden_el.value = url;
			commonCls.displayVisible(parent_img_el);
			commonCls.displayNone(user_filecomp_el);
		}.bind(this);
		upd_params['form_prefix'] = "user_attachment";
		upd_params["callbackfunc_error"] = function(file_list, res){
			// エラー時(File)
			commonCls.alert(res);
			this_el.focus();
		}.bind(this);
		// 複数ファイルコンポーネントがあった場合、複数アップロードされてしまう
		// formタグが１つしかないため、仕方ないが複数ファイルコンポーネントを置けるように
		// 対応した場合、アップロードさせないようにしなければならない
		commonCls.sendAttachment(upd_params);
	},
	deleteFile: function (event, this_el) {
		var image_el = this_el.parentNode.previousSibling;
		var image_parent_el = image_el.parentNode;
		var hidden_el = image_parent_el.nextSibling;
		var user_filecomp_el =image_parent_el.previousSibling;
		hidden_el.value = "";
		image_el.removeAttribute("src", 0);
		commonCls.displayNone(image_parent_el);
		commonCls.displayVisible(user_filecomp_el);
		Element.getChildElement(user_filecomp_el).focus();
	},
	selectOnChange: function(event, enroll_flag, el) {
		if(el == undefined) {
			var event_el = Event.element(event);
		} else {
			var event_el = el;
		}
		
		var optionPageIdArr = new Array();
		var eleOptions = event_el.getElementsByTagName("option");
		var option_len = eleOptions.length;
		if(enroll_flag) {
			for (var i = option_len - 1; i >= 0 ; i--){
				// page_id parent_id
				var split_value = eleOptions[i].value.split("_");
				// 親が選択されたら、子供も選択
				if(!optionPageIdArr[split_value[1]]) optionPageIdArr[split_value[1]] = new Object();
				optionPageIdArr[split_value[1]][split_value[0]] = i;
			}
			for (var i = option_len - 1; i >= 0 ; i--){
				// page_id parent_id
				var split_value = eleOptions[i].value.split("_");
				if (Element.hasClassName(eleOptions[i],"disable_lbl") && eleOptions[i].selected == true){
					// disable
					eleOptions[i].selected = false;
					//if(event_el.selectedIndex == i) {
					//	event_el.selectedIndex = 0;
					//}
				}else if (eleOptions[i].selected == true){
					if(optionPageIdArr[split_value[0]] != undefined) {
						for (var page_id in optionPageIdArr[split_value[0]]) {
							eleOptions[optionPageIdArr[split_value[0]][page_id]].selected = true;
						}
					}
				}
			}
		} else {
			for (var i = 0; i < option_len; i++){
				// page_id parent_id
				var split_value = eleOptions[i].value.split("_");
				// 子が選択されたら、親も選択
				optionPageIdArr[split_value[0]] = i;
			}
			for (var i = 0; i < option_len; i++){
				// page_id parent_id
				var split_value = eleOptions[i].value.split("_");
				// 子が選択されたら、親も選択
				if (eleOptions[i].selected == true){
					if(split_value[1] != 0 && optionPageIdArr[split_value[1]] != undefined) {
						eleOptions[optionPageIdArr[split_value[1]]].selected = true;
					}
				}
			}
		}
	},
	selectAllSelectEl: function(form_el, name) {
		commonCls.frmAllSelectList(form_el, 'not_'+name);
		commonCls.frmAllSelectList(form_el, name);
	},
	focusError: function(res) {
		if(res.match(":")) {
			var mesArr = res.split(":");
			var alert_res = "";
			for(var i = 1; i < mesArr.length; i++) {
				alert_res += mesArr[i];
			}
			// チェックボックス等の場合、うまく動作しないかも
			var focus_el = $("user_items"+ this.id + "_" + mesArr[0])
			if(focus_el) {
				commonCls.alert(alert_res);
				focus_el.focus();
				if(focus_el.type == "text") focus_el.select();
			} else {
				commonCls.alert(res);
			}
		} else {
			commonCls.alert(res);
		}
	},
	chkRadioSelectauth: function(check_el) {
		if(check_el.disabled != true) {
			check_el.checked = true;
		}
		var td_el = Element.getParentElement(check_el, 2);
		var tr_el = Element.getParentElement(td_el);
		if(check_el.type == "radio") {
			var tdList = tr_el.getElementsByTagName("td");
			for (var i = 0; i < tdList.length; i++) {
				var child_el = Element.getChildElement(tdList[i], 2);
				if(child_el && child_el.type == "radio") {
					Element.removeClassName(tdList[i], "user_active_auth_id");
				}
			}
		}
		if(check_el.checked) {
			Element.addClassName(td_el, "user_active_auth_id");
		} else {
			Element.removeClassName(td_el, "user_active_auth_id");
		}
	},
	chkCheckboxCreateroom: function(check_el) {
		var td_el = Element.getParentElement(check_el, 2);
		if(check_el.checked) {
			check_el.checked = false;
			Element.removeClassName(td_el, "user_active_auth_id");
		} else {
			check_el.checked = true;
			Element.addClassName(td_el, "user_active_auth_id");
		}
	},
	// 確認画面print
	print: function() {
		var tab_el = $("user_tabset_content"+this.id);
		var html = "";
		var base_item = Element.getChildElement(tab_el);
		if(base_item) html += base_item.innerHTML;
		var room_el = base_item.nextSibling;
		if(room_el) html += room_el.innerHTML;
		commonCls.print(html);
	},
	user_uploaddata: function(form_el) {
		var this_el = $("user_import_form" + this.id);
		var top_el = $(this.id);
		var params = new Object();
		params["method"] = "post";
		params["param"] = new Object();
		params["param"]["action"] = "user_view_admin_import_upload";
		params["param"]["user_import_option_data_set"] = form_el.user_import_option_data_set.value;
		params["param"]["user_import_option_detail_set"] = form_el.user_import_option_detail_set.value;
		params["form_el"] = form_el;
		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["timeout_flag"] = 0;
		params["target_el"] = $("target"+this.id);
		params['form_prefix'] = "user_import_attachment";
		params["callbackfunc"] = function(res) {
			this.user_checkdata(this_el);
		}.bind(this);
		params["callbackfunc_error"] = function(file_list, res){
			this.focusError(res);
		}.bind(this);
		commonCls.sendAttachment(params);
	},
	user_checkdata: function(this_el) {
		var top_el = $(this.id);
		var chk_params = new Object();
		chk_params["param"] = {"action":"user_view_admin_import_confirm"};
		chk_params["top_el"] = top_el;
		chk_params["loading_el"] = top_el;
		chk_params["target_el"] = $("import_confirm"+ this.id);
		commonCls.send(chk_params);
	},
	user_importdata: function(form_el) {
		var top_el = $(this.id);
		var this_el = $("import_confirm_form" + this.id);
		var action_params = new Object();
		action_params['method'] = "post";
		action_params["param"] = {"action":"user_action_admin_import_confirm"};
		action_params["top_el"] = top_el;
		action_params["form_el"] = form_el;
		action_params["loading_el"] = top_el;
		action_params["target_el"] = $("import_confirm"+ this.id);
		commonCls.send(action_params);
	},
	user_importreturn: function(this_el) {
		var top_el = $(this.id);
		var imp_params = new Object();
		imp_params["param"] = {"action":"user_view_admin_import"};
		imp_params["top_el"] = top_el;
//		imp_params["target_el"] = $("import_form"+ this.id);
		imp_params["loading_el"] = top_el;
		imp_params["callbackfunc"] = function(res) {
			commonCls.sendView(this.id, "user_view_admin_import");
		}.bind(this);
		commonCls.send(imp_params);
	}
}