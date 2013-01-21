var clsLinklist = Class.create();
var linklistCls = Array();

clsLinklist.prototype = {
	initialize: function(id) {
		this.id = id;

		this.currentLinklistID = null;
		this.linklist_id = null;
		this.target = null;
		this.viewCountFlag = false;
		this.popup = null;
		this.popupForm = null;
		
		this.entry = null;
		
		this.oldURL = null;
		this.automatic = false;
		this.automaticError = false;

		this.searchResults = new Array();
	},

	checkCurrent: function() {
		var currentRow = $("linklist_current_row" + this.currentLinklistID + this.id);
		if (!currentRow) {
			return;
		}
		Element.addClassName(currentRow, "highlight");

		var current = $("linklist_current" + this.currentLinklistID + this.id);
		current.checked = true;
	},

	changeCurrent: function(linklistID) {
		var oldCurrentRow = $("linklist_current_row" + this.currentLinklistID + this.id);
		if (oldCurrentRow) {
			Element.removeClassName(oldCurrentRow, "highlight");
		}

		this.currentLinklistID = linklistID;
		var currentRow = $("linklist_current_row" + this.currentLinklistID + this.id);
		Element.addClassName(currentRow, "highlight");

		var post = {
			"action":"linklist_action_edit_current",
			"linklist_id":linklistID
		};
		var params = new Object();
		params["callbackfunc_error"] = function(res) {
											commonCls.alert(res);
											commonCls.sendView(this.id, "linklist_view_edit_list");
										}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	referenceLinklist: function(event, linklistID, prefixID) {
		var params = new Object();
		params["action"] = "linklist_view_main_init";
		params["linklist_id"] = linklistID;
		params["prefix_id_name"] = prefixID;

		var popupParams = new Object();
		var top_el = $(this.id);
		popupParams['top_el'] = top_el;
		popupParams['target_el'] = top_el;
		popupParams['center_flag'] = true;

		commonCls.sendPopupView(event, params, popupParams);
	},

	deleteLinklist: function(linklistID, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;

		var post = {
			"action":"linklist_action_edit_delete",
			"linklist_id":linklistID
		};

		var params = new Object();
		params["target_el"] = $(this.id);
		params["callbackfunc_error"] = function(res) {
											commonCls.sendView(this.id, "linklist_view_edit_list");
										}.bind(this);
		commonCls.sendPost(this.id, post, params);
	},

	selectDisplayDropdown: function() {
		var element = $("linklist_display_description" + this.id);
		element.checked = false;
		element.disabled = true;

		Element.addClassName($("linklist_display_description_label" + this.id), "disable_lbl");
	},

	selectDisplayList: function() {
		$("linklist_display_description" + this.id).disabled = false;
		Element.removeClassName($("linklist_display_description_label" + this.id), "disable_lbl");
	},

	selectItem: function(select, hiddenElement, value) {
		var items = select.parentNode.childNodes;
		for (var i = 0,length = items.length; i < length; i++) {
			Element.removeClassName(items[i], "highlight");
		}

		Element.addClassName(select, "highlight");

		hiddenElement.value = value;
	},

	showPopup: function(action, eventElement) {
		this.popup = new compPopup(this.id,this.id);
		this.popup.modal = true;
		this.popup.loadObserver = function() {
										this.popupForm = this.popup.popupElement.contentWindow.document.getElementsByTagName("form")[0];
										if (this.popupForm["url"]) {
											commonCls.focus(this.popupForm["url"]);
										} else {
											commonCls.focus(this.popupForm);
										}
									}.bind(this);

		var params = new Object();
		params["param"] = {
			"action":action
		};
		params["top_el"] = this.id;
		params["callbackfunc"] = function(res) {
										this.popup.showPopup(res, eventElement);
									}.bind(this);

		commonCls.send(params);
	},

	changeAutomatic: function(checked) {
		commonCls.displayChange(this.popupForm["automatic_title"]);
		commonCls.displayChange(this.popupForm["title"]);
		commonCls.displayChange(this.popupForm["automatic_description"]);
		commonCls.displayChange(this.popupForm["description"]);

		if (!checked) {
			this.popupForm["title"].focus();
			this.popupForm["title"].select();
		} else {
			this.getLink();
		}
	},

	getLink: function() {
		var form = this.popupForm;
		if (form["url"].value == this.oldURL
				|| !form["automatic_check"].checked) {
			return;
		}

		if (this.automatic) {
			return;
		}
		
		this.automatic = true;
		var params = new Object();
		params["param"] = {
			"action":"linklist_view_main_automatic",
			"url":form["url"].value,
			"page_id":_nc_main_page_id
		};
		params["top_el"] = form;
		params["callbackfunc"] = function(res) {
										var tag = res.getElementsByTagName("title")[0];
										if (tag.firstChild) {
											form["automatic_title"].value = tag.firstChild.nodeValue;
										}

										var tag = res.getElementsByTagName("description")[0];
										if (tag.firstChild) {
											form["automatic_description"].value = tag.firstChild.nodeValue;
										} else {
											form["automatic_description"].value = "";
										}

										if (!Element.hasClassName(form["automatic_title"], "display-none")) {
											form["title"].value = form["automatic_title"].value;
										}
										if (!Element.hasClassName(form["automatic_description"], "display-none")) {
											form["description"].value = form["automatic_description"].value;
										}

										this.oldURL = form["url"].value;
										this.automatic = false;
										this.automaticError = false;
									}.bind(this);

		params["callbackfunc_error"] = function(res) {
											commonCls.alert(res);
											this.automatic = false;
											this.automaticError = true;
										}.bind(this);

		
		commonCls.send(params);
	},

	changeDescription: function() {
		commonCls.displayChange(this.popupForm.firstChild.rows[1]);
		this.popup.resize();
	},

	link: function(linkID, url, viewCountElement) {
		var post = {
			"action":"linklist_action_main_count",
			"linklist_id":this.linklist_id,
			"link_id":linkID
		};

		var params = new Object();
		if (this.viewCountFlag) {
			params["callbackfunc"] = function(res) {
										if (viewCountElement.tagName == "OPTION") {
											var tag = res.getElementsByTagName("option")[0];
										} else {
											var tag = res.getElementsByTagName("list")[0];
										}

										if (tag.firstChild) {
											viewCountElement.innerHTML = tag.firstChild.nodeValue;
										}
									}.bind(this);
		}
		commonCls.sendPost(this.id, post, params);

		if (this.target) {
			window.open(url, this.target);
		} else {
			location.href = url;
		}

	},

	selectLink: function(select, target) {
		if (select.value.length == 0)  {
			return;
		}

		var values = select.value.split("|");
		var targetElement = select.options[select.selectedIndex];

		this.link(values[0], values[1], targetElement);
	},

	showInputElement: function(element) {
		commonCls.displayNone(element);
		commonCls.displayVisible(element.nextSibling);
		commonCls.focus(element.nextSibling);
	},

	hideInputElement: function(element) {
		commonCls.displayNone(element);
		commonCls.displayVisible(element.previousSibling);
	},

	enterCategory: function(category, category_id) {
		if (category == null) {
			category = this.popupForm["category_name"];
		}

		var post = {
			"action":"linklist_action_main_category_entry",
			"category_id":category_id,
			"category_name":category.value,
			"entry":this.entry
		};

		var params = new Object();
		if (category_id == null) {
			params["target_el"] = $(this.id);
			params["callbackfunc"] = function(res) {
											this.popup.closePopup();
										}.bind(this);
		} else {
			params["callbackfunc"] = function(res) {
											category.previousSibling.innerHTML = category.value.escapeHTML();
											this.hideInputElement(category);
										}.bind(this);
		}

		commonCls.sendPost(this.id, post, params);
	},

	deleleCategory: function(category_id, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;

		var post = {
			"action":"linklist_action_main_category_delete",
			"category_id":category_id
		};

		var params = new Object();
		params["callbackfunc"] = function(res) {
										var categoryElement = $("linklist_category_link" + category_id + this.id);
										Element.remove(categoryElement);
									}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	enterLink: function(element, link_id) {
		if (link_id == null) {
			this.getLink();
	
			if (this.automaticError) {
				this.automaticError = false;
				return;
			}
			
			if (this.automatic) {
				setTimeout(function() {this.enterLink(element, link_id);}.bind(this), 300);
				return;
			}
		}

		var params = new Object();
		if (link_id == null) {
			if (this.popupForm["automatic_check"].checked) {
				if (this.popupForm["automatic_title"].value != "") {
					var title = this.popupForm["automatic_title"].value;
				} else {
					var title = "";
				}
				if (this.popupForm["automatic_description"].value != "") {
					var description = this.popupForm["automatic_description"].value;
				} else {
					var description = "";
				}
			} else {
				var title = this.popupForm["title"].value;
				var description = this.popupForm["description"].value;
			}

			var post = {
				"action":"linklist_action_main_link_entry",
				"category_id":this.popupForm["category_id"].value,
				"title":title,
				"url":this.popupForm["url"].value,
				"description":description,
				"entry":this.entry
			};
			if (this.popupForm["automatic_check"].checked) {
				post["automatic_check"] = this.popupForm["automatic_check"].value;
			}
			
			params["target_el"] = $(this.id);
			params["callbackfunc"] = function(res) {
											this.popup.closePopup();
										}.bind(this);
		} else {
			var post = {
				"action":"linklist_action_main_link_entry",
				"link_id":link_id,
				"entry":this.entry
			};
			post[element.name] = element.value;
			
			params["callbackfunc"] = function(res) {
											var value = element.value;
											if (element.tagName == "TEXTAREA") {
												element = element.parentNode;
											}
											element.previousSibling.innerHTML = value.escapeHTML();
											this.hideInputElement(element);
										}.bind(this);
		}

		commonCls.sendPost(this.id, post, params);
	},

	deleleLink: function(link_id, confirmMessage) {
		if (!commonCls.confirm(confirmMessage)) return false;

		var post = {
			"action":"linklist_action_main_link_delete",
			"link_id":link_id
		};

		var params = new Object();
		params["callbackfunc"] = function(res) {
										var linkElement = $("linklist_link" + link_id + this.id);
										Element.remove(linkElement);
									}.bind(this);

		commonCls.sendPost(this.id, post, params);
	},

	changeSequence: function(drag_id, drop_id, position) {
		if (drag_id.match(/linklist_category_link/)) {
			var post = {
				"action":"linklist_action_main_category_sequence",
				"drag_category_id":drag_id.match(/\d+/)[0],
				"drop_category_id":drop_id.match(/\d+/)[0],
				"position":position
			};
		} else if (drop_id.match(/linklist_category/)) {
			var post = {
				"action":"linklist_action_main_link_sequence",
				"drag_link_id":drag_id.match(/\d+/)[0],
				"drop_category_id":drop_id.match(/\d+/)[0]
			};
		} else {
			var post = {
				"action":"linklist_action_main_link_sequence",
				"drag_link_id":drag_id.match(/\d+/)[0],
				"drop_link_id":drop_id.match(/\d+/)[0],
				"position":position
			};
		}

		commonCls.sendPost(this.id, post);
	},

	search: function() {
		var params = new Object();
		params["param"] = {
			"action":"linklist_view_main_search_result",
			"search":this.popupForm["search"].value
		};
		params["top_el"] = this.id;
		params["callbackfunc"] = function(res) {
										if (this.searchResults.length > 0) {
											var beforeElement = this.searchResults[this.searchResults.length - 1];
											commonCls.displayNone(beforeElement);
										}

										var resultElement = this.popup.popupElement.contentWindow.document.createElement("DIV");
										resultElement.innerHTML = res;

										var re_words = new RegExp(this.popupForm["search"].value, 'i');
										var hits = 0;

										var replacer = function(str) {
															hits++;
															return '<span class="linklist_highlight">' + str + '</span>'
														};
										var titles = resultElement.getElementsByTagName("a");
										$A(titles).each(
															function(title) {
																title.innerHTML = title.innerHTML.gsub(re_words, replacer);
															}
														);

										var descriptions = resultElement.getElementsByTagName("div");
										$A(descriptions).each(
																function(description) {
																	if (description.className != 'linklist_description') return;
																	description.innerHTML = description.innerHTML.gsub(re_words, replacer);
																}
															);

										if (hits > 0) {
											// TODO
										} else {
											resultElement.innerHTML += '0 hits';
										}

										this.popupForm.appendChild(resultElement);
										this.searchResults.push(resultElement);
										this.popup.resize();
									}.bind(this);

		commonCls.send(params);
	}
}