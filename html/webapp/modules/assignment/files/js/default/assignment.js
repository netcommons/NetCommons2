var clsAssignment = Class.create();
var assignmentCls = Array();

clsAssignment.prototype = {
	initialize: function(id) {
		this.id = id;

		this.assignment_id = null;
		this.yet_submitter = null;

		this.titleIcon = null;
		this.textarea = null;
		this.calendar = null;
		this.liveGrid = null;
	},

	/*
	 * Show the title icon
	 */
	showIcon: function() {
		if (this.titleIcon == null || (this.titleIcon.popup.getPopupElement()).contentWindow == null) {
			this.titleIcon = new compTitleIcon(this.id);
		}
		this.titleIcon.showDialogBox($("assignment_icon_name_img" + this.id), $("assignment_icon_name_hidden" + this.id));
	},

	/*
	 * Display of the report form.
	 */
	showReportForm: function(event){
		var params = new Object();
		params["action"] = "assignment_view_main_submit";
		params["prefix_id_name"] = "popup_assignment_report";
		params["assignment_id"] = this.assignment_id;
		params["theme_name"] = "system";

		commonCls.sendPopupView(event, params, {"top_el":$(this.id), "modal_flag":true, "center_flag":true});
	},

	/*
	 * Display of the modify form.
	 */
	showModifyForm: function(submit_id, report_id){
		var params = new Object();
		params["action"] = "assignment_view_main_modify";
		params["assignment_id"] = this.assignment_id;
		params["submit_id"] = submit_id;
		params["report_id"] = report_id;

		commonCls.sendView(this.id, params, {"target_el":$("assignment_reporter_view"+this.id)});
	},

	/*
	 * Submit of the report.
	 */
	submitReport: function(id, form_el, temporary) {
		var report_body = this.textarea.getTextArea();

		var params_str = Form.serialize(form_el);
		params_str += (temporary !== null) ? "&temporary=" + temporary : "";
		params_str += "&report_body=" + encodeURIComponent(report_body);

		var post = new Object();
		post["callbackfunc"] = function(res){
			var mail_post = new Object();
			mail_post["callbackfunc"] = function(res){
				if (id != this.id) {
					commonCls.removeBlock(this.id);
				}
				commonCls.sendView(id, "assignment_view_main_init");
			}.bind(this);
			mail_post["callbackfunc_error"] = function(res){
				if (id != this.id) {
					commonCls.removeBlock(this.id);
				}
				commonCls.sendView(id, "assignment_view_main_init");
			}.bind(this);
			commonCls.sendPost(this.id, {"action":"assignment_action_main_mail"}, mail_post);
		}.bind(this);

		commonCls.sendPost(this.id, params_str, post);
	},

	/*
	 * Modify of the report.
	 */
	modifyReport: function(form_el, temporary) {
		var report_body = this.textarea.getTextArea();

		var params_str = Form.serialize(form_el);
		params_str += (temporary !== null) ? "&temporary=" + temporary : "";
		params_str += "&report_body=" + encodeURIComponent(report_body);

		var post = new Object();
		post["callbackfunc"] = function(res){
			commonCls.sendPost(this.id, {"action":"assignment_action_main_mail"});
		}.bind(this);
		post["target_el"] = $(this.id);

		commonCls.sendPost(this.id, params_str, post);
	},

	/*
	 * Delete of the report.
	 */
	deleteReport: function(submit_id, report_id, confirm_mes) {
		if (!commonCls.confirm(confirm_mes)) { return; }

		var params = {
			"action":"assignment_action_main_delete",
			"assignment_id":this.assignment_id,
			"submit_id":submit_id,
			"report_id":report_id
		};

		commonCls.sendPost(this.id, params, {"target_el":$(this.id)});
	},

	/*
	 * Show of the report.
	 */
	showOtherReport: function(submit_id, popup, event) {
		var params = new Object();
		params["action"] = "assignment_view_main_init";
		params["assignment_id"] = this.assignment_id;
		params["submit_id"] = submit_id;
		if (popup == "1") {
			params["prefix_id_name"] = "popup_assignment_submitter" + this.assignment_id;
			commonCls.sendPopupView(event, params, {"top_el":$(this.id), "center_flag":true});
		} else {
			commonCls.sendView(this.id, params, {"target_el":$(this.id)});
		}
	},

	/*
	 * Show of the past report.
	 */
	showPastReport: function(submit_id, report_id) {
		var params = new Object();
		params["action"] = "assignment_view_main_report";
		params["assignment_id"] = this.assignment_id;
		params["submit_id"] = submit_id;
		params["report_id"] = report_id;

		commonCls.sendView(this.id, params, {"target_el":$("assignment_reporter_view"+this.id)});
	},

	/*
	 * The comment is written.
	 */
	writeComment: function(form_el) {
		var params_str = "action=assignment_action_main_comment_write" +
							"&" + Form.serialize(form_el);

		commonCls.sendPost(this.id, params_str, {"target_el":$("assignment_comment_view"+this.id)});
	},

	/*
	 * The comment edit screen is displayed.
	 */
	showEditComment: function(comment_id) {
		var form_el = $("assignment_comment_write_form" + this.id);

		if (!Element.hasClassName(form_el["write_btn"], "display-none")) {
			Element.addClassName(form_el["write_btn"], "display-none");
		}
		if (Element.hasClassName(form_el["edit_btn"], "display-none")) {
			Element.removeClassName(form_el["edit_btn"], "display-none");
		}
		if (Element.hasClassName(form_el["cancel_btn"], "display-none")) {
			Element.removeClassName(form_el["cancel_btn"], "display-none");
		}
		form_el["comment_id"].value = comment_id;

		var el = $("assignment_comment_value" + comment_id + this.id);

		form_el["comment_value"].value = el.innerHTML.replace(/\n/ig,"").replace(/(<br(?:.|\s|\/)*?>)/ig,"\n").unescapeHTML();
	},

	/*
	 * The comment is edited.
	 */
	editComment: function(form_el) {
		var params_str = "action=assignment_action_main_comment_edit" +
							"&" + Form.serialize(form_el);

		commonCls.sendPost(this.id, params_str, {"target_el":$("assignment_comment_view"+this.id)});
	},

	/*
	 * Delete of the comment.
	 */
	deleteComment: function(submit_id, report_id, comment_id, confirm_mes) {
		if (!commonCls.confirm(confirm_mes)) { return; }

		var params = {
			"action":"assignment_action_main_comment_delete",
			"assignment_id":this.assignment_id,
			"submit_id":submit_id,
			"report_id":report_id,
			"comment_id":comment_id
		};

		commonCls.sendPost(this.id, params, {"target_el":$("assignment_comment_view"+this.id)});
	},

	/*
	 * The comment edit is canceled.
	 */
	cancelComment: function(form_el) {
		if (Element.hasClassName(form_el["write_btn"], "display-none")) {
			Element.removeClassName(form_el["write_btn"], "display-none");
		}
		if (!Element.hasClassName(form_el["edit_btn"], "display-none")) {
			Element.addClassName(form_el["edit_btn"], "display-none");
		}
		if (!Element.hasClassName(form_el["cancel_btn"], "display-none")) {
			Element.addClassName(form_el["cancel_btn"], "display-none");
		}
		form_el["comment_id"].value = "";
		form_el["comment_value"].value = "";
	},

	/*
	 * Display of the grade form.
	 */
	showGradeForm: function(event, submit_id){
		var params = new Object();
		params["action"] = "assignment_view_main_grade";
		params["prefix_id_name"] = "popup_assignment_grade";
		params["assignment_id"] = this.assignment_id;
		params["submit_id"] = submit_id;
		params["theme_name"] = "system";

		commonCls.sendPopupView(event, params, {"top_el":$(this.id), "modal_flag":true});
	},

	/*
	 * Grade of the report.
	 */
	gradeReport: function(id, form_el) {
		var post = new Object();
		post["callbackfunc"] = function(res){
			commonCls.sendView(id, "assignment_view_main_init");
			commonCls.removeBlock(this.id);
		}.bind(this);

		commonCls.sendPost(this.id, Form.serialize(form_el), post);
	},

	/*
	 * Display of the summary.
	 */
	showSummary: function(event, assignment_id){
		var params = new Object();
		params["action"] = "assignment_view_main_summary";
		params["prefix_id_name"] = "popup_assignment_summary" + assignment_id;
		params["assignment_id"] = assignment_id;
		params["theme_name"] = "system";

		commonCls.sendPopupView(event, params, {"top_el":$(this.id)});
	},

	/*
	 * Display of the yet submitters.
	 */
	showSubmitterList: function(event, assignment_id, yet_submitter){
		var params = this._paramsSubmitList(yet_submitter, "assignment_view_main_submitters");
		params["assignment_id"] = assignment_id;
		params["prefix_id_name"] = "popup_assignment_submitters" + assignment_id;

		commonCls.sendPopupView(event, params, {"top_el":$(this.id)});
	},
	showYetSubmitted: function(yet_submitter, popup){
		var params = this._paramsSubmitList(yet_submitter, "assignment_view_main_submitters");
		if (popup == "1") {
			var target_el = this.id;
		} else {
			var target_el = "assignment_reporter_view"+this.id;
		}
		commonCls.sendView(this.id, params, {"target_el":$(target_el)});
	},
	backSubmitters: function(yet_submitter){
		var params = this._paramsSubmitList(yet_submitter, "assignment_view_main_init");

		commonCls.sendView(this.id, params, {"target_el":$(this.id)});
	},
	_paramsSubmitList: function(yet_submitter, action_name){
		var params = new Object();
		params["action"] = action_name;
		params["assignment_id"] = this.assignment_id;
		if (yet_submitter || yet_submitter === "0") {
			params["yet_submitter"] = yet_submitter;
			this.yet_submitter = yet_submitter;
		} else {
			params["yet_submitter"] = this.yet_submitter;
		}
		return params
	},

	/*
	 * Setting the graded label.
	 */
	setGradeValue: function(form_el) {
		var post = new Object();
		post["callbackfunc"] = function(res){
			commonCls.sendView(this.id, "assignment_view_main_init");
		}.bind(this);

		commonCls.sendPost(this.id, Form.serialize(form_el), post);
	},


	/*
	 * Display of edit list.
	 */
	checkCurrent: function() {
		var currentRow = $("assignment_row_tr" + this.assignment_id + this.id);
		if (!currentRow) {
			return;
		}
		Element.addClassName(currentRow, "highlight");

		var current = $("assignment_row" + this.assignment_id + this.id);
		current.checked = true;
	},
	changeCurrent: function(assignment_id, message) {
		var oldCurrentRow = $("assignment_row_tr" + this.assignment_id + this.id);
		if (oldCurrentRow) {
			Element.removeClassName(oldCurrentRow, "highlight");
		}

		var startElement = $("assignment_operate_start" + assignment_id + this.id);
		if (!(Element.hasClassName(startElement, "display-none"))) {
			if (!commonCls.confirm(message)) {
				this.checkCurrent();
				return false;
			}
			Element.addClassName(startElement, "display-none");
			Element.removeClassName($("assignment_operate_end" + assignment_id + this.id), "display-none");
			var post = {
				"action":"assignment_action_edit_current",
				"assignment_id":assignment_id,
				"activity":1
			};
		} else {
			var post = {
				"action":"assignment_action_edit_current",
				"assignment_id":assignment_id
			};
		}

		this.assignment_id = assignment_id;
		var currentRow = $("assignment_row_tr" + this.assignment_id + this.id);
		Element.addClassName(currentRow, "highlight");


		var params = new Object();
		params["callbackfunc_error"] = function(res){
			commonCls.sendView(this.id, "assignment_view_edit_list");
		}.bind(this);

		commonCls.sendPost(this.id, post, params);
		return true;
	},
	changeActivity: function(element, assignmentID, activity, message) {
		if (!commonCls.confirm(message)) return false;

		var elements = element.parentNode.childNodes;
		for (var i = 0, length = elements.length; i < length; i++) {
			if (elements[i] == element) {
				Element.addClassName(elements[i], "display-none");
			} else {
				Element.removeClassName(elements[i], "display-none");
			}
		}

		var post = {
			"action":"assignment_action_edit_activity",
			"assignment_id":assignmentID,
			"activity":activity
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
			commonCls.alert(res);
			commonCls.sendView(this.id, "assignment_view_edit_list");
		}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	/*
	 * Set of an assignment.
	 */
	setAssignment: function(form_el, activity) {
		var assignment_body = this.textarea.getTextArea();

		var params_str = Form.serialize(form_el);
		params_str += (activity !== null) ? "&activity=" + activity : "";
		params_str += "&assignment_body=" + encodeURIComponent(assignment_body);

		commonCls.sendPost(this.id, params_str, {"target_el":$(this.id)});
	},

	/*
	 * Refer of the assignment.
	 */
	referAssignment: function(event, assignment_id){
		var params = new Object();
		params["action"] = "assignment_view_main_init";
		params["prefix_id_name"] = "popup_assignment_reference" + assignment_id;
		params["assignment_id"] = assignment_id;
		params["display_hide"] = 1;
		params["theme_name"] = "system";

		commonCls.sendPopupView(event, params, {"top_el":$(this.id)});
	},

	/*
	 * Delete of the assignment.
	 */
	deleteAssignment: function(assignment_id, confirm_mes) {
		if (!commonCls.confirm(confirm_mes)) { return; }

		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc_error"] = function(res){
			commonCls.sendView(this.id, "assignment_view_edit_list");
		}.bind(this);

		commonCls.sendPost(this.id, "action=assignment_action_edit_delete&assignment_id=" + assignment_id, params);
	}
}