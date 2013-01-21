var clsBbs = Class.create();
var bbsCls = new Array();

clsBbs.prototype = {
	initialize: function(id) {
		this.id = id;

		this.currentBbsID = null;
		this.bbs_id = null;
		this.post_id = null;
		this.body = null;
		this.titleIcon = null;

		this._current_post_id = null;
	},

	checkCurrent: function() {
		var currentRow = $("bbs_current_row" + this.currentBbsID + this.id);
		if (!currentRow) {
			return;
		}
		Element.addClassName(currentRow, "highlight");

		var current = $("bbs_current" + this.currentBbsID + this.id);
		current.checked = true;
	},

	changeCurrent: function(bbsID) {
		var oldCurrentRow = $("bbs_current_row" + this.currentBbsID + this.id);
		if (oldCurrentRow) {
			Element.removeClassName(oldCurrentRow, "highlight");
		}

		this.currentBbsID = bbsID;
		var currentRow = $("bbs_current_row" + this.currentBbsID + this.id);
		Element.addClassName(currentRow, "highlight");

		var post = {
			"action":"bbs_action_edit_current",
			"bbs_id":bbsID
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
											commonCls.alert(res);
											commonCls.sendView(this.id, "bbs_view_edit_list");
										}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	changeActivity: function(element, bbsID, activity) {
		var elements = element.parentNode.childNodes;
		for (var i = 0, length = elements.length; i < length; i++) {
			if (elements[i] == element) {
				Element.addClassName(elements[i], "display-none");
			} else {
				Element.removeClassName(elements[i], "display-none");
			}
		}

		var post = {
			"action":"bbs_action_edit_activity",
			"bbs_id":bbsID,
			"activity":activity
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
											commonCls.alert(res);
											commonCls.sendView(this.id, "bbs_view_edit_list");
										}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	referenceBbs: function(event, bbsID, prefixID) {
		var params = new Object();
		params["action"] = "bbs_view_main_init";
		params["bbs_id"] = bbsID;
		params["prefix_id_name"] = prefixID;

		var popupParams = new Object();
		var top_el = $(this.id);
		popupParams['top_el'] = top_el;
		popupParams['target_el'] = top_el;
		popupParams['center_flag'] = true;

		commonCls.sendPopupView(event, params, popupParams);
	},

	deleteBbs: function(bbsID, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;

		var post = {
			"action":"bbs_action_edit_delete",
			"bbs_id":bbsID
		};

		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc_error"] = function(res){
											commonCls.sendView(this.id, "bbs_view_edit_list");
										}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	changeMailSend: function(send) {
		if (send) {
			Element.removeClassName($("bbs_mail_send_content" + this.id), "display-none");
		} else {
			Element.addClassName($("bbs_mail_send_content" + this.id), "display-none");
		}
	},

	showList: function(param) {
		var params = new Object();
		params["action"] = "bbs_view_main_init";
		params["bbs_id"] = this.bbs_id;
		Object.extend(params, param);
		commonCls.sendView(this.id, params);
	},

	changeExpand: function(expand) {
		if (this.post_id === null) {
			this.showList({"expand":expand});
		} else {
			this.showPostList(expand);
		}
	},

	showPostList: function(expand) {
		var params = new Object();
		var top_el = $(this.id);

		params["param"] = {
			"action":"bbs_view_main_post_list",
			"bbs_id":this.bbs_id,
			"post_id":this.post_id
		};
		if (expand) params["param"]["expand"] = expand;

		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = $("bbs_post_list" + this.id);
		params["callbackfunc"] = this.selectPostRow.bind(this);
		commonCls.send(params);
	},

	selectPostRow: function() {
		var current = $("bbs_post_row" + this.post_id + this.id);
		if (current == null) {
			return;
		}

		var top_el = $(this.id);
		var params = new Object();

		params["param"] = {
			"action":"bbs_view_main_post_move",
			"bbs_id":this.bbs_id,
			"post_id":this.post_id
		};
		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = $("bbs_move" + this.id);
		commonCls.send(params);

		Element.addClassName(current, "highlight");
		if (Element.hasClassName(current, "bbs_read")) {
			return;
		}
		Element.addClassName(current, "bbs_read");
	},

	showPost: function(post_id) {
		if (this.post_id === null) {
			var params = new Object();
			params["action"] = "bbs_view_main_post";
			params["bbs_id"] = this.bbs_id;
			params["post_id"] = post_id;

			commonCls.sendView(this.id, params);
		} else {
			this.showBody(post_id);
		}
	},

	showBody: function(post_id) {
		var current = $("bbs_post_row" + this._current_post_id + this.id);
		Element.removeClassName(current, "highlight");
		this._current_post_id = post_id;

		var params = new Object();
		var top_el = $(this.id);
		params["param"] = {
			"action":"bbs_view_main_post_body",
			"bbs_id":this.bbs_id,
			"post_id":post_id,
			"nomobile":1
		};
		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = $("bbs_post" + this.id);
		params["focus_flag"] = true;
		params["callbackfunc"] = this.selectPostRow.bind(this);
		params["func_param"] = post_id;

		commonCls.send(params);

		var post = {
			"action":"bbs_action_main_read",
			"bbs_id":this.bbs_id,
			"post_id":post_id
		}
		commonCls.sendPost(this.id, post);
	},
	showPostArea: function() {
		var postForm = $("bbs_post_form" + this.id);
		Element.removeClassName(postForm, "display-none");
		if ((browser.isFirefox || browser.isSafari) && this.body != null) {
			var new_textarea = document.createElement("textarea");
			Element.addClassName(new_textarea, "comptextarea");
			new_textarea.setAttribute("name","body",1);
			this.body.top_table.parentNode.insertBefore(new_textarea, this.body.top_table);
			Element.remove(this.body.top_table);
		}
		if (this.body == null || browser.isFirefox || browser.isSafari) {
			this.body = new compTextarea();
			this.body.uploadAction = {
				image:"bbs_action_main_upload_image",
				file:"bbs_action_main_upload_attachment"
			};
		}

		var quote = $("bbs_quote" + this.id);
		if (quote != null && quote.checked) {
			if (browser.isFirefox) {
				var subject = $("bbs_subject" + this.id).textContent;
			} else {
				var subject = $("bbs_subject" + this.id).innerText;
			}

			var re_count = 0;

			var re_match = subject.match(new RegExp("^(Re:){1,}"));
			if (re_match) {
				re_count = re_match[0].length / 3;
				subject = subject.gsub(/Re:/, "");
			}

			var re_match = subject.match(new RegExp("^(Re([0-9]+):)"));
			if (re_match) {
				re_count += valueParseInt(re_match[2]);
				subject = subject.replace(new RegExp(re_match[0]), "");
			}
			if (re_count > 0) {
				postForm["subject"].value = "Re" + (re_count + 1) + ":" + subject;
			} else {
				postForm["subject"].value = "Re:" + subject;
			}

			var body = $("bbs_body" + this.id);
			var user_wrote = $("bbs_user_wrote" + this.id);
			postForm["body"].value = "<br class=\"bbs_quote\" />" +
										"<blockquote class=\"quote\">" +
										user_wrote.value.escapeHTML() + "<br />" +
										body.innerHTML +
										"</blockquote>" +
										"<div> </div>";
		} else if (quote != null){
			postForm["subject"].value = "";
			postForm["body"].value = "";
		}
		this.body.setTextArea(postForm["body"].value);
		this.body.textareaEditShow(this.id, postForm["body"]);
		$("bbs_cancel" + this.id).focus();
		postForm["subject"].focus();
	},

	showEditPost: function() {
		var top_el = $(this.id);
		var params = new Object();

		params["param"] = {
			"action":"bbs_view_main_post_edit",
			"bbs_id":this.bbs_id,
			"post_id":this.post_id
		};
		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = $("bbs_post" + this.id);

		commonCls.send(params);
	},

	showIcon: function() {
		if (this.titleIcon == null || (this.titleIcon.popup.getPopupElement()).contentWindow == null) {
			this.titleIcon = new compTitleIcon(this.id);
		}
		this.titleIcon.showDialogBox($("bbs_icon_name_img" + this.id), $("bbs_icon_name_hidden" + this.id));
	},

	_post: function(temporary, parent_id, errorCallback) {
		var params = {
			"target_el":$(this.id),
			"focus_flag":true,
			"callbackfunc":function(res){
								commonCls.sendPost(this.id, {"action":"bbs_action_main_mail"}, {"loading_el":null});
							}.bind(this),
			"callbackfunc_error":errorCallback
		};

		var postForm = $("bbs_post_form" + this.id);
		var post = {
			"action":"bbs_action_main_post",
			"bbs_id":this.bbs_id,
			"post_id":this.post_id,
			"icon_name":postForm["icon_name"].value,
			"subject":postForm["subject"].value,
			"body":this.body.getTextArea(),
			"temporary":temporary
		};
		if (parent_id) {
			post["parent_id"] = parent_id;
		}

		commonCls.sendPost(this.id, post, params);
	},

	post: function(temporary) {
		this.post_id = null;
		this._post(temporary);
	},

	reply: function(temporary) {
		var postID = this.post_id;
		this.post_id = null;
		var errorCallback = function(res){
								this.post_id = postID;
								commonCls.alert(res);
							}.bind(this)
		this._post(temporary, postID, errorCallback);
	},

	editPost: function(temporary) {
		this._post(temporary);
	},

	deletePost: function(confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;

		var post = {
			"action":"bbs_action_main_delete",
			"bbs_id":this.bbs_id,
			"post_id":this.post_id
		};

		commonCls.sendPost(this.id, post, {"target_el":$(this.id),"focus_flag":true});
	},

	vote: function() {
		var post = {
			"action":"bbs_action_main_vote",
			"bbs_id":this.bbs_id,
			"post_id":this.post_id
		};

		commonCls.sendPost(this.id, post, {"target_el":$(this.id)});
	}
}