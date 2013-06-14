var clsQuestionnaire = Class.create();
var questionnaireCls = new Array();

clsQuestionnaire.prototype = {
	initialize: function(id) {
		this.id = id;

		this.currentQuestionnaireID = null;
		this.questionnaire_id = null;
		this.question_id = null;

		this.question = null;
		this.description = null;
		this.titleIcon = null;
		this.color = null;
		this.calendar = null;

		this.choiceIteration = 0;
	},

	checkCurrent: function() {
		var currentRow = $("questionnaire_current_row" + this.currentQuestionnaireID + this.id);
		if (!currentRow) {
			return;
		}
		Element.addClassName(currentRow, "highlight");

		var current = $("questionnaire_current" + this.currentQuestionnaireID + this.id);
		current.checked = true;
	},

	changeCurrent: function(questionnaireID,confirmMessage) {
		var oldCurrentRow = $("questionnaire_current_row" + this.currentQuestionnaireID  + this.id);
		if (oldCurrentRow) {
			Element.removeClassName(oldCurrentRow, "highlight");
		}

		var startElement = $("questionnaire_operate_start" + questionnaireID + this.id);
		if (!(Element.hasClassName(startElement, "display-none"))) {
			if (!commonCls.confirm(confirmMessage)) {
				this.checkCurrent();
				return false;
			}
			Element.addClassName(startElement, "display-none");
			Element.removeClassName($("questionnaire_operate_stop" + questionnaireID + this.id), "display-none");
		}

		var currentRow = $("questionnaire_current_row" + questionnaireID + this.id);
		this.currentQuestionnaireID = questionnaireID;

		Element.addClassName(currentRow, "highlight");

		var post = {
			"action":"questionnaire_action_edit_questionnaire_current",
			"questionnaire_id":questionnaireID
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
											commonCls.alert(res);
											commonCls.sendView(this.id, "questionnaire_view_edit_questionnaire_list");
										}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	startQuestion: function(questionnaireID,confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;

		var post = {
			"action":"questionnaire_action_edit_question_current",
			"questionnaire_id":questionnaireID
		};
		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc_error"] = function(res){
											commonCls.alert(res);
											commonCls.sendView(this.id, "questionnaire_view_edit_question_list");
										}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},


	_changeStatus: function(questionnaire_id, status, params) {
		var post = {
			"action":"questionnaire_action_edit_questionnaire_status",
			"questionnaire_id":questionnaire_id,
			"status":status
		};

		var params = new Object();
		params["callbackfunc_error"] = function(res){
											commonCls.alert(res);
											commonCls.sendView(this.id, "questionnaire_view_edit_questionnaire_list");
										}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	start: function(questionnaire_id, status, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;

		Element.addClassName($("questionnaire_operate_start" + questionnaire_id + this.id), "display-none");
		Element.removeClassName($("questionnaire_operate_stop" + questionnaire_id + this.id), "display-none");

		this._changeStatus(questionnaire_id, status);
	},

	stop: function(questionnaire_id, status, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;

		Element.addClassName($("questionnaire_operate_stop" + questionnaire_id + this.id), "display-none");
		Element.removeClassName($("questionnaire_operate_end" + questionnaire_id + this.id), "display-none");

		this._changeStatus(questionnaire_id, status);
	},

	showIcon: function() {
		if (this.titleIcon == null || (this.titleIcon.popup.getPopupElement()).contentWindow == null) {
			this.titleIcon = new compTitleIcon(this.id);
		}
		this.titleIcon.showDialogBox($("questionnaire_icon_name_img" + this.id), $("questionnaire_icon_name_hidden" + this.id));
	},

	changeMailSend: function(send) {
		if (send) {
			Element.removeClassName($("questionnaire_mail_send_content" + this.id), "display-none");
		} else {
			Element.addClassName($("questionnaire_mail_send_content" + this.id), "display-none");
		}
	},

	changeOldUse: function(use) {
		if (use) {
			$("questionnaire_old" + this.id).disabled = false;
		} else {
			$("questionnaire_old" + this.id).disabled = true;
		}
	},

	selectQuestionnaireTypeList: function() {
		var element = $("questionnaire_type_random" + this.id);
		element.checked = false;
		element.disabled = true;
		Element.addClassName($("questionnaire_type_random_label" + this.id), "disable_lbl");
	},

	selectQuestionnaireTypeSequence: function() {
		$("questionnaire_type_random" + this.id).disabled = false;
		Element.removeClassName($("questionnaire_type_random_label" + this.id), "disable_lbl");
	},

	changePeriod: function() {
		this.calendar.disabledCalendar(!$("questionnaire_period_checkbox" + this.id).checked);
	},

	changeNonmember: function(accept) {
		if (accept) {
			var element = $("questionnaire_repeat" + this.id);
			element.checked = true;
			element.disabled = true;
			Element.addClassName($("questionnaire_repeat_label" + this.id), "disable_lbl");

			var element = $("questionnaire_image_authentication" + this.id);
			if (element) {
				element.disabled = false;
				Element.removeClassName($("questionnaire_image_authentication_label" + this.id), "disable_lbl");
			}
			element = $("questionnaire_keypass_use_flag" + this.id);
			if (element) {
				element.disabled = false;
				Element.removeClassName($("questionnaire_keypass_use_flag_label" + this.id), "disable_lbl");
				this.changeKeypass(element.checked);
			}
		} else {
			$("questionnaire_repeat" + this.id).disabled = false;
			$("questionnaire_repeat" + this.id).checked = false;

			Element.removeClassName($("questionnaire_repeat_label" + this.id), "disable_lbl");

			var element = $("questionnaire_image_authentication" + this.id);
			if (element) {
				element.checked = false;
				element.disabled = true;
				Element.addClassName($("questionnaire_image_authentication_label" + this.id), "disable_lbl");
			}
			element = $("questionnaire_keypass_use_flag" + this.id);
			if (element) {
				element.checked = false;
				element.disabled = true;
				Element.addClassName($("questionnaire_keypass_use_flag_label" + this.id), "disable_lbl");
				this.changeKeypass(element.checked);
			}
		}
	},

	changeKeypass: function(keypass) {
		if(keypass) {
			if ($("questionnaire_keypass_phrase" + this.id)) {
				$("questionnaire_keypass_phrase" + this.id).disabled = false;
			}
		}
		else {
			if ($("questionnaire_keypass_phrase" + this.id)) {
				$("questionnaire_keypass_phrase" + this.id).disabled = true;
			}
		}
	},

	changeAnonymity: function(anonymity) {
		if (anonymity) {
			this.changeMailSend(!anonymity);

			$("questionnaire_mail_send_on" + this.id).disabled = true;
			var element = $("questionnaire_mail_send_off" + this.id);
			element.checked = true;
			element.disabled = true;
			Element.addClassName($("questionnaire_mail_send_on_label" + this.id), "disable_lbl");
			Element.addClassName($("questionnaire_mail_send_off_label" + this.id), "disable_lbl");
		} else {
			$("questionnaire_mail_send_on" + this.id).disabled = false;
			$("questionnaire_mail_send_off" + this.id).disabled = false;
			Element.removeClassName($("questionnaire_mail_send_on_label" + this.id), "disable_lbl");
			Element.removeClassName($("questionnaire_mail_send_off_label" + this.id), "disable_lbl");
		}
	},

	deleteQuestionnaire: function(questionnaire_id, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;

		var post = {
			"action":"questionnaire_action_edit_questionnaire_delete",
			"questionnaire_id":questionnaire_id
		};

		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc_error"] = function(res){
											commonCls.sendView(this.id, "questionnaire_view_edit_questionnaire_list");
										}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	enterQuestionnaire: function() {
		var questionnaireForm = $("questionnaire_form" + this.id);
		var post = Form.serialize(questionnaireForm);

		if (questionnaireForm["questionnaire_name"].disabled) {
			post += "&questionnaire_name=" + encodeURIComponent(questionnaireForm["questionnaire_name"].value);
		}

		commonCls.sendPost(this.id, post, {"target_el":$(this.id)});
	},

	_getPopupParams: function() {
		var params = new Object();
		var top_el = $(this.id);
		params['top_el'] = top_el;
		params['target_el'] = top_el;
		params['center_flag'] = true;

		return params;
	},

	showQuestionEntry: function(event, question_id) {
		var params = new Object();
		params["action"] = "questionnaire_view_edit_question_entry";
		params["questionnaire_id"] = this.questionnaire_id;
		params["question_id"] = question_id;
		params["prefix_id_name"] = "questionnaire_question";

		commonCls.sendPopupView(event, params, this._getPopupParams());
	},

	createQuestionArea: function() {
		if (this.question == null) {
			this.question = new compTextarea();
			this.question.uploadAction = {
				image:"questionnaire_action_edit_question_upload_image",
				file:"questionnaire_action_edit_question_upload_attachment"
			};
		}

		this.question.focus = true;
		this.question.textareaEditShow(this.id, $("questionnaire_question_value" + this.id));
	},

	changeDescription: function(description) {
		if (description) {
			Element.removeClassName($("questionnaire_description_area" + this.id), "display-none");

			if (this.description == null) {
				this.description = new compTextarea();
				this.description.uploadAction = {
					image:"questionnaire_action_edit_question_upload_image",
					file:"questionnaire_action_edit_question_upload_attachment"
				};
			}
			this.description.textareaEditShow(this.id, $("questionnaire_description" + this.id));
			this.description.focusEditor();
		} else {
			Element.addClassName($("questionnaire_description_area" + this.id), "display-none");
		}
	},

	changeQuestionType: function(type) {
		var choiceArea = $("questionnaire_choice_area" + this.id);
		if (type == $("questionnaire_question_type_textarea" + this.id).value) {
			Element.addClassName(choiceArea, "display-none");
			return;
		} else {
			Element.removeClassName(choiceArea, "display-none");
		}

		if (type == $("questionnaire_question_type_radio" + this.id).value) {
			var radioFunction = Element.removeClassName;
			var checkFunction = Element.addClassName;
		} else {
			var radioFunction = Element.addClassName;
			var checkFunction = Element.removeClassName;
		}

		var elements = choiceArea.getElementsByTagName("label");
		for (var i = 0; i < elements.length; i++) {
			if (elements[i].firstChild.type == "radio") {
				radioFunction(elements[i], "display-none");
			} else {
				checkFunction(elements[i], "display-none");
			}
		}
	},

	showGraph: function(iteration) {
		if (this.color == null) {
			this.color = new compColor(this.id);
		}
		this.color.showDialogBox($("questionnaire_graph" + iteration + this.id), $("questionnaire_graph_hidden" + iteration + this.id));
	},

	addChoice: function() {
		var params = new Object();
		var top_el = $(this.id);

		params["param"] = {
			"action":"questionnaire_view_edit_choice_add",
			"questionnaire_id":this.questionnaire_id,
			"choice_iteration":this.choiceIteration
		};

		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = $("questionnaire_choice_add" + this.id);
		params["callbackfunc"] = function(res){
										var choice = $("questionnaire_choice" + this.id);
										choice.firstChild.appendChild($("questionnaire_choice_add" + this.id).firstChild.firstChild.firstChild);
										var textareas = choice.getElementsByTagName("textarea");
										commonCls.focus(textareas[textareas.length - 1]);
										this.choiceIteration++;
									}.bind(this);

		commonCls.send(params);
	},

	deleteChoice: function(element, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;
		var choice = $("questionnaire_choice" + this.id);
		choice.firstChild.removeChild(element.parentNode.parentNode);
	},

	enterQuestion: function() {
		var questionnaireForm = $("questionnaire_question_form" + this.id);
		var post = Form.serialize(questionnaireForm);

		if (this.question) {
			post += "&question_value=" + encodeURIComponent(this.question.getTextArea());
		}

		if (questionnaireForm["description_checkbox"].checked) {
			post += "&description=" + encodeURIComponent(this.description.getTextArea());
		}

		if (questionnaireForm["question_type"].disabled) {
			post += "&question_type=" + encodeURIComponent(questionnaireForm["question_type"].value);
		}

		var params = new Object();
		params["callbackfunc"] = function(res){
										var params = new Object();
										params["action"] = "questionnaire_view_edit_question_list";
										params["questionnaire_id"] = this.questionnaire_id;
										commonCls.sendView(this.id.replace("_questionnaire_question", ""), params);
										commonCls.removeBlock(this.id);
									}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	changeQuestionSequence: function(drag_id, drop_id, position) {
		var post = {
			"action":"questionnaire_action_edit_question_sequence",
			"questionnaire_id":this.questionnaire_id,
			"drag_question_id":drag_id.match(/\d+/)[0],
			"drop_question_id":drop_id.match(/\d+/)[0],
			"position":position
		};

		commonCls.sendPost(this.id, post);
	},

	deleteQuestion: function(question_id, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;

		var post = {"action":"questionnaire_action_edit_question_delete",
					"questionnaire_id":this.questionnaire_id,
					"question_id":question_id
					};

		var params = new Object();
		params["callbackfunc"] = function(res){
										commonCls.sendView(this.id, {"action":"questionnaire_view_edit_question_list", "questionnaire_id":this.questionnaire_id});
									}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	mailSend: function() {
		var params = {
			"method":"post",
			"param":{"action":"questionnaire_action_main_mail"},
			"top_el":$(this.id)
		};

		commonCls.send(params);
	},

	answer: function(isAfterConfirm) {
		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc"] = function(res){
									location.href = "#" + this.id;
									this.mailSend();
								}.bind(this);

		if (isAfterConfirm) {
			var post = {
				"action":"questionnaire_action_main_answer",
				"questionnaire_id":this.questionnaire_id
			};
			commonCls.sendPost(this.id, post, params);
		} else {
			commonCls.sendPost(this.id, Form.serialize($("questionnaire_answer_form" + this.id)), params);
		}
	},

	changeAnswerArea: function(question_id) {
		var element = $("questionnaire_answer_area" + question_id + this.id);
		if (Element.hasClassName(element, "display-none")) {
			Element.removeClassName(element, "display-none")
		} else {
			Element.addClassName(element, "display-none")
		}
	},

	showSummaryList: function(event, questionnaire_id, answer_user_id) {
		var params = new Object();
		params["action"] = "questionnaire_view_main_summary";
		params["questionnaire_id"] = questionnaire_id;
		params["answer_user_id"] = answer_user_id;
		params["prefix_id_name"] = "questionnaire_summary_list";
		params["theme_name"] = "system";

		commonCls.sendPopupView(event, params, this._getPopupParams());
	},

	showAnswer: function(event, summary_id) {
		var params = new Object();
		params["action"] = "questionnaire_view_main_answer";
		params["questionnaire_id"] = this.questionnaire_id;
		params["summary_id"] = summary_id;
		params["prefix_id_name"] = "questionnaire_answer" + summary_id;
		if (event != null) {
			params["target_id_name"] = this.id;
		}
		params["theme_name"] = "system";

		commonCls.sendPopupView(event, params, this._getPopupParams());
	},

	showTotal: function(event, questionnaire_id) {
		var params = new Object();
		params["action"] = "questionnaire_view_main_total";
		params["questionnaire_id"] = questionnaire_id;
		params["prefix_id_name"] = "questionnaire_total" + questionnaire_id;
		params["theme_name"] = "system";

		commonCls.sendPopupView(event, params, this._getPopupParams());
	},

	showReference: function(event, questionnaire_id) {
		var params = new Object();
		params["action"] = "questionnaire_view_edit_reference";
		params["questionnaire_id"] = questionnaire_id;
		params["prefix_id_name"] = "questionnaire_reference";

		commonCls.sendPopupView(event, params, this._getPopupParams());
	},

	showQuestionaryAnswer: function(event, question_id) {
		var params = new Object();
		params["action"] = "questionnaire_view_edit_answer";
		params["questionnaire_id"] = this.questionnaire_id;
		params["question_id"] = question_id;
		params["prefix_id_name"] = "questionnaire_questionary_answer" + question_id;

		commonCls.sendPopupView(event, params, this._getPopupParams());
	}
}