var clsMultidatabase = Class.create();
var mdbCls = Array();

clsMultidatabase.prototype = {
	/**
	 * 初期処理
	 *
	 * @param	id	ID
	 * @return  none
	 **/
	initialize: function(id) {
		this.id = id;
		this.currentMdbId = null;
		this.multidatabase_id = null;
		this.popupLnk = null;
		this.dndMgrMetadata = null;
		this.dndCustomDrag = null;
		this.dndCustomDropzone = null;
		this.calendarFm = null;
		this.calendarTo = null;
		this.textarea = new Object();
		this.calendar = new Object();
		this.errorSeparator = null;
	},

	initMetadatas: function(el, multidatabase_id) {
		this.multidatabase_id = multidatabase_id;
		commonCls.moveAutoPosition($(this.id).parentNode);
		//項目追加へfocus移動
		commonCls.focus(el);
		// ドラッグ
		this.dndCustomDrag = Class.create();
		this.dndCustomDrag.prototype = Object.extend((new compDraggable), {
			endDrag: function() {
				// 高さ変更
				var drop_params = this.getParams();
		    	var id = drop_params['id'];
		    	var top_drop_event_el = $("mdb_drop_event1"+id);
				var left_drop_event_el = $("mdb_drop_event2"+id);
				var right_drop_event_el = $("mdb_drop_event3"+id);
				var bottom_drop_event_el = $("mdb_drop_event4"+id);
				top_drop_event_el.style.height = "10px";
				left_drop_event_el.style.height = "10px";
				right_drop_event_el.style.height = "10px";
				bottom_drop_event_el.style.height = "10px";
				left_drop_event_el.style.height = (left_drop_event_el.parentNode.offsetHeight - left_drop_event_el.previousSibling.offsetHeight) + "px";
				right_drop_event_el.style.height = (right_drop_event_el.parentNode.offsetHeight - right_drop_event_el.previousSibling.offsetHeight) + "px";
				var draggable = this.htmlElement;
       			Element.setStyle(draggable, {opacity:"1"});
			}
		});

		// ドロップ
		this.dndCustomDropzone = Class.create();
		this.dndCustomDropzone.prototype = Object.extend((new compDropzone), {
			showHover: function(event) {
				var htmlElement = this.getHTMLElement();
				if ( this._showHover(htmlElement) )
					return;
				if(Element.hasClassName(htmlElement, "mdb_drop_event")) {
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
				if(Element.hasClassName(htmlElement, "mdb_drop_event")) {
					//ドロップエレメント変更
					var row_len = htmlElement.previousSibling.rows.length;
					if(row_len != 0) {
						var row_el = htmlElement.previousSibling.rows[row_len - 1];
						this.acceptChgSeq(draggableObjects, row_el, "bottom");
					} else {
						var theGUI = draggableObjects[0].getDroppedGUI();
						if ( Element.getStyle( theGUI, "position" ) == "absolute" )	{
							theGUI.style.position = "static";
							theGUI.style.top = "";
							theGUI.style.left = "";
						}
						var test = htmlElement.previousSibling;
						var tbody = Element.getChildElement(htmlElement.previousSibling);
						if(tbody == null || tbody.tagName.toLowerCase() != "tbody") {
							tbody = htmlElement.previousSibling;
						}
						tbody.appendChild(theGUI);
					}
				} else {
					this.acceptChgSeq(draggableObjects);
				}
			},

			save: function(draggableObjects) {
				if(this.ChgSeqPosition == null) {
					return false;
				}
				var drop_params = this.getParams();
		    	var id = drop_params['id'];
		    	var top_el = $(id);
		    	var pos = this.ChgSeqPosition;
		    	var drop_el = this.getHTMLElement();				// ドロップ対象エレメント
		    	var display_pos = 1;
		    	var drag_el = draggableObjects[0].getHTMLElement();		// ドラッグ対象エレメント
		    	var drag_metadata_id = drag_el.id.replace("mdb_chg_row"+ id + "_","");
		    	var parent_table = null;
		    	if(Element.hasClassName(drop_el, "mdb_drop_event")) {
		    		parent_table = drop_el.previousSibling;
		    		var row_len = drop_el.previousSibling.rows.length;
		    		if(row_len != 0) {
		    			pos = "bottom";
		    			drop_el = drop_el.previousSibling.rows[row_len - 1];
		    			var drop_metadata_id = drop_el.id.replace("mdb_chg_row"+ id + "_","");
		    			if(drop_metadata_id == drag_metadata_id) {
		    				return;
		    			}
		    		} else {
		    			var drop_metadata_id = drag_metadata_id;
		    		}

		    	} else {
		    		parent_table = Element.getParentElement(drop_el, 2);
		    		var drop_metadata_id = drop_el.id.replace("mdb_chg_row"+ id + "_","");
		    	}

		    	if(Element.hasClassName(parent_table, "mdb_drop_pos_1")) {
	    			display_pos = 1;
	    		}else if(Element.hasClassName(parent_table, "mdb_drop_pos_2")) {
	    			display_pos = 2;
	    		}else if(Element.hasClassName(parent_table, "mdb_drop_pos_3")){
	    			display_pos = 3;
	    		}else if(Element.hasClassName(parent_table, "mdb_drop_pos_4")){
	    			display_pos = 4;
	    		}

		    	var chgseq_params = new Object();
		    	chgseq_params["param"] = {"action":"multidatabase_action_edit_metadataseq",
		    										"drag_metadata_id":drag_metadata_id,
													"drop_metadata_id":drop_metadata_id,
													"position":pos,
													"display_pos":display_pos
													};
				chgseq_params["method"] = "post";
				chgseq_params["top_el"] = top_el;
				chgseq_params["loading_el"] = drag_el;

				commonCls.send(chgseq_params);
				return true;
			}
		});

		var edit_top_el = $("mdb_metadata_setting"+ this.id);
		this.dndMgrMetadata = new compDragAndDrop();
		this.dndMgrMetadata.registerDraggableRange(edit_top_el);

		var top_drop_event_el = $("mdb_drop_event1"+this.id);
		var left_drop_event_el = $("mdb_drop_event2"+this.id);
		var right_drop_event_el = $("mdb_drop_event3"+this.id);
		var bottom_drop_event_el = $("mdb_drop_event4"+this.id);
		this.dndMgrMetadata.registerDropZone(new this.dndCustomDropzone(top_drop_event_el, {"id":this.id}));
		this.dndMgrMetadata.registerDropZone(new this.dndCustomDropzone(left_drop_event_el, {"id":this.id}));
		this.dndMgrMetadata.registerDropZone(new this.dndCustomDropzone(right_drop_event_el, {"id":this.id}));
		this.dndMgrMetadata.registerDropZone(new this.dndCustomDropzone(bottom_drop_event_el, {"id":this.id}));
		//高さ指定
		left_drop_event_el.style.height = "10px";
		left_drop_event_el.style.height = (left_drop_event_el.parentNode.offsetHeight - left_drop_event_el.previousSibling.offsetHeight) + "px";
		right_drop_event_el.style.height = (right_drop_event_el.parentNode.offsetHeight - right_drop_event_el.previousSibling.offsetHeight) + "px";
		bottom_drop_event_el.style.height = "10px";

		var metadata_rowfields = Element.getElementsByClassName(edit_top_el, "mdb_chg_seq");
		metadata_rowfields.each(function(row_el) {
			var top_row_el = Element.getParentElementByClassName(row_el,"mdb_chg_row");
			this.dndMgrMetadata.registerDraggable(new this.dndCustomDrag(top_row_el, row_el, {"id":this.id}));
			this.dndMgrMetadata.registerDropZone(new this.dndCustomDropzone(top_row_el, {"id":this.id}));
		}.bind(this));
	},
	initCalendar: function() {
		this.calendarFm = new compCalendar(this.id, "mdb_search_date_from" + this.id);
		this.calendarTo = new compCalendar(this.id, "mdb_search_date_to" + this.id);
	},
	editCancel: function() {
		commonCls.sendView(this.id, "multidatabase_view_main_init");
	},
	checkCurrent: function() {
		var currentRow = $("mdb_current_row" + this.currentMdbId + this.id);
		if (!currentRow) {
			return;
		}
		Element.addClassName(currentRow, "highlight");

		var current = $("mdb_current" + this.currentMdbId + this.id);
		current.checked = true;
	},
	referenceMdb: function(event, mdb_id) {
		var params = new Object();
		params["action"] = "multidatabase_view_main_init";
		params["multidatabase_id"] = mdb_id;
		params["prefix_id_name"] = "popup_mdb_reference" + mdb_id;

		var popupParams = new Object();
		var top_el = $(this.id);
		popupParams['top_el'] = top_el;
		popupParams['target_el'] = top_el;
		popupParams['center_flag'] = true;

		commonCls.sendPopupView(event, params, popupParams);
	},
	/* 項目追加 */
	initPopupMetadata: function(form_el) {
		commonCls.focus(form_el.name);
	},
	showPopupMetadata: function(event, metadata_id) {
		metadata_id = (metadata_id == undefined) ? 0 : metadata_id;
		var param_popup = new Object();
		var params = new Object();
		param_popup = {
						"action":"multidatabase_view_edit_metadata_detail",
						"multidatabase_id":this.multidatabase_id,
						"metadata_id":metadata_id,
						"prefix_id_name":"popup"
					};
		var top_el = $(this.id);
		params['top_el'] = top_el;
		params['target_el'] = top_el;
		params['center_flag'] = true;
		params['modal_flag'] = true;
		commonCls.sendPopupView(event, param_popup, params);
	},
	showPopupImport: function(event, multidatabase_id) {
		var param_popup = new Object();
		var params = new Object();
		param_popup = {
						"action":"multidatabase_view_edit_import_init",
						"multidatabase_id":multidatabase_id,
						"prefix_id_name":"popup"
					};
		var top_el = $(this.id);
		params['top_el'] = top_el;
		params['target_el'] = top_el;
		params['center_flag'] = true;
		params['modal_flag'] = true;
		commonCls.sendPopupView(event, param_popup, params);
	},
	/* 入力タイプ変更 */
	chgMetadataEditType: function(this_el) {
		var target_el = this_el.nextSibling;
		commonCls.displayNone(target_el);
		commonCls.displayNone(target_el.nextSibling);

		var title_flag_el = $('mdb_title_flag' + this.id);
		var title_metadata_el = $('mdb_metadata_title_metadata_flag' + this.id);
		var require_el = $('mdb_metadata_require_flag' + this.id);
		var list_el = $('mdb_metadata_list_flag' + this.id);
		var sort_el = $('mdb_metadata_sort_flag' + this.id);
		var name_el = $('mdb_metadata_name_flag' + this.id);
		var search_el = $('mdb_metadata_search_flag' + this.id);
		var password_el = $('mdb_metadata_file_password_flag' + this.id);
		var count_el = $('mdb_metadata_file_count_flag' + this.id);

		if(title_flag_el.value == "0") {
			title_metadata_el.disabled = false;
			Element.removeClassName(title_metadata_el.parentNode, "disable_lbl");
		}

		require_el.disabled = false;
		if (title_metadata_el.checked) {
			require_el.checked = true;
			require_el.disabled = true;
			Element.addClassName(require_el, "disable_lbl");
		} else {
			Element.removeClassName(require_el.parentNode, "disable_lbl");
		}

		if(list_el.checked) {
			sort_el.disabled = false;
			Element.removeClassName(sort_el.parentNode, "disable_lbl");
		}

		name_el.disabled = false;
		Element.removeClassName(name_el.parentNode, "disable_lbl");
		search_el.disabled = false;
		Element.removeClassName(search_el.parentNode, "disable_lbl");

		this._disableCheckItem(password_el);
		this._disableCheckItem(count_el);

		switch (this_el.value) {
			case "0":
				this._disableCheckItem(name_el);
				this._disableCheckItem(search_el);
				break;
			case "5":
				var password_hidden_el = $('mdb_metadata_file_password_flag_hidden' + this.id);
				if (password_hidden_el.value == "1") {
					password_el.checked = true;
				}
				password_el.disabled = false;
				Element.removeClassName(password_el.parentNode, "disable_lbl");

				var count_hidden_el = $('mdb_metadata_file_count_flag_hidden' + this.id);
				if (count_hidden_el.value == "1") {
					count_el.checked = true;
				}
				count_el.disabled = false;
				Element.removeClassName(count_el.parentNode, "disable_lbl");

				this._disableCheckItem(search_el);
				break;
			case "4":
			case "12":
				commonCls.displayVisible(target_el);
				break;
			case "7":
				this._disableCheckItem(require_el);
				break;
			case "9":
				this._disableCheckItem(search_el);
				break;
			case "10":
			case "11":
				this._disableCheckItem(require_el);
				this._disableCheckItem(search_el);
				break;
		}
	},
	_disableCheckItem: function(el) {
		el.checked = false;
		el.disabled = true;
		Element.addClassName(el.parentNode, "disable_lbl");
	},
	/*選択肢追加 */
	addOption: function(this_el) {
		var form_el = $("mdb_addmetadata_form"+this.id);
		form_el.options_len.value = parseInt(form_el.options_len.value) + 1;
		var top_el = $(this.id);
		var addoption_param = new Object();
		addoption_param["param"] = {
						"action":"multidatabase_view_edit_option_add",
						"iteration":parseInt(form_el.options_len.value) - 1,
						"prefix_id_name":"popup"
					};
		addoption_param["callbackfunc"] = function(res) {
			var div_parent = document.createElement("DIV");
			div_parent.innerHTML = res;
			var options_el = $("mdb_metadata_options" + this.id);
			options_el.appendChild(Element.getChildElement(div_parent));
			div_parent = null;
			var inputList = options_el.getElementsByTagName("input");
			commonCls.focus(inputList[inputList.length - 1]);
		}.bind(this);
		addoption_param['top_el'] = top_el;
		addoption_param["loading_el"] = this_el;
		commonCls.send(addoption_param);
	},
	delOption: function(this_el, confirmMessage) {
		if (!confirm(confirmMessage)) {
			return false;
		}
		this_el.parentNode.removeChild(this_el);
	},
	/* 項目追加 */
	addMetadata: function(this_el, block_id) {
		var top_el = $(this.id);
		var form_el = $("mdb_addmetadata_form"+this.id);
		var multidatabase_id = form_el.multidatabase_id.value;
		//パラメータ設定
		var add_params = new Object();
		add_params["method"] = "post";
		add_params["param"] = "action=multidatabase_action_edit_addmetadata&prefix_id_name=popup&"+ Form.serialize(form_el);
		add_params["top_el"] = top_el;
		add_params["loading_el"] = top_el;
		//add_params["target_el"] = top_el;
		add_params["callbackfunc"] = function(res) {
			//親をリロード
			commonCls.removeBlock(this.id);
			commonCls.sendView("_"+block_id, {'action':'multidatabase_view_edit_metadata_list','multidatabase_id':multidatabase_id});
		}.bind(this);
		add_params["callbackfunc_error"] = function(res) {
			commonCls.alert(res);
			commonCls.focus($(id));
		}.bind(this);
		commonCls.send(add_params);
	},
	/* 項目削除 */
	delMetadata: function(metadata_id, confirmMessage) {
		if (!confirm(confirmMessage)) {
			return false;
		}
		var top_el = $(this.id);
		var del_params = new Object();
		del_params["method"] = "post";
		del_params["param"] = "action=multidatabase_action_edit_delmetadata&metadata_id="+ metadata_id;
		del_params["top_el"] = top_el;
		del_params["callbackfunc"] = function(res) {
			commonCls.sendView(this.id, {'action':'multidatabase_view_edit_metadata_list','multidatabase_id':this.multidatabase_id});
		}.bind(this);
		del_params["callbackfunc_error"] = function(res) {
			commonCls.alert(res);
			commonCls.sendView(this.id, {'action':'multidatabase_view_edit_metadata_list','multidatabase_id':this.multidatabase_id});
		}.bind(this);
		commonCls.send(del_params);
	},
	styleEdit: function(form_el) {
		var top_el = $(this.id);
		var edit_params = new Object();
		edit_params["param"] = "multidatabase_action_edit_style" + "&"+ Form.serialize(form_el);
		edit_params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
			commonCls.sendView(this.id,"multidatabase_view_edit_list");
		}.bind(this);
		edit_params["method"] = "post";
		edit_params["loading_el"] = top_el;
		edit_params["top_el"] = top_el;
		edit_params["target_el"] = top_el;
		commonCls.send(edit_params);
	},
	//汎用データベース選択
	changeCurrent: function(mdb_id) {
		var oldCurrentRow = $("mdb_current_row" + this.currentMdbId + this.id);
		if (oldCurrentRow) {
			Element.removeClassName(oldCurrentRow, "highlight");
		}

		this.currentMdbId = mdb_id;
		var currentRow = $("mdb_current_row" + this.currentMdbId + this.id);
		Element.addClassName(currentRow, "highlight");

		var post = {
			"action":"multidatabase_action_edit_change",
			"multidatabase_id":mdb_id
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
			commonCls.sendView(this.id, "multidatabase_view_edit_list");
		}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},
	//汎用データベース削除
	delMdb: function(mdb_id, confirmMessage) {
		if (!confirm(confirmMessage)) {
			return false;
		}
		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc_error"] = function(res){
			commonCls.sendView(this.id, "multidatabase_view_edit_list");
		}.bind(this);
		commonCls.sendPost(this.id, "action=multidatabase_action_edit_delete&multidatabase_id=" + mdb_id, params);
	},
	delContent: function(mdb_id, content_id, confirmMessage) {
		if (!confirm(confirmMessage)) {
			return false;
		}
		commonCls.sendPost(this.id, "action=multidatabase_action_main_delcontent&content_id=" + content_id + "&multidatabase_id=" + mdb_id, {"target_el":$('mdb_refresh_target'+this.id)});
	},
	vote: function(mdb_id, content_id) {
		var params = new Object();
		params["top_el"] = $(this.id);
		params["callbackfunc"] = function(res){
			commonCls.sendRefresh(this.id);
		}.bind(this);
		commonCls.sendPost(this.id, "action=multidatabase_action_main_vote&content_id=" + content_id + "&multidatabase_id=" + mdb_id, params);
	},
	confirmContent: function(mdb_id, content_id, confirmMessage) {
		if (!confirm(confirmMessage)) {
			return false;
		}

		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc"] = function(res){
			commonCls.sendPost(this.id, {"action":"multidatabase_action_main_mail"}, {"loading_el":null});
		}.bind(this);
		commonCls.sendPost(this.id, "action=multidatabase_action_main_confirm&content_id=" + content_id + "&multidatabase_id=" + mdb_id, params);
	},
	//コメント登録
	postComment: function(form_el) {
		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc"] = function(res){
			commonCls.displayVisible($('mdb_comment' + this.id));
		}.bind(this);
		commonCls.sendPost(this.id, "action=multidatabase_action_main_comment" + "&" + Form.serialize(form_el), params);
	},
	editComment: function(comment_id, mdb_id, content_id) {
		var div_content = $("comment_content_" + comment_id + this.id);
		var textbox = $("mdb_comment_textarea" + this.id);
		textbox.value = div_content.innerHTML.replace(/\n/ig,"").replace(/(<br(?:.|\s|\/)*?>)/ig,"\n").unescapeHTML();
		var hidden_flag = $("comment_post_id"+this.id);
		hidden_flag.value = comment_id;
		textbox.focus();
		textbox.select();
	},
	deleteComment: function(comment_id, mdb_id, content_id, confirmMessage) {
		if (!confirm(confirmMessage)) {
			return false;
		}
		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc"] = function(res){
			commonCls.displayVisible($('mdb_comment' + this.id));
		}.bind(this);
		commonCls.sendPost(this.id, "action=multidatabase_action_main_delcomment&comment_id=" + comment_id + "&content_id=" + content_id + "&multidatabase_id=" + mdb_id, params);
	},
	//登録フォーム追加
	insertMdb: function(form_el) {
		commonCls.sendPost(this.id, "action=multidatabase_action_edit_create" + "&" + Form.serialize(form_el), {"target_el":$(this.id)});
	},
	//汎用データベース編集
	editMdb: function(form_el) {
		commonCls.sendPost(this.id, "action=multidatabase_action_edit_modify" + "&" + Form.serialize(form_el), {"target_el":$(this.id)});
	},
	toPage: function(el, now_page, position) {
		var form_name = "mdb_page_form"+this.id;
		if(position == "bottom") {
			form_name = form_name + "_bottom";
		}
		var form_el = $(form_name);
		var top_el = $(this.id);
		var params = new Object();
		form_el.now_page.value = now_page;
		params["param"] = "action=multidatabase_view_main_init&"+ Form.serialize(form_el);
		params["top_el"] = top_el;
		params["loading_el"] = el;
		params["target_el"] = top_el;
		commonCls.send(params);
	},
	toSearchPage: function(el, now_page) {
		var form_el = $("mdb_search_form"+this.id);
		form_el.now_page.value = now_page;
		commonCls.sendView(this.id, "action=multidatabase_view_main_search_result" + "&" + Form.serialize(form_el));
	},
	changeActivity: function(element, mdb_id, activity) {
		var elements = element.parentNode.childNodes;
		for (var i = 0, length = elements.length; i < length; i++) {
			if (elements[i] == element) {
				Element.addClassName(elements[i], "display-none");
			} else {
				Element.removeClassName(elements[i], "display-none");
			}
		}
		var post = {
			"action":"multidatabase_action_edit_activity",
			"multidatabase_id":mdb_id,
			"active_flag":activity
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
			commonCls.sendView(this.id, "multidatabase_view_edit_list");
		}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},
	wysiwygInit: function(metadata_id) {
		//テキストエリア
		if (typeof this.textarea[metadata_id] == "undefined") {
			this.textarea[metadata_id] = new compTextarea();
			this.textarea[metadata_id].uploadAction = {
				//unique_id   : 0,
				image    : "multidatabase_action_main_upload_image",
				file     : "multidatabase_action_main_upload_init"
			};
			this.textarea[metadata_id].popupPrefix = "mdb_metadatas_" + metadata_id + this.id;
		}
		this.textarea[metadata_id].textareaShow(this.id, "textarea_" + metadata_id + this.id, "full");
	},
	calendarInit: function(metadata_id) {
		//テキストエリア
		if (typeof this.calendar[metadata_id] == "object") {
			this.calendar[metadata_id] = null;
		}
		if (typeof this.calendar[metadata_id] == "undefined" || this.calendar[metadata_id] == null) {
			this.calendar[metadata_id] = new compCalendar(this.id, "mdb_metadatas_" + metadata_id + this.id);
		}
	},
	setWysiwyg: function(metadata_id, form_el) {
		if (typeof this.textarea[metadata_id] != "undefined") {
			form_el["datas[" + metadata_id + "]"].value = this.textarea[metadata_id].getTextArea();
		}
	},
	showPopupEditPreview: function(event, multidatabase_id) {
		var param_popup = new Object();
		var params = new Object();
		param_popup = {
						"action":"multidatabase_view_edit_metadata_preview",
						"multidatabase_id":multidatabase_id,
						"prefix_id_name":"popup"
					};
		var top_el = $(this.id);
		params['top_el'] = top_el;
		params['target_el'] = top_el;
		params['center_flag'] = true;
		params['modal_flag'] = true;

		commonCls.sendPopupView(event, param_popup, params);
	},
	contentSubmit: function (form_el, temp_flag) {
		var params = new Object();
		if(temp_flag == 1) {
			form_el.temporary_flag.value = 1;
		}
		if(this.textarea != null) {
			for (var metadata_id in this.textarea) {
				this.setWysiwyg(metadata_id, form_el);
			}
		}
		params["param"] = {'action': "multidatabase_action_main_addcontent"};
		params["top_el"] = $(this.id);
		params["method"] = "post";
		params['form_prefix'] = "mdb_attachment";
		params["callbackfunc"] = function(res){
				var content_id = (form_el.content_id) ? form_el.content_id.value : '';
				commonCls.sendView(this.id, {"action":"multidatabase_view_main_init",'content_id':content_id});
				commonCls.sendPost(this.id, {"action":"multidatabase_action_main_mail"}, {"loading_el":null});
		}.bind(this);
		params["callbackfunc_error"] = function(file_list, res){
			form_el.temporary_flag.value = 0;
			// エラー時(File)
			if (!res.match(this.errorSeparator)) {
				commonCls.alert(res);
				return;
			}

			var resArray = res.split(this.errorSeparator);
			var element_id = resArray.shift();

			res = resArray.join("\n");
			commonCls.alert(res);
			$(element_id + this.id).focus();
		}.bind(this);
		commonCls.sendAttachment(params);
	},
	searchMdb: function(form_el) {
		commonCls.sendView(this.id, "action=multidatabase_view_main_search_result&" + Form.serialize(form_el));
	},
	importCsv: function (event, this_el, multidatabase_id) {
		var top_el = $(this.id);
		var params = new Object();
		params["param"] = {'action': "multidatabase_action_edit_uploadcsv", "multidatabase_id": multidatabase_id};
		params["top_el"] = top_el;
		params["method"] = "post";
		params["loading_el"] = top_el;
		params['form_prefix'] = "mdb_import_attachment";
		params["callbackfunc"] = function(res){
			var msg_div = $('mdb_import_success_result'+this.id);
			commonCls.alert(msg_div.innerHTML);
			$('mdb_import'+this.id).value = null;
			commonCls.removeBlock(this.id);
		}.bind(this);
		params["callbackfunc_error"] = function(file_list, res){
			// エラー時(File)
			commonCls.alert(res);
		}.bind(this);
		commonCls.sendAttachment(params);
	},
	changeTitle: function(title_el) {
		var require_el = $("mdb_metadata_require_flag" + this.id);
		var inputtype_el = $("mdb_inputtype" + this.id);
		if (inputtype_el.value == "7" || inputtype_el.value == "10" || inputtype_el.value == "11") {
			require_el.disabled = true;
			Element.addClassName(require_el.parentNode, "disable_lbl");
			return;
		}
		if(title_el.checked) {
			require_el.checked = true;
			require_el.disabled = true;
			Element.addClassName(require_el.parentNode, "disable_lbl");
		}else {
			require_el.disabled = false;
			Element.removeClassName(require_el.parentNode, "disable_lbl");
		}
	},
	setList: function(list_el) {
		var meta_type_el = $('mdb_inputtype' + this.id);
		var meta_type = meta_type_el.options[meta_type_el.selectedIndex];
		var sort_el = $("mdb_metadata_sort_flag" + this.id);
		if(list_el.checked) {
			sort_el.disabled = false;
			Element.removeClassName(sort_el.parentNode, "disable_lbl");
		}else {
			sort_el.checked = false;
			sort_el.disabled = true;
			Element.addClassName(sort_el.parentNode, "disable_lbl");
		}
	},
	showDataSeqPop: function(event, multidatabase_id) {
		var param_popup = new Object();
		param_popup = {
			"action":"multidatabase_view_main_sequence",
			"multidatabase_id":multidatabase_id,
			"prefix_id_name":"multidatabase_sequence_popup"
		};

		var params = new Object();
		params['top_el'] = $(this.id);
		params['modal_flag'] = true;
		commonCls.sendPopupView(event, param_popup, params);
	},
	closeDataSeqPop: function(form_el) {
		var top_el = $(this.id);
		var params = new Object();
		params['top_el'] = top_el;
		params["callbackfunc"] = function(res){
			commonCls.removeBlock(this.id);
			commonCls.sendView(this.id.replace("_multidatabase_sequence_popup",""), {'action':'multidatabase_view_main_init','sort_metadata':'seq'});
		}.bind(this);
		commonCls.sendPost(this.id, "action=multidatabase_action_main_chgseq&" + Form.serialize(form_el), params);
	},
	changeSequence: function(drag_id, drop_id, position) {
		var post = {
			"action":"multidatabase_action_main_sequence",
			"drag_content_id":drag_id.match(/\d+/)[0],
			"drop_content_id":drop_id.match(/\d+/)[0],
			"position":position
		};

		commonCls.sendPost(this.id, post);
	},
	submitPassword: function(event, form_el) {
		if (event.keyCode == 13) {
			this.submitPasswordAction(form_el);
			return false;
		}
		return true;
	},
	submitPasswordAction: function(form_el) {
		var params = new Object();
		params["callbackfunc"] = function(res){
			var upload_id = form_el.upload_id.value;
			var metadata_id = form_el.metadata_id.value;
			var password = form_el.password.value;
			commonCls.removeBlock(this.id);
			this.downloadFile(upload_id, metadata_id, password);
		}.bind(this);
		commonCls.sendPost(this.id, "action=multidatabase_action_main_filedownload&" + Form.serialize(form_el), params);
	},
	downloadFile: function(upload_id, metadata_id, password) {
		var str = _nc_base_url + _nc_index_file_name + '?action=multidatabase_action_main_filedownload&download_flag=1&upload_id='+upload_id+'&metadata_id='+metadata_id;
		if(password != "") {
			str += '&password='+password;
		}
		window.location = str;
		this.setDownloadCount(upload_id);
	},
	setDownloadCount: function(upload_id) {
		var block_id = null;
		if(this.id.indexOf("_mdb_popup_password") != -1) {
			block_id = this.id.replace("_mdb_popup_password","");
		}else {
			block_id = this.id;
		}

		var count_el = $("mdb_file_download_count_" + upload_id + block_id);
		if(count_el) {
			var download_count = parseInt(count_el.innerHTML.match(/\d+/)[0]) + 1;
			count_el.innerHTML = count_el.innerHTML.replace(/\d+/, download_count);
		}
	},
	chkAuth: function() {
		var moderate_el = $("mdb_contents_authority3" + this.id);
		if(moderate_el.checked) {
			$(this.id + "_mdb_contents_comment_setting0").disabled = false;
			$(this.id + "_mdb_contents_comment_setting1").disabled = false;
			Element.removeClassName($(this.id + "_mdb_contents_comment_setting_label0"), "disable_lbl");
			Element.removeClassName($(this.id + "_mdb_contents_comment_setting_label1"), "disable_lbl");
		}else {
			commonCls.displayNone($(this.id + '_mdb_contents_comment_setting_detail'));
			$(this.id + "_mdb_contents_comment_setting1").disabled = true;
			$(this.id + "_mdb_contents_comment_setting0").checked = true;
			$(this.id + "_mdb_contents_comment_setting0").disabled = true;
			Element.addClassName($(this.id + "_mdb_contents_comment_setting_label0"), "disable_lbl");
			Element.addClassName($(this.id + "_mdb_contents_comment_setting_label1"), "disable_lbl");
		}
	},

	changeOldUse: function(use) {
		if (use) {
			$("multidatabase_old" + this.id).disabled = false;
		} else {
			$("multidatabase_old" + this.id).disabled = true;
		}
	}

}