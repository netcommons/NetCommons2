var clsJournal = Class.create();
var journalCls = Array();

clsJournal.prototype = {
	/**
	 * 初期処理
	 *
	 * @param	id	ID
	 * @return  none
	 **/
	initialize: function(id) {
		this.id = id;
		this.currentJournalId = null;
		this.textarea = null;
		this.textarea_more = null;
		this.pager = null;
		this.category_id =null;
		this.visible_item = null;
		this.inItems = new Object();
		this.inUpdItems = new Object();
		this.popupLnk = null;
		this.dndCustomDrag = null;
		this.dndCustomDropzone = null;
		this.dndMgrCatObj = null;
		this.titleIcon = null;
	},

	initCategory: function(journal_id) {
		// ドラッグ
		this.dndCustomDrag = Class.create();
		this.dndCustomDrag.prototype = Object.extend((new compDraggable), {
			prestartDrag: function() {
				var htmlElement = this.getHTMLElement();
				this._displayChg(htmlElement);
			},
			cancelDrag: function() {
				var draggable = this.htmlElement;
				Element.setStyle(draggable, {opacity:""});
				this._displayChg(draggable, 1);
   			},
			_displayChg: function(htmlElement, cancel_flag) {
				if(Element.hasClassName(htmlElement, "_journal_cat" + this.id)) {
			    	var paramObj= this.getParams();
			    	var el= paramObj['top_el'];
			    	var cat_fields = Element.getElementsByClassName(el, "_journal_cat" + this.id);
					cat_fields.each(function(cat_el) {
						commonCls.displayChange(cat_el);
					}.bind(this));
					if(cancel_flag) {
						var top_row_el = Element.getChildElementByClassName(htmlElement, "_journal_cat" + this.id);
						commonCls.displayVisible(top_row_el);
					}
				}
			}
		});

		// ドロップ
		this.dndCustomDropzone = Class.create();
		this.dndCustomDropzone.prototype = Object.extend((new compDropzone), {
			showHover: function(event) {
				var htmlElement = this.getHTMLElement();
				if ( this._showHover(htmlElement) )
					return;
				if(this.getParams() && !Element.hasClassName(htmlElement, "_journal_cat_top")) {
					this.showChgSeqHoverInside(event);
				} else {
					//inside
					this.showChgSeqHover(event);
				}
			},

			hideHover: function(event) {
				this.hideChgSeqHover(event);
			},

			accept: function(draggableObjects) {
				this.acceptChgSeq(draggableObjects);
				var drag_el = draggableObjects[0].getHTMLElement();	// ドラッグ対象エレメント
				commonCls.blockNotice(null, drag_el);
			},

			save: function(draggableObjects) {
				if(this.ChgSeqPosition == null) {
					return false;
				}
				var htmlElement = this.getHTMLElement();
				var paramObj= draggableObjects[0].getParams();
				var id= paramObj['id'];
				var drag_el = draggableObjects[0].getHTMLElement();	// ドラッグ対象エレメント

				var chgseq_params = new Object();
				var top_el = $(id);

				if (Element.hasClassName(drag_el, "_journal_cat_top")) {
					var category_id = drag_el.id.replace("_journal_cat"+ id + "_category_id_","");

					// 表示順変更
					var drop_category_id = htmlElement.id.replace("_journal_cat"+ id + "_category_id_","");
					chgseq_params["param"] = {
						"action":"journal_action_edit_categoryseq",
						"journal_id":journal_id,
						"category_id":category_id,
						"drop_category_id":drop_category_id,
						"position":this.ChgSeqPosition
					};
				}
				chgseq_params["method"] = "post";
				chgseq_params["top_el"] = top_el;
				chgseq_params["loading_el"] = drag_el;
				commonCls.send(chgseq_params);
				return true;
			}
		});

		var edit_top_el = $("_journal_cat"+ this.id);
		this.dndMgrCatObj = new compDragAndDrop();
		this.dndMgrCatObj.registerDraggableRange(edit_top_el);

		this.dndMgrLinkObj = new compDragAndDrop();
		this.dndMgrLinkObj.registerDraggableRange(edit_top_el);

		var cat_rowfields = Element.getElementsByClassName(edit_top_el, "_journal_cat_chg_seq");
		cat_rowfields.each(function(row_el) {
			var top_row_el = Element.getParentElementByClassName(row_el,"_journal_cat_top");
			this.dndMgrLinkObj.registerDraggable(new this.dndCustomDrag(top_row_el, row_el, {"top_el":edit_top_el,"id":this.id}));
			this.dndMgrLinkObj.registerDropZone(new this.dndCustomDropzone(top_row_el));
		}.bind(this));
	},
	checkCurrent: function() {
		var currentRow = $("journal_current_row" + this.currentJournalId + this.id);
		if (!currentRow) {
			return;
		}
		Element.addClassName(currentRow, "highlight");

		var current = $("journal_current" + this.currentJournalId + this.id);
		current.checked = true;
	},
	//日誌選択
	changeCurrent: function(journal_id) {
		var oldCurrentRow = $("journal_current_row" + this.currentJournalId + this.id);
		if (oldCurrentRow) {
			Element.removeClassName(oldCurrentRow, "highlight");
		}

		this.currentJournalId = journal_id;
		var currentRow = $("journal_current_row" + this.currentJournalId + this.id);
		Element.addClassName(currentRow, "highlight");

		var post = {
			"action":"journal_action_edit_change",
			"journal_id":journal_id
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
			commonCls.sendView(this.id, "journal_view_edit_list");
		}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},
	changeActivity: function(element, journal_id, activity) {
		var elements = element.parentNode.childNodes;
		for (var i = 0, length = elements.length; i < length; i++) {
			if (elements[i] == element) {
				Element.addClassName(elements[i], "display-none");
			} else {
				Element.removeClassName(elements[i], "display-none");
			}
		}
		var post = {
			"action":"journal_action_edit_activity",
			"journal_id":journal_id,
			"active_flag":activity
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
			commonCls.sendView(this.id, "journal_view_edit_list");
		}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},
	editCancel: function() {
		commonCls.sendView(this.id,"journal_view_main_init");
	},
	referenceJournal: function(event, journal_id) {
		var params = new Object();
		params["action"] = "journal_view_main_init";
		params["journal_id"] = journal_id;
		params["prefix_id_name"] = "popup_journal_reference" + journal_id;

		var popupParams = new Object();
		var top_el = $(this.id);
		popupParams['top_el'] = top_el;
		popupParams['target_el'] = top_el;
		popupParams['center_flag'] = true;

		commonCls.sendPopupView(event, params, popupParams);
	},
	//日誌削除
	delJournal: function(journal_id, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) {
			return false;
		}
		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc_error"] = function(res){
			commonCls.sendView(this.id, "journal_view_edit_list");
		}.bind(this);
		commonCls.sendPost(this.id, "action=journal_action_edit_delete&journal_id=" + journal_id, params);
	},
	//日誌追加
	insJournal: function(form_el) {
		commonCls.sendPost(this.id, "action=journal_action_edit_create&" + Form.serialize(form_el), {"target_el":$(this.id)});
	},
	styleEdit: function(form_el) {
		commonCls.sendPost(this.id, "action=journal_action_edit_style&" + Form.serialize(form_el), {"target_el":$(this.id)});
	},
	//編集画面初期処理
	postInit: function() {
		//テキストエリア
		this.textarea = new compTextarea();
		this.textarea.uploadAction = {
			//unique_id   : 0,
			image    : "journal_action_upload_image",
			file     : "journal_action_upload_init"
		};
		this.textarea.textareaShow(this.id, "textarea"+this.id, "full");

		//カレンダー
		new compCalendar(this.id, "journal_date" + this.id);
	},
	moreInit: function() {
		//テキストエリア;
		if(this.textarea_more == null) {
			this.textarea_more = new compTextarea();
			this.textarea_more.uploadAction = {
				//unique_id   : 0,
				image    : "journal_action_upload_image",
				file     : "journal_action_upload_init"
			};
		}
		this.textarea_more.textareaShow(this.id, "textarea_more"+this.id, "full");
	},
	showIcon: function() {
		this.titleIcon = new compTitleIcon(this.id);
		this.titleIcon.showDialogBox($("journal_icon_name_img" + this.id), $("journal_icon_name_hidden" + this.id));
		this.titleIcon == null;
	},
	//日誌登録
	post: function(form_el, temp_flag) {
		var top_el = $(this.id);
		var journal_id = form_el.journal_id.value;
		var journal_date = form_el.journal_date.value;
		var journal_hour = form_el.journal_hour.value;
		var journal_minute = form_el.journal_minute.value;
		var icon_name = form_el.icon_name.value;
		var title = form_el.title.value;
		var category_id = form_el.category_id.value;
		var content = this.textarea.getTextArea();
		var more_checked = 0;
		if(form_el.more_checkbox.checked) {
			more_checked = 1;
		}
		var more_title = null;
		var more_content = null;
		var hide_more_title = null;
		if(this.textarea_more != null) {
			more_title = form_el.more_title.value;
			more_content = this.textarea_more.getTextArea();
			hide_more_title = form_el.hide_more_title.value;
		}
		var edit_flag = form_el.edit_flag.value;
		var post_id = form_el.post_id.value;
		var tb_url = "";
		if(form_el.tb_url) {
			tb_url = form_el.tb_url.value;
		}
		//パラメータ設定
		var ins_params = new Object();
		ins_params["method"] = "post";
		ins_params["param"] = {
			"action":"journal_action_main_post",
			"journal_id":journal_id,
			"journal_date":journal_date,
			"journal_hour":journal_hour,
			"journal_minute":journal_minute,
			"icon_name":icon_name,
			"title":title,
			"category_id":category_id,
			"content":content,
			"more_checked":more_checked,
			"more_title":more_title,
			"more_content":more_content,
			"hide_more_title":hide_more_title,
			"edit_flag":edit_flag,
			"post_id":post_id,
			"temp_flag":temp_flag,
			"tb_url":tb_url
		};
		ins_params["top_el"] = top_el;
		ins_params["loading_el"] = top_el;
		ins_params["target_el"] = top_el;
		ins_params["callbackfunc"] = function(){
			commonCls.sendPost(this.id, {"action":"journal_action_main_mail"}, {"loading_el":null});
		}.bind(this);
		ins_params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
		}.bind(this);
		commonCls.send(ins_params);
	},
	checkMore: function(check_el, confirmMessage) {
		if(check_el.checked == true) {
			commonCls.displayChange($('journal_more_content' + this.id));
			this.moreInit();
		}else {
			if (!commonCls.confirm(confirmMessage)) {
				check_el.checked = true;
				return false;
			}
			commonCls.displayChange($('journal_more_content' + this.id));
		}
	},
	//日誌コメント登録
	postComment: function(form_el) {
		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc"] = function(res){
			commonCls.sendPost(this.id, {"action":"journal_action_main_mail"}, {"loading_el":null});
		}.bind(this);
		commonCls.sendPost(this.id, "action=journal_action_main_comment&" + Form.serialize(form_el), params);
	},
	postConfirm: function(el, post_id, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) {
			return false;
		}

		var params = new Object();
		params["loading_el"] = el;
		params["target_el"] = $(this.id);
		params["callbackfunc"] = function(res){
			commonCls.sendPost(this.id, {"action":"journal_action_main_mail"}, {"loading_el":null});
		}.bind(this);
		commonCls.sendPost(this.id, "action=journal_action_main_confirm&post_id=" + post_id, params);
	},
	toPage: function(el, journal_id, cat_classname, num_classname, now_page, position) {
		var cat_name = null;
		var num_name = null;
		if(position == "bottom") {
			cat_name = cat_classname + "_bottom";
			num_name = num_classname + "_bottom";
		}else {
			cat_name = cat_classname;
			num_name = num_classname;
		}
		var cat = $(cat_name);
		var visible_item = $(num_name);
		var catlist = document.getElementsByClassName(cat_classname);
		var cats = $A(catlist);
		cats.each(function(c){
			c.selectedIndex = cat.selectedIndex;
		});
		var numlist = document.getElementsByClassName(num_classname);
		var nums = $A(numlist);
		nums.each(function(n){
			n.selectedIndex = visible_item.selectedIndex;
		});
		var cat_id = cat.options[cat.selectedIndex].value;
		var num = visible_item.options[visible_item.selectedIndex].value;
		var journal_id = $("journal_id"+this.id).value;
		var top_el = $(this.id);
		var params = new Object();

		params["param"] = {
			"action":"journal_view_main_init",
			"journal_id":journal_id,
			"category_id":cat_id,
			"visible_item":num,
			"now_page":now_page
		};
		params["top_el"] = top_el;
		params["loading_el"] = el;
		params["target_el"] = top_el;
		commonCls.send(params);
	},
	deleteComment: function(el, comment_id, post_id, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) {
			return false;
		}
		var params = new Object();
		params["target_el"] = $(this.id);
		params["loading_el"] = el;
		params["callbackfunc"] = function(res){
			commonCls.displayVisible($('journal_comment' + this.id));
		}.bind(this);
		commonCls.sendPost(this.id, "action=journal_action_main_delete&post_id=" + post_id + "&comment_id=" + comment_id, params);
	},
	deleteTrackback: function(el, trackback_id, post_id, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) {
			return false;
		}
		var params = new Object();
		params["target_el"] = $(this.id);
		params["loading_el"] = el;
		params["callbackfunc"] = function(res){
			commonCls.displayVisible($('journal_trackback' + this.id));
		}.bind(this);
		commonCls.sendPost(this.id, "action=journal_action_main_deltrackback&post_id=" + post_id + "&trackback_id=" + trackback_id, params);
	},
	editComment: function(comment_id, post_id) {
		var div_content = $("journal_comment_content_" + comment_id + this.id);
		var textbox = $("journal_comment_textarea" + this.id);
		textbox.value = div_content.innerHTML.replace(/\n/ig,"").replace(/(<br(?:.|\s|\/)*?>)/ig,"\n").unescapeHTML();
		var hidden_flag = $("comment_post_id" + this.id);
		hidden_flag.value = comment_id;
		textbox.focus();
		textbox.select();
	},
	deletePost: function(el, post_id, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) {
			return false;
		}
		var params = new Object();
		params["target_el"] = $('journal_detail'+this.id+'_'+post_id);
		params["loading_el"] = el;
		commonCls.sendPost(this.id, "action=journal_action_main_delete&post_id=" + post_id, params);
	},
	vote: function(el, post_id) {
		var params = new Object();
		params["top_el"] = $(this.id);
		params["loading_el"] = el;
		params["callbackfunc"] = function(res){
			commonCls.sendRefresh(this.id);
		}.bind(this);
		params["callbackfunc_error"] = function(res){
			commonCls.sendView(this.id, "journal_view_main_init");
		}.bind(this);
		commonCls.sendPost(this.id, "action=journal_action_main_vote&post_id=" + post_id, params);
	},
	focusItem: function(item_id, focus_flag) {
		this.inItems[item_id] = focus_flag;
	},
	updItems: function(event, this_el, journal_id, category_id) {
		var top_el = $(this.id);

		var edit_el = Element.getParentElementByClassName(this_el,"journal_cat_edit_item");
		var label_el = edit_el.previousSibling;

		var upd_params = new Object();
		upd_params['action'] = "journal_action_edit_category";
		upd_params['journal_id'] = journal_id;
		upd_params['category_id'] = category_id;
		upd_params['category_name'] = this_el.value;
		if(this.inUpdItems[upd_params['category_id']] == true) {
			// 更新中
			return;
		}
		this.inUpdItems[upd_params['category_id']] = true;
		var send_param = new Object();
		send_param["method"] = "post";
		send_param["param"] = upd_params;
		send_param["top_el"] = top_el;
		send_param["callbackfunc"] = function(res){
			// 正常終了
			commonCls.displayNone(edit_el);
			commonCls.displayVisible(label_el);
			label_el.innerHTML = upd_params['category_name'].escapeHTML();
			if(Event.element(event).type == "radio" || Event.element(event).type == "select-one"
				 || Event.element(event).type == "button" || event.keyCode == 13) {
				label_el.focus();
			}
			this.inUpdItems[upd_params['category_id']] = false;
		}.bind(this);
		send_param["callbackfunc_error"] = function(res){
			// エラー(File以外)
			commonCls.alert(res);
			this_el.focus();
			this.inUpdItems[upd_params['category_id']] = false;
		}.bind(this);
		commonCls.send(send_param);
	},
	clkItems: function(this_el) {
		var edit_el = this_el.nextSibling;
		commonCls.displayNone(this_el);
		commonCls.displayVisible(edit_el);
		var input_el = Element.getChildElement(edit_el);
		input_el.focus();
		input_el.select();
	},
	delCategory: function(journal_id, category_id, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) {
			return false;
		}

		var top_el = $(this.id);
		var del_params = new Object();
		del_params["param"] = {
			"action":"journal_action_edit_categorydel",
			"journal_id":journal_id,
			"category_id":category_id
		};
		del_params["callbackfunc"] = function(){
			commonCls.sendView(this.id,{"action":"journal_view_edit_category","journal_id":journal_id});
		}.bind(this);
		del_params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
			commonCls.sendView(this.id,"journal_view_edit_list");
		}.bind(this);
		del_params["method"] = "post";
		del_params["loading_el"] = top_el;
		del_params["top_el"] = top_el;
		commonCls.send(del_params);
	},
	showAddPopup: function(this_el) {
		if(this.popupLnk == null || !$(this.popupLnk.popupID)) {
			this.popupLnk = new compPopup(this.id, "addCategory" + this.id);
			this.popupLnk.loadObserver = function() {
				commonCls.focus(this.popupLnk.popupElement.contentWindow.document.getElementsByTagName("form")[0]);
			}.bind(this);
		}
		this.popupLnk.showPopup(this.popupLnk.getPopupElementByEvent(this_el), this_el);
	},
	addCategory: function() {
		var top_el = $(this.id);
		var form = this.popupLnk.popupElement.contentWindow.document.getElementsByTagName("form")[0];
		var add_params = new Object();
		add_params["param"] = "journal_action_edit_categoryadd" + "&"+ Form.serialize(form);
		add_params["callbackfunc"] = function(){
			this.popupLnk.closePopup();
			commonCls.sendView(this.id,{"action":"journal_view_edit_category","journal_id":form.journal_id.value});
		}.bind(this);
		add_params["callbackfunc_error"] = function(res){
			if(res.match("^(category_name):")) {
				var mesArr = res.split(":");
				//メッセージ表示
				commonCls.alert(mesArr[1]);
				//フォーカスの移動
				form.content.focus();
				form.content.select();
			} else {
				commonCls.alert(res);
			}
		}.bind(this);
		add_params["method"] = "post";
		add_params["loading_el"] = top_el;
		add_params["top_el"] = top_el;
		commonCls.send(add_params);
	},
	showEditPost: function(el, post_id) {
		var top_el = $(this.id);
		var params = new Object();

		params["param"] = {
			"action":"journal_view_main_post",
			"post_id":post_id
		};
		params["top_el"] = top_el;
		params["loading_el"] = el;
		params["target_el"] = top_el;

		commonCls.send(params);
	},
	chkAuth: function() {
		var moderate_el = $("journal_post_authority3" + this.id);
		if(moderate_el.checked) {
			$(this.id + "_journal_post_agree0").disabled = false;
			$(this.id + "_journal_post_agree1").disabled = false;
			Element.removeClassName($(this.id + "_journal_post_agree_label0"), "disable_lbl");
			Element.removeClassName($(this.id + "_journal_post_agree_label1"), "disable_lbl");
		}else {
			commonCls.displayNone($(this.id + '_journal_post_agree_mail_flag'));
			$(this.id + "_journal_post_agree0").disabled = true;
			$(this.id + "_journal_post_agree1").checked = true;
			$(this.id + "_journal_post_agree1").disabled = true;
			Element.addClassName($(this.id + "_journal_post_agree_label0"), "disable_lbl");
			Element.addClassName($(this.id + "_journal_post_agree_label1"), "disable_lbl");
		}
	},
	chkCommentOrTrackback: function() {
		var comment_el = $(this.id + "_journal_comment1");
		var trackback_el = $(this.id + "_journal_trackback0");
		if(comment_el.checked || trackback_el.checked) {
			$(this.id + "_journal_comment_agree0").disabled = false;
			$(this.id + "_journal_comment_agree1").disabled = false;
			Element.removeClassName($(this.id + "_journal_comment_agree_label0"), "disable_lbl");
			Element.removeClassName($(this.id + "_journal_comment_agree_label1"), "disable_lbl");
		}else {
			var comment_no_el = $(this.id + "_journal_comment0");
			if(comment_no_el.checked && !trackback_el.checked) {
				commonCls.displayNone($(this.id + '_journal_comment_agree_mail_flag'));
				$(this.id + "_journal_comment_agree0").disabled = true;
				$(this.id + "_journal_comment_agree1").checked = true;
				$(this.id + "_journal_comment_agree1").disabled = true;
				Element.addClassName($(this.id + "_journal_comment_agree_label0"), "disable_lbl");
				Element.addClassName($(this.id + "_journal_comment_agree_label1"), "disable_lbl");
			}
		}
	}
}