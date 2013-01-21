var clsQuiz = Class.create();
var quizCls = new Array();

clsQuiz.prototype = {
	initialize: function(id) {
		this.id = id;

		this.currentQuizID = null;
		this.quiz_id = null;
		this.question_id = null;

		this.question = null;
		this.description = null;
		this.titleIcon = null;
		this.color = null;
		this.calendar = null;

		this.choiceIteration = 0;
	},

	checkCurrent: function() {
		var currentRow = $("quiz_current_row" + this.currentQuizID + this.id);
		if (!currentRow) {
			return;
		}
		Element.addClassName(currentRow, "highlight");

		var current = $("quiz_current" + this.currentQuizID + this.id);
		current.checked = true;
	},

	changeCurrent: function(quizID,confirmMessage) {
		var oldCurrentRow = $("quiz_current_row" + this.currentQuizID + this.id);
		if (oldCurrentRow) {
			Element.removeClassName(oldCurrentRow, "highlight");
		}

		var startElement = $("quiz_operate_start" + quizID + this.id);
		if (!(Element.hasClassName(startElement, "display-none"))) {
			if (!commonCls.confirm(confirmMessage)) {
				this.checkCurrent();
				return false;
			}
			Element.addClassName(startElement, "display-none");
			Element.removeClassName($("quiz_operate_stop" + quizID + this.id), "display-none");
		}

		this.currentQuizID = quizID;
		var currentRow = $("quiz_current_row" + this.currentQuizID + this.id);
		Element.addClassName(currentRow, "highlight");

		var post = {
			"action":"quiz_action_edit_quiz_current",
			"quiz_id":quizID
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res){
											commonCls.alert(res);
											commonCls.sendView(this.id, "quiz_view_edit_list");
										}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	startQuiz: function(quizID,confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;

		var post = {
			"action":"quiz_action_edit_question_current",
			"quiz_id":quizID
		};
		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc_error"] = function(res){
											commonCls.alert(res);
											commonCls.sendView(this.id, "quiz_view_edit_question_list");
										}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	_changeStatus: function(quiz_id, status, params) {
		var post = {
			"action":"quiz_action_edit_quiz_status",
			"quiz_id":quiz_id,
			"status":status
		};

		var params = new Object();
		params["callbackfunc_error"] = function(res){
											commonCls.alert(res);
											commonCls.sendView(this.id, "quiz_view_edit_quiz_list");
										}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	start: function(quiz_id, status, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;

		Element.addClassName($("quiz_operate_start" + quiz_id + this.id), "display-none");
		Element.removeClassName($("quiz_operate_stop" + quiz_id + this.id), "display-none");

		this._changeStatus(quiz_id, status);
	},

	stop: function(quiz_id, status, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;

		Element.addClassName($("quiz_operate_stop" + quiz_id + this.id), "display-none");
		Element.removeClassName($("quiz_operate_end" + quiz_id + this.id), "display-none");

		this._changeStatus(quiz_id, status);
	},

	showIcon: function() {
		if (this.titleIcon == null || (this.titleIcon.popup.getPopupElement()).contentWindow == null) {
			this.titleIcon = new compTitleIcon(this.id);
		}
		this.titleIcon.showDialogBox($("quiz_icon_name_img" + this.id), $("quiz_icon_name_hidden" + this.id));
	},

	changeMailSend: function(send) {
		if (send) {
			Element.removeClassName($("quiz_mail_send_content" + this.id), "display-none");
		} else {
			Element.addClassName($("quiz_mail_send_content" + this.id), "display-none");
		}
	},

	changeOldUse: function(use) {
		if (use) {
			$("quiz_old" + this.id).disabled = false;
		} else {
			$("quiz_old" + this.id).disabled = true;
		}
	},

	selectQuizTypeList: function() {
		var element = $("quiz_type_random" + this.id);
		element.checked = false;
		element.disabled = true;
		$("quiz_correct" + this.id).disabled = false;

		Element.addClassName($("quiz_type_random_label" + this.id), "disable_lbl");
		Element.removeClassName($("quiz_correct_label" + this.id), "disable_lbl");
	},

	selectQuizTypeSequence: function() {
		$("quiz_type_random" + this.id).disabled = false;
		var element = $("quiz_correct" + this.id);
		element.checked = true;
		element.disabled = true;

		Element.removeClassName($("quiz_type_random_label" + this.id), "disable_lbl");
		Element.addClassName($("quiz_correct_label" + this.id), "disable_lbl");
	},

	changePeriod: function() {
		this.calendar.disabledCalendar(!$("quiz_period_checkbox" + this.id).checked);
	},

	changeNonmember: function(accept) {
		if (accept) {
			var element = $("quiz_repeat" + this.id);
			element.checked = true;
			element.disabled = true;
			Element.addClassName($("quiz_repeat_label" + this.id), "disable_lbl");

			var element = $("quiz_image_authentication" + this.id);
			if (element) {
				element.disabled = false;
				Element.removeClassName($("quiz_image_authentication_label" + this.id), "disable_lbl");
			}
		} else {
			$("quiz_repeat" + this.id).disabled = false;
			Element.removeClassName($("quiz_repeat_label" + this.id), "disable_lbl");

			var element = $("quiz_image_authentication" + this.id);
			if (element) {
				element.checked = false;
				element.disabled = true;
				Element.addClassName($("quiz_image_authentication_label" + this.id), "disable_lbl");
			}
		}
	},

	deleteQuiz: function(quiz_id, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;

		var post = {
			"action":"quiz_action_edit_quiz_delete",
			"quiz_id":quiz_id
		};

		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc_error"] = function(res){
											commonCls.sendView(this.id, "quiz_view_edit_quiz_list");
										}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	enterQuiz: function() {
		var quizForm = $("quiz_form" + this.id);
		var post = Form.serialize(quizForm);

		if (quizForm["quiz_name"].disabled) {
			post += "&quiz_name=" + encodeURIComponent(quizForm["quiz_name"].value);
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
		params["action"] = "quiz_view_edit_question_entry";
		params["quiz_id"] = this.quiz_id;
		params["question_id"] = question_id;
		params["prefix_id_name"] = "quiz_question";

		commonCls.sendPopupView(event, params, this._getPopupParams());
	},

	createQuestionArea: function() {
		if (this.question == null) {
			this.question = new compTextarea();
			this.question.uploadAction = {
				image:"quiz_action_edit_question_upload_image",
				file:"quiz_action_edit_question_upload_attachment"
			};
		}

		this.question.focus = true;
		this.question.textareaEditShow(this.id, $("quiz_question_value" + this.id));
	},

	changeDescription: function(description) {
		if (description) {
			Element.removeClassName($("quiz_description_area" + this.id), "display-none");

			if (this.description == null) {
				this.description = new compTextarea();
				this.description.uploadAction = {
					image:"quiz_action_edit_question_upload_image",
					file:"quiz_action_edit_question_upload_attachment"
				};
			}
			this.description.textareaEditShow(this.id, $("quiz_description" + this.id));
			this.description.focusEditor();
		} else {
			Element.addClassName($("quiz_description_area" + this.id), "display-none");
		}
	},

	changeQuestionType: function(type) {
		var choiceArea = $("quiz_choice_area" + this.id);
		var wordArea = $("quiz_choice_word_area" + this.id);
		if (type == $("quiz_question_type_textarea" + this.id).value) {
			Element.addClassName(choiceArea, "display-none");
			Element.addClassName(wordArea, "display-none");
			return;
		} else if (type == $("quiz_question_type_word" + this.id).value) {
			Element.addClassName(choiceArea, "display-none");
			Element.removeClassName(wordArea, "display-none");
			return;
		} else {
			Element.removeClassName(choiceArea, "display-none");
			Element.addClassName(wordArea, "display-none");
		}

		if (type == $("quiz_question_type_radio" + this.id).value) {
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
		this.color.showDialogBox($("quiz_graph" + iteration + this.id), $("quiz_graph_hidden" + iteration + this.id));
	},

	addChoice: function(type) {
		var params = new Object();
		var top_el = $(this.id);

		params["param"] = {
			"action":"quiz_view_edit_choice_add",
			"quiz_id":this.quiz_id,
			"choice_iteration":this.choiceIteration
		};
		if (type == $("quiz_question_type_word" + this.id).value) {
			params["param"]["type"] = $("quiz_question_type_word" + this.id).value;
		}
		this.choiceIteration++;

		params["top_el"] = top_el;
		params["loading_el"] = top_el;
		params["target_el"] = $("quiz_choice_add" + this.id);
		params["callbackfunc"] = function(res){
										if (type == $("quiz_question_type_word" + this.id).value) {
											var choice = $("quiz_choice_word" + this.id);
											choice.firstChild.appendChild($("quiz_choice_add" + this.id).firstChild.firstChild.firstChild);
											var inputs = choice.getElementsByTagName("input");
											commonCls.focus(inputs[inputs.length - 1]);
										} else {
											var choice = $("quiz_choice" + this.id);
											choice.firstChild.appendChild($("quiz_choice_add" + this.id).firstChild.firstChild.firstChild);
											var textareas = choice.getElementsByTagName("textarea");
											commonCls.focus(textareas[textareas.length - 1]);
										}
									}.bind(this);

		commonCls.send(params);
	},

	deleteChoice: function(element, confirmMessage, type) {
		if (!commonCls.confirm(confirmMessage)) return false;
		if (type == $("quiz_question_type_word" + this.id).value) {
			var choice = $("quiz_choice_word" + this.id);
		} else {
			var choice = $("quiz_choice" + this.id);
		}

		choice.firstChild.removeChild(element.parentNode.parentNode);
	},

	enterQuestion: function() {
		var quizForm = $("quiz_question_form" + this.id);
		var post = Form.serialize(quizForm);

		if (this.question) {
			post += "&question_value=" + encodeURIComponent(this.question.getTextArea());
		}

		if (quizForm["description_checkbox"].checked) {
			post += "&description=" + encodeURIComponent(this.description.getTextArea());
		}

		if (quizForm["allotment"].disabled) {
			post += "&allotment=" + encodeURIComponent(quizForm["allotment"].value);
		}

		if (quizForm["question_type"].disabled) {
			post += "&question_type=" + encodeURIComponent(quizForm["question_type"].value);
		}

		var params = new Object();
		params["callbackfunc"] = function(res){
										var params = new Object();
										params["action"] = "quiz_view_edit_question_list";
										params["quiz_id"] = this.quiz_id;
										commonCls.sendView(this.id.replace("_quiz_question", ""), params);
										commonCls.removeBlock(this.id);
									}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	changeQuestionSequence: function(drag_id, drop_id, position) {
		var post = {
			"action":"quiz_action_edit_question_sequence",
			"quiz_id":this.quiz_id,
			"drag_question_id":drag_id.match(/\d+/)[0],
			"drop_question_id":drop_id.match(/\d+/)[0],
			"position":position
		};

		commonCls.sendPost(this.id, post);
	},

	deleteQuestion: function(question_id, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;

		var post = {"action":"quiz_action_edit_question_delete",
					"quiz_id":this.quiz_id,
					"question_id":question_id
					};

		var params = new Object();
		params["callbackfunc"] = function(res){
										commonCls.sendView(this.id, {"action":"quiz_view_edit_question_list", "quiz_id":this.quiz_id});
									}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	mailSend: function() {
		var params = {
			"method":"post",
			"param":{"action":"quiz_action_main_mail"},
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
				"action":"quiz_action_main_answer",
				"quiz_id":this.quiz_id
			};
			commonCls.sendPost(this.id, post, params);
		} else {
			commonCls.sendPost(this.id, Form.serialize($("quiz_answer_form" + this.id)), params);
		}
	},

	changeAnswerArea: function(question_id) {
		var element = $("quiz_answer_area" + question_id + this.id);
		if (Element.hasClassName(element, "display-none")) {
			Element.removeClassName(element, "display-none")
		} else {
			Element.addClassName(element, "display-none")
		}
	},

	showSummaryList: function(event, quiz_id, answer_user_id) {
		var params = new Object();
		params["action"] = "quiz_view_main_summary";
		params["quiz_id"] = quiz_id;
		params["answer_user_id"] = answer_user_id;
		params["prefix_id_name"] = "quiz_summary_list";
		params["theme_name"] = "system";

		commonCls.sendPopupView(event, params, this._getPopupParams());
	},

	showAnswer: function(event, summary_id) {
		var params = new Object();
		params["action"] = "quiz_view_main_answer";
		params["quiz_id"] = this.quiz_id;
		params["summary_id"] = summary_id;
		params["prefix_id_name"] = "quiz_answer" + summary_id;
		if (event != null) {
			params["target_id_name"] = this.id;
		}
		params["theme_name"] = "system";

		commonCls.sendPopupView(event, params, this._getPopupParams());
	},

	showTotal: function(event, quiz_id) {
		var params = new Object();
		params["action"] = "quiz_view_main_total";
		params["quiz_id"] = quiz_id;
		params["prefix_id_name"] = "quiz_total" + quiz_id;
		params["theme_name"] = "system";

		commonCls.sendPopupView(event, params, this._getPopupParams());
	},

	showReference: function(event, quiz_id, prefix_id_name) {
		var params = new Object();
		params["action"] = "quiz_view_edit_reference";
		params["quiz_id"] = quiz_id;
		params["prefix_id_name"] = prefix_id_name;

		commonCls.sendPopupView(event, params, this._getPopupParams());
	},

	disableScore: function(question_id) {
		var element = $("quiz_answer_score" + question_id + this.id);
		element.disabled = true;
		element.value = 0;
	},

	showQuestionaryAnswer: function(event, question_id) {
		var params = new Object();
		params["action"] = "quiz_view_edit_answer";
		params["quiz_id"] = this.quiz_id;
		params["question_id"] = question_id;
		params["prefix_id_name"] = "quiz_questionary_answer" + question_id;

		commonCls.sendPopupView(event, params, this._getPopupParams());
	},

	enterScore: function(target_id_name) {
		var answerResultForm = $("quiz_answer_result_form" + this.id);

		var params = new Object();
		params["callbackfunc"] = function(res){
										if ($(target_id_name) != null) {
											var params = new Object();
											params["action"] = "quiz_view_main_summary";
											params["quiz_id"] = this.quiz_id;
											params["theme_name"] = "system";
											commonCls.sendView(target_id_name, params);
										}
										commonCls.removeBlock(this.id);
									}.bind(this);

		commonCls.sendPost(this.id, Form.serialize(answerResultForm), params);
	}
}